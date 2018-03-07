<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

	class Notification extends Client_Controller
	{	
		function __construct()
		{
			parent::__construct();
			$this->load->library('notify_lib');
			$this->load->library('pagination');
		}
		
		function index()
		{
			$this->get_messages();
		}
		
		//获取消息列表
		function get_messages($page = 1)
		{
			//如果没有登录，重定向到首页
			if (!$this->_guid) {
				redirect(base_url());
			}

			$page    = intval($page) < 1 ? 1 : intval($page);
			$orderby = 'user_notify_relations.time_created';
			$sort    = 'desc';
			$limit   = $this->config->item('per_page');
			$offset  = $limit*($page-1);
			$where   = array(
				'user_notify_relations.user_guid' => $this->_guid,
				'notify.type' => 'notice',
			);
			$select_items = 'notify.*,user_notify_relations.user_guid,user_notify_relations.open_time,user_notify_relations.is_read';
			$items = $this->notify_lib->get_notify_list($select_items, $where, $limit, $offset, $orderby, $sort);
			$this->notify_lib->handle_notify_data($items);
			$count = $this->notify_lib->get_notify_list('notify.id', $where, NULL, 0, NULL, NULL, true);
			$pages_no = ceil($count/$limit);
			//获取下一页信息
			if ($page >= $pages_no) {
				$next_page = false;
			}else {
				$next_page = $page+1;
				$next_page = base_url("/notification/get_messages/{$next_page}");
			}
			//获取上一页信息
			if ($page <= 1) {
				$prev_page = false;
			}else {
				$prev_page = $page-1;
				$prev_page = base_url("/notification/get_messages/{$prev_page}");
			}
			$data = array(
				'items'           => $items,
				'pages_no'        => $pages_no,
				'current_page_no' => $page,
				'next_page'       => $next_page,
				'prev_page'       => $prev_page, 
			);
			// var_dump($data);exit;

			//分页
			$config['base_url']         = base_url("/notification/get_messages");
			$config['total_rows']       = $count;
			$config['per_page']         = $limit;
			$config['uri_segment']      = 3;
			$config['use_page_numbers'] = TRUE;
			$this->pagination->initialize($config);
			$this->show_global_msg('session');
			$this->template->set('page','account-page no-back');
			$this->template->set_partial('navigation','partials/new_vertical_navigation', array('highlight' => ''));
			$action = '通知';
			$arr = array('header_type' => 'header', 'text' => $action);
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			$this->template->set_partial('sidebar', 'global/user_center_sidebar', array('current' => $action));
			$this->template->set_layout('new_layout_3');
			$this->template->build('notification/list_of_messages', $data);
		}
	}