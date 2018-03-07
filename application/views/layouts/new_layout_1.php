<!DOCTYPE html>
<html lang="zh">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
		<title><?php echo $template['title'] ?></title>
		<?php echo $template['partials']['meta']; ?>
	</head>
	<body class="<?php echo @$page; ?>">
		<div class="vertical-container">
			<?php echo $template['body'] ?>
			<?php echo $template['partials']['footer']; ?>
		</div>
	</body>
	<?php echo $template['partials']['endmeta']; ?>
	<?php require(APPPATH.'views/partials/message.php'); ?>
</html>