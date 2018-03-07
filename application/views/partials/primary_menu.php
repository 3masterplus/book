<header class="nav">
	<div class="menu"><i class="icon-menu"></i></div>
	<a href="<?php echo base_url('home'); ?>" class="logo">知乐</a>
	
	<nav class="pull-left">
		<a href="<?php echo base_url('admin/get_courses'); ?>">课程管理</a>
		<!--
			<a href="#">发布新课程</a>
			<div class="search-box"><i class="icon-search"></i><input type="text"></div>
		-->
	</nav>
	
	<div class="account-area">
		<?php if(!$is_login) { ?>
			<a href="<?php echo base_url('user/login'); ?>" >登录</a> | 
			<a href="<?php echo base_url('user/register'); ?> ">注册</a>
		<?php } else { ?>
			<div class="dropdown">
				<button type="button" id="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" class="btn btn-default dropdown-toggle">
					<!--<img src="<?php echo $avatar_url; ?>" alt="">-->
					<i><?php echo $username; ?></i><span class="caret"></span>
				</button>
				<ul aria-labelledby="dropdownMenu1" class="dropdown-menu">
					<li><a href="<?php echo base_url('admin/get_courses'); ?>">退出</a></li>
				</ul>
			</div>
		<?php } ?>
		<!--<a class="btn btn-default btn-sm">我要开课</a>-->
	</div>
</header>


<div class="mobile-nav">
	<div class="search-box"><i class="icon-search"></i><input type="text"></div>
	<ul>
		<li> <a href="#">课程管理</a></li>
		<!--<li><a href="#">制作课程</a></li>-->
	</ul>
</div>
