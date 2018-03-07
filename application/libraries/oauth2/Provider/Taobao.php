<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 淘宝 Provider
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_Taobao extends OAuth2_Provider
{
	public $name = 'taobao';

	public $human = '淘宝';

	public $uid_key = 'taobao_user_id';
        
	public $method = 'POST';
 
	public function url_authorize()
	{
		return 'https://oauth.taobao.com/authorize';
	}

	public function url_access_token()
	{
		return 'https://oauth.taobao.com/token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{

		$url = 'https://eco.taobao.com/router/rest?'.http_build_query(array(
			'access_token' => $token->access_token,
            'method' => 'taobao.user.get',
			'v' => '2.0',
            'format' => 'json',
            'fields' => 'user_id,uid,nick,location,avatar',
		));
		$user = json_decode(file_get_contents($url));

      	if (array_key_exists('error_response', $user))
        {
        	throw new OAuth2_Exception((array) $user);
        }

		// Create a response from the request
		return array(
            'via' => 'taobao',
			'uid' => $user->user_get_response->user->user_id,
			'screen_name' => $user->user_get_response->user->nick,
			'name' => $user->user_get_response->user->uid,
			'location' => $user->user_get_response->user->location,
			'description' => '',
            'image' => $user->user_get_response->user->avatar,
			'access_token' => $token->access_token,
			'expire_at' => $token->expires,
			'refresh_token' => $token->refresh_token
		);
	}
}
