<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class User_lib extends my_lib{
	
		var $_max_login_attempts;
		var $_default_avatar_url;
		var $_email_verificaiton_expiration;
		var $_auto_login_cookie;
		var $_auto_login_cookie_expiration;
		var $_password_reset_link_expiration;
		var $_site_name;
		var $_email_no_reply_address;
		
		function __construct(){	
			parent::__construct();
			$this->ci = & get_instance();
			
			$this->_max_login_allowence 			= config('max_login_attempts');
			$this->_default_avatar_url 				= config('default_avatar_url');
			$this->_email_verification_expiration 	= config('email_verificaiton_expiration');
			$this->_autologin_cookie 				= config('autologin_cookie');
			$this->_autologin_cookie_expiration 	= config('autologin_cookie_expiration');
			$this->_password_reset_link_expiration	= config('password_reset_link_expiration');
			$this->_site_name						= config('site_name');
			$this->_email_no_reply_address			= config('email_no_reply_address');
		}
		
		// ********************************************************
		// 用户注册相关的方法
		// ********************************************************
		
		// 创建新用户
		function create_a_user($username, $email = '', $password = ''){
		
			$condition = array('unique_key' => $this->_user_identifier);
			
			/*
				如果当前用户在创建账号的时候，系统给她分配的“user_unique_key”在系统中不存在，那用户创建
				账号的“user_unique_key”将使用系统给她所分配的这个“key”，否则的话，系统会给她重新生成一个“key”，
				并把这个“key”写入到“session”和“cookie”中
			*/
			
			if(!$this->check_a_record('users', $condition)){
				$user_unique_key = $this->_user_identifier;
			} else {
				if($email != '' AND $password != ''){
					$rand_str = $email.$password.$username;
				} else {
					$rand_str = $username.rand_str();
				}
				
				$user_unique_key = $this->generate_entity_unique_key($rand_str);
				$this->set_user_identifier($user_unique_key);
			}
			
			//开始数据库存储事件
			$this->ci->db->trans_start();
				
			//创建“entity”记录
			$guid = $this->create_an_entity(0, 'user', 0, TRUE, $user_unique_key, $username, '');
			
			$salt = '';
			
			//如果用户注册有邮箱和密码，盐为空
			if($email != '' && $password != ''){
				$salt = md5($username.$password.$email.rand_str(32)); //为用户生成密码盐
				$password = md5($password.$salt);
			}
			
			$user_group_id = $this->get_user_group_id('registered');
			
			//拼装数据
			$data = array(
				'guid'				=> $guid,
				'user_group_id'		=> $user_group_id,
				'username'			=> $username,
				'email'				=> $email,
				'is_email_verified'	=> false,
				'salt'				=> $salt,
				'password'			=> $password,
				'unique_key'		=> $user_unique_key
			);
			
			//将数据插入“users”表
			$this->create_a_record('users', $data);
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			$result = $this->ci->db->trans_status();
			
			if($result === TRUE){
				//登录用户
				$this->sign_me_in($guid, $user_group_id, $username, $email, $user_unique_key, $this->_default_avatar_url, false);
				
				//给用户发激活邮件
				if($email != ''){
					$this->send_verification_link($email, $user_unique_key);
				}
				
				//将用户注册行为和首次登录行为都写入日志
				//$this->ci->river_lib->logit('register', $guid, $user_unique_key, array(), 'register_with_email');
				//$this->ci->river_lib->logit('login', $guid, $user_unique_key, array(), 'initial_login');
				
				//写入注册成功信息
				$this->set_a_msg('您已成功注册并登录，请查收邮件并验证邮箱','success');
				
				return $guid;
			} else {
				return 0;
			}
		}
		
		
		// ********************************************************
		// 用户登录相关的方法
		// ********************************************************
		
		function login_a_user($email, $password, $remember_me = TRUE){
		
			//获取用户信息
			$user = $this->get_user_info($email);
			
			if($user['user_group_id'] == $this->get_user_group_id('banned')){
				$this->set_a_msg('您的账号被禁用', 'error');
				$this->increase_login_attempt();
				return False;
			} elseif($user['password'] != md5($password.$user['salt'])) {
				$this->set_a_msg('您输入的密码错误', 'error');
				$this->increase_login_attempt();
				return False;
			} else {
				
				//将要写入的session数据准备好，并写入session
				$guid				= $user['guid'];
				$user_group_id		= $user['user_group_id'];
				$username			= $user['username'];
				$email				= $user['email'];
				$is_email_verified	= $user['is_email_verified'];
				$user_unique_key	= $user['unique_key'];
				$avatar_url			= iif($user['avatar_url'] != '', $user['avatar_url'], $this->_default_avatar_url);
				
				//如果“user_identifier”和“user_unique_key”不同
				if($this->_user_identifier != $user_unique_key){
					$this->set_user_identifier($user_unique_key);
				}
				
				//sign me in
				$this->sign_me_in($guid, $user_group_id, $username, $email, $user_unique_key, $avatar_url, $is_email_verified);
				
				//将用户行为写入日志系统
				//$this->ci->river_lib->logit('login', $guid, $user_unique_key, array(), 'login_with_email');
					
				//如果用户选择了“记住我”,记录自动登录功能
				if($remember_me){
					$this->create_autologin($user_unique_key);
				}
					
				//清除登陆失败记录
				$this->clear_login_attempts();
				
				return TRUE;
			}
		}
		
		// 将用户信息写入“session”
		private function sign_me_in($guid, $user_group_id, $username, $email, $user_unique_key, $avatar_url, $is_email_verified){
		
			$data = array(
				'guid'				=> $guid,
				'user_group_id'		=> $user_group_id,
				'username'			=> $username,
				'email'				=> $email,
				'avatar_url'		=> $avatar_url,
				'user_unique_key'	=> $user_unique_key,
				'is_login'			=> true,
				'is_email_verified'	=> $is_email_verified
			);
			
			$this->ci->_guid 				= $guid;
			$this->ci->_user_group_id 		= $user_group_id;
			$this->ci->_email				= $email;
			$this->ci->_username			= $username;
			$this->ci->_avatar_url			= $avatar_url;
			$this->ci->_user_unique_key		= $user_unique_key;
			$this->ci->_is_login			= true;
			$this->ci->_is_email_verified	= $is_email_verified;
			
			//将“$data”中的数据写入“sesssion”
			return $this->ci->session->set_userdata($data);
		}
		
		//判断用户登陆次数是否达到最大值
		function is_max_login_attempts_exceeded(){
			$login_attempts = $this->get_records('login_attempts', array('id'), array('ip_address'=> $this->_ip_address), NULL, 0, NULL, 'DESC', TRUE);
			if($login_attempts >= $this->_max_login_allowence) return TRUE;
			return FALSE;
		}
		
		//增加用户登陆错误数
		function increase_login_attempt(){
			$data = array('ip_address' => $this->_ip_address, 'time' => time());
			return $this->create_a_record('login_attempts', $data);
		}
		
		//清除登录失败记录
		function clear_login_attempts(){
			$condition = array('ip_address' => $this->_ip_address);
			return $this->delete_records('login_attempts', $condition);
		}
		
		// ********************************************************
		// 用户自动登录相关的方法
		// ********************************************************
		
		//将用户的登录数据写入“cookie”
		function create_autologin($user_unique_key){
		
			$this->prune_key($user_unique_key); //将数据库中autologins的某用户的最近一次登录信息删除
			$this->store_key($user_unique_key); //将登陆记录写入autologins
			
			$data = array('user_unique_key' => $user_unique_key);
			$cookie = array('name' => $this->_autologin_cookie, 'value' => serialize($data), 'expire' => $this->_autologin_cookie_expiration);
			
			return set_cookie($cookie);
		}
		
		//删除“autologins”中的自动登录数据
		private function prune_key($user_unique_key){
			$condition = array('user_unique_key' => $user_unique_key);
			RETURN $this->delete_records('autologins', $condition);
		}
		
		//添加一条“autologins”中自动登录的用户数据
		private function store_key($user_unique_key){
		
			$data = array(
				'user_unique_key'	=> $user_unique_key,
				'user_agent' 		=> $this->_user_agent,
				'last_ip'	 		=> $this->_ip_address,
				'last_login' 		=> time()
			);
			
			return $this->create_a_record('autologins', $data);
		}
		
		//用户自动登录
		function auto_login($user_unique_key){
		
			$user = $this->get_user_info($user_unique_key); //获取用户信息
			
			if(count($user) == 0 OR !$this->check_a_record('autologins', array('user_unique_key' => $user_unique_key))){
				return False;
			} else {
				//将要写入的session数据准备好，并写入session
				$guid				= $user['guid'];
				$user_group_id		= $user['user_group_id'];
				$username			= $user['username'];
				$email				= $user['email'];
				$is_email_verified	= $user['is_email_verified'];
				$user_unique_key	= $user['unique_key'];
				$avatar_url			= iif($user['avatar_url'] != '', $user['avatar_url'], $this->_default_avatar_url);
				
				//如果“user_identifier”和“user_unique_key”不同
				if($this->_user_identifier != $user_unique_key) $this->set_user_identifier($user_unique_key);
				
				//sign me in
				$this->sign_me_in($guid, $user_group_id, $username, $email, $user_unique_key, $avatar_url, $is_email_verified);
				
				//再次创建自动登录数据
				$this->create_autologin($user_unique_key);
				
				//将用户行为写入日志系统
				//$this->ci->river_lib->logit('login', $guid, $user_unique_key, array(), 'auto_login');
				
				return True;
			}
		}
		
		//通过一个用户的“user_unique_key”直接登录该用户
		function direct_login($user_unique_key, $action = 'direct_login_after_password_reset'){
		
			$user = $this->get_user_info($user_unique_key);
			
			//将要写入的session数据准备好，并写入session
			$guid				= $user['guid'];
			$user_group_id		= $user['user_group_id'];
			$username			= $user['username'];
			$email				= $user['email'];
			$is_email_verified	= $user['is_email_verified'];
			$avatar_url			= iif($user['avatar_url'] != '', $user['avatar_url'], $this->_default_avatar_url);
				
			//如果“user_identifier”和“user_unique_key”不同
			if($this->_user_identifier != $user_unique_key) $this->set_user_identifier($user_unique_key);
				
			//登录用户
			$this->sign_me_in($guid, $user_group_id, $username, $email, $user_unique_key, $avatar_url, $is_email_verified);
			
			//如果用户选择了“记住我”,记录自动登录功能
			$this->create_autologin($user_unique_key);
			
			//将用户行为写入日志系统
			//$this->ci->river_lib->logit('login', $guid, $user_unique_key, array(), $action);
			
			return TRUE;
		}
		
		// ********************************************************
		// 退出系统线相关方法
		// ********************************************************
		
		// 退出系统
		function logout($user_unique_key){
		
			//如果存在"auto_login"的"cookie"，删除该"cookie" 
			if(get_cookie($this->_autologin_cookie)) $this->delete_autologin($user_unique_key);
			
			//如果存在"no-email"的"cookie", 删除该"cookie"
			if(get_cookie('no-email')) delete_cookie('no-email');
			
			//如果存在"not-verified"的"cookie"，删除该"cookie"
			if(get_cookie('not-verified')) delete_cookie('not-verified');
			
			//销毁全部"session"数据
			$this->ci->session->sess_destroy();
			
			return;
		}
		
		//删除“autologin”
		private function delete_autologin($user_unique_key){
			$condition = array('user_unique_key' => $user_unique_key);
			$this->delete_records('autologins', $condition);
			delete_cookie($this->_autologin_cookie);
			return;
		}
		
		// ********************************************************
		// 验证用户电子邮箱相关方法
		// ********************************************************
		
		//给注册成功的用户发一封激活电子邮件
		public function send_verification_link($email, $user_unique_key, $if_for_new_sign_up = true){
			
			//发送邮件
			$this->ci->email->from($this->_email_no_reply_address, $this->_site_name);
			$this->ci->email->to($email);
			
			if($if_for_new_sign_up){
				$this->ci->email->subject('欢迎加入'.$this->ci->config->item('site_name'));
			} else {
				$this->ci->email->subject('请验证您的电子邮箱');
			}
			
			//更新邮件发送时间
			$time_verification_link_sent = time();
			$condition = array('unique_key' => $user_unique_key);
			$data = array('time_verification_link_sent' => $time_verification_link_sent);
			$this->update_records('users', $data, $condition);
			
			//生成激活链接
			$activation_link = base_url("user/verify/$user_unique_key/".md5($email.$user_unique_key.$time_verification_link_sent));
			
			//初始化邮件内容
			$msg = '';
			
			 //如果是用户首次注册，加上“欢迎”语
			if($if_for_new_sign_up){
				$msg .= '欢迎您加入“知乐儿”。我们希望您能够在这获得更多的知识。';
			}
			
			//带有激活链接的文字
			$msg .= "请点击下面链接，验证您的电子邮箱: $activation_link ";
			
			$this->ci->email->message($msg);
			
			return $this->ci->email->send();
		}
		
		//验证邮件地址的有效性
		function verify_an_email($user_unique_key, $encrypted_str){
			if($user_unique_key == '' OR $encrypted_str == ''){
				$this->set_a_msg('链接有误，无有效参数', 'error');
				return False;
			} else {
				//获取该用户的信息
				$user = $this->get_user_info($user_unique_key);
				
				if(count($user) == 0){
					$this->set_a_msg('验证用户不存在', 'error');
					return False;
				} else {
				
					//将要写入的“session”数据准备好，并写入“session”
					$guid							= $user['guid'];
					$user_group_id					= $user['user_group_id'];
					$username						= $user['username'];
					$email							= $user['email'];
					$is_email_verified				= $user['is_email_verified'];
					$user_unique_key				= $user['unique_key'];
					$avatar_url						= iif($user['avatar_url'] != '', $user['avatar_url'], $this->_default_avatar_url);
					$salt 							= $user['salt'];
					$time_verification_link_sent 	= $user['time_verification_link_sent'];
					
					if($is_email_verified){
						$this->set_a_msg('你的电子邮箱此前已成功验证，无需再次验证', 'error');
						return False;
					} elseif(time() - $time_verification_link_sent > $this->_email_verification_expiration) {
						$this->set_a_msg('该链接已过期', 'error');
						return False;
					} elseif(md5($email.$user_unique_key.$time_verification_link_sent) != $encrypted_str) {
						$this->set_a_msg('该链有误，请检测链接后重新验证', 'error');
						return False;
					} elseif($user_group_id == 4) {
						$this->set_a_msg('你已经被拉黑，啥都不能做了', 'error');
						return False;
					} else {
					
						/**
							验证邮箱通过后：
							- 将“is_email_verified”设置为“true”，
							- 将发送验证邮件点时间清空
							- 更新本地cookie的“identifier”
							- 登录用户
						*/
						
						$condition = array('guid' => $guid);
						$data = array('is_email_verified' => True, 'time_verification_link_sent' => '');
						
						$this->update_records('users', $data, $condition);
						
						//如果“user_identifier”和“user_unique_key”不同
						if($this->_user_identifier != $user_unique_key){
							$this->set_user_identifier($user_unique_key);
						}
						
						//设置“is_email_verified”为“true”，然后设置到“session”
						$is_email_verified = True;
						$this->ci->session->set_userdata(array('is_email_verified' => $is_email_verified)); //更新SESSION
						
						//登录用户
						if(!$this->_is_login){
							$this->sign_me_in($guid, $user_group_id, $username, $email, $user_unique_key, $avatar_url, $is_email_verified);
						}
						
						return True;
					}
				}
			}
		}
		
		// ********************************************************
		// 找回密码的相关方法
		// ********************************************************
		
		//给用户发送重置密码的通知邮件
		function request_password_reset($email){
		
			//根据用户的“email”地址，获取用户信息
			$fields = array('unique_key', 'user_group_id');
			$user = $this->get_user_info($email);
			
			if($user['user_group_id'] == $this->get_user_group_id('banned')){
				$this->set_a_msg('您的账号已被禁用，不能进行找回密码的相关操作。如有疑问，请和管理员联系', 'error');
				return False;
			} else {
			
				//更新数据库里密码重设置的发送时间
				$user_guid = $user['guid']; //获取该用户的“unique_key”
				$user_unique_key = $user['unique_key']; //获取该用户的“guid”
				
				$time_password_reset_link_sent = time();
				
				$data = array('time_password_reset_link_sent' => $time_password_reset_link_sent);
				$condition = array('guid' => $user_guid);
				$this->update_records('users', $data, $condition);
				
				//生成加密的字符串
				$encrypted_str = md5($email.$user_unique_key.$time_password_reset_link_sent);
				
				//给用户发邮件，个用户发送密码重置密码
				$password_reset_link = base_url("user/reset_password/$user_unique_key/".$encrypted_str);
				
				//发送邮件
				$this->ci->email->from($this->_email_no_reply_address, $this->_site_name);
				$this->ci->email->to($email);
				$this->ci->email->subject('重置密码');
				$message = "请点击链接重置您的密码: $password_reset_link";
				$this->ci->email->message($message);
				$this->ci->email->send();
				
				//将用户行为写入日志系统
				//$this->ci->river_lib->logit('request_password_reset', $user_guid, $user_unique_key);
				
				return True;
			}
		}
		
		// 检查重置密码的链接是否有效
		function check_password_reset_link($user_unique_key, $encrypted_str){
			if($user_unique_key == '' OR $encrypted_str == ''){
				$this->set_a_msg('链接有误，请检查链接后再试！', 'error');
				RETURN FALSE;
			} else {
				$fields = array('time_password_reset_link_sent', 'email', 'user_group_id');
				$user = $this->get_a_subtype_row('users', $user_unique_key, $fields);
				if(count($user) == 0){
					$this->set_a_msg('该用户不存在', 'error');
					RETURN FALSE;
				} else {					
					if($this->get_user_group($user['user_group_id']) == 'banned'){
						$this->set_a_msg('您的账号已被禁用，请和管理员联系', 'error');
						return FALSE;
					} elseif(time() - $user['time_password_reset_link_sent'] > $this->_password_reset_link_expiration) {
						$this->set_a_msg('该链接已过期', 'error');
						return FALSE;
					} elseif(md5($user['email'].$user_unique_key.$user['time_password_reset_link_sent']) != $encrypted_str) {
						$this->set_a_msg('该链有误，请检测链接', 'error');
						return FALSE;
					}
				}
			}
		}
		
		// ********************************************************
		// 修改用户信息的相关方法
		// ********************************************************
		
		// 修改密码
		function reset_password($user_unique_key, $password)
		{
			//首先判断，该用户是否已经设置电子邮箱
			$email = $this->get_a_value('users', 'email', array('unique_key' => $user_unique_key));
			
			if($email == ''){
				$this->set_a_msg('尚未设置电子邮箱地址，不能设置密码', 'error');	
				return FALSE;
			} else {
				$salt = md5($user_unique_key.$password.time().rand_str(32));
				$password = md5($password.$salt);
				$data = array('password' => $password, 'salt' => $salt);
			
				//开始数据库存储事件
				$this->ci->db->trans_start();
					
				//更新用户的密码
				$condition = array('unique_key' => $user_unique_key);
				$this->update_records('users', $data, $condition);
			
				//结束数据库存储事件
				$this->ci->db->trans_complete();
				return $this->ci->db->trans_status();
			}
		}
		
		/**
		//更新用户名
		function update_username($guid, $new_username)
		{
			$condition = array('guid' => $guid);
			
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			//更新保存在“entities”表中的用户名
			$this->update_records('entities', array('title' => $new_username), $condition);
			
			//更新保存在“users”表中的用户名
			$this->update_records('users', array('username' => $new_username), $condition);
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			if($this->ci->db->trans_status() === TRUE)
			{
				//更新“session”中的用户名
				$this->ci->session->set_userdata(array('username' => $new_username));
				
				//将用户行为写入日志系统
				//$this->ci->river_lib->logit('update_username');
				
				return TRUE;
			}
			
			return FALSE;
		}
		*/
		
		//更新电子邮箱
		function update_email($user_guid, $email)
		{
			$condition = array('guid' => $user_guid);
			
			//分别更新数据库和“SESSION”
			$data = array('email' => $email, 'is_email_verified' => false); //准备要更新的数据
			$this->update_records('users', $data, $condition); //更新数据库
			$this->ci->session->set_userdata($data); //更新SESSION
			
			//给用户发邮箱验证邮件
			$this->send_verification_link($email, $this->_user_identifier, false);
			
			return TRUE;
		}
		
		//更新用户信息
		function update_profile($user_guid, $data)
		{
			$condition = array('guid' => $user_guid);
			$this->update_records('users', $data, $condition);
			return TRUE;
		}
		
		// ********************************************************
		// 其它方法
		// ********************************************************
		
		// 通过用户的GUID，获取一个用户所绑定的第三方社会化媒体的信息
		public function get_user_social_media($user_guid, $platform = 'wechat')
		{
			$fields = array('third_username');
			$condition = array('guid' => $user_guid, 'third_platform' => $platform);
			$result = $this->get_records('third_party_bindings', $fields, $condition);
			
			if(sizeof($result) == 0)
			{
				return;
			}
			
			return $result[0]['third_username'];
		}
		
		// 判断用户是否设置密码
		public function is_user_password_set($user_guid)
		{
			$condition = array('guid' => $user_guid);
			$password = $this->get_a_value('users', 'password', $condition);
			
			if($password != '') return true;
			return false;
		}
		
		// 通过用户的GUID、unique_key、或邮箱地址，获取用户的信息
		public function get_user_info($key, $fields = array('*'))
		{
			$user_arr 	= array();
			$condition 	= "guid = '$key' OR unique_key = '$key' OR email = '$key' ";
			$user 		= $this->get_records('users', $fields, $condition);
			if(count($user) > 0) $user_arr = $user[0];
			return $user_arr;
		}
		
		// 根据“group_id”获取“group_name”
		private function get_user_group($group_id)
		{
			return $this->get_a_value('user_groups', 'user_group', array('id' => $group_id));	
		}
		
		// 根据“group_name”获取“group_id”
		private function get_user_group_id($group_name)
		{
			return $this->get_a_value('user_groups', 'id', array('user_group' => $group_name));
		}
		
		// 根据微信生成的code, 获取微信的access_token
		function get_access_token($code){
			$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx64b8083d5c121170&secret=4087e2ce57b5e2e76f9c4f742a6223a8&code='.$code.'&grant_type=authorization_code';
            $header_string = array();
            $pdata = array();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header_string);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $pdata);
            $res = curl_exec($ch);
            curl_close($ch);
            $res = json_decode($res, true);
            return $res;
		}
		
		// 获取某一用户的微信信息
		function get_weixin_userinfo($access_token, $openid){
			$url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
            $header_string = array();
            $pdata = array();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header_string);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $pdata);
            $res = curl_exec($ch);
            curl_close($ch);
            $res = json_decode($res, true);
            return $res;
		}
	}