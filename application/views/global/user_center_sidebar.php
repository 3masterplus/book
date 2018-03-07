<div class="side-box">
	<nav>
		<a <?php echo iif($current == 'dashboard', 'class="current"', ''); ?> href="<?php echo base_url('dashboard'); ?>" title="<?php echo $this->config->item('dashboard'); ?>"><?php echo $this->config->item('dashboard'); ?></a>
		<a <?php echo iif($current == '账号设置', 'class="current"', ''); ?> href="<?php echo base_url('user/account/edit'); ?>" title="账号设置">账号设置</a>
		<a <?php echo iif($current == '个人资料', 'class="current"', ''); ?> href="<?php echo base_url('user/profile/edit'); ?>" title="个人资料">个人资料</a>
	</nav>
</div>