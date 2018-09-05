<?php 
	include "header.php";
	// form submitted
	if(count($_REQUEST) > 0) {
		if (array_key_exists("EMAIL_ENABLE",$_REQUEST)) run("set_email_enable '".$_REQUEST["EMAIL_ENABLE"]."'");
		if (array_key_exists("EMAIL_TO",$_REQUEST) && array_key_exists("EMAIL_SERVER",$_REQUEST)) run("set_email '".$_REQUEST["EMAIL_TO"]."' '".$_REQUEST["EMAIL_SERVER"]."' '".$_REQUEST["EMAIL_TLS"]."' '".$_REQUEST["EMAIL_USERNAME"]."' '".$_REQUEST["EMAIL_PASSWORD"]."'");
		if (array_key_exists("SLACK_ENABLE",$_REQUEST)) run("set_slack_enable '".$_REQUEST["SLACK_ENABLE"]."'");
		if (array_key_exists("SLACK_TOKEN",$_REQUEST) && array_key_exists("SLACK_CHANNEL",$_REQUEST)) run("set_slack '".$_REQUEST["SLACK_TOKEN"]."' '".$_REQUEST["SLACK_CHANNEL"]."'");
		run("configure_notifications");
		load_config();
		array_push($message["success"],"Configuration applied successfully");
	}
	include "messages.php";
?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-<?php print $env["MY_WEB_PANEL_STYLE"] ?>">
                        <div class="panel-heading">
                            <i class="fa fa-bell fa-fw"></i> Notifications
                        </div>
                        <div class="panel-body">
							<form id="form" method="POST" role="form">
								<div class="row">
									<div class="col-lg-6">
										<div class="panel panel-default">
											<div class="panel-body">
												<h3>E-mail</h3>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="EMAIL_ENABLE">
														<input value="1" name="EMAIL_ENABLE" type="checkbox"<?php if ($config["EMAIL_ENABLE"] === "1") print " checked"; ?>>Enable e-mail notifications
													</label>
												</div>
												<p class="help-block">When a motion is detected, the snapshot is attached to an e-mail message and sent to the configured recipients</p>
												<div class="form-group">
													<label>Recipients</label>
													<input name="EMAIL_TO" class="form-control" value="<?php print $config["EMAIL_TO"]; ?>">
													<p class="help-block">The e-mail address the notification has to be sent to. For multiple recipients, separate them with comma (e.g. <i>user1@gmail.com, user2@gmail.com</i>).</p>
												</div>
												<div class="form-group">
													<label>Mail Server</label>
													<input name="EMAIL_SERVER" class="form-control" value="<?php print $config["EMAIL_SERVER"]; ?>">
													<p class="help-block">The mail server to use (e.g. <i>smtp.gmail.com:587</i>) </p>
												</div>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="EMAIL_TLS">
														<input value="1" name="EMAIL_TLS" type="checkbox"<?php if ($config["EMAIL_TLS"] === "1") print " checked"; ?>>Use TLS/SSL
													</label>
													<p class="help-block">Check the box if the mail server requires SSL/TLS before starting the negotiation</p>
												</div>
												<div class="form-group">
													<label>Username</label>
													<input name="EMAIL_USERNAME" class="form-control" value="<?php print $config["EMAIL_USERNAME"]; ?>">
													<p class="help-block">Optional username for authenticating against the mail server. Leave it empty for no authentication.</p>
												</div>
												<div class="form-group">
													<label>Password</label>
													<input name="EMAIL_PASSWORD" type="password" class="form-control" value="<?php print $config["EMAIL_PASSWORD"]; ?>">
													<p class="help-block">Optional password for authenticating against the mail server.</p>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="panel panel-default">
											<div class="panel-body">
												<h3>Slack</h3>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="SLACK_ENABLE">
														<input value="1" name="SLACK_ENABLE" type="checkbox"<?php if ($config["SLACK_ENABLE"] === "1") print " checked"; ?>>Enable Slack notifications
													</label>
												</div>
												<p class="help-block">When a motion is detected, the snapshot is posted on the configured slack channel</p>
												<div class="form-group">
													<label>Token</label>
													<input name="SLACK_TOKEN" class="form-control" value="<?php print $config["SLACK_TOKEN"]; ?>">
													<p class="help-block">The token used for authenticating against Slack. Click <a target="_blank" href="https://api.slack.com/custom-integrations/legacy-tokens">HERE</a> for generating a new token.</p>
												</div>
												<div class="form-group">
													<label>Channel</label>
													<input name="SLACK_CHANNEL" class="form-control" value="<?php print $config["SLACK_CHANNEL"]; ?>">
													<p class="help-block">The slack channel to upload the snapshot once motion is detected(<i>e.g. #general</i>)</p>
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