<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 腾讯微博 Provider
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_Tweibo extends OAuth2_Provider
{
	public $name = 'tweibo';

	public $human = '腾讯微博';

	public $uid_key = 'openid';

	public $method = 'POST';
 
	public function url_authorize()
	{
		return 'https://open.t.qq.com/cgi-bin/oauth2/authorize';
	}

	public function url_access_token()
	{
		return 'https://open.t.qq.com/cgi-bin/oauth2/access_token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{

		$url = 'https://open.t.qq.com/api/user/info?'.http_build_query(array(
			'access_token' => $token->access_token,
            'oauth_consumer_key' => $this->client_id,
			'openid' => $token->uid,
            'clientip' => get_instance()->input->ip_address(),
            'oauth_version' => '2.a'
		));
		$user = json_decode(file_get_contents($url));

      	if ($user->ret)
        {
        	throw new OAuth2_Exception((array) $user);
        }

		// Create a response from the request
		return array(
            'via' => 'tweibo',
            'uid' => $user->data->openid,
            'screen_name' => $user->data->nick,
            'name' => $user->data->name,
            'location' => '',
            'description' => $user->data->introduction,
            'image' => $user->data->head.'/100',
            'access_token' => $token->access_token,
            'expire_at' => $token->expires,
            'refresh_token' => $token->refresh_token
		);
	}
}
