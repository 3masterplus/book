<header class="additional-nav">
	<div>
		<?php 
			foreach($secondary_menu AS $row) echo '<a href="'.$row['url'].'" '.iif($row['on'], 'class="on"', '').' >'.$row['text'].'</a>'; 
		?>
	</div>
</header>