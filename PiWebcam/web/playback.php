<?php 
	include "header.php";
	include "messages.php";
?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-<?php print $env["MY_WEB_PANEL_STYLE"] ?>">
                        <div class="panel-heading">
                           <i class="fa fa-play-circle-o fa-fw"></i> Playback
                        </div>
                        <div class="panel-body">
                            <div class="row">
								<div class="col-lg-12">
									<iframe height="400" class="col-lg-12" frameborder="0" src="http://<?php print($_SERVER["SERVER_NAME"].'/'.$env["PLAYBACK_DIR"]); ?>"></iframe>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
<?php 
	include "footer.php" 
?>