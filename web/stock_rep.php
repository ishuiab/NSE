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
	<script src="js/stock.js"></script>
	<style>
		body .modal-dialog {
				/* new custom width */
			width: 1100px;
		}
	</style>
	<title>Lucifer</title>
<?php
	get_nav("detailed_view");
?>
</head>
<body>
	<div id="page-content-wrapper">
		<div class="panel panel-gray" style='width:99%;'>
			<div id="admin_panel">
            <center>
					<br>
			<table style='width:10%;vertical-align:middle;' class='table table-bordered'>
					<tr>
						<th colspan='3'>
								<center>Generate Stock Data</center>
						</th>
					</tr>
					<tr>
						<th>
								<center>Seller</center>
						</th>
					</tr>
					<tr>
						<td class='success'>
							<center>
									<?php
                                            print get_selr("STOCK","NO");    
                                    ?>
							</center>
						</td>
					</tr>
					<tr class='danger'>
						<td colspan='4'>
							<center>
								<button type="button" style="font-size:12px;" class="btn btn-warning .btn-sm" onclick="gen_stk_rep()">Get Stock</button>
							</center>
						</td>
					</tr>
			</table>
	</center>
			<div class="container" id='stock_div' style='width:99% !important'>
			</div>
		<center>
				<div class="container" id='date_div'>
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
          <h4 class="modal-title" id='m_title'>User Details</h4>
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
<div class="modal fade" id="ord" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id='m_title'>Order Details</h4>
        </div>
        <center>
        <div class="modal-body" id='o_body'>
          <p>Some text in the modal.</p>
        </div>
        </center>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
