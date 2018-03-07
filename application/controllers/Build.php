<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

	class Build extends Tutor_Controller{
		
		function __construct()
		{
			parent::__construct();
			
			$this->members_only();
			$this->load->library('form_validation');
			$this->load->library('build_lib');
			$this->load->library('menu_lib');
			$this->template->set_layout('layout_2_2');
		}
		
		//列出当前用户构建的全部课程
		function my_courses($course_status = 'draft')
		{
			$this->load->library('menu_lib');
			$this->template->set_layout('layout_2');
			
			$secondary_menu = $this->menu_lib->generate_secondary_menu('账号');
			$this->template->set_partial('secondary_menu','partials/secondary_menu', array('secondary_menu' => $secondary_menu));
			
			$vertical_menu = $this->generate_my_courses_vertical_menu('建造中的课程');
			$this->template->set_partial('left_vertical_menu', 'partials/left_vertical_menu', array('vertical_menu' => $vertical_menu));
			
			$courses = $this->build_lib->list_my_courses($this->_guid, 'draft');
			
			$this->template->set('page','my_courses');
			$this->template->build('course/my_courses', array('courses' => $courses));
		}
		
		// ********************************************************
		// 设置一个课程的相关方法
		// ********************************************************
		
		//设置课程的基本信息
		function set_a_course($course_unique_key)
		{
			$this->if_entity_valid($course_unique_key);
			$this->gate($course_unique_key, $this->_guid);
			
			if(!CI_POST('set_a_course'))
			{
				//通过“course_unique_key”获取课程的信息
				$fields = array('title', 'main', 'audience', 'objectives', 'description', 'video_url', 'status', 'is_course_free', 'template');
				$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, $fields);
				
				$title			= $course['title'];
				$main			= $course['main'];
				$objectives		= $course['objectives'];
				$audience		= $course['audience'];
				$description	= $course['description'];
				$video_url		= $course['video_url'];
				$status			= $course['status'];
				$is_course_free	= $course['is_course_free'];
				$theme			= $course['template'];
			}
			else
			{
				$title			= CI_POST('title');
				$main			= CI_POST('main','<ul><li><p>');
				$audience		= CI_POST('audience','<ul><li><span>');
				$objectives		= CI_POST('objectives','<ul><li><span>');
				$description 	= CI_POST('description','<ul><li><p>');
				$video_url		= CI_POST('video_url');
				$status			= CI_POST('status');
				$is_course_free	= CI_POST('is_course_free');
				$theme			= current(CI_POST('theme'));
				
				$this->form_validation->set_rules('title', '课程名称', 'required');
				$this->form_validation->set_rules('main', '课程简介', 'required');
				$this->form_validation->set_rules('objectives', '课程目标', 'required');
				$this->form_validation->set_rules('description', '课程说明', 'required');
				$this->form_validation->set_rules('status', '课程状态', 'required');
				
				$data = array(
					'title'			=> $title,
					'main'			=> $main,
					'audience'		=> $audience,
					'objectives'	=> $objectives,
					'description'	=> $description,
					'video_url'		=> $video_url,
					'status'		=> $status,
					'template'			=> $theme
				);
				
				if($this->form_validation->run() AND $this->my_lib->set_a_subtype_row('courses', $course_unique_key, $data))
				{
					$this->cache->delete($course_unique_key.'_get_course_home'); //删除缓存文件
					$this->my_lib->set_a_msg('课程设置成功！','success');
				}
				else
				{
					$this->my_lib->set_a_msg($this->my_lib->generate_error_message(), 'error');
				}
			}
			
			$data = array(
				'unique_key'		=> $course_unique_key,
				'title'				=> $title,
				'main'				=> $main,
				'audience'			=> $audience,
				'objectives'		=> $objectives,
				'description'		=> $description,
				'video_url'			=> $video_url,
				'status'			=> $status,
				'is_course_free'	=> $is_course_free,
				'theme'				=> $theme,
				'heading'			=> '设置课程基本信息'
			);
			
			$this->show_global_msg('session');
			$this->template->set('page','course-setting');
				
			//设置“secondary_menu_for_course”的数据，并把数据传入到模版中
			$secondary_menu = array('title' => $title, 'status' => $status);
			$secondary_menu['menu'] = $this->menu_lib->generate_secondary_menu_for_course($course_unique_key, '设置');
			$this->template->set_partial('secondary_menu', 'course/secondary_menu_for_course', $secondary_menu);
			
			//设置“left_vertical_menu”的数据，并把数据传入到模板中
			$if_node_set = iif($is_course_free, False, True);
			$vertical_menu = $this->generate_course_setting_vertical_menu($course_unique_key, '设置课程基本信息', $if_node_set);
			$this->template->set_partial('left_vertical_menu', 'partials/left_vertical_menu', array('vertical_menu' => $vertical_menu));
			
			$this->template->build('course/setting', $data);
		}
		
		//设置课程图片
		function set_image($course_unique_key)
		{
			$this->if_entity_valid($course_unique_key);
			$this->gate($course_unique_key, $this->_guid);
		
			//通过“course_unique_key”获取课程的信息
			$fields = array('title', 'banner_url', 'status', 'is_course_free');
			$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, $fields);
			
			$title		= $course['title'];
			$banner_url	= '/'.$course['banner_url'];
			$status		= $course['status'];
			
			$data = array(
				'unique_key'	=> $course_unique_key,
				'title'			=> $title,
				'status'		=> $status,
				'banner_url'	=> $banner_url,
				'heading'		=> '设置课程图片'
			);
			
			$this->show_global_msg('session');
			$this->template->set('page','course-setting-image');
				
			//设置“secondary_menu_for_course”的数据，并把数据传入到模版中
			$secondary_menu = array('title' => $title, 'status' => $status);
			$secondary_menu['menu'] = $this->menu_lib->generate_secondary_menu_for_course($course_unique_key, '设置');
			$this->template->set_partial('secondary_menu', 'course/secondary_menu_for_course', $secondary_menu);
			
			//设置“left_vertical_menu”的数据，并把数据传入到模板中
			$if_node_set = iif($course['is_course_free'], False, True);
			$vertical_menu = $this->generate_course_setting_vertical_menu($course_unique_key, '设置课程图片', $if_node_set);
			$this->template->set_partial('left_vertical_menu', 'partials/left_vertical_menu', array('vertical_menu' => $vertical_menu));
			
			$this->template->build('course/set_image', $data);
		}
		
		//设置课程的“section”
		function set_a_section($course_unique_key)
		{
			$this->if_entity_valid($course_unique_key);
			$this->gate($course_unique_key, $this->_guid);
		
			//通过“course_unique_key”获取课程的信息
			$fields = array('guid', 'title', 'publish_option', 'status', 'is_course_free', 'fee_policy');
			$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, $fields);
			
			//获取该课程下的全部课节
			$this->load->library('Course_lib');
			$sections = $this->course_lib->get_sections($course['guid']);
			
			//拼接数据	
			$data = array(
				'unique_key'		=> $course_unique_key,
				'title'				=> $course['title'],
				'status'			=> $course['status'],
				'publish_option'	=> $course['publish_option'],
				'is_course_free'	=> $course['is_course_free'],
				'fee_policy'		=> $course['fee_policy'],
				'sections'			=> $sections,
				'heading'			=> '设置课节'
			);
			
			$this->template->set('page','course-setting-node');
				
			//设置“secondary_menu_for_course”的数据，并把数据传入到模版中
			$secondary_menu = array(
				'title'  => $course['title'],
				'status' => $course['status'],
				'menu'	 => $this->menu_lib->generate_secondary_menu_for_course($course_unique_key, '设置')
			);
			
			$this->template->set_partial('secondary_menu', 'course/secondary_menu_for_course', $secondary_menu);
			
			//设置“left_vertical_menu”的数据，并把数据传入到模板中
			$if_node_set = true;
			$vertical_menu = $this->generate_course_setting_vertical_menu($course_unique_key, '设置课节', $if_node_set);
			$this->template->set_partial('left_vertical_menu', 'partials/left_vertical_menu', array('vertical_menu' => $vertical_menu));
			
			$this->template->build('course/set_section', $data);
		}
		
		//设置课程的发布和收费方式
		function set_fee($course_unique_key)
		{
			$this->if_entity_valid($course_unique_key);
			$this->gate($course_unique_key, $this->_guid);
		
			//通过“course_unique_key”获取课程的信息
			$fields = array(
				'title',
				'publish_option',
				'is_course_free',
				'by_course_fee',
				'status',
				'access',
				'fee_policy',
				'by_section_is_completed'
			);
			
			//获取该课程的信息
			$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, $fields);
			
			//拼接数据	
			$data = array(
				'unique_key'				=> $course_unique_key,
				'title'						=> $course['title'],
				'status'					=> $course['status'],
				'publish_option'			=> $course['publish_option'],
				'is_course_free'			=> $course['is_course_free'],
				'by_course_fee'				=> $course['by_course_fee'],
				'access'					=> $course['access'],
				'fee_policy'				=> $course['fee_policy'],
				'by_section_is_completed'	=> $course['by_section_is_completed'],
				'heading'					=> '设置收费模式'
			);
			
			$this->template->set('page','course-setting-fee');
				
			//设置“secondary_menu_for_course”的数据，并把数据传入到模版中
			$secondary_menu = array(
				'title'  => $course['title'],
				'status' => $course['status'],
				'menu'	 => $this->menu_lib->generate_secondary_menu_for_course($course_unique_key, '设置')
			);
			
			$this->template->set_partial('secondary_menu', 'course/secondary_menu_for_course', $secondary_menu);
			
			//设置“left_vertical_menu”的数据，并把数据传入到模板中
			$if_node_set = iif($course['is_course_free'], False, True);
			$vertical_menu = $this->generate_course_setting_vertical_menu($course_unique_key, '设置收费模式', $if_node_set);
			$this->template->set_partial('left_vertical_menu', 'partials/left_vertical_menu', array('vertical_menu' => $vertical_menu));
			
			$this->template->build('course/set_fee', $data);
		}
		
		//异步设置某一个课程的发布和收费模式
		function ajax_set_fee()
		{
			if(CI_POST('set_fee'))
			{
				$course_unique_key			= CI_POST('course_unique_key');
				$publish_option				= CI_POST('publish_option');
				$is_course_free				= CI_POST('is_course_free');
				$fee_policy					= (CI_POST('fee_policy')) ? CI_POST('fee_policy') : null;
				$by_course_fee				= (CI_POST('by_course_fee')) ? CI_POST('by_course_fee') : 0;
				$access						= (CI_POST('access')) ? CI_POST('access') : null;
				$by_section_is_completed 	= (CI_POST('by_section_is_completed')) ? CI_POST('by_section_is_completed') : 0; 
				
				$publish_option 	= strtoupper($publish_option);
				$access				= strtoupper($access);
				$fee_policy			= strtoupper($fee_policy);
				
				$this->form_validation->set_rules('course_unique_key', 'course unique key', 'required|callback_is_existent[courses.unique_key]');
				$this->form_validation->set_rules('publish_option', '发布模式', 'required');
				$this->form_validation->set_rules('is_course_free', '课程是否免费', 'required');
				
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($course_unique_key, $this->_guid);
					
					if($this->build_lib->set_course_publish_option($course_unique_key, $publish_option, $is_course_free, $fee_policy, $by_course_fee, $access, $by_section_is_completed))
					{
						$this->ajax_ini();
					}
					else
					{
						$this->_ajax_message = $this->my_lib->generate_error_message();
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		// ********************************************************
		// 构建一个课程的相关方法
		// ********************************************************
		
		//课程编辑页
		function build_syllabus($course_unique_key, $section_unique_key = '', $node_unique_key = ''){
		
			if($course_unique_key != ''){
			
				$this->if_entity_valid($course_unique_key);
				$this->gate($course_unique_key, $this->_guid);
			
				$fields = array('guid', 'title', 'status', 'publish_option', 'is_course_free');
				$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, $fields);
				$this->template->set('page','course-edit');
			
				//根据课程的“guid”，将一个课程的全部课程目录遍历并输出到目录的模版
				$data = array(
					'course_unique_key' => $course_unique_key,
					'syllabus'			=> $this->build_lib->get_syllabus_menu($course['guid'])
				);
				
				$this->template->set_partial('left_vertical_menu','course/syllabus_menu', $data);
				
				//设置“secondary_menu_for_course”的数据，并把数据传入到模版中
				$secondary_menu = array('title' => $course['title'], 'status' => $course['status']);
				$secondary_menu['menu'] = $this->menu_lib->generate_secondary_menu_for_course($course_unique_key, '内容');
				
				$this->template->set_partial('secondary_menu', 'course/secondary_menu_for_course', $secondary_menu);
				
				if($section_unique_key !='' AND $node_unique_key != ''){
				
					$this->if_entity_valid($node_unique_key);
					$this->gate($node_unique_key, $this->_guid);
				
					$fields = array('title', 'main', 'status');
					
					$node = $this->my_lib->get_a_subtype_row('nodes', $node_unique_key, $fields);
					
					$template 	= 'course/edit_a_node';
					
					$data = array(
						'node'				=> $node,
						'is_pjax'			=> FALSE,
						'node_unique_key' 	=> $node_unique_key
					);
					
					//获取节点的GUID
					$node_guid = $this->my_lib->get_guid_by_unique_key($node_unique_key);
					
					/**
					$quizzes = array();
					
					//判断这个节点是否有测试题
					
					if($this->my_lib->check_a_record('entities', array('father_guid' => $node_guid))){
						$this->load->library('quiz_lib');
						$quizzes = $this->quiz_lib->get_quizzes($node_guid);
					}
					
					$data['quizzes'] = $quizzes;
					*/
					
					if(isset($_SERVER['HTTP_X_PJAX'])){
						$data['is_pjax'] = true;
						$this->template->set_layout('layout_for_pjax');
						echo $this->template->build($template, $data);
						exit();
					}
					
				} elseif($section_unique_key != '' AND $node_unique_key == '') {
				
					$this->if_entity_valid($section_unique_key);
					$this->gate($section_unique_key, $this->_guid);
					
					$publish_option = $course['publish_option'];
					$is_course_free = $course['is_course_free'];
					
					$if_set_price = TRUE;
					
					if($publish_option == 'BY_COURSE' OR ($publish_option == 'BY_SECTION' AND $is_course_free)){
						$if_set_price = FALSE;
					}
					
					$fields = array('title', 'main', 'price', 'status');
					$section = $this->my_lib->get_a_subtype_row('sections', $section_unique_key, $fields);
					
					$template = 'course/edit_a_section';
					$data = array(
						'section'				=> $section,
						'is_pjax'				=> FALSE,
						'section_unique_key'	=> $section_unique_key,
						'if_set_price'			=> $if_set_price
					);
					
					if(isset($_SERVER['HTTP_X_PJAX'])){
						$data['is_pjax'] = true;
						$this->template->set_layout('layout_for_pjax');
						echo $this->template->build($template, $data);
						exit();
					}
				} else {
					$template 	= 'course/empty';
					$data 		= '';
				}
			}
			
			$this->template->set_layout('layout_3');
			$this->template->build($template, $data);
		}
		
		/**
		//用户提交课程要求进行审核
		function ajax_submit_course_for_review()
		{
			if(CI_POST('submit_course_for_review'))
			{
				$course_unique_key = CI_POST('course_unique_key');
				$this->form_validation->set_rules('course_unique_key', 'course_unique_key', 'required|callback_is_existent[courses.unique_key]');
				
				if($this->form_validation->run() AND $this->build_lib->request_a_course_review($course_unique_key))
				{
					$this->ajax_ini();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		*/
		
		// ********************************************************
		// 操作课节（SECTION）的相关方法
		// ********************************************************
		
		// 改变课节的排序
		function ajax_resort_section()
		{
			if(CI_POST('resort_section'))
			{	
				$section_unique_key = CI_POST('section_unique_key');
				$section_weight 	= CI_POST('section_weight');
				
				$this->form_validation->set_rules('section_unique_key', 'section_unique_key', 'required|callback_is_existent[sections.unique_key]');
				$this->form_validation->set_rules('section_weight', '排序值', 'required');
				
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($section_unique_key, $this->_guid);
					$this->build_lib->resort_section($section_unique_key, $section_weight);
					$this->ajax_ini();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		// 修改课节的接口
		function ajax_edit_a_section(){
		
			if(CI_POST('edit_a_section')){
				
				$section_title		= CI_POST('section_title');
				$section_main		= CI_POST('section_main');  
				$section_unique_key = CI_POST('section_unique_key');
				$course_unique_key	= CI_POST('course_unique_key');
				$status				= strtoupper(CI_POST('status'));
				
				$this->form_validation->set_rules('section_unique_key', 'section_unique_key', 'required|callback_is_existent[sections.unique_key]');
				$this->form_validation->set_rules('section_title', '课节标题', 'required');
				$this->form_validation->set_rules('section_main', '课节介绍', 'required');
				
				if($this->form_validation->run()){
				
					$this->gate_for_ajax($section_unique_key, $this->_guid);
					$section = array('title' => $section_title, 'main' => $section_main, 'status' => $status);
					$this->my_lib->set_a_subtype_row('sections', $section_unique_key, $section);
					
					$section_guid = $this->my_lib->get_guid_by_unique_key($section_unique_key);
					
					if($status == 'PUBLISHED'){
						$this->my_lib->activate_an_entity($section_guid);
					} elseif($status == 'DRAFT' OR $status == 'CLOSED') {
						$this->my_lib->deactivate_an_entity($section_guid);
					}
					
					$this->ajax_ini();
				
				} else {
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		/***
		// 删除一个课节的接口
		function ajax_delete_a_section()
		{
			if(CI_POST('delete_a_section'))
			{
				$section_unique_key = CI_POST('section_unique_key');
				$this->form_validation->set_rules('section_unique_key', '课节KEY', 'required|callback_is_existent[sections.unique_key]');
				
				if($this->form_validation->run())
				{
					$section_guid = $this->my_lib->get_guid_by_unique_key($section_unique_key);
					if($this->my_lib->is_entity_deletable($section_guid) AND $this->build_lib->delete_a_section($section_unique_key))
					{
						$this->ajax_ini();
					}
					else
					{
						$this->_ajax_message = "该节点不能被删除，请前删除子节点，然后再回来删除本节点";
					}	
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}	
				
				$this->ajax_response();
			}
		}
		*/
		
		//添加一个课节的接口
		function ajax_build_a_section()
		{			
			if(CI_POST('build_a_section'))
			{
				$course_unique_key 	= CI_POST('course_unique_key');
				$section_title		= CI_POST('section_title');
				$weight				= CI_POST('weight');
				
				$this->form_validation->set_rules('course_unique_key', '课程KEY', 'required|callback_is_existent[courses.unique_key]');
				$this->form_validation->set_rules('section_title', '课节名称', 'required');
				$this->form_validation->set_rules('weight', '排序值', 'required');
					
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($course_unique_key, $this->_guid);
					$course_guid = $this->my_lib->get_guid_by_unique_key($course_unique_key);
					$result_arr = $this->build_lib->create_a_section($course_guid, $course_unique_key, $section_title, $weight);
					
					if(count($result_arr) > 0)
					{
						$this->ajax_ini($result_arr);
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		
		// ********************************************************
		// 操作节点（NODE）的相关方法
		// ********************************************************
		
		// 改变课节的排序
		function ajax_resort_node(){
		
			if(CI_POST('resort_node')){
				
				$course_unique_key	= CI_POST('course_unique_key');
				$node_unique_key	= CI_POST('node_unique_key');
				$node_weight		= CI_POST('node_weight');
				$section_unique_key = CI_POST('section_unique_key');
				$father_unique_key 	= CI_POST('father_unique_key');
				
				$this->form_validation->set_rules('course_unique_key', '课程KEY', 'required|callback_is_existent[courses.unique_key]');
				$this->form_validation->set_rules('node_unique_key', '节点KEY', 'required|callback_is_existent[nodes.unique_key]');
				$this->form_validation->set_rules('node_weight', '节点排序值', 'required');
				$this->form_validation->set_rules('section_unique_key', '课节KEY', 'required|callback_is_existent[sections.unique_key]');
				$this->form_validation->set_rules('father_unique_key', '父级KEY', 'required|callback_is_existent[entities.unique_key]');
				
				if($this->form_validation->run()){
				
					$this->gate_for_ajax($node_unique_key, $this->_guid);
					$this->build_lib->resort_node($course_unique_key, $section_unique_key, $node_unique_key, $father_unique_key, $node_weight);
					$this->ajax_ini();
				
				} else {
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		// 修改节点的接口
		function ajax_edit_a_node()
		{
			if(CI_POST('edit_a_node'))
			{	
				$node_title				= CI_POST('node_title');
				$allowed_tags			= '<img><p><audio><br><h1><h2><strong><em><blockquote><ul><li><iframe><source><video>';
				$node_main				= CI_POST('node_main', $allowed_tags); 
				$node_unique_key 		= CI_POST('node_unique_key');
				$course_unique_key		= CI_POST('course_unique_key');
				$deleted_image_array 	= CI_POST('delete_image_array') ? CI_POST('delete_image_array') : array();
				$deleted_audio_array	= CI_POST('delete_audio_array') ? CI_POST('delete_audio_array') : array();
				$number_of_video_clips 	= (int)CI_POST('number_of_video_clips');
				$video_duration			= CI_POST('video_duration') ? CI_POST('video_duration') : 0;
				$user_guid				= $this->_guid;
				$status					= strtoupper(CI_POST('status'));
				
				$this->form_validation->set_rules('node_unique_key', 'node_unique_key', 'required');
				$this->form_validation->set_rules('node_title', '节点标题', 'required');
				$this->form_validation->set_rules('node_main', '节点介绍', 'required');
				
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($node_unique_key, $user_guid);
					
					$node = array('title' => $node_title, 'main' => $node_main, 'video_duration' => $video_duration, 'status' => $status);
					
					$this->my_lib->set_a_subtype_row('nodes', $node_unique_key, $node);
					
					$node_guid = $this->my_lib->get_guid_by_unique_key($node_unique_key);
					
					if($status == 'PUBLISHED'){
						$this->my_lib->activate_an_entity($node_guid);
					} elseif($status == 'DRAFT' OR $status == 'CLOSED') {
						$this->my_lib->deactivate_an_entity($node_guid);
					}
					
					$word_count = str_utf8_mix_word_count(strip_tags($node_main));
					$this->after_update($node_unique_key, $deleted_image_array, $deleted_audio_array, $number_of_video_clips, 'node', $word_count);
					$this->ajax_ini();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//当用户更新了“node”和“section”之后，有大量数据方面的变更，包括：图片（增加或删除）、音频、视频等，以及相应统计数据等
		private function after_update($entity_unique_key, $deleted_image_array, $deleted_audio_array, $number_of_video_clips, $entity_type = 'node', $word_count = 0)
		{
		
			//从数据库中减少该节点的图片读数
			$entity_guid = $this->my_lib->get_guid_by_unique_key($entity_unique_key);
			
			if($entity_type == 'node')
			{
				$node = $this->my_lib->get_a_subtype_row('nodes', (int)$entity_guid, array('section_guid', 'course_guid'));
				$node_guid = $entity_guid;
				$section_guid = $node['section_guid'];
				$course_guid = $node['course_guid'];
			}
			elseif($entity_type == 'section')
			{
				$section = $this->my_lib->get_a_subtype_row('sections', (int)$entity_guid, array('course_guid'));
				$section_guid = $entity_guid;
				$course_guid = $section['course_guid'];
			}
			
			$number_of_deleted_images 		= count($deleted_image_array);
			$number_of_deleted_audio_clips 	= count($deleted_audio_array);
					
			//删除图片
			if($number_of_deleted_images > 0)
			{
				foreach($deleted_image_array AS $row)
				{
					$condition = array('id' => $row['resized_image_id']);
							
					$this->my_lib->delete_records('images', $condition); //删除裁剪图片的数据库记录
					$this->my_lib->delete_records('image_entity_relations', $condition); //删除裁剪之后图片和GUID之间的关系
							
					$condition = array('id' => $row['original_image_id']);
							
					$this->my_lib->delete_records('images', $condition); //删除原始图片的数据库记录
					$this->my_lib->delete_records('image_entity_relations', $condition); //删除原始图片和GUID之间的关系
				}
				
				if($entity_type == 'node')
				{
					$this->count_lib->minus($node_guid, 'image', $number_of_deleted_images);
				}
				
				$this->count_lib->minus($section_guid, 'image', $number_of_deleted_images);
				$this->count_lib->minus($course_guid, 'image', $number_of_deleted_images);
			}
					
			//删除音频
			if($number_of_deleted_audio_clips > 0)
			{
				foreach($deleted_audio_array AS $row)
				{
					$condition = array('id' => $row['id']);
					$this->my_lib->delete_records('files', $condition);
					$this->my_lib->delete_records('file_entity_relations', $condition);
				}
				
				if($entity_type == 'node')
				{
					$this->count_lib->minus($node_guid, 'mp3', $number_of_deleted_audio_clips);
				}
				
				$this->count_lib->minus($section_guid, 'mp3', $number_of_deleted_audio_clips);
				$this->count_lib->minus($course_guid, 'mp3', $number_of_deleted_audio_clips);
			}
			
			//增加或减少该节点的视频数量
			$count_number_of_video_clips = $this->count_lib->get_count($entity_guid, 'video');
						
			if($number_of_video_clips > $count_number_of_video_clips)
			{
				$number_of_video_clips_added = $number_of_video_clips - $count_number_of_video_clips;
				
				if($entity_type == 'node')
				{
					$this->count_lib->plus($node_guid, 'video', $number_of_video_clips_added);
				}
				
				$this->count_lib->plus($section_guid, 'video', $number_of_video_clips_added);
				$this->count_lib->plus($course_guid, 'video', $number_of_video_clips_added);
			}
			elseif($number_of_video_clips < $count_number_of_video_clips)
			{
				$number_of_video_clips_deleted = $count_number_of_video_clips - $number_of_video_clips;
				
				if($entity_type == 'node')
				{
					$this->count_lib->minus($node_guid, 'video', $number_of_video_clips_deleted);
				}
				
				$this->count_lib->minus($section_guid, 'video', $number_of_video_clips_deleted);
				$this->count_lib->minus($course_guid, 'video', $number_of_video_clips_deleted);
			}
			
			//文字数计数
			if($word_count > 0 AND $entity_type == 'node')
			{
				$node_word_count = $this->count_lib->get_count($node_guid, 'word_count');
				$difference = $word_count - $node_word_count;
				
				if($difference != 0)
				{
					$this->count_lib->update_count($node_guid, 'word_count', $word_count);
					$this->count_lib->plus($section_guid, 'word_count', $difference);
					$this->count_lib->plus($course_guid, 'word_count', $difference);
				}
			}
			
			return;
		}
		
		// 添加一个节点的接口
		function ajax_build_a_node()
		{
			if(CI_POST('build_a_node'))
			{
				$course_unique_key	= CI_POST('course_unique_key');
				$section_unique_key	= CI_POST('section_unique_key');
				$father_unique_key	= CI_POST('father_unique_key');
				$node_title			= CI_POST('node_title');
				$node_weight		= CI_POST('weight');
				
				$this->form_validation->set_rules('course_unique_key', '课程KEY', 'required|callback_is_existent[courses.unique_key]');
				$this->form_validation->set_rules('section_unique_key', '课节KEY', 'required|callback_is_existent[sections.unique_key]');
				$this->form_validation->set_rules('father_unique_key', '父级KEY', 'required|callback_is_existent[entities.unique_key]');
				$this->form_validation->set_rules('node_title', '节点标题', 'required');
				$this->form_validation->set_rules('weight', '节点排序值', 'required');
			
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($course_unique_key, $this->_guid);
					
					$course_guid 	= $this->my_lib->get_guid_by_unique_key($course_unique_key);
					$section_guid 	= $this->my_lib->get_guid_by_unique_key($section_unique_key);
					$father_guid	= $this->my_lib->get_guid_by_unique_key($father_unique_key);
					
					$result_arr = $this->build_lib->create_a_node($course_guid, $section_guid, $father_guid, $node_title, $node_weight);
					
					if(count($result_arr) > 0)
					{
						$this->ajax_ini($result_arr);
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//设置一个“section”的价格
		function ajax_set_a_section_price()
		{
			if(CI_POST('set_a_section_price'))
			{
				$section_unique_key = CI_POST('section_unique_key');
				$section_price		= CI_POST('section_price');
				
				$this->form_validation->set_rules('section_unique_key', 'section_unique_key', 'required|callback_is_existent[sections.unique_key]');
				
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($section_unique_key, $this->_guid);
					$condition = array('unique_key' => $section_unique_key);
					$data = array('price' => $section_price);
					$this->my_lib->update_records('sections', $data, $condition);
					$this->ajax_ini();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}	
				
				$this->ajax_response();
			}
		}
		
		//设置一个“section”是否可以免费试学状态
		function ajax_set_a_section_open_status()
		{
			if(CI_POST('set_a_section_open_status'))
			{
				$section_unique_key = CI_POST('section_unique_key');
				$is_open = CI_POST('is_open');
				$is_open = iif($is_open == 'true', 1, 0);
				
				$this->form_validation->set_rules('section_unique_key', 'section_unique_key', 'required|callback_is_existent[sections.unique_key]');
				
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($section_unique_key, $this->_guid);
					$condition = array('unique_key' => $section_unique_key);
					$data = array('is_open' => $is_open);
					$this->my_lib->update_records('sections', $data, $condition);
					
					//首先，获取课程"guid"
					$course_guid = $this->my_lib->get_a_value('sections', 'course_guid', $condition);
					
					//获取课程相关信息
					$course = $this->my_lib->get_a_subtype_row('courses', (int)$course_guid,  array('is_course_free', 'fee_policy'));
					
					$is_course_free = $course['is_course_free'];
					$fee_policy 	= $course['fee_policy'];
					
					$if_set_price = false;
					
					if(!$is_open AND !$is_course_free AND $fee_policy != 'BY_COURSE') $if_set_price = true;
					
					$data = array('if_set_price' => $if_set_price);
					
					$this->ajax_ini($data);
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}	
				
				$this->ajax_response();
			}
		}
		
		/****
		//删除一个节点的接口
		function ajax_delete_a_node()
		{
			if(CI_POST('delete_a_node'))
			{
				$node_unique_key = CI_POST('node_unique_key');
				
				$this->form_validation->set_rules('node_unique_key', '节点KEY', 'required|callback_is_existent[nodes.unique_key]');
				
				if($this->form_validation->run())
				{
					$node_guid = $this->my_lib->get_guid_by_unique_key($node_unique_key);
					if($this->my_lib->is_entity_deletable($node_guid) AND $this->build_lib->delete_a_node($node_unique_key))
					{
						$this->ajax_ini();
					}
					else
					{
						$this->_ajax_message = "该节点不能被删除，请前删除子节点，然后再回来删除本节点";
					}	
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}	
				
				$this->ajax_response();
			}
		}
		*/
		
		// ********************************************************
		// 其它相关方法
		// ********************************************************
		
		private function generate_my_courses_vertical_menu($on)
		{			
			$text = '建造中的课程';
			$vertical_menu[0]['text'] = $text;
			$vertical_menu[0]['url'] = base_url('user/edit_account');
			$vertical_menu[0]['on'] = iif($text == $on, True, False);
			return $vertical_menu;
		}
		
		//生成课程设置左侧的的菜单
		private function generate_course_setting_vertical_menu($course_unique_key, $on, $if_node_set = false)
		{
			$count = 0;
				
			$text = '设置课程基本信息';
			$menu[$count]['text'] = $text;
			$menu[$count]['url'] = base_url('course/'.$course_unique_key.'/setting');
			$menu[$count]['on'] = iif($text == $on, true, false);
			
			$count++;
			
			$text = '设置收费模式';
			$menu[$count]['text'] = $text;
			$menu[$count]['url'] = base_url('course/'.$course_unique_key.'/setting/fee');
			$menu[$count]['on'] = iif($text == $on, true, false);
			
			$count++;
			
			
			$text = '设置课节';
			$menu[$count]['text'] = $text;
			$menu[$count]['url'] = base_url('course/'.$course_unique_key.'/setting/section');
			$menu[$count]['on'] = iif($text == $on, true, false);			
			$count++;
			
			
			$text = '设置课程图片';
			$menu[$count]['text'] = $text;
			$menu[$count]['url'] = base_url('course/'.$course_unique_key.'/setting/image');
			$menu[$count]['on'] = iif($text == $on, true, false);
			
			return $menu;
		}
	}