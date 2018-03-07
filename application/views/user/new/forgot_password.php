<div class="account-header">
	<h1 class="logo"><img src="/public/new/css/img/logo.png"></h1>
	<h2>找回密码</h2>
</div>

<div class="account-card">
	<div class="form reset-password">
		<div class="padding-area">
			<?php echo form_open(base_url("user/forgot_password")); ?>
				<p class="note">输入您注册的电子邮箱，查收邮件，根据提示，重置密码。</p>
				<?php if($error_str != ''){echo '<div class="message">'.$error_str.'<i class="icon-cross"></i></div>';} ?>
				<input <?php echo iif($error_str !='', 'class="error"', ''); ?> name="email" type="text" id="email" placeholder="电子邮箱" value="" autocomplete="off">
				<input type="hidden" name="forgot_password" value="1">
				<button type="submit" class="btn big hilight">重置密码</button>
			</form>
		</div>
		<div class="bottom-area">已重置了密码？<a href="<?php echo base_url('user/login'); ?>" title="返回加入">返回登录</a></div>
	</div>
</div>