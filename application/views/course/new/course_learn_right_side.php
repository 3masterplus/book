<?php
	if(!$is_a_node_single_attached){
		function display_nested_nodes($array, $data){
			foreach($array AS $arr){
				$course_unique_key 	= $data['course_unique_key'];
				$section_unique_key = $data['section_unique_key'];
				$node_unique_key 	= $data['node_unique_key'];
			
				$base_url = base_url("course/node/".$course_unique_key."/".$section_unique_key."/".$arr['unique_key']);
				
				echo '<div class="node '.iif($arr['if_node_learned'], 'finished', '').' '.iif(sizeof($arr['child']) > 0, 'has-child', '').' '.iif($node_unique_key == $arr['unique_key'], 'current', '').' ">';
				echo '<div class="cont">';
				echo '<a href="'.$base_url.'" title="'.$arr['title'].'"><i class="icon"></i><span>'.$arr['title'].'</span></a>';
				echo '</div>';
			
				if(sizeof($arr['child']) > 0){display_nested_nodes($arr['child'], $data);}
			
				echo '</div>';
			}
		}
	}
?>

<div class="side-area">

	<?php if(!$is_a_node_single_attached){ ?>
	
		<div class="gray-box">
			<h2>
				<a href="<?php echo base_url("course/$course_unique_key/home#$section_unique_key"); ?>"><?php echo '第'.$section_index_number.'课：'.$section_title; ?></a>
				<?php if($if_openmark_showed) echo '<span>'.$this->config->item('openmark').'</span>'; ?>
			</h2>
			
			<?php
			
				echo '<div class="node-tree expanded">';
					
					foreach($section_tree AS $row){
						echo '<section class="'.iif($row['if_node_learned'], 'finished', '').' '.iif(sizeof($row['child']) > 0, 'has-child', '').' '.iif($row['unique_key'] == $node_unique_key, 'current', '').' ">';
					
						echo '<div class="cont">';
							echo '<a href="'.base_url('course/node/'.$course_unique_key.'/'.$section_unique_key.'/'.$row['unique_key']).'" title="'.$row['title'].'">';
								echo '<i class="icon"></i>';
								echo '<span>'.$row['title'].'</span>';
							echo '</a>';
						echo '</div>';
						
						if(sizeof($row['child']) > 0){
							$data['course_unique_key'] 	= $course_unique_key;
							$data['section_unique_key'] = $section_unique_key;
							$data['node_unique_key']	= $node_unique_key;
							display_nested_nodes($row['child'], $data);
						}
						
						echo '</section>';
					}
					
				echo '</div>';
				echo '<a href="'.base_url("course/$course_unique_key/home#$section_unique_key").'" class="link green" style="margin: 10px 0;"><i class="icon-angle-left2"></i>返回课程首页</a>';
			
			?>
		</div>
	
	<?php
		} else {
			echo '<a class="link no-margin" href="'.base_url("course/$course_unique_key/home#$section_unique_key").'"><i class="icon-angle-left2"></i>返回课程首页</a>';
		}
	?>
</div>