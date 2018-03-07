<?php

	//获取当前问题的序号
	function get_current_index($array, $question_unique_key)
	{
		$i = 1;
		
		foreach($array AS $row)
		{
			if($row['unique_key'] == $question_unique_key)
			{
				return $i;
			}
			
			$i++;
		}
		
		return null;
	}
	
	$question_type	= $question['question']['type'];
	
	if($question_type == 'OPTION')
	{
		function which_option($options, $option_guid)
		{
			$n = 1;
			foreach($options AS $row)
			{
				if($row['id'] == $option_guid)
				{
					return show_abc($n);
				}
				
				$n++;
			}
		}
	}
	
	$return_url		= base_url('course/learn/'.$course_unique_key.'/'.$section_unique_key.'/'.$node_unique_key);
	$home_url		= base_url('/course/'.$course_unique_key.'/home');
	
	$current_index 	= get_current_index($quiz['questions'], $question_unique_key);
	$previous_index = $current_index - 1;
	
	if($previous_index > 0)
	{
		$previous_question_unique_key = $quiz['questions'][$previous_index - 1]['unique_key'];
		$previous_link = base_url('course/quiz/'.$course_unique_key.'/'.$section_unique_key.'/'.$node_unique_key.'/'.$quiz_unique_key.'/'.$previous_question_unique_key);				
	}
	else
	{
		$previous_link = '';
	}
	
	$next_index = $current_index + 1;
	
	if($next_index <= count($quiz['questions']))
	{
		$next_question_unique_key = $quiz['questions'][$next_index - 1]['unique_key'];
		$next_link = base_url('course/quiz/'.$course_unique_key.'/'.$section_unique_key.'/'.$node_unique_key.'/'.$quiz_unique_key.'/'.$next_question_unique_key);				
	}
	else
	{
		$next_link = '';
	}
	
?>

