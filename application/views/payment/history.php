<div class="card full-width padding">
	<div class="responsive-table-box">
		<?php if(count($history) > 0){ ?>
		<table class="buy-history">
			<thead>
				<tr>
					<th width="20%">日期</th>
					<th width="10%">金额</th>
					<th width="10%">类型</th>
					<th>内容</th>
				</tr>
			</thead>
			
			<tbody>
				<?php foreach($history AS $row){ ?>
				<?php
					$entity_guid	= $row['entity_guid'];
					$entity_subtype = $row['entity_subtype'];
					$amount			= $row['amount'];
					$time_created	= $row['time_created'];
					
					if($entity_subtype == strtoupper('course'))
					{
						$course 			= $this->my_lib->get_a_subtype_row('courses', (int)$entity_guid, array('title', 'unique_key'));
						$course_unique_key 	= $course['unique_key'];
						$title 				= $course['title'];
						$url 				= base_url("course/$course_unique_key/home");
						$subtype 			= '课程';
					}
					elseif($entity_subtype == strtoupper('section'))
					{
						$section 			= $this->my_lib->get_a_subtype_row('sections', (int)$entity_guid, array('title', 'unique_key', 'course_guid'));
						$course_guid 		= $section['course_guid'];
						$course_unique_key 	= $this->my_lib->get_unique_key_by_guid($course_guid);
						$title 				= $section['title'];
						$section_unique_key = $section['unique_key'];
						$url 				= base_url("course/section/$course_unique_key/$section_unique_key");
						$subtype 			= '课节';
					}
				?>
				<tr>
					<td><?php echo time_std_format($time_created); ?></td>
					<td>¥<?php echo $amount; ?></td>
					<td><?php echo $subtype; ?></td>
					<td><a target="_blank" href="<?php echo $url ?>"><?php echo $title; ?></a></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php } ?>
	</div>
	
	<!--
	<div class="pagination">
		<button class="btn bordered disabled prev">上一页</button>
		<div class="page">页数 1/3</div>
		<button class="btn bordered hilight next">下一页</button>
	</div>
	-->
	
</div>