<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 移动微博 Provider 
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_10086 extends OAuth2_Provider
{
    public $name = '10086';

    public $human = '移动微博';
        
    public $client_id_key = 'api_key';

    public $client_secret_key = 'api_secret';
        
    public $error_key = 'error_code';
        
    public $access_token_key = 'session_key';
        
    public $method = 'POST';

    public function url_authorize()
    {
        return 'http://oapi.weibo.10086.cn/oauth/authorize.php';
    }

    public function url_access_token()
    {
        //oauth1.0强行转换成2.0的后果
        $this->params['namespace'] = 'mig';
        $this->params['random'] = substr(md5(time()), 0, 16);
        $this->params['mig'] = md5($_GET['code'].substr($this->params['random'], -1, 12).$this->client_secret.$this->params['namespace']);
        return 'http:// oapi.weibo.10086.cn/oauth/token.php';
    }

    public function get_user_info(OAuth2_Token_Access $token)
    {  	
        $call_id = time();
        $mi_sig = md5('api_key'.$this->client_id.'call_id'.$call_id.'session_key'.$token->access_token.'v2.0'.$this->client_secret);

        $url = 'http://oapi.weibo.10086.cn/users/getloggedinuser.json?'.http_build_query(array(
            'session_key' => $token->access_token,
            'api_key' => $this->client_id,
            'v' => '2.0',
            'call_id' => $call_id,
            'mi_sig' => $mi_sig
        ));
        $user = json_decode(file_get_contents($url));
                
        if ($user->error_code > 0)
        {
            throw new OAuth2_Exception((array) $user);
        }

        $uid = $user->uid;

        $url = 'http://oapi.weibo.10086.cn/users/getinfo.json?'.http_build_query(array(
            'session_key' => $token->access_token,
            'api_key' => $this->client_id,
            'v' => '2.0',
            'call_id' => $call_id,
            'mi_sig' => $mi_sig
        ));
        $user = json_decode(file_get_contents($url));

        if ($user->error_code > 0)
        {
            throw new OAuth2_Exception((array) $user);
        }
                
        // Create a response from the request
        return array(
            'via' => '10086',
            'uid' => $uid,
            'screen_name' => $user->screen_name,
            'name' => $user->username,
            'location' => '',
            'description' => '',
            'image' => $user->tinyurl,
            'access_token' => $token->access_token,
            'expire_at' => $token->expires,
            'refresh_token' => $token->refresh_token
        );
    }
}
