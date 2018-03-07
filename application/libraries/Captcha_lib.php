<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
	
	class Captcha_lib extends MY_lib
	{
		var $_word_length;
		var $_img_path;
		var $_img_url;
		var $_font_path;
		var $_img_width;
		var $_img_height;
		var $_expiration;
		var $_word;
		
		function __construct()
		{
			parent::__construct();
			$this->ci =& get_instance();
			
			// $this->_word_length = 4;
			// $this->_word 		= rand_str($this->_word_length);
			// $this->_img_path	= FCPATH.'captcha/images/';
			// $this->_img_url		= site_url('captcha/images/');
			// $this->_font_path	= FCPATH.'catcha/fonts/windmaker.ttf';
			// $this->_expiration	= 7200;
			// $this->_img_width	= 120;
			// $this->_img_height	= 40;
		


			$this->_word_length   = 4;
			$this->_word          = rand_str($this->_word_length);
			$this->_img_path      = FCPATH.'captcha/images/';
			$this->_img_url       = site_url('captcha/images/').'/';
			$this->_font_path     = FCPATH.'catcha/fonts/windmaker.ttf';
			$this->_expiration    = 7200;
			$this->_width         = 120;
			$this->_height        = 40;
			$this->_minWordLength = 4;
			$this->_maxWordLength = 4;
			$this->_spots         = 20;
			$this->lines          = 3;

		}
	
		//生成验证码
		function create_a_captcha($ip_address)
		{

			require_once APPPATH . 'third_party/cool-captcha/Captcha.php';
			$vals = array(

				'img_path'		=> $this->_img_path,
				'img_url'		=> $this->_img_url,
				'minWordLength' => $this->_minWordLength,
				'maxWordLength' => $this->_maxWordLength,
				'fonts' => array(
					'VeraSansBold'    => array('spacing' => 0, 'minSize' => 18, 'maxSize' => 18, 'font' => 'VeraSansBold.ttf')
				),
				'width'      => $this->_width,
				'height'     => $this->_height,
				'expiration' => $this->_expiration,
			);
			$cap = new TMTCaptcha($vals);
			$captcha = $cap->createImage();
			$this->_word = $captcha['word'];




			// $this->ci->load->helper('captcha');
			// $vals = array(
			// 	'word'			=> $this->_word,
			// 	'img_path'		=> $this->_img_path,
			// 	'img_url'		=> $this->_img_url,
			// 	'font_path'		=> $this->_font_path,
			// 	'img_width'		=> $this->_img_width,
			// 	'img_height'	=> $this->_img_height,
			// 	'expiration'	=> $this->_expiration,
			// 	'word_length'	=> $this->_word_length
			// );
			// $captcha = create_captcha($vals);//create a captcha image
			
			$this->insert_a_captcha_record($this->_word, $ip_address);//将“captcha”存入到数据库
			return $captcha;
		}
		
		//将生成的验证码存入数据库
		private function insert_a_captcha_record($word, $ip_address)
		{
			$data = array('word'=>$word, 'ip_address'=>$ip_address,'captcha_time'=>time());
			return $this->create_a_record('captcha', $data);
		}
		
		 //检查验证码
		function captcha_check($captcha)
		{		
			$expiration = time() - $this->_expiration;
			$this->delete_expired_captcha($expiration);
			
			$captcha = strtoupper($captcha);
			$condition = array('ip_address' => $this->_ip_address, 'word' => $captcha);
			$query = $this->get_records('captcha', 'captcha_id', $condition);
			
			if(count($query) > 0)
			{
				$this->delete_records('captcha', $condition);
				return TRUE;
			}
			
			return FALSE;
		}
		
		//删除数据库中过期的验证码
		private function delete_expired_captcha($expiration)
		{
			return $this->ci->db->where('captcha_time < ', $expiration) -> delete('captcha');
		}
		
		
	}