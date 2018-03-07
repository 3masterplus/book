<div class="col-md-9 col-sm-9">
	<div class="form-inline bottom-space-5">
		<h4><?php echo $heading; ?></h4>
		<div class="form-body">
			<?php if(count($courses) > 0){ ?>
				<?php foreach($courses AS $row){ ?>
					<div class="row setting-list bottom-space-4">
						<div class="col-sm-6 text-box">
							<p>
								<a href="<?php echo base_url('build/build_syllabus/'.$row['unique_key']); ?>"><?php echo $row['title']; ?></a>
								<a href="<?php echo base_url('build/build_syllabus/'.$row['unique_key']); ?>">修改</a>
								<a target="_blank" href="<?php echo base_url('course/'.$row['unique_key'].'/home'); ?>">预览</a>
							</p>
						</div>
					</div>
				<?php } ?>
			<?php } else { ?>
				尚无相关记录
			<?php } ?>
		</div>
	</div>
</div>