<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

	function CI_POST($post_data, $allowable_tags = '')
	{
		$ci=& get_instance();
		$post_data = $ci->input->post($post_data);
		
		if(gettype($post_data) == 'string')
		{
			$post_data = filter_str($post_data, $allowable_tags);
		}
		
		return $post_data;
	}
	
	function filter_str($str, $allowable_tags = '')
	{
		$str = trim($str);
		return strip_tags($str, $allowable_tags);
	}
	
	function config($item)
	{
		$ci=& get_instance();
		return $ci->config->item($item);
	}
	
	function CI_GET($get_data)
	{
		$ci=& get_instance();
		return $ci->input->get($get_data);
	}
	
	function iif($bool=TRUE, $tstr='', $fstr='')
	{
		if($bool) $str = $tstr;
		else $str = $fstr;
		return $str;
	}
	
	function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
	{
		$chars_length = (strlen($chars)-1);//Length of character list
		$string = $chars{rand(0, $chars_length)};//Start our string
			
		//Generate random string
		for($i=1; $i < $length; $i = strlen($string))
		{
			$r = $chars{rand(0,$chars_length)};//Grab a random character from our list
			if ($r != $string{$i - 1}) $string .= $r;//Make sure the same two characters don't appear next to each other
		}
		
		return $string;
	}
	
	function time_std_format($timestamp)
	{
		return date('Y.m.d H:i', $timestamp);
	}
	
	//输入一个1-26的数字，返回对应的字母
	function show_abc($i = 1)
	{
		switch ($i)
		{
			case 1:
				$letter = 'A';
				break;
			case 2:
				$letter = 'B';
				break;
			case 3:
				$letter = 'C';
				break;
			case 4:
				$letter = 'D';
				break;
			case 5:
				$letter = 'E';
				break;
			case 6:
				$letter = 'F';
				break;
			case 7:
				$letter = 'G';
				break;
			case 8:
				$letter = 'H';
				break;
			case 9:
				$letter = 'I';
				break;
			case 10:
				$letter = 'J';
				break;
			case 11:
				$letter = 'K';
				break;
			case 12:
				$letter = 'L';
				break;
			case 13:
				$letter = 'M';
				break;
			case 14:
				$letter = 'N';
				break;
			case 15:
				$letter = 'O';
				break;
			case 16:
				$letter = 'P';
				break;
			default:
				$letter = 'Q';
		}
		
		return $letter;
	}
	
	//积分向货币的转换
	function credit_to_currency_conversion($credits)
	{
		return $credits * 0.1;
	}
	
	//货币向积分的转换
	function currenty_to_credit_conversion($unit_of_currency)
	{
		return $unit_of_currency * 10;
	}
	
	//输入一个mp3文件，生成peak json文件
	function generate_wav($mp3_file)
	{
		$generator_path			= FCPATH."application/third_party/php-waveform-json.php";
		$path_info 				= pathinfo($mp3_file); //获取文件的全部信息
		$output_file_basename	= strtoupper(basename($mp3_file)); //获取文件名
		$output_file_basename	= basename($output_file_basename, '.MP3');
		
		$output_file_extenstion	= $path_info['extension']; //获取文件后缀
		$output_path			= $path_info['dirname']; //获取文件的绝对路径
		
		$output_path			= $output_path.'/'.$output_file_basename.'.json';	
		return exec("php $generator_path $mp3_file > $output_path");
	}
	
	//count Chinese string
	function str_utf8_chinese_word_count($str = ""){
		$str = preg_replace(UTF8_SYMBOL_PATTERN, "", $str);
		return preg_match_all(UTF8_CHINESE_PATTERN, $str, $textrr);
	}
	
	// count both chinese and english
	function str_utf8_mix_word_count($str = ""){
		$str = preg_replace(UTF8_SYMBOL_PATTERN, "", $str);
		return str_utf8_chinese_word_count($str) + str_word_count(preg_replace(UTF8_CHINESE_PATTERN, "", $str));
	}

	function __time($time)
	{
		$t=time()-$time;
		$f=array(
			'31536000'=>'年',
			'2592000'=>'个月',
			'604800'=>'星期',
			'86400'=>'天',
			'3600'=>'小时',
			'60'=>'分钟',
			'1'=>'秒'
		);
		foreach ($f as $k=>$v)
			if (0!=$c=floor($t/(int)$k)){
				$m = floor($t%$k);
				foreach($f as $x=>$y)
					if (0!=$r=floor($m/(int)$x))
					 return $c.$v.$r.$y.'前';
				return $c.$v.'前';
		}
	}
	
	/*
	 * @desc URL安全形式的base64编码 
	 * @param string $str 
	 * @return string 
	 */ 
	function urlsafe_base64_encode($str){
		$find = array("+","/"); 
		$replace = array("-", "_"); 
		return str_replace($find, $replace, base64_encode($str)); 
	}
	
	/** 
	 * generate_access_token 
	 * 
	 * @desc 签名运算 
	 * @param string $access_key 
	 * @param string $secret_key 
	 * @param string $url 
	 * @param array $params 
	 * @return string 
	 */ 
	
	function qiniu_access_token($access_key, $secret_key, $url, $params = ''){
		
		$parsed_url = parse_url($url); 
		$path = $parsed_url['path']; 
		$access = $path; 
		
		if (isset($parsed_url['query'])) $access .= "?" . $parsed_url['query'];
		
		$access .= "\n"; 
		
		if($params){ 
			if(is_array($params)) $params = http_build_query($params);
			$access .= $params; 
		}
		
		$digest = hash_hmac('sha1', $access, $secret_key, true); 
		return $access_key.':'.$this->urlsafe_base64_encode($digest); 
	}

	//curl post method
	function tran_curl_data($url, $data, $func)
	{
		$ch = curl_init();

		if (strtolower($func) == 'get' || strtolower($func) == 'put' || strtolower($func) == 'delete')
		{
			$url = $url . "?" . http_build_query($data);
		}

		switch($func)
		{
			case 'get':
				$func = 'GET';
				break;
			case 'post':
				$func = 'POST';
				break;
			case 'put':
				$func = 'PUT';
				break;
			case 'delete':
				$func = 'DELETE';
				break;
			default:
				$func = 'POST';
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $func);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$result = curl_exec($ch);

		curl_close($ch);
		return $result;
	}
	
	//将秒转化为时分秒
	function convert_seconds($seconds){
		
		$arr = explode(':', gmdate("H:i:s", $seconds));
		
		$hour = (int)$arr[0];
		$minute = (int)$arr[1];
		$seconds = (int)$arr[2];
		
		$output = '';
		
		if($hour > 0){
			$output .= $hour.'小时';
			if($minute > 0) $output .= $minute.'分';
		} else {
			if($minute > 0) $output .= $minute."分";
			if($seconds > 0) $output .= $seconds.'秒';
		}
			
		return $output;
	}
	
	
	
?>