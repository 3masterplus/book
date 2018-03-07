<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

	class Home extends MY_Controller{
		
		function __construct(){
			parent::__construct();
			$this->template->set_partial('meta','partials/new/new_meta');
			$this->template->set_partial('endmeta', 'partials/new/new_endmeta');
			$this->template->set_layout('new_layout_5');
		}
		
		function index(){
			$this->unregistered_only('/dashboard');
			$this->template->title('吾生也有涯，而知也无涯', $this->config->item('site_name'));
			$this->template->build('home/index_home_new');
		}
	}