<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 搜狐微博 Provider
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_Sohu extends OAuth2_Provider
{
	public $name = 'sohu';

	public $human = '搜狐微博';

	public $uid_key = 'id';
        
    public $state_key = 'wrap_client_state'; 

	public $method = 'POST';
        
    public function __construct(array $options = array())
	{
		empty($options['scope']) and $options['scope'] = 'basic';
		$options['scope'] = (array) $options['scope'];
		parent::__construct($options);
	}
 
	public function url_authorize()
	{
		return 'https://api.t.sohu.com/oauth2/authorize';
	}

	public function url_access_token()
	{
		return 'https://api.t.sohu.com/oauth2/access_token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{

		$url = 'http://api.t.sohu.com/users/show/id.json?'.http_build_query(array(
			'access_token' => $token->access_token
		));
		$user = json_decode(file_get_contents($url));

		if (array_key_exists("error", $user))
		{
			throw new OAuth2_Exception((array) $user);
		}

		// Create a response from the request
		return array(
			'via' => 'sohu',
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
