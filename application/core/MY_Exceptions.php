<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
	
	class MY_Exceptions extends CI_Exceptions
	{
		public function __construct()
		{
			parent::__construct();
		}
		
		function show_404($page = '', $log_error = TRUE)
		{	
			$heading = "404";
			$message = "您请求的页面不存在！";

			// By default we log this, but allow a dev to skip it
			if ($log_error)
			{
				log_message('error', '404 Page Not Found --> '.$page);
			}

			echo $this->show_error($heading, $message, 'error_404', 404);
			exit;
		}
	}