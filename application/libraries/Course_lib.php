<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
	
	class Course_lib extends MY_lib{
		
		function __construct(){
			parent::__construct();
			$this->ci = & get_instance();
		}
		
		/**
			这是一个获取用户dashboard的方法
		*/
		
		//获取用户尚未完成的课程
		public function get_user_uncompleted_courses($user_guid){
		
			$arr = array();
			
			//获取当前用户正在学习课程的GUID
			$condition	= array('user_guid' => $user_guid, 'action' => LEARNING_A_COURSE);
			$fields		= array('course_guid', 'time_updated');
			$courses 	= $this->get_records('user_course_relations', $fields, $condition);
			
			//如果课程数为0, 返回空数组
			if(sizeof($courses) == 0){
				return $arr;
			}
			
			$count = 0; //设置读数
			
			foreach($courses AS $row){
			
				$course_guid 	= $row['course_guid']; //获取当前课程的GUID
				$time_updated	= $row['time_updated']; //获取当前课程学习的最后时间
				
				//计算当前用户完成该课程的百分比
				$percentage_of_course_completion = $this->get_percentage_of_course_completion($user_guid, $course_guid);
				
				//获取当前用户对于本课还没有学习的知识点的数量
				$number_of_nodes_unlearned = $this->get_number_of_nodes_unlearned($user_guid, $course_guid);
				
				//获取该课程的全部信息
				$fields = array('unique_key', 'title', 'publish_option', 'template');
				$course_info = $this->get_a_subtype_row('courses', (int)$course_guid, $fields);
				
				//数组的初始化
				$arr[$count] = array(
					'time_updated'						=> $time_updated,
					'percentage_of_course_completion'	=> $percentage_of_course_completion,
					'number_of_nodes_unlearned'			=> $number_of_nodes_unlearned,
					'course_unique_key' 				=> $course_info['unique_key'],
					'course_title'						=> $course_info['title'],
					'course_publish_option'				=> $course_info['publish_option'],
					'course_template'					=> $course_info['template'],
					'course_url'						=> base_url('course/'.$course_info['unique_key'].'/home'),
					'current_node_title'				=> '',
					'current_node_url'					=> '',
					'next_node_title'					=> '',
					'next_node_url'						=> '',
					'next_node_direction'				=> ''
				);
				
				if($percentage_of_course_completion == 0){
					
					/**
						如果当前用户还未开始学习这个课程，输出用户应该学习的第一个知识点
					*/
					
					//如果用户还没有开始学习，返回该课程的第一个知识点
					$first_node = $this->get_a_course_first_node($course_guid);
					
					//获取第一个知识点所属的“section”的“guid”
					$first_node_section_guid 	= $first_node['section_guid'];
					$first_node_unique_key 		= $first_node['unique_key'];
					
					//获取这个“section”的“unique_key”
					$first_node_section_unique_key = $this->get_unique_key_by_guid($first_node_section_guid);
					
					//获取这个知识点的地址
					$first_node_url 	= base_url('course/node/'.$course_info['unique_key'].'/'.$first_node_section_unique_key.'/'.$first_node_unique_key);
					$first_node_title 	= $first_node['title'];
					
					if($first_node_title == 'single_attached_node'){
						$first_node_title = $this->get_a_single_attached_node_section_title($first_node['guid'], $first_node_section_guid, true, $course_guid);
					}
					
					$arr[$count]['next_node_url'] 	= $first_node_url;
					$arr[$count]['next_node_title'] = $first_node_title;
				
				} elseif ($percentage_of_course_completion > 0 AND $percentage_of_course_completion < 100) {
					
					//获取该用户学习该课程的最新进度
					$current_learned_node = $this->get_current_learned_node($user_guid, $course_guid);
					
					//获取用户学习该课程的最新更新时间
					$arr[$count]['time_updated'] = $current_learned_node['time_updated'];
					
					//用户最近学习的知识点
					$current_learned_node_guid 			= $current_learned_node['node_guid'];
					$current_node 						= $this->get_a_subtype_row('nodes', (int)$current_learned_node_guid, array('unique_key', 'title', 'guid', 'section_guid'));
					
					$current_node_title 				= $current_node['title'];
					$current_node_section_guid 			= $current_node['section_guid'];
					$current_node_section_unique_key 	= $this->get_unique_key_by_guid($current_node_section_guid);
					$current_node_unique_key 			= $current_node['unique_key'];
					
					if($current_node_title == 'single_attached_node'){
						$current_node_title = $this->get_a_single_attached_node_section_title($current_node['guid'], $current_node_section_guid, true, $course_guid);
					}
					
					$arr[$count]['current_node_unique_key'] = $current_node_unique_key;
					$arr[$count]['current_node_title'] 		= $current_node_title;
					$arr[$count]['current_node_url'] 		= base_url('course/node/'.$course_info['unique_key'].'/'.$current_node_section_unique_key.'/'.$current_node_unique_key);
					
					//返回下一个还没有学习的课程
					$course_menu 			= $this->get_syllabus_menu_for_course_home($course_guid);
					$course_menu			= $this->check_if_node_learned($course_menu, $user_guid);
					$flat_course_menu 		= $this->flat_a_nested_tree($course_menu);
					$current_pointer 		= $this->pointer($flat_course_menu, $current_learned_node_guid);
					
					$next_pointer_array 	= $this->search_unlearned_pointer($current_pointer, $flat_course_menu);
					$next_pointer			= $next_pointer_array['pointer'];
					$next_pointer_direction = $next_pointer_array['direction']; 
					
					$next_node_guid					= $flat_course_menu[$next_pointer]['guid'];
					$next_node 						= $this->get_a_subtype_row('nodes', (int)$next_node_guid, array('unique_key', 'title', 'section_guid'));
					$next_node_unique_key 			= $next_node['unique_key'];
					$next_node_title 				= $next_node['title'];
					$next_node_section_guid 		= $next_node['section_guid'];
					$next_node_section_unique_key 	= $this->get_unique_key_by_guid($next_node_section_guid);
					
					if($next_node_title == 'single_attached_node'){
						$next_node_title = $this->get_a_single_attached_node_section_title($next_node_guid, $next_node_section_guid, true, $course_guid);
					}
					
					$arr[$count]['next_node_title'] 	= $next_node_title;
					$arr[$count]['next_node_url'] 		= base_url('course/node/'.$course_info['unique_key'].'/'.$next_node_section_unique_key.'/'.$next_node_unique_key);
					$arr[$count]['next_node_direction']	= $next_pointer_direction;
				
				} elseif($percentage_of_course_completion == 100) {
					
					
					
				}
				
				$count++;
			}
			
			usort($arr, function($a, $b) {
				return $b['time_updated'] - $a['time_updated'];
			});
			
			return $arr;
		}
		
		//获取推荐给用户的课程
		public function get_recommended_courses($user_guid)
		{
			$sql = "SELECT guid, unique_key, title, template AS course_template, main AS summary, template FROM courses where courses.status = 'published' AND courses.guid ";
			$sql.= "NOT IN (SELECT course_guid FROM `user_course_relations` WHERE user_guid = $user_guid) ORDER BY courses.guid";
			
			$result = $this->get_records_with_sql($sql);
			
			//获取每个课程拥有的知识节点数量
			for($i = 0; $i < sizeof($result); $i++)
			{
				$result[$i]['number_of_course_nodes'] = $this->get_course_number_of_nodes($result[$i]['guid']);
				$result[$i]['number_of_mp3'] = $this->ci->count_lib->get_count($result[$i]['guid'], 'mp3');
				$result[$i]['number_of_video'] = $this->ci->count_lib->get_count($result[$i]['guid'], 'video');
				$result[$i]['number_of_image'] = $this->ci->count_lib->get_count($result[$i]['guid'], 'image');
			}
			
			return $result;
		}
		
		//获取用户已完成的课程
		public function get_completed_course($user_guid)
		{
			$condition = array('user_guid' => $user_guid, 'action' => 'complete_a_course');
			$fields = array('course_guid', 'time_updated');
			$completed_courses = $this->get_records('user_course_relations', $fields, $condition);
			
			if(sizeof($completed_courses) > 0)
			{
				
			}
		}
		
		//获取全部课程
		public function get_all_available_courses(){
		
			$sql = "SELECT owner_guid, time_updated, courses.* FROM entities ";
			$sql.= "JOIN courses ";
			$sql.= "where entities.guid = courses.guid ";
			$sql.= "AND status = 'PUBLISHED' ";
			$sql.= "order by time_created DESC";
			
			$result = $this->get_records_with_sql($sql);
			
			for($i = 0; $i < sizeof($result); $i++){
			
				$result[$i]['course_template'] 			= $result[$i]['template'];
				$result[$i]['summary'] 					= $result[$i]['main'];
				$result[$i]['owner_unique_key'] 		= $this->get_unique_key_by_guid($result[$i]['owner_guid']);
				$result[$i]['owner_username']		 	= $this->get_a_value('users', 'username', array('guid' => $result[$i]['owner_guid']));
				$result[$i]['number_of_course_nodes'] 	= $this->get_course_number_of_nodes($result[$i]['guid']);
				$result[$i]['total_mp3_duration']		= $this->calculate_total_duration_mp3_course($result[$i]['guid']);
				$result[$i]['total_video_duration']		= $this->calculate_total_duration_video_course($result[$i]['guid']);
				$result[$i]['publish_option']			= $result[$i]['publish_option'];
			
			}
			
			return $result;			
			
		}
		
		//获取一门课有多少节点
		public function get_course_number_of_nodes($course_guid)
		{
			$condition = array('course_guid' => $course_guid);
			return $this->get_records('nodes', 1, $condition, NULL, 0, NULL, 'DESC', TRUE);
		}
		
		//获取一门课有多少习题
		/**
		public function get_course_number_of_questions($course_guid)
		{
			return $this->ci->count_lib->get_count($course_guid, 'question');
		}
		*/
		
		//获取一门课有多少课节
		public function get_course_number_of_sections($course_guid)
		{
			$condition = array('course_guid' => $course_guid, 'status' => 'PUBLISHED');
			return $this->get_records('sections', 1, $condition, NULL, 0, NULL, 'DESC', TRUE);
		}
		
		//获取一个section下面有多少个知识点
		function get_section_number_of_nodes($section_guid)
		{
			$condition = array('section_guid' => $section_guid, 'status' => 'PUBLISHED');
			return $this->get_records('nodes', 1, $condition, NULL, 0, NULL, 'DESC', TRUE);
		}
		
		//获取一个用户针对一个课程学习的百分比
		function get_percentage_of_section_completion($user_guid, $section_guid){
		
			//计算出本课程的全部课节数
			$number_of_section_nodes = $this->get_section_number_of_nodes($section_guid);
		
			//计算出该用户完成的节点数
			$number_of_nodes_completed = $this->get_number_of_nodes_completed($user_guid, $section_guid, 'section');
			
			if($number_of_section_nodes == 0) return 100;
			else return round($number_of_nodes_completed / $number_of_section_nodes * 100);
		}
		
		//获取一个用户针对一个课程学习的百分比
		function get_percentage_of_course_completion($user_guid, $course_guid)
		{
			//计算出本课程的全部课节数
			$number_of_course_nodes = $this->get_course_number_of_nodes($course_guid);
		
			//计算出该用户完成的节点数
			$number_of_nodes_completed = $this->get_number_of_nodes_completed($user_guid, $course_guid, 'course');
			
			if($number_of_course_nodes != 0)
			{
				return round($number_of_nodes_completed / $number_of_course_nodes * 100);
			}
			else
			{
				return 0;
			}
		}
		
		//计算出一个用户在一个课程或一个课节内完成的知识点的数量
		public function get_number_of_nodes_completed($user_guid, $entity_guid, $type = 'course')
		{
			$condition = array(
				'user_guid'	=> $user_guid,
				'action'	=> 'learn_a_node',
			);
			
			$condition[$type.'_guid'] = $entity_guid;
			
			return $this->get_records('user_course_relations', '1', $condition, NULL, 0, NULL, 'DESC', true);
		}
		
		//计算出一个用户还剩下多少知识点才能完成一个课程
		public function get_number_of_nodes_unlearned($user_guid, $course_guid)
		{
			return $this->get_course_number_of_nodes($course_guid) - $this->get_number_of_nodes_completed($user_guid, $course_guid);
		}
		
		public function get_number_of_sections_completed($user_guid, $course_guid)
		{
			$condition = array(
				'user_guid' 	=> $user_guid,
				'course_guid' 	=> $course_guid,
				'action' 		=> 'learn_a_node'
			);
			
			return $this->get_records('user_course_relations', '1', $condition, NULL, 0, NULL, 'DESC', true);
		}
		
		//获取一个课程的第一个节点
		public function get_a_course_first_node($course_guid)
		{
			//首先，获得这个课程第一个SECTION的GUID
			$sql = "SELECT sections.guid, entities.guid FROM sections ";
			$sql.= "JOIN entities ON sections.guid = entities.guid ";
			$sql.= "WHERE sections.course_guid = $course_guid ";
			$sql.= "ORDER BY entities.weight ASC LIMIT 1";
			$section = $this->get_records_with_sql($sql);
			$first_section_guid = $section[0]['guid'];
			
			//然后根据"first_section_guid"，计算出
			$sql  = "SELECT nodes.title, nodes.section_guid, nodes.unique_key, nodes.guid FROM entities ";
			$sql .= "JOIN nodes ON entities.guid = nodes.guid ";
			$sql .= "WHERE nodes.course_guid = $course_guid AND nodes.father_guid = $first_section_guid ";
			$sql .= "ORDER BY weight ASC LIMIT 1";
			$result = $this->get_records_with_sql($sql);
			
			return $result[0];
		}
		
		
		// ********************************************************
		// 下面是建立用户和课程关系的一系列方法
		// ********************************************************
		
		/*
			当一个课程为付费课程，用户支付了该课程：
				- buy_a_course
				- learning_a_course 
				- follow_a_course
				
			当一个课程为免费课程，用户加入了该课程
				- join_a_course
				- follow_a_course
				
			当一个课程为付费课程，用户仅仅支付了其中的一个课节，
				- pay_a_section
				- follow_a_course
			
			当一个用户退出一个课程，该用户
				- quit_a_course
				- unfollow_a_course
				
				如果课程是付费用户，用户已经支付，用户可以再次
					- join_a_course
					- follow_a_course
				
				如果课程是免费课程，用户虽然退出了课程，用户还可以再次
					- join_a_course
					- follow_a_course
			
			当一个用户不想收到和课程有关的任何信息，
				-unfollow_a_course
			 
		*/
		
		//用户学习一个知识点
		public function learn_a_node($course_guid, $section_guid, $node_guid, $user_guid)
		{
			if($this->if_a_node_learned($user_guid, $node_guid))
			{
				$condition = array('user_guid' => $user_guid, 'node_guid' => $node_guid, 'action' => 'learn_a_node');
				$data =  array('time_updated' => time());
				return $this->update_records('user_course_relations', $data, $condition);
			}
			else
			{
				$data = array(
					'course_guid'	=> $course_guid,
					'section_guid'	=> $section_guid,
					'node_guid'		=> $node_guid,
					'user_guid'		=> $user_guid,
					'time_updated'	=> time(),
					'action'		=> 'learn_a_node'
				);
				
				return $this->create_a_record('user_course_relations', $data);
			}
		}
		
		//判断一个用户是否已经学习过一个知识点
		public function if_a_node_learned($user_guid, $node_guid)
		{
			$condition = array(
				'node_guid' => $node_guid,
				'user_guid' => $user_guid,
				'action' 	=> 'learn_a_node'
			);
			
			return $this->check_a_record('user_course_relations', $condition);
		}
		
		//用户标注没有学一个知识点
		public function unlearn_a_node($user_guid, $node_guid)
		{
			$condition = array(
				'user_guid' => $user_guid,
				'node_guid' => $node_guid,
				'action' 	=> 'learn_a_node'
			);
			
			return $this->delete_records('user_course_relations', $condition);
		}
		
		//用户支付了一个课程
		public function pay_a_course($user_guid, $course_guid)
		{
			if(!$this->if_a_course_paid($user_guid, $course_guid))
			{
				$data = array(
					'course_guid'	=> $course_guid,
					'section_guid'	=> 0,
					'node_guid'		=> 0,
					'user_guid'		=> $user_guid,
					'time_updated'	=> time(),
					'action'		=> 'pay_a_course'
				);
				
				$this->create_a_record('user_course_relations', $data);
			}
			
			return null;
		}
		
		//判断一个课程是否被某一个用户支付
		public function if_a_course_paid($user_guid, $course_guid)
		{
			$condition = array('course_guid' => $course_guid, 'user_guid' => $user_guid, 'action' => 'pay_a_course');
			return $this->check_a_record('user_course_relations', $condition);
		}
		
		//用户支付了一个课节
		public function pay_a_section($user_guid, $course_guid, $section_guid)
		{
			if(!$this->if_a_section_paid($user_guid, $section_guid))
			{
				$data = array(
					'course_guid'	=> $course_guid,
					'section_guid'	=> $section_guid,
					'node_guid'		=> 0,
					'user_guid'		=> $user_guid,
					'time_updated'	=> time(),
					'action' 		=> 'pay_a_section'
				);
				
				$this->create_a_record('user_course_relations', $data);
			}
			
			return;
		}
		
		//判断一个课程是否被某一个用户支付
		public function if_a_section_paid($user_guid, $section_guid)
		{
			$condition = array('section_guid' => $section_guid, 'user_guid' => $user_guid, 'action' => 'pay_a_section');
			return $this->check_a_record('user_course_relations', $condition);
		}
		
		// 用户关注了一门课程
		public function follow_a_course($user_guid, $course_guid){
			if(!$this->if_a_course_followed()){
				$data = array(
					'course_guid' => $course_guid, 
					'section_guid' => 0,
					'node_guid' => 0,
					'user_guid' => $user_guid,
					'time_updated' => time(),
					'action' => 'follow_a_course'
				);
				$this->create_a_record('user_course_relations', $data);
			}
			
			RETURN;
		}
		
		//判断一个课程是否被某一个用户支付
		public function if_a_course_followed($user_guid, $course_guid){
			$condition = array('course_guid' => $course_guid, 'user_guid' => $user_guid, 'action' => 'follow_a_section');
			RETURN $this->check_a_record('user_course_relations', $condition);
		}
		
		//用户取消关注了一门课程
		function unfollow_a_course($user_guid, $course_guid){
			$condition = array('user_guid' => $user_guid, 'course_guid' => $course_guid, 'action' => 'follow_a_course');	
			return $this->delete_records('user_course_relations', $condition);
		}
		
		//用户加入了一门课程
		function join_a_course($user_guid, $course_guid){	
			if(!$this->if_a_course_joined($user_guid, $course_guid)){
				$data = array('course_guid'	=> $course_guid, 'section_guid' => 0, 'node_guid' => 0, 'user_guid' => $user_guid, 'time_updated' => time(), 'action' => LEARNING_A_COURSE);
				$this->create_a_record('user_course_relations', $data);
			}
			
			RETURN;
		}
		
		//判断一个用户是否已加入某课程
		function if_a_course_joined($user_guid, $course_guid){
			$condition = array('user_guid' => $user_guid, 'course_guid' => $course_guid, 'action' => LEARNING_A_COURSE);
			return $this->check_a_record('user_course_relations', $condition);
		}
		
		//用户离开了一门课程
		function quit_a_course($user_guid, $course_guid){
			
			//初始化条件
			$condition = array('user_guid' => $user_guid, 'course_guid' => $course_guid);
			
			//删除“join_a_course”的关系
			$condition['action'] = LEARNING_A_COURSE;
			$this->delete_records('user_course_relations', $condition);
			
			//删除所有的“learn_a_node”的关系
			$condition['action'] = 'learn_a_node';
			$this->delete_records('user_course_relations', $condition);
			
			//删除“follow_a_course”的关系
			$this->unfollow_a_course($user_guid, $course_guid);
			
			RETURN;
		}
		
		// ********************************************************
		// 节点的树状结构相关操作
		// ********************************************************
		
		//获取一个课程的全部课节
		function get_sections($course_guid)
		{
			$sql = "SELECT sections.unique_key, sections.title, sections.main, sections.price, sections.is_open, entities.guid FROM sections ";
			$sql.= "JOIN entities ON sections.guid = entities.guid WHERE sections.course_guid = $course_guid ORDER BY entities.weight ASC";
			return $this->get_records_with_sql($sql);
		}
		
		
		
		
		
		//输入一个“section”的GUID，返回该“section”是第几课
		function get_index_number_of_a_section($section_guid, $course_guid){
		
			$sections = $this->get_sections($course_guid);
			$count = 1;
			
			foreach($sections AS $row){
				if($row['guid'] == $section_guid){
					return $count;
				} else {
					$count++;
				}
			}
			
			return;
		}
		
		
		/**
			如果一个节点的标题是“single_attached_node”，这个节点须显示其父级“section”的标题。
			如果参数没有提供“section_guid”，函数通过“node_guid”来计算出“section_guid”
			当“is_index_number_prefixed”为“true”，在标题前显示“第X课”。如果函数调用者希望显示“第x课”的话，
			最好将课程的“guid”也提供给函数，否则函数还要根据“node_guid”计算“course_guid”
		*/
		
		function get_a_single_attached_node_section_title($node_guid, $section_guid = 0, $is_index_number_prefixed = FALSE, $course_guid = 0){
			
			//如果用户没有提供“section”的GUID，即首先通过“node_guid”，计算出该“node”的GUID
			if($section_guid == 0){
				$section_guid = $this->get_a_value('nodes', 'section_guid', array('guid' => $node_guid));
			}
			
			$title = $this->get_a_value('sections', 'title', array('guid' => $section_guid));
			
			if($is_index_number_prefixed){
				
				if($course_guid == 0){
					$course_guid = $this->get_a_value('nodes', 'course_guid', array('guid' => $node_guid));
				}
				
				$index_number = $this->get_index_number_of_a_section($section_guid, $course_guid);		
				$title = '第'.$index_number.'课：'.$title;
			}
			
			return $title;
		}
		
		//判断一个“section”是否仅包含“single_attached_node”
		function if_a_section_single_node_attached($section_guid)
		{
			$condition = array('title' => 'single_attached_node', 'section_guid' => $section_guid);
			return $this->check_a_record('nodes', $condition);
		}
		
		//根据一个包含“single_attached_node”的“section”的“guid”，获取这个“single_attached_node”的“unique_key”
		function get_a_single_node_attached_unique_key($section_guid)
		{
			$condition = array('title' => 'single_attached_node', 'section_guid' => $section_guid);
			return $this->get_a_value('nodes', 'unique_key', $condition);
		}
		
		
		
		
		
		
		
		
		//计算一个node节点的音频总长度
		function calculate_total_duration_mp3_node($node_guid)
		{
			//获取音频文件的ID
			$condition = array('guid' => $node_guid, 'relation' => strtoupper('audio attachment'));
			$audio_file_ids = $this->get_records('file_entity_relations', array('id'), $condition);
			
			//初始化音频长度
			$duration = 0;
			
			if(sizeof($audio_file_ids) > 0)
			{
				foreach($audio_file_ids AS $row)
				{
					$file_duration = $this->get_a_value('files', 'duration', array('id' => $row['id']));
					if($file_duration == null or $file_duration == '') $file_duration = 0;
					$duration = $duration + $file_duration;
				}
			}
			
			return $duration;
		}
		
		//计算某一个“section”下的全部“node”所包含的mp3的总长度
		function calculate_total_duraition_mp3_section($section_guid)
		{
			//获取音频文件的ID
			$condition = array('section_guid' => $section_guid);
			$node_guids = $this->get_records('nodes', array('guid'), $condition);
			
			//初始化音频长度
			$duration = 0;
			
			if(sizeof($node_guids) > 0)
			{
				foreach($node_guids AS $row)
				{
					$node_mp3_duration = $this->calculate_total_duration_mp3_node($row['guid']);
					$duration = $duration + $node_mp3_duration;
				}
			}
			
			return $duration;
		}
		
		//计算某一个“course”下的全部“node”所包含的mp3的总长度
		function calculate_total_duration_mp3_course($course_guid)
		{
			//获取音频文件的ID
			$condition = array('course_guid' => $course_guid);
			$node_guids = $this->get_records('nodes', array('guid'), $condition);
			
			//初始化音频长度
			$duration = 0;
			
			if(sizeof($node_guids) > 0)
			{
				foreach($node_guids AS $row)
				{
					$node_mp3_duration = $this->calculate_total_duration_mp3_node($row['guid']);
					$duration = $duration + $node_mp3_duration;
				}
			}
			
			return $duration;
		}
		
		//计算出一个“node”所包含的 “video”的长度
		function calculate_total_duration_video_node($node_guid)
		{
			return $this->get_a_value('nodes', 'video_duration', array('guid' => $node_guid));
		}
		
		//计算出一个“section”所包含的全部“video”的长度
		function calculate_total_duration_video_section($section_guid)
		{
			//获取音频文件的ID
			$condition = array('section_guid' => $section_guid);
			$node_guids = $this->get_records('nodes', array('guid'), $condition);
			
			//初始化音频长度
			$duration = 0;
			
			if(sizeof($node_guids) > 0)
			{
				foreach($node_guids AS $row)
				{
					$node_video_duration = $this->calculate_total_duration_video_node($row['guid']);
					$duration = $duration + $node_video_duration;
				}
			}
			
			return $duration;
		}
		
		//计算出一个“course”所包含的全部“video”的长度
		function calculate_total_duration_video_course($course_guid)
		{
			//获取音频文件的ID
			$condition = array('course_guid' => $course_guid);
			$node_guids = $this->get_records('nodes', array('guid'), $condition);
			
			//初始化音频长度
			$duration = 0;
			
			if(sizeof($node_guids) > 0)
			{
				foreach($node_guids AS $row)
				{
					$node_video_duration = $this->calculate_total_duration_video_node($row['guid']);
					$duration = $duration + $node_video_duration;
				}
			}
			
			return $duration;
		}
		
		//获取课程机构的菜单
		function get_syllabus_menu_for_course_home($course_guid)
		{
			return $this->populate_children_for_course_home($course_guid);
		}
		
		private function populate_children_for_course_home($father_guid){
			$array = array();
			
			$node_subtype_id 	= $this->get_subtype_id('node');
			$section_subtype_id	= $this->get_subtype_id('section');
			
			$condition = array('father_guid' => $father_guid, 'visibility' => true);
			$fields = array('guid','subtype_id','father_guid','unique_key','title','main');
			$result = $this->get_records('entities', $fields, $condition, NULL, 0, 'weight', 'ASC');
			
			if(count($result) > 0){
			
				$section_count = 1;
				foreach($result AS $row){
				
					$subtype_id = $row['subtype_id'];
					
					//如果用户的“subtype”是“node”，计算出该“subtype”的“section_unque_key”
					$subtype = $this->get_subtype($subtype_id);
					
					$section_single_node_attached_unique_key = '';
					
					if($subtype == 'node'){
					
						$condition = array('guid' => $row['guid']);
						$section_guid = $this->get_a_value('nodes', 'section_guid', $condition);
						$section_unique_key = $this->get_unique_key_by_guid($section_guid);
						
						$section_price		= '';
						$section_is_open 	= '';
						$section_if_single_node_attched = '';
						
						$title = $row['title'];
						
						if($row['title'] == 'single_attached_node'){
							$row['title'] = $this->get_a_single_attached_node_section_title($row['guid']);
						}
						
					} elseif($subtype == 'section') {
					
						$section_unique_key = $row['unique_key'];
						$fields = array('guid', 'price', 'is_open');
						$section = $this->get_a_subtype_row('sections', $section_unique_key, $fields);
						
						$section_price 		= $section['price'];
						$section_is_open 	= $section['is_open'];
						$section_guid 		= $section['guid'];
						
						$title = '第'.$section_count.'课：'.$row['title'];
						
						$section_count++;
						
						$section_if_single_node_attched = $this->if_a_section_single_node_attached($section_guid);
						
						if($section_if_single_node_attched)
						{
							$section_single_node_attached_unique_key = $this->get_a_single_node_attached_unique_key($section_guid);
						}
						
					}
					
					if($subtype == 'node' OR $subtype == 'section'){
					
						$array[] = array(
							'guid'										=> $row['guid'],
							'subtype_id'							 	=> $row['subtype_id'],
							'subtype'									=> $subtype,
							'father_guid'								=> $row['father_guid'],
							'unique_key'								=> $row['unique_key'],
							'title'										=> $title,
							'summary'									=> $row['main'],
							'section_unique_key'						=> $section_unique_key,
							'number_of_images'							=> $this->ci->count_lib->get_count($row['guid'], 'image'),
							'number_of_mp3_files'						=> $this->ci->count_lib->get_count($row['guid'], 'mp3'),
							'number_of_video_clips'						=> $this->ci->count_lib->get_count($row['guid'], 'video'),
							'section_price'								=> $section_price,
							'section_is_open'							=> $section_is_open,
							'section_if_single_node_attached' 			=> $section_if_single_node_attched,
							'section_single_node_attached_unique_key' 	=> $section_single_node_attached_unique_key,
							'child'										=> $this->populate_children_for_course_home($row['guid'])
						);
					}
				}	
			}
			
			return $array;
		}
		
		//把“syllabus”的输入，检查每个“node”用户是否学过，“syllabus”原样输出，不做增加了“if_node_learned”
		function check_if_node_learned($array, $user_guid = 0)
		{
			for($i=0; $i<sizeof($array); $i++)
			{
				$guid = $array[$i]['guid'];
				$subtype = $array[$i]['subtype'];
				
				$array[$i]['if_node_learned'] = false;
				
				if($subtype == 'node' AND $user_guid != 0)
				{
					$array[$i]['if_node_learned'] =  $this->if_a_node_learned($user_guid, $guid);
				}
				
				if(sizeof($array[$i]['child']) > 0)
				{
					$array[$i]['child'] = $this->check_if_node_learned($array[$i]['child'], $user_guid);
				}
			}
			
			return $array;
		}
		
		/**
			根据用户的ID和课程的信息来判断用户和某课程之间的关系
			课程信息是数组，下面信息：
				- course_guid
				- course_unique_key
				- publish_option
				- is_course_free
				- fee_policy
				- by_course_fee
				- access
				
			情况：
				
				模式 1: 用户没有学习中，课程免费
				模式 2: 用户没有学习中，课程不免费，按照整个课程收费
				模式 3: 用户没有学习中，课程不免费，按照单课节收费
				模式 4: 用户没有学习中，课程不免费，按照双轨方式收费
				模式 5：用户已经加入该课程
				模式 6：用户没用学习中，课程不免费，但用户曾支付过这个课程
		*/
		
		
		function check_accessibility($course_data, $user_guid, $syllabus){
		
			$result_arr = array();
		
			$course_guid 		= $course_data['course_guid'];
			$course_unique_key 	= $course_data['course_unique_key'];
			$publish_option 	= $course_data['publish_option'];
			$is_course_free 	= $course_data['is_course_free'];
			$fee_policy			= $course_data['fee_policy'];
			$by_course_fee		= $course_data['by_course_fee'];
			$access				= $course_data['access'];
		
			//首先判断用户是否正在学这门课“learn_a_course”
			$condition = array('user_guid' => $user_guid, 'course_guid' => $course_guid, 'action' => LEARNING_A_COURSE);
			
			if($this->check_a_record('user_course_relations', $condition))
			{
				$result_arr['mode'] = 5;
				$result_arr['learning_progress'] = $this->check_learning_progress($user_guid, $course_guid, $syllabus);
				
			}
			else
			{
				if($is_course_free)
				{
					$result_arr['mode'] = 1;
				}
				else
				{
					if($fee_policy == 'BY_COURSE')
					{
						$result_arr['mode'] = 2;
						$result_arr['by_course_price'] = $by_course_fee;
					}
					elseif($fee_policy == 'BY_SECTION')
					{
						$result_arr['mode'] = 3;
						$result_arr['lowest_section_price'] = $this->get_lowest_section_price($course_guid);
					}
					elseif($fee_policy == 'BY_BOTH')
					{
						$result_arr['mode'] = 4;
						$result_arr['by_course_price'] = $by_course_fee;
						$result_arr['lowest_section_price'] = $this->get_lowest_section_price($course_guid);
					}
				}
			}
			
			return $result_arr;
		}
		
		/*
			判断一个用户是否可以访问一个知识点
			
			节点信息包括:
				
			- node_guid
			- section_guid
			- course_guid
			- is_course_free
			- course_publish_option
			- fee_policy
			
				
		*/
		
		function is_node_accessible($user_guid, $course_guid, $section_guid, $node_guid, $is_course_free){
			
			//首先判断，该知识点所属课程是否开放。如果开放，访问权限为“TRUE”
			if($this->get_a_value('sections', 'is_open', array('guid' => $section_guid))){
				return true;
			}
			
			//然后判断，当前用户是否登录，如果当前用户未登陆，访问权限为“FALSE”
			if($user_guid == 0){
				return false;
			}
			
			//判断当前用户是否已加入了该课程
			if($this->check_a_record('user_course_relations', array('user_guid' => $user_guid, 'action' => LEARNING_A_COURSE))){
				return true;
			}
			
			return false;
		}
		
		//获取一个课程价格最低的章节，免费的除外
		public function get_lowest_section_price($course_guid)
		{
			$sql = "SELECT MIN(price) AS price FROM sections where course_guid = $course_guid AND price != 0 AND is_open = 0";
			$result = $this->get_records_with_sql($sql);
			return $result[0]['price'];
		}
		
		
		
		/**
			写出一个方法，输入用户的GUID和COURSE_GUID
			返回：
				- 这个用户是否可以学习该课程
				- 这个用户完成该课程的百分比
				- 这个用户上次学习完成的知识点
					- 知识点ID
					- 知识点的unique_key
					- 知识点标题
					- 知识点的链接地址
		*/
		
		function check_learning_progress($user_guid, $course_guid, $syllabus){
		
			$is_access_allowed = false;
		
			//首先检查用户是否可以学这门课程
			$condition = array('user_guid' => $user_guid, 'course_guid' => $course_guid, 'action' => LEARNING_A_COURSE);
			
			if($this->check_a_record('user_course_relations', $condition)){
			
				//用户被允许无限制访问该课程
				$is_access_allowed = true;
				
				//计算出该用户完成该课程的百分比
				$percentage_of_course_completion = $this->get_percentage_of_course_completion($user_guid, $course_guid);
				$data['percentage_of_course_completion'] = $percentage_of_course_completion;
				
				if($percentage_of_course_completion > 0){
				
					$course_unique_key	= $this->get_unique_key_by_guid($course_guid);
					$nested_tree 		= $syllabus;
					$flat_tree 			= $this->flat_a_nested_tree($nested_tree);
					
					//获取用户最近一次学习的知识点
					$current_node = $this->get_current_learned_node($user_guid, $course_guid);
					
					$current_node_guid					= $current_node['node_guid'];
					$current_pointer 					= $this->pointer($flat_tree, $current_node_guid);
					$current_node_section_guid			= $this->get_a_value('nodes', 'section_guid', array('guid' => $current_node_guid));
					$current_node_section_unique_key	= $this->get_unique_key_by_guid($current_node_section_guid);
					$current_node_unique_key			= $flat_tree[$current_pointer]['unique_key'];
					
					$data['current_node_title'] 		= $flat_tree[$current_pointer]['title'];
					$data['current_node_time_updated']	= $current_node['time_updated'];
					$data['current_node_url'] 			= base_url("course/node/$course_unique_key/$current_node_section_unique_key/$current_node_unique_key");
					$data['current_node_section_guid']	= $current_node_section_guid;
					
					/**
					if($percentage_of_course_completion != 100)
					{
						$pointer_array = $this->get_unlearned_pointer($current_pointer, $flat_tree);
						
						if(sizeof($pointer_array) != 0)
						{
							$data['direction'] = $pointer_array['direction'];
							$pointer = $pointer_array['pointer'];
							$pointer_node_guid = $flat_tree[$pointer]['guid'];
							$pointer_node_section_guid = $this->get_a_value('nodes', 'section_guid', array('guid' => $pointer_node_guid));
							$pointer_node_section_unique_key = $this->get_unique_key_by_guid($pointer_node_section_guid);
							$pointer_node_unique_key = $flat_tree[$pointer]['unique_key'];
							
							$data['node_title'] = $flat_tree[$pointer]['title'];
							$data['node_url'] = base_url("course/node/$course_unique_key/$pointer_node_section_unique_key/$pointer_node_unique_key"); 
						}
					}
					*/
				} else {
				
					$first_node 					= $this->get_a_course_first_node($course_guid);
					$first_node_title 				= $first_node['title'];
					$first_node_course_unique_key 	= $this->get_unique_key_by_guid($course_guid);
					$first_node_section_unique_key 	= $this->get_unique_key_by_guid($first_node['section_guid']);
					$first_node_unique_key 			= $first_node['unique_key']; 
					$first_node_url					= base_url("course/node/$first_node_course_unique_key/$first_node_section_unique_key/$first_node_unique_key");
					
					$data['first_node_title'] 		 = $first_node_title;
					$data['first_node_url']			 = $first_node_url;
					$data['first_node_section_guid'] = $first_node['section_guid'];
				
				}
			}
			
			$data['is_access_allowed'] = $is_access_allowed;
			
			return $data;
		}
		
		//经通过递归所生成的多维数组的转化成一维的数组
		private function flat_a_nested_tree($array){
			
			$new_array = array();
			if(sizeof($array) > 0){
				foreach($array AS $row){
					$new_array[] = array(
						'guid'			=> $row['guid'],
						'unique_key'	=> $row['unique_key'],
						'subtype_id'	=> $row['subtype_id'],
						'father_guid'	=> $row['father_guid'],
						'title'			=> $row['title'],
						'if_learned'	=> $row['if_node_learned']
					);
					
					if(count($row['child']) > 0){
						$new_array = array_merge($new_array, $this->flat_a_nested_tree($row['child']));
					}
				}
			}
			
			return $new_array;
		}
		
		//将一维化的数组和一个unique_key输入，返回这个unique_key在素组中的位置
		private function pointer($array, $guid){
			$count = 0;
			$pointer = '';
	
			foreach($array AS $row){
				if($row['guid'] == $guid) return $count;
				$count++;
			}
		}
		
		//获取用户学习某一个课程的最近一次的知识点
		public function get_current_learned_node($user_guid, $course_guid){
		
			$condition = array('user_guid' => $user_guid, 'course_guid' => $course_guid, 'action' => 'learn_a_node');
			$result = $this->get_records('user_course_relations', array('node_guid', 'time_updated'), $condition, 1, 0, 'time_updated', 'DESC');
			
			if(sizeof($result) == 0){
				return array();
			}
			
			return $result[0];
		}
		
		// 获取在某一个课程里，下一个该学的知识点是什么
		public function get_next_pointer($current_pointer, $flat_tree){
		
			$tree_size = sizeof($flat_tree);
			$current_pointer++;
			
			if($tree_size >= $current_pointer){
				if($flat_tree[$current_pointer]['subtype_id'] == 4) return $current_pointer;
				else return $this->get_next_pointer($current_pointer, $flat_tree);
			} else {
				return;
			}
		
		}
		
		public function get_unlearned_pointer($current_pointer, $flat_tree){
		
			$new_arr = array(); //创建一个新数组
			//$current_pointer++;
			
			for($i = 0; $i < sizeof($flat_tree); $i++){
				if($flat_tree[$i]['subtype_id'] == 4 AND !$flat_tree[$i]['if_learned']){
					$new_arr[] = $i;
				}
			}
			
			$pointer = '';
			$direction = 'next';
			
			if(sizeof($new_arr) == 0){
				return array();
			} else {
				for($i = 0; $i < sizeof($new_arr); $i++){
					if($new_arr[$i] > $current_pointer){
						$pointer = $new_arr[$i];
						break;
					}
				}
				
				if($pointer == ''){
					$pointer = $new_arr[0];
					$direction = 'previous';
				}
			}
			
			return array('pointer' => $pointer, 'direction' => $direction);
		}
		
		//下一个要学的东西是啥
		public function search_for_unlearned_pointer($current_pointer, $flat_tree){
			
			//创建一个新的数组，用来容纳所有没有学习的知识点
			$unlearned_tree = array();
			
			foreach($flat_tree AS $row){
				if($row['subtype_id'] == 4 AND $row['if_learned'] == FALSE){
					
				}
			}
		}
		
		//寻找还没有学习的知识点
		public function search_unlearned_pointer($current_pointer, $flat_tree){
			$result = array();
			$next_pointer = $this->get_next_unlearned_pointer($current_pointer, $flat_tree);
			if($next_pointer > 0){
				$result = array('pointer' => $next_pointer, 'direction' => 'next');
			} else {
				$first_pointer = $this->get_first_unlearned_pointer($flat_tree);
				if($first_pointer >= 0){
					$result = array('pointer' => $first_pointer, 'direction' => 'previous');
				}
			}
			return $result;
		}
		
		// 获取在某一个课程里，下一个该学的知识点是什么
		public function get_next_unlearned_pointer($current_pointer, $flat_tree){
			$tree_size = sizeof($flat_tree);
			$current_pointer++;
			if($tree_size > $current_pointer){
				if($flat_tree[$current_pointer]['subtype_id'] == 4 AND !$flat_tree[$current_pointer]['if_learned']){
					return $current_pointer;
				} else {
					return $this->get_next_unlearned_pointer($current_pointer, $flat_tree);
				}
			}
			return;
		}
		
		// 获取在某一个课程里，下一个该学的知识点是什么
		public function get_previous_unlearned_pointer($current_pointer, $flat_tree){
			$current_pointer--;
			if($current_pointer > 0){
				if($flat_tree[$current_pointer]['subtype_id'] == 4 AND !$flat_tree[$current_pointer]['if_learned']){
					return $current_pointer;
				} else {
					return $this->get_previous_unlearned_pointer($current_pointer, $flat_tree);
				}
			}
			return;
		}
		
		// 获取第一个没学的知识点
		public function get_first_unlearned_pointer($flat_tree){
			$pointer = 0;
			foreach($flat_tree AS $row){
				if($row['subtype_id'] == 4 AND !$row['if_learned']){
					return $pointer;
				}
				$pointer++;
			}
			return;
		}

		// 获取用户的购买记录
		public function get_pay_history($uid, $limit = 10, $offset = 0, $order = 'time_created', $sort = 'desc', $forcount = false)
		{
			$res = array();
			$where = array(
				'user_guid' => $uid,
				'status' => 'COMPLETED',
			);
			$items = $this->ci->my_lib->get_records('transactions', '*',$where, $limit, $offset, $order, $sort, $forcount);
			if (!$items) {
				return array();
			}
			foreach ($items as $k=>$item) {
				$entity_subtype = $item['entity_subtype'];
				switch ($entity_subtype) {
					case 'COURSE':
						$course = $this->ci->my_lib->get_records('courses','*', array('guid' => $item['entity_guid']), 1, 0);
						$cdata = array(
							'course_unique_key' => $course[0]['unique_key'],
							'course_title' => $course[0]['title'],
							'actual_price' => $item['amount'],
							'time_created' => $item['time_created'],
							'type' => 'COURSE',
						);
						$res[] = $cdata;
						break;
					case 'SECTION':
						$section = $this->ci->my_lib->get_records('sections','*', array('guid' => $item['entity_guid']), 1, 0);
						$course = $this->ci->my_lib->get_records('courses','*', array('guid' => $section[0]['course_guid']), 1, 0);
						
						$s = array(
							'section_unique_key' => $section[0]['unique_key'],
							'section_title' => $section[0]['title'],
						);

						$sdata = array(
							'course_unique_key' => $course[0]['unique_key'],
							'course_title' => $course[0]['title'],
							'actual_price' => $item['amount'],
							'time_created' => $item['time_created'],
							'type' => 'SECTION',
							'section' => $s,
						);
						$res[] = $sdata;
						break;
					default:
						continue;
						break;
				}
			}
			return $res;
		}


		/**
		 * 获取微信公众账号access_token
		 * http://mp.weixin.qq.com/wiki/15/54ce45d8d30b6bf6758f68d2e95bc627.html
		 * @return array
		 */
		function token_get($force_update = false)
		{
			$t            = time();
			$new_generate = true;
			$wx_data      = array();
			$url          = 'https://api.weixin.qq.com/cgi-bin/token';
			$where        = array(
				'name' => 'mp_wx_access_token'
			);
			if (!$force_update) {
				$access_token_data = $this->ci->my_lib->get_records('token_cache', '*', $where, 1, 0, 'time_created', 'desc');
				if ($access_token_data) {
					$time_expire  = $access_token_data[0]['time_expire'];
					$access_token = $access_token_data[0]['value'];
					if ($t < $time_expire) {
						$new_generate = false;
						$wx_data = array(
							'access_token' => $access_token,
							'expires_in' => $time_expire - $t,
						);
						$res = json_encode($wx_data);
					}
				}
			}
			//需要新生成access_token
			if ($new_generate || $force_update) {
				$data = array(
					'grant_type' => 'client_credential',
					'appid'      => 'wx45e3d24d9d47c52d',
					'secret'     => '2e65defe43982180362afd8430c38f2a',
				);
				$res = tran_curl_data($url, $data, 'GET');
				$wx_data = @json_decode($res, true);
				if (isset($wx_data['access_token'])  && isset($wx_data['expires_in']) ){
					$insert_data = array(
						'name'         => 'mp_wx_access_token',
						'value'        => $wx_data['access_token'],
						'time_created' => $t,
						'time_expire'  => $wx_data['expires_in'] + $t,
					);
					$this->ci->my_lib->create_a_record('token_cache', $insert_data);
				}
			}
			return $res;
		}

		/**
		 * 通过access_token获取js_ticket数据
		 * http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html#.E9.99.84.E5.BD.951-JS-SDK.E4.BD.BF.E7.94.A8.E6.9D.83.E9.99.90.E7.AD.BE.E5.90.8D.E7.AE.97.E6.B3.95
		 * @return json
		 */
		function ticket_get()
		{
			$t            = time();
			$new_generate = true;
			$wx_data      = array();
			$where = array(
				'name' => 'mp_wx_js_ticket',
			);
			$wx_js_data = $this->ci->my_lib->get_records('token_cache', '*', $where, 1, 0, 'time_created', 'desc');
			if ($wx_js_data) {
				$mp_wx_js_ticket = $wx_js_data[0]['value'];
				$time_expire = $wx_js_data[0]['time_expire'];
				if ($t < $time_expire) {
					$new_generate = false;
					$wx_data = array(
						'errcode' => 0,
						'errmsg' => "ok",
						'ticket' => $mp_wx_js_ticket,
						'expires_in' => $time_expire - $t,
					);
					$res = json_encode($wx_data);
				}
			}
			//重新生成新的ticket
			if ($new_generate) {
				try {
					$ac  = $this->token_get();
					$res = $this->update_wx_js_ticket(json_decode($ac, 1), 0);
				} catch (Exception $e) {
					return $e->getMessage();
				}
			}
			return $res;
		}

		function update_wx_js_ticket($access_token_data, $recursive_time = 0)
		{
			$t   = time();
			$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';
			if (!isset($access_token_data['access_token'])){
				throw new Exception('{"errcode":10000,"errmsg":"unknow error, please try again"}');
			}
			$data = array(
				'access_token' => $access_token_data['access_token'],
				'type' => 'jsapi'
			);
			$res = tran_curl_data($url, $data, 'GET');
			$wx_data = @json_decode($res, true);
			if (isset($wx_data['ticket']) && isset($wx_data['expires_in']) ){
				$insert_data = array(
					'name'         => 'mp_wx_js_ticket',
					'value'        => $wx_data['ticket'],
					'time_created' => $t,
					'time_expire'  => $wx_data['expires_in'] + $t,
				);
				$this->ci->my_lib->create_a_record('token_cache', $insert_data);
			}else if (isset($wx_data['errcode']) && $wx_data['errcode'] == 40001 ) {
				if ($recursive_time < 3) {
					$re_wx_data = $this->token_get(true);
					return $this->update_wx_js_ticket( json_decode($re_wx_data,1), $recursive_time);
					$recursive_time++;
				}
			}
			return $res;
		}

		function randomString($type="number,upper,lower",$length){
			$valid_type = array('number','upper','lower');
			$case = explode(",",$type);
			$count = count($case);
			
			//根据交集判断参数是否合法
			if($count !== count(array_intersect($case,$valid_type))){
				return false;
			}
			$lower = "abcdefghijklmnopqrstuvwxyz";
			$upper = strtoupper($lower);
			$number = "0123456789";
			$str_list = "";
			for($i=0;$i<$count;++$i){
				$str_list .= $$case[$i];
			}
			return substr(str_shuffle($str_list),0,$length);
		}
		
		
		//获取一个“node”的相关信息
		function get_a_node($node_guid, $section_guid, $course_guid){
			
			//获取一个“node”的相关信息
			$fields = array('title', 'main', 'status');
			$node = $this->get_a_subtype_row('nodes', (int)$node_guid, $fields);
			
			//初始化一个节点是否是“single_attahed”
			$node['is_a_node_single_attached'] = false;
			
			//如果一个“node”的标题是“”
			if($node['title'] == 'single_attached_node'){
				$node['title'] = $this->get_a_single_attached_node_section_title($node_guid, $section_guid, true, $course_guid);
				$node['is_a_node_single_attached'] = true;
			}
			
			return $node;
		}
	}