<?php 
	include "header.php";
	// form submitted
	if(count($_REQUEST) > 0) {
		if (array_key_exists("RESOLUTION",$_REQUEST)) run("set_resolution '".$_REQUEST["RESOLUTION"]."'");
		if (array_key_exists("ROTATE",$_REQUEST)) run("set_rotate '".$_REQUEST["ROTATE"]."'");
		if (array_key_exists("DISABLE_PICTURE",$_REQUEST)) run("set_disable_picture '".$_REQUEST["DISABLE_PICTURE"]."'");
		if (array_key_exists("DISABLE_MOVIE",$_REQUEST)) run("set_disable_movie '".$_REQUEST["DISABLE_MOVIE"]."'");
		if (array_key_exists("MOTION_THRESHOLD",$_REQUEST)) run("set_motion_threshold '".$_REQUEST["MOTION_THRESHOLD"]."'");
		if (array_key_exists("MOTION_FRAMES",$_REQUEST)) run("set_motion_frames '".$_REQUEST["MOTION_FRAMES"]."'");
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
												<h3>Image</h3>
												<div class="form-group">
													<label>Resolution</label>
													<select name="RESOLUTION" class="form-control">
														<?php 
															$resolutions = array('','640x480','1024x768','1296x976','1640x1232');
															foreach ($resolutions as $index => $resolution) {
																print "<option value=\"".$resolution."\"";
																if ($config["RESOLUTION"] === $resolution) print " selected"; 
																print ">$resolution</option>\n";
															}
														?>
													</select>
													<p class="help-block">Select the resolution for picture/video (<i>default: 640x480</i>)</p>
												</div>
												<div class="form-group">
													<label>Rotate</label>
													<select name="ROTATE" class="form-control">
														<?php
															$rotates = array('','0','90','180','270');
															foreach ($rotates as $index => $rotate) {
																print "<option value=\"".$rotate."\"";
																if ($config["ROTATE"] === $rotate) print " selected"; 
																print ">$rotate</option>\n";
															}
														?>
													</select>
													<p class="help-block">Rotate image this number of degrees (<i>default: 0</i>)</p>
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
														<input type='hidden' value="0" name="DISABLE_PICTURE">
														<input value="1" name="DISABLE_PICTURE" type="checkbox"<?php if ($config["DISABLE_PICTURE"] === "1") print " checked"; ?>>Disable picture
													</label>
												</div>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="DISABLE_MOVIE">
														<input value="1" name="DISABLE_MOVIE" type="checkbox"<?php if ($config["DISABLE_MOVIE"] === "1") print " checked"; ?>>Disable video
													</label>
												</div>
												<p class="help-block">Select to prevent saving an image/video when motion is detected (<i>default: unchecked</i>)</p>
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