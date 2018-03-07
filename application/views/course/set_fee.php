<?php
	/*
		模式一：（1）用户以连载方式发布课程；（2）课程免费；（3）选择了访问权限
		模式二：（1）用户以连载方式发布课程；（2）课程不免费
		模式三：（1）用户以整体方式发布课程；（2）课程免费；（3）选择了访问权限
		模式四：（1) 用户以整体方式发布课程；（2）课程不免费；（3）选择了整体收费；（4）课程价格
		模式五：（1）用户以整体方式发布课程；（2）课程不免费；（3）选择了按课节收费
		模式六：（1）用户以整体方式发布课程；（2）课程不免费；（3）选择两种收费模式都可以
	*/

	//判断模式
	function check_fee_mode($publish_option, $is_course_free, $fee_policy, $by_course_fee, $access)
	{
		if($publish_option == 'BY_SECTION' AND $is_course_free AND $access != NULL)
		{
			return 1;
		}
		elseif($publish_option == 'BY_SECTION' AND !$is_course_free)
		{
			return 2;
		}
		elseif($publish_option == 'BY_COURSE' AND $is_course_free AND $access != NULL)
		{
			return 3;
		}
		elseif($publish_option == 'BY_COURSE' AND !$is_course_free AND $fee_policy == 'BY_COURSE' AND $by_course_fee > 0)
		{
			return 4;
		}
		elseif($publish_option == 'BY_COURSE' AND !$is_course_free AND $fee_policy == 'BY_SECTION')
		{
			return 5;
		}
		elseif($publish_option == 'BY_COURSE' AND !$is_course_free AND $fee_policy == 'BY_BOTH')
		{
			return 6;
		}
		else
		{
			return 0;
		}
	}
	
	$mode = check_fee_mode($publish_option, $is_course_free, $fee_policy, $by_course_fee, $access);
	
?>


<div class="col-md-9 col-sm-9">
	<div class="form form-inline">
		<?php echo form_open(base_url("course/".$unique_key.'/setting/fee')); ?>
			<h4><?php echo $heading; ?></h4>
			<div class="form-body">
				<div class="row bottom-space-4">
					<label class="col-sm-2" for="fee-type">选择发布模式</label>
					<select name="publish_option" class="form-control input-normal">
						<option <?php echo iif($publish_option == 'BY_SECTION', 'selected', ''); ?> value="BY_SECTION">连载发布</option>
						<option <?php echo iif($publish_option == 'BY_COURSE', 'selected', ''); ?> value="BY_COURSE">整课发布</option>
					</select>
				</div>

				<div class="row bottom-space-4">
					<label for="is_course_free" class="col-sm-2">是否课程免费</label>
					<div class="col-sm-3">
						<label><input type="radio" name="is_course_free" value="1" <?php echo iif($is_course_free, 'checked', ''); ?>>免费</label>
						<label><input type="radio" name="is_course_free" value="0" <?php echo iif(!$is_course_free, 'checked', ''); ?>>收费</label>
					</div>
				</div>

				<div class="row bottom-space-4 type-box fee-policy-box <?php echo iif($mode == 4 OR $mode == 5 OR $mode == 6 OR $mode == 2, 'showit', ''); ?>">
					<label for="fee_policy" class="col-sm-2">收费模式</label>
					<select <?php if($mode == 2) echo "disabled = 'true'"; ?> name="fee_policy" class="form-control input-normal">
						<option <?php echo iif($fee_policy == 'BY_COURSE', 'SELECTED', ''); ?> value="BY_COURSE">整课收费</option>
						<option <?php echo iif($fee_policy == 'BY_SECTION', 'SELECTED', ''); ?> value="BY_SECTION">按章节收费</option>
						<option <?php echo iif($fee_policy == 'BY_BOTH', 'SELECTED', ''); ?> value="BY_BOTH">两种方式并存</option>
					</select>
				</div>
				
				<?php if($publish_option == 'BY_SECTION'){ ?>
				
				<div class="row bottom-space-4 type-box completed-box <?php echo iif($mode == 1 OR $mode == 2, 'showit', ''); ?>">
					<label for="fee_policy" class="col-sm-2">是否完结</label>
					<select name="by_section_is_completed" class="form-control input-normal">
						<option <?php echo iif($by_section_is_completed == 0, 'SELECTED', ''); ?> value="0">连载中</option>
						<option <?php echo iif($by_section_is_completed == 1, 'SELECTED', ''); ?> value="1">已完结</option>
					</select>
				</div>
				
				<?php } ?>
				
				<div class="row bottom-space-4 type-box by-course-fee-box <?php echo iif($mode == 4 OR $mode == 6, 'showit', ''); ?>">
					<label for="by_course_fee" class="col-sm-2">价格</label>
					<div class="col-sm-3"><input type="text" id="by_course_fee" name="by_course_fee" value="<?php echo $by_course_fee; ?>"> 元</div>
				</div>

				<div class="row bottom-space-4 type-box access-box <?php echo iif($mode == 1 OR $mode == 3, 'showit', ''); ?>">
					<label for="username" class="col-sm-2">开放模式</label>
					<select disabled = "disabled" name="access" class="form-control input-normal">
						<option value="all">全部用户可见</option>
						<option value="registered">注册用户可见</option>
						<option value="follower">加入用户可见</option>
					</select>
				</div>
				
				<input type="hidden" name="title" value="<?php echo $title; ?>">
				<input type="hidden" name="status" value="<?php echo $status; ?>">
				<input type="hidden" name="set_fee" value="1">
				<input type="hidden" name="course_unique_key" value="<?php echo $unique_key ?>">
				
				<button class="btn btn-hilight" id="set-fee">提交</button>
			</div>
		</form>
	</div>
</div>