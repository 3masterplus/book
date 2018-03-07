<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	var $_user_identifier;
	var $_device;
	var $_platform;
	var $_is_mobile_browser;
	var $_is_wechat_browser;
	var $_is_robot;
	var $_guid;
	var $_username;
	var $_user_group_id;
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
	var $_delimiter;
	
	//为AJAX异步返回准备全局变量数据
	var $_ajax_data;
	var $_ajax_result;
	var $_ajax_message;
	var $_ajax_type;
	
	function __construct()
	{
		parent::__construct();
		
		//加载资源文件
		$this->load->library('MY_lib');
		$this->load->library('river_lib');
		$this->load->library('count_lib');
		
		$this->auto_login();
		
		$this->ini_var();
		
		$this->show_global_msg();
		$this->ajax_ini(array(), 'failure', '操作失败');
		//$this->profiler(true);
		$this->update_last_activity();
	}
	
	function profiler($open = TRUE){
		if($open){
			$this->output->enable_profiler(TRUE);
			$sections = array('config' => TRUE, 'queries' => TRUE, 'benchmarks' => TRUE, 'get' => TRUE);
			return $this->output->set_profiler_sections($sections);
		}
	}
	
	private function ini_var()
	{
		$this->_user_identifier		= $this->my_lib->_user_identifier;
		$this->_device				= $this->my_lib->_device;
		$this->_platform			= $this->my_lib->_platform;
		$this->_is_mobile_browser	= $this->my_lib->_is_mobile_browser;
		$this->_is_wechat_browser	= $this->my_lib->_is_wechat_browser;
		$this->_is_robot			= $this->my_lib->_is_robot;
		
		/**
		$this->_guid				= $this->my_lib->_guid;
		$this->_username			= $this->my_lib->_username;
		$this->_is_login			= $this->my_lib->_is_login;
		$this->_user_group_id		= $this->my_lib->_user_group_id;
		$this->_email				= $this->my_lib->_email;
		$this->_user_unique_key		= $this->my_lib->_user_unique_key;
		$this->_avatar_url			= $this->my_lib->_avatar_url;
		*/
		
		$this->_guid				= $this->my_lib->get_session_guid();
		$this->_username			= $this->my_lib->get_session_username();
		$this->_user_group_id		= $this->my_lib->get_session_user_group_id();
		$this->_is_login			= $this->my_lib->get_session_login_status();
		$this->_email				= $this->my_lib->get_session_email();
		$this->_is_email_verified	= $this->my_lib->get_session_is_email_verified();
		$this->_user_unique_key		= $this->my_lib->get_session_user_unique_key();
		$this->_avatar_url			= $this->my_lib->get_session_avatar_url();
		
		$this->_user_agent			= $this->my_lib->_user_agent;
		$this->_ip_address			= $this->my_lib->_ip_address;
		$this->_session_id			= $this->my_lib->_session_id;
		$this->_class				= $this->my_lib->_class;
		$this->_method				= $this->my_lib->_method;
		$this->_msg					= $this->my_lib->_msg;
		$this->_referrer			= $this->my_lib->_referrer;
		$this->_delimiter 			= $this->config->item('delimiter');
	}
	
	private function auto_login()
	{
		$cookie = get_cookie($this->config->item('autologin_cookie'));
		$is_login = $this->session->userdata('is_login');
		
		if(strlen($cookie) > 0 AND !$is_login)
		{
			$this->load->library('user_lib');
			$arr = unserialize($cookie);
			return $this->user_lib->auto_login($arr['user_unique_key']);
		}
	}
	
	//显示全局提示
	function show_global_msg($from = 'var', $if_show_button = false, $button_text = '', $button_url = '')
	{
		$if_show = FALSE;
		
		if($from == 'var') $msg = $this->_msg;
		elseif($from == 'session') $msg = $this->my_lib->get_a_msg();
		
		if(count($msg) > 0)
		{
			$if_show	= true;
			$message 	= $msg['message'];
			$type		= $msg['type'];
			
			$this->template->set('if_show_button', $if_show_button);
			if($if_show_button)
			{
				$this->template->set('if_show_button', $if_show_button);
				$this->template->set('button_text', $button_text);
				$this->template->set('button_url', $button_url);
			}
			
			$this->template->set('message', $message);
			$this->template->set('type', $type);
		}
		
		return $this->template->set('if_show', $if_show);
	}
	
	//更新登录用户的最后活动时间
	function update_last_activity()
	{
		if($this->_guid > 0)
		{
			$this->my_lib->update_records('entities', array('time_updated' => time()), array('guid' => $this->_guid));
		}
		
		return NULL;
	}
	
	// ********************************************************
	// AJAX相关方法
	// ********************************************************
	
	function ajax_ini($data = array(), $status = 'success', $message = 'ok')
	{
		$this->_ajax_data 		= $data;
		$this->_ajax_message 	= $message;
		$this->_ajax_result 	= iif($status == 'success', 'success', 'failure');
		$this->_ajax_type 		= iif($status == 'success', 'success', 'error');
	}
	
	function ajax_response()
	{
		return $this->my_lib->response($this->_ajax_data, $this->_ajax_message, $this->_ajax_type, $this->_ajax_result);
	}
	
	//验证某个表单中的一个key是否存在
	function is_existent($str, $field)
	{
		return $this->form_validation->is_existent($str, $field);
	}
	
	// ********************************************************
	// 权限控制相关方法
	// ********************************************************
	
	//仅仅是未注册的用户才可用的方法
	function unregistered_only($redirect_url = '/')
	{
		if($this->_is_login)
		{
			redirect(base_url($redirect_url));
		}
	}
	
	//仅仅是未注册的用户才可用的方法
	function members_only($redirect_url = '/')
	{
		if(!$this->_is_login)
		{
			redirect(base_url($redirect_url));
		}
	}
	
	//为ajax方法加上这个操作只有注册用户才可以操作
	function ajax_members_only()
	{
		if(!$this->_is_login)
		{
			$this->ajax_ini(array(), 'failure', '您未登陆，不能做此操作');
			$this->ajax_response();	
			return false;
		}
		
		return true;
	}
	
	//检查“unique_key”是否存在或有效
	function if_entity_valid($unique_key, $if_check_visibility = false)
	{
		$condition = array('unique_key' => $unique_key);
		
		if($if_check_visibility)
		{
			$condition['visibility'] = 1;
		}
		
		if($this->my_lib->check_a_record('entities', $condition))
		{
			return true;
		}
		else
		{
			echo '404';
			exit;
		}
	}
	
	private function check_ownership($entity_guid, $user_guid)
	{
		$condition = array('guid' => $entity_guid);
		$owner_guid = $this->my_lib->get_a_value('entities', 'owner_guid', $condition);
		if($owner_guid == $user_guid)
		{
			return True;
		}
		
		return False;
	}
	
	//这是专为AJAX而构建的方法，检查一个用户是否有权限操作一个“entity”
	function gate_for_ajax($key, $user_guid)
	{
		if(gettype($key) == 'integer')
		{
			$entity_guid = $key;
		}
		else
		{
			$entity_guid = $this->my_lib->get_guid_by_unique_key($key);
		}
		
		$is_ownership = $this->check_ownership($entity_guid, $user_guid);
		
		if($is_ownership)
		{
			return true;
		}
		else
		{
			//$this->ajax_ini(array(), 'failure', '你没有权限进行该操作');
			//$this->ajax_response();	
			//return False;
			
			return true;
		}
	}
	
	//检查一个用户是否有权限操作一个“entity”
	function gate($key, $user_guid)
	{
		/**
		if(gettype($key) == 'integer')
		{
			$entity_guid = $key;
		}
		else
		{
			$entity_guid = $this->my_lib->get_guid_by_unique_key($key);
		}
		
		$is_ownership = $this->check_ownership($entity_guid, $user_guid);
		
		if($is_ownership)
		{
			return True;
		}
		else
		{
			$redirect_url = '/';
			redirect(base_url($redirect_url));
		}
		*/
		
		return true;
	}
	
	//显示单页面全局提示
	function load_msg_page($type, $header, $message = '', $button = array())
	{
		$this->template->set_layout('new_layout_1');
		$this->template->set('page','account');
		$data = array('type' => $type, 'header' => $header, 'message' => $message, 'button' => $button);
		echo $this->template->build('global/new_info', $data);
		exit;
	}
	
}

require(APPPATH.'core/Tutor_Controller.php');
require(APPPATH.'core/Client_Controller.php');