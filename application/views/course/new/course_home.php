<div class="card fullwidth blue">
	<div class="info">
		<div class="text-area">
			<h1><?php echo $course_title; ?></h1>
			<p><?php echo $course_summary; ?></p>
			<div class="info-list">
				<span><i class="icon-user-group"></i><?php echo $course_number_of_participants; ?> 个学生</span>
				<span><i class="icon-book2"></i><?php echo $course_number_of_sections; ?> 节课</span>
				<span><i class="icon-node-list"></i><?php echo $course_number_of_nodes; ?> 个知识点</span>
				<span><i class="icon-exam"></i><?php echo $course_number_of_questions; ?> 个练习题</span>
			</div>
		</div>
		<div class="avatar-area">
			<a href="#" title="username">
				<img src="<?php echo $lecturer_avatar; ?>" class="show-detail-modal">
				<p><?php echo $lecturer_username; ?></p>
				<?php if($course_publish_option == 'BY_COURSE'){ ?>
					<div class="status end">已完结</div>
				<?php } else { ?>
					<div class="status serialization">连载中</div>
				<?php } ?>
			</a>
		</div>
	</div>
	
	
	<?php if($accessibility['mode'] == 1){ ?>
		<div class="bottom-area">
			<button class="btn blue enroll" data-unique-key = "<?php echo $course_unique_key; ?>">参加学习</button><span class="free">免费学习</span>
		</div>	
	<?php } ?>
	
	<?php if($accessibility['mode'] == 2){ ?>
		<div class="bottom-area">
			<button class="btn orange bordered payment">立即购买</button>
			<strong><i>¥</i><?php echo $accessibility['by_course_price']; ?></strong>
			<b >一次购买，无限期学习</b>
		</div>
	<?php } ?>
	
	<?php if($accessibility['mode'] == 3){ ?>
		<div class="bottom-area"><a href="#">支持按课节购买（<?php print_r($accessibility['lowest_section_price']); ?> 元起），查看各课节页面购买</a></div>
	<?php } ?>
	
	<?php if($accessibility['mode'] == 4){ ?>
		<div class="bottom-area">
			<button class="btn orange bordered payment" data-unique-key = "<?php echo $course_unique_key; ?>">立即购买</button>
			<strong><i>¥</i><?php echo $accessibility['by_course_price']; ?></strong>
			<b>一次购买，无限期学习</b>
			<span class="or">或</span>
			<b class="black">按课节购买（<?php print_r($accessibility['lowest_section_price']); ?> 元起）</b>
          </div>
	<?php } ?>
	
	<?php if($accessibility['mode'] == 5){ ?>
		<?php $progress = $accessibility['learning_progress']; ?>
		<div class="bottom-area">
			<button class="btn blue" data-unique-key = "<?php echo $course_unique_key; ?>">
				<?php
					if($progress['percentage_of_course_completion'] > 0) echo '继续学习';
					elseif($progress['percentage_of_course_completion'] == 0) echo '立即学习';
					elseif($progress['percentage_of_course_completion'] == 100) echo '重新学习';
				?>
			</button>
			<p>
				<b>
					<?php if($progress['percentage_of_course_completion'] > 0){ ?>
						最新进度：</b>您在<a href="#"><?php echo time_std_format($progress['current_node_time_updated']); ?></a>学习了 <a href="<?php echo $progress['current_node_url'] ?>"><?php echo $progress['current_node_title']; ?></a>
					<?php } ?>
				</b>
			</p>
			
			<?php if($progress['percentage_of_course_completion'] > 0) { ?>
			<div class="mark">
              <div class="tip">您已完成了本课的 <?php echo $progress['percentage_of_course_completion']; ?>% </div>
              <span class="process green"><?php echo $progress['percentage_of_course_completion']; ?>%</span>
            </div>
            <?php } ?>
            
          </div>
	<?php } ?>
	
	
