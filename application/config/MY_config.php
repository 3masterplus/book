<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

	//系统基本信息
	$config['site_name'] 							= '知乐';
	$config['delimiter'] 							= '|';
	$config['openmark']								= '试';
	
	
	//菜单翻译
	$config['home']									= '首页';
	$config['dashboard']							= '控制台';
	$config['all_courses']							= '课程';
	$config['setting']								= '设置';
	$config['account']								= '我的';
	$config['notification']							= '通知';

	//系统时间显示格式
	$config['std_date_format'] 						= 'Y-m-d H:i';

	//系统分页设置
	$config['per_page']								= 10;

	//系统Cookie设置
	$config['user_identifier_cookie'] 				= 'user_identifier';
	$config['user_identifier_cookie_expiration'] 	= 60 * 60 * 24 * 30 * 12;
	
	$config['autologin_cookie'] 					= 'autologin';
	$config['autologin_cookie_expiration'] 			= 60 * 60 * 24 * 31;
	
	//系统登录设置
	$config['max_login_attempts']					= 5;

	//允许上传类型
	$config['upload_type'] 							= 'jpg|jpeg|png|gif|pdf|doc|docx|txt|mp3';
	
	//电子邮件相关设置
	$config['email_verificaiton_expiration'] 		= 60*60*24*3;
	$config['password_reset_link_expiration'] 		= 60*60*24*3;
	$config['email_no_reply_address'] 				= 'no_reply@zhiler.com';
	
	//课程状态的中文翻译
	$config['draft']								= '制作中';
	$config['published']							= '发布中';
	$config['pending']								= '审核中';
	$config['rejected']								= '被拒绝';
	$config['deleted']								= '准备被删除';
	
	//图片设置
	$config['upload_base_path'] 					= 'upload/';
	
	$config['image_allowed_types'] 					= 'gif|jpg|jpeg|png';
	$config['image_max_size']						= 2000;
	$config['image_max_width']						= 1500;
	$config['image_max_height']						= 3000;
	
	//上次文件设置
	$config['file_upload_base_path'] 				= 'upload/files/';
	$config['file_allowed_types'] 					= 'doc|pdf|mp3|txt';
	$config['file_max_size']						= 10000;
	
	//默认的用户头像大小
	$config['default_avatar_width']					= 200;
	$config['default_avatar_height']				= 200;
	$config['default_avatar_url']					= 'upload/public/avatar/default_avatar_200_200.jpg';
	
	$config['BY_COURSE']							= '持续更新中';
	$config['BY_SECTION']							= '全部内容发布完毕';
	
	
	/**
	 * api中一些配置信息
	 */
		
	//客户端校验过程中时间戳AES加密key
	$config['client_unixtimescamp_aes_keys'] = array(
		'iphone'  => 'PAX4LlMyO4d8opkWFKOUyA9nlaiwD06S',
		'android' => 'b32bba2d1000ed3f51801eddf3430ea5',
		'pc'      => 'MnghSo7Ezy7StduymvLkib9LcolFXwIk',
	);

	//客户端校验过程中token私钥
	$config['client_private_keys'] = array(
		'iphone'  => '0983Fkamaada1324ks',
		'android' => '73842Km2K13D238239',
		'pc'      => 'qfFkdSab4ZDsW5XY8V'
	);
	
	//微信认证之后的回调地址
	$config['wechat_redirect_url'] = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx64b8083d5c121170&redirect_uri=http%3A%2F%2Fzhiler.com%2Fuser%2Fwechat_login&response_type=code&scope=snsapi_userinfo&state=1234#wechat_redirect";
	
	//使用composer，写在此处的原因是避免变更系统文件或系统配置
	require_once FCPATH.'vendor/autoload.php';
