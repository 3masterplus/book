<div class="row container">
	<div class="col-md-5 col-lg-5 col-sm-5 col-center row-space-8">
		<div class="panel register">
			<?php echo form_open(base_url("user/register")); ?>
				<div class="panel-heading"><h4>创建新账号</h4></div>
				<div class="panel-body">
					<?php if($error_str != ''){ ?>
					
					<div role="alert" class="alert alert-danger alert-dismissible">
						<button type="button" data-dismiss="alert" aria-label="Close" class="close"><span aria-hidden="true">&times;</span></button>
						<?php echo $error_str; ?>
					</div>
					
					<?php } ?>
					
					<div class="form-group">
						<div class="input-group">
							<input type="text" placeholder="用户名" aria-describedby="" value="<?php echo $username; ?>" name="username" class="form-control"><span class="input-group-addon"> <i class="icon-head"></i></span>
						</div>
					</div>
			
					<div class="form-group">
						<div class="input-group">
							<input type="text" placeholder="电子邮箱" aria-describedby="" value="<?php echo $email; ?>" name="email" class="form-control"><span class="input-group-addon"> <i class="icon-mail"></i></span>
						</div>
					</div>
					
					<div class="form-group">
						<div class="input-group">
							<input type="password" placeholder="密码" aria-describedby="" name="password" class="form-control"><span class="input-group-addon"><i class="icon-unlock"></i></span>
						</div>
					</div>
					
					<div class="form-group">
						<div class="input-group captcha-box">
							<input type="text" placeholder="验证码" aria-describedby="" name="captcha" class="form-control">
							<?php echo $captcha['image']; ?>
							<i class="icon-refresh"></i>
						</div>
					</div>
					
					<input type="hidden" name="register_a_user" value="1">
					<input type="hidden" name="referrer" value="<?php echo $referrer; ?>">
					<button type="submit" class="btn btn-block btn-primary btn-large">创建新账号</button>
					
				</div>
				<hr>
				<div class="panel-body">已有账号？ <a href="/user/login">立即登录</a></div>
			</form>
		</div>
	</div>
</div>