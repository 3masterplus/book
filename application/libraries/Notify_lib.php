<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Pheanstalk\Pheanstalk;

/**
 * 引入自定义异常类
 */
require_once APPPATH.'third_party/exception/ApiException.php';


class Notify_lib extends my_lib
{
    /**
     * 通知表
     */
    const TABLE_NOTIFY          = 'notify';
    
    /**
     * 用户通知关系表
     */
    const TABLE_USER_NOTIFY_REL = 'user_notify_relations';
    
    /**
     * 代发邮件任务表
     */
    const TABLE_EMAIL_JOBS      = 'email_jobs';

    /**
     * 秒级时间戳
     * @var
     */
    private $_t;

    /**
     * 任务队列 通知
     * @var 
     */
    private $task_notify;
    /**
     * 任务队列 推送
     * @var
     */
    private $task_push;
    
    /**
     * 任务队列 邮件
     * @var
     */
    private $task_email;

    /**
     * 消息类型
     * @var mix
     */
    private $msg_type = null;

    /**
     * 默认发件人为系统
     * @var integer
     */
    private $_sender = 0;

    /**
     * 默认收件人为空
     * @var null
     */
    private $_receiver = null;


    public $_target = null;


    public $_targetType = null;


    /**
     * 默认消息体
     * @var array
     */
    public $_msg = array();

