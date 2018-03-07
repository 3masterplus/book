<?php
	function flatten($array)
	{
		$new_array = array();
		if(sizeof($array) > 0)
		{
			foreach($array AS $row)
			{
				$new_array[] = array(
					'guid'			=> $row['guid'],
					'unique_key'	=> $row['unique_key'],
					'subtype_id'	=> $row['subtype_id'],
					'father_guid'	=> $row['father_guid'],
					'title'			=> $row['title']
				);
				
				if(count($row['child']) > 0)
				{
					$new_array = array_merge($new_array, flatten($row['child']));
				}
			}
		}
		return $new_array;
	}
	
	function pointer($array, $unique_key)
	{
		$count = 0;
		$pointer = '';
		foreach($array AS $row)
		{
			if($row['unique_key'] == $unique_key)
			{
				$pointer = $count;
				break;
			}
			$count++;
		}
		
		return $pointer;
	}
	
	function search_sub_array($array, $key)
	{
		foreach($array AS $row)
		{
			if($row['unique_key'] == $key AND count($row['child']) > 0)
			{
				return $row['child'];
			}
			elseif(count($row['child']) > 0)
			{
				search_sub_array($row['child'], $key);	
			}
		}
		
		return array();
	}
	
	//将多维数组转化为一维数组
	$flat_syllabus = flatten($syllabus);
	
	
	//计算出当前节点的位置
	
	$current 	= pointer($flat_syllabus, $unique_key);
	$pre 		= $current - 1;
	$next		= $current + 1;
	
	//计算前一个节点的相关数据
	if($pre >= 0)
	{
		$pre_title 	= $flat_syllabus[$pre]['title'];
		$pre_key 	= $flat_syllabus[$pre]['unique_key'];
		$pre_type	= $this->my_lib->get_subtype($flat_syllabus[$pre]['subtype_id']);
	
		if($pre_type == 'node')
		{
			$pre_section_guid = $this->my_lib->get_a_value('nodes', 'section_guid', array('unique_key' => $pre_key));
			$pre_section_unique_key = $this->my_lib->get_unique_key_by_guid($pre_section_guid);
			$pre_url = base_url('course/learn/'.$course_unique_key.'/'.$pre_section_unique_key.'/'.$pre_key);
		}
		elseif($pre_type == 'section')
		{
			$pre_url = base_url('course/learn/'.$course_unique_key.'/'.$pre_key);
		}
	}
	else
	{
		$pre_title = "到达顶部，返回课程首页";
		$pre_url = base_url('course/'.$course_unique_key.'/home');
	}
	
	//计算下一个节点的相关数据
	if($next < count($flat_syllabus))
	{
		$next_title	= $flat_syllabus[$next]['title'];
		$next_key	= $flat_syllabus[$next]['unique_key'];
		$next_type	= $this->my_lib->get_subtype($flat_syllabus[$next]['subtype_id']);
	
		if($next_type == 'node')
		{
			$next_section_guid = $this->my_lib->get_a_value('nodes', 'section_guid', array('unique_key' => $next_key));
			$next_section_unique_key = $this->my_lib->get_unique_key_by_guid($next_section_guid);
			$next_url = base_url('course/learn/'.$course_unique_key.'/'.$next_section_unique_key.'/'.$next_key);
		}
		elseif($next_type == 'section')
		{
			$next_url = base_url('course/learn/'.$course_unique_key.'/'.$next_key);
		}
	}
	else
	{
		$next_title = "到底底部，返回课程首页";
		$next_url = base_url('course/'.$course_unique_key.'/home');
	}
	
	$menu = search_sub_array($syllabus, $unique_key);
?>

