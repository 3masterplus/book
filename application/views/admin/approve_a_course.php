<div class="col-md-9 col-sm-9">
	<div class="form">
		<?php echo form_open(base_url("admin/approve_a_course/".$course['id'])); ?>
			<h4><a href="<?php echo $referrer; ?>">< 返回</a> <?php echo $course['title']; ?></h4>
			<div class="form-body">
				<label>
					课程名称 <span>（必填）</span>
					<input type="text" name="title" value="<?php echo $course['title']; ?>">
				</label>
				<label>
					课程简介 <span>（必填）</span>
					<textarea name="summary"><?php echo $course['summary']; ?></textarea>
				</label>
				<label>
					课程目标<span>（必填）</span>
					<textarea name="goal"><?php echo $course['objectives']; ?></textarea>
				</label>		
				<label>
					课程详情<span>（必填）</span>
					<textarea name="description"><?php echo $course['description']; ?></textarea>
				</label>
				<label>
					相关背景<span>（必填）</span>
					<textarea name="qualification"><?php echo $course['qualification']; ?></textarea>
				</label>
				
				<input type="hidden" name="approve_a_course" value="1">
				<button class="btn btn-hilight">通过</button>
				
			</div>
		</form>
	</div>
</div>