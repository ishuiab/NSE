<?php
require_once("config/config.php");
extract($_GET);
chk_inv($id);
$st  = "00000";
$tax_rate = 5.5;
$name   = "";
$total  =  0;
$date   = "";
$mail   = "";
$phone  = "";
$addr   = "";
$type   = "";
$ship   =  0;
$inv    = get_inv_no();
$inv_no = "SKA".substr($st,0,strlen($inv)).$inv;
$items = array();
$inv_path = "";

function chk_inv($id){
    $qry = "SELECT INV_NUM FROM lucifer.inv_tracker WHERE id = '$id'";
    $NUM = select_single($qry,"INV_NUM");
    $st  = "00000";
    if($NUM != ""){
        print "SKA".substr($st,0,strlen($NUM)).$NUM;
        exit();
    }
}

function get_inv_no(){

    $qry = "SELECT INV_NUM FROM lucifer.inv_tracker ORDER BY INV_NUM DESC LIMIT 1";
    $inv = (select_single($qry,"INV_NUM")+1);
    return $inv;
}

function date_conv($date){
    $mnt   = array("01"=>"JAN","02"=>"FEB","03"=>"MAR","04"=>"APR","05"=>"MAY","06"=>"JUN","07"=>"JUL","08"=>"AUG","09"=>"SEP","10"=>"OCT","11"=>"NOV","12"=>"DEC");
    $yr    = substr($date,0,4);
    $month = substr($date,4,-2);
    $day   = substr($date,strlen($date)-2);
    $date = "$day $mnt[$month] $yr";
    return $date;
}

$query = "SELECT * FROM lucifer.sales WHERE order_id ='$id'";
$result = mysqli_query(create_con(), $query);
		while($row = mysqli_fetch_assoc($result)){
				$name   = $row['cust_name'];
                $total += $row['total'];
                $date   = $row['date'];
                $mail   = $row['cust_email'];
                $phone  = $row['cust_phone'];
                $addr   = $row['cust_add'];
                $type   = $row['type'];
                $ship  += $row['shipping'];
                $row['price'] = round((($row['price']/ (100 + $tax_rate))*100),2);
                $items[$row['item_id']]['desc']  = $row['item_descr'];
                $items[$row['item_id']]['qty']   = $row['qty'];
                $items[$row['item_id']]['price'] = $row['price'];
                $items[$row['item_id']]['ship']  = $row['shipping'];
        }

$addr   = explode(" ",$addr);
$md     = $date;
$date   = date_conv($date);
$ship   = round((($ship/ (100 + $tax_rate))*100),2);
$stotal = round((($total/ (100 + $tax_rate))*100),2);
$ttotal = ($total - $stotal);



        $inv_str = '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Invoice Number '. $inv_no.'</title>
  </head>
  <body>
    <header class="clearfix">
      <div id="logo">
        <img src="../logo.png">
      </div>
      <div id="company">
        <h2 class="name">Starlight Innovations</h2>
        <div>#84, 1st Main, 19th Cross, Agrahara layout</div>
         <div>Yelahanka, Bangalore - 560045</div>
        <div>(91) 9964419486</div>
        <div><a href="mailto:skastores@gmail.com">skastores@gmail.com</a></div>
      </div>
      <link rel="stylesheet" href="../style.css" media="all" />
    </header>
    <main>
      <div id="details" class="clearfix">
        <div id="client">
          <div class="to">INVOICE TO:</div>
          <h2 class="name">'.strtoupper($name).'</h2>
          <div class="address">';
          $ctr =0;
          foreach($addr as $ad){
            $inv_str .= "$ad ";
            if($ctr == 6){
                $inv_str .= "<br/>";
                $ctr = 0;
            }
            $ctr++;
          }
          $inv_str .='</div>
          <div class="email"><a href="mailto:'.$mail.'">E-mail: - '.$mail.'</a></div>
          <div class="email"><a href="mailto:'.$mail.'">Phone: - '.$phone.'</a></div>
          </div>
        <div id="invoice">
          <h1>INVOICE '.$inv_no.'</h1>
          <div class="date">Date of Invoice: '.$date.'</div>
          <div class="date">Due Date: '.$date.'</div>
        </div>
        </div>
        <table border="0" cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th class="no">#</th>
            <th class="desc">DESCRIPTION</th>
            <th class="unit">UNIT PRICE</th>
            <th class="qty">QUANTITY</th>
            <th class="total">TOTAL</th>
          </tr>
        </thead>
        <tbody>';
        $ctr = 1;
       foreach($items as $key=>$value){
            $inv_str .= "<tr>
                            <td style='color: #FFFFFF;font-size: 1.6em;background: #57B223;'class='no'>$ctr</td>
                            <td class='desc'>".$items[$key]['desc']."</td>
                            <td class='unit'> &#x20B9; ".$items[$key]['price']."</td>
                            <td class='qty'>".$items[$key]['qty']."</td>
                            <td class='qty'> &#x20B9; ".($items[$key]['price'] * $items[$key]['qty'])."</td>
                         </tr>";
            $ctr++;
       }
         $inv_str .= "<tr>
                            <td class='no'>$ctr</td>
                            <td class='desc'>Shipping Charges</td>
                            <td class='unit'> &#x20B9; ".$ship."</td>
                            <td class='qty'>1</td>
                            <td class='qty'> &#x20B9; $ship</td>
                         </tr>";
        $inv_str .= "</tbody>
                     <tfoot>
                         <tr>
                            <td colspan='2'></td>
                            <td colspan='2'>SUB TOTAL</td>
                            <td> &#x20B9; $stotal</td>
                        </tr>
                        <tr>
                        <td colspan='2'></td>
                            <td colspan='2'>SALES TAX 5.5%</td>
                            <td> &#x20B9; $ttotal</td>
                        </tr>
                        <tr>
                            <td colspan='2'></td>
                            <td colspan='2'>GRAND TOTAL</td>
                            <td> &#x20B9;  $total</td>
                        </tr>

                    </tfoot>
                </table>
                <div id='thanks'>Thank you!</div>
      <div id='notices'>
        <div>NOTICE:</div>
        <div class='notice'>
                Computer Generated Invoice Does Not Required Seal And Signature
        </div>
      </div>
      </br>
      <center><button onclick='window.print();'>Print Invoice</button></center>
    </main>
  </body>
</html>";
$file = fopen("inv\\$inv_no".".html","w");
        fwrite($file,$inv_str);
        fclose($file);

if(file_exists("inv\\$inv_no".".html")){
    insert_mysql("INSERT INTO lucifer.inv_tracker VALUES ($inv,$id,'$md')");
    print $inv_no;
}else{
    print 1;
}

?>
