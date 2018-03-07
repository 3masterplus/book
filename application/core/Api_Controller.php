<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Api_Controller extends REST_Controller
{
    var $_output = true;
    var $_device;
    var $_app_version;
    var $_ts;
    var $_platform;
    var $_is_mobile_browser;
    var $_is_wechat_browser;
    var $_is_robot;
    var $_guid;
    var $_username;
    var $_user_group_id;
    var $_is_login;
    var $_email;
    var $_user_unique_key;
    var $_avatar_url;
    var $_avatar_path;
    var $_user_agent;
    var $_ip_address;
    var $_class;
    var $_method;
    var $_msg;
    var $_referrer;
    var $_delimiter;
    var $_t;
    var $site_name = 'zhiler.com';
    
    function __construct()
    {
        parent::__construct();
        //加载资源文件
        $this->load->library('MY_lib');
        $this->load->library('api/api_base_lib');
        $this->load->library('river_lib');
        $this->load->library('count_lib');
        $this->load->library('form_validation');

        $this->load->helper(array('form', 'url'));
        $this->load->helper('api');
        $this->load->helper('language');
        $this->load->library('email');

        //初始化变量
        $this->ini_var();
        //是否开启debug调试
        $this->profiler(false);
        $this->update_last_activity();

        /**
         * 引入自定义异常类
         */
        require_once APPPATH.'third_party/exception/ApiException.php';

    }
    
    private function profiler($open = TRUE)
    {
        if($open)
        {
            $this->output->enable_profiler(TRUE);
            $sections = array('config' => TRUE, 'queries' => TRUE, 'benchmarks' => TRUE, 'get' => TRUE);
            return $this->output->set_profiler_sections($sections);
        }
        return NULL;
    }
    
    private function ini_var()
    {
        $this->_t               = time();
        $this->_device          = strtolower(trim(getHeaderValue('device')));
        $this->_app_version     = trim(getHeaderValue('app_version'));
        $this->_token           = trim(getHeaderValue('token')); //device+app_version+private_key+unixtimescamp后做sha1加密串
        $this->_unixtimestamp   = trim(getHeaderValue('ut')); //时间戳AES加密后的字符串
        $this->_authcode        = trim(getHeaderValue('authcode')); //用户登陆后的授权码

        if (!$this->_device) {
            echoErr('missing device', 'missing device');
        }
        if (!$this->_app_version) {
            echoErr('missing app_version', 'missing app_version');
        }
        if (!$this->_token) {
            echoErr('missing token', 'missing token');
        }
        if (!$this->_unixtimestamp) {
            echoErr('missing unixtimestamp', 'missing unixtimestamp');
        }

        $this->_platform          = $this->my_lib->_platform;
        $this->_is_mobile_browser = $this->my_lib->_is_mobile_browser;
        $this->_is_wechat_browser = $this->my_lib->_is_wechat_browser;
        $this->_is_robot          = $this->my_lib->_is_robot;
        $this->_user_agent        = $this->my_lib->_user_agent;
        $this->_ip_address        = get_real_nginx_ip();
        $this->_class             = $this->my_lib->_class;
        $this->_method            = $this->my_lib->_method;
        $this->_referrer          = $this->my_lib->_referrer;
        $this->_delimiter         = $this->config->item('delimiter');
        $this->_guid              = 0;
        $this->_is_login          = false;
        
        //校验客户端凭证
        $this->api_base_lib->valid_client();
        //校验登陆用户的凭证
        $this->api_base_lib->get_user_guid();
        //获取用户基本信息
        $this->api_base_lib->init_user_info();
        //校验用户组合法性
        if ($this->_user_group_id) {
            $this->api_base_lib->ac_user_group($this->_user_group_id);
        }
    }

    
    //更新entities的最后活动时间
    private function update_last_activity()
    {
        if($this->_guid > 0)
        {
            $this->my_lib->update_records('entities', array('time_updated' => time()), array('guid' => $this->_guid));
        }
        
        return NULL;
    }
}