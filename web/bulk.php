<?php
require_once("config/config.php");
?>
<html>
<head>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/execution.css" rel="stylesheet">
	<link href="css/font-awesome.min.css" rel="stylesheet">
	<link href="css/bootstrap-datetimepicker.css" rel="stylesheet">
	<link href="css/bootstrapValidator.min.css" rel="stylesheet">
	<link href="css/page.css" rel="stylesheet">
	<link href="css/jquery-confirm.css" rel="stylesheet">
	
    <!-- you don't need ignore=notused in your code, this is just here to trick the cache -->
	<script src="js/highcharts.js"></script>
	<script src="js/exporting.js"></script>
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery.form.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/bootstrapValidator.min.js"></script>
	<script src="js/moment-with-locales.js"></script>
	<script src="js/bootstrap-datetimepicker.js"></script>
	<script src="js/jquery-confirm.js"></script>
    <script src="js/jquery.bootstrap.wizard.js"></script>
    <script src="js/prettify.js"></script>
	<script src="js/bulk.js"></script>
	<style>
		body .modal-dialog {
				/* new custom width */
			width: 1200px;
		}
		.CHR {
	width: 1200px;
	height: 300px;
	margin: 0 auto
}
	</style>
	<title>Lucifer</title>
<?php
	get_nav("detailed_view");
?>
</head>
<body>
	<div id="page-content-wrapper">
		<div class="panel panel-gray" style='width:100%;'>
			<div id="admin_panel">
				<center>
					<?php
						get_sel();
					?>
				</center>
			<center>
					<div class="container" id='stats'>
					</div>
			</center>
			</div>
		</div>
	</div>
  <!-- Modal -->
<div class="modal fade" id="data" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id='m_title'>Strategy Details</h4>
        </div>
        <div class="modal-body" id='m_body'>
          <p>Some text in the modal.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>
</div>
</body>
</html>
