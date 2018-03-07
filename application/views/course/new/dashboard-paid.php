<?php
	$data = array('highlight' => '已购买课程', 'is_recommended' => False);
	$this->load->view('course/new/dashboard-menu', $data); 
?>

<div class="course-list">
	<div class="card half-width c5">
		<div class="head-area">
			<div class="bar">
				<i class="icon-book2"></i>
				<ul class="dot-list">
					<li class="finished"></li>
					<li class="current"></li>
					<li> </li>
					<li> </li>
				</ul>
				<span>剩余10个知识点</span>
			</div>
		</div>
		
		<div class="full-area">
			<h3>第二课：绘图及图像</h3>
			<p>根据系统记录，你上次学习的课程是<a href="#">文本处理及正则表达式</a> 。你已经完成了 20%，你可以继续你的学习：</p>
			<div class="section-box"> 
				<section>
					<a href="#"><i class="icon-chevron-left-circle"></i><span>搜索引擎、数据库应用、信息安全</span></a>
					<div class="media-area"><i class="icon-file-image"></i><i class="icon-file-audio"></i><i class="icon-file-video"> </i><i class="icon-file-exam"></i></div>
				</section>
			</div>
			<div class="bottom-box">
				<button class="btn green">开始学习</button><a href="#" class="bookmark"><i class="icon-bookmark"></i>收藏</a>
			</div>
		</div>
	</div>
</div>