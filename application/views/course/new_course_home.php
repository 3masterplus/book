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
				<img src="<?php echo $lecturer_avatar; ?>">
				<p><?php echo $lecturer_username; ?></p>
			</a>
		</div>
	</div>
	<div class="bottom-area">
		<button class="btn blue">参加学习</button><span class="free">免费学习</span>
	</div>
</div>	
<div class="primary-container xs-full">
	<h2>课程详情</h2>
	<div class="card"><p><?php echo $course_description; ?></p></div>
	
	<h2>课程内容（共<?php echo $course_number_of_sections; ?>节）</h2>
	
	<?php foreach($course_sections AS $row) { ?>
		<div class="card has-bottom">
			<div class="head-area">
				<div class="primary-area">
					<h3><?php echo $row['title']; ?></h3>
					<p><?php echo $row['main']; ?></p>
				</div>
              <div class="side-area">
                <div class="vertical-middle">
                  <div><a href="#" title="" class="btn">重新学习</a></div>
                </div>
              </div>
            </div>
            <div class="bottom-area">
              <div class="ctrl"><a href="#" data-section-unique-key="<?php echo $row['unique_key'];?>"><i class="icon-angle-down"></i><span>展开</span></a>
                <div class="right-area"><a href="#"><i class="icon-node-list"></i>13 个知识点</a></div>
              </div>
            </div>
          </div>
    <?php } ?>
    
	<div class="card lock">
            <div class="locker"><i class="icon-lock"></i></div>
            <p>课程开发中</p>
          </div>
</div>
<div class="side-container xs-full">
	<h3>谁适合学？</h3>
	<div class="list"><?php echo $course_audience; ?></div>
		
	<h3>能得到什么</h3>
	<div class="list"><?php echo $course_objectives; ?></div>
		
	<h3>关于教师</h3>
	<div class="teacher">
		<a href="#" title="" class="avatar"><img src="<?php echo $lecturer_avatar; ?>"></a>
		<div class="info">
			<a href="#" title=""><?php echo $lecturer_username; ?></a>
			<div class="contact">
				<a href="#" title=""><i class="icon-weibo"></i></a>
				<a href="#" title=""><i class="icon-tencent-weibo"></i></a>
			</div>
		</div>
		<p class="intro"><?php echo $lecturer_signature; ?></p>
		<a href="#" titel="" class="btn bordered">了解更多信息 </a>
	</div>
</div>

<input type="hidden" name="course_unique_key" value="<?php echo $course_unique_key; ?>" >