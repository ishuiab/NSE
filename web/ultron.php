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
	<script src="js/ultron.js"></script>
	<style>
		body .modal-dialog {
				/* new custom width */
			width: 80%;
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
			<table style='width:50%;vertical-align:middle;' class='table table-bordered'>
					<tr>
						<th colspan='4'>
								<center>Generate Ultron Data</center>
						</th>
					</tr>
					<tr>
						<th>
								<center>Series</center>
						</th>
						<th>
								<center>Start</center>
						</th>
						<th>
								<center>End</center>
						</th>
						<th>
								<center>Status</center>
						</th>
					</tr>
					<tr>
						<td class='success'>
							<center>
									<select class='form-control' id='series'>
										<option value='M'>M</option>
										<option value='I'>I</option>
										<option value='A'>A</option>
									</select>
							</center>
						</td>
						<td class='success'>
								<input class='form-control' id='start' type='number' min='1' max='2' step='1' value='1'>
						</td>
						<td class='success'>
								<input class='form-control' id='end' type='number' min='1' max='2' step='1' value='1000'>
						</td>
						<td class='success'>
							<center>
									<select class='form-control' id='status'>
										<option value='OA'>Only Active</option>
										<option value='A'>All </option>
									</select>
							</center>
						</td>
					</tr>
					<tr class='danger'>
						<td colspan='4'>
							<center>
								<button type="button" style="font-size:12px;" class="btn btn-warning .btn-sm" onclick="search()">Unleash Ultron</button>
							</center>
						</td>
					</tr>
			</table>
	</center>
		<center>
				<div class="container" style='width: 95% !important' id='anon_div'></div>
				<div class="container" id='ult_API'></div>
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
