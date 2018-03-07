<?php
	function display_nested_nodes($array, $data){
	
		foreach($array AS $arr){
		
			$course_unique_key = $data['course_unique_key'];
			$base_url = base_url("course/node/".$course_unique_key."/".$arr['section_unique_key']."/".$arr['unique_key']);
			
			echo '<div class="node '.iif(sizeof($arr['child']) > 0, 'has-child', '').'">';
			echo '<div class="cont">';
			echo '<a '.iif($arr['if_node_learned'], 'class="finished"', '').' href="'.$base_url.'" title="'.$arr['title'].'">';
			echo '<i class="icon"></i><span>'.$arr['title'].'</span></a>';
			
			echo '<div class="media-area">';
			
			if($arr['number_of_images'] > 0) echo '<i class="icon-file-image" data-tip="'.$arr['number_of_images'].'个图片"></i>';
			if($arr['number_of_mp3_files'] > 0) echo '<i class="icon-file-audio" data-tip="'.$arr['number_of_mp3_files'].'段音频"></i>';
			if($arr['number_of_video_clips'] > 0) echo '<i class="icon-file-video" data-tip="'.$arr['number_of_video_clips'].'段视频"></i>';
			
			echo '</div>';
			echo '</div>';
			
			if(sizeof($arr['child']) > 0){
				display_nested_nodes($arr['child'], $data);
			}
			
			echo '</div>';
		}
	}
	
	foreach($syllabus AS $row){
	
		$is_tree_displayed = true;
		
		$percentage_of_section_completion = $this->course_lib->get_percentage_of_section_completion($user_guid, $row['guid']);
		
		if($percentage_of_section_completion == 100) $is_tree_displayed = false;
		
			echo '<div class="card section-card has-bottom on '.iif($row['section_if_single_node_attached'], 'single', '').'">';
				echo '<div class="head-area">';
				
				if($percentage_of_section_completion > 0 AND $percentage_of_section_completion < 100){ 
					echo '<div class="pp green" data-val="'.$percentage_of_section_completion.'"></div>';
				} elseif($percentage_of_section_completion == 100) {
					echo '<div class="status-icon finished"><i class="icon-checkmark"></i></div>';
				} else {
					echo '<div class="status-icon restart"><i class="icon-arrow"></i></div>';
				}
				
				echo '<div class="primary-area">';
					
					echo '<div class="title-div">';
						if($row['section_if_single_node_attached']){
							echo '<h3>';
								echo '<a href="'.base_url('course/node/'.$course_unique_key.'/'.$row['unique_key'].'/'.$row['section_single_node_attached_unique_key']).'">';
								echo $row['title'];
								
								if($row['section_is_open'] AND $accessibility['mode'] != 5){
									echo '<span>'.$this->config->item('openmark').'</span>';
								}
								
								echo '</a>';
							echo '</h3>';
						} else {
						
							echo '<h3>';
							
								$section_url = '';
								if(sizeof($row['child']) > 0){
									$first_node_unique_key = $row['child'][0]['unique_key'];
									$section_url = base_url('course/node/'.$course_unique_key.'/'.$row['unique_key'].'/'.$first_node_unique_key);
								}
								
								echo '<a href="'.$section_url.'">'.$row['title'].'</a>';
								
								if($row['section_is_open'] AND $accessibility['mode'] != 5) {
									echo '<span>'.$this->config->item('openmark').'</span>';
								}
							
							echo '</h3>';
						
						}
						
						/**
						
						<?php if($accessibility['mode'] == 3 OR $accessibility['mode'] == 4){ ?>
						
							<?php if($row['section_is_open']){ ?>
								<a href="" class="btn green">免费试学</a>
							<?php } else { ?>
								<a href="#" class="btn orange payment" data-unique-key="<?php echo $row['unique_key']; ?>" >￥ <?php echo $row['section_price']; ?> /课</a>
							<?php } ?>
							
						<?php } elseif ($accessibility['mode'] == 5){ ?>
						
							<?php if($percentage_of_section_completion == 100){ ?>
								<a href="#" class="btn green">重新学习</a>
							<?php } elseif($percentage_of_section_completion == 0){ ?>
								<a href="#" class="btn">开始学习</a>
							<?php } else { ?>
								<a href="#" class="btn">继续学习</a>
							<?php } ?>
						
						<?php } ?>
						
						*/
						
					echo '</div>';
				echo '<p>'.$row['summary'].'</p>';
			echo '</div>';
		echo '</div>';
		
		if(sizeof($row['child']) > 0 AND !$row['section_if_single_node_attached']){
			echo '<div class="bottom-area open">';
						
				echo '<div class="ctrl">';
					echo '<a href="#" '.iif($is_tree_displayed, 'class = "expanded"', '').'>';
					echo '<i class="icon-angle-'.iif($is_tree_displayed, 'up', 'down').'"></i>';
					echo $this->course_lib->get_section_number_of_nodes($row['guid']).' 个知识点';
					echo '</a>';
				echo '</div>';
						
						
				echo '<div class="node-tree '.iif($is_tree_displayed, 'expanded', '').'">';
						
					foreach($row['child'] AS $section){
								
						echo '<section class="finished '.iif(sizeof($section['child']) > 0, 'has-child', '').'">';
							echo '<div class="cont">';
							
								echo '<a '.iif($section['if_node_learned'], "class='finished'", '').' href="'.base_url('course/node/'.$course_unique_key.'/'.$section['section_unique_key']).'/'.$section['unique_key'].'" title="'.$section['title'].'">';
								echo '<i class="icon"></i>';
								echo '<span>'.$section['title'].'</span>';
								echo '</a>';
									
								echo '<div class="media-area">';
										
									if($section['number_of_images'] > 0){
										echo '<i class="icon-file-image" data-tip="'.$section['number_of_images'].'个图片"></i>';
									}
											
									if($section['number_of_mp3_files'] > 0){
										echo '<i class="icon-file-audio" data-tip="'.$section['number_of_mp3_files'].'段音频"></i>';
									}
										
									if($section['number_of_video_clips'] > 0){
										echo '<i class="icon-file-video" data-tip="'.$section['number_of_video_clips'].'段视频"></i>';
									}
									
								echo '</div>';
							
							echo '</div>';
									
							if(sizeof($section['child']) > 0){
							
								$data = array('course_unique_key' => $course_unique_key);
								display_nested_nodes($section['child'], $data);
							
							}
									
						echo '</section>';
					}
						
				echo '</div>';
			echo '</div>';
		}
		
		echo '</div>';
	
	}

	if($course_publish_option == 'BY_SECTION' AND !$course_by_section_is_completed){
		echo '<div class="continue"><span>本课程连载中，更多内容敬请期待</span></div>';
	}
?>