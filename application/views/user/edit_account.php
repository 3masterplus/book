<?php $form_url = base_url('user/edit_account'); ?>
<div class="col-md-9 col-sm-9">
	<div class="form-inline bottom-space-5">
		<?php echo form_open($form_url); ?>
			<h4>修改用户名</h4>
			<div class="form-body">
				<div class="row bottom-space-4">
					<label for="username" class="col-sm-2">用户名</label>
					<div class="col-sm-10"><input type="text" id="username" name="username" value="<?php echo $username; ?>"></div>
				</div>
				<input type="hidden" name="update_username" value="1">
				<button class="btn btn-hilight">提交</button>
			</div>
		</form>
	</div>
	
	<div class="form-inline bottom-space-5">
		<?php echo form_open($form_url); ?>
			<h4>修改邮箱</h4>
			<div class="form-body">
				<div class="row bottom-space-4">
					<label for="email" class="col-sm-2">邮箱</label>
					<div class="col-sm-10"><input type="text" id="email" name="email" value="<?php echo $email; ?>"></div>
				</div>
				<input type="hidden" name="update_email" value="1">
				<button class="btn btn-hilight">提交</button>
			</div>
		</form>
	</div>
	
	<div class="form-inline bottom-space-5">
		<?php echo form_open($form_url); ?>
			<h4>修改密码</h4>
			<div class="form-body">
				<div class="row bottom-space-4">
					<label class="col-sm-2">原密码</label>
					<div class="col-sm-10"><input type="password" name="old_password" id="old_password" value=""></div>
				</div>
				<div class="row bottom-space-4">
					<label class="col-sm-2">新密码</label>
					<div class="col-sm-10"><input type="password" name="new_password" id="new_password" value=""></div>
				</div>
				<div class="row bottom-space-4">
				<label class="col-sm-2" for="new_password_confirm">重复新密码</label>
				<div class="col-sm-10"><input type="password" name="new_password_confirm" id="new_password_confirm" value=""></div>
				<input type="hidden" name="update_password" value="1">
				</div>
				<button class="btn btn-hilight">提交</button>
			</div>
		</form>
	</div>	
</div>