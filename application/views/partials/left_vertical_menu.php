<nav class="third-nav col-md-3 col-sm-3">
	<ul>
		<?php foreach($vertical_menu AS $row) echo '<li><a href="'.$row['url'].'" '.iif($row['on'], 'class="on"', '').' >'.$row['text'].'</a></li>'; ?>
	</ul>
</nav>