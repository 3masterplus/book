<div class="col-md-9 col-sm-9">
	<div class="form">
		<?php echo form_open(base_url('user/profile/edit')); ?>
			<h4>个人资料</h4>
			<div class="form-body">
				<label>签名 <i>签名说明 </i><textarea name="signature"><?php echo $signature; ?></textarea></label>
				<label>简介 <textarea name="bio"><?php echo $bio; ?></textarea></label>
				<input type="hidden" name="edit_profile" value="1">
				<button class="btn btn-hilight">提交</button>
			</div>	
		</form>
	</div>	
</div>