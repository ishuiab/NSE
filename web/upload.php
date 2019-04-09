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
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery.form.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/bootstrapValidator.min.js"></script>
	<script src="js/moment-with-locales.js"></script>
	<script src="js/bootstrap-datetimepicker.js"></script>
	<script src="js/jquery-confirm.js"></script>
    <script src="js/jquery.bootstrap.wizard.js"></script>
    <script src="js/prettify.js"></script>
	<script src="js/anomaly.js"></script>
	<style>
		body .modal-dialog {
				/* new custom width */
			width: 480px;
		}
	</style>
	<title>Lucifer</title>
<?php
	get_nav();
	
?>
</head>
<body>
   <!-- Sidebar -->
        <!-- /#sidebar-wrapper -->
	<br>
	<div id="page-content-wrapper">
		<div class="panel panel-gray" style='width:99%;'>
			<div id="admin_panel">
				<center>
					<br>
			<table style='width:30%;vertical-align:middle;' class='table table-bordered'>
					<tr>
						<th style='font-size:10px;text-align:center;font-weight: bold;'>File</th>
						<th style='font-size:10px;text-align:center;font-weight: bold;'>Upload File</th>
					</tr>
					<tr>
						<form action="upload.php" method="post" enctype="multipart/form-data">
							<td> <input type="file" name="fileToUpload" id="fileToUpload"></td>
							<td><center><button  name='submit' type="submit" style='font-size:10px'class="btn btn-warning .btn-sm" >Upload</button></center></td>
						</form>
					</tr>
			</table>
		</center>
		<center>
				<div class="container" id='anon_div'>
					<?php
						map_anomalies();
						if(count($_POST)){
							file_upload();
						}
					?>
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
          <h4 class="modal-title" id='m_title'>Summary Details</h4>
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
