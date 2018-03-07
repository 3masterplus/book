<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 飞信 Provider
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_Feixin extends OAuth2_Provider
{
	public $name = 'feixin';

	public $human = '飞信';

	public $uid_key = 'userId';

	public $method = 'POST';
 
	public function url_authorize()
	{
		return 'https://i.feixin.10086.cn/oauth2/authorize';
	}

	public function url_access_token()
	{
		return 'https://i.feixin.10086.cn/oauth2/access_token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{

		$url = 'GET https://i.feixin.10086.cn/api/user.json?'.http_build_query(array(
			'access_token' => $token->access_token
		));
		$user = json_decode(file_get_contents($url));

      	if (array_key_exists("error", $user))
        {
        	throw new OAuth2_Exception((array) $user);
        }

		// Create a response from the request
		return array(
            'via' => 'feixin',
			'uid' => $user->userId,
			'screen_name' => $user->nickname,
			'name' => '',
			'location' => '',
			'description' => $user->introducation,
			'image' => $user->portraitTiny,
			'access_token' => $token->access_token,
			'expire_at' => $token->expires,
			'refresh_token' => $token->refresh_token
		);
	}
}
