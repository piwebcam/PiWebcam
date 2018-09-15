<?php 
	include "header.php";
	// retrieve status information
	parse_text(run("status"),$status);
	include "messages.php";
?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-<?php print $env["MY_WEB_PANEL_STYLE"] ?>">
                        <div class="panel-heading">
                           <i class="fa fa-dashboard fa-fw"></i> Device Status
                        </div>
                        <div class="panel-body">
								<div class="row">
									<div class="col-lg-6">
										<div class="panel panel-default">
											<div class="panel-body">
												<h3>System</h3>
												<dl class="dl-horizontal">
													<dt>CPU Utilization</dt>
													<dd>
														<div class="progress">
															<div class="progress-bar progress-bar-default" role="progressbar" aria-valuenow="<?php print $status["SYSTEM_CPU"] ?>%" aria-valuemin="0" aria-valuemax="100" style="width: <?php print $status["SYSTEM_CPU"] ?>%;"><?php print $status["SYSTEM_CPU"] ?>%
															</div>
														</div>
													</dd>
													
													<dt>ROM Used</dt>
													<dd>
														<div class="progress">
															<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?php print $status["DISK_ROM"] ?>%" aria-valuemin="0" aria-valuemax="100" style="width: <?php print $status["DISK_ROM"] ?>%;"><?php print $status["DISK_ROM"] ?>%
															</div>
														</div>
													</dd>
													
													<dt>Cache Used</dt>
													<dd>
														<div class="progress">
															<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?php print $status["DISK_CACHE"] ?>%" aria-valuemin="0" aria-valuemax="100" style="width: <?php print $status["DISK_CACHE"] ?>%;"><?php print $status["DISK_CACHE"] ?>%
															</div>
														</div>
													</dd>
													
													<dt>Storage Used</dt>
													<dd>
														<div class="progress">
															<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?php print $status["DISK_DATA"] ?>%" aria-valuemin="0" aria-valuemax="100" style="width: <?php print $status["DISK_DATA"] ?>%;"><?php print $status["DISK_DATA"] ?>%
															</div>
														</div>
													</dd>
													
													<dt>Uptime</dt>
													<dd><?php print $status["SYSTEM_UPTIME"] ?></dd>
													
													<dt>Temperature</dt>
													<dd><?php print $status["SYSTEM_TEMP"] ?>°C</dd>
													
													<dt>Motion service</dt>
													<dd>
														<?php 
															print '<strong><p class="text-';
															if($status["SERVICE_MOTION"] === "1") print 'success">Running';
															else print 'danger">Not Running';
															print '</p></strong>';
														?>
													</dd>
												</dl>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="panel panel-default">
											<div class="panel-body">
												<h3>Network</h3>
												<dl class="dl-horizontal">
													<dt>WiFI Network</dt>
													<dd><?php print $status["WIFI_SSID"] ?></dd>
													
													<dt>WiFI Signal</dt>
													<dd>
														<div class="progress">
															<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php print $status["WIFI_LINK_QUALITY"] ?>%" aria-valuemin="0" aria-valuemax="100" style="width: <?php print $status["WIFI_LINK_QUALITY"] ?>%;">
															<?php 
															if (array_key_exists("WIFI_SIGNAL_LEVEL",$status)) print $status["WIFI_SIGNAL_LEVEL"]." dBm";
															?>
															</div>
														</div>
													</dd>
													
													<dt>IP Address</dt>
													<dd><?php print $status["NETWORK_IP"] ?></dd>
													
													<dt>Internet Connectivity</dt>
													<dd>
														<?php 
															print '<strong><p class="text-';
															if($status["NETWORK_INTERNET"] === "1") print 'success">Connected';
															else print 'danger">Not Connected';
															print '</p></strong>';
														?>
													</dd>
													
													<dt>Access Point service</dt>
													<dd>
														<?php 
															print '<p class="text-';
															if($status["SERVICE_AP"] === "1") print 'success">Running';
															else print 'warning">Not Running';
															print '</p>';
														?>
													</dd>
												</dl>
											</div>
										</div>
									</div>
								</div>
						</div>
					</div>
				</div>
			</div>
<?php 
	include "footer.php" 
?>