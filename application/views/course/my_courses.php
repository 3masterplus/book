<?php
	foreach($courses AS $row)
	{
		echo '<li><a href="'.base_url('course/'.$row['unique_key'].'/setting').'">'.$row['title'].'</a></li>';
	}
?>
