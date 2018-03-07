<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

/**
 * 引入自定义异常类
 */
require_once APPPATH.'third_party/exception/ApiException.php';


class Api_user_lib extends my_lib
{
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    /**
     * 通过用户名和密码获取用户登录凭证
     * @param  string $email
     * @param  string $password
     * @return
     */
    public function login_a_user_api($email, $password)
    {
        $user = $this->ci->user_lib->get_user_info($email);
        $banned_group_id = $this->ci->my_lib->get_a_value('user_groups', 'id', array('user_group' => 'banned'));
        if ($user['user_group_id'] == $banned_group_id) {
            echoErr('banned user', 'banned user');
        } elseif ($user['password'] != md5($password . $user['salt'])) {
            echoErr('username or password error', 'username or password error');
        } else {
            //将要写入的session数据准备好，并写入session
            $guid = $user['guid'];
            $user_unique_key = $user['unique_key'];
            //将用户行为写入日志系统
            //$this->ci->river_lib->logit('login', $guid, $user_unique_key, array(), 'login_with_email');
            //generate authcode
            $output = $this->generate_user_authcode($guid);
            return $output;
        }
    }

    /**
     * 生成用户登陆授权码
     * @param  integer $guid 用户GUID
     * @return array
     */
    public function generate_user_authcode($guid)
    {
        //先将原来该平台上的用户登陆授权码设置为过期

        $t = time();
        $udata = array(
            'user_guid' => $guid,
            'authcode' => rand_str(16),
            'device' => $this->ci->_device,
            'time_created' => $t,
            'expire_time' => $t + 2592000, //一个月有效期
        );
        $this->ci->my_lib->create_a_record('authcodes', $udata);

        $user_unique_key = $this->ci->my_lib->get_a_value('users', 'unique_key', array('guid' => $guid));

        // var_dump($user_unique_key,$guid);exit;

        $res = array(
            'authcode' => $udata['authcode'],
            'expire_time' => $udata['expire_time'],
            'user_unique_key' => $user_unique_key,
        );
        return $res;
    }

    // 修改密码
    function reset_password($user_unique_key, $password)
    {
        //首先判断，该用户是否已经设置电子邮箱
        $email = $this->ci->my_lib->get_a_value('users', 'email', array('unique_key' => $user_unique_key));
        if(!$email) {
            throw new ApiException("need set email first", '尚未设置邮箱地址，不能修改密码', 406);
        } else{
            $salt = md5($user_unique_key.$password.time().rand_str(32));
            $password = md5($password.$salt);
            $data = array('password' => $password, 'salt' => $salt);
            //开始数据库存储事件
            $this->ci->db->trans_start();
            //更新用户的密码
            $condition = array('unique_key' => $user_unique_key);
            $this->update_records('users', $data, $condition);
            //结束数据库存储事件
            $this->ci->db->trans_complete();
            return $this->ci->db->trans_status();
        }
    }

    /**
     * 获取用户的公开的个人资料
     * @param  string $select_items 自定义获取数据
     * @param  array $field        自定义获取数据
     * @return string
     */
    public function append_field($select_items, $field)
    {
        if (in_array('user_unique_key', $field)) {
            $select_items .= ",users.unique_key as user_unique_key";
        }
        if (in_array('username', $field)) {
            $select_items .= ",users.username";
        }
        if (in_array('user_group_id', $field)) {
            $select_items .= ",users.user_group_id";
        }
        if (in_array('signature', $field)) {
            $select_items .= ",users.signature";
        }
        if (in_array('bio', $field)) {
            $select_items .= ",users.bio";
        }
        if (in_array('avatar_url', $field)) {
            $select_items .= ",users.avatar_url";
        }
        return $select_items;
    }

