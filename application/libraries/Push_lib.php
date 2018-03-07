<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * 通用的推送写入，通知写入，邮件发送
 *
 * 针对单一用户的推送，先立即调用信sdk执行推送，再记录到数据库备份,一对多的情况都是先记录到数据库，再通过定时任务执行
 *
 */

class Push_lib extends my_lib
{
    const TABLE_PUSH_SINGLE   = 'push_to_single'; //一对一推送表
    const TABLE_PUSH_GROUP    = 'push_to_group'; //一对多推送表
    const TABLE_SINGLE_NOTIFY = 'notifications'; //一对一推送表
    const TABLE_SINGLE_EMAIL  = 'email_notifies'; //一对一推送表

    /**
     * 秒级时间戳
     * @var [type]
     */
    private $t;

    /**
     * 消息受益主体
     *
     * course_feeder //课程订阅者
     * all_users //所有用户
     * user_guid //单一用户，具体的用户ID
     *
     * @var array
     */
    private $receiver_types = array('course_feeder', 'all_users');

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

    /**
     * 默认消息体
     * @var array
     */
    private $_message = array();

    /**
     * 邮件模版
     * @var array
     */
    private $email_tpls = array();

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->ci->load->config('email_tpls');
        $this->email_tpls = $this->ci->config->item('email_tpls');
    }

    /**
     * 校验消息受益主体是否合法
     * @param  [type] $receiver [description]
     * @return [type]           [description]
     */
    private function check_receiver_type($receiver)
    {
        /**
         * 校验消息主体是否存在
         */
        if (is_int($receiver)) {
            $where = array(
                'guid' => $receiver,
            );
            $isU = $this->my_lib->check_a_record('users', $where);
            if (!$isU) {
                return false;
            }
        } else if (!in_array($receiver, $this->receiver_types)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 添加消息
     * @param  integer $sender       [description]
     * @param  integer $receiver     [description]
     * @param  array   $message      消息体, 包含参数参考注释部分
     * @param  boolean $push         是否推送
     * @param  boolean $notify       是否通知
     * @param  boolean $email        是否邮件
     * @return json
     *
     * $message包含的参数, 方便起见， $message 使用$msg缩写
     *
     * $msg['body'] = '这是一个消息主体内容' //必需
     * $msg['code'] = '这是一个消息类型码，用于匹配对应的推送模板，通知模板，邮件模板' //可选， 如果没有匹配到对应的模板，则会发送纯文本
     *
     */
    public function addSinglePush($sender = 0, $receiver = 0, $message = array(), $push = false, $notify = false, $email = false)
    {
        $check_ret = $this->check_receiver_type($receiver);
        if (!$check_ret) {
            return false;
        }
        $this->_sender = $sender;
        $this->_receiver = $receiver;
        $this->_message = $message;

        $this->format_data();

        /**
         * 写入推送记录
         */
        if ($push) {
            $this->record_push();
        }
        if ($notify) {
            $this->record_notify();
        }
        if ($email) {
            $this->record_email();
        }
        return true;
    }

    /**
     * 记录推送消息
     * @return [type] [description]
     */
    private function record_push()
    {
        if (is_int($this->_receiver)) {
            $table = TABLE_PUSH_SINGLE;
        }else {
            $table = TABLE_PUSH_GROUP;
        }
    }

    /**
     * 记录站内消息
     * @return [type] [description]
     */
    private function record_notify()
    {
        $table = TABLE_SINGLE_NOTIFY;
    }

    /**
     * 记录邮件到消息队列或直接发送邮件
     * @return [type] [description]
     */
    private function record_email()
    {
        $table = TABLE_SINGLE_EMAIL;
    }

    /**
     * 生成对应的推送模板，消息模板，邮件模板
     * @return [type] [description]
     */
    private function format_data()
    {
        $template_code = $this->_message['code'];
        $message_body = $this->_message['body'];

        switch ($template_code) {
            /**
             * 有新的课程发布
             * 必需的参数:
             * course_guid - integer //课程guid
             */
            case 'new_course_published':
                try {
                    if (!isset($this->_message['course_guid'])) {
                        throw new Exception('push_lib//new_course_published template missing: course_guid');
                    }
                } catch (Exception $e) {
                    exit( $e->getMessage() );
                }
                $course_guid = $this->_message['course_guid'];
                /**
                 * 推送模板
                 */
                $this->tpl['push'] = array(
                    'push_type' => 'new_course_published',
                    'push_title' => '知乐',
                    'message' => $message_body,
                    'custom' => array(
                        'course_unique_key' => $course_unique_key,
                        'push_type' => 'new_course_published',
                    ),
                    'push_time' => isset($this->_message['push_time']) ? $this->_message['push_time'] : $this->ci->_t,
                    'time_created' => $this->ci->_t,
                    'is_pushed' => '0'
                );
                /**
                 * 通知模板
                 */
                $this->tpl['notify'] = array(
                    'notify_type' => 'new_course_published',
                    'message' => $message_body,
                    'custom' => array(
                        'course_unique_key' => $course_unique_key,
                        'notify_type' => 'new_course_published',
                    ),
                    'time_created' => $this->ci->_t,
                    'is_read' => '0'
                );
                /**
                 * 通知邮件
                 */
                $this->tpl['email'] = array(
                    'subject' => '有新的课程发布了, 快去看看吧',
                    'message' => isset($this->email_tpls['new_course_published']) ? $this->email_tpls['new_course_published'] : $message_body,
                );
                break;

            default:
                
                $this->tpl['push'] = array(
                    'push_type' => 'common',
                    'push_title' => '知乐',
                    'message' => $message_body,
                    'custom' => array(
                        'push_type' => 'common'
                    ), 
                    'push_time' => isset($this->_message['push_time']) ? $this->_message['push_time'] : $this->ci->_t,
                    'time_created' => $this->ci->_t,
                    'is_pushed' => '0'
                );
                $this->tpl['notify'] = array(
                    'notify_type' => 'common',
                    'message' => $message_body,
                    'custom' => array(
                        'notify_type' => 'common',
                    ),
                    'time_created' => $this->ci->_t,
                    'is_read' => '0'
                );
                $this->tpl['email'] = array(
                    'subject' => '知乐有新的动态了，快去看看吧',
                    'message' => $message_body,
                );
                break;
        }
    }

    // ***************************************************
    // 发送邮件
    // ***************************************************
    public function custom_sendmail($data, $conf = null)
    {
        $email_main_from = $this->ci->config->item('email_main_from');
        $email_main_from_name = $this->ci->config->item('email_main_from_name');


        $this->ci->load->library('email');

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
            $this->emaillogs($from, $from_name, $to, $subject, $message, '0');
            return false;
        } else {
            $this->emaillogs($from, $from_name, $to, $subject, $message, '1');
            return true;
        }

    }

    /**
     * 记录email发送日志
     * @return [type] [description]
     */
    private function emaillogs($from = null, $from_name = null, $to = null, $subject = null, $content = null, $send_status = '0')
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
            'time_created' => $this->ci->_t,
            'send_status'  => $send_status,
        );
        return $this->create_a_record('emaillogs', $edata);
    }

}
