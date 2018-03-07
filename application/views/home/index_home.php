<div class="full-container">
	<div class="hero">
		<div class="hero-area">
			<div class="info">
				<p>我们是一个分享型互联网平台，为大家提供各种精品公开课的学习，这是我们的介绍。</p>
				<a href="<?php echo base_url('user/register'); ?>" class="btn yellow">立即免费加入</a>
			</div>
			
			<div class="account-card">
				<div class="login-form form">
					<div class="padding-area">
						<form action="<?php echo base_url('user/login'); ?>" method="post" accept-charset="utf-8">
							<input name="email" type="text" id="email" placeholder="电子邮箱" value="" autocomplete="off">
							<input name="password" type="password" id="password" placeholder="密码" value="" autocomplete="off">
							<div class="ctrl-area">
								<input name="remember" type="checkbox" id="remember">
								<label for="remember">记住我</label><a href="<?php echo base_url('user/forgot_password'); ?>">忘记密码？</a>
							</div>
							
							<input type="hidden" name="login_a_user" value="1">
							<input type="hidden" name="referrer" value="">
							<button class="btn hilight">立即登录</button>
							
							<a class="link green" id="show-wechat-login"><i class="icon-wechat"></i>使用微信账号登录</a>
						</form>
					</div>
				</div>
				
				<div class="wechat-login">
					<div class="padding-area">
						<div class="form">
							<p class="note center">打开您手机上的微信，扫描下面的二维码</p>
							<div class="qrcode-area">
								<!-- <img src="../public/new/css/img/qrcode.png"></div><a href="#" title="使用邮箱注册" class="link">  -->
								<div id="qrcode"></div>
								<a href="#" class="link green" id="show-email-login"><i class="icon-mail"></i>或使用邮箱注册</a>
							</div>
						</div>
					</div>
				</div><!-- END OF wechat-login -->
			</div><!-- END OF account-card -->
		</div><!-- END OF hero-area -->
	</div><!--END OF hero -->
	
	<div class="course-hilight">
		<div class="course-area">
			<h2>全部精彩课程</h2>
			<ul class="course-list">
				<?php foreach($courses AS $row){ ?>
				<li>
					<a href="#" class="course-icon"><img src="/public/new/css/img/icon01.png"></a>
					<h3><?php echo $row['title']; ?></h3>
					<p><?php echo $row['main']; ?></p>
					<a href="#" class="btn green">查看详情</a>
					<div class="course-info">
						<a href="#" class="owner">
							<img src="<?php echo $this->my_lib->get_user_avatar($row['owner_unique_key']); ?>">
							<span><?php echo $row['owner_username']; ?></span>
						</a>
						
							<?php
								if($row['is_course_free'])
								{
									echo '<strong class="price free">免费</strong>';
								}
								else
								{
									if($row['fee_policy'] == 'BY_COURSE')
									{
										echo '<strong class="price by-course">'.$row['by_course_fee'].'</strong>';
									}
									elseif($row['fee_policy'] == 'BY_BOTH' OR $row['fee_policy'] == 'BY_SECTION')
									{
										echo '<strong class="price fee">'.$this->course_lib->get_lowest_section_price($row['guid']).'</strong>';
									}
								}
								
							?>
						
					</div>
				</li>
				<?php } ?>
			</ul>
		</div>
	</div>
	

        <div class="why-us">
          <div class="why-area">
            <h2>为何学在知乐？</h2>
            <ul class="course-list">
              <li>
                <img src="/public/new/css/img/i1.png">
                <strong>灵活的收费机制</strong>
                <p>本课程在介绍C#语言及面向对象的程序设计基本介绍各种常见的信息的处理方法。</p>
              </li>
              <li>
                <img src="/public/new/css/img/i2.png">
                <strong>学习社区</strong>
                <p>iOS 上只预装了一种中文字体，质素不如人意。</p>
              </li>
              <li>
                <img src="/public/new/css/img/i3.png">
                <strong>知识点管理</strong>
                <p>本课程在介绍C#语言及面向对象的程序设计基本介绍各种常见的信息的处理方法。</p>
              </li>
              <li>
                <img src="/public/new/css/img/i4.png">
                <strong>多种学习模式</strong>
                <p>为此我们在字节社中内嵌了「信黑体」，由拥有二十多年字体设计经验。</p>
              </li>
            </ul>
          </div>
        </div>

        <div class="sns">
          <div class="sns-area">
            <h2>关注微信公众账号</h2>
            <img src="/public/new/css/img/wx_qrcode.jpg">
          </div>
        </div>
    </div>

      