		<div class="row">
			<div class="col-lg-12">
				 <div class="pull-right">
					<h5><small>PiWebcam v<?php print $env["MY_VERSION"] ?> (Build <?php print $env["MY_BUILD"] ?>)</small></h5>
				</div>
			</div>
        </div>

    </div>

    <!-- jQuery -->
    <script src="js/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="js/metisMenu.min.js"></script>
	
	<!-- DataTables JavaScript -->
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <script src="js/dataTables.responsive.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/sb-admin-2.js"></script>
	
	<?php
		// if a page is requesting to add something to the footer, print it out
		foreach ($footer as $index => $entry) echo "$entry\n"
	?>

</body>

</html>
