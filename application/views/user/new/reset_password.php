<div class="account-header">
	<h1 class="logo"><img src="/public/new/css/img/logo.png"></h1>
	<h2>重置密码</h2>
</div>

<div class="account-card">
	<div class="login-form form">
		<div class="padding-area">
			<?php echo form_open(base_url("user/reset_password/$user_unique_key/$encrypted_str")); ?>
				
				<?php if($error_str != ''){ ?>
					<div class="message"><?php echo $error_str; ?><i class="icon-cross"></i></div>
				<?php } ?>
				
				<input name="new_password" type="password" id="password" placeholder="新密码" autocomplete="off">
				<input name="new_password_repeat" type="password" id="new_password_repeat" placeholder="确认新密码" autocomplete="off">
				
				<input type="hidden" name="reset_password" value="1">
				<button class="btn hilight">重置密码</button>
				
			<form>
		</div>
		<div class="bottom-area">如果遇到任何问题，请和我们联系！</div>
	</div>
</div>