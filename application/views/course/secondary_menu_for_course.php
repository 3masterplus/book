<header class="additional-nav">
	<div>
		<div class="course-name"><strong><?php echo $title; ?></strong></div>
		<nav><?php foreach($menu AS $row){ echo '<a href="'.$row['url'].'" '.iif($row['on'], 'class="on"', '').' >'.$row['text'].'</a>';}?></nav>
		<div class="action pull-right">
			<p>状态: <?php echo config(strtolower($status)); ?></p>
			<button class="btn btn-hilight btn-sm" id="submit-to-review"><?php echo iif(strtolower($status) == 'draft', '提交审核', '未知操作'); ?></button>
			<button class="btn btn-default btn-sm">课程预览</button>
		</div>
	</div>
</header>