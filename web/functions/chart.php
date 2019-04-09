<?php
    function prep_json(){
        $sim_id = $_GET['id'];
        $scrip  = $_GET['scrip'];
        $int    = 1; 
        if(isset($_GET['int'])){
            $int = $_GET['int'];
        }
        list($date,$sim_data,$dts,$entry) = get_isim_data($sim_id);
        $start     = strtotime(substr($date,0,-5)." 09:15");
        $end       = strtotime(substr($date,0,-5)." 15:30");
        list($ohlc_data,$max,$min) = get_ohlc_data($start,$end,strtolower($scrip),$int);
        $ohlc_keys = array_keys($ohlc_data);
        $cat_keys  = prep_cat_keys($ohlc_keys);
        $intd_tar  = get_intended($sim_id,$entry);
        #Preparing Chart
        $header = '{
            "chart": {
              "caption": "Analysis of Scrip '.$scrip.' For Simulation ID '.$sim_id.'",
              "subcaption": "Date:- '.substr($date,0,-5).'",
              "pyaxisname": "Price (Rs)",
              "theme": "fusion",
              "formatNumberScale": 0,
              "pYAxisMaxValue": "'.$max.'",
              "pYAxisMinValue": "'.$min.'",
            },';
        
        $cat = '"categories": [
                    {
                    "category": [';    
        foreach($cat_keys as $ck){
            $tm   = $ohlc_data[$ohlc_keys[$ck]]['T'];
            $cat .= '{
                        "label": "'.substr($tm,11,5).'",
                        "x": "'.$ck.'"
                    },';
        }
        $cat .= ' ]
            }
        ],';
        $dataset = get_dataset($ohlc_data);
        
        #TREND LINES
        if(isset($ohlc_data[$dts]['X'])){
            $ep = $ohlc_data[$dts]['X'];
        }else{
            $dts = get_dp($dts,$ohlc_keys);
            $ep = $ohlc_data[$dts]['X'];
        }
        
        $xtrend = '"trendlines": [
            {
              "line": [
                {
                    "startvalue": "'.$entry.'",
                    "color": "#5D62B5",
                    "displayvalue": "Entry Taken",
                    "showontop": "0"
                },';
        
        $ik_map = array("T1H" => "Calculated Target 1","T2H" => "Calculated Target 2","SQF" => "Square Off","SLH" => "Calculated Stop Loss");   
        $ik_col = array("T1H" => "#0099ff","T2H" => "#0099cc","SQF" => "#0000ff","SLH" => "#ff0066");    
         
        foreach($intd_tar as $ik => $iv){
            if(!isset($sim_data[$ik])){
                $xtrend .= '{
                    "startvalue": "'.$iv['A'].'",
                    "color": "'.$ik_col[$ik].'",
                    "displayvalue": "'.$ik_map[$ik].'",
                    "showontop": "0"
                },'; 
            }
        }        

        $trend = '"vtrendlines": [
            {
                "line": [{
                    "startvalue": "'.$ep.'",
                    "color": "#5D62B5",
                    "displayvalue": "Entry Taken",
                    "showontop": "0"
                },';
        
        $dv_map = array("T1H" => "Achieved Target 1","T2H" => "Achieved Target 2","SQF" => "Achieved Square Off","SLH" => "Stop Loss Hit");
        $dv_col = array("T1H" => "#00cc00","T2H" => "#009900","SQF" => "#0000ff","SLH" => "#ff3300");
        foreach($sim_data as $sk=>$sv){
            if(isset($ohlc_data[$sv['E']]['X'])){
                $dv   =  $ohlc_data[$sv['E']]['X'];
            }else{
                $sv['E']   =  get_dp($sv['E'],$ohlc_keys);
                $dv        =  $ohlc_data[$sv['E']]['X'];
            }
            
            $xp   =  $sv['XP'];
            $trend .= '{
                        "startvalue": "'.$dv.'",
                        "color": "'.$dv_col[$sk].'",
                        "displayvalue": "'.$dv_map[$sk].'",
                        "showontop": "0"
                    },';
            
            $xtrend .= '{
                        "startvalue": "'.$xp.'",
                        "color": "'.$dv_col[$sk].'",
                        "displayvalue": "'.$dv_map[$sk].'",
                        "showontop": "0"
                    },';        
        }    
        $xtrend .= ']}],';    
        $trend  .= ']
                }
            ]
        }';
        $json = $header.$cat.$dataset.$xtrend.$trend;
        return $json;
    }

    function get_dataset($ohlc_data){
        $ret = '"dataset": [
            {
              "data": [';
        $ctr = 1;
        foreach($ohlc_data as $k=>$v){
            #print "$k";
            #print_r($v);
            $ret .= '{
                        "date": "'.$v['T'].'",
                        "tooltext": "<b>'.substr($v['T'],11,8).'</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
                        "open": '.$v['O'].',
                        "high": '.$v['H'].',
                        "low": '.$v['L'].',
                        "close": '.$v['C'].',
                        "volume": "'.$v['V'].'",
                        "x": '.$ctr.'
                    },';
                    $ctr++;
            #break;
        }      

        $ret .= '    ]
                }
            ],';
            return $ret;
    }

    function get_isim_data($sim_id){
        $ret     = array();
        $date    = "";
        $entry   = 0;
        $sim_det = "SELECT * FROM stocki.sim_results WHERE sim_id ='$sim_id'";
        if(nr($sim_det)){
            $result = mysqli_query(create_con(), $sim_det);
            while($row = mysqli_fetch_assoc($result)){
                $date  = $row['start'];
                $entry = $row['entry_price'];
                $ret[$row['status']]['S']   = $row['start'];
                $ret[$row['status']]['E']   = $row['end'];
                $ret[$row['status']]['EP']  = $row['entry_price'];
                $ret[$row['status']]['XP']  = $row['exit_price'];
                $ret[$row['status']]['V']   = $row['volume'];
                $ret[$row['status']]['R']   = $row['result'];
            }
        }
        return array(date('d-m-Y H:i',$date),$ret,$date,$entry);   
    }

    function get_ohlc_data($start,$end,$scrip,$int){
        $ret     = array();
        $query   = "SELECT * FROM stocki.$scrip WHERE `timestamp` BETWEEN $start and $end";
        $ctr     = 1;
        $max     = 0;
        $min     = 99999999;
        if(nr($query)){
            $result = mysqli_query(create_con(), $query);
            while($row = mysqli_fetch_assoc($result)){
                $ret[$row['timestamp']]['T'] = $row['time'];
                $ret[$row['timestamp']]['O'] = $row['open'];
                $ret[$row['timestamp']]['L'] = $row['low'];
                $ret[$row['timestamp']]['H'] = $row['high'];
                $ret[$row['timestamp']]['C'] = $row['close'];
                $ret[$row['timestamp']]['V'] = $row['volume'];
                $ret[$row['timestamp']]['X'] = $ctr;
                $ctr++;

                if($max < $row['high']){
                    $max = $row['high'];
                }

                if($min > $row['low']){
                    $min = $row['low'];
                }
            }
        }
           
        if($int > 1){
            $chunks = array_chunk($ret,$int,2);
            $ck_ret = array();
            $x      = 1;
            $lim    = ($int-1);
            foreach($chunks as $ck){
                $ks     = array_keys($ck);
                $lim    = (count($ck) -1);
                $ck_ret[$ks[$lim]]['T'] = $ck[$ks[$lim]]['T'];
                $ck_ret[$ks[$lim]]['C'] = $ck[$ks[$lim]]['C'];
                $ck_ret[$ks[$lim]]['O'] = $ck[$ks[0]]['O'];
                $ck_ret[$ks[$lim]]['X'] = $x;
                $ck_ret[$ks[$lim]]['V'] = 0;
                $kmap = array('H' => array(),"L" => array());
                foreach($ks as $k){
                     array_push($kmap['H'],$ck[$k]['H']);
                     array_push($kmap['L'],$ck[$k]['L']);
                     $ck_ret[$ks[$lim]]['V'] += $ck[$k]['V'];
                }
                $ck_ret[$ks[$lim]]['H'] = max($kmap['H']);
                $ck_ret[$ks[$lim]]['L'] = min($kmap['L']);
                $x++;
            }
            $ret = $ck_ret;
        }

        $ret  = convert_to_heikinashi($ret);

        $max += $max*0.003;
        $min -= $min*0.003;
        return array($ret,round($max),round($min));
    }

    function prep_cat_keys($ts_keys){
        $delim = round((count($ts_keys)/30));
        $max   = (count($ts_keys) - 1);
        $cats  = array(0);
        $ctr   = 0;
        while($ctr < $max){
            $ctr +=$delim;
            if($ctr < $max){
                array_push($cats,$ctr);
            }
        }
        array_push($cats,$max);
        return $cats;
    }

    function get_intended($sim_id,$entry){
        $query   = "SELECT * FROM stocki.sim_tracker WHERE sim_id='$sim_id'";
        $int_map = array(); 
        if(nr($query)){
            $result = mysqli_query(create_con(), $query);
            while($row = mysqli_fetch_assoc($result)){
                $int_map['T1H']['P'] = $row['target_1'];
                $int_map['T2H']['P'] = $row['target_2'];
                $int_map['SLH']['P'] = $row['stop_loss'];

                $int_map['T1H']['E'] = $entry;
                $int_map['T2H']['E'] = $entry;
                $int_map['SLH']['E'] = $entry;

                if($row['transaction'] == "SELL"){
                    foreach(array("T1H","T2H") as $p){
                        $int_map[$p]['A'] = ($entry - ($entry * $int_map[$p]['P']));
                    }
                    $int_map['SLH']['A'] = ($entry + ($entry * $int_map['SLH']['P']));
                }elseif($row['transaction'] == "BUY"){
                    foreach(array("T1H","T2H") as $p){
                        $int_map[$p]['A'] = ($entry + ($entry * $int_map[$p]['P']));
                    }
                    $int_map['SLH']['A'] = ($entry - ($entry * $int_map['SLH']['P']));
                }
            }
        }
        return $int_map;
    }

    function convert_to_heikinashi($ret){
        $ts_keys = array_keys($ret);
        $ph = $ret[$ts_keys[0]]['H'];
        $pl = $ret[$ts_keys[0]]['L'];
        $po = $ret[$ts_keys[0]]['O'];
        $pc = $ret[$ts_keys[0]]['C'];
        array_shift($ts_keys);
        foreach($ts_keys as $tk){
            $open  = $ret[$tk]['O'];
            $high  = $ret[$tk]['H'];
            $close = $ret[$tk]['C'];
            $low   = $ret[$tk]['L'];

            $ho    = round(($po+$pc)/2,2);
            $hc    = round(($open+$high+$low+$close)/4,2);
            $hh    = max($high,$ho,$hc);
            $hl    = min($low,$ho,$hc);
            
            $ret[$tk]['O'] = $ho;
            $ret[$tk]['L'] = $hl;
            $ret[$tk]['H'] = $hh;
            $ret[$tk]['C'] = $hc;
           
            $po = $ho;
            $ph = $hh;
            $pc = $hc;
            $pl - $hl;
            
        }

        return $ret;
    }

    function get_dp($ep,$okeys){
        $ret  = 0;
        foreach($okeys as $ok){
            if($ok > $ep){
                $ret = $ok;
                break;
            }
        }
        return $ret;
    }

    function get_sim_details(){
        list($date,$sim_data,$dts,$entry) = get_isim_data($_GET['id']);
        $ret = "<br><table style='width:50%;vertical-align:middle;' class='table table-hover'>
                    <tr>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' colspan='10'>Simulation Details</th>
                    </tr>
                    <tr>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >Scrip</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >Date</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >Type</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >Transaction</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >Capital</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >T1 Volume</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >T1 Target</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >T2 Volume</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >T2 Target</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >Stop Loss</th>
                    </tr>";
        $targets = array();
        $query = "SELECT * FROM stocki.sim_tracker WHERE sim_id='$_GET[id]'";
        if(nr($query)){
            $result = mysqli_query(create_con(), $query);
            while($row = mysqli_fetch_assoc($result)){
                $ret .= "<tr>
                            <td style='font-size:12px;text-align:center;font-weight:bold;' class='Success'>$row[scrip]</th>
                            <td style='font-size:12px;text-align:center;font-weight:bold;' class='Success'>".substr($date,0,-5)."</th>
                            <td style='font-size:12px;text-align:center;font-weight:bold;' class='Success'>$row[type]</th>
                            <td style='font-size:12px;text-align:center;font-weight:bold;' class='Success'>$row[transaction]</th>
                            <td style='font-size:12px;text-align:center;font-weight:bold;' class='Success'>$row[capital]</th>
                            <td style='font-size:12px;text-align:center;font-weight:bold;' class='Success'>$row[target1_vol]</th>
                            <td style='font-size:12px;text-align:center;font-weight:bold;' class='Success'>$row[target_1]%</th>
                            <td style='font-size:12px;text-align:center;font-weight:bold;' class='Success'>$row[target2_vol]</th>
                            <td style='font-size:12px;text-align:center;font-weight:bold;' class='Success'>$row[target_2]%</th>
                            <td style='font-size:12px;text-align:center;font-weight:bold;' class='Success'>$row[stop_loss]%</th>
                        </tr>";
                $targets['T1H'] = $row['target_1'];
                $targets['T2H'] = $row['target_2'];
                $targets['SLH'] = $row['stop_loss'];
                $targets['SQF'] = 0;
                $targets['TRN'] = $row['transaction'];
            }
        }
        
        $ret .= "</table>";  
        $ret .= "<table style='width:70%;vertical-align:middle;' class='table table-hover'>
                    <tr>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' colspan='14'>Simulation Results</th>
                    </tr>
                    <tr>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >Entry Time</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' >Entry Price</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' colspan='3' class='success'>Target 1</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' colspan='3' class='warning'>Target 2</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' colspan='3' class='danger'>Stop Loss</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' colspan='3' class='info'>Square Off</th>
                    </tr>
                    <tr>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' ></th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' ></th>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='success'>Hit</td>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='success'>Time</td>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='success'>Value</td>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' class='warning'>Hit</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' class='warning'>Time</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' class='warning'>Value</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' class='danger'>Hit</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' class='danger'>Time</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' class='danger'>Value</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' class='info'>Hit</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' class='info'>Time</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;' class='info'>Value</th>
                    </tr>
                    <tr>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='active'>$date</td>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='active'>$entry</td>"; 
        foreach(array("T1H" => "success","T2H"  => "warning","SLH"  => "danger","SQF"=>"info") as $tp=>$cv){
           if(isset($sim_data[$tp])){
                $ret .= "<td style='font-size:12px;text-align:center;font-weight:bold;' class='$cv'>YES</td>";
                $ret .= "<td style='font-size:12px;text-align:center;font-weight:bold;' class='$cv'>".date("H:i",$sim_data[$tp]['E'])."</td>";
                $ret .= "<td style='font-size:12px;text-align:center;font-weight:bold;' class='$cv'>".$sim_data[$tp]['XP']."</td>";
           }else{
                $ret .= "<td style='font-size:12px;text-align:center;font-weight:bold;' class='$cv'>NO</td>";
                $ret .= "<td style='font-size:12px;text-align:center;font-weight:bold;' class='$cv'>-</td>";
                $val  = 0;
                if($targets['TRN'] == "BUY"){
                    $val = round(($entry + ($entry * $targets[$tp])),2);
                    if($tp == "SLH"){
                        $val = round(($entry - ($entry * $targets[$tp])),2);
                    }
                }elseif($targets['TRN'] == "SELL"){
                    $val = round(($entry - ($entry * $targets[$tp])),2);
                    if($tp == "SLH"){
                        $val = round(($entry + ($entry * $targets[$tp])),2);
                    }
                }
                #$ret .= "<td style='font-size:12px;text-align:center;font-weight:bold;' class='$cv'>".$entry + ($entry * $targets[$tp])."</td>";
                $ret .= "<td style='font-size:12px;text-align:center;font-weight:bold;' class='$cv'>$val</td>";
           }
            
        }
        $ret .= "</th></table>";   
        print $ret;
    }

?>