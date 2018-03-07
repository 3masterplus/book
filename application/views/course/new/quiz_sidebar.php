<?php //echo $question_unique_key; ?>
<div class="side-box">
	<strong><?php echo $title; ?></strong>
	<ul class="quiz-list">
		<?php
			$i = 1;
			foreach($questions AS $row)
			{
				$question_url = base_url('course/quiz/'.$course_unique_key.'/'.$section_unique_key.'/'.$node_unique_key.'/'.$quiz_unique_key.'/'.$row['unique_key']);
				echo '<li '.iif($row['unique_key'] == $question_unique_key, 'class = "on"', '').'><a href="'.$question_url.'" title="第 '.$i.' 题">第 '.$i.' 题</a></li>';
				$i++;
			}
		?>
	</ul>
</div>