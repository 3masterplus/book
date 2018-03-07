<div class="row row-space-8">
	<div class="col-md-4 col-lg-4 col-center">
		<div class="panel register">
			<?php echo form_open(base_url("user/reset_password/$user_unique_key/$encrypted_str")); ?>
				<div class="panel-heading"><h4>重置密码</h4></div>
				<div class="panel-body">
					<?php if($error_str != ''){ ?>
					
					<div role="alert" class="alert alert-danger alert-dismissible">
						<button type="button" data-dismiss="alert" aria-label="Close" class="close"><span aria-hidden="true">&times;</span></button>
						<?php echo $error_str; ?>
					</div>
					
					<?php } ?>
					
					<div class="form-group">
						<div class="input-group">
							<input type="password" placeholder="密码" aria-describedby="" name="password" class="form-control"><span class="input-group-addon"><i class="icon-unlock"></i></span>
						</div>
					</div>
					
					<div class="form-group">
						<div class="input-group">
							<input type="password" placeholder="确认密码" aria-describedby="" name="password_confirm" class="form-control"><span class="input-group-addon"><i class="icon-unlock"></i></span>
						</div>
					</div>
					
					<input type="hidden" name="reset_password" value="1">
					
					<button type="submit" class="btn btn-block btn-primary btn-large">重置</button>
					
				</div>
				<hr>
				<div class="panel-body">如果遇到任何问题，请和我们联系</a></div>
			</form>
		</div>
	</div>
</div>