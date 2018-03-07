<?php
	function display_nested_nodes($father_guid, $array, $course_unique_key)
	{
		if(count($array) > 0)
		{
			foreach($array AS $row)
			{
				$node_url = '/course/learn/'.$course_unique_key.'/'.$row['section_unique_key'].'/'.$row['unique_key'];
				echo '<div class="node">';
					echo '<a href="'.$node_url.'">'.$row['title'].'</a>';
					if(count($row['child']) > 0) display_nested_nodes($row['guid'], $row['child'], $course_unique_key);
				echo '</div>';
			}
		}
	}
?>

<div class="row row-space-12 row-fullwidth">
	<div class="learn-process">
		<p>您已经完成了</p><strong><?php echo $percentage_of_course_completion; ?>%</strong>
		<div class="process-box"><div class="process-bar" data-width="<?php echo $percentage_of_course_completion; ?>%"></div></div>
	</div>
	
	<div class="course-content">
        <h2>课程内容</h2>
        
        <?php 
        	$count = 1;
        	foreach($course_syllabus AS $row)
        	{
        		$section_url = '/course/learn/'.$course_unique_key.'/'.$row['section_unique_key'];
        		echo '<section class = "finished"><a href="'.$section_url.'">第'.$count.'课 '.$row['title'].'</a>';
        		display_nested_nodes($row['guid'], $row['child'], $course_unique_key);
        		echo '</section>';
        		$count++;
        	}
        ?>
    </div>
</div>