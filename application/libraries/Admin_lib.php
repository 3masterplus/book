<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Admin_lib extends my_lib
	{	
		function __construct()
		{
			parent::__construct();
			$this->ci = & get_instance();
		}
		
		//创建一门课程
		function create_a_course($user_guid, $course_title, $course_summary)
		{
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			$course_unique_key = $this->generate_entity_unique_key(rand_str(32));
			
			//创建“entity”记录
			$course_guid = $this->create_an_entity($user_guid, 'course', 0, FALSE, $course_unique_key, $course_title, $course_summary);
			
			//拼装数据
			$data = array(
				'guid'			=> $course_guid,
				'unique_key'	=> $course_unique_key,
				'title'			=> $course_title,
				'main'			=> $course_summary,
				'status'		=> 'draft'
			);
			
			//将数据插入“users”表
			$this->create_a_record('courses', $data);
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			return $this->ci->db->trans_status();
		}
		
		//获取课程列表
		function get_list_of_course($status)
		{
			$fields = array('*');
			$condition = array('status' => $status);
			return $this->get_records('courses', $fields, $condition, NULL, 0, 'guid', 'DESC');
		}
	}