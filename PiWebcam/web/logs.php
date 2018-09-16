<?php 
	include "header.php";
	// get the logs
	$logs = run("show_logs");
	include "messages.php";
?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-<?php print $env["MY_WEB_PANEL_STYLE"] ?>">
                        <div class="panel-heading">
                           <i class="fa fa-th-list fa-fw"></i> Logs
                        </div>
                        <div class="panel-body">
								<div class="row">
									<div class="col-lg-12">
										<div class="panel panel-default">
											<div class="panel-body">
												<div class="table-responsive">
												<table width="100%" class="table nowrap cell-border compact table-striped table-hover" id="table">
													<thead>
														<tr>
															<th>Date</th>
															<th>Time</th>
															<th>IP</th>
															<th>Log Entry</th>
														</tr>
													</thead>
													<tbody>
													<?php
														// clean up and prepare the entry
														$logs = array_reverse(explode("\n", $logs));
														// print out all the entries
														foreach ($logs as $index => $log) {
															preg_match('\'\\[([^\\]]+)\\]\\[([^\\]]+)\\] (.+)$\'',$log,$matches);
															if (count($matches) != 4) continue;
															$timestamp = $matches[1];
															$remote_ip = $matches[2];
															$log_entry = $matches[3];
															if ($timestamp == "PiWebcam") {
																// v1.0 log file
																$timestamp = $remote_ip;
																$remote_ip = "";
															}
															$timestamp = explode(" ", $timestamp);
															echo "<tr class=\"odd\">\n";
															echo "<td>".$timestamp[0]."</td>\n";
															echo "<td>".$timestamp[1]."</td>\n";
															echo "<td>".$remote_ip."</td>\n";
															echo "<td>".htmlspecialchars($log_entry)."</td>\n";
															echo "</tr>\n";
														}
														$load_table = <<<EOF
															<script>
															$(document).ready(function() {
																$('#table').DataTable({
																	"order": [],
																	"pageLength": 25
																});
															});
															</script>
EOF;
														array_push($footer,$load_table);
													?>
													</tbody>
												</table>
												</div>
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