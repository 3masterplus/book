<?php
	//获取当前问题的序号
	function get_current_index($array, $question_unique_key)
	{
		$i = 1;
		foreach($array AS $row)
		{
			if($row['unique_key'] == $question_unique_key) return $i;
			$i++;
		}
		return;
	}
	
	$current_index = get_current_index($questions, $question_unique_key);
?>

<div class="card quiz">
	<div class="head-area"><h1>第 <?php echo $current_index; ?> 题（填空题）</h1></div>
	<div class="quiz-detail">
		<?php echo $question['question']['main']; ?>
		<ul class="answer-list">
			<?php $n = 1; ?>
			<?php foreach($question['options'] AS $row){ ?>
				<li>
					<div class="checkbox">
						<span><?php echo show_abc($n); ?></span>
						<input type="checkbox" name="A" value="abc">
					</div>
					<p><?php echo $row['text']; ?></p>
				</li>
				<?php $n++; ?>
			<?php } ?>
		</ul>
		<button class="btn green">下一题</button>
	</div>
</div>