    /**
     * 获取用户的私有的个人资料
     * @param  string $select_items 自定义获取数据
     * @param  array $field        自定义获取数据
     * @return string
     */
    public function append_private_field($select_items, $field)
    {
        //用户未登陆
        if (!$this->ci->_guid) {
            return $select_items;
        }
        if (in_array('email', $field)) {
            $select_items .= ",users.email";
        }
        if (in_array('time_password_reset_link_sent', $field)) {
            $select_items .= ",users.time_password_reset_link_sent";
        }
        if (in_array('time_verification_link_sent', $field)) {
            $select_items .= ",users.time_verification_link_sent";
        }
        if (in_array('name', $field)) {
            $select_items .= ",users.name";
        }
        if (in_array('mobile', $field)) {
            $select_items .= ",users.mobile";
        }
        if (in_array('have_pwd', $field)) {
            $select_items .= ",users.password as have_pwd";
        }
        
        // if (in_array('wecaht', $field)) {
        //     $select_items .= ",users.wechat";
        // }
        // if (in_array('qq', $field)) {
        //     $select_items .= ",users.qq";
        // }
        return $select_items;
    }

    /**
     * 获取用户复杂的公开自定义输出
     * @param  array $udata 二维数组用户数据
     * @param  array $field
     * @return array
     */
    public function process_user_data($udata, $field)
    {
        if (!$udata) {
            return $udata;
        }

        foreach ($udata as $k=>$u) {
            if (isset($u['guid'])) {
                $uid = $u['guid'];
            }elseif (isset($u['user_unique_key']))  {
                $unique_key = $u['user_unique_key'];
                $uid = $this->ci->my_lib->get_a_value('users', 'guid', array('unique_key' => $unique_key));
            }else {
                throw new ApiException("miss uid or unique_key", '基础数据缺失');
            }
            if (isset($u['avatar_url'])) {
                $udata[$k]['avatar_url'] = IMGHOST.$u['avatar_url'];
            }
        }

        return $udata;
    }

    /**
     * 获取用户复杂的私有自定义输出
     * @param  array $udata 二维数组用户数据
     * @param  array $field
     * @return array
     */
    public function process_user_private_data($udata, $field)
    {
        if (!$this->ci->_guid) {
            return $udata;
        }
        if (!$udata) {
            return $udata;
        }
        foreach ($udata as $k=>$u) {
            if (isset($u['guid'])) {
                $uid = $u['guid'];
            }elseif (isset($u['user_unique_key']))  {
                $unique_key = $u['user_unique_key'];
                $uid = $this->ci->my_lib->get_a_value('users', 'guid', array('unique_key' => $unique_key));
            }else {
                throw new ApiException("miss uid or unique_key", '基础数据缺失');
            }
            //是否设置了密码
            if (in_array('have_pwd', $field)) {
                $udata[$k]['have_pwd'] = @$u['have_pwd'] ? true : false;
            }
        }
        return $udata;
    }

    /**
     * 获取第三方账号绑定信息
     * @return array
     */
    public function get_user_social_media()
    {
        if (!$this->ci->_guid) {
            return array();
        }
        $where = array(
            'guid' => $this->ci->_guid,
        );
        return $this->ci->my_lib->get_records('third_party_bindings', 'third_uid,third_username,third_platform', $where);
    }


    /**
     * 取消第三方账号绑定, 用户必需是登陆状态
     * @param  string type //第三方平台标识 platform
     * @return boolean
     */
    public function cancel_media_bingding($type)
    {
        if (!$this->ci->_guid) {
            throw new ApiException("need login", '请先登录');
        }

        //判断用户是否设置了邮箱和密码
        $udata = $this->ci->user_lib->get_user_info($this->ci->_user_unique_key);
        if (!$udata['email']) {
            throw new ApiException('need set email first', "您需要设置登录邮箱后才能解除绑定");
        }
        if (!$udata['password']) {
            throw new ApiException('need set pwd first', "您需要设置密码后才能解除绑定");
        }
        $where = array(
            'guid' => $this->ci->_guid,
            'third_platform' => $type,
        );
        return $this->ci->my_lib->delete_records('third_party_bindings', $where);
    }




