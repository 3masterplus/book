<div class="card fullwidth <?php echo iif($if_learnt, 'learned-tag', ''); ?>">
	<div class="section-info">
		<div class="text-area">
			<h1><?php echo $title.iif($if_openmark_showed,'<span>'.$this->config->item('openmark').'</span>',''); ?></h1>
			<div class="ctrl"><a href="#" class="wechat-share"><i class="icon-share"></i>分享到朋友圈</a></div>
		</div>
		
		<div class="side-area">
			<div class="vertical-middle">
				<div>
					<?php 
						if(!$if_learnt){ 
							echo '<a href="#" title="" class="btn green bordered learned">标记已学完</a>'; 
						} else {
							echo '<a href="#" title="" class="btn green icon unlearned"><i class="icon-checkmark"></i>已学完</a>';
							echo '<span>点击取消学完标记</span>';
						}
					?>
				</div>
			</div>
		</div>
	</div>
	
	<div class="section-detail">
		<div class="primary-area <?php echo iif($is_node_accessible, '', 'mask'); ?>">
			<!--<strong>课程内容</strong>-->

			<?php		
				
				$main = str_replace(chr(10).chr(10), '</p><p>', $main);			
				$main_arr = explode('</p><p>', $main);
					
				if($is_node_accessible){
					
					echo '<p>'.$main.'</p>';	
					if($user_guid == 0) $this->load->view('course/new/login_layer');
					
				} else {
					
					$number_of_paragraphs = sizeof($main_arr);
						
					if($number_of_paragraphs > 1){
						
						$number_of_displayed_paragrahs = round($number_of_paragraphs/2);	
						$str = '</p><p>';
							
						for($i = 0; $i < $number_of_displayed_paragrahs; $i++){
							$str .= $main_arr[$i];
							if($i != $number_of_displayed_paragrahs - 1) $str .= '</p><p>';
						}
							
						echo '<div class = "content-main"><p>'.$str.'</p></div>';
					}
					
					$this->load->view('course/new/access_reminder');
						
				}
			?>
			
		</div>
		
		<?php $this->load->view('course/new/course_learn_right_side'); ?>
	
	</div>
</div>

<button class="open-node-tree open-sidearea <?php echo iif($is_a_node_single_attached, 'goback', ''); ?>"><i class="<?php echo iif($is_a_node_single_attached, 'icon-arrow-back', 'icon-nodetree'); ?>"></i></button>

<input type="hidden" id="course_unique_key" value="<?php echo $course_unique_key; ?>">
<input type="hidden" id="section_unique_key" value="<?php echo $section_unique_key; ?>">
<input type="hidden" id="node_unique_key" value="<?php echo $node_unique_key; ?>">