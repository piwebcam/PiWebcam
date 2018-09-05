<?php 
	include "header.php";
	include "messages.php";
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
										setInterval(function(){$("#video").attr("src", "video.php?"+new Date().getTime());},2000);
									</script>
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