<div class="row row-space-12 row-fullwidth container">
	<div id="mobile-menu" class="col-md-12 col-sm-12 mobile">
		<i class="icon-circle-right show-section-box"></i>
		<a href=""><i class="icon-home"></i></a>
	</div>
	
	<div id="menu" class="col-md-3 col-sm-3 section-box">
		<div>
			<div class="section-header">
				<a href="<?php echo $pre_url; ?>" title="<?php echo $pre_title; ?>"><i class="icon-rewind"></i>返回</a>
				<a href="/course/<?php echo $course_unique_key ?>/home" title="" class="home"><i class="icon-home"></i></a>
			</div>
			
			<section>
				<a href="#" title=""><?php echo $title; ?></a>
				<?php
					foreach($menu AS $row)
					{
						$url = base_url('course/learn/'.$course_unique_key.'/'.$row['section_unique_key'].'/'.$row['unique_key']);
				?>
						<div class="node"><a href="<?php echo $url; ?>"><?php echo $row['title']; ?></a><br/></div>
				<?php
					}
				?>
			</section>
		</div>
	</div>
	
	<div class="col-md-9 col-sm-9 content-box">
		<nav>
			<a href="<?php echo $pre_url; ?>" class="prev"><i class="icon-rewind"></i>返回上一级</a>
			<a href="<?php echo $next_url; ?>" class="next"><?php echo $next_title; ?><i class="icon-fast-forward"> </i></a>
		</nav>
		
		<div class="breadcrumb">
			<a href="#">首页</a>
			<?php 
				foreach($breadcrumb AS $row)
				{
					if($row['subtype_id'] == 2)
					{
						$url = base_url("course/".$row['unique_key']."/home");
					}
					elseif($row['subtype_id'] == 3)
					{
						$url = base_url("course/learn/".$course_unique_key."/".$row['unique_key']);
					}
					elseif($row['subtype_id'] == 4)
					{
						$breadcrumb_section_guid = $this->my_lib->get_a_value('nodes', 'section_guid', array('guid' => $row['guid']));
						$breadcrumb_section_unique_key = $this->my_lib->get_unique_key_by_guid($breadcrumb_section_guid);
						$url = base_url("course/learn/".$course_unique_key."/".$breadcrumb_section_unique_key.'/'.$row['unique_key']);
					}
					
					echo '<a href="'.$url.'">'.$row['title'].'</a>';
				}
			?>
			
			<a href="#" class="on"><?php echo $title; ?></a>
		</div>
			
		<h1 class="content-title"><?php echo $title; ?></h1>
		<div class="content">
			<!-- 节点内容 -->
			<?php echo $main; ?>
			
			<!-- 如果节点有考试，列出考试题 -->
			<?php if(count($quizzes) > 0){ ?>
				<ul class="quiz-list">
					<?php 
						foreach($quizzes AS $row)
						{
							$questions = $this->quiz_lib->get_questions($row['guid']);
							if(count($questions) > 0)
							{
								$quiz_url = base_url('course/quiz/'.$course_unique_key.'/'.$section_unique_key.'/'.$unique_key.'/'.$row['unique_key']);
								echo '<li><a href="'.$quiz_url.'" title="'.$row['title'].'"><i class="icon-pencil"></i>'.$row['title'].'</a></li>';
							}
						}
					?>
				</ul>
			<?php } ?>
			
			
			<!-- 如果该节点是“section”，列出该“section”下的下一级节点 -->
			<?php
				if(count($menu) > 0)
				{
					echo '<ul class="section-intro">';
					foreach($menu AS $row)
					{
						$url = '/course/learn/'.$course_unique_key.'/'.$row['section_unique_key'].'/'.$row['unique_key'];
						echo '<li><a href="'.$url.'">'.$row['title'].'</a></li>';
					}
					echo '</ul>';
				}
			?>
		</div>
		<nav>
			<a href="<?php echo $pre_url; ?>" class="prev"><i class="icon-rewind"></i>返回上一级</a>
			<a href="<?php echo $next_url; ?>" class="next"><?php echo $next_title; ?><i class="icon-fast-forward"> </i></a>
		</nav>
	</div>
</div>