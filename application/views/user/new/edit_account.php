<div class="card padding">

	<?php echo form_open(base_url('user/edit_account')); ?>
		<h2 class="form-title">修改邮箱</h2>
		<?php if( $email ): ?>
			<p class="status-text" id="email-text"><?php echo $email; ?><span><?php echo iif($this->_is_email_verified, '', '（未验证）'); ?></span>
			<?php if($this->_is_email_verified): ?>
				<a href="#" class="set" for="set-email-cell">设置</a>
			<?php else: ?>
				<a href="#" class="re-verify" for="set-email-cell" data-email="<?php echo $email; ?>">重新验证</a>
			<?php endif; ?>
		</p>
		<?php else: ?>
			<p class="status-text" id="email-text">未设置电子邮箱<a href="#" class="set" for="set-email-cell">设置</a></p>
		<?php endif; ?>
		<div class="ctrl-cell ctrl-hide" for="email-text" id="set-email-cell">
			<input name="email" type="text" value="" id="email" autocomplete="off" placeholder="输入电子邮箱">
			<button id="account-reset-email" class="btn green">确定</button>
			<a href="#" class="cancel">取消</a>
		</div>

		<h2 class="form-title">修改密码</h2>
		
		<?php if($is_user_password_set): ?>
			<p class="status-text" id="password-text">*******<a href="#" class="set" for="set-password-cell">修改</a></p>
		<?php else: ?>
			<p class="status-text" id="password-text">尚未设置登录密码<a href="#" class="set first" for="set-password-cell">设置</a></p>
		<?php endif; ?>
		<div class="ctrl-cell ctrl-hide" for="password-text" id="set-password-cell">
			<input name="new_password" type="password" value="" id="new-password" placeholder="新密码" autocomplete="off">
			<input name="new_password_confirm" type="password" value="" id="new-password-confirm" placeholder="请再次输入新密码" autocomplete="off">
			<button id="update-password" class="btn green">确定</button>
			<a href="#" class="cancel">取消</a>
		</div>

		
	</form>
	
	<h2 class="form-title">绑定社交帐号</h2>

	<div class="sns-list">
		<?php if($wechat == ''){ ?>
			<div class="sns"><div class="icon"><i class="icon-wechat2"></i></div><div><a href="#" class="bind-wechat">绑定微信</a></div></div>
		<?php } else { ?>
			<div class="sns"><div class="icon"><i class="icon-wechat2"></i></div><div><?php echo $wechat; ?><a href="/user/sns/cancle/wechat" class="cancel-sns-bind" data-type="wechat">取消绑定</a></div></div>
		<?php } ?>
	</div>
</div>