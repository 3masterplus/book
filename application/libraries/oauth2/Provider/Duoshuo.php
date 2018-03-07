<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 多说 Provider 
  * 
  * @author     chekun <234267695@qq.com>
  */

class OAuth2_Provider_Duoshuo extends OAuth2_Provider
{
	public $name = 'duoshuo';

	public $human = '多说';

	public $uid_key = 'user_id';

	public $method = 'POST';
 
	public function url_authorize()
	{
		throw new OAuth2_Exception(array('code' => '403', 'message' => '亲，多说的授权不是从这里进来的.'));
	}

	public function url_access_token()
	{
		return 'http://api.duoshuo.com/oauth2/access_token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{
		return array(
			'via' => 'duoshuo',
			'uid' => $token->uid,
			'screen_name' => $token->uid,
			'name' => '',
			'location' => '',
			'description' => '',
			'image' => '',
			'access_token' => $token->access_token,
			'expire_at' => 0,
			'refresh_token' => ''
		);
	}
}
