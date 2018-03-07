<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 网易微博 Provider 
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_163 extends OAuth2_Provider
{
	public $name = '163';

	public $human = '网易微博';

	public $uid_key = 'user_id';

	public $method = 'POST';
 
	public function url_authorize()
	{
		return 'https://api.t.163.com/oauth2/authorize';
	}

	public function url_access_token()
	{
		return 'https://api.t.163.com/oauth2/access_token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{

		$url = 'https://api.t.163.com/users/show.json?'.http_build_query(array(
			'access_token' => $token->access_token,
            'user_id' => $token->uid
		));
		$user = json_decode(file_get_contents($url));

		if (array_key_exists("error", $user))
		{
			throw new OAuth2_Exception((array) $user);
		}

		// Create a response from the request
		return array(
			'via' => '163',
			'uid' => $user->id,
			'screen_name' => $user->screen_name,
			'name' => $user->name,
			'location' => '',
			'description' => $user->description,
			'image' => $user->profile_image_url,
			'access_token' => $token->access_token,
			'expire_at' => $token->expires,
			'refresh_token' => $token->refresh_token
		);
	}
}
