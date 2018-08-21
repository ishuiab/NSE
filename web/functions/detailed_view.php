<?php

#Function to get nav bar
function get_nav(){
	$ret =	"<table class='table' style='width:100%;'>
				<tr>
					<th class='info' style='text-align:center;'><a href='bulk.php'>Bulk Test</a></th>
				</tr>
			</table>";

	print $ret;		
}
?>