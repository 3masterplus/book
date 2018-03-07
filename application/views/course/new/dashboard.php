<div class="card fullwidth tab-link">
	<nav>
		<a href="<?php echo base_url('dashboard'); ?>" class="on">学习中</a>
		<a href="<?php echo base_url('dashboard/completed'); ?>">已完成</a>
	</nav>
</div>

<?php if(sizeof($current) > 0){ ?>
	<h2 class="title">最近一次学习的课程</h2>
	<div class="card fullwidth padding">

		<div class="head-area">
			<h2 class="title">
				<?php echo $current['title']; ?>
				<span>剩余 <?php echo $current['number_of_nodes_unlearned']; ?> 个知识点没学</span>
			</h2>
		</div>
	
		<div class="full-area">
			<p>
				根据系统记录，你上次学习的知识点是<a href="#"><?php echo $current['current_node_title']; ?></a> 。
				你已经完成了 <?php echo $current['percentage_of_course_completion']; ?>%，你可以继续你的学习：</p>
				<div class="section-box">
				<section>
					<a href="#">
						<i class="icon-chevron-left-circle"></i>
						<span><?php echo $current['next_node_title']; ?></span>
					</a>
					<div class="media-area">
						<?php if($current['next_node_number_of_image'] > 0){ ?>
							<i class="icon-image"></i>
						<?php } ?>
				
						<?php if($current['next_node_number_of_mp3'] > 0){ ?>
							<i class="icon-file-audio"></i>
						<?php } ?>
					
						<?php if($current['next_node_number_of_video'] > 0){ ?>
							<i class="icon-video"></i>
						<?php } ?>
						
					</div>
				</section>
    	    </div>
    	    <div class="bottom-box"><button class="btn green">开始学习</button></div>
        </div>
    </div>
<?php } ?>

<h2 class="title">其他进行中的课程</h2>
<div class="course-list">
	<div class="card half-width c1">
		<div class="head-area">
			<div class="bar">
				<i class="icon-book2"></i>
				<ul class="dot-list">
					<li class="finished"></li>
					<li class="current"></li>
				</ul>
				<span>剩余10个知识点</span>
			</div>
		</div>
		
		<div class="full-area">
			<h3>第二课：绘图及图像</h3>
				<p>根据系统记录，你上次学习的课程是<a href="#">文本处理及正则表达式</a> 。你已经完成了 20%，你可以继续你的学习：</p>
				<div class="section-box"> 
					<section>
						<a href="#">
							<i class="icon-chevron-left-circle"></i>
							<span>搜索引擎、数据库应用、信息安全</span>
						</a>
						
						<div class="media-area">
							<i class="icon-file-image"></i>
							<i class="icon-file-audio"></i>
							<i class="icon-file-video"></i>
							<i class="icon-file-exam"></i>
						</div>
					</section>
				</div>
              <div class="bottom-box">
              <button class="btn green">开始学习</button><a href="#" class="bookmark"><i class="icon-bookmark"></i>收藏</a>
            </div>
        </div>
    </div>
</div>