<?php if(!$is_pjax){ ?><div class="col-md-8 col-sm-8" id="detail"><?php } ?>
	<div class="form" data-key="<?php echo $section_unique_key; ?>">
		
		<h4>编辑：<?php echo $section['title']; ?></h4>
		
		<div class="form-body">
			
			<label>课节名称 <span>（必填）</span><i></i>
				<input type="text" name="section-name" value="<?php echo $section['title']; ?>">
			</label>
			
			<label>课节简介 <span>（必填）</span><i></i>
				<textarea name="section-introduction"><?php echo $section['main']; ?></textarea>
			</label>

			<label>
			
				<select name="status-select">
					<option value="published" <?php echo iif($section['status'] == 'PUBLISHED', 'selected = "selected"', ''); ?> >发布</option>
					<option value="draft" <?php echo iif($section['status'] == 'DRAFT', 'selected = "selected"', ''); ?> >草稿</option>
					<option value="closed" <?php echo iif($section['status'] == 'CLOSED', 'selected = "selected"', ''); ?> >关闭</option>
				</select>
				
			</label>
			<button class="btn btn-hilight" id="edit-a-section">提交</button>
			
		
		</div>
	
	</div>
<?php if(!$is_pjax){ ?></div><?php } ?>

<input type="hidden" id="course_unique_key" value="<?php echo $course_unique_key;?>">
<input type="hidden" id="if_set_price" value="<?php echo $if_set_price; ?>">

