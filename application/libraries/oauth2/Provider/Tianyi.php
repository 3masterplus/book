<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Oauth2 SocialAuth for CodeIgniter
  * 天翼 Provider
  * 
  * @author     chekun <234267695@qq.com>
  */
 
class OAuth2_Provider_Tianyi extends OAuth2_Provider
{
	public $name = 'tianyi';

	public $human = '天翼';

	public $uid_key = 'p_user_id';
        
    public $client_id_key = 'app_id';

    public $client_secret_key = 'app_secret';
    
    public $error_key = 'res_code';
        
	public $method = 'POST';
 
	public function url_authorize()
	{
		return 'https://oauth.api.189.cn/emp/oauth2/authorize';
	}

	public function url_access_token()
	{
		return 'https://oauth.api.189.cn/emp/oauth2/access_token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{

		$url = 'http://api.189.cn/upc/vitual_identity/user_network_info?type=json&'.http_build_query(array(
			'access_token' => $token->access_token,
            'app_id' => $this->client_id
		));
		$user_network_info = json_decode(file_get_contents($url));

		if (array_key_exists("error", $user_network_info))
		{
			throw new OAuth2_Exception((array) $user_network_info);
		}
                
		// Create a response from the request
		return array(
			'via' => 'tianyi',
			'uid' => $token->uid,
			'screen_name' => $user_network_info->user_nickname,
			'name' => '',
			'location' => '',
			'description' => $user_network_info->user_selfdesc,
			'image' => '',
			'access_token' => $token->access_token,
			'expire_at' => $token->expires,
			'refresh_token' => $token->refresh_token 
		);
	}
}
