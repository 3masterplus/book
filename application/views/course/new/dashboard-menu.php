<div class="card fullwidth tab-link">
	<nav>
		<a href="<?php echo base_url('dashboard'); ?>" class="<?php echo iif($highlight == '学习中的课程', 'on', ''); ?>">学习中</a>
		<a href="<?php echo base_url('dashboard/completed'); ?>" class="<?php echo iif($highlight == '已完成课程', 'on', ''); ?>">已完成</a>
	</nav>
</div>