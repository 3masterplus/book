<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

	class User extends Client_Controller{
			
		function __construct(){
			parent::__construct();
			$this->load->library('form_validation');
			$this->load->library('user_lib');
			$this->template->set_layout('new_layout_1');
		}
		
		// ********************************************************
		// 用户注册相关方法
		// ********************************************************
		
		function register(){
		
			$this->_user_identifier;
			
			//如果用户是已登录用户，将跳转到首页
			$this->unregistered_only();
			
			//加载资源
			$this->load->library('email');
			$this->load->library('Captcha_lib');
			
			//初始化“$data”
			$data = array('email' => '', 'username' => '', 'error_str' => '');
			
			$error_marker = array();
			
			//生成验证码
			$data['captcha'] = $this->captcha_lib->create_a_captcha($this->_ip_address);
			
			//获取访问来源
			$referrer = iif(CI_POST('register_a_user'), CI_POST('referrer'), $this->_referrer);
			$data['referrer'] = iif(strstr($referrer, base_url()), $referrer, '');
			
			if(CI_POST('register_a_user')){
			
				$email		= CI_POST('email');
				$password	= CI_POST('password');
				$username	= CI_POST('username');
				$captcha	= CI_POST('captcha');
				
				$this->form_validation->set_rules('username', '用户名', 'required');
				$this->form_validation->set_rules('email', '电子邮箱', 'required|valid_email|is_unique[users.email]');
				$this->form_validation->set_rules('password', '密码', 'required|callback_password_check');
				$this->form_validation->set_rules('captcha', '验证码', 'required|callback_captcha_check');
				
				if($this->form_validation->run() AND $this->user_lib->create_a_user($username, $email, $password) > 0){
					$destination = iif($referrer!='', $referrer, base_url());
					redirect($destination);
				} else {
					
					if(strlen($this->form_validation->error('email')) == 0){
						$data['email'] = $email;
					} else {
						$error_marker[] = 'email';
					}
					
					if(strlen($this->form_validation->error('username')) == 0){
						$data['username'] = $username;
					} else {
						$error_marker[] = 'username';
					}
					
					if(strlen($this->form_validation->error('password')) != 0){
						$error_marker[] = 'password';
					}
					
					if(strlen($this->form_validation->error('captcha')) != 0){
						$error_marker[] = 'captcha';
					}
					
					$data['error_str'] = $this->my_lib->generate_error_message();
				}			
			}
			
			$data['error_marker'] = $error_marker;
			
			$this->template->set('page','account');
			$this->template->title('注册新账号', $this->config->item('site_name'));
			$this->template->build('user/new/register_with_email', $data);
		}
		
		// ********************************************************
		// 用户登录相关方法
		// ********************************************************
		
		function login(){
		
			//如果用户是已登录用户，将跳转到首页
			$this->unregistered_only();
		
			//加载资源
			$this->load->library('Captcha_lib');
			
			//初始化数据
			$data = array('email' => '', 'remember_me' => 1, 'error_str' => '');
			
			$error_marker = array();
			
			$is_max_login_attempts_exceeded = $this->user_lib->is_max_login_attempts_exceeded();
			$data['is_max_login_attempts_exceeded'] = $is_max_login_attempts_exceeded;
			
			if($is_max_login_attempts_exceeded){
				$data['captcha'] = $this->captcha_lib->create_a_captcha($this->_ip_address);
			}
			
			//获取访问来源
			$referrer = iif(CI_POST('login_a_user'), CI_POST('referrer'), $this->_referrer);
			$data['referrer'] = iif(strstr($referrer, base_url()), $referrer, '');
			
			if(CI_POST('login_a_user')){
			
				$email			= CI_POST('email');
				$password		= CI_POST('password');
				$remember_me	= CI_POST('remember_me');
				$remember_me 	= true;
				
				if($is_max_login_attempts_exceeded) $captcha = CI_POST('captcha');
				
				$this->form_validation->set_rules('email', '电子邮箱', 'required|valid_email|callback_is_existent[users.email]');
				$this->form_validation->set_rules('password', '密码', 'required');
				
				//验证码验证规则
				if($is_max_login_attempts_exceeded) {
					$this->form_validation->set_rules('captcha', '验证码', 'required|callback_captcha_check');
				}
				
				if($this->form_validation->run() AND $this->user_lib->login_a_user($email, $password, $remember_me)){
					$destination = iif($referrer !='', $referrer, '/dashboard');
					redirect($destination);
				} else {
				
					if(strlen($this->form_validation->error('email')) == 0){
						$data['email'] = $email;
					} else {
						$error_marker[] = 'email';
					}
					
					if(strlen($this->form_validation->error('password')) != 0){
						$error_marker[] = 'password';
					}
					
					if(strlen($this->form_validation->error('captcha')) != 0){
						$error_marker[] = 'captcha';
					}
					
					$data['error_str'] = $this->my_lib->generate_error_message();
					$data['remember_me'] = $remember_me;
				}
			}
			
			$data['error_marker'] = $error_marker;
			$this->template->set('page','account');
			$this->template->title('注册', $this->config->item('site_name'));
			$this->template->build('user/new/login_with_email', $data);
		}

		function sns($provider){
			$this->load->helper('api');
			$this->load->library('email');
			$this->load->helper('language');
			$this->load->library('api/Api_user_lib');

			$sns_userinfo = $this->session->get_userdata('user')['user'];
			$user_guid = $this->my_lib->get_a_value('third_party_bindings', 'guid', array('third_uid' => $sns_userinfo['uid'], 'third_platform' => $sns_userinfo['via']));
			
			$refer = CI_GET('refer') ? CI_GET('refer') : '/';

			$data['refer'] = $refer;
			
			if( $this->session->userdata('sns_action') === 'just_bind')
			{
				$sns_userinfo = $this->session->get_userdata('user')['user'];

				$wechat_uid = $sns_userinfo['uid'];
				$wechat_openid = $sns_userinfo['openid'];
				$wechat_avatar = $sns_userinfo['image'];
				$wechat_nickname = $sns_userinfo['name'];
				
				$expired_in = $sns_userinfo['expire_at'];
				$refresh_token = $sns_userinfo['refresh_token'];
				
				$third_data = array(
					'third_access_token' => $sns_userinfo['access_token'],
					'third_uid' => $wechat_uid,
					'expired_in' => $expired_in,
					'avatar' => $wechat_avatar,
					'third_name' => $wechat_nickname,
					'third_openid' => $wechat_openid,
					'third_refresh_token' => $refresh_token
				);

				try{
					$res = $this->api_user_lib->user_binding_media($sns_userinfo['via'], $third_data, array(), false, false);
				}
				catch (exception $e){
					$error_msg = $e->getMessage();
					echo '抱歉，绑定失败，失败的原因是: '. $error_msg;
					exit();
				}
				
				if($res)
				{
					redirect($refer);
					exit;
				}
				else
				{
					var_dump($res); 
					exit;
				}
			}
			
			//第三方账号已绑定过
			
			if($user_guid && $this->session->userdata('sns_action') !== 'just_bind') {
                //$tokendata = $this->generate_user_authcode($user_guid);
                $userinfo = $this->user_lib->get_user_info($user_guid);
                //被动更新用户过期的access_token
                $update_data = array(
                    'third_access_token' => $sns_userinfo['access_token'],
                    'expired_in' => $sns_userinfo['expire_at'],
                );
                $this->my_lib->update_records('third_party_bindings', $update_data, array('third_uid' => $sns_userinfo['uid'], 'third_platform' => $sns_userinfo['via']));
            	$this->user_lib->direct_login($userinfo['unique_key']);

            	redirect($refer);
            	exit;
            }
            else{

            	$sns_userinfo = $this->session->get_userdata('user')['user'];

				$wechat_uid = $sns_userinfo['uid'];
		        $wechat_avatar = $sns_userinfo['image'];
		        $wechat_nickname = $sns_userinfo['name'];

		        $expired_in = $sns_userinfo['expire_at'];
		        $refresh_token = $sns_userinfo['refresh_token'];


		        $third_data = array(
		            'third_access_token' => $sns_userinfo['access_token'],
		            'third_uid' => $wechat_uid,
		            'expired_in' => $expired_in,
		            'third_avatar' => $wechat_avatar,
		            'third_name' => $wechat_nickname,
		            'third_openid' => $wechat_uid,
		            'third_refresh_token' => $refresh_token
		        );

		        try{
		        	$res = $this->api_user_lib->handle_media_bindings_simple('wechat', $third_data, array(), false, false);
		        }
		        catch (exception $e){
		        	$error_msg = $e->getMessage();
					echo '抱歉，绑定失败，失败的原因是: '. $error_msg;
					exit();
		        }
            	
		        if($res){
		        	redirect($refer);
		        	exit;
		        }
            }

			// $data['avatar_url'] = $sns_userinfo['image'];
			// $data['user_name'] = $sns_userinfo['name'];

			// $this->template->set('page','account');
			// $this->template->build('user/new/register_or_bind_from_sns', $data);
		}


		//第三方账号登录，注册新账号
		function ajax_register_with_sns(){
			if( CI_POST('register_with_sns') ){
				$this->load->helper('api');
				$this->load->library('email');
				$this->load->helper('language');
				$this->load->library('api/Api_user_lib');

				$username = CI_POST('username');
				$email    = CI_POST('email');
				$password = CI_POST('password');

				$userinfo = $this->session->get_userdata('user')['user'];

				$wechat_uid = $userinfo['uid'];
				$wechat_avatar = $userinfo['image'];
				$wechat_nickname = $userinfo['name'];

				$expired_in = $userinfo['expire_at'];
				$refresh_token = $userinfo['refresh_token'];

				$third_data = array(
					'third_access_token' => $userinfo['access_token'],
					'third_uid' => $wechat_uid,
					'third_email' => $email,
					'expired_in' => $expired_in,
					'username' => $username,
					'password' => $password,
					'third_avatar' => $wechat_avatar,
					'third_name' => $wechat_nickname,
					'third_openid' => $wechat_uid,
					'third_refresh_token' => $refresh_token
				);

				$res = $this->api_user_lib->handle_media_bindings_simple('wechat', $third_data, array(), false, false);
				$this->ajax_ini($res);
				$this->ajax_response();
			}
		}

		//第三方账号登录，绑定已有账号
		function ajax_bind_sns(){
			if( CI_POST('bind_sns') ){
				$this->load->helper('api');
				$this->load->library('email');
				$this->load->helper('language');
				$this->load->library('api/Api_user_lib');

				$email		= CI_POST('email');
				$password	= CI_POST('password');

				$this->form_validation->set_rules('email', '电子邮箱', 'required|valid_email|callback_is_existent[users.email]');
				$this->form_validation->set_rules('password', '密码', 'required');

				if($this->form_validation->run() AND $this->user_lib->login_a_user($email, $password))
				{
					$sns_userinfo = $this->session->get_userdata('user')['user'];

					$wechat_uid = $sns_userinfo['uid'];
					$wechat_avatar = $sns_userinfo['image'];
					$wechat_nickname = $sns_userinfo['name'];
					
					$expired_in = $sns_userinfo['expire_at'];
					$refresh_token = $sns_userinfo['refresh_token'];


					$third_data = array(
						'third_access_token' => $sns_userinfo['access_token'],
						'third_uid' => $wechat_uid,
						'third_email' => $email,
						'expired_in' => $expired_in,
						'username' => '',
						'password' => $password,
						'avatar' => $wechat_avatar,
						'third_name' => $wechat_nickname,
						'third_openid' => $wechat_uid,
						'third_refresh_token' => $refresh_token
					);
					
					$res = $this->api_user_lib->user_binding_media($sns_userinfo['via'], $third_data, array(), false, false);
					$this->ajax_ini($res);
				}
				else
				{
					var_dump('error');exit;
				}

				$this->ajax_response();
			}
		}
		
		/**
			用户在使用微信内置浏览器内，此时如果用户正在课程页，
			点击“立即学习”，在“Session”中记录用户登录后的课程。
		*/
		
		function ajax_set_session_for_wechat_login(){
			if(CI_POST('set_session_for_wechat_login')){
				
				$course_unique_key = CI_POST('course_unique_key');
				$this->form_validation->set_rules('course_unique_key', 'course_unique_key', 'required|callback_is_existent[courses.unique_key]');
				
				if($this->form_validation->run()){
					//将“$data”中的数据写入“sesssion”
					$this->session->set_userdata(array('redirected_course_unique_key' => $course_unique_key));
					$this->ajax_ini();
				} else {
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//微信登录成功后的中继页面
		function wechat_login_relay(){
		
			//加载“course_lib”
			$this->load->library('course_lib');
			
			// 首先，判断“session”中是否包含“redirected_course_unique_key”
			
			$course_unique_key = ($this->session->userdata('redirected_course_unique_key')) ? $this->session->userdata('redirected_course_unique_key'): '';
			$user_guid = $this->_guid;
			
			// 如果“session”中存有“course_unique_key”
			if($course_unique_key != ''){
				$course_guid = $this->my_lib->get_guid_by_unique_key($course_unique_key);
				$redirected_url = base_url('course/'.$course_unique_key.'/home');
				if(!$this->course_lib->if_a_course_joined($user_guid, $course_guid)){
					$redirected_url .= '?unlearned';
				}
				redirect($redirected_url);
			} else {
				$condition = array('user_guid' => $user_guid, 'action' => 'join_a_course');
				$fields = array('course_guid', 'time_updated');
				$courses = $this->my_lib->get_records('user_course_relations', $fields, $condition);
				if(sizeof($courses) > 0) redirect(base_url('dashboard'));
				else redirect(base_url('course'));
			}
		}
		
		//通过微信的浏览器登录
		function wechat_login()
		{
			$code = CI_GET('code');
			
			if($code){
				$this->load->library('api/Api_user_lib');
				$wx_token = $this->user_lib->get_access_token($code);
				
				if(!isset($wx_token['errcode'])){
				
					$access_token		= $wx_token['access_token'];
					$expires_in			= $wx_token['expires_in'];
					$openid				= $wx_token['openid'];
					$refresh_token		= $wx_token['refresh_token'];
					$weixin_userinfo 	= $this->user_lib->get_weixin_userinfo($access_token, $openid);
					$unionid 			= $weixin_userinfo['unionid'];
					
					//获取当前的用户的绑定GUID
					$user_guid = $this->my_lib->get_a_value('third_party_bindings', 'guid', array('third_uid' => $unionid, 'third_platform' => 'wechat'));

					//登录所用微信账号已绑定到知乐账号
					if($user_guid)
					{	
						//被动更新用户过期的access_token
						$update_data = array('third_access_token' => $access_token, 'expired_in' => $expires_in);
						$this->my_lib->update_records('third_party_bindings', $update_data, array('third_uid' => $unionid, 'third_platform' => 'wechat'));
						
						//根据用户的GUID获取用户的user_unique_key
						$user_unique_key = $this->my_lib->get_unique_key_by_guid($user_guid);
						
						//直接登录
						$this->user_lib->direct_login($user_unique_key);
						
						//直接挑战到“dashboard”
						redirect(base_url('user/wechat_login_relay'));
						
					} else {
						
						$third_data = array(
							'third_access_token'	=> $access_token,
							'third_uid' 			=> $unionid,
							'expired_in'			=> $expires_in + time(),
							'third_avatar' 			=> $weixin_userinfo['headimgurl'],
							'third_name' 			=> $weixin_userinfo['nickname'],
							'third_openid' 			=> $openid,
							'third_refresh_token' 	=> $refresh_token
						);
						
						try{
							$res = $this->api_user_lib->handle_media_bindings_simple('wechat', $third_data, array(), false, false);
							
							//直接挑战到“dashboard”
							redirect(base_url('user/wechat_login_relay'));
						
						} catch(exception $e) {
							$error_msg = $e->getMessage();
							echo '抱歉，绑定失败，失败的原因是: '. $error_msg;
							exit();
						}
					}
				}
			}
		}
		
		public function cancel_sns_bind(){
			if(CI_POST('cancel_sns_bind')){
			
				$this->load->helper('api');
				$this->load->library('email');
				$this->load->helper('language');
				$this->load->library('api/Api_user_lib');

				$type = CI_POST('type');

				$result = $this->api_user_lib->cancel_media_bingding($type);

				$this->ajax_ini($result);
				$this->ajax_response();
			}
		}
		
		//异步登录
		public function ajax_login() {
		
			if(!CI_POST('login_a_user')){
				return;
			}
			
			//加载资源
			$this->load->library('Captcha_lib');
			
			//初始化数据
			$data = array('email' => '', 'remember_me' => '', 'error_str' => '');
			
			$is_max_login_attempts_exceeded = $this->user_lib->is_max_login_attempts_exceeded();
			$data['is_max_login_attempts_exceeded'] = $is_max_login_attempts_exceeded;
			
			$email			= CI_POST('email');
			$password		= CI_POST('password');
			$remember_me	= CI_POST('remember_me');
			$remember_me 	= (boolean)$remember_me;
				
			if($is_max_login_attempts_exceeded) $captcha = CI_POST('captcha');
				
			$this->form_validation->set_rules('email', '电子邮箱', 'required|valid_email|callback_is_existent[users.email]');
			$this->form_validation->set_rules('password', '密码', 'required');
				
			if($is_max_login_attempts_exceeded) $this->form_validation->set_rules('captcha', '验证码', 'required|callback_captcha_check');
				
			if($this->form_validation->run() AND $this->user_lib->login_a_user($email, $password, $remember_me)) {
			
				$user = $this->session->all_userdata();
				
				$userinfo = array(
					'avatar_url'		=> $user['avatar_url'],
					'email' 			=> $user['email'],
					'is_email_verified'	=> $user['is_email_verified'],
					'guid' 				=> $user['guid'],
					'user_group_id' 	=> $user['user_group_id'],
					'username'			=> $user['username']
				);
					
				$this->ajax_ini($userinfo);
			
			} else {
			
				if($is_max_login_attempts_exceeded) {
					$this->_ajax_data = $this->captcha_lib->create_a_captcha($this->_ip_address)['image'];
				}
				
				$this->_ajax_message = $this->my_lib->generate_error_message();			
			}
				
			$this->ajax_response();
		}
		
		//用户设置或更新一个电子邮箱，都需要调用这个接口
		public function ajax_update_email(){	
			
			if(!CI_POST('update_email')){
				return;
			}
			
			$this->ajax_members_only();
		
			//加载邮件发送库
			$this->load->library('email');
			
			$email 		= CI_POST('email'); //获取从客户端点电子邮箱地址
			$user_guid 	= $this->_guid; //获取当前用户GUID
			
			$this->form_validation->set_rules('email', '电子邮箱', 'required|valid_email|is_unique[users.email]');
				
			if($this->form_validation->run() && $this->user_lib->update_email($user_guid, $email)){
				$data['message'] = '您的电子邮件设置成功。我们刚刚给您的电子邮箱发送了一封邮件，请根据邮件提示，验证您的电子邮箱地址。';
				$this->ajax_ini($data);
			} else {
				$this->_ajax_message = $this->my_lib->generate_error_message();
			}
			
			$this->ajax_response();
		}
		
		//用户设置或更新一个密码
		public function ajax_update_password(){
			
			//框架性限制
			if(!CI_POST('update_password')){
				RETURN;
			}
			
			$this->ajax_members_only(); //用户身份限制
			
			if($this->_email != ''){
				
				$new_password			= CI_POST('new_password');
				$new_password_repeat	= CI_POST('new_password_repeat');	
				$user_unique_key 		= $this->_user_unique_key; //获取当前用户GUID
				
				$this->form_validation->set_rules('new_password', '新密码', 'required|callback_password_check');
				$this->form_validation->set_rules('new_password_repeat', '重复确认密码', 'required|matches[new_password]');
			
				if($this->form_validation->run() && $this->user_lib->reset_password($user_unique_key, $new_password)){
					$this->ajax_ini();
				} else {
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
			} else {
				$this->_ajax_message = '请首先设置一个电子邮箱，否则不能设置或更新登录密码';
			}
			
			$this->ajax_response();
		}
		
		//用户要求重新发送验证邮件
		public function ajax_send_verification_email(){
			
			if(!CI_POST('send_verification_email')) return;
			$this->ajax_members_only();
			
			$this->load->library('email'); //加载邮件发送库
			$email = $this->_email; //获取当前用户点电子邮件
			$user_unique_key = $this->_user_unique_key; //获取当前用户unique_key
			
			if($email != ''){
				$this->user_lib->send_verification_link($email, $user_unique_key); //发送邮件
				$this->ajax_ini();
			} else {
				$this->_ajax_message = '您尚未设置电子邮箱';
			}
			
			$this->ajax_response();
		}
		
		//判断一个用户是否可以解绑他的微信账号
		public function ajax_is_sns_removal_allowed(){
			
			if(!CI_POST('is_sns_removal_allowed')){
				return;
			}
			
			/**
			$this->ajax_members_only();
			
			$user_guid = $this->_guid;
			
			$email 	= $this->_email;
			$is_user_password_set =  $this->user_lib->is_user_password_set($user_guid);
			
			$result = false;
			$message = '尚未设置邮箱和密码，不能解除微信绑定';
			
			if($email != '' && $is_user_password_set)
			{
				$result = true;
				$message = '';
			}
			*/
			
			$result = false;
			$message = '暂不提供微信解绑功能';
			
			$data = array('is_sns_removal_allowed' => $result, 'message' => $message);
			
			$this->ajax_ini($data);
			$this->ajax_response();
		}
		
		// ********************************************************
		// 验证用户邮箱的相关方法
		// ********************************************************
		
		
		//验证用户的电子邮件是否有效
		function verify($user_unique_key = '', $encrypted_str = ''){	
			if($this->user_lib->verify_an_email($user_unique_key, $encrypted_str)){
				$header = "操作成功";
				$type 	= "success";
				$message = '您成功验证了您的电子邮箱';
				$button = array('text' => '立即登录', 'link' => '');
				
				//$data = array('message' => $header, 'status' => $type);
			} else {
				$msg = $this->my_lib->get_a_msg();
				$type = 'error';
				$header = '操作失败';
				$message = $msg['message'];
				$button = array('text' => '返回', 'link' => '');
			}
			
			$this->load_msg_page($type, $header, $message, $button);
		}
		
		// ********************************************************
		// 忘记密码相关方法
		// ********************************************************
		
		//用户忘记密码
		public function forgot_password(){
			
			$this->unregistered_only('user/edit_account'); //如果用户是已登录用户，将跳转到首页
			$this->load->library('email');
			
			$data = array('email' => '', 'error_str' => '');
			
			if(CI_POST('forgot_password')){
				$email = CI_POST('email');
				$this->form_validation->set_rules('email', '电子邮箱', 'required|valid_email|callback_is_existent[users.email]');
				
				if($this->form_validation->run() AND $this->user_lib->request_password_reset($email)){
					$msg 	= "我们刚刚给您发了一封电子邮件。请查收邮件，根据提示，重置密码。";
					$header	= "请求重置密码成功";
					$this->template->title($header, $this->config->item('site_name'));
					$button = array('button_text' => '登录', 'button_link' => base_url('user/login'));
					return $this->my_lib->feedback($header, 'success', $msg, $button);
				} else {
					$data['error_str'] = $this->my_lib->generate_error_message();
				}
			}
			
			$this->template->set('page','account');
			$this->template->title('找回密码', $this->config->item('site_name'));
			$this->template->build('user/new/forgot_password', $data);	
		}
		
		//用户设置新密码
		public function reset_password($user_unique_key = '', $encrypted_str = ''){
		
			//如果用户是已登录用户，将跳转到首页
			$this->unregistered_only('user/edit_account');
			
			if(!$this->user_lib->check_password_reset_link($user_unique_key, $encrypted_str)){
				$msg = $this->my_lib->get_a_msg();
				$message = $msg['message'];
				
				
				$this->template->title('重置密码', $this->config->item('site_name'));
				return $this->my_lib->feedback('链接验证失败', 'error', $message);
						
			} else {
			
			}
			
			//$user_unique_key 	= CI_POST('user_unique_key');
			//$encrypted_str		= CI_POST()
			
		}
		
		// ********************************************************
		// 用户退出系统
		// ********************************************************
		
		//用户退出
		function logout(){
			$this->user_lib->logout($this->_user_unique_key);
			redirect('home');
		}
		
		// ********************************************************
		// 用户用户账号信息
		// ********************************************************
		
		function profile(){
		
			$user_info = $this->user_lib->get_user_info($this->_guid);
			
			$this->template->set('page','home account-page');
			$this->template->set_partial('navigation','partials/new_vertical_navigation', array('highlight' => ''));
			
			$action = '我的主页';
			
			$arr = array('header_type' => 'header', 'text' => $action);
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			$this->template->set_partial('sidebar', 'global/user_center_sidebar', array('current' => $action));
			
			$this->template->set_layout('new_layout_3');			
			$this->template->build('user/new/profile', $user_info);
		}
		
		function edit_profile(){
		
			$this->members_only();
			
			$user_guid = $this->_guid;
			$user = $this->user_lib->get_user_info($user_guid, array('username', 'bio', 'signature','unique_key'));
			
			$data = array(
				'username' 	=> $user['username'],
				'bio' 		=> $user['bio'], 
				'signature' => $user['signature'],
				'unique_key'=> $user['unique_key']
			);
			
			if(CI_POST('edit_profile')){
			
				$is_username_changed 	= false;
				$is_signature_changed 	= false;
				$is_bio_changed			= false;
				
				$username	= CI_POST('username');
				$signature 	= CI_POST('signature');
				$bio 		= CI_POST('bio');
				
				$this->form_validation->set_rules('username', '用户姓名', 'required');
				
				if($username != $user['username']) $is_username_changed = true;
				if($signature != $user['signature']) $is_signature_changed = true;
				if($bio != $user['bio']) $is_bio_changed = true;
				
				$data['username']	= $username;
				$data['bio'] 		= $bio;
				$data['signature'] 	= $signature;
				
				if(!$is_username_changed AND !$is_bio_changed AND !$is_signature_changed){
					$this->_msg = array('message' => '您没有进行任何操作', 'type' => 'warning');
				} else {
					if($this->form_validation->run() AND $this->user_lib->update_profile($this->_guid, $data)){
						
						//如果用户的用户名被更新，在“SESSION”中更新用户名
						$this->_msg = array('message' => '您的个人资料设置成功', 'type' => 'success');	
						
						if($is_username_changed){
							$this->session->set_userdata(array('username' => $username));
						}
					
					} else {
						$this->_msg = array('message' => $this->my_lib->generate_error_message(), 'type' => 'error');
					}
				}
			}
			
			$this->show_global_msg();
			$this->template->set('page','account-page account-avatar');
			
			$params = array('highlight' => 'setting', 'is_mobile_sidebar_set' => false);
			$this->template->set_partial('navigation','partials/new_vertical_navigation', $params);
			
			$action = '个人资料';
			
			$arr = array('header_type' => 'header', 'text' => $action);
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			$this->template->set_partial('sidebar', 'global/user_center_sidebar', array('current' => $action));
			
			$this->template->set_layout('new_layout_3');			
			$this->template->build('user/new/edit_profile', $data);
		}
				
		// ********************************************************
		// 其它相关方法
		// ********************************************************
		
		//验证密码是否符合密码规则
		function password_check($str){
			return $this->form_validation->password_check($str);
		}
		
		//验证当前密码是否正确
		function is_current_password_correct($str){
			$user_guid = $this->_guid;
			$user = $this->user_lib->get_user_info($user_guid, array('salt', 'password'));
			
			if($user['password'] == md5($str.$user['salt'])){
				return true;
			} else {
				$this->form_validation->set_message('is_current_password_correct','您输入当前密码有误，请重新输入!');
				return false;
			}
		}
		
		//验证用户名是否符合用户名规则
		function username_check($str){
			return $this->form_validation->username_check($str);
		}
		
		//验证验证码
		function captcha_check($str){
			return $this->form_validation->captcha_check($str);
		}
		
		//生成用户账号设置的左侧垂直导航
		private function generate_edit_account_vertical_menu($on){			
			$text = '更新登录账号';
			$vertical_menu[0]['text'] = $text;
			$vertical_menu[0]['url'] = base_url('user/edit_account');
			$vertical_menu[0]['on'] = iif($text == $on, True, False);
			
			$text = '更新个人信息';
			$vertical_menu[1]['text'] = $text;
			$vertical_menu[1]['url'] = base_url('user/edit_profile');
			$vertical_menu[1]['on'] = iif($text == $on, True, False);
			
			$text = '更新头像';
			$vertical_menu[2]['text'] = $text;
			$vertical_menu[2]['url'] = base_url('user/edit_avatar');
			$vertical_menu[2]['on'] = iif($text == $on, True, False);
			
			return $vertical_menu;
		}
		
		//修改用户账号信息（电子邮箱和密码）
		function edit_account(){
			$this->members_only();
		
			//从SESSION中获取用户信息
			$user_guid 	= $this->_guid;
			$user_email	= $this->_email;
			
			$data = array('email' => $this->_email);
			
			//判断用户是否有设置密码
			$data['is_user_password_set'] =  $this->user_lib->is_user_password_set($user_guid);
			
			//获取用户微信的账号信息
			$data['wechat'] = $this->user_lib->get_user_social_media($user_guid);

			$this->template->set('page','account-page');
			
			$params = array('highlight' => 'setting', 'is_mobile_sidebar_set' => false);
			$this->template->set_partial('navigation','partials/new_vertical_navigation', $params);
			
			$action = '账号设置';
			$arr = array('header_type' => 'header', 'text' => $action);
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			$this->template->set_partial('sidebar', 'global/user_center_sidebar', array('current' => $action));
			
			$this->template->set_layout('new_layout_3');			
			$this->template->build('user/new/edit_account', $data);
		}
	}