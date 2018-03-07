<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 人人 Provider
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_Renren extends OAuth2_Provider
{
    public $name = 'renren';

    public $human = '人人'; 
        
    public $method = 'POST';

    public function url_authorize()
    {
    	return 'https://graph.renren.com/oauth/authorize';
    }

    public function url_access_token()
    {
    	return 'https://graph.renren.com/oauth/token';
    }

    public function get_user_info(OAuth2_Token_Access $token)
    {

    	$url = 'https://api.renren.com/restserver.do';
    	$params = array(
    		'access_token' => $token->access_token,
    		'format' => 'JSON',
    		'v' => '1.0',
    		'call_id' => time(),
    		'method' => 'users.getInfo'
    	);
    	$opts = array(
    		'http' => array(
    			'method'  => 'POST',
    			'header'  => 'Content-type: application/x-www-form-urlencoded',
    			'content' => http_build_query($params)
    		)
    	);
    	$_default_opts = stream_context_get_params(stream_context_get_default());
    	$context = stream_context_create(array_merge_recursive($_default_opts['options'], $opts));
    	$user = json_decode(file_get_contents($url, false, $context));
    	
    	if ( ! is_array($user) OR ! isset($user[0]) OR ! ($user = $user[0]) OR array_key_exists("error_code", $user))
    	{
            throw new OAuth2_Exception((array) $user);
    	}
                
                
    	// Create a response from the request
    	return array(
    		'via' => 'renren',
    		'uid' => $user->uid,
    		'screen_name' => $user->name,
    		'name' => '',
    		'location' => '',
    		'description' => '',
    		'image' => $user->tinyurl,
    		'access_token' => $token->access_token,
    		'expire_at' => $token->expires,
    		'refresh_token' => $token->refresh_token
    	);
    }
}
