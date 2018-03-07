<div class="col-md-9 col-sm-9">
	<div class="form-inline bottom-space-5">
		<h4><?php echo $heading; ?></h4>
		<div class="form-body">
			<?php if(count($courses) > 0){ ?>
				<?php foreach($courses AS $row){ ?>
					<div class="row setting-list bottom-space-4">
						<div class="col-sm-6 text-box">
							<p><a href="<?php echo base_url('admin/approve_a_course/'.$row['id']); ?>"><?php echo $row['title']; ?></a></p>
							<?php echo time_std_format($row['time_created']); ?>
						</div>
						<a class="btn btn-default btn-sm pull-right"><?php echo $row['status']; ?></a>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
</div>