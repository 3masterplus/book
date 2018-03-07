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
		<div style="display: none;">
			<img src="/public/images/share_img.jpg">
		</div>
		<div>
			<?php echo $template['partials']['navigation']; ?>
			<div class="main-container">
				<?php echo $template['partials']['header']; ?>
				<?php echo $template['body'] ?>
				<?php echo $template['partials']['footer']; ?>
			</div>
		</div>
	</body>
	<?php echo $template['partials']['endmeta']; ?>
	<script src="/public/new/js/vendor/sea.js"></script>
	<script src="/public/new/js/main.js"></script>

	<?php if(@$page == 'learn'){ ?>
		<input name="timestamp" value="<?php echo $timestamp; ?>" type="hidden">
		<input name="nonceStr" value="<?php echo $nonceStr; ?>"  type="hidden">
		<input name="signature" value="<?php echo $signature; ?>"  type="hidden">
		<input name="title" value="<?php echo $title; ?>"  type="hidden">
		        
	
	<?php } ?>

	<?php require(APPPATH.'views/partials/message.php'); ?>

</html>