    /**
     * 第三方账号绑定, 用户必需是登陆状态
     * @return boolean
     */
    public function user_binding_media($type, $third_data)
    {
        if (!$this->ci->_guid) {
            throw new ApiException('need login',"请先登录");
            // return echoErr('need login', '请先登陆');
        }

        /**
         * third_data中的参数说明
         *
         * third_email //第三方平台账号邮箱，可选，目前没有要求绑定时更新该字段
         * third_uid //必需，第三方平台的唯一账号id，请注意微信中应该使用union_id, 而不是openid
         * third_access_token //必需，第三方平台账号access_token
         * avatar //可选，第三方平台账号头像
         * third_name //必需，第三方平台账号的用户名
         * third_openid //可选，微信专用
         * third_refresh_token//可选，第三方平台如果提供refresh_token,则传入
         * expired_in //必需，第三方access_token过期10位秒级时间戳， eg: 1448765452
         */

        $wb_email   = isset($third_data['third_email']) ? $third_data['third_email'] : null; //绑定时是否需要修改用户的email为第三方账号的email? 目前的处理方式是不修改，一旦修改，逻辑复杂度会增加
        $wb_uid     = isset($third_data['third_uid']) ? $third_data['third_uid'] : null;
        $wb_at      = isset($third_data['third_access_token']) ? $third_data['third_access_token'] : null;
        $wb_avatar  = isset($third_data['avatar']) ? $third_data['avatar'] : null;
        $wb_name    = isset($third_data['third_name']) ? $third_data['third_name'] : null;
        $wb_openid  = isset($third_data['third_openid']) ? $third_data['third_openid'] : null;
        $wb_rt      = isset($third_data['third_refresh_token']) ? $third_data['third_refresh_token'] : null;
        $wb_expired = isset($third_data['expired_in']) ? $third_data['expired_in'] : null;
        //当前登陆用户guid和详细信息
        $uid        = $this->ci->_guid;
        $udata      = $this->ci->my_lib->get_records('users', '*', array('guid' => $uid), 1, 0);
        $udata      = $udata[0];

        //用户是否已经绑定第三方账号
        $third_uid = $this->ci->my_lib->get_a_value('third_party_bindings', 'third_uid', array('guid' => $uid, 'third_platform' => $type));
        //用户已绑定
        if ($third_uid) {
            //绑定过的账号和当前待绑定账号是同一个账号
            if ($third_uid == $wb_uid) {
                //被动更新用户过期的access_token
                $update_data = array(
                    'third_access_token' => $wb_at,
                    'expired_in' => $wb_expired,
                );
                $this->ci->my_lib->update_records('third_party_bindings', $update_data, array('guid' => $uid, 'third_platform' => $type));
                throw new ApiException('have bind this third_uid',"您已经绑定过了该账号");
                // return echoErr('have bingding current third account', '您已经绑定过了该账号');
            }else {  //绑定过的第三方账号和当前待绑定第三方账号不一致
                throw new ApiException('have bind another third_uid',"您已经绑定过了另外一个账号");
                // return echoErr('have binding other third account', '您已经绑定过了另外一个账号');
            }
        }else { //用户未绑定任何第三方账号

            /**
             * 判断当前的第三方账号是否已经被他人绑定了
             */
            $other_user_guid = $this->ci->my_lib->get_a_value('third_party_bindings', 'guid', array('third_uid' => $wb_uid, 'third_platform' => $type));

            switch ($other_user_guid) {
                case true:
                    throw new ApiException('this third_uid have binded', "该账号已经被他人绑定过了");
                    // return echoErr('third account have bingdinged', '该账号已经被他人绑定过了');
                    break;
                case false:
                    if ($wb_avatar && !$udata['avatar_url']) {
                        $user_unique_key = $this->ci->my_lib->get_a_value('users', 'unique_key', array('guid' => $uid));
                        $relative_path = "upload/".$user_unique_key."/images/avatar";
                        $this->ci->load->library('api/api_image_lib');
                        $imgdata = $this->ci->api_image_lib->save_img_to_local($wb_avatar, $relative_path);
                        //将图片相对地址写入到用户头像字段中
                        if ($imgdata) {
                            $relative_img = preg_replace('/^\/+/', '', $imgdata['relative_name']);
                            $this->ci->my_lib->update_records('users', array('avatar_url' => $relative_img), array('guid' => $uid));
                        }
                    }
                    //创建绑定关系
                    $binding_data = array(
                        'third_uid' => $wb_uid,
                        'third_username' => $wb_name,
                        'third_access_token' => $wb_at,
                        'third_platform' => $type,
                        'guid' => $uid,
                        'time_created' => time(),
                        'expired_in' => $wb_expired,
                        'third_refresh_token' => $wb_rt,
                        'third_openid' => $wb_openid,
                    );
                    $this->ci->my_lib->create_a_record('third_party_bindings', $binding_data);
                    //发送欢迎邮件
                    break;
            }
        }
        return true;
    }




