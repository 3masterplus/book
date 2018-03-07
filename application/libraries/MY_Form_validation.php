<?php defined('BASEPATH') OR exit('No direct script access allowed');

	class MY_Form_validation extends CI_Form_validation
	{
		function __construct($rules = array() )
		{
			parent::__construct( $rules );
			$this->ci =& get_instance();
			$this->message_rewrite();
		}
		
		function message_rewrite()
		{
			$this->set_message('required','您没有输入%s');
			$this->set_message('is_unique','您输入的%s已存在');
			$this->set_message('min_length','%s至少要包含个%s字符');
			$this->set_message('max_length','%s最多只能包含个%s字符');
			$this->set_message('valid_email','您输入的邮箱格式不正确');
			$this->set_message('matches','您两次输入的密码不一致');
			$this->set_message('password_check','密码不符合规范，密码仅可包含英文字母、数字、及特殊字符（!@#$%&*^&*_-）');
			$this->set_message('username_check','用户昵称仅可包含中英文字符和数字，不可包含空格');
			$this->set_message('captcha_check','您输入的验证码无效');
			$this->set_message('is_existent','您输入的%s不存在');
			$this->set_message('file_path_check','您输入的%s目录地址不存在');
			$this->set_error_delimiters('', '');
		}
		
		function file_path_check($file_path)
		{
			return file_exists($file_path);
		}
		
		function password_check($str)
		{
			if(!preg_match('/^[0-9A-Za-z!@#$%&*^&*_-]{3,15}$/', $str)) return FALSE;
			return TRUE;
		}
			
		function username_check($str)
		{
			if(!preg_match('/^[\x{4e00}-\x{9fa5}\w-]+$/u', $str)) return FALSE;
			return TRUE;
		}
		
		function is_existent($str, $field)
		{
			list($table, $field) = explode('.', $field);
			$query = $this->ci->db->limit(1)->get_where($table, array($field => $str));
			if($query->num_rows() == 0) return FALSE;
			return TRUE;
    	}
    	
    	function captcha_check($captcha)
		{
			return $this->ci->captcha_lib->captcha_check($captcha);
		}

		public function clear_field_data() 
		{
	        $this->_field_data = array();
	        return $this;
	    }
		
	}