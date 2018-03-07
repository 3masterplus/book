<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use \Curl\Curl;

class Api_base_lib
{
    /**
     * 构造函数
     */
    function __construct()
    {
        $this->ci = & get_instance();
    }


    /**
     * 校验客户端请求合法性
     * @return boolean
     */
    function valid_client()
    {
        //make test data
        return true;

        $key_name = $this->ci->_device;
        $this->ci->load->library('encryption');
        //aes解密时间戳
        $aes_key_arrs = $this->ci->config->item('client_unixtimescamp_aes_keys');
        if (!isset($aes_key_arrs[$key_name])) {
            echoErr('unsupport client', 'unsupport client');
        }
        $this->ci->encryption->initialize(
        array(
                'cipher' => 'aes-256',
                'mode'   => 'cbc',
                'key'    => $aes_key_arrs[$key_name],
            )
        );
        $unixtimestamp    = $this->ci->encryption->decrypt($this->ci->_unixtimestamp);
        //校验token信息
        $token            = $this->ci->_token;
        $private_key_arrs = $this->ci->config->item('client_private_keys');
        $private_key      = $private_key_arrs[$key_name];
        $generate_token   = sha1($this->ci->_device.$this->ci->_app_version.$private_key.$unixtimestamp);
        
        //make test data
        // $ut = $this->ci->encryption->encrypt(1445916462);
        // var_dump($ut, $generate_token);exit;

        if ($generate_token != $token) {
            echoErr('token not valied', 'token not valied');
        }
        //每次请求有效期为3分钟
        if (time() - $unixtimestamp > 180) {
            echoErr('token has out of date', 'token has out of date');
        }
        return true;
    }


    /**
     * 通过authcode获取当前用户的guid
     * @return integer
     */
    function get_user_guid()
    {
        $authcode = $this->ci->_authcode;
        $udata = $this->ci->my_lib->get_records('authcodes', '*', array('authcode' => $authcode, 'device' => $this->ci->_device), 1, 0, 'id', 'desc');
        if ($udata){
            $udata = $udata[0];
            $user_guid = $udata['user_guid'];
            if ($udata['expire_time'] < time()){
                echoErr('user login authorize out of date', 'user login authorize out of date');
            }
            $this->ci->_guid = $user_guid;
            $this->ci->_is_login = true;
        }
        return;
    }


    /**
     * 初始化用户的基本信息
     * @return [type] [description]
     */
    function init_user_info()
    {
        if ($this->ci->_guid) {
            $udata = $this->ci->my_lib->get_records('users', '*', array('guid' => $this->ci->_guid), 1, 0  );
            if ($udata){
                $udata = $udata[0];
                $this->ci->_username        = $udata['username'];
                $this->ci->_user_group_id   = $udata['user_group_id'];
                $this->ci->_email           = $udata['email'];
                $this->ci->_user_unique_key = $udata['unique_key'];
                $this->ci->_avatar_path     = IMGPATH.$udata['avatar_url'];
                $this->ci->_avatar_url      = IMGHOST.$udata['avatar_url'];
            }
        }
        return;
    }


    /**
     * 校验非法的用户组权限
     * @return [type] [description]
     */
    function ac_user_group($group_id = NULL, $user_guid = NULL)
    {
        if (!$user_guid && !$group_id) {
            return false;
        }
        if (!$group_id) {
            $group_id = $this->ci->my_lib->get_a_value('users', 'user_group_id',  array('user_guid' => $user_guid)  );
        }
        //开始校验权限
        if ($group_id == BANNED_USER_GROUP_ID) {
            echoErr('banned user', 'banned user');
        }
        return true;
    }


    /**
     * 判断当前访问者是否是登陆用户，如果是，返回true,否则输出错误
     * @return uid - integer
     */
    function checkAccesstoken()
    {
        if (!$this->ci->_guid) {
            echoErr('need login', 'need login');
        }
        return true;
    }

}