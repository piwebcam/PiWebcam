<?php 
	include "header.php";
	// form submitted
	if(count($_REQUEST) > 0) {
		if (array_key_exists("CAMERA_RESOLUTION",$_REQUEST)) run("set CAMERA_RESOLUTION '".$_REQUEST["CAMERA_RESOLUTION"]."'");
		if (array_key_exists("CAMERA_ROTATE",$_REQUEST)) run("set CAMERA_ROTATE '".$_REQUEST["CAMERA_ROTATE"]."'");
		if (array_key_exists("CAMERA_FRAMERATE",$_REQUEST)) run("set CAMERA_FRAMERATE '".$_REQUEST["CAMERA_FRAMERATE"]."'");
		
		if (array_key_exists("MOTION_MOVIE",$_REQUEST)) run("set MOTION_MOVIE '".$_REQUEST["MOTION_MOVIE"]."'");
		if (array_key_exists("MOTION_THRESHOLD",$_REQUEST)) run("set MOTION_THRESHOLD '".$_REQUEST["MOTION_THRESHOLD"]."'");
		if (array_key_exists("MOTION_FRAMES",$_REQUEST)) run("set MOTION_FRAMES '".$_REQUEST["MOTION_FRAMES"]."'");
		if (array_key_exists("MOTION_EVENT_GAP",$_REQUEST)) run("set MOTION_EVENT_GAP '".$_REQUEST["MOTION_EVENT_GAP"]."'");
		run("configure_camera");
		load_config();
		array_push($message["success"],"Configuration applied successfully");
	}
	include "messages.php";
?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-<?php print $env["MY_WEB_PANEL_STYLE"] ?>">
                        <div class="panel-heading">
                           <i class="fa fa-wrench fa-fw"></i> Camera settings
                        </div>
                        <div class="panel-body">
							<form id="form" method="POST" role="form">
								<div class="row">
									<div class="col-lg-6">
										<div class="panel panel-default">
											<div class="panel-body">
												<h3>Camera</h3>
												<div class="form-group">
													<label>Resolution</label>
													<select name="CAMERA_RESOLUTION" class="form-control">
														<?php 
															$resolutions = array('','640x480','1024x768','1296x976','1640x1232');
															foreach ($resolutions as $index => $resolution) {
																print "<option value=\"".$resolution."\"";
																if ($config["CAMERA_RESOLUTION"] === $resolution) print " selected"; 
																print ">$resolution</option>\n";
															}
														?>
													</select>
													<p class="help-block">Select the resolution for picture/video (<i>default: 640x480</i>)</p>
												</div>
												<div class="form-group">
													<label>Rotate</label>
													<select name="CAMERA_ROTATE" class="form-control">
														<?php
															$rotates = array('','0','90','180','270');
															foreach ($rotates as $index => $rotate) {
																print "<option value=\"".$rotate."\"";
																if ($config["CAMERA_ROTATE"] === $rotate) print " selected"; 
																print ">$rotate</option>\n";
															}
														?>
													</select>
													<p class="help-block">Rotate image this number of degrees (<i>default: 0</i>)</p>
												</div>
												<div class="form-group">
													<label>Framerate</label>
													<input name="CAMERA_FRAMERATE" class="form-control" value="<?php print $config["CAMERA_FRAMERATE"]; ?>">
													<p class="help-block">Maximum number of frames to be captured per second (<i>default: 5</i>)</p>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="panel panel-default">
											<div class="panel-body">
												<h3>Motion detection</h3>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="MOTION_MOVIE">
														<input value="1" name="MOTION_MOVIE" type="checkbox"<?php if ($config["MOTION_MOVIE"] === "1") print " checked"; ?>>Record movie
													</label>
												</div>
												<p class="help-block">If checked a movie (in addition to a picture) will be recorded upon motion (<i>default: checked</i>)</p>
												<div class="form-group">
													<label>Threshold</label>
													<input name="MOTION_THRESHOLD" class="form-control" value="<?php print $config["MOTION_THRESHOLD"]; ?>">
													<p class="help-block">Number of changed pixels that triggers motion detection (<i>default: 1500</i>)</p>
												</div>
												<div class="form-group">
													<label>Minimum motion frames</label>
													<input name="MOTION_FRAMES" class="form-control" value="<?php print $config["MOTION_FRAMES"]; ?>">
													<p class="help-block">The minimum number of frames in a row to be considered true motion (<i>default: 1</i>)</p>
												</div>
												<div class="form-group">
													<label>Event gap</label>
													<input name="MOTION_EVENT_GAP" class="form-control" value="<?php print $config["MOTION_EVENT_GAP"]; ?>">
													<p class="help-block">Seconds of no motion that triggers the end of an event (<i>default: 60</i>)</p>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<button id="button" type="submit" class="pull-right btn btn-primary" onclick='$("#button").addClass("disabled");'>Apply settings</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
<?php 
	include "footer.php" 
?>