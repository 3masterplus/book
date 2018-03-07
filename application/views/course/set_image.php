<div class="col-md-9 col-sm-9">
	<div class="form">
		<?php echo form_open(base_url("course/".$unique_key.'/setting')); ?>
			<h4><?php echo $heading; ?></h4>
			<div class="form-body">
				<div class="upload-box">
					<input type="hidden" name="entity_unique_key" value="<?php echo $unique_key; ?>" >
					<input type="file" name="userfile" data-url="/file/ajax_upload_an_image" />
					<img src="<?php echo $banner_url; ?>" >
				</div>
				<button class="btn btn-hilight btn-sm" id="upload-pic">上传课程图片</button>
				<div class="upload-ctrl	">
					<button class="btn btn-default btn-sm" id="upload-another-pic">重新上传</button>
					<button class="btn btn-hilight btn-sm" id="confirm-submit">提交</button>
				</div>
				<input type="hidden" name="status" value="<?php echo $status; ?>">
				<input type="hidden" name="set_a_course" value="1">
			</div>
		</form>
	</div>
</div>