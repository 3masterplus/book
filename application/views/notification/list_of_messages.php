<div class="card full-width padding">
	<ul class="notification-list">

		<?php if ($items): foreach ($items as $k=>$item): ?>

		<li class="new"><span class="type"><?php echo $item['targetType_alias']; ?>  •  <?php echo $item['human_time_created']; ?></span><?php echo $item['message_with_link']; ?></li>

		<?php endforeach;endif; ?>
	</ul>
	
	<div class="pagination">
		<?php if ($prev_page){echo '<a href="'.$prev_page.'">';} ?><button class="btn bordered <?php if (!$prev_page){ echo 'disabled';}else{echo 'hilight';} ?> prev">上一页</button><?php if ($prev_page){echo '</a>';} ?>
		<div class="page">页数 <?php echo $current_page_no.'/',$pages_no; ?></div>
		<?php if ($next_page){echo '<a href="'.$next_page.'">';} ?><button class="btn bordered <?php if (!$next_page){ echo 'disabled';}else{echo 'hilight';} ?> next">下一页</button><?php if ($next_page){echo '</a>';} ?>
	</div>
</div>