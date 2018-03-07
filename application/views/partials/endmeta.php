<script src="/public/js/jquery-1.11.3.min.js"></script>
<script src="/public/bootstrap/js/bootstrap.min.js"></script>

<script src="/public/js/sea.js"></script>
<script src="/public/js/main.js"></script>

<!-- 临时加入需要同步顺序加载的 js begin -->
<script src="/public/js/redactor/redactor.js"></script>
<script src="/public/js/redactor/plugins/zlquote.js"></script>
<script src="/public/js/redactor/plugins/zlimage.js"></script>
<script src="/public/js/redactor/plugins/zlaudio.js"></script>
<script src="/public/js/redactor/plugins/zlvideo.js"></script>
<script src="/public/js/redactor/plugins/zlbreakline.js"></script>
<script src="/public/js/redactor/plugins/fullscreen.js"></script>
<!-- 临时加入需要同步顺序加载的 js end -->

<!-- 是否显示全局的消息提示 -->
<?php if($if_show){ ?>
<script>window.WN.show_global_message("<?php echo @$type; ?>", "<?php echo @$message; ?>")</script>
<?php } ?>