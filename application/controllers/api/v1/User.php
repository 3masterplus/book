<?php defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . '/core/Api_Controller.php';

class User extends Api_Controller
{
    /**
     *
     *  构造函数
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('user_lib');
        $this->load->library('api/api_user_lib');
        $this->load->library('email');
    }

    public function index_get()
    {
        echoErr();
    }

    /**
     * 用户注册
     * @return json
     */
    public function register_post()
    {
        ParamErr('api/user/register_post', 'post', 500);
        $email = api_post('email');
        $password = api_post('password');
        $username = api_post('username');
        $res = $this->user_lib->create_a_user($username, $email, $password);
        if ($res) {
            echoSucc();
        } else {
            echoErr();
        }
    }

    /**
     * 忘记密码,请求发送找回密码功能
     * @return json
     */
    public function forgot_pwd_post()
    {
        ParamErr('api/user/forgot_pwd_post', 'post', 500);
        $email = api_post('email');
        $res = $this->user_lib->request_password_reset($email);
        if ($res) {
            echoSucc('我们刚刚给您发了一封电子邮件。请查收邮件，根据提示，重置密码。');
        } else {
            echoErr('please try again', 'please try again');
        }
    }

    /**
     * 普通邮箱密码登陆
     * @return json
     */
    public function common_login_post()
    {
        ParamErr('api/user/common_login_post', 'post', 500);
        $email = api_post('email');
        $password = api_post('password');
        $res = $this->api_user_lib->login_a_user_api($email, $password);
        if ($res) {
            echoSucc('', $res);
        } else {
            echoErr('please try again', 'please try again');
        }
    }


    /**
     * 用户点击退出按钮退出
     * @return json
     */
    public function logout_delete()
    {
        ParamErr('api/user/logout_delete', 'delete', 500);
        $device_token = api_get('device_token');
        /**
         * 清空客户端的用户关联的device_token记录
         */
        if ($device_token && $this->_guid ) {
            $where = array(
                'user_guid' => $this->_guid,
                'device_token' => $device_token,
                'device' => $this->_device,
            );
            $this->ci->my_lib->delete_records('device_token_relations', $where);
        }
        echoSucc();
    }


    /**
     * 微信授权注册 （已废弃）
     * @return json
     */
    public function wechat_register_post()
    {
        $t = time();
        ParamErr('api/user/wechat_register_post', 'post', 500);
        $access_token = api_post('access_token');
        $openid = api_post('openid');
        $expires_in = api_post('expires_in');
        $refresh_token = api_post('refresh_token');
        $username = api_post('username'); //用户自定义的用户名，用于注册使用
        $email = api_post('email');
        $password = api_post('password');
        //通过客户端传递过来的access_token从新浪服务器获取微博UID
        include_once APPPATH . 'third_party/wechat/auth_login.php';
        $wx = new WeixinOauth(WX_AKEY, WX_SKEY, $access_token, $openid);
        $check = $wx->AuthAccessToken();
        if ($check->errcode != 0) {
            echoErr($check->errmsg, $check->errmsg);
        }
        $wdata = $wx->getuserinfo();
        if (!isset($wdata->unionid)) {
            echoErr('invalid access token', 'wechat access token is invalid');
        }
        $wechat_uid = $wdata->unionid;
        $wechat_avatar = $wdata->headimgurl;
        $wechat_nickname = $wdata->nickname;
        $third_data = array(
            'third_access_token' => $access_token,
            'third_uid' => $wechat_uid,
            'third_email' => $email,
            'expired_in' => $expires_in + $t,
            'username' => $username,
            'password' => $password,
            'avatar' => $wechat_avatar, 
            'third_name' => $wechat_nickname,
            'third_openid' => $openid,
            'third_refresh_token' => $refresh_token,
        );
        $res = $this->api_user_lib->handle_media_bindings('wechat', $third_data, array(), false, false);

        echoSucc('', $res);
    }

    /**
     * 微信授权登陆 (已废弃)
     * @return json
     */
    public function wechat_login_post()
    {
        $t = time();
        ParamErr('api/user/wechat_login_post', 'post', 500);
        $access_token = api_post('access_token');
        $openid = api_post('openid');
        $expires_in = api_post('expires_in');
        $refresh_token = api_post('refresh_token');
        //通过客户端传递过来的access_token从新浪服务器获取微博UID
        include_once APPPATH . 'third_party/wechat/auth_login.php';
        $wx = new WeixinOauth(WX_AKEY, WX_SKEY, $access_token, $openid);
        $check = $wx->AuthAccessToken();
        if ($check->errcode != 0) {
            echoErr($check->errmsg, $check->errmsg);
        }
        $wdata = $wx->getuserinfo();
        if (!isset($wdata->unionid)) {
            echoErr('invalid access token', 'wechat access token is invalid');
        }
        $wechat_uid = $wdata->unionid;
        $wxdata = array(
            'third_access_token' => $access_token,
            'third_uid' => $wechat_uid,
            'expired_in' => $expires_in + $t,
        );
        $res = $this->api_user_lib->handle_media_bindings('wechat', $wxdata, array(), false, true);

        echoSucc('', $res);
    }

