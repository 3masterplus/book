<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class MY_lib
	{
		var $_user_identifier;
		var $_device;
		var $_platform;
		var $_is_mobile_browser;
		var $_is_wechat_browser;
		var $_is_robot;
		var $_guid;
		var $_username;
		var $_group_id;
		var $_is_login;
		var $_email;
		var $_is_email_verified;
		var $_user_unique_key;
		var $_avatar_url;
		var $_user_agent;
		var $_ip_address;
		var $_session_id;
		var $_class;
		var $_method;
		var $_msg;
		var $_referrer;
		
		function __construct()
		{
			$this->ci = & get_instance();
			$this->_user_identifier		= $this->get_session_user_identifier();
			$this->_device				= $this->detect_device();
			$this->_platform			= $this->detect_platform();
			$this->_is_mobile_browser	= $this->is_mobile_browser();
			$this->_is_wechat_browser	= $this->is_wechat_browser();
			$this->_is_robot			= $this->is_robot();
			$this->_guid				= $this->get_session_guid();
			$this->_username			= $this->get_session_username();
			$this->_user_group_id		= $this->get_session_user_group_id();
			$this->_is_login			= $this->get_session_login_status();
			$this->_email				= $this->get_session_email();
			$this->_is_email_verified	= $this->get_session_is_email_verified();
			$this->_user_unique_key		= $this->get_session_user_unique_key();
			$this->_avatar_url			= $this->get_session_avatar_url();
			$this->_user_agent			= $this->ci->input->user_agent();
			$this->_ip_address			= $this->ci->input->ip_address();
			$this->_session_id			= $this->ci->session->session_id;
			$this->_class				= $this->ci->router->fetch_class();
			$this->_method				= $this->ci->router->fetch_method();
			$this->_msg					= $this->get_a_msg();
			$this->_referrer			= $this->ci->agent->referrer();
			
			$this->ci->load->model('Core_model','core_model');
		}

		// ********************************************************
		// 获取当前用户的ID, 登录状态，以及用户组ID
		// ********************************************************

		//获取当前用户的“guid”, 如果当前用户为未登陆登录用户，“guid” 为 “0”。
		function get_session_guid()
		{
			return ($this->ci->session->userdata('guid')) ? $this->ci->session->userdata('guid') : 0;
		}
		
		//获取当前用户的“username”, 如果当前用户为未登陆登录用户，“username” 为空。
		function get_session_username(){
			return ($this->ci->session->userdata('username')) ? $this->ci->session->userdata('username') : '';
		}

		//获取当前用户的登录状态“is_login”，登陆用户的登陆状态为“TRUE”，否则为“FALSE”。
		function get_session_login_status()
		{
			return ($this->ci->session->userdata('is_login')) ? TRUE : FALSE;
		}

		//获取当前用户的“group_id”，如果当前用户未登录，“group_id” 为 “0”。
		function get_session_user_group_id()
		{
			return ($this->ci->session->userdata('user_group_id')) ? $this->ci->session->userdata('user_group_id') : 0;
		}

		//获取当前用户的“email”，如果当前用户未登录，“email”为空。
		function get_session_email()
		{
			return ($this->ci->session->userdata('email')) ? $this->ci->session->userdata('email') : '';
		}
		
		//获取当前用户点邮箱地址是否已经验证
		function get_session_is_email_verified()
		{
			return ($this->ci->session->userdata('is_email_verified')) ? $this->ci->session->userdata('is_email_verified') : 0;
		}
		
		//获取当前用户的“user_unique_key”，如果当前用户未登录，“user_unique_key”为空。
		function get_session_user_unique_key()
		{
			return ($this->ci->session->userdata('user_unique_key')) ? $this->ci->session->userdata('user_unique_key') : '';
		}
		
		//获取当前用户的“avatar_url”, 如果当前用户未登录，“avatar_url” 为空。
		function get_session_avatar_url()
		{
			return ($this->ci->session->userdata('avatar_url')) ? $this->ci->session->userdata('avatar_url') : '';
		}
		
		//获取用户的头像地址
		function get_user_avatar($user_unique_key, $width = 200, $height = 200)
		{
			$default_avatar_url = config('default_avatar_url'); //设置默认的“avatar_url”
			
			if($user_unique_key == '')
			{
				$avatar_url = $this->generate_avatar($default_avatar_url, $width, $height, $user_unique_key);
			}
			else
			{
				if($user_unique_key == $this->_user_unique_key)
				{
					if(!file_exists(FCPATH.$this->_avatar_url))
					{
						$avatar_url = $this->generate_avatar($default_avatar_url, $width, $height, $user_unique_key);
					}
					else
					{
						$avatar_url = $this->generate_avatar($this->_avatar_url, $width, $height, $user_unique_key);
					}
				}
				else
				{
					//获取用户的头像地址
					$condition = array('unique_key' => $user_unique_key);
					$user_avatar_url = $this->get_a_value('users', 'avatar_url', $condition);
					
					if($user_avatar_url != '' AND file_exists(FCPATH.$user_avatar_url))
					{
						$avatar_url = $this->generate_avatar($user_avatar_url, $width, $height, $user_unique_key);
					}
					else
					{
						$avatar_url = $this->generate_avatar($default_avatar_url, $width, $height, $user_unique_key);
					}
				}
			}
			
			return $avatar_url;
		}
		
		//生成缩略图的私有方法
		private function generate_avatar($image_path, $width, $height, $user_unique_key)
		{
			
			if(file_exists(FCPATH.$image_path))
			{
				return base_url($image_path);
			}
			else
			{
				$pathinfo 			= pathinfo($image_path); //获取文件的“pathinfo”的信息
				$image_ext 			= pathinfo($image_path, PATHINFO_EXTENSION); //获取图片的后缀
			
				$image_base_name 	= iif($image_path == config('default_avatar_url'), 'default_avatar', $user_unique_key);
				$image_filename		= $image_base_name.'_'.$width.'_'.$height;
			
				$upload_path		= $pathinfo['dirname'];
				
				$this->ci->load->library('file_lib');				
				return $this->ci->file_lib->resize_an_image($image_path, $width, $height, $upload_path, $image_filename);
			}
		}
		
		// ********************************************************
		// 设置、读取用户“identifier”的方法
		// ********************************************************
		
		/**
			获取当前用户的“user_identifier”，如果用户的“session”中不存在，就从“cookie”中读取
			或者给该用户创建一个新的“identifier”。
		*/
		function get_session_user_identifier()
		{
			return ($this->ci->session->userdata('user_identifier')) ? $this->ci->session->userdata('user_identifier') : $this->get_user_identifier_from_cookie();
		}
		
		//通过“cookie”设置或读取来自浏览器访问用户的“identifier”
		private function get_user_identifier_from_cookie()
		{
			$user_identifier_cookie = get_cookie($this->ci->config->item('user_identifier_cookie'));
			
			if(strlen($user_identifier_cookie) > 0)
			{
				$arr = unserialize($user_identifier_cookie);
				$user_identifier = $arr['user_identifier'];
			}
			else
			{
				$user_identifier = $this->generate_entity_unique_key(rand_str(32));
				$this->set_user_identifier_to_cookie($user_identifier); //将用户“identifier”写入cookie
			}
			
			//将从“cookie”读出的或刚刚生成的“identifier”写入“session”
			$this->set_user_identifier_to_session($user_identifier);
			
			return $user_identifier;
		}
		
		//将“user_identifier”保存到“session”和“cookie”中
		protected function set_user_identifier($user_identifier)
		{
			$this->set_user_identifier_to_session($user_identifier);
			$this->set_user_identifier_to_cookie($user_identifier);
			return;
		}
		
		//将“user_identifier”保存到“session”
		protected function set_user_identifier_to_session($user_identifier)
		{
			$data = array('user_identifier' => $user_identifier);
			return $this->ci->session->set_userdata($data);
		}
		
		//将“user_identifier”保存到“cookie”
		protected function set_user_identifier_to_cookie($user_identifier)
		{	
			//序列化数据
			$value = serialize(array('user_identifier' => $user_identifier));
			
			//设置过期时间
			$expire = $this->ci->config->item('user_identifier_cookie_expiration');
			
			//拼接“cookie”数据
			$cookie = array(
				'name'	=> $this->ci->config->item('user_identifier_cookie'),
				'value' => $value,
				'expire' => $expire
			);
			
			//生成“cookie”
			return set_cookie($cookie);
		}
		
		// ********************************************************
		// 获取设备信息等方法
		// ********************************************************
		
		//判断是否是手机浏览器访问
		function is_mobile_browser()
		{
			RETURN $this->ci->agent->is_mobile();
		}
		
		//判断是否是爬虫访问
		function is_robot()
		{
			RETURN $this->ci->agent->is_robot();
		}
		
		//判断是否是微信内置浏览器访问
		function is_wechat_browser()
		{
			RETURN FALSE;
		}
		
		//判断访问设备
		function detect_device()
		{
			return 'pc';
		}
		
		//判断访问操作系统
		function detect_platform()
		{
			return $this->ci->agent->platform();
		}
		
		// ***************************************************
		// 获取和设置交互提示信息
		// ***************************************************

		// 设置信息。信息类型包括: 'info', 'success', 'error'。
		function set_a_msg($str, $type = 'info')
		{
			$msg['msg']['message'] = $str;
			$msg['msg']['type'] = $type;
			RETURN $this->ci->session->set_userdata($msg);
		}

		// 获取提示信息，提示信息是一个数组，包含信息以及信息类型。
		function get_a_msg()
		{
			$msg = $this->ci->session->userdata('msg');
			$this->ci->session->unset_userdata('msg');
			RETURN $msg;
		}
		
		function generate_error_message($output = 'error_str')
		{
			$error_arr = array();
			$error_arr = array_merge($error_arr, array_values($this->ci->form_validation->error_array()));
					
			$msg = $this->get_a_msg();
			if(count($msg) > 0) $error_arr = array_merge($error_arr, array($msg['message']));
			
			if($output == 'error_str')
			{
				return $error_arr[0];
			}
			else
			{
				return $error_arr;
			}
		}

		// ***************************************************
		// 数据库的插入、更新、删除、查询
		// ***************************************************

		// 向数据库里插入一条记录的方法，返回被插入数据的ID
		function create_a_record($table, $data)
		{
			return $this->ci->core_model->insert_a_row($table, $data);
		}

		// 更新数据库里的一条记录，如果更新成功返回true, 否则返回false
		function update_records($table, $data, $condition)
		{
			if(count($condition) > 0 OR strlen($condition) > 0) return $this->ci->core_model->update_rows($table, $data, $condition);
			return FALSE;
		}

		// 删除数据库里的记录 返回？？？？
		function delete_records($table, $condition)
		{
			if(count($condition) > 0 OR strlen($condition) > 0) return $this->ci->core_model->delete_rows($table, $condition);
			return FALSE;
		}

		// 从单表中获取查询数据
		function get_records($table, $field, $condition, $limit = NULL, $offset = 0, $orderby = NULL, $sort = 'DESC', $forcount = FALSE)
		{
			$field = is_array($field) ? $field : array($field);
			$field = implode(',', $field);
			$query = $this->ci->core_model->select_rows($table, $field, $condition, $limit, $offset, $orderby, $sort, $forcount);
			if($forcount) return $query;
			else return $query->result_array();
		}

		// 根据SQL直接从数据库中查询数据
		function get_records_with_sql($sql, $limit = NULL, $offset = 0, $orderby = NULL, $sort = 'DESC', $forcount = FALSE)
		{
			if($forcount)
			{
				$query = $this->ci->core_model->select_rows_with_sql($sql);
				return $query->num_rows();
			}
			else
			{
				$query = $this->ci->core_model->select_rows_with_sql($sql, $limit, $offset, $orderby, $sort);
				return $query->result_array();
			}
		}
		
		// ***************************************************
		// 基本的数据库查询
		// ***************************************************

		// 检查数据库某表中是否存在满足条件的记录, 返回值为TRUE或FALSE
		function check_a_record($table, $condition)
		{
			if($this->get_records($table, array(1), $condition, NULL, NULL, NULL, NULL, TRUE) > 0) return TRUE;
			return FALSE;
		}
		
		// 检查一个“entity”可否被删除
		function is_entity_deletable($guid)
		{
			return !$this->check_a_record('entities', array('father_guid' => $guid));
		}

		// 这个方法的目的就是通过输入一个唯一值来定位某一个表中的唯一条记录的某一个值, 比如通过一个用户的用户名来查找某一个用户的电子邮箱等.
		function get_a_value($table, $key, $condition)
		{
			$query = $this->get_records($table, array($key), $condition);
			if(count($query) == 1) return $query[0][$key];
			return NULL;
		}

		// ***************************************************
		// 和ENTITY相关的方法
		// ***************************************************
		
		// 随机生成一个专门用于保存在“entity”表中的“unique_key”
		function generate_entity_unique_key($str = '')
		{
			return md5(time().$str.$this->_user_agent.rand_str());
		}
		
		// 用于创建一个ENTITY的方法
		function create_an_entity($owner_guid, $subtype, $father_guid = 0, $visibility = TRUE, $unique_key, $title = '', $main = '', $weight = 0)
		{
			$subtype_id = $this->get_subtype_id($subtype);
			
			$current_time = time();
			
			$data = array(
				'father_guid'	=> $father_guid,
				'subtype_id'	=> $subtype_id,
				'owner_guid'	=> $owner_guid,
				'time_created'	=> $current_time,
				'time_updated'	=> $current_time,
				'visibility'	=> $visibility,
				'unique_key'	=> $unique_key,
				'title'         => $title,
				'main'          => $main,
				'weight'		=> $weight
			);
			
		 	return $this->create_a_record('entities', $data);
		}
		
		//根据“subtype”的名称获取“subtype_id”
		function get_subtype_id($subtype)
		{
			return $this->get_a_value('subtypes', 'id', array('subtype' => $subtype));
		}
		
		//根据“subtype_id”获取“subtype”名称
		function get_subtype($subtype_id)
		{
			return $this->get_a_value('subtypes', 'subtype', array('id' => $subtype_id));
		}
		
		//根据“unique_key”获取“guid”
		function get_guid_by_unique_key($unique_key)
		{
			return $this->get_a_value('entities', 'guid', array('unique_key'=>$unique_key));
		}
		
		//根据“guid”获取“unique_key”
		function get_unique_key_by_guid($guid)
		{
			return $this->get_a_value('entities', 'unique_key', array('guid'=>$guid));
		}
		
		/**
			根据“unique_key”或“guid”获取某个“subtype”类型所在表里的数据，
			如“users”、“courses”、“sections”等
		*/
		
		function get_a_subtype_row($table, $key, $fields = array('*'), $condition = array()){
		
			if(gettype($key) == 'string'){
				$condition = array_merge(array('unique_key' => $key), $condition);
			}elseif(gettype($key) == 'integer'){
				$condition = array_merge(array('guid' => $key), $condition);
			}
			
			$arr = $this->get_records($table, $fields, $condition);
			
			if(count($arr) > 0){
				$arr = $arr[0];
			}
			
			return $arr;
		}
		
		/**
			根据“unique_key”或“guid”，针对某个“subtype”类型对应的表进行数据更新
		*/
		function set_a_subtype_row($table, $key, $data)
		{
			if(gettype($key) == 'string')
			{
				$condition = array('unique_key' => $key);
			}
			elseif(gettype($key) == 'integer')
			{
				$condition = array('guid' => $key);
			}
		
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			$this->update_records($table, $data, $condition);
			
			$data = array('title' => $data['title'], 'main' => $data['main']);
			$this->update_records('entities', $data, $condition);
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			return $this->ci->db->trans_status();
		}
		
		/**
		 * 接口格式化输出
		 * @param  array  $data   		接口具体数据
		 * @param  string $message		操作结果的返回信息，默认为“ok”
		 * @param  string $type			信息类型，默认为“info”，如果有错误发生，类型为“warning”		
		 * @param  string $result		接口请求结果状态 'success' | 'failure'  //调用成功|调用失败
		 * @return json
		 */
		 
		function response($data = array(), $message = 'ok', $type = 'info', $result = 'success')
		{
			$this->ci->load->library('javascript');
			header("Content-Type: application/json; charset=utf-8");
			$msg = array('message' => $message, 'type' => $type);
			$json_data = array('result'=>$result, 'msg'=>$msg, 'data'=>$data);
			$response = $this->ci->javascript->generate_json($json_data, TRUE);
			echo $response;
			exit();
		}
		
		//在某个操作完成之后，现实feedback页面		
		function feedback($header, $status = 'success', $message = '', $button = array()){
		
			$data = array(
				'message' 	=> $message,
				'status' 	=> $status,
				'header' 	=> $header,
				'button'	=> $button
			);
			
			$this->ci->template->set('page','account');
			return $this->ci->template->build('global/new_info', $data);
		}
		
		//让一个ENTITY可见
		function activate_an_entity($guid){
			return $this->update_records('entities', array('visibility' => 1), array('guid' => $guid));
		}
		
		//让一个ENTITY不可见
		function deactivate_an_entity($guid){
			return $this->update_records('entities', array('visibility' => 0), array('guid' => $guid));
		}
		
	}