<div class="card padding">
	<div class="avatar-area">
		<div class="avatar"><img src="<?php echo $this->my_lib->get_user_avatar($this->_user_unique_key); ?>"></div>
		<div class="ctrl-area">
			<button class="btn bordered" id="upload-avatar" data-key="<?php echo $unique_key; ?>">选择文件</button><span></span>
			<input type="file" name="userfile" id="userfile" data-url="/file/ajax_upload_an_image">
			<p>上传图片格式为 jpeg、png，大小在 500K 以内。</p>
		</div>
	</div>
	
	<?php echo form_open(base_url('user/profile/edit')); ?>
		<div class="ctrl-cell">
			<label for="username">姓名</label>
			<input name="username" type="text" value="<?php echo $username; ?>" id="username" autocomplete="off">
		</div>
		<div class="ctrl-cell">
			<label for="signature">用户签名</label>
			<textarea name="signature" id="signature"><?php echo $signature; ?></textarea>
		</div>
		<div class="ctrl-cell">
			<label for="about">用户详细介绍</label>
			<textarea name="bio" id="about" class="big"><?php echo $bio; ?></textarea>
		</div>
		<input type="hidden" name="edit_profile" value="1">
		<input type="submit" value="更新用户信息" class="btn green">
	</form>
</div>