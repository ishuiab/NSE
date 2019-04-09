<?php
require_once("config/config.php");

extract($_POST);

if($type == "get_inv"){
	$id = substr($id,3);
	$query = "SELECT * FROM lucifer.inv_tracker WHERE INV_NUM='$id'";

	if(!nr($query)){
        print 0;
    }else{
		$ids = select_single($query,"ID");
		$query = "SELECT * FROM lucifer.sales WHERE order_id='$ids'";

		$ret_str = "<center><table style='width:90%;vertical-align:middle;' class='table table-bordered'>
                            <tr>
                                <th colspan='6' style='font-size:13px;text-align:center;font-weight: bold;'>Results For Invoice $id</th>
                            </tr>
                            <tr>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>Name</th>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>PP ID</th>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>Date</th>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>Item</th>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>Cost</th>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>Action</th>
                            </tr>";
        $result = mysqli_query(create_con(),$query);
        while($row = mysqli_fetch_assoc($result))
			{
                    $ret_str .= "<tr>
                                    <td style='font-size:11px;text-align:center;font-weight: bold;'>$row[cust_name]</td>
									<td style='font-size:10px;text-align:center;'>$row[order_id]</td>
									<td style='font-size:10px;text-align:center;'>$row[date]</td>
									<td style='font-size:10px;text-align:center;'>$row[item_descr]</td>
									<td style='font-size:10px;text-align:center;'>$row[total]</td>
									<td style='text-align:center;'>
										<button style='font-size:9px;' type='button' class='btn btn-success btn-xs' onclick='gen_invoice(\"$row[order_id]\")'>Gen Invoice</button>
									</td>
                                </tr>";
            }
        $ret_str .= "</table></center>";
        print $ret_str;
	}
}
if($type == "search_user"){
	if($t == "U"){
			$query = "SELECT * FROM lucifer.sales WHERE user_id='$user'";
			$msg   = "User ID $user";
	}elseif($t == "I"){
		    $query = "SELECT * FROM lucifer.sales WHERE order_id='$id'";
			$msg   = "Paisapay ID $id";
	}
	if(!nr($query)){
        print 0;
    }else{
        $ret_str = "<center><table style='width:90%;vertical-align:middle;' class='table table-bordered'>
                            <tr>
                                <th colspan='6' style='font-size:13px;text-align:center;font-weight: bold;'>Results For User ID $msg</th>
                            </tr>
                            <tr>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>Name</th>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>PP ID</th>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>Date</th>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>Item</th>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>Cost</th>
                                <th style='font-size:13px;text-align:center;font-weight: bold;'>Action</th>
                            </tr>";
        $result = mysqli_query(create_con(),$query);
        while($row = mysqli_fetch_assoc($result))
			{
                    $ret_str .= "<tr>
                                    <td style='font-size:11px;text-align:center;font-weight: bold;'>$row[cust_name]</td>
									<td style='font-size:10px;text-align:center;'>$row[order_id]</td>
									<td style='font-size:10px;text-align:center;'>$row[date]</td>
									<td style='font-size:10px;text-align:center;'>$row[item_descr]</td>
									<td style='font-size:10px;text-align:center;'>$row[total]</td>
									<td style='text-align:center;'>
										<button style='font-size:9px;' type='button' class='btn btn-success btn-xs' onclick='gen_invoice(\"$row[order_id]\")'>Gen Invoice</button>
									</td>
                                </tr>";
            }
        $ret_str .= "</table></center>";
        print $ret_str;
    }
}

