<div class="row container">
	<div class="col-md-6 col-lg-4 col-center row-space-8">
		<div class="panel login">
			<?php echo form_open(base_url("user/forgot_password")); ?>
				<div class="panel-heading"><h4>找回密码</h4></div>
				<div class="panel-body">
					<?php if(strlen($error_str) > 0){ ?>
						<div role="alert" class="alert alert-danger alert-dismissible">
							<button type="button" data-dismiss="alert" aria-label="Close" class="close"><span aria-hidden="true">&times;</span></button>
							<?php echo $error_str;?>
						</div>
					<?php } ?>
					
					<div class="form-group"><div class="input-group"><input type="text" placeholder="电子邮箱" aria-describedby="" name="email" class="form-control"><span class="input-group-addon"> <i class="icon-mail"></i></span></div></div>
					<input type="hidden" name="forgot_password" value="1">
					<button type="submit" class="btn btn-block btn-primary btn-large">找回密码</button>
				</div>
				<hr>
				<div class="panel-body">还没有帐号？<a href="<?php echo base_url('user/register'); ?>" class="link-to-signup-in-login">注册</a></div>
			</form>
		</div>
	</div>
</div>