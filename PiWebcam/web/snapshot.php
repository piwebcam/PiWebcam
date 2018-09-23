<?php
	// prevent to show up headers
	$_REQUEST["no_headers"] = 1;
	include "header.php";
	// generate the snapshot
	file_get_contents('http://localhost:8080/0/action/snapshot');
	sleep(3);
	
	// display the file
	$file = $env["DATA_DIR"]."/lastsnap.jpg";
	header("Content-Type: image/jpeg");
	header("Content-Length: ".filesize($file));
	echo file_get_contents($file);
?>