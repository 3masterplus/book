<div class="col-md-9 col-sm-9">
	<div class="form">
		<?php echo form_open(base_url("course/create_a_course")); ?>
			<h4>申请开课</h4>
			<div class="form-body">
				<label>
					课程名称 <span>（必填）</span><i></i>
					<input type="text" name="title" value="<?php echo $title; ?>">
				</label>
				<label>
					课程简介 <span>（必填）</span><i></i>
					<textarea name="summary"><?php echo $summary; ?></textarea>
				</label>
				<label>
					<!-- <button>unorderlist</button> -->
					课程目标<span>（必填）</span><i></i>
					<div class="meditor" contenteditable="true" ></div>
					<textarea name="objectives" style="display:none;"></textarea>
				</label>		
				<label>
					课程详情<span>（必填）</span><i></i>
					<textarea name="description"><?php echo $description; ?></textarea>
				</label>
				<label>
					相关背景<span>（必填）</span><i>说明您在相关知识领域的经验、背景等资格</i>
					<textarea name="qualification"><?php echo $qualification; ?></textarea>
				</label>
					
				<input type="hidden" name="create_a_course" value="1">
				<button class="btn btn-hilight">提交</button>
			</div>
		</form>
	</div>
</div>

