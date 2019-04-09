<?php
require_once("config/config.php");
?>
<html>
<head>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/execution.css" rel="stylesheet">
	<link href="css/font-awesome.min.css" rel="stylesheet">
	<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
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
	<script src="js/execution.js"> </script>
	<script src="js/jquery-confirm.js"></script>
   <script src="js/jquery.bootstrap.wizard.js"></script>
   <script src="js/prettify.js"></script>
<script>
	function gen_invoice(id){
		  $.ajax({
				url: "inv.php",
				data: "id="+id,
				cache: false,
				dataType: 'html',
				type: 'GET',
				success: function(result){
						if (result != 1) {

							$("#"+id).html(result);
                        }else{
							$("#"+id).html("FAILED");
						}
				}});
			}
	function geninvoices() {
        $(".inv").each(function() {
			 var id = $(this).html();
			 $(this).html("Generating");
			 var x = gen_invoice(id);

		});
    }
</script>

</head>
<body onload="geninvoices()">
	<div style="height: 10px;">&nbsp;</div>
   <!-- Sidebar -->
        <!-- /#sidebar-wrapper -->
	<br>
	<div id="page-content-wrapper">
		<div class="panel panel-gray" style='width:99%;'>
			<div id="admin_panel">
				<center>
					<br>
			<table style='width:70%;vertical-align:middle;' class='table table-bordered'>
					<tr>
						<th colspan='5' style='font-size:10px;text-align:center;font-weight: bold;'>Inv Details</th>
					</tr>
					<tr>
						<th style='font-size:10px;text-align:center;font-weight: bold;'>SL No</th>
						<th style='font-size:10px;text-align:center;font-weight: bold;'>Inv No</th>
						<th style='font-size:10px;text-align:center;font-weight: bold;'>Date</th>
						<th style='font-size:10px;text-align:center;font-weight: bold;'>Item</th>
						<th style='font-size:10px;text-align:center;font-weight: bold;'>Value</th>
					</tr>
<?php
		require_once("functions/functions_gen.php");
		gen_all();
?>
			</table> </center>
			</div>
		</div>
	</div>
	<div style="padding-bottom:40px;">&nbsp;</div>
	</div>


</body>
</html>

<?php

?>
