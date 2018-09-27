<?php 
	include "header.php";
	include "messages.php";
	require_once "Michelf/MarkdownExtra.inc.php";
?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-<?php print $env["MY_WEB_PANEL_STYLE"] ?>">
                        <div class="panel-heading">
                           <i class="fa fa-book fa-fw"></i> User Manual
                        </div>
                        <div class="panel-body">
                            <div class="row">
								<div class="col-lg-12">
									<?php
										echo \Michelf\MarkdownExtra::defaultTransform(file_get_contents($env["MY_DIR"]."/README.md"));
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
<?php 
	include "footer.php" 
?>