<!DOCTYPE html>
<html lang="zh">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
		<title><?php echo $template['title'] ?></title>
		<?php echo $template['partials']['meta']; ?>
	</head>
	<body class="index">
		<div>
			<nav class="mobile-sidebar">
				<a href="#" class="on"><i class="icon-home2"></i><?php echo $this->config->item('home'); ?></a>
				<a href="/course"><i class="icon-course"></i><?php echo $this->config->item('all_courses'); ?></a>
				<a href="<?php echo $this->config->item('wechat_redirect_url'); ?>" class="smart-login"><i class="icon-mine"></i><?php echo $this->config->item('account'); ?></a>
			</nav>
			<div class="main-container index-container">
				<?php echo $template['partials']['navigation']; ?>
				<?php echo $template['body'] ?>
				<?php echo $template['partials']['footer']; ?>
			</div>
		</div>
	</body>
	<?php echo $template['partials']['endmeta']; ?>
</html>