<div class="account-header">
	<h1 class="logo"><img src="/public/new/css/img/logo.png"></h1>
	<h2>注册</h2>
</div>

<div class="account-card">
	<div class="register-form form">
		<div class="padding-area">
			<?php echo form_open(base_url("user/register")); ?>
				<a class="link btn green" id="show-wechat-login"><i class="icon-wechat"></i>使用微信账号登录</a>
				<span class="split"><i>或</i></span>
				<?php if($error_str != ''){ ?><div class="message"><?php echo $error_str; ?><i class="icon-cross"></i></div><?php } ?>
				<input <?php echo iif(in_array('username', $error_marker), 'class="error"', ''); ?> name="username" type="text" id="username" placeholder="姓名" value="<?php echo $username; ?>" autocomplete="off">
				<input <?php echo iif(in_array('email', $error_marker), 'class="error"', ''); ?> name="email" type="text" id="email" placeholder="电子邮箱" value="<?php echo $email; ?>" autocomplete="off">
				<input <?php echo iif(in_array('password', $error_marker), 'class="error"', ''); ?> name="password" type="password" id="password" placeholder="密码" value="" autocomplete="off">
				<div class="captcha-area">
					<input <?php echo iif(in_array('captcha', $error_marker), 'class="error"', ''); ?> name="captcha" id="captcha" type="text" value="" placeholder="验证码" autocomplete="off">
					<div class="captcha-img-box"><?php echo $captcha['image']; ?></div><i class="icon-repeat" id="change-captcha"></i>
				</div>
				<input type="hidden" name="register_a_user" value="1">
				<input type="hidden" name="referrer" value="<?php echo $referrer; ?>">
				<button class="btn hilight" style="margin-top: 25px;">注册新账号</button>
				<!-- <a class="link"><i class="icon-wechat"></i>使用微信创建新账号</a> -->
			</form>
		</div>
		<div class="bottom-area">已经注册？<a href="<?php echo base_url('user/login'); ?>" title="点击登录">立即登录</a></div>
	</div>
	<div class="wechat-login">
	  <div class="padding-area">
	    <div class="form">
	      <p class="note center">打开您手机上的微信，扫描下面的二维码</p>
	      <div class="qrcode-area">
	      	<!-- <img src="../public/new/css/img/qrcode.png"></div><a href="#" title="使用邮箱注册" class="link">  -->
	      	<div id="qrcode"></div>
	      	<a href="#" class="link green" id="show-email-register"><i class="icon-mail"></i>或使用邮箱注册</a>
	    </div>
	  </div>
	  </div>
	  <div class="bottom-area">尚未注册?<a href="<?php echo base_url('user/register'); ?>" title="返回加入">免费注册</a></div>
	</div>
</div>