</div>	
<div class="primary-container xs-full">
	<h2>课程详情</h2>
	<div class="card"><p><?php echo $course_description; ?></p></div>
	
	<h2>课程内容（共<?php echo $course_number_of_sections; ?>节）</h2>
	
	<?php foreach($course_sections AS $row) { ?>
		
		<?php
			
			$percentage_of_section_completion = 0;
			$section_number_of_nodes = $this->course_lib->get_section_number_of_nodes($row['guid']);
			
			if($accessibility['mode'] == 5)
			{
				$percentage_of_section_completion = $this->course_lib->get_percentage_of_section_completion($user_guid, $row['guid']);
			}
		?>
		
		<?php if($section_number_of_nodes > 0){ ?>
		
		<div class="card has-bottom <?php echo iif($percentage_of_section_completion == 100, 'learned', ''); ?>" id="<?php echo $row['unique_key']; ?>">
			<div class="head-area">
				<div class="primary-area">
					<h3><a href="<?php echo base_url("course/section/$course_unique_key/".$row['unique_key']); ?>"><?php echo $row['title']; ?></a></h3>
					<p><?php echo $row['main']; ?></p>
				</div>
				
				<div class="side-area">
					
					<?php if($accessibility['mode'] == 3 OR $accessibility['mode'] == 4){ ?>
						<?php if(!$row['is_open']){ ?>
							<div class="vertical-middle">
								<div>
									<strong><span>¥</span><?php echo $row['price']; ?></strong>
									<a href="#" title="" class="btn orange payment"  data-unique-key="<?php echo $row['unique_key']; ?>">立即购买</a>
									<b>一次购买，无限期学习</b>
								</div>
							</div>
						<?php } else { ?>
							<div class="vertical-middle">
								<div><a href="#" title="" class="btn blue">开放试学</a></div>
							</div>
						<?php } ?>
					<?php } ?>
					
					<?php if($accessibility['mode'] == 2){ ?>
						<?php if(!$row['is_open']){ ?>
							<div class="vertical-middle">
								<div>
									<a href="#" title="" class="btn orange">查看详情</a>
								</div>
							</div>
						<?php } else { ?>
							<div class="vertical-middle">
								<div><a href="#" title="" class="btn blue">开放试学</a></div>
							</div>
						<?php } ?>
					<?php } ?>
					
					<?php if($accessibility['mode'] == 1){ ?>
						<div class="vertical-middle">
							<div><a href="#" title="" class="btn blue">立即学习</a></div>
						</div>
					<?php } ?>
					
					<?php if($accessibility['mode'] == 5){ ?>
						<div class="vertical-middle">
							<div><a href="#" title="" class="btn blue">立即学习</a></div>
						</div>
					<?php } ?>
					
				</div>
			</div>
			
			<div class="bottom-area">
				<div class="ctrl">
					<a href="#" data-section-unique-key="<?php echo $row['unique_key'];?>"><i class="icon-angle-down"></i>展开</a>
					<div class="right-area"><a href="#"><i class="icon-node-list"></i><?php echo $section_number_of_nodes; ?> 个知识点</a></div>
				</div>
			</div>
			
		</div>
		
		<?php } ?>
	<?php } ?>
</div>


<div class="side-container xs-full">
	<h3>谁适合学？</h3>
	<div class="list"><?php echo $course_audience; ?></div>
		
	<h3>能得到什么</h3>
	<div class="list"><?php echo $course_objectives; ?></div>
		
	<h3>关于教师</h3>
	<div class="teacher">
		<a href="#" title="" class="avatar"><img src="<?php echo $lecturer_avatar; ?>" class="show-detail-modal"></a>
		<div class="info">
			<a href="#" title=""><?php echo $lecturer_username; ?></a>
			
			<!--
			<div class="contact">
				<a href="#" title=""><i class="icon-weibo"></i></a>
				<a href="#" title=""><i class="icon-tencent-weibo"></i></a>
			</div>
			-->
			
		</div>
		<p class="intro"><?php echo $lecturer_signature; ?></p>
		<a href="#" titel="" class="btn bordered">了解更多信息 </a>
	</div>
</div>

<input type="hidden" name="course_unique_key" value="<?php echo $course_unique_key; ?>" >

