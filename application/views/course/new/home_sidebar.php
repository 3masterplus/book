<div class="side-container">
	<?php
		$mode = $accessibility['mode'];
		
		if($mode == 1){
			echo '<strong class="free">免费学习</strong>';
			echo '<p>免费注册，立即参与学习</p>';
			echo '<a href="#" class="btn enroll" data-unique-key="'.$course_unique_key.'">立即加入</a>';
		} elseif($mode == 2) {
			/**
			echo '<strong>¥ '.$accessibility['by_course_price'].'</strong>';
			echo '<p>一次购买，无限期学习！</p>';
			echo '<a href="#" class="btn payment" data-unique-key="'.$course_unique_key.'">立即购买</a>';
			*/
		} elseif($mode == 3) {
			/**
			echo '<p class="impact">本课程支持按课节支付</p>';
			echo '<p class="impact">最低每节<span>'.$accessibility['lowest_section_price'].' 元</span>';
			*/
		} elseif($mode == 4) {
			/**
			echo '<strong>¥ '.$accessibility['by_course_price'].'</strong>';
			echo '<a href="#" class="btn payment" data-unique-key="'.$course_unique_key.'">立即购买</a><i>或</i>';
			echo '<p>按课节购买<span>'.$accessibility['lowest_section_price'].' 元起</span></p>';
			*/
		} elseif($mode == 5) {
			
			echo '<strong class="title">学习进度</strong>';
			
			$percentage_of_course_completion = $accessibility['learning_progress']['percentage_of_course_completion'];
			
			if($percentage_of_course_completion > 0 AND $percentage_of_course_completion < 100) {
			
				$current_node_time_updated	= $accessibility['learning_progress']['current_node_time_updated'];
				$current_node_title 		= $accessibility['learning_progress']['current_node_title'];
				$current_node_url			= $accessibility['learning_progress']['current_node_url'];
				
				// 如果该node的标题是“single_attached_node”，页面所显示的需要是该“node”所属 “section”的标题，以及这个“section”的课节数。
				if($current_node_title == 'single_attached_node') {
					$current_node_section_guid = $accessibility['learning_progress']['current_node_section_guid'];
					$current_node_section_title = $this->my_lib->get_a_value('sections', 'title', array('guid' => $current_node_section_guid));
					$section_index_number = $this->course_lib->get_index_number_of_a_section($current_node_section_guid, $course_guid);
					$current_node_title = '第'.$section_index_number.'课：'.$current_node_section_title;
				}
				
				echo '<p style="margin-bottom: 1px;margin-top: 20px;">'.time_std_format($current_node_time_updated).'</p>';
				echo '<p class="impact">您学习了 <a href="'.$current_node_url.'">'.$current_node_title.'</a></p>';
				echo '<div data-percent="'.$percentage_of_course_completion.'" class="circle small percircle animate"></div>';
				echo '<p class="impact" style="margin-bottom: 25px;">您已经完成了本课程的'.$percentage_of_course_completion.'%</p>';
				
			} elseif ($percentage_of_course_completion == 0) {
				
				$first_node_title = $accessibility['learning_progress']['first_node_title'];
				$first_node_url = $accessibility['learning_progress']['first_node_url'];
				
				// 如果该node的标题是“single_attached_node”，页面所显示的需要是该“node”所属 “section”的标题，以及这个“section”的课节数。
				if($first_node_title == 'single_attached_node') {
					$first_node_section_guid = $accessibility['learning_progress']['first_node_section_guid'];
					$first_node_section_title = $this->my_lib->get_a_value('sections', 'title', array('guid' => $first_node_section_guid));
					$section_index_number = $this->course_lib->get_index_number_of_a_section($first_node_section_guid, $course_guid);
					$first_node_title = '第'.$section_index_number.'课：'.$first_node_section_title;
				}
				
				echo '<p>开始学习<a href="'.$first_node_url.'">'.$first_node_title.'</a></p>';
				echo '<a href="'.$first_node_url.'" class="btn special"><i class="icon-arrow"></i>立即学习</a>';
			
			} elseif($percentage_of_course_completion == 100) {
				echo '<p>您已完成本课已发布的全部内容。本课更多内容在持续发布中。敬请期待！</p>';
			}
			
			echo '<a href="#" class="gray" id="quit-course-popup" data-unique-key="'.$course_unique_key.'">退出本课学习</a>';
	 	}
	 ?>
</div>

<?php
	if($mode == 1) {
		echo '<div class="mobile-bottom-ctrl sm-show enroll" data-unique-key="'.$course_unique_key.'">';
		echo '<p>免费学习，立即报名<i class="icon-play2"></i></p>';
		echo '</div>';
	} elseif($mode == 5) {
		if($percentage_of_course_completion == 0){
			echo '<div class="mobile-bottom-ctrl sm-show half">';
			echo '<a href="'.$first_node_url.'">立即开始学习<i class="icon-play2"></i></a>';
			echo '</div>';
		} elseif($percentage_of_course_completion > 0 AND $percentage_of_course_completion < 100) {
			echo '<div class="mobile-bottom-ctrl sm-show half">';
			echo '<a href="">立即开始学习<i class="icon-play2"></i></a>';
			echo '<span id="show-learn-porcess"><i class="icon-course"></i>学习进度</span>';
			echo '</div>';
		} elseif($percentage_of_course_completion == 100) {
			if($course_publish_option == 'BY_SECTION'){
				echo '<div class="mobile-bottom-ctrl sm-show button-continued" data-unique-key="'.$course_unique_key.'">';
				echo '<p>更多内容连载中，敬请期待<i class="icon-play2"></i></p>';
				echo '</div>';
			} elseif ($course_publish_option == 'BY_COURSE') {
				echo '<div class="mobile-bottom-ctrl sm-show button-restart" data-unique-key="'.$course_unique_key.'">';
				echo '<p>已完成全部课程<i class="icon-play2"></i></p>';
				echo '</div>';
			}
		}
	}
?>