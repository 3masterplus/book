<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

	class Course extends Client_Controller{
		
		function __construct()
		{
			parent::__construct();

			$this->load->library('course_lib');
			$this->template->set_layout('new_layout_2');
		}
		
		//用户加入一门免费课程
		function ajax_join_a_free_course()
		{
			if(CI_POST('enroll_a_free_course'))
			{
				$this->ajax_members_only();
				
				//加载“form_validation”的库类
				$this->load->library('form_validation');
				
				//获取用户要加入的“course_unqieu_key”
				$course_unique_key 	= CI_POST('course_unique_key');
				
				//检验用户传来的这个“course_unique_key”
				$this->form_validation->set_rules('course_unique_key', 'course_unique_key', 'required|callback_is_existent[courses.unique_key]');
				
				//获取当前用户的GUID
				$user_guid = $this->_guid;
				
				if($this->form_validation->run())
				{
					// 获取课程的相关信息
					$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, array('guid', 'is_course_free'));
					$is_course_free = $course['is_course_free'];
					$course_guid = $course['guid'];
					
					// 首先，如果用户已经加入了这个课程，告知用户，他已经加入了该课程；
					if($this->course_lib->if_a_course_joined($user_guid, $course_guid))
					{
						$this->_ajax_message = '您已加入该课程，不能反复加入';
					}
					else
					{
						//如果该课程是免费课程，用户加入
						if($is_course_free)
						{
							$this->course_lib->join_a_course($user_guid, $course_guid);
							$this->ajax_ini();
						}
						else
						{
							$this->_ajax_message = '该课程为付费课程，不可以直接加入';
						}
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//退出一门课程
		function ajax_quit_a_course()
		{
			if(CI_POST('quit_a_course'))
			{
				$this->ajax_members_only();
				
				//加载“form_validation”的库类
				$this->load->library('form_validation');
				
				//获取用户要加入的“course_unqieu_key”
				$course_unique_key 	= CI_POST('course_unique_key');
				
				//检验用户传来的这个“course_unique_key”
				$this->form_validation->set_rules('course_unique_key', 'course_unique_key', 'required|callback_is_existent[courses.unique_key]');
				
				//获取当前用户的GUID
				$user_guid = $this->_guid;
				
				if($this->form_validation->run())
				{
					// 获取课程的相关信息
					$course_guid = $this->my_lib->get_guid_by_unique_key($course_unique_key);
					
					//如果确实存在用户和课程的关系，让用户退出课程
					if($this->course_lib->if_a_course_joined($user_guid, $course_guid))
					{
						$this->course_lib->quit_a_course($user_guid, $course_guid);
						$this->ajax_ini();
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		
		// 加入一门课程
		function ajax_enroll_a_course()
		{
			if(CI_POST('enroll_a_course'))
			{
				$this->ajax_members_only();
				
				//加载“form_validation”的库类
				$this->load->library('form_validation');
				
				//获取用户要加入的“course_unqieu_key”
				$course_unique_key 	= CI_POST('course_unique_key');
				
				//检验用户传来的这个“course_unique_key”
				$this->form_validation->set_rules('course_unique_key', 'course_unique_key', 'required|callback_is_existent[courses.unique_key]');
				
				//获取当前用户的GUID
				$user_guid = $this->_guid;
				
				if($this->form_validation->run())
				{
					$course_guid = $this->my_lib->get_guid_by_unique_key($course_unique_key);
					
					if($user_guid > 0 AND !$this->course_lib->if_a_course_paid($user_guid, $course_guid))
					{
						$this->course_lib->pay_a_course($course_guid, $user_guid);
						$this->ajax_ini();
					}
					else
					{
						$this->_ajax_message = '您已加入开课程或已经退出登录';
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//AJAX接口，返回当前用户是否可以完整学习整个知识节点的内容
		public function ajax_get_a_node_accessibility()
		{
			if(CI_POST('get_a_node_accessibility'))
			{
				$this->load->library('form_validation');
				$node_unique_key = CI_POST('node_unique_key');
				$this->form_validation->set_rules('node_unique_key', 'node_unique_key', 'required|callback_is_existent[nodes.unique_key]');
				
				if($this->form_validation->run())
				{
					//根据“node_unique_key”获取相应信息
					
					$condition	= array('unique_key' => $node_unique_key);
					$fields		= array('guid', 'course_guid', 'section_guid');
					$nodes		= $this->my_lib->get_a_subtype_row('nodes', $node_unique_key, $fields, $condition);
					
					$course_guid 	= $nodes['course_guid'];
					$section_guid	= $nodes['section_guid'];
					$node_guid		= $nodes['guid'];
					$is_course_free = $this->my_lib->get_a_value('courses', 'is_course_free', array('guid' => $course_guid));
					$user_guid		= $this->_guid;
					
					$is_node_accessible = $this->course_lib->is_node_accessible($user_guid, $course_guid, $section_guid, $node_guid, $is_course_free);
					
					$data = array('is_node_accessible' => $is_node_accessible, 'is_course_free' => $is_course_free);
					$this->ajax_ini($data);
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//用户操作一个课程
		function ajax_do_a_course()
		{
			if(CI_POST('user_course_action'))
			{
				$this->ajax_members_only();
				
				$this->load->library('form_validation');
				
				$course_unique_key 	= CI_POST('course_unique_key');
				$section_unique_key = CI_POST('section_unique_key');
				$node_unique_key 	= CI_POST('node_unique_key');
				$action				= CI_POST('action');
				$user_guid			= $this->_guid;
				
				$course_guid = $this->my_lib->get_guid_by_unique_key($course_unique_key);
				
				$this->form_validation->set_rules('course_unique_key', 'course_unique_key', 'required|callback_is_existent[courses.unique_key]');
				$this->form_validation->set_rules('action', 'action', 'required|callback_action_check');
				
				if($action == 'learn_a_node')
				{
					$this->form_validation->set_rules('section_unique_key', 'section_unique_key', 'required|callback_is_existent[sections.unique_key]');
					$this->form_validation->set_rules('node_unique_key', 'node_unique_key', 'required|callback_is_existent[nodes.unique_key]');
					
					$section_guid = $this->my_lib->get_guid_by_unique_key($section_unique_key);
					$node_guid = $this->my_lib->get_guid_by_unique_key($node_unique_key);
					
					if($this->form_validation->run() AND $this->course_lib->learn_a_node($course_guid, $section_guid, $node_guid, $user_guid))
					{
						$this->ajax_ini();
					}
					else
					{
						$this->_ajax_message = $this->my_lib->generate_error_message();
					}
				}
				elseif($action == 'unlearn_a_node')
				{
					$node_guid = $this->my_lib->get_guid_by_unique_key($node_unique_key);
					
					if($this->form_validation->run() AND $this->course_lib->unlearn_a_node($user_guid, $node_guid))
					{
						$this->ajax_ini();
					}
					else
					{
						$this->_ajax_message = $this->my_lib->generate_error_message();
					}
				}
				
				$this->ajax_response();
			}
		}
		
		function action_check($str){
		
			$action_arr = array('learn_a_node', 'unlearn_a_node');
			
			if(in_array($str, $action_arr)){
				return True;
			} else {
				$this->form_validation->set_message('action_check','您输入的ACTION不存在');
				return False;
			}
		}
		
		//用户查看一个“node”
		public function node($course_unique_key = '', $section_unique_key = '', $node_unique_key = ''){
		
			//获取当前用户的GUID
			$user_guid = $this->_guid;
		
			//分别获取“COURSE”、“SELECTION”、以及“NODE”的GUID
			$course_guid	= $this->my_lib->get_guid_by_unique_key($course_unique_key);
			$section_guid	= $this->my_lib->get_guid_by_unique_key($section_unique_key);
			$node_guid		= $this->my_lib->get_guid_by_unique_key($node_unique_key);
			
			//获取一个节点的基本信息
			$data = $this->course_lib->get_a_node($node_guid, $section_guid, $course_guid);
			
			//获取当前用户是否曾经学过这个知识点
			$data['if_learnt'] = $this->course_lib->if_a_node_learned($user_guid, $node_guid);
			
			//获取这个知识点相关的"Section Tree"
			$section_tree = $this->course_lib->get_syllabus_menu_for_course_home($section_guid);
			$section_tree = $this->course_lib->check_if_node_learned($section_tree, $user_guid);
			
			$data['section_tree'] 		= $section_tree;
			$data['course_unique_key'] 	= $course_unique_key;
			$data['section_unique_key'] = $section_unique_key;
			$data['node_unique_key'] 	= $node_unique_key;
			$data['user_guid']			= $user_guid;
			
			//获取课程的信息
			$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, array('is_course_free', 'title'));
			
			//判断用户是否可以学习该课程
			$is_node_accessible = $this->course_lib->is_node_accessible($user_guid, $course_guid, $section_guid, $node_guid, $course['is_course_free']);
			$data['is_node_accessible'] = $is_node_accessible;
			
			$section = $this->my_lib->get_a_subtype_row('sections', (int)$section_guid, array('is_open', 'title'));
			
			//是否显示“免费试学”的标识
			$if_course_learned = $this->course_lib->if_a_course_joined($user_guid, $course_guid);
			$data['if_openmark_showed'] = iif($section['is_open'] AND !$if_course_learned, true, false);
			
			
			$data['section_title'] = $section['title'];
			$data['section_index_number'] = $this->course_lib->get_index_number_of_a_section($section_guid, $course_guid);
			
			
			//微信分享
			$ticket = json_decode($this->course_lib->ticket_get())->ticket;
			$timestamp = time();
			$data['timestamp'] = $timestamp;
			$nonceStr = $this->course_lib->randomString("number,upper,lower",16);
			$signatureStr = 'jsapi_ticket='.$ticket.'&noncestr='.$nonceStr.'&timestamp='.$timestamp.'&url='.base_url($_SERVER['REQUEST_URI']);
			$signature = sha1($signatureStr);
			$data['nonceStr'] = $nonceStr;
			$data['signature'] = $signature;
			
			//拼接模板
			$params = array('highlight' => 'course', 'is_mobile_sidebar_set' => false);
			$this->template->set_partial('navigation','partials/new_vertical_navigation', $params);
			$return_link = base_url("course/$course_unique_key/home#$node_unique_key");
			$arr = array('header_type' => 'return', 'text' => $course['title'], 'return_link' => $return_link);
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			$this->template->set('page', 'learn');
			$this->template->title($data['title'], $this->config->item('site_name'));
			$this->template->build('course/new/course_learn', $data);
		}
		
		// 获取一个课程的首页
		function get_course_home($course_unique_key)
		{
			//判断这个课程能否被查看
			$this->check_course_availability($course_unique_key);
			
			//获取课程的基本信息
			$fields = array('*');
			$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, $fields);			
			
			//获取这个课程的“GUID”
			$course_guid = $course['guid'];
			
			//获取这个课程下的全部“SECTION”
			$course_sections = $this->course_lib->get_sections($course_guid);
			
			//获取当前用户的GUID			
			$user_guid = $this->_guid;
			
			$course_data = array(
				'course_guid' 		=> $course_guid,
				'course_unique_key' => $course_unique_key,
				'publish_option'	=> $course['publish_option'],
				'is_course_free'	=> $course['is_course_free'],
				'fee_policy'		=> $course['fee_policy'],
				'by_course_fee'		=> $course['by_course_fee'],
				'access'			=> $course['access']
			);
			
			//获取课程老师的GUID
			$owner_guid = $this->my_lib->get_a_value('entities', 'owner_guid', array('unique_key' => $course_unique_key));
			
			//获取这位老师的信息
			$lecturer = $this->my_lib->get_a_subtype_row('users', (int)$owner_guid);
			
			$syllabus = $this->course_lib->get_syllabus_menu_for_course_home($course_guid);
			$syllabus = $this->course_lib->check_if_node_learned($syllabus, $user_guid);
			
			$data = array(
				'course_guid'					=> $course['guid'],
				'course_title'					=> $course['title'],
				'course_summary'				=> $course['main'],
				'course_description'			=> str_replace(chr(10).chr(13), '</p><p>', $course['description']),
				'course_audience'				=> $course['audience'],
				'course_objectives'				=> $course['objectives'],
				'course_publish_option'			=> $course['publish_option'],
				'course_is_course_free'			=> $course['is_course_free'],
				'course_by_course_fee'			=> $course['by_course_fee'],
				'course_template'				=> $course['template'],
				'course_by_section_is_completed'=> $course['by_section_is_completed'],
				'course_number_of_participants' => 100,
				'course_number_of_sections'		=> $this->course_lib->get_course_number_of_sections($course['guid']),
				'course_number_of_nodes'		=> $this->course_lib->get_course_number_of_nodes($course['guid']),
				'course_total_mp3_duration'		=> $this->course_lib->calculate_total_duration_mp3_course($course['guid']),
				'course_total_video_duration'	=> $this->course_lib->calculate_total_duration_video_course($course['guid']),
				'course_total_word_count'		=> $this->count_lib->get_count($course['guid'], 'word_count'),
				'course_sections'				=> $course_sections,
				'lecturer_username'				=> $lecturer['username'],
				'lecturer_bio'					=> $lecturer['bio'],
				'lecturer_signature'			=> $lecturer['signature'],
				'lecturer_avatar'				=> $this->my_lib->get_user_avatar($lecturer['unique_key'], 40, 40),
				'user_guid'						=> $user_guid,
				'syllabus'						=> $syllabus
			);

			$data['course_unique_key'] = $course_unique_key;
			$data['accessibility'] = $this->course_lib->check_accessibility($course_data, $user_guid, $syllabus);
			
			$this->template->set('page','course-info');
			
			$params = array('highlight' => 'course', 'is_mobile_sidebar_set' => false);
			$this->template->set_partial('navigation','partials/new_vertical_navigation', $params);
			
			$arr = array('header_type' => 'return', 'text' => '查看全部课程', 'return_link' => base_url('course'));
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			
			$this->template->title($course['title'], $this->config->item('site_name'));
			$this->template->build('course/new/course_home_1', $data);
		}
		
		//检测一个课程是否有效，允许被访问
		private function check_course_availability($course_unique_key)
		{
			$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, array('status'));
			
			//如果这个课程不存在，返回 404
			if(sizeof($course) > 0)
			{
				//获取课程状态
				$course_status = $course['status'];
				
				//如果课程是发布状态，返回“True”
				if($course_status == strtoupper('published'))
				{
					return True;
				}
				else
				{
					$type 	= 'error';
					$header = '课程尚未状态';
					$button = array(
						'link'	=> 'http://www.tmtpost.com',
						'text'	=> '返回首页'
					);
					
					$this->load_msg_page($type, $header, '这是一段测试文字', $button);
				}
			}
			else
			{
				show_404();
			}
		}
		
		
		//根据一个“section”的“section_unique_key”，获得该“section”下的全部“nodes”
		function ajax_get_nested_nodes_by_section()
		{
			if(CI_POST('get_nested_nodes_by_section'))
			{
			
				$user_guid = $this->_guid;
				
				$this->load->library('form_validation');
				
				$section_unique_key = CI_POST('section_unique_key');
				$this->form_validation->set_rules('section_unique_key', 'section_unique_key', 'required|callback_is_existent[sections.unique_key]');
				
				if($this->form_validation->run())
				{
					$section_guid = $this->my_lib->get_guid_by_unique_key($section_unique_key);
					$nested_nodes = $this->course_lib->get_syllabus_menu_for_course_home($section_guid);
					$new_array = $this->array_rework($nested_nodes, $user_guid);
					$this->ajax_ini($new_array);
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		
		//对多维数组进行拆解然后加上用户和每一个知识点的关系，进行重新组合
		private function array_rework($array, $user_guid)
		{
			$this->load->library('Count_lib');
			
			$count = 0;
			
			if(count($array) > 0)
			{
				foreach($array AS $row)
				{
					$node_guid = $row['guid'];
					$array[$count]['number_of_video'] = $this->count_lib->get_count($node_guid, 'video');
					$array[$count]['number_of_audio'] = $this->count_lib->get_count($node_guid, 'mp3');
					$array[$count]['number_of_image'] = $this->count_lib->get_count($node_guid, 'image');
					
					if($user_guid > 0)
					{
						$array[$count]['is_current_user_learned'] = $this->course_lib->if_a_node_learned($user_guid, $node_guid);
					}
					
					if(count($row['child']) > 0)
					{
						$array[$count]['child'] = $this->array_rework($row['child'], $user_guid);
					}
					
					$count++;
				}
			}
					
			return $array;
		}
		
		//计算一个多维数组中的elements
		private function multi_array_count($array, $count = 0)
		{			
			foreach($array AS $row)
			{
				$count++;	
				if(count($row['child']) > 0)
				{
					$count = $this->multi_array_count($row['child'], $count);
				}
			}
			return $count;
		}
		
		//列出全部可以学习的课程
		function list_all_available_courses(){
			$this->load->library('Count_lib');
			$courses = $this->course_lib->get_all_available_courses();
			$data['courses'] = $courses;
			$this->template->set('page','my-course');
			$this->template->set_partial('navigation','partials/new_vertical_navigation', array('highlight' => 'course'));
			$action = '全部课程';
			$arr = array('header_type' => 'header', 'text' => $action);
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			$this->template->title($action, $this->config->item('site_name'));
			$this->template->build('course/new/all_courses', $data);
		}
		
		//用户的Dashboard
		function dashboard($pointer = 'uncompleted'){
			
			$this->members_only();
		
			//加载计数库文件
			$this->load->library('Count_lib');
			
			$this->template->set('page','my-course');
			$this->template->set_partial('navigation','partials/new_vertical_navigation', array('highlight' => 'dashboard'));
				
			$action = $this->config->item('dashboard');
			$arr = array('header_type' => 'header', 'text' => $action);
			
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			$this->template->set_layout('new_layout_2');
			
			//获取默认的TAB
			if($pointer == '') $pointer = 'uncompleted';
			
			//获取当前用户的GUID
			$user_guid = $this->_guid;
			
			if($pointer == 'uncompleted'){
				
				$course_uncompleted = $this->course_lib->get_user_uncompleted_courses($user_guid); //获取未完成的课程列表
				$data['course_uncompleted'] = $course_uncompleted;
				$template = 'course/new/dashboard-home'; //设置模板
				$this->template->title('学习中的课程', '控制台', $this->config->item('site_name'));
				
			} elseif ($pointer == 'completed') {
			
				$course_completed = array();
				$data['course_completed'] = $course_completed;
				$template = 'course/new/dashboard-completed';
				$this->template->title('已完成的课程', '控制台', $this->config->item('site_name'));
				
			}
			
			$this->template->build($template, $data);
		}
		
		//从七牛获取视频截图
		public function ajax_get_thumb_of_video(){

			include_once(APPPATH.'/third_party/qiniu/autoload.php');

			$qauth   = new Qiniu\Auth('6iR3qSwNE_eJk_ZS7wHm4hZjIy7zmq-_kmxHIYPn', '0mBgeTRHl4berRrLhk67TpDQXnCzPXyjLneIFsPP');
			$query   = '/pfop/?bucket=uktastesvideo&key=xmasmessage2015.mp4&fops=vframe%2fjpg%2foffset%2f7%2fw%2f480%2fh%2f360';
			$ac      = $qauth->authorization($query);			
			$ac_head = 'Authorization: '.$ac['Authorization'];
			
			$header_strings = array(
			    "Content-Type: application/x-www-form-urlencoded",
			    $ac_head,
			);
			
			$url  = 'http://api.qiniu.com'.$query;
			$data = array();
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header_strings);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			$res = curl_exec($ch);
			curl_close($ch);

			var_dump($res);


			if(CI_POST('get_thumb_of_video'))
			{
				$video_name = CI_POST('video_name');
				$video_thumb_width = CI_POST('video_thumb_width');
				$video_thumb_height = CI_POST('video_thumb_height');
			}
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
		    if (isset($parsed_url['query'])) { 
		        $access .= "?" . $parsed_url['query']; 
		    } 
		    $access .= "\n"; 
		    if($params){ 
		        if (is_array($params)){ 
		            $params = http_build_query($params); 
		        } 
		    $access .= $params; 
		    } 
		    $digest = hash_hmac('sha1', $access, $secret_key, true); 
		    return $access_key.':'.$this->urlsafe_base64_encode($digest); 
		}
		
		/**
		
		//回答问题
		function quiz($course_unique_key, $section_unique_key, $node_unique_key, $quiz_unique_key, $question_unique_key = '')
		{
			$this->load->library('quiz_lib');
		
			$course_guid	= $this->my_lib->get_guid_by_unique_key($course_unique_key);
			$section_guid	= $this->my_lib->get_guid_by_unique_key($section_unique_key);
			$node_guid		= $this->my_lib->get_guid_by_unique_key($node_unique_key);
			$quiz_guid		= $this->my_lib->get_guid_by_unique_key($quiz_unique_key);
			
			//如果“question_unique_key”为空，获取该“quiz”的第一个问题
			if($question_unique_key == '')
			{
				$question_guid 			= $this->quiz_lib->get_first_question($quiz_guid);
				$question_unique_key 	= $this->my_lib->get_unique_key_by_guid($question_guid);
			}
			else
			{
				$question_guid 	= $this->my_lib->get_guid_by_unique_key($question_unique_key);
			}
			
			//获取“quiz”相关信息
			$quiz = array();
			$quiz['title'] = $this->my_lib->get_a_value('entities', 'title', array('unique_key' => $quiz_unique_key));
			$quiz['questions'] = $this->my_lib->get_records('entities', array('unique_key'), array('father_guid' => $quiz_guid));
			$quiz['question_unique_key'] = $question_unique_key;
			
			$question = $this->quiz_lib->get_a_question($question_guid);
			$question_type = $question['question']['type'];
			
			$data = array(
				'course_unique_key' 	=> $course_unique_key,
				'section_unique_key'	=> $section_unique_key,
				'node_unique_key'		=> $node_unique_key,
				'quiz_unique_key'		=> $quiz_unique_key,
				'question_unique_key'	=> $question_unique_key,
				'question'				=> $question,
				'answer_history'		=> $this->quiz_lib->get_answer_history($this->_guid, $question_guid, $question_type)
			);
			
			$this->template->set('page','quiz');
			$this->template->set_partial('navigation','partials/new_vertical_navigation', array('highlight' => 'course'));
			
			$node = $this->my_lib->get_a_subtype_row('nodes', $node_unique_key, array('title'));
			$node_title = $node['title'];
			$return_link = base_url("course/node/$course_unique_key/$section_unique_key/$node_unique_key");
			$arr = array('header_type' => 'return', 'text' => $node_title, 'return_link' => $return_link);
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			
			$this->template->set_partial('sidebar', 'course/new/quiz_sidebar', $quiz);
			$this->template->set_layout('new_layout_3');
			
			if($question_type == 'OPTION')
			{
				$this->template->build('course/new/question_multiple_selection', $data);
			}
			elseif($question_type == 'GAP')
			{
				$this->template->build('course/new/question_gaps', $data);
			}
		}
		
		//回答一个问题
		function ajax_answer_a_question()
		{
			if(CI_POST('answer_a_question'))
			{
				$this->load->library('form_validation');
				$this->load->library('quiz_lib');
				
				$question_unique_key 	= CI_POST('question_unique_key');
				$data					= CI_POST('data');
				
				$this->form_validation->set_rules('question_unique_key', 'question_unique_key', 'required|callback_is_existent[questions.unique_key]');
				
				if(!$this->form_validation->run())
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				else
				{
					$fields = array('type', 'guid', 'quiz_guid', 'course_guid');
					$question = $this->my_lib->get_a_subtype_row('questions', $question_unique_key, $fields);
					
					$question_guid 	= $question['guid'];
					$question_type 	= $question['type'];
					$quiz_guid		= $question['quiz_guid'];
					$course_guid	= $question['course_guid'];
					
					$this->quiz_lib->answer_a_question($question_guid, $question_type, $quiz_guid, $course_guid, $data);
					$this->ajax_ini();
				}
				
				$this->ajax_response();
			}
		}
		
		//获取一个课程的信息
		function ajax_get_a_course_for_enrollment()
		{
			if(CI_POST('get_a_course_for_enrollment'))
			{	
				//加载“form_validation”的库类
				$this->load->library('form_validation');
				
				//获取用户要加入的“course_unqieu_key”
				$course_unique_key 	= CI_POST('course_unique_key');
				
				//检验用户传来的这个“course_unique_key”
				$this->form_validation->set_rules('course_unique_key', 'course_unique_key', 'required|callback_is_existent[courses.unique_key]');
				
				//获取当前用户的GUID
				$user_guid = $this->_guid;
				
				//获取课程的信息
				$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, array('title', 'is_course_free'));
				
				//该用户是否免费	
				$is_course_free = $course['is_course_free'];
				
				//计算出这个课程的GUID
				$course_guid = $this->my_lib->get_guid_by_unique_key($course_unique_key);
				
				//获取该课程的标题
				$course_title = $course['title'];
				
				if($this->form_validation->run())
				{	
					if($is_course_free OR $this->course_lib->is_course_paid($user_guid, $course_guid))
					{
						$data = array('is_user_login' => $this->_is_login, 'course_title' => $course_title);
						$this->ajax_ini($data);
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
		
		/***
		//用户查看一个“section”
		function section($course_unique_key, $section_unique_key)
		{
			$user_guid = $this->_guid;
		
			//分别获取“course”和“section”的GUID
			$course_guid	= $this->my_lib->get_guid_by_unique_key($course_unique_key);
			$section_guid 	= $this->my_lib->get_guid_by_unique_key($section_unique_key);
			
			$course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, array('title'));
			$section = $this->my_lib->get_a_subtype_row('sections', $section_unique_key);
			
			//判断该“section”是否包含“single_attached_node”
			$condition = array('title' => 'single_attached_node', 'section_guid' => $section_guid);
			$if_single_node_attached = $this->my_lib->check_a_record('nodes', $condition);
			
			$this->template->set_partial('navigation','partials/new_vertical_navigation', array('highlight' => 'course'));
			
			$arr = array('header_type' => 'return', 'text' => $course['title'], 'return_link' => base_url("course/$course_unique_key/home#$section_unique_key"));
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			
			$this->template->set('page','learn');
			
			if(!$if_single_node_attached)
			{
				$data = array(
					'title'					=> $section['title'],
					'main'					=> $section['main'],
					'course_unique_key'		=> $course_unique_key,
					'section_unique_key'	=> $section_unique_key
				);
				
				$this->template->build('course/new/learn_section', $data);		
			}
			else
			{
				$node_guid = $this->my_lib->get_a_value('nodes', 'guid', $condition);
				$node_unique_key = $this->my_lib->get_unique_key_by_guid($node_guid);
				$data = $this->get_a_node_info($node_guid, $node_unique_key, $section_unique_key, $course_unique_key, $section['title'], $section['main']);
				
				//获取当前用户是否曾经学过这个知识点
				$data['if_learnt'] = $this->course_lib->if_a_node_learned($user_guid, $node_guid);
				
				$this->template->build('course/new/course_learn', $data);
			}
			
		}
		
		*/
		
		
	}