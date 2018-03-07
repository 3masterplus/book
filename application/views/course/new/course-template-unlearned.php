<div class="card half-width <?php echo $course_template; ?>" >
	<div class="head-area">
		<div class="bar">
			<i class="icon-book2"></i>
			<?php echo $this->config->item($publish_option); ?>
		</div>
	</div>
	<div class="full-area left">
		<h3><a href="<?php echo base_url('course/'.$course_unique_key.'/home'); ?>"><?php echo $course_title; ?></a></h3>
		<p><?php echo $course_summary; ?></p>
		<div class="bottom-box">
			<a href="<?php echo base_url('course/'.$course_unique_key.'/home'); ?>" class="btn green">查看详情</a>
			<ul class="media-list-mini">
				
				<?php
					if($total_mp3_duration > 0)
						echo '<li><i class="icon-file-audio"></i><div class="tip">'.convert_seconds($total_mp3_duration).'</div></li>';
					
					if($total_video_duration > 0)
						echo '<li><i class="icon-video"></i><div class="tip">'.convert_seconds($total_video_duration).'</div></li>';
					
					
					
				?>
				
			</ul>
		</div>
	</div>
</div>