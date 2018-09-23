<?php
	// prevent the browser from caching the image
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	// connect to motion stream server
	$f = fopen("http://localhost:8081/stream.mjpg", 'r');
	if($f) {
		// return a jpg
		header("Content-type: image/jpeg");
		$r = null;
		while(substr_count($r, "\xFF\xD8") != 2) $r .= fread($f, 512);
		$start = strpos($r, "\xFF\xD8");
		$end = strpos($r, "\xFF\xD9", $start)+2;
		$frame = substr($r, $start, $end-$start);
		fclose($f);
		echo $frame;
	} else {
		$file = "images/loading.gif";
		header("Content-type: image/gif");
		header("Content-Length: ".filesize($file));
		echo file_get_contents($file);
	}
?>