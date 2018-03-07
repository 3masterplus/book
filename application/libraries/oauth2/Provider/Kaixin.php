<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 开心网 Provider
  *  
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_Kaixin extends OAuth2_Provider
{
	public $name = 'kaixin';

	public $human = '开心网';

	public $method = 'POST';
 
	public function url_authorize()
	{
		return 'http://api.kaixin001.com/oauth2/authorize';
	}

	public function url_access_token()
	{
		return 'https://api.kaixin001.com/oauth2/access_token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{

		$url = 'https://api.kaixin001.com/users/me.json?'.http_build_query(array(
			'access_token' => $token->access_token
		));
		$user = json_decode(file_get_contents($url));

		if (array_key_exists("error", $user))
		{
			throw new OAuth2_Exception((array) $user);
		}

		return array(
			'via' => 'kaixin',
			'uid' => $user->uid,
			'screen_name' => $user->name,
			'name' => '',
			'location' => '',
			'description' => '',
			'image' => $user->logo50,
			'access_token' => $token->access_token,
			'expire_at' => $token->expires,
			'refresh_token' => $token->refresh_token
		);
	}
}
