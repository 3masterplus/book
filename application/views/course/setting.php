<div class="col-md-9 col-sm-9">
	<div class="form">
		<?php echo form_open(base_url("course/".$unique_key.'/setting')); ?>
			<h4><?php echo $heading; ?></h4>
			<div class="form-body">
				<label>课程名称<input type="text" name="title" value="<?php echo $title; ?>"></label>
				<label>课程简介<textarea name="main"><?php echo $main; ?></textarea></label>
				<label>课程详细介绍<textarea name="description"><?php echo $description; ?></textarea></label>
				<label>能学到什么？
					<div class="meditor" contenteditable="true" ><?php echo $objectives; ?></div>
					<textarea name="objectives" style="display:none;"><?php echo $objectives; ?></textarea>
				</label>
				<label>谁适合学习？
					<div class="meditor" contenteditable="true" ><?php echo $audience; ?></div>
					<textarea name="audience" style="display:none;"><?php echo $audience; ?></textarea>
				</label>
				<label>宣传视频地址<input type="text" name="video_url" value="<?php echo $video_url; ?>"></label>
				<label>课程主题
					<div>
						<label class="theme c0"><input name="theme[]" value="c0" id="theme" type="radio" <?php echo iif($theme =='c0', 'checked', '') ?> /></label>
						<label class="theme c1"><input name="theme[]" value="c1" id="theme" type="radio" <?php echo iif($theme =='c1', 'checked', '') ?> /></label>
						<label class="theme c2"><input name="theme[]" value="c2" id="theme" type="radio" <?php echo iif($theme =='c2', 'checked', '') ?> /></label>
						<label class="theme c3"><input name="theme[]" value="c3" id="theme" type="radio" <?php echo iif($theme =='c3', 'checked', '') ?> /></label>
						<label class="theme c4"><input name="theme[]" value="c4" id="theme" type="radio" <?php echo iif($theme =='c4', 'checked', '') ?> /></label>
					</div>
				</label>
				
				<input type="hidden" name="status" value="<?php echo $status; ?>">
				<input type="hidden" name="set_a_course" value="1">
				<input type="hidden" name="is_course_free" value="<?php echo $is_course_free; ?>">
				
				<button class="btn btn-hilight">提交</button>
			</div>
		</form>
	</div>
</div>