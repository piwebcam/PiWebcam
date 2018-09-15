<?php 

// global variables
$config = array();
$env = array();

// initialize the different message types
$message["success"] = array();
$message["info"] = array();
$message["warning"] = array();
$message["danger"] = array();

// run a given command in the PiWebcam shell and return its output
function run($command) {
	global $message;
	global $config;
	if (array_key_exists("DEBUG",$config) && $config["DEBUG"]) array_push($message["info"],"$ ".$command);
	$ret = shell_exec("sudo /boot/PiWebcam/PiWebcam.sh ".$command." 2>&1");
	if (array_key_exists("DEBUG",$config) && $config["DEBUG"]) array_push($message["info"],$ret);
	return $ret;
}

// parse a text in the format key=value and return an array in the format array[key] = value
function parse_text($text,&$array) {
	$rows = explode("\n",$text);
	foreach($rows as $index => $row) {
		if (preg_match('/^#/',$row)) continue;
		if (!preg_match('/=/',$row)) continue;
		$row = str_replace("'","",$row);
		$data = explode("=",$row);
		$array[$data[0]] = $data[1];
	}
}

// load a PiWebcam configuration file
function load_config() {
	global $config;
	parse_text(run("show_config"),$config);
}

// generate a bootstrap modal with the given name and message. On close re-enable the button through which the modal was generated, on submit submit the form
function generate_modal($name,$message,$button,$form) {
	print('<div class="modal fade" id="'.$name.'" tabindex="-1" role="dialog" aria-labelledby="'.$name.'_label" aria-hidden="true">'."\n");
    print('		<div class="modal-dialog">'."\n");
    print('			<div class="modal-content">'."\n");
    print('				<div class="modal-header">'."\n");
    print('					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'."\n");
    print('					<h4 class="modal-title" id="confirm_label">Are you sure?</h4>'."\n");
	print('				</div>'."\n");
    print('				<div class="modal-body">'.$message.'<br><br>Do you want to continue?</div>'."\n");
    print('				<div class="modal-footer">'."\n");
    print('					<button type="button" onclick=\'$("#'.$button.'").removeClass("disabled");\' class="btn btn-default" data-dismiss="modal">Close</button>'."\n");
    print('					<button type="button" id="'.$name.'_button" onclick=\'$("#'.$name.'_button").addClass("disabled"); $("#'.$name.'_button").html("Please Wait..."); $("#'.$form.'").submit()\' class="btn btn-primary">Confirm</button>'."\n");
    print('				</div>'."\n");
    print('			</div>'."\n");
    print('		</div>'."\n");
	print('</div>'."\n");
}

// load the constants and variables
parse_text(run("env"),$env);

// load the configuration
load_config();

// unless no headers is requested, print out the header
global $no_headers;
if (! array_key_exists("no_headers",$_REQUEST)) {
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php print $env["MY_NAME"] ?></title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="css/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="js/html5shiv.js"></script>
        <script src="js/respond.min.js"></script>
    <![endif]-->
	
	<!-- Prevent from being opened within an iframe -->
	<script type="text/javascript">
		if ( window.self !== window.top ) window.top.location.href=window.location.href;
	</script>

</head>

<body>

    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php"><?php print $env["MY_NAME"] ?> v<?php print $env["MY_VERSION"] ?></a>
            </div>

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="#"><i class="fa fa-fw"></i>Camera<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="view.php"><i class="fa fa-video-camera fa-fw"></i> View</a>
                                </li>
                                <li>
                                    <a href="playback.php"><i class="fa fa-play-circle-o fa-fw"></i> Playback</a>
                                </li>
                                <li>
                                    <a href="notification.php"><i class="fa fa-bell fa-fw"></i> Notifications</a>
                                </li>
                                <li>
                                    <a href="settings.php"><i class="fa fa-wrench fa-fw"></i> Settings</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-fw"></i>Device<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="status.php"><i class="fa fa-dashboard fa-fw"></i> Status</a>
                                </li>
                                <li>
                                    <a href="network.php"><i class="fa fa-video-camera fa-fw"></i> Network</a>
                                </li>
                                <li>
                                    <a href="system.php"><i class="fa fa-gears fa-fw"></i> System</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">
					<img class="logo" src="images/logo.png"> 
					<?php print $config["DEVICE_NAME"]; ?>
					</h1>
                </div>
            </div>
<?php
	}
?>