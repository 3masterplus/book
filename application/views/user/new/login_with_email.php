<div class="account-header">
	<h1 class="logo"><img src="/public/new/css/img/logo.png"></h1>
	<h2>登录</h2>
</div>

<div class="account-card">
	<div class="login-form form">
		<div class="padding-area">
			<?php echo form_open(base_url("user/login")); ?>

				<a class="link btn green" id="show-wechat-login"><i class="icon-wechat"></i>使用微信账号登录</a>
				<span class="split"><i>或</i></span>
				
				<?php if($error_str != ''){ ?>
					<div class="message"><?php echo $error_str; ?><i class="icon-cross"></i></div>
				<?php } ?>
				
				<input <?php echo iif(in_array('email', $error_marker), 'class="error"', ''); ?> name="email" type="text" id="email" placeholder="电子邮箱" value="<?php echo $email; ?>" autocomplete="off">
				<input <?php echo iif(in_array('password', $error_marker), 'class="error"', ''); ?> name="password" type="password" id="password" placeholder="密码" value="" autocomplete="off">
				
				<?php if($is_max_login_attempts_exceeded){ ?>	
					<div class="captcha-area">
						<input <?php echo iif(in_array('captcha', $error_marker), 'class="error"', ''); ?> name="captcha" id="captcha" type="text" value="" placeholder="验证码" autocomplete="off">
						<div class="captcha-img-box"><?php echo $captcha['image']; ?></div><i class="icon-repeat"></i>
					</div>
				<?php } ?>
			
				<div class="ctrl-area">
					<input name="remember" type="checkbox" id="remember">
					<label for="remember">记住我</label><a href="<?php echo base_url('user/forgot_password'); ?>">忘记密码？</a>
				</div>
				
				<input type="hidden" name="login_a_user" value="1">
				<input type="hidden" name="referrer" value="<?php echo $referrer; ?>">
				<button class="btn hilight">登录</button>
				
			<form>
		</div>
		
		<div class="bottom-area">还未注册？<a href="<?php echo base_url('user/register'); ?>" title="返回加入">立即注册</a></div>
	
	</div>

	<div class="wechat-login">
	  <div class="">
	    <div class="form">
	      <p class="note center">打开您手机上的微信，扫描下面的二维码</p>
	      <div class="qrcode-area">
	      	<!-- <img src="../public/new/css/img/qrcode.png"></div><a href="#" title="使用邮箱注册" class="link">  -->
	      	<div id="qrcode"></div>
	      	<a href="#" class="link green" id="show-email-login"><i class="icon-mail"></i>或使用邮箱注册</a>
	    </div>
	  </div>
	  </div>
	  <div class="bottom-area">尚未注册?<a href="<?php echo base_url('user/register'); ?>" title="返回加入">免费注册</a></div>
	</div>

</div>