if($type == "search_inv"){
	#Categories
	#Cat A >100
	#Cat B >100 <300
	#Cat C >300 <600
	#Cat D >600 <1000
	#Cat E >1000 <2000
	#Cat F >2000
	$cat     = array();
	$mst     = array();

	$mst["A"]["CST"] = 0;
	$mst["B"]["CST"] = 0;
	$mst["C"]["CST"] = 0;
	$mst["D"]["CST"] = 0;
	$mst["E"]["CST"] = 0;
	$mst["F"]["CST"] = 0;

	$mst["A"]["GST"] = 0;
	$mst["B"]["GST"] = 0;
	$mst["C"]["GST"] = 0;
	$mst["D"]["GST"] = 0;
	$mst["E"]["GST"] = 0;
	$mst["F"]["GST"] = 0;

	$mst["A"]["ORD"] = 0;
	$mst["B"]["ORD"] = 0;
	$mst["C"]["ORD"] = 0;
	$mst["D"]["ORD"] = 0;
	$mst["E"]["ORD"] = 0;
	$mst["F"]["ORD"] = 0;

	$mst["A"]["GRD"] = 0;
	$mst["B"]["GRD"] = 0;
	$mst["C"]["GRD"] = 0;
	$mst["D"]["GRD"] = 0;
	$mst["E"]["GRD"] = 0;
	$mst["F"]["GRD"] = 0;

	$mst["CST"] = 0;
	$mst["ORD"] = 0;


	$all     = array();
	$query   = "SELECT * FROM lucifer.sales WHERE date like '$m%' AND order_id != '' ORDER BY date";
	$result  = mysqli_query(create_con(), $query);
		while($row = mysqli_fetch_assoc($result)){
						if(isset($all[$row['order_id']])){
							$all[$row['order_id']]['cost'] += $row['total'];
						}
						else{
							$all[$row['order_id']]['date']  = $row['date'];

							$cat[$row['date']]['CST']       = 0;
							$cat[$row['date']]['ORD']       = 0;

							$cat[$row['date']]['A']['CST']  = 0;
							$cat[$row['date']]['B']['CST']  = 0;
							$cat[$row['date']]['C']['CST']  = 0;
							$cat[$row['date']]['D']['CST']  = 0;
							$cat[$row['date']]['E']['CST']  = 0;
							$cat[$row['date']]['F']['CST']  = 0;

							$cat[$row['date']]['A']['ORD']  = 0;
							$cat[$row['date']]['B']['ORD']  = 0;
							$cat[$row['date']]['C']['ORD']  = 0;
							$cat[$row['date']]['D']['ORD']  = 0;
							$cat[$row['date']]['E']['ORD']  = 0;
							$cat[$row['date']]['F']['ORD']  = 0;

							$cat[$row['date']]['A']['GST']  = 0;
							$cat[$row['date']]['B']['GST']  = 0;
							$cat[$row['date']]['C']['GST']  = 0;
							$cat[$row['date']]['D']['GST']  = 0;
							$cat[$row['date']]['E']['GST']  = 0;
							$cat[$row['date']]['F']['GST']  = 0;

							$cat[$row['date']]['A']['GRD']  = 0;
							$cat[$row['date']]['B']['GRD']  = 0;
							$cat[$row['date']]['C']['GRD']  = 0;
							$cat[$row['date']]['D']['GRD']  = 0;
							$cat[$row['date']]['E']['GRD']  = 0;
							$cat[$row['date']]['F']['GRD']  = 0;

							$all[$row['order_id']]['cost']  = $row['total'];
						}
				}

		foreach($all as $k=>$v){
			$cost = $all[$k]['cost'];
			$date = $all[$k]['date'];
			$cat[$date]['CST'] += $cost;
			$cat[$date]['ORD'] ++;

			$mst['CST'] += $cost;
			$mst['ORD'] ++;

			#For Cat A
			if($cost <= 100 ){
				$cat[$date]['A']['CST'] += $cost;
				$cat[$date]['A']['ORD'] ++;

				$mst['A']['CST'] += $cost;
				$mst['A']['ORD'] ++;
			}
			#For Cat B
			elseif(($cost > 100) &&($cost <= 300)){
				$cat[$date]['B']['CST'] += $cost;
				$cat[$date]['B']['ORD'] ++;

				$mst['B']['CST'] += $cost;
				$mst['B']['ORD'] ++;

			}
			#For Cat C
			elseif(($cost > 300) &&($cost <= 600)){
				$cat[$date]['C']['CST'] += $cost;
				$cat[$date]['C']['ORD'] ++;

				$mst['C']['CST'] += $cost;
				$mst['C']['ORD'] ++;

			}
			#For Cat D
			elseif(($cost > 600) &&($cost <= 1000)){
				$cat[$date]['D']['CST'] += $cost;
				$cat[$date]['D']['ORD'] ++;

				$mst['D']['CST'] += $cost;
				$mst['D']['ORD'] ++;
			}
			#For Cat E
			elseif(($cost > 1000) &&($cost <= 2000)){
				$cat[$date]['E']['CST'] += $cost;
				$cat[$date]['E']['ORD'] ++;

				$mst['E']['CST'] += $cost;
				$mst['E']['ORD'] ++;

			}
			#For Cat F
			elseif($cost > 2000){
				$cat[$date]['F']['CST'] += $cost;
				$cat[$date]['F']['ORD'] ++;

				$mst['F']['CST'] += $cost;
				$mst['F']['ORD'] ++;
			}
		}


	$qr      = "SELECT * FROM lucifer.inv_tracker WHERE DATE LIKE '$m%'";
	$inv     = select_hash_mysql($qr,'ID','DATE');

	foreach($inv as $k=>$v){
		$cost = $all[$k]['cost'];
		$date = $all[$k]['date'];
		#For Cat A
			if($cost <= 100 ){
				$cat[$date]['A']['GST'] += $cost;
				$cat[$date]['A']['GRD'] ++;

				$mst['A']['GST'] += $cost;
				$mst['A']['GRD'] ++;
			}
			#For Cat B
			elseif(($cost > 100) &&($cost <= 300)){
				$cat[$date]['B']['GST'] += $cost;
				$cat[$date]['B']['GRD'] ++;

			    $mst['B']['GST'] += $cost;
				$mst['B']['GRD'] ++;
			}
			#For Cat C
			elseif(($cost > 300) &&($cost <= 600)){
				$cat[$date]['C']['GST'] += $cost;
				$cat[$date]['C']['GRD'] ++;

				$mst['C']['GST'] += $cost;
				$mst['C']['GRD'] ++;
			}
			#For Cat D
			elseif(($cost > 600) &&($cost <= 1000)){
				$cat[$date]['D']['GST'] += $cost;
				$cat[$date]['D']['GRD'] ++;

				$mst['D']['GST'] += $cost;
				$mst['D']['GRD'] ++;
			}
			#For Cat E
			elseif(($cost > 1000) &&($cost <= 2000)){
				$cat[$date]['E']['GST'] += $cost;
				$cat[$date]['E']['GRD'] ++;

				$mst['D']['GST'] += $cost;
				$mst['D']['GRD'] ++;
			}
			#For Cat F
			elseif($cost > 2000){
				$cat[$date]['F']['GST'] += $cost;
				$cat[$date]['F']['GRD'] ++;

				$mst['D']['GST'] += $cost;
				$mst['D']['GRD'] ++;
			}
	}

	#print_r($cat);
	$ret_str = "<form method='POST' action='gen_inv.php'><center><table style='width:90%;vertical-align:middle;' class='table table-bordered'>
							<input type='hidden' name='month' value='$m'>
                            <tr>
                                <th colspan='33' style='font-size:12px;text-align:center;font-weight: bold;'>Results For ".date_conv($m)."</th>
                            </tr>
                            <tr>
                                <th style='font-size:10px;text-align:center;font-weight: bold;'>Date</th>
                                <th colspan='5' style='font-size:10px;text-align:center;font-weight: bold;'>Below 100</th>
                                <th colspan='5' style='font-size:10px;text-align:center;font-weight: bold;'>100 - 300</th>
                                <th colspan='5' style='font-size:10px;text-align:center;font-weight: bold;'>300 - 600</th>
                                <th colspan='5' style='font-size:10px;text-align:center;font-weight: bold;'>600 - 1000</th>
                                <th colspan='5' style='font-size:10px;text-align:center;font-weight: bold;'>1000 - 2000</th>
								<th colspan='5' style='font-size:10px;text-align:center;font-weight: bold;'>Above 2000</th>

								<th colspan='2' style='font-size:9px;text-align:center;font-weight: bold;'>Total</th>
                            </tr>
							<tr>
							    <th style='font-size:9px;text-align:center;font-weight: bold;' class='success'></th>
                                <th style='background-color:#7FFFD4;font-size:8px;text-align:center;font-weight: bold;'>CST</th>
                                <th style='background-color:#7FFFD4;font-size:8px;text-align:center;font-weight: bold;'>ORD</th>
                                <th style='background-color:#7FFFD4;font-size:8px;text-align:center;font-weight: bold;'>GST</th>
                                <th style='background-color:#7FFFD4;font-size:8px;text-align:center;font-weight: bold;'>GRD</th>
								<th style='background-color:#7FFFD4;font-size:8px;text-align:center;font-weight: bold;'>GEN</th>


                                <th style='background-color:#FFE4C4;font-size:8px;text-align:center;font-weight: bold;'>CST</th>
                                <th style='background-color:#FFE4C4;font-size:8px;text-align:center;font-weight: bold;'>ORD</th>
                                <th style='background-color:#FFE4C4;font-size:8px;text-align:center;font-weight: bold;'>GST</th>
                                <th style='background-color:#FFE4C4;font-size:8px;text-align:center;font-weight: bold;'>GRD</th>
								<th style='background-color:#FFE4C4;font-size:8px;text-align:center;font-weight: bold;'>GEN</th>


                                <th style='background-color:#87CEEB;font-size:8px;text-align:center;font-weight: bold;'>CST</th>
                                <th style='background-color:#87CEEB;font-size:8px;text-align:center;font-weight: bold;'>ORD</th>
                                <th style='background-color:#87CEEB;font-size:8px;text-align:center;font-weight: bold;'>GST</th>
                                <th style='background-color:#87CEEB;font-size:8px;text-align:center;font-weight: bold;'>GRD</th>
								<th style='background-color:#87CEEB;font-size:8px;text-align:center;font-weight: bold;'>GEN</th>


                                <th style='background-color:#FFFAF0;font-size:8px;text-align:center;font-weight: bold;'>CST</th>
                                <th style='background-color:#FFFAF0;font-size:8px;text-align:center;font-weight: bold;'>ORD</th>
                                <th style='background-color:#FFFAF0;font-size:8px;text-align:center;font-weight: bold;'>GST</th>
                                <th style='background-color:#FFFAF0;font-size:8px;text-align:center;font-weight: bold;'>GRD</th>
								<th style='background-color:#FFFAF0;font-size:8px;text-align:center;font-weight: bold;'>GEN</th>


                                <th style='background-color:#FFB6C1;font-size:8px;text-align:center;font-weight: bold;'>CST</th>
                                <th style='background-color:#FFB6C1;font-size:8px;text-align:center;font-weight: bold;'>ORD</th>
                                <th style='background-color:#FFB6C1;font-size:8px;text-align:center;font-weight: bold;'>GST</th>
                                <th style='background-color:#FFB6C1;font-size:8px;text-align:center;font-weight: bold;'>GRD</th>
								<th style='background-color:#FFB6C1;font-size:8px;text-align:center;font-weight: bold;'>GEN</th>


                                <th style='background-color:#FFF8DC;font-size:8px;text-align:center;font-weight: bold;'>CST</th>
                                <th style='background-color:#FFF8DC;font-size:8px;text-align:center;font-weight: bold;'>ORD</th>
                                <th style='background-color:#FFF8DC;font-size:8px;text-align:center;font-weight: bold;'>GST</th>
                                <th style='background-color:#FFF8DC;font-size:8px;text-align:center;font-weight: bold;'>GRD</th>
								<th style='background-color:#FFF8DC;font-size:8px;text-align:center;font-weight: bold;'>GEN</th>

								<th style='font-size:8px;text-align:center;font-weight: bold;' class='success'>CST</th>
								<th style='font-size:8px;text-align:center;font-weight: bold;' class='success'>ORD</th>

                            </tr>";

			foreach($cat as $k=>$v){
				$ret_str .= "<tr>
								<td style='font-size:9px;text-align:center;font-weight:bold;' class='success'>".date_conv_d($k)."</td>

								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['A']['CST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['A']['ORD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['A']['GST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['A']['GRD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'><input class='form-control A' type='number'".dis_check($cat[$k]['A']['ORD'],"A",$k,$cat[$k]['A']['GRD'],$cat[$k]['A']['CST'])."></td>
								<input type='hidden' class='A_A' id=${k}_A_ALL value='".$cat[$k]['A']['CST'].":".$cat[$k]['A']['ORD'].":$k'>

								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['B']['CST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['B']['ORD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['B']['GST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['B']['GRD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'><input class='form-control B' type='number'".dis_check($cat[$k]['B']['ORD'],"B",$k,$cat[$k]['B']['GRD'],$cat[$k]['B']['CST'])."></td>
								<input type='hidden' class='B_A' id=${k}_B_ALL value='".$cat[$k]['B']['CST'].":".$cat[$k]['B']['ORD'].":$k'>

								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['C']['CST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['C']['ORD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['C']['GST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['C']['GRD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'><input class='form-control C' type='number'".dis_check($cat[$k]['C']['ORD'],"C",$k,$cat[$k]['C']['GRD'],$cat[$k]['C']['CST'])."></td>
								<input type='hidden' class='C_A' id=${k}_C_ALL value='".$cat[$k]['C']['CST'].":".$cat[$k]['C']['ORD'].":$k'>

								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['D']['CST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['D']['ORD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['D']['GST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['D']['GRD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'><input class='form-control D' type='number'".dis_check($cat[$k]['D']['ORD'],"D",$k,$cat[$k]['D']['GRD'],$cat[$k]['D']['CST'])."></td>
								<input type='hidden' class='D_A' id=${k}_D_ALL value='".$cat[$k]['D']['CST'].":".$cat[$k]['D']['ORD'].":$k'>

								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['E']['CST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['E']['ORD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['E']['GST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['E']['GRD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'><input class='form-control E' type='number'".dis_check($cat[$k]['E']['ORD'],"E",$k,$cat[$k]['E']['GRD'],$cat[$k]['E']['CST'])."></td>
								<input type='hidden' class='E_A' id=${k}_E_ALL value='".$cat[$k]['E']['CST'].":".$cat[$k]['E']['ORD'].":$k'>

								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['F']['CST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['F']['ORD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['F']['GST']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'>".$cat[$k]['F']['GRD']."</td>
								<td style='font-size:8px;text-align:center;font-weight:bold;'><input class='form-control F' type='number'".dis_check($cat[$k]['F']['ORD'],"F",$k,$cat[$k]['F']['GRD'],$cat[$k]['F']['CST'])."></td>
								<input type='hidden' class='F_A' id=${k}_F_ALL value='".$cat[$k]['F']['CST'].":".$cat[$k]['F']['ORD'].":$k'>

								<td style='font-size:9px;text-align:center;font-weight: bold;' class='success'>".$cat[$k]['CST']."</td>
								<td style='font-size:9px;text-align:center;font-weight: bold;' class='success'>".$cat[$k]['ORD']."</td>
							 </tr>";
			}

	$ret_str .= "<tr class='warning'>
						<td style='font-size:9px;text-align:center;font-weight:bold;'>Total</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['A']['CST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['A']['ORD']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['A']['GST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['A']['GRD']."</td>
						<td style='font-size:9px;text-align:center;'><input class='form-control' type='number' id='AGEN' style='background-color:#7FFFD4;font-size:9px;width:40px;height:18px;font-weight: bold;' value ='0' disabled></td>

						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['B']['CST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['B']['ORD']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['B']['GST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['B']['GRD']."</td>
						<td style='font-size:9px;text-align:center;'><input class='form-control' type='number' id='BGEN' style='background-color:#FFE4C4;font-size:9px;width:40px;height:18px;font-weight: bold;' value ='0' disabled></td>

						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['C']['CST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['C']['ORD']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['C']['GST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['C']['GRD']."</td>
						<td style='font-size:9px;text-align:center;'><input class='form-control' type='number' id='CGEN' style='background-color:#87CEEB;font-size:9px;width:40px;height:18px;font-weight: bold;' value ='0' disabled></td>

						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['D']['CST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['D']['ORD']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['D']['GST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['D']['GRD']."</td>
						<td style='font-size:9px;text-align:center;'><input class='form-control' type='number'  id='DGEN' style='background-color:#FFFAF0;font-size:9px;width:40px;height:18px;font-weight: bold;' value ='0' disabled></td>

						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['E']['CST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['E']['ORD']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['E']['GST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['E']['GRD']."</td>
						<td style='font-size:9px;text-align:center;'><input class='form-control' type='number'  id='EGEN' style='background-color:#FFB6C1;font-size:9px;width:40px;height:18px;font-weight: bold;' value ='0' disabled></td>

						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['F']['CST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['F']['ORD']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['F']['GST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['F']['GRD']."</td>
						<td style='font-size:9px;text-align:center;'><input class='form-control' type='number'  id='FGEN' style='background-color:#FFF8DC;font-size:9px;width:40px;height:18px;font-weight: bold;' value ='0' disabled></td>

						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['CST']."</td>
						<td style='font-size:9px;text-align:center;font-weight:bold'>".$mst['ORD']."</td>
				</tr>
			</table>";
	#print_r($mst);
	print $ret_str;

	$inv_tab = "<center><table style='width:90%;vertical-align:middle;' class='table table-bordered'>
                            <tr>
                                <th colspan='14' style='font-size:10px;text-align:center;font-weight: bold;'>Average Invoice Values For ".date_conv($m)."</th>
                            </tr>
                            <tr>
                                <th colspan='2' style='font-size:8px;text-align:center;font-weight: bold;'>Below 100</th>
                                <th colspan='2' style='font-size:8px;text-align:center;font-weight: bold;'>100 - 300</th>
                                <th colspan='2' style='font-size:8px;text-align:center;font-weight: bold;'>300 - 600</th>
                                <th colspan='2' style='font-size:8px;text-align:center;font-weight: bold;'>600 - 1000</th>
                                <th colspan='2' style='font-size:8px;text-align:center;font-weight: bold;'>1000 - 2000</th>
								<th colspan='2' style='font-size:8px;text-align:center;font-weight: bold;'>Above 2000</th>
								<th colspan='2' style='font-size:8px;text-align:center;font-weight: bold;'>Total</th>
                            </tr>
							<tr>
								<th style='font-size:8px;text-align:center;font-weight: bold;background-color:#7FFFD4;'>Number</th>
                                <th style='font-size:8px;text-align:center;font-weight: bold;background-color:#7FFFD4;'>Avg Value</th>

								<th style='font-size:8px;text-align:center;font-weight: bold;background-color:#FFE4C4;'>Number</th>
                                <th style='font-size:8px;text-align:center;font-weight: bold;background-color:#FFE4C4;'>Avg Value</th>

								<th style='font-size:8px;text-align:center;font-weight: bold;background-color:#87CEEB;'>Number</th>
                                <th style='font-size:8px;text-align:center;font-weight: bold;background-color:#87CEEB;'>Avg Value</th>

								<th style='font-size:8px;text-align:center;font-weight: bold;background-color:#FFFAF0;'>Number</th>
                                <th style='font-size:8px;text-align:center;font-weight: bold;background-color:#FFFAF0;'>Avg Value</th>

								<th style='font-size:8px;text-align:center;font-weight: bold;background-color:#FFB6C1;'>Number</th>
                                <th style='font-size:8px;text-align:center;font-weight: bold;background-color:#FFB6C1;'>Avg Value</th>

								<th style='font-size:8px;text-align:center;font-weight: bold;background-color:#FFF8DC;'>Number</th>
                                <th style='font-size:8px;text-align:center;font-weight: bold;background-color:#FFF8DC;'>Avg Value</th>

								<th style='font-size:8px;text-align:center;font-weight: bold;background-color:#ddff99;'>Number</th>
                                <th style='font-size:8px;text-align:center;font-weight: bold;background-color:#ddff99;'>Avg Value</th>

							</tr>
							<tr>
								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='ATGEN' style='background-color:#7FFFD4;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>
								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='ATVAL' style='background-color:#7FFFD4;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>

								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='BTGEN' style='background-color:#FFE4C4;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>
								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='BTVAL' style='background-color:#FFE4C4;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>

								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='CTGEN' style='background-color:#87CEEB;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>
								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='CTVAL' style='background-color:#87CEEB;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>

								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='DTGEN' style='background-color:#FFFAF0;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>
								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='DTVAL' style='background-color:#FFFAF0;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>

								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='ETGEN' style='background-color:#FFB6C1;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>
								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='ETVAL' style='background-color:#FFB6C1;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>

								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='FTGEN' style='background-color:#FFF8DC;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>
								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='FTVAL' style='background-color:#FFF8DC;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>

								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='TGEN' style='background-color:#ddff99;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>
								<td style='font-size:8px;text-align:center;'><center><input class='form-control' type='number'  id='TVAL' style='background-color:#ddff99;font-size:9px;width:80px;height:18px;font-weight: bold;' value ='0' disabled></center></td>
							</tr>
							<tr>
								<td colspan='14' style='font-size:8px;text-align:center;'>
									<button id='sub_me' style='font-size:8px;text-align:center;' type='submit' class='btn btn-primary' disabled>Generate</button>
								</td>
							</tr>
						</table>
						</form>
						";
	print $inv_tab;

}
function dis_check($ord,$cat,$date,$grd,$oval){
	$col = array("A"=>"7FFFD4","B"=>"FFE4C4","C"=>"87CEEB","D"=>"FFFAF0","E"=>"FFB6C1","F"=>"FFF8DC");
	$cl  = $col[$cat];
	if($ord){
		if($ord == $grd){
			return "style='font-size:9px;width:40px;height:18px' value='0' disabled";
		}else{
			return "style='background-color:#$cl;font-size:9px;width:40px;height:18px' id='${date}_${cat}' name='${date}_${cat}' onchange='upd_inv(\"".($ord - $grd)."\",\"$cat\",\"$date\",\"${date}_${cat}\",\"$oval\")' value='0'";
		}
		
	}else{
		return "style='font-size:9px;width:40px;height:18px' value='0' disabled";
	}
}

function date_conv($date){
			$mnt   = array("01"=>"Jan","02"=>"Feb","03"=>"Mar","04"=>"Apr","05"=>"May","06"=>"Jun","07"=>"Jul","08"=>"Aug","09"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dec");
			$yr    = substr($date,0,4);
			$month = substr($date,4,2);
			$date = "$mnt[$month] $yr";
			return $date;
}

function date_conv_d($date){
    $mnt   = array("01"=>"JAN","02"=>"FEB","03"=>"MAR","04"=>"APR","05"=>"MAY","06"=>"JUN","07"=>"JUL","08"=>"AUG","09"=>"SEP","10"=>"OCT","11"=>"NOV","12"=>"DEC");
    $yr    = substr($date,0,4);
    $month = substr($date,4,-2);
    $day   = substr($date,strlen($date)-2);
    $date = "$day $mnt[$month] $yr";
    return $date;
}

?>
