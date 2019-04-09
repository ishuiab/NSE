<?php
require_once("config/config.php");
include("/functions/fusioncharts.php");
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
  <script type="text/javascript" src="js/fc/fusioncharts.js"></script>
  <script type="text/javascript" src="js/fc/themes/fusioncharts.theme.fusion.js"></script>
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
					<?php
                          $json = prep_json();
                          $columnChart = new FusionCharts("candlestick", "ex1", "100%", 500, "admin_panel", "json",$json);
                          $columnChart->render();
					?>
          <center>
					<table style='width:40%;vertical-align:middle;' class='table table-hover'>
								<tr>
									<th style='font-size:12px;text-align:center;font-weight:bold;' colspan='5'>Time Interval For Chart</th>
								</tr>
								<tr>
										<?php
												print "<td style='font-size:12px;text-align:center;font-weight:bold;' class='warning'><a href='chart.php?id=$_GET[id]&scrip=$_GET[scrip]' class='btn btn-primary'>1 Min</a></td>
														   <td style='font-size:12px;text-align:center;font-weight:bold;' class='warning'><a href='chart.php?id=$_GET[id]&scrip=$_GET[scrip]&int=3' class='btn btn-warning'>3 Min</a></td>
															 <td style='font-size:12px;text-align:center;font-weight:bold;' class='warning'><a href='chart.php?id=$_GET[id]&scrip=$_GET[scrip]&int=5' class='btn btn-success'>5 Min</a></td>
															 <td style='font-size:12px;text-align:center;font-weight:bold;' class='warning'><a href='chart.php?id=$_GET[id]&scrip=$_GET[scrip]&int=10' class='btn btn-info'>10 Min</a></td>
															 <td style='font-size:12px;text-align:center;font-weight:bold;' class='warning'><a href='chart.php?id=$_GET[id]&scrip=$_GET[scrip]&int=15' class='btn btn-danger'>15 Min</a></td>";
										?>
								</tr>
						</table>
		    		<div id="admin_panel"> 
						
			    	</div>
						<?php
								get_sim_details();
						?>
          </center>
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
