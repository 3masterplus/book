<header>
	<?php if($header_type == 'return'){ ?>
		<a href="<?php echo $return_link; ?>" title="<?php echo $text; ?>" class="back"><i class="icon-chevron-left"></i><?php echo $text; ?></a>
	<?php } elseif($header_type == 'header'){ ?>
		<strong><?php echo $text; ?></strong>
	<?php } ?>
	
	<div class="ctrl sm-hide">
		<?php if(!$this->_is_login){ ?>
			<a href="<?php echo base_url('user/login'); ?>" title="用户登录" class="show-login-popup">用户登录</a>
			<a href="<?php echo base_url('user/register'); ?>" class="btn hilight bordered show-register-popup">免费创建账号</a>
		<?php } else { ?>
			<div class="alert on">
				<i class="icon-bell"></i>
				<ul>
					<li class="white-bg">
						<a href="#">
							<span>新课程发布 · 5小时前</span>
							<b>绘图及图像</b>
						</a>
					</li>
					<li class="white-bg">
						<a href="#">
							<span>新课程发布 · 3小时前</span>
							<b>摄影与摄像</b>
						</a>
					</li>
				</ul>
			</div>
			<span class="line"></span>
			<div href="#" title="" class="avatar-box">
				<img src="<?php echo $this->my_lib->get_user_avatar($this->_user_unique_key); ?>">
				<i class="icon-angle-down"></i>
				<ul>
					<li class="white-bg"><a href="<?php echo base_url('dashboard'); ?>"><strong><?php echo $this->_username; ?></strong></a></li>
					<li><a href="<?php echo base_url('dashboard'); ?>"><?php echo $this->config->item('dashboard'); ?></a></li>
					<li><a href="<?php echo base_url('user/account/edit'); ?>">账号设置</a></li>
					<li><a href="<?php echo base_url('user/profile/edit'); ?>">个人资料</a></li>
					<li class="white-bg"><a href="<?php echo base_url('user/logout'); ?>">退出</a></li>
				</ul>
			</div>
		<?php } ?>
	</div>
	<div class="menu-switch"><i class="icon-bars"></i></div>
</header>