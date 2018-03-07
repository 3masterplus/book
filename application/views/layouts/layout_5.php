<!DOCTYPE html>
<html lang="zh">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
	<title><?php echo $template['title'] ?></title>
	<?php echo $template['partials']['meta']; ?>
	<body class="<?php echo @$page; ?>">
		<?php echo $template['partials']['primary_menu']; ?>		
		<?php echo $template['body']; ?>
		<?php echo $template['partials']['footer']; ?>
		<?php echo $template['partials']['endmeta']; ?>
	</body>
</html>