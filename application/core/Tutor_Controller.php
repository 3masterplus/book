<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tutor_Controller extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
		
		$this->load_partials();
		$this->get_primary_menu($this->my_lib->get_session_login_status());
	}
	
	private function load_partials()
	{
		$this->template->set_partial('meta','partials/meta');
		$this->template->set_partial('footer', 'partials/footer');
		$this->template->set_partial('endmeta', 'partials/endmeta');
		return;
	}
	
	private function get_primary_menu($is_login)
	{
		$user_unique_key = $this->my_lib->get_session_user_unique_key();
		$avatar_url = $this->my_lib->get_user_avatar($user_unique_key, 80, 80);
		$data = array('is_login' => $is_login, 'username' => $this->my_lib->get_session_username(), 'avatar_url' => $avatar_url);		
		$this->template->set_partial('primary_menu','partials/primary_menu', $data);
		return;
	}
}

