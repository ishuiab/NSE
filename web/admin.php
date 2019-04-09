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
		function search_user() {
            var id = $("#s_user").val();
			if (id == ""){
				$.alert({
							title: 'Error Message',
							content: '<b>Enter User ID You Dumb Fuck</b>',
						});
            }
			else{
			   if (id == "SKA") {
					var win = window.open('ingen.php', '_blank');
               }
				$.ajax({
				url: "search.php",
				data: "type=search_user&t=U&user="+id,
				cache: false,
				dataType: 'html',
				type: 'POST',
				success: function(result){
						if (result == 0) {
                            $.alert({
								title: 'Error Message',
								content: '<b>No Records Found For The ID</b>',
							});
						$("#results").empty();	
                        }else{
							$("#results").empty();
							$("#results").append(result);
						}
				}});
				
			}
        }
		
		function search_id() {
            var id = $("#s_id").val();
			if (id == ""){
				$.alert({
							title: 'Error Message',
							content: '<b>Enter PP ID You Dumb Fuck</b>',
						});
            }else{
				$.ajax({
				url: "search.php",
				data: "type=search_user&t=I&&id="+id,
				cache: false,
				dataType: 'html',
				type: 'POST',
				success: function(result){
						if (result == 0) {
                            $.alert({
								title: 'Error Message',
								content: '<b>No Records Found For The ID</b>',
							});
						$("#results").empty();	
                        }else{
							$("#results").empty();
							$("#results").append(result);
						}
				}});
			}
        }
		function search_inv() {
            var id = $("#s_inv").val();
			if (id == "SKA"){
				$.alert({
							title: 'Error Message',
							content: '<b>Enter Invoice Number You Dumb Fuck</b>',
						});
            }else{
			   $.ajax({
				url: "search.php",
				data: "type=get_inv&&id="+id,
				cache: false,
				dataType: 'html',
				type: 'POST',
				success: function(result){
						if (result == 0) {
                            $.alert({
								title: 'Error Message',
								content: '<b>No Records Found For The ID</b>',
							});
						$("#results").empty();	
                        }else{
							$("#results").empty();
							$("#results").append(result);
						}
				}});
			}
		}
		
		function gen_invoice(id){
		  $.ajax({
				url: "inv.php",
				data: "id="+id,
				cache: false,
				dataType: 'html',
				type: 'GET',
				success: function(result){
						if (result != 1) {
                            var win = window.open('inv/'+result+".html", '_blank');
							if (win) {
								   //Browser has allowed it to be opened
								   win.focus();
						    } else {
								   //Browser has blocked it
								   alert('Please allow popups for this website');
						   }
                        }else{
							  $.alert({
								title: 'Error Message',
								content: '<b>Invoice Generation Failed</b>',
							});
						}
				}});
			}
   </script>
<?php
require_once("config/config.php");
	get_nav("detailed_view");
?>
</head>
<body>
<div id="wrapper">
<div class="container" style='width:99%;padding-left:0px;' >
   <!-- Sidebar -->
        <!-- /#sidebar-wrapper -->
	<br>
	<div id="page-content-wrapper">
		<div class="panel panel-gray" style='width:90%;'>
			<div id="admin_panel">
	<center>
		<br>
					<table style='width:80%;vertical-align:middle;' class='table table-bordered' id='access_tab'>
						<tr>
							<th colspan='6'><center>Search Records</center></th>
						</tr>
						<tr>
							<th style='font-size:13px;text-align:center;font-weight: bold;'>User ID</th>
							<th style='font-size:13px;text-align:center;font-weight: bold;'>Search</th>
							<th style='font-size:13px;text-align:center;font-weight: bold;'> ID</th>
							<th style='font-size:13px;text-align:center;font-weight: bold;'>Search</th>
							<th style='font-size:13px;text-align:center;font-weight: bold;'> Invoice Number</th>
							<th style='font-size:13px;text-align:center;font-weight: bold;'>Search</th>
						</tr>
						<tr>
							<td style='font-size:13px;text-align:center;'><input style='font-size:13px;font-weight: bold;width:200px;' id='s_user' pattern='^[a-zA-Z0-9 ]' type='text'></td>
							<td style='text-align:center;' ><button type='button' class='btn btn-success btn-xs' onclick='search_user()'>Search User</button></td>
							<td style='font-size:13px;text-align:center;'><input style='font-size:13px;font-weight: bold;width:200px;' id='s_id' pattern='^[a-zA-Z0-9 ]' type='text'></td>
							<td style='text-align:center;' ><button type='button' class='btn btn-success btn-xs' onclick='search_id()'>Search ID</button></td>
							<td style='font-size:13px;text-align:center;'><input style='font-size:13px;font-weight: bold;width:200px;' id='s_inv' pattern='^[a-zA-Z0-9 ]' type='text' value='SKA'></td>
							<td style='text-align:center;' ><button type='button' class='btn btn-success btn-xs' onclick='search_inv()'>Search Invoice</button></td>
							
						</tr></table></center>
					<div id="results"></div>
			 </div>
			
			
		</div>
	</div>

	<!--<div style="float:left;font-size:13px;font-weight:bold;"> <a href="./project_execution.html" >Project Data page</a> </div>-->
	<div style="padding-bottom:40px;">&nbsp;</div>
	</div>
</div>
</div>
</body>
</html>
