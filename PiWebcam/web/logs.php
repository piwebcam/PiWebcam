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
												<table width="100%" class="table table-striped table-bordered table-hover" id="table">
													<thead>
														<tr>
															<th>Timestamp</th>
															<th>Log Entry</th>
														</tr>
													</thead>
													<tbody>
													<?php
														// clean up and prepare the entry
														$logs = str_replace("[PiWebcam]","",$logs);
														$logs = explode("\n", $logs);
														// print out all the entries
														foreach ($logs as $index => $log) {
															$log = explode("] ", $log);
															$log[0] = str_replace("[","",$log[0]);
															echo "<tr class=\"odd\">\n";
															echo "<td>".$log[0]."</td>\n";
															echo "<td>".$log[1]."</td>\n";
															echo "</tr>\n";
														}
														$load_table = <<<EOF
															<script>
															$(document).ready(function() {
																$('#table').DataTable({
																	responsive: true,
																	"order": [[ 0, "desc" ]]
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
<?php 
	include "footer.php" 
?>