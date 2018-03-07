<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Build_lib extends my_lib
	{	
		function __construct()
		{
			parent::__construct();
			$this->ci = & get_instance();
		}
		
		//获取课程机构的菜单
		function get_syllabus_menu($course_guid)
		{
			return $this->populate_children($course_guid);
		}
		
		//列出某用户处于某状态的课程
		function list_my_courses($owner_guid, $status = 'published')
		{
			$subtype_id = $this->get_subtype_id('course');
			$status = strtoupper($status);
			
			$sql = "SELECT courses.title, courses.unique_key FROM courses LEFT JOIN entities on entities.guid = courses.guid ";
			$sql.= "WHERE entities.owner_guid = $owner_guid AND entities.subtype_id = $subtype_id AND courses.status = '$status' ";
			
			return $this->get_records_with_sql($sql);
		}
		
		//设置一个课程的收费模式		
		function set_course_publish_option($course_unique_key, $publish_option, $is_course_free, $fee_policy, $by_course_fee, $access, $by_section_is_completed = 0)
		{
			/*
				模式一：（1）用户以连载方式发布课程；（2）课程免费；（3）选择了访问权限
				模式二：（1）用户以连载方式发布课程；（2）课程不免费
				模式三：（1）用户以整体方式发布课程；（2）课程免费；（3）选择了访问权限
				模式四：（1) 用户以整体方式发布课程；（2）课程不免费；（3）选择了整体收费；（4）课程价格
				模式五：（1）用户以整体方式发布课程；（2）课程不免费；（3）选择了按课节收费
				模式六：（1）用户以整体方式发布课程；（2）课程不免费；（3）选择两种收费模式都可以
			*/
			
			//更新条件
			$condition = array('unique_key' => $course_unique_key);
			
			//初始化数据
			$data = array(
				'publish_option' => strtoupper($publish_option),
				'is_course_free' => $is_course_free
			);
			
			if($publish_option == 'BY_SECTION' AND $is_course_free AND $access != NULL)
			{
				$data['access'] 					= $access;
				$data['fee_policy']					= NULL;
				$data['by_course_fee']				= 0;
				$data['by_section_is_completed']	= $by_section_is_completed;
				
				$this->update_records('courses', $data, $condition);
				$result = TRUE;
			}
			elseif($publish_option == 'BY_SECTION' AND !$is_course_free)
			{
				$data['access'] 					= NULL;
				$data['fee_policy']					= strtoupper('by_section');
				$data['by_course_fee']				= 0;
				$data['by_section_is_completed']	= $by_section_is_completed;
				
				$this->update_records('courses', $data, $condition);
				$result = TRUE;
			}
			elseif($publish_option == 'BY_COURSE' AND $is_course_free AND $access != NULL)
			{
				$data['access'] 		= $access;
				$data['fee_policy']		= NULL;
				$data['by_course_fee']	= 0;
				
				$this->update_records('courses', $data, $condition);
				$result = TRUE;
			}
			elseif($publish_option == 'BY_COURSE' AND !$is_course_free AND $fee_policy == 'BY_COURSE' AND $by_course_fee > 0)
			{
				$data['fee_policy'] 	= $fee_policy;
				$data['by_course_fee'] 	= $by_course_fee;
				$data['access'] 		= NULL;
				
				$this->update_records('courses', $data, $condition);
				$result = TRUE;
			}
			elseif($publish_option == 'BY_COURSE' AND !$is_course_free AND $fee_policy == 'BY_SECTION')
			{
				$data['fee_policy'] 	= $fee_policy;
				$data['by_course_fee']	= 0;
				$data['access'] 		= NULL;
				
				$this->update_records('courses', $data, $condition);
				$result = TRUE;
			}
			elseif($publish_option == 'BY_COURSE' AND !$is_course_free AND $fee_policy == 'BY_BOTH')
			{
				$data['fee_policy'] 	= $fee_policy;
				$data['by_course_fee']	= $by_course_fee;
				$data['access'] 		= NULL;
				
				$this->update_records('courses', $data, $condition);
				$result = TRUE;
			}
			else
			{
				if($publish_option == 'BY_COURSE' AND !$is_course_free AND $fee_policy == 'BY_COURSE' AND ($by_course_fee == 0 OR $by_course_fee == ''))
				{
					$this->set_a_msg('您没有设置课程价格','error');
				}
				else
				{
					$this->set_a_msg('数据提交不正确','error');
				}
				
				$result = FALSE;
			}
			
			return $result;
		}
		
		// ********************************************************
		// 操作课节（SECTION）的相关方法
		// ********************************************************		
		
		function resort_section($section_unique_key, $weight)
		{
			$condition = array('unique_key' => $section_unique_key);
			$data = array('weight' => $weight);
			return $this->update_records('entities', $data, $condition);
		}
		
		function delete_a_section($section_unique_key)
		{
			$this->ci->db->trans_start(); //开始数据库存储事件
			$this->delete_records('entities', array('unique_key' => $section_unique_key));
			$this->delete_records('sections', array('unique_key' => $section_unique_key));
			$this->ci->db->trans_complete(); //结束数据库存储事件
			
			return $this->ci->db->trans_status();
		}
		
		//创建一个课程的“section”
		function create_a_section($course_guid, $course_unique_key, $section_title, $weight)
		{
			$result_arr = array();
		
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			//创建“entity”记录
			$owner_guid = $this->_guid;
			$section_unique_key = $this->generate_entity_unique_key($owner_guid.$course_unique_key);
			
			$section_guid = $this->create_an_entity($owner_guid, 'section', $course_guid, TRUE, $section_unique_key, $section_title, '', $weight);
			
			//准备写入到“sections”表的数据
			$data = array(
				'guid' 			=> $section_guid, 
				'unique_key' 	=> $section_unique_key,
				'course_guid'	=> $course_guid, 
				'title' 		=> $section_title
			);
			
			//将数据插入“sections”表
			$this->create_a_record('sections', $data);
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			if ($this->ci->db->trans_status() === TRUE)
			{
				//把用户行为写进日志
				$related_guids = array('related_guid_1' => $section_guid);
				//$this->ci->river_lib->logit('create_a_section', $this->_guid, $this->_user_unique_key, $related_guids);
				
				//返回“section_guid”和“section_unique_key”
				$result_arr['guid'] = $section_guid;
				$result_arr['unique_key'] = $section_unique_key;
			}
			
			return $result_arr;
		}
		
		// ********************************************************
		// 操作节点（Node）的相关方法
		// ********************************************************		
		
		function resort_node($course_unique_key, $section_unique_key, $node_unique_key, $father_unique_key, $weight){
			
			$course_guid 	= $this->get_guid_by_unique_key($course_unique_key);
			$section_guid 	= $this->get_guid_by_unique_key($section_unique_key);
			$node_guid		= $this->get_guid_by_unique_key($node_unique_key);
			$father_guid	= $this->get_guid_by_unique_key($father_unique_key);
			
		
			//开始数据库存储事件
			$this->ci->db->trans_start();
		
			//改变“entities”表中的数据
			$condition = array('guid' => $node_guid);
			$data = array('father_guid' => $father_guid, 'weight' => $weight);
			$this->update_records('entities', $data, $condition);
			
			//改变“nodes”表中的数据
			$this->section_guid_resursive_update($course_guid, $section_guid, $node_guid, $father_guid);
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			return $this->ci->db->trans_status();
		}
		
		//递归更新某一个节点下面全部子节点的section_guid
		private function section_guid_resursive_update($course_guid, $section_guid, $node_guid, $father_guid){
		
			$condition = array('guid' => $node_guid);
			$data = array('course_guid' => $course_guid, 'section_guid' => $section_guid, 'father_guid' => $father_guid);
			$this->update_records('nodes', $data, $condition);
			
			$condition = array('father_guid' => $node_guid);
			
			$childs = $this->get_records('nodes', array('guid'), $condition);
			
			/**
				如果这个节点有子节点，递归更新该节点下面所有的子节点的section_guid都要更新
			*/
			
			if(sizeof($childs) > 0) {
				foreach($childs AS $row) {
					$this->section_guid_resursive_update($course_guid, $section_guid, $row['guid'], $node_guid);
				}
			}
			
			return;
		}
		
		//创建一个节点
		function create_a_node($course_guid, $section_guid, $father_guid, $node_title, $weight)
		{
			$result_arr = array();
		
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			//创建“entity”记录
			$owner_guid = $this->_guid;
			$node_unique_key = $this->generate_entity_unique_key($owner_guid.$course_guid.$section_guid,$father_guid);
			
			//生成“node”的“entity”数据
			if($father_guid == 0) $father_guid = $section_guid;
			$node_guid = $this->create_an_entity($owner_guid, 'node', $father_guid, TRUE, $node_unique_key, $node_title, '', $weight);
			
			//将数据插入“nodes”表
			$data = array(
				'guid'			=> $node_guid,
				'unique_key' 	=> $node_unique_key,
				'course_guid'	=> $course_guid,
				'section_guid'	=> $section_guid,
				'father_guid'	=> $father_guid,
				'title' 		=> $node_title
			);
			
			$this->create_a_record('nodes', $data);
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			if ($this->ci->db->trans_status() === TRUE)
			{
				//把用户行为写进日志
				$related_guids = array('related_guid_1' => $node_guid);
				//$this->ci->river_lib->logit('create_a_node', $this->_guid, $this->_user_unique_key, $related_guids);
				
								
				$result_arr['unique_key'] = $node_unique_key;
				$result_arr['guid'] = $node_guid;
			}
			
			return $result_arr;
		}
		
		//删除一个节点
		function delete_a_node($node_unique_key)
		{
			$this->ci->db->trans_start(); //开始数据库存储事件
			$this->delete_records('entities', array('unique_key' => $node_unique_key));
			$this->delete_records('nodes', array('unique_key' => $node_unique_key));
			$this->ci->db->trans_complete(); //结束数据库存储事件
			return $this->ci->db->trans_status();
		}
		
		//请求对一个课程进行审核
		function request_a_course_review($course_unique_key)
		{
			$condition = array('unique_key' => $course_unique_key);
			$data = array('status' => 'PENDING');
			return $this->update_records('courses', $data, $condition);
		}
		
		
		// ********************************************************
		// 节点的树状结构相关操作
		// ********************************************************	
		
		private function populate_children($father_guid)
		{
			$array = array();
			
			$node_subtype_id 	= $this->get_subtype_id('node');
			$section_subtype_id	= $this->get_subtype_id('section');
			
			$condition = "father_guid = $father_guid AND guid != 0";
			$fields = array('guid','subtype_id','father_guid','unique_key','visibility','title','weight');
			$result = $this->get_records('entities', $fields, $condition, NULL, 0, 'weight', 'ASC');
			
			if(count($result) > 0)
			{
				foreach($result AS $row)
				{
					$subtype_id = $row['subtype_id'];
					
					if($subtype_id == $node_subtype_id OR $subtype_id == $section_subtype_id)
						$array[] = array(
							'guid'			=> $row['guid'],
							'subtype_id' 	=> $row['subtype_id'],
							'father_guid'	=> $row['father_guid'],
							'unique_key'	=> $row['unique_key'],
							'visibility'	=> $row['visibility'],
							'title'			=> $row['title'],
							'weight'		=> $row['weight'],
							'child'			=> $this->populate_children($row['guid'])
						);
				}	
			}
			
			return $array;
		}
	}