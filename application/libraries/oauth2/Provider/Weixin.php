<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
  * Oauth2 SocialAuth for CodeIgniter
  * 微信 Provider
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_Weixin extends Oauth2_Provider {

    const API_URL = 'https://api.weixin.qq.com/';

    public $name = 'weixin';

    public $human = '微信';

    public $uid_key = 'openid';

    public $client_id_key = 'appid';

    public $client_secret_key = 'secret';

    protected $scope = 'snsapi_userinfo';

    public $method = 'POST';
    
    public function scope_min()
    {
         $this->scope = 'snsapi_base';
    }
    public function url_authorize()
    {
        return 'https://open.weixin.qq.com/connect/oauth2/authorize';
    }

    public function url_access_token()
    {
        return 'https://api.weixin.qq.com/sns/oauth2/access_token';
    }

    public function get_user_info(OAuth2_Token_Access $token)
    {
        $url = static::API_URL . 'sns/userinfo?'.http_build_query(array(
                'access_token' => $token->access_token,
                'openid' => $token->uid,
                'lang' => 'zh_CN'
            ));
        $user = json_decode(file_get_contents($url));
        if (array_key_exists("errcode", $user)) {
            throw new OAuth2_Exception((array) $user);
        }
        return array(
            'via' => 'wechat',
            'uid' => $user->unionid,
            'openid' => $user->openid,
            'screen_name' => $user->nickname,
            'name' => $user->nickname,
            'location' => $user->province,
            'description' => '',
            'image' => $user->headimgurl,
            'access_token' => $token->access_token,
            'expire_at' => $token->expires,
            'refresh_token' => $token->refresh_token
        );
    }
}
