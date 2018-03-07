<div class="form quiz-form" id="quiz-box" >
	<h4>添加测试</h4>
	<div class="form-body">
		<ul class="quiz-list">
			<?php $count = 1; ?>
			<?php foreach($quizzes AS $row){ ?>
				<li id="<?php echo $row['unique_key'] ?>" data-main="<?php echo $row['main'] ?>" data-weight="<?php echo $row['weight'] ?>">
					<div class="quiz-box">
						<strong>测试 <i><?php echo $count; ?></i>: 
							<span><?php echo $row['title']; ?></span>
							<a href="#" class="edit-quiz-btn">编辑</a>
						</strong>
						
						<?php if(count($row['questions']) > 0){ ?>
							<ul class="question-list">
								<?php $count1 = 1; ?>
								<?php foreach($row['questions'] AS $row1){?>
									<li id="<?php echo $row1['unique_key']?>" class="<?php echo $row1['type']; ?>" data-weight="<?php echo $row1['weight']; ?>">
										<p><i><?php echo $count1; ?>.</i> <?php echo $row1['main'] ?></p>
										<div>
											<a href="#" class="edit-a-question">编辑</a>
											<a href="#" class="delete-a-question">删除</a>
										</div>
									</li>
									<?php $count1++; ?>
								<?php } ?>
							</ul>
						<?php } ?>
						
						<a href="#" class="add-a-question"><i class="icon-plus"> </i>添加问题</a>
					</div>
				</li>	
				<?php $count++; ?>
			<?php } ?>
		</ul>
		<a href="#" id="add-a-quiz-btn"><i class="icon-plus"> </i>添加一个测试</a>
	</div>
</div>