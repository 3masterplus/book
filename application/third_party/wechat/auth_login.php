<?php
/**
 * Codeignter for Weixin (using OAuth2)
 * 
 * @author Jo Hubro <ir.xiaoyan@gmail.com>
 */


/**
 * 
 * 微信授权登陆
 *
 */
class WeixinOauth {

	/**
	 * 获取code的微信host
	 * @var string
	 */
	private $code_host = 'https://open.weixin.qq.com/connect/qrconnect';

	/**
	 * 通过code换取access token的微信API host
	 * @var string
	 */
	private $api_host = 'https://api.weixin.qq.com';

	/**
	 * 微信应用ID
	 * @var string
	 */
	private $AppID;

	/**
	 * 微信应用Secret
	 * @var string
	 */
	private $AppSecret;

	/**
	 * 用户代理信息
	 * @var string
	 */
	private $useragent = 'Weixin OAuth2 v0.1';

	/**
	 * curl连接超时时长
	 * @var integer
	 */
	private $connecttimeout = 30;

	/**
	 * curl请求时长
	 * @var integer
	 */
	private $timeout = 30;

	/**
	 * 是否需要ssl校验
	 * @var boolean
	 */
	private $ssl_verifypeer = FALSE;

	/**
	 * http info
	 * @var [type]
	 */
	private $http_info;

	/**
	 * 是否开启调试模式
	 * @var boolean
	 */
	private $debug = false;


	/**
	 * 构造函数
	 */
	function __construct($AppID, $AppSecret, $access_token = NULL, $openid = NULL, $refresh_token = NULL)
	{
		$this->AppID         = $AppID;
		$this->AppSecret     = $AppSecret;
		$this->access_token  = $access_token;
		$this->openid        = $openid;
		$this->refresh_token = $refresh_token;
	}

	/**
	 * 生成获取code的链接
	 * @param  $redirect_uri [description]
	 * @param  [type] $state        [description]
	 * @return [type]               [description]
	 */
	function get_auth_code($redirect_uri, $state = NULL)
	{	
		$param = array(
			'appid'         => $this->AppID,
			'redirect_uri'  => $redirect_uri,
			'response_type' => 'code',
			'scope'         => 'snsapi_login',
		);
		if ($state) $param['state'] = $state;
		return $this->code_host.'?'.http_build_query($param).'#wechat_redirect';
	}


	/**
	 * 通过code获取access_token
	 * @param  string $code
	 * @return string
	 */
	function getAccessToken($code = NULL)
	{	
		$url = $this->api_host.'/sns/oauth2/access_token';

		$param = array(
			'appid'      => $this->AppID,
			'secret'     => $this->AppSecret,
			'code'       => $code,
			'grant_type' => 'authorization_code',
		);

		return $this->oAuthRequest($url, 'GET', $param);
	}

	/**
	 * 刷新access_token
	 * @return string
	 */
	function refreshAccessToken($refresh_access_token = NULL)
	{
		$url = $this->api_host.'/sns/oauth2/refresh_token';

		$param = array(
			'appid'         => $this->AppID,
			'secret'        => $this->AppSecret,
			'refresh_token' => $refresh_access_token ? refresh_access_token : $this->refresh_token,
			'grant_type'    => 'refresh_token',
		);

		return $this->oAuthRequest($url, 'GET', $param);
	}


	/**
	 * 检测acces_token有效期
	 * @return string
	 */
	function AuthAccessToken()
	{
		$url = $this->api_host.'/sns/auth';
		$param = array(
			'access_token' => $this->access_token,
			'openid'       => $this->openid,
		);
		return $this->oAuthRequest($url, 'GET', $param);
	}


	/**
	 * 获取用户基本信息
	 * @return string
	 */
	function getuserinfo()
	{
		$url = $this->api_host.'/sns/userinfo';
		$param = array(
			'access_token' => $this->access_token,
			'openid'       => $this->openid,
		);
		return $this->oAuthRequest($url, 'GET', $param);	
	}


	/**
	 * Format and sign an OAuth / API request
	 *
	 * @return string
	 * @ignore
	 */
	function oAuthRequest($url, $method, $parameters, $multi = false) 
	{
		switch ($method) 
		{
			case 'GET':
				$url = $url . '?' . http_build_query($parameters);
				return json_decode($this->http($url, 'GET'));
			default:
				$headers = array();
				if (!$multi && (is_array($parameters) || is_object($parameters)) ) {
					$body = http_build_query($parameters);
				} else {
					$body = self::build_http_query_multi($parameters);
					$headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
				}
				return json_decode($this->http($url, $method, $body, $headers));
		}
	}

	/**
	 * Make an HTTP request
	 *
	 * @return string API results
	 */
	function http($url, $method, $postfields = NULL, $headers = array()) 
	{
		$this->http_info = array();
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		if (version_compare(phpversion(), '5.4.0', '<')) {
			curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 1);
		} else {
			curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		}
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
					$this->postdata = $postfields;
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
		}

		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;

		if ($this->debug) {
			echo "=====post data======\r\n";
			var_dump($postfields);

			echo "=====headers======\r\n";
			print_r($headers);

			echo '=====request info====='."\r\n";
			print_r( curl_getinfo($ci) );

			echo '=====response====='."\r\n";
			print_r( $response );
		}
		curl_close ($ci);
		return $response;
	}

	/**
	 * Get the header info to store.
	 *
	 * @return int
	 */
	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}

	
	/**
	 * 构造表单提交
	 * @param  array $params
	 * @return string
	 */
	public static function build_http_query_multi($params) 
	{
		if (!$params) return '';

		uksort($params, 'strcmp');

		$pairs = array();

		self::$boundary = $boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

		foreach ($params as $parameter => $value) {

			if( in_array($parameter, array('pic', 'image')) && $value{0} == '@' ) {
				$url = ltrim( $value, '@' );
				$content = file_get_contents( $url );
				$array = explode( '?', basename( $url ) );
				$filename = $array[0];

				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
				$multipartbody .= "Content-Type: image/unknown\r\n\r\n";
				$multipartbody .= $content. "\r\n";
			} else {
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
				$multipartbody .= $value."\r\n";
			}

		}
		$multipartbody .= $endMPboundary;
		return $multipartbody;
	}

}
