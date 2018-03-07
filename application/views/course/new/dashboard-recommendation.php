<?php
	$data = array('highlight' => '推荐课程', 'is_recommended' => True);
	$this->load->view('course/new/dashboard-menu', $data); 
?>

<h2 class="title">我们为您推荐如下课程：</h2>

<div class="course-list">

<?php 
	if(sizeof($courses_recommended) > 0)
	{
		foreach($courses_recommended AS $row)
		{
?>
			<div class="card half-width <?php echo $row['template']; ?>" >
				<div class="head-area">
					<div class="bar">
						<i class="icon-book2"></i>
						<span>包含 <?php echo $row['number_of_course_nodes']; ?> 个知识点</span>
					</div>
				</div>					

				<div class="full-area">
					<h3><a href="<?php echo base_url('course/'.$row['unique_key'].'/home'); ?>"><?php echo $row['title']; ?></a></h3>
					<p><?php echo $row['summary']; ?></p>
					<div class="bottom-box">
						<a href="<?php echo base_url('course/'.$row['unique_key'].'/home'); ?>" class="btn green">查看详情</a>
						<ul class="media-list-mini">
							<li><i class="icon-file-image"></i><div class="tip">图片</div></li>
							<li><i class="icon-file-audio"></i><div class="tip">音频</div></li>
							<li><i class="icon-file-video"></i><div class="tip">视频</div></li>
						</ul>
					</div>
				</div>
			</div>

<?php
		}
	}
?>

</div>