<?php

	$this->load->view('course/new/dashboard-menu', array('highlight' => '已完成课程')); 
	
	
	if(sizeof($course_completed) > 0){
	
		echo '<div class="course-list">';
	
			foreach($course_completed AS $row) {
			
				echo '<div class="card half-width c5" data-percent = "100">';
	
					echo '<div class="head-area">';
						echo '<div class="bar">';
							echo '<i class="icon-book2"></i>';
						echo '</div>';
						
					echo '</div>';
		
					echo '<div class="full-area">';
						echo '<h3>第二课：绘图及图像</h3>';
						echo '<p class="text-up">2012.12.11 13:10 完成</p>';
						echo '<div class="bottom-box">';
							echo '<button class="btn green">重新学习</button>';
						echo '</div>';
					echo '</div>';
				
				echo '</div>';
				
			}
		
		echo '</div>';
		
	} else {
		
		echo '<div class="no-data"><span><i class="icon-sad"></i>还没有学习完成的课程</span></div>';
		
	}
	
	
?>
