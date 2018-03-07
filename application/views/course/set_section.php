<div class="col-md-9 col-sm-9">
	<div class="form form-inline">
		<?php echo form_open(base_url("course/".$unique_key.'/setting/node')); ?>
			<h4><?php echo $heading; ?></h4>
			<div class="form-body">
				<ul class="node-list">
				<?php foreach($sections AS $row) { ?>
					<li data-unique-key="<?php echo $row['unique_key']?>" ><?php echo $row['title']; ?>
					 	<input type="checkbox" name="switch" data-unique-key="<?php echo $row['unique_key']?>" <?php echo iif($row['is_open'], 'checked', '') ?> >
						<?php if((!$is_course_free AND !$row['is_open'] AND $fee_policy != 'BY_COURSE')) { ?>
							<a href="#" class="set-price-btn" data-price="<?php echo iif($row['price'] != 0 && $row['price'] != null, $row['price'], '' ); ?>"><?php echo iif($row['price'] != 0 && $row['price'] != null, $row['price'].'元', '设置价格' ); ?></a>
						<?php } ?>
					</li>
				<?php } ?>
				</ul>
			</div>
		</form>
	</div>
</div>