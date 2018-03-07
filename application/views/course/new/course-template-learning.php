<div class="card half-width <?php echo $course_templete; ?>" data-percent = "<?php echo $percentage_of_course_completion; ?>">

	<div class="head-area">
		<div class="bar">
			<i class="icon-book2"></i>
		</div>
	</div>
	
	<div class="full-area">
		<h3><a href="<?php echo $course_url; ?>"><?php echo $course_title; ?><a/></h3>
		<?php
			
			if($percentage_of_course_completion == 100){
				echo '<p class="text-un">您已经本课所发布的全部内容。本课更多内容正在发布中，敬请期待！</p>';	
			} else {
				if($percentage_of_course_completion > 0){
					echo '<p class="text-up">你上次学习的内容是：</p>';
					echo '<a href="'.$current_node_url.'" class="up-link">'.$current_node_title.'</a>';
				}elseif($percentage_of_course_completion == 0){
					echo '<p class="text-up">立即学习第一节：</p>';
				}
				
				echo '<p class="text-up">你下一个要学习的知识点是：</p>';
				echo '<div class="section-box">';
					echo '<section>';
					echo '<a href="'.$next_node_url.'">';
					echo '<i class="icon-chevron-left-circle"></i>';
					echo '<span>'.$next_node_title.'</span>';
					echo '</a>';
					echo '</section>';
				echo '</div>';
				
				echo '<p class="text-up">你已经完成本课的 '.$percentage_of_course_completion.'%</p>';
				echo '<p class="text-down">还有 '.$number_of_nodes_unlearned.' 个知识点等你去学习</p>';	
			}
		?>
	</div>
</div>