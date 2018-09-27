<?php 
	include "header.php";
	include "messages.php";
	// form submitted
	if(count($_REQUEST) > 0 && $_REQUEST["action"] === "night_mode") {
		run("night_mode ".$_REQUEST["night_mode"]);
	}
	$night_mode_status = run("night_mode_status");
?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-<?php print $env["MY_WEB_PANEL_STYLE"] ?>">
                        <div class="panel-heading">
                           <i class="fa fa-video-camera fa-fw"></i> View
                        </div>
                        <div class="panel-body">
                            <div class="row">
								<div class="col-lg-10 col-lg-offset-1">
									<center><img id="video" src="video.php" border="0" width="100%"></center>
									<script type="text/javascript">
										refresh = setInterval(function(){$("#video").attr("src", "video.php?"+new Date().getTime());},3000);
									</script>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2 col-lg-offset-5">
								<br>
								<center>
									<form method="POST" role="form">
									<input type="hidden" name="action" value="night_mode">
										<div class="btn-group" role="group">
											<button name="night_mode" value="0" type="submit" class="btn btn-sm btn-<?php echo $night_mode_status == 0 ?  "primary" : "default" ?>"><i class="fa fa-sun-o"></i></button>
											<button name="night_mode" value="1" type="submit" class="btn btn-sm btn-<?php echo $night_mode_status == 1 ?  "primary" : "default" ?>"><i class="fa fa-moon-o"></i></button>
										</div>
									</form>
								</center>
								</div>
								<div class="col-lg-2 col-lg-offset-5">
								<br>
									<center>
									<p class="help-block">Refresh (seconds)</p>
									<div class="input-group">
										<span class="input-group-btn">
											<button type="button" class="btn btn-default btn-number" onclick='
											if (parseInt($("#refresh_interval").val()) <= 2) return;
											clearInterval(refresh);
											$("#refresh_interval").val(parseInt($("#refresh_interval").val()-1));
											refresh = setInterval(function(){$("#video").attr("src", "video.php?"+new Date().getTime());},parseInt($("#refresh_interval").val())*1000);
											'>
												<span class="fa fa-minus"></span>
											</button>
										</span>
										<input id="refresh_interval" type="text" class="form-control input-number" value="3">
										<span class="input-group-btn">
											<button type="button" class="btn btn-default btn-number" onclick='
											clearInterval(refresh);
											$("#refresh_interval").val(parseInt($("#refresh_interval").val())+1);
											refresh = setInterval(function(){$("#video").attr("src", "video.php?"+new Date().getTime());},parseInt($("#refresh_interval").val())*1000);
											'>
												<span class="fa fa-plus"></span>
											</button>
										</span>
									</div>
									</center>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-4 col-lg-offset-4">
									<br>
									<center><button onclick="window.open('snapshot.php','_blank');" class="btn btn-outline btn-primary">Snapshot</button></center>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
<?php 
	include "footer.php" 
?>