    /**
     * 微信登陆（新授权不需要提供额外资料）
     * @return 
     */
    public function wechat_login_simple_post()
    {
        $t = time();
        ParamErr('api/user/wechat_login_post', 'post', 500);
        $access_token = api_post('access_token');
        $openid = api_post('openid');
        $expires_in = api_post('expires_in');
        $refresh_token = api_post('refresh_token');
        //通过客户端传递过来的access_token从新浪服务器获取微博UID
        include_once APPPATH . 'third_party/wechat/auth_login.php';
        $wx = new WeixinOauth(WX_AKEY, WX_SKEY, $access_token, $openid);
        $check = $wx->AuthAccessToken();
        if ($check->errcode != 0) {
            echoErr($check->errmsg, $check->errmsg);
        }
        $wdata = $wx->getuserinfo();
        if (!isset($wdata->unionid)) {
            echoErr('invalid access token', 'wechat access token is invalid');
        }
        $wechat_uid = $wdata->unionid;
        $wechat_avatar = $wdata->headimgurl;
        $wechat_nickname = $wdata->nickname;

        // $wechat_uid = '12121212';
        // $wechat_avatar = 'http://ww3.sinaimg.cn/bmiddle/68147f68gw1ez4u180uwzj20qo0k0dji.jpg';
        // $wechat_nickname = '我是昵称';

        $wxdata = array(
            'third_access_token' => $access_token,
            'third_uid' => $wechat_uid,
            'third_avatar' => $wechat_avatar,
            'third_openid' => $openid,
            'third_refresh_token' => $refresh_token,
            'third_name' => $wechat_nickname,
            'expired_in' => $expires_in + $t,
        );
        
        try {
            $res = $this->api_user_lib->handle_media_bindings_simple('wechat', $wxdata, array() );   
        } catch (Exception $e) {
            echoErr($e->getMessage(), $e->getDescription(), $e->getHttpCode() );
        }
        echoSucc('', $res);   
    }


    /**
     * 微信绑定
     * @return json
     */
    public function wechat_bind_post()
    {
        $t = time();
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        
        ParamErr('api/user/wechat_login_post', 'post', 500);
        $access_token = api_post('access_token');
        $openid = api_post('openid');
        $expires_in = api_post('expires_in');
        $refresh_token = api_post('refresh_token');
        //通过客户端传递过来的access_token从新浪服务器获取微博UID
        include_once APPPATH . 'third_party/wechat/auth_login.php';
        $wx = new WeixinOauth(WX_AKEY, WX_SKEY, $access_token, $openid);
        $check = $wx->AuthAccessToken();
        if ($check->errcode != 0) {
            echoErr($check->errmsg, $check->errmsg);
        }
        $wdata = $wx->getuserinfo();
        if (!isset($wdata->unionid)) {
            echoErr('invalid access token', 'wechat access token is invalid');
        }
        $wechat_uid = $wdata->unionid;
        $wechat_avatar = $wdata->headimgurl;
        $wechat_nickname = $wdata->nickname;

        // $wechat_uid = '12121212';
        // $wechat_avatar = 'http://ww3.sinaimg.cn/bmiddle/68147f68gw1ez4u180uwzj20qo0k0dji.jpg';
        // $wechat_nickname = '我是昵称';

        /**
         * wxdata中的参数说明
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

        $wxdata = array(
            'third_access_token' => $access_token,
            'third_uid' => $wechat_uid,
            'avatar' => $wechat_avatar,
            'third_openid' => $openid,
            'third_refresh_token' => $refresh_token,
            'third_name' => $wechat_nickname,
            'expired_in' => $expires_in + $t,
        );
        
        try {
            $res = $this->api_user_lib->user_binding_media('wechat', $wxdata );   
        } catch (Exception $e) {
            echoErr($e->getMessage(), $e->getDescription(), $e->getHttpCode() );
        }
        echoSucc('', $res);   
    }


    /**
     * 获取用户第三方账号的绑定信息
     * @return json
     */
    public function sns_get()
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        $snsdata = $this->api_user_lib->get_user_social_media();
        echoSucc('',$snsdata);
    }


    /**
     * 解除第三方账号绑定
     * @return json
     */
    public function sns_delete()
    {
        ParamErr('api/user/user_sns_delete', 'get', 500);
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        $type = api_get('third_platform');
        try {
            $ret = $this->api_user_lib->cancel_media_bingding($type);   
        } catch (Exception $e) {
            echoErr($e->getMessage(), $e->getDescription(), $e->getHttpCode());
        }
        echoSucc();
    }




