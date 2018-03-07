<?php 
	$is_login = $this->_is_login;
	if(!isset($is_mobile_sidebar_set)) $is_mobile_sidebar_set = true;
?>

<nav class="sidebar">
	
	<?php
		if($is_login){
			echo '<a href="'.base_url('dashboard').'" title="'.$this->config->item('dashboard').'" class="dashboard '.iif($highlight == 'dashboard', 'on', '').'">';
			echo '<i class="icon-indicator"></i>';
			echo '<span>'.$this->config->item('dashboard').'</span>';
			echo '</a>';
		}else{
			echo '<a href="'.base_url('home').'" title="'.$this->config->item('home').'" class="home">';
			echo '<i class="icon-home"></i><span>'.$this->config->item('home').'</span>';
			echo '</a>';
		}
		
		echo '<a href="'.base_url('course').'"title="'.$this->config->item('all_courses').'" class="course-page '.iif($highlight == 'course', 'on', '').'">';
		echo '<i class="icon-book"></i><span>'.$this->config->item('all_courses').'</span></a>';
		
		if($is_login){
			echo '<a href="'.base_url('user/account/edit').'" title="'.$this->config->item('setting').'" class="setting '.iif($highlight == 'setting', 'on', '').'">';
			echo '<i class="icon-setting2"></i><span>'.$this->config->item('setting').'</span></a>';
		}
	?>
</nav>


<?php if($is_mobile_sidebar_set){ ?>

<nav class="mobile-sidebar">
	<?php 
		if(!$is_login) echo '<a href="'.base_url().'"><i class="icon-home2"></i>'.$this->config->item('home').'</a>';
		echo '<a '.iif($highlight == 'course', 'class="on"', '').' href="'.base_url('course').'"><i class="icon-course"></i>'.$this->config->item('all_courses').'</a>';
	?>
	
	<?php
		if($is_login){
			echo '<a '.iif($highlight == 'dashboard', 'class="on"', '').' href="'.base_url('dashboard').'" ><i class="icon-indicator dashboard"></i>'.$this->config->item('dashboard').'</a>';
			echo '<a '.iif($highlight == 'notification', 'class="on"', '').' href="'.base_url('notification').'"><i class="icon-bell2"></i>'.$this->config->item('notification').'</a>';
			echo '<a href="'.base_url('setting').'" class="smart-setting"><i class="icon-setting2"></i>'.$this->config->item('setting').'</a>';
		}else{
			echo '<a href="'.$this->config->item('wechat_redirect_url').'" class="smart-login"><i class="icon-mine"></i>'.$this->config->item('account').'</a>';
		}
	?>
</nav>

<?php } ?>