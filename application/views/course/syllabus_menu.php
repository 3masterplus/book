<?php
	function display_nested_nodes($father_guid, $array){
		if(count($array) > 0){
			$count = 1;
			foreach($array AS $row){
				echo '<div class="node '.iif($row['visibility'], 'published', '').' drag" id="'.$row['unique_key'].'" data-weight="'.$row['weight'].'">';
					echo '<p>';
						echo '<span class="section-num"></span>';
						echo '<a href="" data-key="'.$row['unique_key'].'" class="section-text" data-pjax>'.$row['title'].'</a>';
						echo '<button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle">';
						echo '<i class="icon-circle-down"></i>';
						echo '</button>';
					echo '</p>';
					
					if(count($row['child']) > 0){
						display_nested_nodes($row['guid'], $row['child']);
					}
				
				echo '</div>';
				$count++;
			}
		}
	}	
?>

<nav class="third-nav col-md-4 col-sm-4">
	<div class="node-box">
	
		<?php
			$count = 1; 
			foreach($syllabus AS $row){
				echo '<section class = "open '.iif($row['visibility'], 'published', '').'" id="'.$row['unique_key'].'" data-guid="'.$row['guid'].'" data-weight="'.$row['weight'].'">';	
					echo '<p>';
						echo '<i class="icon-file-subtract"></i><span class="section-num"></span>';
						echo '<a href="" data-key="'.$row['unique_key'].'" class="section-text" data-pjax>'.$row['title'].'</a>';
						echo '<button type="button"><i class="icon-circle-down"></i></button>';
					echo '</p>';
					display_nested_nodes($row['guid'], $row['child']);		
				echo '</section>';
				$count++;
			}	
		?>
		
		<div class="add-node static">
			<a href="#">
				<i class="icon-plus"></i>
				添加新章节
			</a>
			<div class="create-node">
				<textarea></textarea>
				<button type="submit" class="btn btn-default">添加</button>
				<a class="cancle">取消</a>
			</div>
		</div>
		
	</div><!-- END OF node-box -->
</nav>