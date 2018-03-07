<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Client_Controller extends MY_Controller
	{
		function __construct()
		{
			parent::__construct();
			$this->load_partials();
		}
	
		private function load_partials()
		{
			$this->template->set_partial('meta','partials/new/new_meta');
			$this->template->set_partial('footer', 'partials/new/new_footer');
			$this->template->set_partial('endmeta', 'partials/new/new_endmeta');
			
			$this->template->set('is_login', $this->_is_login);
			$this->template->set('email', $this->_email);
			$this->template->set('is_email_verified', $this->_is_email_verified);
		}
	}