    /**
     * 获取用户信息
     * @return json
     */
    public function info_get($user_unique_key = null)
    {
        //将user_unique_key加入待校验字符数组中
        $_GET['user_unique_key'] = $user_unique_key;

        ParamErr('api/user/user_info_get', 'get', 500);
        $field = api_get('field');
        $field = $field ? explode(',', $field) : array();
        $select_items = "users.username,users.unique_key as user_unique_key,users.user_group_id,users.signature,users.bio,users.avatar_url";
        $select_items = $this->api_user_lib->append_field($select_items, $field);
        $select_items = $this->api_user_lib->append_private_field($select_items, $field);
        $udata = $this->user_lib->get_user_info($user_unique_key, $select_items);
        if (!$udata) {
            echoErr('user no found', 'user no found');
        }

        $udata = array($udata);
        try {
            $udata = $this->api_user_lib->process_user_data($udata, $field);
            $udata = $this->api_user_lib->process_user_private_data($udata, $field);   
        } catch (Exception $e) {
            echoErr($e->getMessage(), $e->getDescription(), $e->getHttpCode());
        }
        echoSucc('', $udata[0]);
    }

    /**
     * 更新用户的个人资料
     * @param  string $user_unique_key
     * @return json
     */
    public function profile_post()
    {
        $update_data = array();
        //need login
        $this->api_base_lib->checkAccesstoken();
        ParamErr('api/user/profile_post', 'post', 500);
        $bio = api_post('bio');
        $signature = api_post('signature');
        $username = api_post('username');
        if ($bio) {
            $update_data['bio'] = $bio;
        }
        if ($signature) {
            $update_data['signature'] = $signature;
        }
        if ($username) {
            $update_data['username'] = $username;   
        }
        if ($update_data) {
            $this->my_lib->update_records('users', $update_data, array('guid' => $this->_guid));
            echoSucc();
        } else {
            echoErr('nothing changed', 'nothing changed');
        }
    }

    /**
     * 修改用户头像
     * @return json
     */
    public function avatar_post()
    {
        /**
         * 合法的图片格式
         * @var array
         */
        $ok_mime = array('image/jpeg', 'image/png');
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        if (!isset($_FILES['avatar'])) {
            echoErr('avatar is empty', 'avatar is empty');
        }
        $file_mime = $_FILES['avatar']['type'];
        if (!in_array($file_mime, $ok_mime)) {
            echoErr('unsupport image type', 'unsupport image type');
        }
        $file_info = getimagesize($_FILES['avatar']['tmp_name']);
        if ($_FILES['avatar']['error'] > 0 || $file_info == false) {
            echoErr('avatar uploaded error', 'avatar uploaded error');
        }
        $file_ext = str_ireplace('image/', '', $file_mime);
        //获取用户信息
        $udata = $this->my_lib->get_records('users', '*', array('guid' => $this->_guid), 1, 0);
        $udata = $udata[0];
        $avatar_path = $udata['avatar_url'] ? IMGPATH . $udata['avatar_url'] : null;
        //删除作者原来的裁剪头像
        // if ($avatar_path){
        //     $avatar_thumbs_prefix = IMGPATH.THUMB_IMG_PATH.urlencode(sha1($avatar_path)).'_';
        //     $files = glob($avatar_thumbs_prefix.'*');
        //     if (!empty($files)){
        //         foreach ($files as $thumb){
        //             @unlink($thumb);
        //         }
        //     }
        //     @unlink($avatar_path);
        // }
        $file_name = $this->_guid . '_' . time() . '.' . $file_ext;
        $relative_path = 'upload/' . $udata['unique_key'] . '/images/avatar/';
        $path = IMGPATH . $relative_path . $file_name;
        $url = IMGHOST . $relative_path . $file_name;
        //创建保存地址
        if (!is_dir(IMGPATH . $relative_path)) {
            $tmp_dir = IMGPATH . $relative_path;
            if (!@mkdir($tmp_dir, 0755, true)) {
                echoErr('permission deny', "{$tmp_dir} not writable");
            }
        }
        $res = move_uploaded_file($_FILES["avatar"]["tmp_name"], $path);
        if (!$res) {
            echoErr('permissions deny', "{$path} not writable");
        }
        //将用户的头像信息，更新到数据库
        $this->my_lib->update_records('users', array('avatar_url' => $relative_path), array('guid' => $this->_guid));
        $data = array(
            'url' => $url,
        );
        echoSucc('', $data);
    }

    /**
     * 修改账号信息 account, password
     * @return json
     */
    public function account_post()
    {
        $change_data = array();
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        
        //待修改的数据
        $new_password = api_post('new_password');
        $email = api_post('email');

        /**
         * 修改密码
         */
        if ($new_password) {
            ParamErr('api/user/account_post_01', 'post', 500);
            try {
                $this->api_user_lib->reset_password($this->_user_unique_key, $new_password);   
            } catch (Exception $e) {
                echoErr($e->getMessage(), $e->getDescription(), $e->getHttpCode() );
            }
        }
        /**
         * 修改邮箱
         */
        if ($email){
            ParamErr('api/user/account_post', 'post', 500);
            $this->user_lib->update_email($this->_guid, $email);
        }
        echoSucc();
    }
}
