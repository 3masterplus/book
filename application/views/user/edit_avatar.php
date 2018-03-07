<div class="col-md-9 col-sm-9 change-avatar-div">
	<div class="form">
		<h4>更新头像</h4>
		<div class="form-body">
			<div class="avatar-box">
				<input type="hidden" name="entity_unique_key" value="<?php echo $user_unique_key; ?>" >
				<input type="file" name="userfile" data-url="/file/ajax_upload_an_image" />
				<img src="<?php echo $avatar_url; ?>" >
			</div>
			<button class="btn btn-hilight btn-sm" id="upload-pic">修改头像</button>
			<div class="avatar-ctrl">
				<button class="btn btn-default btn-sm" id="upload-another-pic">重新上传</button>
				<button class="btn btn-hilight btn-sm" id="change-avatar">提交</button>
			</div>
		</div>	
	</div>	
</div>