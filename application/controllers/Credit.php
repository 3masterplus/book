<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

	class Credit extends Client_Controller
	{	
		function __construct()
		{
			parent::__construct();
			$this->load->library('credit_lib');
		}
	}