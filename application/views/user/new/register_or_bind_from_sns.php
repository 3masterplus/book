<div class="account-header">
	<h1 class="logo"><img src="/public/new/css/img/logo.png"></h1>
	<h2>创建或绑定账号</h2>
</div>

<div class="account-card">
	<div class="login-account-select">
	  <div class="sns-avatar">
	  	<img src="<?php echo $avatar_url; ?>">
	  </div>
	  <div class="padding-area">
	  	<a href="#" title="创建新账号" class="btn big hilight" id="create-account-btn">创建新账号</a>
	  	<a href="#" title="绑定到已有账号" class="btn big" id="bind-account-btn">绑定到已有账号</a>
	    <p>请详细阅读 <a href="#">用户使用条款</a>。注册知乐，意味着您接受并遵守我们的相关条款。</p>
	  </div>
	  <div class="register-form form hide">
		  <div class="padding-area">
		    <input name="username" type="text" id="username" placeholder="用户昵称" value="<?php echo $user_name; ?>">
		    <!-- <input name="email" type="text" id="email" placeholder="电子邮箱" value=""> -->
		    <input name="password" type="password" id="password" placeholder="密码" value="">
		    <button class="btn hilight" id="create-account-wechat">创建新账号</button>
		  </div>
		  <div class="bottom-area">已经是知乐会员?<a href="#" title="绑定账号" class="bind-sns-link">绑定账号</a></div>
		</div>
		
		<div class="login-form form hide">
		  <div class="padding-area">
		    <input name="email" type="text" id="email" placeholder="电子邮箱" value="">
		    <input name="password" type="password" id="password" placeholder="密码" value="">
		    <button class="btn hilight" id="bind-account-wechat">绑定到此账号</button>
		  </div>
		  <div class="bottom-area">尚未注册?<a href="#" title="创建新账号" class="create-account-link">创建新账号</a></div>
		</div>
	  <input type="hidden" name="refer" value="<?php echo $refer; ?>" >
	</div>
</div>