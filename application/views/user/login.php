<div class="row container">
	<div class="col-md-5 col-lg-5 col-sm-5 col-center row-space-8">
		<div class="panel login">
			<?php echo form_open(base_url("user/login")); ?>
				<div class="panel-heading"><h4>登录</h4></div>
				<div class="panel-body">
				
					<?php if($error_str != ''){ ?>
						<div role="alert" class="alert alert-danger alert-dismissible">
							<button type="button" data-dismiss="alert" aria-label="Close" class="close"><span aria-hidden="true">&times;</span></button>
							<?php echo $error_str;?>
						</div>
					<?php } ?>
					
					<div class="form-group">
						<div class="input-group">
							<input type="text" placeholder="电子邮箱" value="<?php echo $email; ?>" aria-describedby="" name="email" class="form-control"><span class="input-group-addon"> <i class="icon-mail"></i></span>
						</div>
					</div>
					
					<div class="form-group">
						<div class="input-group">
							<input type="password" placeholder="密码" aria-describedby="" name="password" class="form-control"><span class="input-group-addon"><i class="icon-unlock"></i></span>
						</div>
					</div>
					
					<?php if($is_max_login_attempts_exceeded){ ?>
						<div class="form-group">
							<div class="input-group captcha-box">
								<input type="text" placeholder="验证码" aria-describedby="" name="captcha" class="form-control">
								<?php echo $captcha['image']; ?>
								<i class="icon-refresh"></i>
							</div>
						</div>
					<?php } ?>
					
					<div class="clearfix row-space-2">
						<label for="remember-me" class="checkbox remember-me pull-left">
							<input type="checkbox" name="remember_me" value="1" class="remember_me">记住我
						</label><a href="<?php echo base_url('user/forgot_password'); ?>" class="forgot-password pull-right">忘记密码？</a>
					</div>
					
					<input type="hidden" name="login_a_user" value="1">
					<input type="hidden" name="referrer" value="<?php echo $referrer; ?>">
					
					<button type="submit" class="btn btn-block btn-primary btn-large">登录</button>
				</div>
				<hr>
				<div class="panel-body">还没有帐号？<a href="<?php echo base_url('user/register'); ?>" class="link-to-signup-in-login">注册</a></div>
			</form>
		</div>
	</div>
</div>