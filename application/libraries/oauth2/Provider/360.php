<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 360 Provider 
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_360 extends OAuth2_Provider
{
	public $name = '360';

	public $human = '360';

	public $uid_key = 'id';

	public $method = 'POST';
 
	public function url_authorize()
	{
		return 'https://openapi.360.cn/oauth2/authorize';
	}

	public function url_access_token()
	{
		return 'https://openapi.360.cn/oauth2/access_token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{

		$url = 'https://openapi.360.cn/user/me?'.http_build_query(array(
			'access_token' => $token->access_token
		));
		$user = json_decode(file_get_contents($url));

		if (array_key_exists("error", $user))
		{
			throw new OAuth2_Exception((array) $user);
		}

		// Create a response from the request
		return array(
			'via' => '360',
			'uid' => $user->id,
			'screen_name' => $user->name,
			'name' => '',
			'location' => '',
			'description' => '',
			'image' => $user->avatar,
			'access_token' => $token->access_token,
			'expire_at' => $token->expires,
			'refresh_token' => $token->refresh_token
		);
	}
}
