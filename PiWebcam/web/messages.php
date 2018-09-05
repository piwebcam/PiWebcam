<?php 
	$types = array("success","info","warning","danger");
	foreach ($types as $index => $type) {
		if (count($message[$type]) > 0) {
			print "<div class=\"alert alert-$type alert-dismissable\">";
			print '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
			foreach ($message[$type] as $index => $msg) {
				$msg = str_replace("\n","<br>",$msg);
				print $msg."<br>";
			}
			print '</div>';
		}
	} 
?>