<div class="row row-space-12 row-fullwidth container">
	<div id="mobile-menu" class="col-md-12 col-sm-12 mobile"><i class="icon-circle-right show-section-box"></i><a href="#"><i class="icon-home"></i></a></div>
	<div id="menu" class="col-md-3 col-sm-3 section-box">
		<div>
			<div class="section-header"><a href="<?php echo $return_url; ?>" title="返回"><i class="icon-rewind"></i>返回</a><a href="<?php echo $home_url; ?>" title="" class="home"><i class="icon-home"></i></a></div>
			<div class="quiz-title"><a href="#" title=""><?php echo $quiz['title']; ?></a></div>
			<ul class="quiz-list">
				<?php
					$i = 1;
					foreach($quiz['questions'] AS $row)
					{
						$question_url = base_url('course/quiz/'.$course_unique_key.'/'.$section_unique_key.'/'.$node_unique_key.'/'.$quiz_unique_key.'/'.$row['unique_key']);
						echo '<li '.iif($row['unique_key'] == $question_unique_key, 'class = "on"', '').'><a href="'.$question_url.'" title="第 '.$i.' 题">第 '.$i.' 题</a></li>';
						$i++;
					}
				?>
			</ul>
		</div>
	</div>
	
	<div class="col-md-9 col-sm-9 content-box">
		<nav><a href="<?php echo $return_url; ?>" class="prev"><i class="icon-rewind"></i>返回</a></nav>
		<div class="breadcrumb">
		
		<a href="#">首页</a>
			<?php 
				foreach($breadcrumb AS $row)
				{
					if($row['subtype_id'] == 2)
					{
						$url = base_url("course/".$row['unique_key']."/home");
					}
					elseif($row['subtype_id'] == 3)
					{
						$url = base_url("course/learn/".$course_unique_key."/".$row['unique_key']);
					}
					elseif($row['subtype_id'] == 4)
					{
						$url = base_url("course/learn/".$course_unique_key."/".$section_unique_key.'/'.$row['unique_key']);
					}
					elseif($row['subtype_id'] == 5)
					{
						$url = base_url("course/quiz/".$course_unique_key."/".$section_unique_key.'/'.$node_unique_key.'/'.$quiz_unique_key);
					}
					
					echo '<a href="'.$url.'">'.$row['title'].'</a>';
				}
			?>
			<a href="#" class="on">第 <?php echo get_current_index($quiz['questions'], $question_unique_key); ?> 题</a>
		</div>
		
		
		
		<a href="<?php echo $previous_link; ?>" class="previous-question"><i class="icon-rewind"> 上一题</i></a>
		<div class="quiz-process"><p>第 <?php echo $current_index; ?> 题 / 共 <?php echo count($quiz['questions']); ?> 题</p></div>
		<a href="<?php echo $next_link; ?>" class="next-question">下一题<i class="icon-fast-forward"> </i></a>
		
		<div class="content">
			<?php if(count($answer_history) > 0 AND $question_type == 'OPTION') { ?>
				<div class="result-msg <?php echo iif($answer_history['result'] == 'T', "success", "error"); ?>">
					<p>您在 <?php echo time_std_format($answer_history['time_created']); ?> 回答本题，选择了答案
						<?php
							foreach(unserialize($answer_history['data']) AS $id)
							{
								echo " ".which_option($question['options'], $id);
							}					
						?>，
						回答<?php echo iif($answer_history['result'] == 'T', "正确", "错误"); ?>！
					</p>
				</div>
			<?php } ?>
			
			<?php if(count($answer_history) > 0 AND $question_type == 'GAP') { ?>
			<?php
				//计算争取的和错误的的答案数量
				$number_of_correct_answers = 0;
				$number_of_incorrect_answers = 0;
				
				//print_r($answer_history);
				
				foreach($answer_history['answers'] AS $row)
				{
					if($row['result'] == 'T')
					{
						$number_of_correct_answers++;
					}
					elseif($row['result'] == 'F')
					{
						$number_of_incorrect_answers++;
					} 
				}
				
				$str = '共'.count($answer_history['answers']).'个空，您';
				if($number_of_correct_answers > 0) $str.='填对了'.$number_of_correct_answers.'个空 ';
				if($number_of_incorrect_answers > 0) $str.='填错了'.$number_of_incorrect_answers.'个空';
				
				
			?>
				<div class="result-msg error">
					<p>您在 <?php echo time_std_format($answer_history['time_created']); ?> 回答了此道题。<?php echo $str; ?>。<a href="javascript:;" data-timestamp="<?php echo $answer_history['time_created']; ?>" id="show-latest-user-answer">点击这里，查看您的答案</a>。</p>
				</div>
			<?php } ?>
			
			<div class="question">
			<?php 
				if($question_type == 'OPTION')
				{
					echo '<p>'.$question['question']['main'].'</p>';
					echo '<ul class="choice-list">';
						$n = 1;
						foreach($question['options'] AS $row)
						{
							echo '<li id="'.$row['id'].'" data-correct="'.$row['is_correct'].'" data-explanation="'.$row['explanation'].'">';
							echo '<label><input name="choice" value="'.$row['id'].'" type="'.iif($question['number_of_correct_options'] > 1, 'checkbox', 'radio').'"><span>'.show_abc($n).'. '.$row['text'].'</span></label>';
							echo '</li>';
							$n++;
						}
					echo '</ul>';
				}
				elseif($question_type == 'GAP')
				{
					echo '<p>'.$question['question']['main'].'</p>';
				}
			?>
			</div>
			
			<input type="hidden" value="<?php echo $question_unique_key ?>" id="question-unique-key"/>
			<input type="hidden" value="<?php echo $question_type ?>" id="question-type"/>
			<button type="submit" id="submit-answer" class="btn btn-hilight">提交</button>
			<?php if(count($answer_history) > 0) { ?>
				<a href="#" class="get-answer">查看答案</a>
			<?php }?>
			<!--
			<div class="stat bottom-space-5">
				<div class="stat-box">你的正确率<strong>85<span>%</span></strong></div>
				<div class="versus">vs.</div>
				<div class="stat-box">平均正确率<strong>85<span>%</span></strong></div>
			</div>
			<a class="btn-small history-data">答题历史</a>
			-->
		</div>	
	</div>
</div>

<?php if($question_type == 'OPTION'){ ?>
	<div id="answer-box">
		<ul class="answer-list">
			<?php 
			$n = 1;
			foreach($question['options'] AS $row)
			{
				echo '<li class="'.iif($row['is_correct'], 'correct', 'incorrect').'">';
				echo '<i class="'.iif($row['is_correct'], 'icon-circle-check', 'icon-circle-cross').'"></i>';
				echo '<p>'.show_abc($n).'. '.$row['text'];
				if($row['explanation'] != ''){
					echo '<strong>解释说明：'.$row['explanation'].'</strong></p>';
				}
				else{
					echo '</p>';
				}
				echo '</li>';
				$n++;
			}
			?>
		</ul>
	</div>
<?php } ?>