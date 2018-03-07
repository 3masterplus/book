<?php //echo gmdate("H:i:s", $course_total_video_duration); ?>
<div class="card fullwidth <?php echo $course_template; ?>">
	<div class="info">
		<div class="text-area">
			<h1><?php echo $course_title; ?></h1>
			<p><?php echo $course_summary; ?></p>
			<div class="info-list">
			
			<?php
				if($course_number_of_participants > 0){
					echo '<span><i class="icon-user-group"></i>'.$course_number_of_participants.' 个学生</span>';
				}
				
				echo '<span><i class="icon-book2"></i>'.$course_number_of_sections.' 节课</span>';
				echo '<span><i class="icon-node-list"></i>'.$course_number_of_nodes.' 个知识点</span>';
				echo '<span><i class="icon-file"></i>'.$course_total_word_count.' 个字</span>';
				
				if($course_total_video_duration > 0){
					echo '<span><i class="icon-video"></i>'.$course_total_video_duration.' 秒视频</span>';
				}
				
				if($course_total_mp3_duration > 0){
					echo '<span><i class="icon-audio" style="font-size:16px"></i>'.$course_total_mp3_duration.' 秒音频</span>';
				}
			?>
			
			</div>
		</div>
		<div class="avatar-area">
				<a href="#" title="username">
				<img src="<?php echo $lecturer_avatar; ?>">
				<p><?php echo $lecturer_username; ?></p>
			</a>
		</div>
		
		<?php if($course_publish_option == 'BY_SECTION'){ ?>
			<div class="status <?php echo iif($course_by_section_is_completed, 'end', 'serializing'); ?>">
				<?php echo iif($course_by_section_is_completed, '已完结', '连载中'); ?>
			</div>
		<?php } ?>
		
	</div>
	
	<div class="bottom-area">
		<div class="primary-container">
			<nav class="detail-switch">
				<a href="#" title="#" <?php echo iif($accessibility['mode'] != 5, 'class="on"', ''); ?> >课程介绍</a>
				<a href="#" title="#" <?php echo iif($accessibility['mode'] == 5, 'class="on"', ''); ?> >课程内容</a>
			</nav>
			<div id="course-introduction" class="switch-block <?php echo iif($accessibility['mode'] != 5, 'on', ''); ?>">
				<h2>课程详情</h2>
				<div class="course-detail"><p><?php echo $course_description; ?></p></div>
				<div class="course-point-box">
					<div class="course-point">
						<div class="point-cell">
							<h3>谁合适学？</h3>
							<?php echo $course_audience; ?>
						</div>
						
						<div class="point-cell">
							<h3>能得到啥？</h3>
							<?php echo $course_objectives; ?>
						</div>
					</div>
				</div>
				<h2>关于教师</h2>
				<div class="teacher">
					<a href="#" title="" class="avatar"><img src="<?php echo $lecturer_avatar; ?>"></a>
					<div class="info">
						<a href="#" title=""><?php echo $lecturer_username; ?></a>
						<p class="intro"><?php echo $lecturer_signature; ?></p>
					</div>
				</div>
			</div>
			<div id="course-content" class="switch-block <?php echo iif($accessibility['mode'] == 5, 'on', ''); ?>">
				<select name="view-filter" data-class="c-select" style="display: none">
					<option value="all">全部</option>
					<option value="unlearned">未学完</option>
				</select>
				<?php
					$data = array('syllabus' => $syllabus);
					$this->load->view('course/new/course_home_2', $data);
				?>
			</div>
		</div>
		
		<?php
			$data['accessibility'] = $accessibility;  
			$this->load->view('course/new/home_sidebar', $data);
		?>
		
	</div>
</div>