<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 豆瓣 Provider 
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_Douban extends OAuth2_Provider
{
	public $name = 'douban';

	public $human = '豆瓣';

	public $uid_key = 'douban_user_id';
        
	public $error_key = 'msg';

	public $method = 'POST';
 
	public function url_authorize()
	{
		return 'https://www.douban.com/service/auth2/auth';
	}

	public function url_access_token()
	{
		return 'https://www.douban.com/service/auth2/token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{

		$url = 'https://api.douban.com/v2/user/'.$token->uid.'?'.http_build_query(array(
			'access_token' => $token->access_token
		));
		$user = json_decode(file_get_contents($url));

		if ( ! $user OR array_key_exists('msg', $user))
		{
			throw new OAuth2_Exception((array) $user);
		}

		// Create a response from the request
		return array(
			'via' => 'douban',
			'uid' => $user->id,
			'screen_name' => $user->uid,
			'name' => $user->name,
			'location' => '',
			'description' => '',
			'image' => $user->avatar,
			'access_token' => $token->access_token,
			'expire_at' => $token->expires,
			'refresh_token' => $token->refresh_token
		);
	}
}
