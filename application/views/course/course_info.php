<?php
	function display_nested_nodes($father_guid, $array)
	{
		if(count($array) > 0)
		{
			foreach($array AS $row)
			{
				echo '<div class="node">';
					echo '<a>'.$row['title'];
						if($row['number_of_mp3_files'] > 0) echo '<i class="icon-music"></i>';
						if($row['number_of_images'] > 0) echo '<i class="icon-image3"></i>';
						if($row['number_of_questions'] > 0) echo '<i class="icon-pencil"></i>';

					echo '</a>';
					if(count($row['child']) > 0) display_nested_nodes($row['guid'], $row['child']);
				echo '</div>';
			}
		}
	}	
?>

<div class="row row-fullwidth row-space-12 course-bg">
	<div class="course-title">
		<h1><?php echo $course_title; ?></h1>
		<p><?php echo $course_summary; ?></p>
	</div>
</div>

<div class="row"> 
	<div class="col-md-3 col-sm-3 course-action">
		<div class="course-enroll-box">
			<p>
				<?php
					if($course_is_course_free) echo "本课程对全部注册会员免费";
					else echo "本课程按课节收费";
				?>
			</p>
			<button class="btn btn-hilight">
				<?php
					if($course_is_course_free) echo '免费参加课程';
					else echo '免费注册报名学习';
				?>
			</button>
			<strong>已有<span><?php echo $course_number_of_participants; ?></span>人报名学习</strong>
			<ul class="stat-list">
				<li class="icon-list"><?php echo $course_number_of_sections; ?> 课节</li>
				<li class="icon-flow-tree"><?php echo $course_number_of_nodes; ?> 知识点</li>
				<li class="icon-pencil"><?php echo $course_number_of_questions; ?> 练习题</li>
			</ul>
		</div>
	</div>
	
	<div class="col-md-9 col-sm-9 course-detail">
		<div class="course-introduction">
			<h2>课程简介</h2>
			<p><?php echo $course_description; ?></p>
		</div>
		<div class="course-objective">
			<h2>课程目标</h2>
			<?php echo $course_objectives; ?>
		</div>
		
		<div class="course-content">
			<h2>课程内容</h2>
			
			<?php
				$count = 1;
				foreach($course_syllabus AS $row)
				{
					echo '<section><a href="#" title="">第'.$count.'课  '.$row['title'].'</span></a>';
					display_nested_nodes($row['guid'], $row['child']);
					echo '</section>';
					$count++;
				}
			?>	
			<p>课程内容仍在持续更新中...</p>
			
		</div>
		
		<div class="course-owner">
			<h2>关于老师</h2>
			<div class="owner-box"> 
				<div class="owner-info">
					<a href="#" title=""><img src="<?php echo $lecturer_avatar; ?>" alt="<?php echo $lecturer_username; ?>"></a>
					<div class="owner-about">
						<a href="#" title=""><?php echo $lecturer_username; ?></a>
						<p><?php echo $lecturer_signature; ?></p>
					</div>
					<div class="owner-comment"><p><?php echo $lecturer_bio; ?></p></div>
				</div>
			</div>
		</div>
	</div>
</div>
