<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 百度 Provider 
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_Baidu extends OAuth2_Provider
{
	public $name = 'baidu';

	public $human = '百度';

	public $uid_key = 'uid';

	public $method = 'POST';
 
	public function url_authorize()
	{
		return 'https://openapi.baidu.com/oauth/2.0/authorize';
	}

	public function url_access_token()
	{
		return 'https://openapi.baidu.com/oauth/2.0/token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{

		$url = 'https://openapi.baidu.com/rest/2.0/passport/users/getLoggedInUser?'.http_build_query(array(
			'access_token' => $token->access_token,
			'uid' => $token->uid,
		));
		$user = json_decode(file_get_contents($url));

		if (array_key_exists("error", $user))
		{
			throw new OAuth2_Exception((array) $user);
		}

		// Create a response from the request
		return array(
			'via' => 'baidu',
			'uid' => $user->uid,
			'screen_name' => $user->uname,
			'name' => '',
			'location' => '',
			'description' => '',
			'image' => 'http://tb.himg.baidu.com/sys/portraitn/item/'.$user->portrait,
			'access_token' => $token->access_token,
			'expire_at' => $token->expires,
			'refresh_token' => $token->refresh_token
		);
	}
}
