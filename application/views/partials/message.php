<?php if(@$if_show){ ?>

<!--
	通过 php 调用 js 提示框 ，可在 php 判断语句中加入以下代码 
	如有必要，此部分可单独作为一个 partial 供所有 layout 使用
		
	参数说明：
		type: success/warning/info/error
		text: 提示信息的文本
		is_fixed: fixed/空 是否滚动跟随，暂未实现
		auto_close: true/false 是否2秒后自动关闭
-->

<script>
	var button_option = null;
	
	<?php if(@$if_show_button){ ?>
	
	//如果需要显示按钮
	button_option = {
		text: '<?php echo @$button_text; ?>',
		url: '<?php echo @$button_url ?>',
		id: 'update-account-info'
	}
	
	<?php } ?>
	
	ZL.show_global_message('<?php echo @$type ?>', '<?php echo @$message; ?>', button_option);
</script>

<?php } ?>