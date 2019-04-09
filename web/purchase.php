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
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
   <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="js/jquery.form.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/bootstrapValidator.min.js"></script>
	<script src="js/moment-with-locales.js"></script>
	<script src="js/bootstrap-datetimepicker.js"></script>
	
	<script src="js/jquery-confirm.js"></script>
    <script src="js/jquery.bootstrap.wizard.js"></script>
    <script src="js/prettify.js"></script>
   <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
   <script src="js/purchase.js"></script>
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
					<input type='hidden' id='conv' value='71'>
				<table style='width:40%;vertical-align:middle;' class='table table-bordered'>
					<tr class='info'>
						<td>
							<center>
								<button type="button" style="font-size:10px;margin-top:5px;" class="btn btn-warning .btn-sm" onclick="add_order()">Add Order</button>	
							</center>
						</td>
						<td>
							<center>
								<button type="button" style="font-size:10px;margin-top:5px;" class="btn btn-warning .btn-sm" onclick="view_order(1)">View Pending Order's</button>	
							</center>
						</td>
						<td>
							<center>
								<button type="button" style="font-size:10px;margin-top:5px;" class="btn btn-warning .btn-sm" onclick="view_order(0)">View Completed Order's</button>	
							</center>
						</td>
					</tr>
			</table>

		</center>
		<center>
				<div width='80%'  id='inv_div'>
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
          <h4 class="modal-title" id='m_title'>Images Details</h4>
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
