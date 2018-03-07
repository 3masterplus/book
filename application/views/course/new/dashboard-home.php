<?php
	
	$this->load->view('course/new/dashboard-menu', array('highlight' => '学习中的课程'));
	
	if(sizeof($course_uncompleted) > 0){
		
		echo '<div class="course-list">';
		
			foreach($course_uncompleted AS $row){
			
				$data = array(
					'percentage_of_course_completion'	=> $row['percentage_of_course_completion'],
					'number_of_nodes_unlearned'			=> $row['number_of_nodes_unlearned'],
					'course_unique_key'					=> $row['course_unique_key'],
					'course_url'						=> $row['course_url'],
					'course_title'						=> $row['course_title'],
					'current_node_title'				=> $row['current_node_title'],
					'current_node_url'					=> $row['current_node_url'],
					'next_node_title'					=> $row['next_node_title'],
					'next_node_url'						=> $row['next_node_url'],
					'next_node_direction'				=> $row['next_node_direction'],
					'course_templete'					=> $row['course_template']
				);
				
				
				//print_r($data);
						
				$this->load->view('course/new/course-template-learning', $data);
			}
		
		echo '</div>';
		
	} else {
		echo '<div class="no-data"><span><i class="icon-sad"></i>当前没有在学习的课程</span></div>';
	}
	
?>