<div class="account-card">
	<div class="padding-area">
		<div class="icon-area">
			<div class="icon <?php echo $status; ?>">
				<i class="<?php echo iif($status === 'success', 'icon-check', 'icon-cross') ?>"></i>
			</div>
			<strong>
				<?php echo $header; ?>
				<?php if($message != ''){ ?>
					<span><?php echo $message; ?></span>
				<?php } ?>
			</strong>
    	</div>
    	<?php if(count($button) > 0){ ?>
    		<a href="<?php echo $button['button_link']; ?>" title="<?php echo $button['button_text']; ?>" class="btn green"><?php echo $button['button_text']; ?></a>
    	<?php } ?>
	</div>
</div>