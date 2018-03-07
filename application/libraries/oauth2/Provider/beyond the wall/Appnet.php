<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * App.net OAuth2 Provider
 *
 * @package    CodeIgniter/OAuth2
 * @category   Provider
 * @author     Brennan Novak
 */
 
class OAuth2_Provider_Appnet extends OAuth2_Provider {

	/** 
	 * @array scope items for App.net
	 */ 
	protected $scope = array('stream','email','write_post','follow','messages','export');
	
	public $name = 'appnet';

	/**
	 * @var  string  scope separator, most use "," but some like Google are spaces
	 */
	public $scope_seperator = ',';

	/**
	 * @var  string  the method to use when requesting tokens
	 */

	public function url_authorize()
	{
		return 'https://alpha.app.net/oauth/authenticate';
	}

	public function url_access_token()
	{
		return 'https://alpha.app.net/oauth/access_token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{	
		$url = 'https://alpha-api.app.net/stream/0/users/me?'.http_build_query(array(
			'access_token' => $token->access_token,
		));

		$user = json_decode(file_get_contents($url));

		// Create a response from the request
		return array(
			'uid' => $user->id,
			'nickname' => $user->username,
			'name' => $user->name
		);

	}

}