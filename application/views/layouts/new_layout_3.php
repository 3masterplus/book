<!DOCTYPE html>
<html lang="zh">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
		<title><?php echo $template['title'] ?></title>
		<link href="/public/new/js/vendor/mmenu/jquery.mmenu.css" rel="stylesheet">
		<link href="/public/new/js/vendor/mmenu/jquery.mmenu.positioning.css" rel="stylesheet">
		<?php echo $template['partials']['meta']; ?>
	</head>
	<body class="<?php echo @$page; ?>">
		<div>
			<?php echo $template['partials']['navigation']; ?>
			<div class="main-container">
				<?php echo $template['partials']['header']; ?>
				<div class="primary"><?php echo $template['body'] ?></div>
				<?php echo $template['partials']['sidebar'] ?>
				<?php echo $template['partials']['footer']; ?>
			</div>
		</div>
	</body>
	<?php echo $template['partials']['endmeta']; ?>
	<script src="/public/new/js/vendor/mmenu/jquery.mmenu.min.js"></script>
	<script src="/public/new/js/vendor/sea.js"></script>
	<script src="/public/new/js/main.js"></script>
	<?php require(APPPATH.'views/partials/message.php'); ?>
</html>