    /**
     * 邮件模版
     * @var array
     */
    private $template_for_email = array();

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = & get_instance();
        $this->ci->load->config('MY_email');
        $this->ci->load->model('notify_model');
        $this->template_for_email = $this->ci->config->item('my_email');
        $this->_t = time();
    }

    /**
     * 校验消息受益主体是否合法
     * @param  [type] $receiver [description]
     * @return [type]           [description]
     */
    private function get_msg_type($receiver)
    {
        if (is_array($receiver)) {
            $this->_msg_type = 'group';
        }else if (is_numeric($receiver) && $this->ci->my_lib->check_a_record('users', array('guid' => $receiver))) {
            $this->_msg_type = 'single';
        }else if (is_string($receiver)) {
            $this->_msg_type = 'tag';
        }
        else {
            throw new Exception("Error Notify Request: illegal receiver");
        }
    }

    /**
     * 添加通知或者私信或者推送或者邮件
     * @param integer $sender     发送者guid
     * @param integer $receiver   接收者guid, 如果为某一个群体时传0
     * @param string  $type       消息类型，可选值：notice (通知)，msg (私信)
     * @param string  $targetType 消息体类型，可选值：new_course_pub（新课程发布）new_section_pub (新课节发布) 后期还会不断增加
     * @param array   $msg        消息体包含的自定义元素, 根据不同的消息体类型，自定义元素会不一样
     * @param boolean $push       是否需要客户端推送，优先级低于用户自定义的优先级
     * @param boolean $notify     是否需要写入到通知表，优先级低于用户自定义的优先级
     * @param boolean $email      是否需要邮件推送，优先级低于用户自定义的优先级
     */
    public function set($sender = 0, $receiver = 0, $type, $targetType, $msg = array(), $push = true, $notify = true, $email = true)
    {
        $this->get_msg_type($receiver);
        $this->_sender         = $sender;
        $this->_receiver       = $receiver;
        $this->_msg            = $msg;
        $this->_targetType     = $targetType;
        $this->_type           = $type;
        $this->_trigger_push   = (bool)$push;
        $this->_trigger_notify = (bool)$notify;
        $this->_trigger_email  = (bool)$email;
        $this->handle_msg(); //处理_msg数组
        $this->assign_msg(); //将_msg数组分配到不同的对象中去
        return true;
    }


    /**
     * 批量记录写入数据库
     * @param  string $table
     * @param  string $data
     * @return boolean
     */
    private function create_batch_record($table, $data)
    {
        return $this->ci->db->insert_batch($table, $data);
    }

    /**
     * 处理不同通知目标的任务
     * @return bool
     */
    private function notify()
    {
        $arr = array(
            'sender'       => $this->_sender,
            'type'         => $this->_type,
            'target'       => $this->_target,
            'targetType'   => $this->_targetType,
            'message'      => $this->_msg['body'],
            'custom'       => json_encode($this->_msg['custom']),
            'time_created' => $this->_t,
        );
        $notify_id = $this->create_a_record(self::TABLE_NOTIFY, $arr);
        
        $user_notify_rel_data = array(
            'notify_id' => $notify_id,
            'time_created' => $this->_t,
        );
        switch ($this->_msg_type) {
            case 'group':
                $job = $user_notify_rel_data;
                foreach ($this->_receiver as $k=>$v) {
                    $job['user_guid'] = $v;
                    $task_notify[] = $job;
                }
                $this->create_batch_record(self::TABLE_USER_NOTIFY_REL, $task_notify);
                break;
            case 'single':
                $job = $user_notify_rel_data;
                $job['user_guid'] = $this->_receiver;
                $task_notify[] = $job;
                $this->create_batch_record(self::TABLE_USER_NOTIFY_REL, $task_notify);
                break;
            case 'tag': //批量发送的消息，需要使用异步方式写入用户的关系表
                $job = array(
                    'user_notify_rel_data' => $user_notify_rel_data,
                    'tag' => $this->_receiver,
                );
                $ndata = json_encode($job);
                $this->send_notify_by_pheanstalk($ndata);
                break;
        }
        return true;
    }

    /**
     * 发送客户端推送
     * @return bool
     */
    private function push()
    {

    }


    private function email()
    {
        if (!isset($this->template_for_email['template'][$this->_targetType])) {
            return false;
        }
        $mailObj = $this->template_for_email['template'][$this->_targetType];
        $data['from']      = isset($mailObj['from']) ? $mailObj['from'] : $this->template_for_email['from'];
        $data['from_name'] = isset($mailObj['from_name']) ? $mailObj['from_name'] : $this->template_for_email['from_name'];
        $data['subject']   = $mailObj['subject'];
        $data['message']   = $mailObj['message'];
        $sm                = $this->convert_mail_content(['subject' => $data['subject'], 'message' => $data['message']]);
        $data['subject']   = $sm['subject'];
        $data['message']   = $sm['message'];
        switch ($this->_msg_type) {
            case 'group':
                foreach ($this->_receiver as $k => $v) {
                    $mail = $data;
                    $mail['to'] = $this->ci->my_lib->get_a_value('users', 'email', array('guid' => $v));
                    $mail['type'] = 'single';
                    $this->sendemail_by_pheanstalk($mail);
                }
                break;
            case 'single':
                $mail = $data;
                $mail['to'] = $this->ci->my_lib->get_a_value('users', 'email', array('guid' => $this->_receiver));
                $mail['type'] = 'single';
                $this->sendemail_by_pheanstalk($mail);
                break;
            case 'tag':
                $mail = $data;
                $mail['to'] = $this->_receiver;
                $mail['type'] = 'tag';
                $this->sendemail_by_pheanstalk($mail);
                break;
        }
    }

    /**
     * 处理邮件模版中的动态变量
     * @param  array $rough 处理前的数据
     * @return array
     */
    private function convert_mail_content($rough) 
    {
        $res = array_map(function($v){
            if (preg_match('/\[course_title\]/', $v)) {
                if (!isset($this->_msg['course_title'])) {
                    throw new Exception("Notify:email template missing course_title");
                }
                $course_title = $this->_msg['course_title'];
                $v = preg_replace('/\[course_title\]/', $course_title, $v);
            }
            if (preg_match('/\[section_title\]/', $v)) {
                if (!isset($this->_msg['section_title'])) {
                    throw new Exception("Notify:email template missing section_title");
                }
                $section_title = $this->_msg['section_title'];
                $v = preg_replace('/\[section_title\]/', $section_title, $v);
            }
            return $v;

        }, $rough);
        return $res;
    }


    /**
     * 生成对应的推送模板，消息模板，邮件模板
     * @return array
     */
    private function assign_msg()
    {
        if ($this->_trigger_notify) {
            $this->notify();
        }
        if ($this->_trigger_push) {
            $this->push();
        }
        if ($this->_trigger_email) {
            $this->email();
        }
    }


    /**
     * 校验格式化_msg数据
     * @return array
     */
    private function handle_msg()
    {
        switch ($this->_targetType) {
            /**
             * 有新的课程发布
             * 必需的参数:
             * $_msg['course_guid'] - integer //课程guid
             * 可选参数:
             * $_msg['course_unique_key'] - string //课程唯一识别码
             * $_msg['body'] - string //推送内容
             */
            case 'new_course_pub':
                if (!isset($this->_msg['course_guid'])) {
                    throw new Exception('Notify:new_course_pub missing course_guid');
                }
                if (!isset($this->_msg['course_unique_key'])) {
                    $this->_msg['course_unique_key'] = $this->ci->my_lib->get_a_value('courses', 'unique_key', array('guid' => $this->_msg['course_guid']) );
                }
                if (!isset($this->_msg['body'])) {
                    $this->_msg['body'] = "有新的课程发布了，快去看看吧";
                }
                $this->_target = $this->_msg['course_guid'];
                $this->_msg['custom'] = isset($this->_msg['custom']) ? $this->_msg['custom'] : array();
                $this->_msg['custom'] = array_merge($this->_msg['custom'], array('course_unique_key' => $this->_msg['course_unique_key']) );
                break;
            /**
             * 有新的课节发布
             * 必需的参数:
             * $_msg['section_guid'' - integer //课节guid
             * 可选参数:
             * $_msg['body'] - string //推送内容
             */
            case 'new_section_pub':
                if (!isset($this->_msg['section_guid'])) {
                    throw new Exception('Notify:new_section_pub missing section_guid');
                }
                $section_data = $this->_msg['section_unique_key'] = $this->ci->my_lib->get_records('sections', 'unique_key as section_unique_key,title as section_title, course_guid', array('guid' => $this->_msg['section_guid']) );
                if (!$section_data) {
                    throw new Exception("Notify:section_guid no found");
                }
                $section_data = $section_data[0];
                $course_title = $this->ci->my_lib->get_a_value('courses', 'title', array('guid' => $section_data['course_guid']));
                if (!isset($this->_msg['body'])) {
                    $this->_msg['body'] = "您关注的课程{$course_title}有新的课节[{$section_data['section_title']}]发布了，快去看看吧";
                }
                $this->_target = $this->_msg['section_guid'];
                $this->_msg['custom'] = isset($this->_msg['custom']) ? $this->_msg['custom'] : array();
                $this->_msg['custom'] = array_merge($this->_msg['custom'], array('section_unique_key' => $this->_msg['section_unique_key']) );
                break;
            /**
             * 常规性的消息推送
             * 必需的参数:
             * $_msg['body'] - string //消息主体
             * 可选参数:
             * $_msg['custom'] - array //自定义数据
             */
            default:
                if (!isset($this->_msg['body']) || !is_string($this->_msg['body']) ) {
                    throw new Exception("Error Notify Request");
                }
                if (!array_key_exists('custom', $this->_msg)) {
                    $this->_msg['custom'] = array();
                }
                break;
        }
    }

    // ***************************************************
    // 发送邮件
    // ***************************************************
    
    /**
     * 将发送邮件任务写入消息队列
     * @param  array $data
     * @param  array $conf
     * @return bool
     */
    public function sendemail_by_pheanstalk($data, $conf = null)
    {
        $edata = array(
            'data' => $data,
            'conf' => $conf,
        );
        $edata = json_encode($edata);
        $pheanstalk = new Pheanstalk('127.0.0.1', '11300', 2);
        $pheanstalk_st = $pheanstalk->getConnection()->isServiceListening();
        if ($pheanstalk_st) {
            $pheanstalk->useTube('Zhiler/Sendemail');
            $pheanstalk->put($edata);
        }else {
            //发送任务写入到数据库
            $data = array(
                'content' => $edata,
                'send_time' => $this->_t,
                'time_created' => $this->_t,
            );
            $this->ci->my_lib->create_a_record(self::TABLE_EMAIL_JOBS, $data);
        }
        return true;
    }


    /**
     * 将发送通知的任务写入消息队列
     * @param  array $data
     * @param  array $conf
     * @return bool
     */
    public function send_notify_by_pheanstalk($ndata)
    {
        $pheanstalk = new Pheanstalk('127.0.0.1', '11300', 2);
        $pheanstalk_st = $pheanstalk->getConnection()->isServiceListening();
        if ($pheanstalk_st) {
            $pheanstalk->useTube('zhiler_send_tag_notify');
            $pheanstalk->put($ndata);
            return true;
        }
        return false;
    }

    /**
     * 发送邮件
     * @param  array $data 邮件数据
     * @param  array $conf 邮件配置
     * @return bool
     */
    public function custom_sendmail($data, $conf = null)
    {
        $this->ci->load->library('email');
        $email_main_from = $this->template_for_email['from'];
        $email_main_from_name = $this->template_for_email['from_name'];
        if ($conf) {
            $this->ci->email->initialize($conf);
        }
        $from      = isset($data['from']) ? $data['from'] : $email_main_from;
        $from_name = isset($data['from_name']) ? $data['from_name'] : $email_main_from_name;
        $to        = $data['to'];
        $subject   = isset($data['subject']) ? $data['subject'] : null;
        $message   = isset($data['message']) ? $data['message'] : null;
        $this->ci->email->from($from, $from_name);
        $this->ci->email->to($to);
        $this->ci->email->subject($subject);
        $this->ci->email->message($message);
        if (!$this->ci->email->send()) {
            $this->logemail($from, $from_name, $to, $subject, $message, '0');
            return false;
        } else {
            $this->logemail($from, $from_name, $to, $subject, $message, '1');
            return true;
        }
    }

    /**
     * 记录email发送日志
     * @return [type] [description]
     */
    private function logemail($from = null, $from_name = null, $to = null, $subject = null, $content = null, $send_status = '0')
    {
        $edata = array(
            'from'         => $from,
            'from_name'    => $from_name,
            'to'           => is_array($to) ? implode(';', $to) : $to,
            'subject'      => $subject,
            'content'      => $content,
            'user_guid'    => $this->ci->_guid,
            'ip_address'   => $this->ci->_ip_address,
            'user_agent'   => $this->ci->_user_agent,
            'time_created' => $this->_t,
            'send_status'  => $send_status,
        );
        return $this->create_a_record('emaillogs', $edata);
    }

    /**
     * 获取通知列表
     * @param  [type]  $select   [description]
     * @param  [type]  $where    [description]
     * @param  [type]  $limit    [description]
     * @param  integer $offset   [description]
     * @param  string  $orderby  [description]
     * @param  string  $sort     [description]
     * @param  boolean $forcount [description]
     * @return [type]            [description]
     */
    function get_notify_list($select, $where, $limit = NULL, $offset = 0, $orderby = '', $sort = '', $forcount = false)
    {
        return $this->ci->notify_model->get_notify_list($select, $where, $limit, $offset, $orderby, $sort, $forcount);
    }

    /**
     * 格式化消息数据
     * @param  array $notify
     * @return array
     */
    function handle_notify_data( & $notify)
    {
        if ($notify) {
            foreach ($notify as $k=>$v) {
                $time_created = $v['time_created'];
                $human_time_created = __time($time_created);
                $targetType = $v['targetType'];
                $message = $v['message'];
                $custom = json_decode($v['custom'], true);
                switch ($targetType) {
                    case 'new_course_pub':
                        $targetType_alias  = '新课程发布';
                        $url = "course/".$custom['course_unique_key']."/home";
                        $message_with_link = "<a href=".base_url($url).">".$message."</a>";
                        break;
                    case 'new_section_pub':
                        $targetType_alias = '新章节发布';
                        $message_with_link = $message;
                        break;
                    default:
                        $targetType_alias = '新通知';
                        $message_with_link = $message;
                        break;
                }
                $notify[$k]['message_with_link']  = $message_with_link;
                $notify[$k]['targetType_alias']   = $targetType_alias;
                $notify[$k]['human_time_created'] = $human_time_created;
                $notify[$k]['custom']             = $custom;
                if ($v['sender']) {
                    $notify[$k]['sender_unique_key'] = $this->ci->my_lib->get_a_value('users', 'unique_key', array('guid' => $v['sender']));
                }else {
                    $notify[$k]['sender_unique_key'] = null;
                }
            }
        }
    }

    /**
     * 标记消息为已读
     * @param  integer $user_guid [description]
     * @param  integer $notify_id [description]
     * @return bool
     */
    function mark_read($user_guid, $notify_id)
    {
        $where = array(
            'user_guid' => $user_guid,
            'notify_id' => $notify_id,
        );
        $notify = $this->ci->my_lib->get_records('user_notify_relations', '*', $where, 1);
        if (!$notify) {
            throw new ApiException("notify no found", '通知不存在');
        }
        $status = $notify[0]['is_read'];
        if ($status == 1) {
            throw new ApiException("notify readed", '通知已读');   
        }
        return $this->ci->my_lib->update_records('user_notify_relations', array('is_read' => '1'), $where);
    }
}