    /**
     * 第三方账号注册和登陆，授权登陆注册  (为了保证access_token不会过期，还需要额外写一个crontab, 用来主动自动更新即将过期的access_token)
     * @param  string  $type              第三方平台标识
     * @param  string  $third_data        第三方平台数据
     * @param  [type]  $userdata          用户原有数据
     * @param  boolean $is_email_modified 邮箱是否修改过
     * @param  boolean $is_login          是否是登陆请求
     * @return array
     */
    public function handle_media_bindings($type = null, $third_data, $userdata, $is_email_modified = false, $is_login = false)
    {
        $wb_email = isset($third_data['third_email']) ? $third_data['third_email'] : null;
        $wb_uid = isset($third_data['third_uid']) ? $third_data['third_uid'] : null;
        $wb_at = isset($third_data['third_access_token']) ? $third_data['third_access_token'] : null;
        $wb_avatar = isset($third_data['avatar']) ? $third_data['avatar'] : null;
        $wb_name = isset($third_data['third_name']) ? $third_data['third_name'] : null;
        $wb_openid = isset($third_data['third_openid']) ? $third_data['third_openid'] : null;
        $wb_rt = isset($third_data['third_refresh_token']) ? $third_data['third_refresh_token'] : null;
        $wb_expired = isset($third_data['expired_in']) ? $third_data['expired_in'] : null;
        $wb_pwd = isset($third_data['password']) ? $third_data['password'] : null;
        $username = isset($third_data['username']) ? $third_data['username'] : $wb_name;
        //第三方账号是否已经绑定
        $user_guid = $this->ci->my_lib->get_a_value('third_party_bindings', 'guid', array('third_uid' => $wb_uid, 'third_platform' => $type));

        switch ($user_guid) {
            //第三方账号已绑定过
            case true:
                $tokendata = $this->generate_user_authcode($user_guid);
                //被动更新用户过期的access_token
                $update_data = array(
                    'third_access_token' => $wb_at,
                    'expired_in' => $wb_expired,
                );
                $this->ci->my_lib->update_records('third_party_bindings', $update_data, array('third_uid' => $wb_uid, 'third_platform' => $type));
                break;
            //第三方账号未绑定
            case false:
                //属于登陆请求的话：
                if ($is_login) {
                    $tokendata = echoErr('reg', 'first time associated');
                }else {
                    $have_email = $this->ci->my_lib->check_a_record('users', array('email' => $wb_email));
                    switch ($have_email) {
                    //第三方账号邮箱在系统中存在
                    case true:
                            $tokendata = echoErr('bd', "email registered");
                            break;
                    //第三方账号邮箱在系统中不存在
                    case false:
                            //注册
                            $this->ci->user_lib->create_a_user($username, $wb_email, $wb_pwd);
                            $user_guid = $this->ci->my_lib->get_a_value('users', 'guid', array('email' => $wb_email));
                            if ($wb_avatar) {
                                $user_unique_key = $this->ci->my_lib->get_a_value('users', 'unique_key', array('guid' => $user_guid));
                                $relative_path = "upload/".$user_unique_key."/images/avatar";
                                $this->ci->load->library('api/api_image_lib');
                                $imgdata = $this->ci->api_image_lib->save_img_to_local($wb_avatar, $relative_path);
                                //将图片相对地址写入到用户头像字段中
                                if ($imgdata) {
                                    $relative_img = preg_replace('/^\/+/', '', $imgdata['relative_name']);
                                    $this->ci->my_lib->update_records('users', array('avatar_url' => $relative_img), array('guid' => $user_guid));
                                }
                            }
                            //创建绑定关系
                            $binding_data = array(
                                'third_uid' => $wb_uid,
                                'third_username' => $wb_name,
                                'third_access_token' => $wb_at,
                                'third_platform' => $type,
                                'guid' => $user_guid,
                                'time_created' => time(),
                                'expired_in' => $wb_expired,
                                'third_refresh_token' => $wb_rt,
                                'third_openid' => $wb_openid,
                            );
                            $this->ci->my_lib->create_a_record('third_party_bindings', $binding_data);
                            $tokendata = $this->generate_user_authcode($user_guid);
                            //发送欢迎邮件
                            break;
                    }
                }
                break;
        }
        return $tokendata;
    }


