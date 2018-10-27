<?php 
	include "header.php";
	// form submitted
	if(count($_REQUEST) > 0 && $_REQUEST["action"] === "configure_camera") {
		save_config(array('CAMERA_RESOLUTION','CAMERA_ROTATE','CAMERA_FRAMERATE','CAMERA_NIGHT_MODE','MOTION_RECORD_MOVIE','MOTION_THRESHOLD','MOTION_FRAMES','MOTION_EVENT_GAP','MOTION_PROCESS_MOVIE','AI_ENABLE','AI_TOKEN','AI_OBJECT','AI_THRESHOLD','AI_KEEP_NOT_FOUND'));
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
							<input type="hidden" name="action" value="configure_camera">
								<div class="row">
									<div class="col-lg-12">
										<div class="panel panel-default">
											<div class="panel-body">
												<h3>Camera</h3>
												<div class="form-group">
													<label>Resolution</label>
													<select name="CAMERA_RESOLUTION" class="form-control">
														<?php 
															$resolutions = array('','352x288','640x480','1024x768','1296x976','1640x1232');
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
													<p class="help-block">Maximum number of frames to be captured per second (<i>default: 2</i>)</p>
												</div>
												<div class="form-group">
													<label>Night Mode</label>
													<select name="CAMERA_NIGHT_MODE" class="form-control">
														<option value="ON" <?php if ($config["CAMERA_NIGHT_MODE"] === "ON") print " selected"?>>On</option>
														<option value="AUTO" <?php if ($config["CAMERA_NIGHT_MODE"] === "AUTO") print " selected"?>>Auto</option>
														<option value="OFF" <?php if ($config["CAMERA_NIGHT_MODE"] === "OFF") print " selected"?>>Off</option>
													</select>
													<p class="help-block">Enter night mode manually or automatically (when the value of a pin changes).</p>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-6">
										<div class="panel panel-default">
											<div class="panel-body">
												<h3>Motion detection</h3>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="MOTION_RECORD_MOVIE">
														<input value="1" name="MOTION_RECORD_MOVIE" type="checkbox"<?php if ($config["MOTION_RECORD_MOVIE"] === "1") print " checked"; ?>>Record movie
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
												<label>Motion processing</label>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="MOTION_PROCESS_MOVIE">
														<input value="1" name="MOTION_PROCESS_MOVIE" type="checkbox"<?php if ($config["MOTION_PROCESS_MOVIE"] === "1") print " checked"; ?>>Use video instead of picture
													<p class="help-block">When a motion is detected, process (e.g. notify and analyze) the entire video instead of just the snapshot picture (<i>default: unchecked</i>).
													</label>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="panel panel-default">
											<div class="panel-body">
												<h3>Image Analysis</h3>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="AI_ENABLE">
														<input value="1" name="AI_ENABLE" type="checkbox"<?php if ($config["AI_ENABLE"] === "1") print " checked"; ?>>Enable object detection
													<p class="help-block">If checked, upon a motion the image will be further analyzed with an artifical intelligence model to detect a specific object.
													</label>
												</div>
												<div class="form-group">
													<label>Token</label>
													<input name="AI_TOKEN" class="form-control" value="<?php print $config["AI_TOKEN"]?>">
													<p class="help-block">The token for authenticating against the cloud service. Click <a target="_blank" href="https://clarifai.com/developer/account/keys/create">HERE</a> for generating a the token.</p>
												</div>
												<div class="form-group">
													<label>Object</label>
													<input name="AI_OBJECT" class="form-control" value="<?php print $config["AI_OBJECT"]?>">
													<p class="help-block">The object that must be present in the image to trigger the motion/notification (e.g. people). Click <a target="_blank" href="https://clarifai.com/demo">HERE</a> for testing your image and check which objects are identified.</p>
												</div>
												<div class="form-group">
													<label>Threshold</label>
													<input name="AI_THRESHOLD" class="form-control" value="<?php print $config["AI_THRESHOLD"]?>">
													<p class="help-block">The probability threshold for the ojbect to trigger the notification (e.g. 0.9).</p>
												</div>
												<label>False positives</label>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="AI_KEEP_NOT_FOUND">
														<input value="1" name="AI_KEEP_NOT_FOUND" type="checkbox"<?php if ($config["AI_KEEP_NOT_FOUND"] === "1") print " checked"; ?>>Keep false positives
													<p class="help-block">If checked motion pictures and videos without the object will be kept (but not notified), otherwise they will be deleted as false positives (<i>default: unchecked</i>).
													</label>
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