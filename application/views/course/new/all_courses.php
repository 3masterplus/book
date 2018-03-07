<div class="course-list">
	<?php
		foreach($courses AS $row)
		{
			$data = array(
				'course_template'			=> $row['course_template'],
				'number_of_course_nodes' 	=> $row['number_of_course_nodes'],
				'publish_option'			=> $row['publish_option'],
				'course_unique_key'			=> $row['unique_key'],
				'course_title'				=> $row['title'],
				'course_summary'			=> $row['summary'],
				'total_mp3_duration'		=> $row['total_mp3_duration'],
				'total_video_duration'		=> $row['total_video_duration']
			);
			
			$this->load->view('course/new/course-template-unlearned', $data);
		}
	?>
</div>