    /**
     * 第三方账号注册和登陆，授权登陆注册 新用户直接登陆，不需要补充额外资料  (为了保证access_token不会过期，还需要额外写一个crontab, 用来主动自动更新即将过期的access_token)
     * @param  string  $type              第三方平台标识
     * @param  string  $third_data        第三方平台数据
     * @return array
     */
    public function handle_media_bindings_simple($type = null, $third_data)
    {
        
        /**
         * third_data参数说明：
         * 
         * third_uid : 第三方账号用户ID,需要注意的是微信请使用union_id,而不是open_id, 必传
         * third_access_token : 第三方账号授权token, 必传
         * third_name: 第三方账号用户名称， 必传
         * expired_in: token过期时间，10位秒级时间戳 必传
         * 
         * 
         * third_avatar: 第三方账号头像url, 非必传
         * third_openind: 微信特有的open_id, 非必传， 微信账号授权则必传
         * third_refresh_token: 非必传
         */


        $wb_uid     = isset($third_data['third_uid']) ? $third_data['third_uid'] : null;
        $wb_at      = isset($third_data['third_access_token']) ? $third_data['third_access_token'] : null;
        $wb_avatar  = isset($third_data['third_avatar']) ? $third_data['third_avatar'] : null;
        $wb_name    = isset($third_data['third_name']) ? $third_data['third_name'] : null;
        $wb_openid  = isset($third_data['third_openid']) ? $third_data['third_openid'] : null;
        $wb_rt      = isset($third_data['third_refresh_token']) ? $third_data['third_refresh_token'] : null;
        $wb_expired = isset($third_data['expired_in']) ? $third_data['expired_in'] : null;

        //第三方账号是否已经绑定
        $user_guid = $this->ci->my_lib->get_a_value('third_party_bindings', 'guid', array('third_uid' => $wb_uid, 'third_platform' => $type));

        switch ($user_guid) {
            //第三方账号已绑定过
            case true:
                $tokendata = $this->generate_user_authcode($user_guid);
                //被动更新用户过期的access_token
                $update_data = array(
                    'third_access_token' => $wb_at,
                    'expired_in' => $wb_expired,
                );
                $this->ci->my_lib->update_records('third_party_bindings', $update_data, array('third_uid' => $wb_uid, 'third_platform' => $type));
                break;
            //第三方账号未绑定
            case false:
                //注册
                $user_guid = $this->ci->user_lib->create_a_user($wb_name);
                if (!$user_guid) {
                    throw new ApiException('auth failed',"授权失败，请重试");
                }
                if ($wb_avatar) {
                    $user_unique_key = $this->ci->my_lib->get_a_value('users', 'unique_key', array('guid' => $user_guid));
                    $relative_path = "upload/".$user_unique_key."/images/avatar";
                    $this->ci->load->library('api/api_image_lib');
                    $imgdata = $this->ci->api_image_lib->save_img_to_local($wb_avatar, $relative_path);
                    //将图片相对地址写入到用户头像字段中
                    if ($imgdata) {
                        $relative_img = preg_replace('/^\/+/', '', $imgdata['relative_name']);
                        $this->ci->my_lib->update_records('users', array('avatar_url' => $relative_img), array('guid' => $user_guid));
                    }
                }
                //创建绑定关系
                $binding_data = array(
                    'third_uid' => $wb_uid,
                    'third_username' => $wb_name,
                    'third_access_token' => $wb_at,
                    'third_platform' => $type,
                    'guid' => $user_guid,
                    'time_created' => time(),
                    'expired_in' => $wb_expired,
                    'third_refresh_token' => $wb_rt,
                    'third_openid' => $wb_openid,
                );
                $this->ci->my_lib->create_a_record('third_party_bindings', $binding_data);
                $tokendata = $this->generate_user_authcode($user_guid);
                //发送欢迎邮件
                break;
        }
        return $tokendata;
    }
}
