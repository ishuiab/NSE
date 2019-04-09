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
	<script src="js/jquery-confirm.js"></script>
   <script src="js/jquery.bootstrap.wizard.js"></script>
   <script src="js/prettify.js"></script>
   <script>
   function search_invoice() {
            var id = $("#mt").val();
			if (id == ""){
				$.alert({
							title: 'Error Message',
							content: '<b>Choose A Month</b>',
						});
            }
			else{
				$.ajax({
				url: "search.php",
				data: "type=search_inv&m="+id,
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

	function upd_inv(ord,cat,date,id,oval){
		var v = $('#'+id).val();
		if (v == "") {
            v =0;
        }else{
			v = parseInt(v);
		}
		if (v > ord) {
			$('#'+id).val(ord);
			 v = ord;
             $.alert({
								title: 'Error Message',
								content: '<b>Max Invoice Can Be Generated On '+date+' Are '+ord+'.<br/>Please Choose Less Or Equal To'+ord+'.<br> Setting Gen Value To Max Value '+ord,
					});
        }

		calc_all(cat,id);
	}

function calc_all(cat,id){
		var tot_val = 0;
		$('.'+cat).each(function() {
			    var i  = this.id;
				if (i != "") {
					var x =  parseInt($("#"+i).val());
					if(isNaN(x)) {
                        x = 0;
						$("#"+i).val(x);
					}
                    tot_val += x;
                }
		});

		$("#"+cat+"GEN").val(tot_val);
		$("#"+cat+"TGEN").val(tot_val);

		var a = parseInt($("#AGEN").val());
		var b = parseInt($("#BGEN").val());
		var c = parseInt($("#CGEN").val());
		var d = parseInt($("#DGEN").val());
		var e = parseInt($("#EGEN").val());
		var f = parseInt($("#FGEN").val());

		$("#TGEN").val(a+b+c+d+e+f);

		var AVG_CST = 0;
		$('.'+cat+"_A").each(function() {
			var i   = this.id;
			var x   = $("#"+i).val();
			var drr = x.split(":");

			if (drr[0] != "0") {
                var CST = drr[0];
				var ORD = drr[1];
				var ID  = drr[2];

					var ord_qt = parseInt($("#"+ID+"_"+cat).val());
					if (ord_qt != 0) {
						AVG_CST += ((CST/ORD)*ord_qt);
                    }
			}
		});


		$("#"+cat+"TVAL").val(Math.round(AVG_CST));

		var a = parseInt($("#ATVAL").val());
		var b = parseInt($("#BTVAL").val());
		var c = parseInt($("#CTVAL").val());
		var d = parseInt($("#DTVAL").val());
		var e = parseInt($("#ETVAL").val());
		var f = parseInt($("#FTVAL").val());

		var tv = (a+b+c+d+e+f);

		$("#TVAL").val(tv);
		if (tv == 0) {
            $("#sub_me").prop('disabled', true);
        }else{
			$("#sub_me").prop('disabled', false);
		}

}

</script>
</head>
<body>
	<div style="height: 10px;">&nbsp;</div>
   <!-- Sidebar -->
        <!-- /#sidebar-wrapper -->
	<br>
	<div id="page-content-wrapper">
		<div class="panel panel-gray" style='width:99%;'>
			<div id="admin_panel">
	<center>
		<br>
					<table style='width:40%;vertical-align:middle;' class='table table-bordered' id='access_tab'>
						<tr>
							<th style='font-size:10px;text-align:center;font-weight: bold;' colspan='2'><center>Search And Populate</center></th>
						</tr>
						<tr>
							<th style='font-size:10px;text-align:center;font-weight: bold;'>Month</th>
							<th style='font-size:10px;text-align:center;font-weight: bold;'>Search</th>
						</tr>
						<tr>
							<td>
								<?php get_months();?>
							</td>
							<td style='text-align:center;'>
								<button style='font-size:10px;' type='button' class='btn btn-success btn-xm' onclick='search_invoice()'>Search DB</button>
							</td>
						</tr>
				 </table></center>
					<div id="results"></div>
			 </div>


		</div>
	</div>

	<!--<div style="float:left;font-size:13px;font-weight:bold;"> <a href="./project_execution.html" >Project Data page</a> </div>-->
	<div style="padding-bottom:40px;">&nbsp;</div>
	</div>


</body>
</html>

<?php
function get_months(){
		$ret_str = "<select style='float:left;font-size:10px;font-weight:bold;' class='form-control' id='mt'>
						<option value=''>Select Month</option>";
		$query   = "SELECT DISTINCT SUBSTR(DATE,1,6) AS MT FROM lucifer.sales ORDER BY MT";
		$result  = mysqli_query(create_con(), $query);
				while($row = mysqli_fetch_assoc($result))
						{
							 $ret_str .= "<option style='float:left;font-size:10px;font-weight:bold;' value='$row[MT]'>".date_conv($row['MT'])."</option>";
						}
		$ret_str .= "</select>";

		print $ret_str;
	}

function date_conv($date){
			$mnt   = array("01"=>"JAN","02"=>"FEB","03"=>"MAR","04"=>"APR","05"=>"MAY","06"=>"JUN","07"=>"JUL","08"=>"AUG","09"=>"SEP","10"=>"OCT","11"=>"NOV","12"=>"DEC");
			$yr    = substr($date,0,4);
			$month = substr($date,4,2);
			$date = "$mnt[$month] $yr";
			return $date;
}
?>
