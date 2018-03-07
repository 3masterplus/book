<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Menu_lib extends my_lib
	{	
		function __construct()
		{
			parent::__construct();
			$this->ci = & get_instance();
		}
		
		function generate_secondary_menu_for_course($course_unique_key, $on = '设置')
		{
			$text = '设置';
			$menu[0]['text'] = $text;
			$menu[0]['url'] = base_url('course/'.$course_unique_key.'/setting');
			$menu[0]['on'] = iif($text == $on, true, false);
			
			$text = '内容';
			$menu[1]['text'] = $text;
			$menu[1]['url'] = base_url('build/build_syllabus/'.$course_unique_key);
			$menu[1]['on'] = iif($text == $on, True, False);
			
			return $menu;
		}
		
		
		function generate_secondary_menu($on = '控制面板')
		{
			$text = '学习课程';
			$menu[0]['text'] = $text;
			$menu[0]['url'] = base_url('home/dashboard');
			$menu[0]['on'] = iif($text == $on, True, False);
			
			$text = '发布课程';
			$menu[1]['text'] = $text;
			$menu[1]['url'] = base_url('build/my_courses');
			$menu[1]['on'] = iif($text == $on, True, False);
			
			$text = '通知';
			$menu[2]['text'] = $text;
			$menu[2]['url'] = base_url('notification');
			$menu[2]['on'] = iif($text == $on, True, False);
			
			$text = '账号';
			$menu[3]['text'] = $text;
			$menu[3]['url'] = base_url('user/edit_account');
			$menu[3]['on'] = iif($text == $on, True, False);
			
			return $menu;
		}
		
		function generate_admin_menu_for_course($on = '发布中')
		{
			$text = '发布中';
			$menu[0]['text'] = $text;
			$menu[0]['url'] = base_url('admin/get_courses/published');
			$menu[0]['on'] = iif($text == $on, True, False);
			
			$text = '待审核';
			$menu[1]['text'] = $text;
			$menu[1]['url'] = base_url('admin/get_courses/pending');
			$menu[1]['on'] = iif($text == $on, True, False);
			
			$text = '制作中';
			$menu[2]['text'] = $text;
			$menu[2]['url'] = base_url('admin/get_courses/draft');
			$menu[2]['on'] = iif($text == $on, True, False);
			
			$text = '创建一个新课程';
			$menu[3]['text'] = $text;
			$menu[3]['url'] = base_url('admin/create_a_course');
			$menu[3]['on'] = iif($text == $on, True, False);
			
			return $menu;
		}
		
		function generate_secondary_menu_for_admin_pannel($on = '用户')
		{
			/**
			$text = '用户管理';
			$menu[0]['text'] = $text;
			$menu[0]['url'] = base_url('home/dashboard');
			$menu[0]['on'] = iif($text == $on, True, False);
			*/
			
			$text = '课程管理';
			$menu[0]['text'] = $text;
			$menu[0]['url'] = base_url('course/my');
			$menu[0]['on'] = iif($text == $on, True, False);
			
			return $menu;
		}
		
	}