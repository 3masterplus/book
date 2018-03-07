<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

	class Admin extends Tutor_Controller{
		
		function __construct()
		{
			parent::__construct();
			$this->load->library('admin_lib');
			$this->load->library('course_lib');
		}
		
		//获取产品列表
		function get_courses($status = 'published')
		{
			//加载资源库
			$this->load->library('menu_lib');
			
			//获取课程列表
			$courses = $this->admin_lib->get_list_of_course($status);
			
			$data['courses'] = $courses;
			
			//设置模板
			$this->template->set_layout('layout_2');
			
			//设置并加载左侧菜单
			switch($status)
			{
				case 'published':
					$on = '发布中';
					break;
				case 'pending':
					$on = '待审核';
					break;
				case 'draft':
					$on = '制作中';
					break;
				case 'waiting_for_approval':
					$on = '申请制作的课程';
					break;
				default:
					$on = '发布中';
			}
			
			$secondary_menu = $this->menu_lib->generate_admin_menu_for_course($on);
			$this->template->set_partial('secondary_menu','partials/secondary_menu', array('secondary_menu' => $secondary_menu));
			
			$data['heading'] = $on;
			$data['status'] = $status;
			
			$this->template->build('admin/list_of_course', $data);
		}
		
		function create_a_course()
		{
			//加载必要的资源库
			$this->load->library('form_validation');
			$this->load->library('menu_lib');
			
			$data = array('title' => '', 'summary' => '');
			
			if(CI_POST('create_a_course'))
			{
				$title 		= CI_POST('title');
				$summary 	= CI_POST('summary');
				
				$data['user_unique_key']	= $this->_user_unique_key;
				$data['title'] 				= $title;
				$data['summary']	 		= $summary;
				
				$this->form_validation->set_rules('title', '课程名称', 'required|is_unique[courses.title]');
				$this->form_validation->set_rules('summary', '课程简介', 'required');
				
				if($this->form_validation->run() AND $this->admin_lib->create_a_course($this->_guid, $title, $summary))
				{
					$this->my_lib->set_a_msg('新课程创建成功', 'success');
					redirect(base_url('admin/get_courses/draft'));
				}
				else
				{
					$this->my_lib->set_a_msg($this->my_lib->generate_error_message(), 'error');
				}
			}
			
			$this->show_global_msg('session');
			
			//设置模板类型
			$this->template->set_layout('layout_2');
			$this->template->set('page','create-a-course');
			
			//加载和设置二级导航
			$secondary_menu = $this->menu_lib->generate_admin_menu_for_course('创建一个新课程');
			$this->template->set_partial('secondary_menu','partials/secondary_menu', array('secondary_menu' => $secondary_menu));
			
			//显示整个页面
			$this->template->build('admin/create_a_course', $data);		
		}
	}