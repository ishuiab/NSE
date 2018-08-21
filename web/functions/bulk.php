<?php
function get_sel(){
    $ret = "<table style='width:50%;vertical-align:middle;' class='table table-bordered'>
                <tr>
                    <th style='font-size:12px;text-align:center;font-weight: bold;'>Strategy</th>
                    <th style='font-size:12px;text-align:center;font-weight: bold;'>Scrip</th>
                    <th style='font-size:12px;text-align:center;font-weight: bold;'>Bulk ID</th>
                    <th style='font-size:12px;text-align:center;font-weight: bold;'>Search</th>
                <tr>
                <tr>
                    <td>
                        <center>
                            <select class='form-control' id='strategy' onchange='getscrips(this)'>"
                                .get_uniq_strategy().
                            "</select>
                        </center>    
                    </td>
                    <td>
                        <center>
                            <select class='form-control' id='scrip' disabled onchange='getbulkids(this)'>
                                <option>Select</option>
                            </select>
                        </center>
                    </td>
                    <td>
                        <center>
                            <select class='form-control'  id='bulk_id' disabled>
                                <option>Select</option>
                            </select>
                        </center>
                    </td>
                    <td>
                        <center>
                            <button type='button' style='font-size:12px' class='btn btn-warning .btn-sm' onclick='fetch_info()'>Fetch Info</button>
                        </center>
                    </td>
                </tr>    
            </table>";

    print $ret."\n";
}

function get_uniq_strategy(){
    $query      = "SELECT DISTINCT strategy_name FROM stocki.strategy";
    $ret        = "<option value='select'>Select</option>";
    $strategies = select_array($query,"strategy_name");
    foreach($strategies as $stat){
        $ret .= "<option value='$stat'>$stat</option>";
    }

    return $ret;
}

function get_scrips($star){
    $query  = "select DISTINCT scrip FROM stocki.sim_tracker WHERE strategy_id IN (select DISTINCT strategy_id from stocki.strategy WHERE strategy_name='$star')";
    $scrips =  select_array($query,"scrip");
    $ret    = "<option value='select'>Select</option>";
    foreach($scrips as $scp){
        $ret .= "<option value='$scp'>$scp</option>";
    }
    return $ret;
}

function get_bulk_ids($scrip){
    $query = "SELECT DISTINCT bulk_id from stocki.strategy WHERE params LIKE '%: ''$scrip''%'";
    $bids  = select_array($query,"bulk_id");
    $ret    = "<option value='select'>Select</option>";
    foreach($bids as $bd){
        $ret .= "<option value='$bd'>$bd</option>";
    }
    return $ret;
}

function get_bulk_stat($bulk_id){
    $query     = "SELECT  strategy_id,params from stocki.strategy WHERE bulk_id='$bulk_id'";
    $strat_det = sql_hash($query,"strategy_id","params");
    $disp_stat = gen_strat_disp($bulk_id);
    $strategy  = select_single("SELECT  strategy_name from stocki.strategy WHERE bulk_id='$bulk_id' LIMIT 1","strategy_name");
    
    $ret = "<center>
                <table style='width:90%;vertical-align:middle;' class='table table-bordered'>
                    <tr>
                        <th colspan='3' style='font-size:12px;text-align:center;font-weight: bold;'>Strategy Details</th>    
                    </tr>
                    <tr>
                        <th style='font-size:12px;text-align:center;font-weight: bold;'>Strategy</th>
                        <th style='font-size:12px;text-align:center;font-weight: bold;'>Bulk ID</th>
                        <th style='font-size:12px;text-align:center;font-weight: bold;'>No Of Params</th>
                    </tr>
                    <tr>
                        <td style='font-size:12px;' class='active'>
                            <center>
                                $strategy
                            </center>
                        </td>
                        <td style='font-size:12px;' class='active'>
                            <center>
                                $bulk_id
                            </center>
                        </td>
                        <td style='font-size:12px;' class='active'>
                            <center>"
                             .count($strat_det).  
                            "</center>
                        </td>
                    </tr></table>";
    $ret .= '<ul class="nav nav-pills">
                <li class="active"><a data-toggle="pill" href="#TOP_W_S">Top Strategies By Wins For Short</a></li>
                <li><a data-toggle="pill" href="#TOP_W_B">Top Strategies By Wins For Long</a></li>
                <li><a data-toggle="pill" href="#TOP_P_S">Top Strategies By Profit For Short</a></li>
                <li><a data-toggle="pill" href="#TOP_P_B">Top Strategies By Profit For Long</a></li>
            </ul>
    
            <div class="tab-content">
                <div id="TOP_W_S" class="tab-pane fade in active">
                '.
                    $disp_stat['WS']
                .'
                </div>
                <div id="TOP_W_B" class="tab-pane fade">
                '.
                    $disp_stat['WB']
                .'
                </div>
                <div id="TOP_P_S" class="tab-pane fade">
                '.
                    $disp_stat['PS']
                .'
                </div>
                <div id="TOP_P_B" class="tab-pane fade">
                '.
                    $disp_stat['PB']
                .'
                </div>
            </div>';

    $ret .= "   
            </center>";            
    return $ret;
}

function gen_strat_disp($bulk_id){
    #Get All Startegies for a given bulk ID
    $query     = "SELECT strategy_id FROM stocki.strategy  WHERE bulk_id='$bulk_id' AND strategy_id IN (SELECT strategy_id FROM stocki.sim_tracker)";
    $uniq_star = select_array($query,"strategy_id");
    $sim_query = "SELECT * FROM stocki.sim_tracker WHERE strategy_id IN ('".implode("','",$uniq_star)."') AND type='ACT'";
    $star_map  = array();
    $sim_ids   = "";
    if(nr($sim_query)){
        $result = mysqli_query(create_con(), $sim_query);
		while($row = mysqli_fetch_assoc($result)){
            $star_id  = $row['strategy_id'];
            $sim_id   = $row['sim_id'];
            $t1       = $row['target1_vol'];
            $t2       = $row['target2_vol'];
            $trn      = $row['transaction'];
            $sim_ids .= $sim_id."','";
            if(!isset($star_map[$star_id])){
                $star_map[$star_id] = array();
                $star_map[$star_id]['RES']['BUY']['WP']  = 0;
                $star_map[$star_id]['RES']['SELL']['WP'] = 0;
                $star_map[$star_id]['RES']['BUY']['PR']  = 0;
                $star_map[$star_id]['RES']['SELL']['PR'] = 0;
                $star_map[$star_id]['RES']['BUY']['C']   = 0;
                $star_map[$star_id]['RES']['BUY']['W']   = 0;
                $star_map[$star_id]['RES']['SELL']['C']  = 0;
                $star_map[$star_id]['RES']['SELL']['W']  = 0;
            }
            #Assignment
            $star_map[$star_id][$sim_id]['TR'] = $trn;
            $star_map[$star_id][$sim_id]['T1'] = $t1;
            $star_map[$star_id][$sim_id]['T2'] = $t2;
            $star_map[$star_id][$sim_id]['PR'] = 0;
            $star_map[$star_id]['RES'][$trn]['C']++;
        }
    }
    #Cache Sim Details
    $sim_det   = "SELECT sim_id,result FROM stocki.sim_results WHERE sim_id IN ('$sim_ids')";
    $sim_hash  = array();
    if(nr($sim_query)){
        $result = mysqli_query(create_con(), $sim_det);
		while($row = mysqli_fetch_assoc($result)){
            $sid = $row['sim_id'];
            $prf = $row['result'];
            if(!isset($sim_hash[$sid])){
                $sim_hash[$sid] = 0;
            }
            $sim_hash[$sid] += $prf;
        }
    }
    #Calculate the Data Here
    foreach($star_map as $star => $sval){
        $skey = array_keys($sval);
        $wpb   = 0;
        $wps   = 0;
        $prb   = 0;
        $prs   = 0;
        $wpcb  = 0.0;
        $wpcs  = 0.0;
        foreach($skey as $sim){
            if(isset($sim_hash[$sim])){
                $spr = $sim_hash[$sim];
                $star_map[$star][$sim]['PR'] = $spr; 
                if ($star_map[$star][$sim]['TR'] == "BUY"){
                    $prb += $spr;
                    if($spr > 0){
                        $wpb++;
                    }
                } if ($star_map[$star][$sim]['TR'] == "SELL"){
                    $prs += $spr;
                    if($spr > 0){
                        $wps++;
                    }
                } 
            }
        }
        $star_map[$star]['RES']['BUY']['W']   = $wpb;
        $star_map[$star]['RES']['SELL']['W']  = $wps;
        $star_map[$star]['RES']['BUY']['PR']  = $prb;
        $star_map[$star]['RES']['SELL']['PR'] = $prs;
        
        if($wpb){
            $wpcb = round(($wpb/$star_map[$star]['RES']['BUY']['C'])*100,2);
        }
        if($wps){
            $wpcs = round(($wps/$star_map[$star]['RES']['SELL']['C'])*100,2);
        }
        $star_map[$star]['RES']['BUY']['WP']   = $wpcb;
        $star_map[$star]['RES']['SELL']['WP']  = $wpcs;
    }
    $disp_data = calc_disp($star_map,20);
    
    $ret_data = array();
    $trn_map  = array();
    $trn_map['WS'] = "SELL";
    $trn_map['WB'] = "BUY";
    $trn_map['PS'] = "SELL";
    $trn_map['PB'] = "BUY";
    foreach($disp_data as $dat=>$sval){
        $ret_data[$dat] = "<br><table style='width:80%;vertical-align:middle;' class='table table-hover'>
                                <tr>
                                    <th colspan='8' style='font-size:12px;text-align:center;font-weight:bold;'>Strategy Details</th>    
                                </tr>
                                <tr>
                                    <th style='font-size:12px;text-align:center;font-weight:bold;'>SL No</th>
                                    <th style='font-size:12px;text-align:center;font-weight:bold;'>Strategy ID</th>
                                    <th style='font-size:12px;text-align:center;font-weight:bold;'>Simulations</th>
                                    <th style='font-size:12px;text-align:center;font-weight:bold;'>Wins</th>
                                    <th style='font-size:12px;text-align:center;font-weight:bold;'>Losses</th>
                                    <th style='font-size:12px;text-align:center;font-weight:bold;'>Success Rate</th>
                                    <th style='font-size:12px;text-align:center;font-weight:bold;'>Profit</th>
                                    <th style='font-size:12px;text-align:center;font-weight:bold;'>Summary</th>
                                    
                                </tr>";
        $sl = 1;
        arsort($sval);                        
        foreach($sval as $st=>$sv){
            
            $ret_data[$dat] .= "<tr>
                                    <td style='font-size:11px;text-align:center;font-weight:bold;'><b>$sl</b></td>
                                    <td style='font-size:11px;text-align:center;font-weight:bold;' class='warning'>$st</td>
                                    <td style='font-size:11px;text-align:center;font-weight:bold;' class='active'>".$star_map[$st]['RES'][$trn_map[$dat]]['C']."</td>
                                    <td style='font-size:11px;text-align:center;font-weight:bold;' class='success'>".$star_map[$st]['RES'][$trn_map[$dat]]['W']."</td>
                                    <td style='font-size:11px;text-align:center;font-weight:bold;' class='danger'> ".($star_map[$st]['RES'][$trn_map[$dat]]['C'] - $star_map[$st]['RES'][$trn_map[$dat]]['W'])."</td>
                                    <td style='font-size:11px;text-align:center;font-weight:bold;' class='active'> ".$star_map[$st]['RES'][$trn_map[$dat]]['WP']."%</td>
                                    <td style='font-size:11px;text-align:center;font-weight:bold;' class='warning'>".$star_map[$st]['RES'][$trn_map[$dat]]['PR']."</td>
                                    <td style='font-size:11px;text-align:center;font-weight:bold;' class='warning'>
                                        <button type='button' onclick='view_data(\"$st\",\"$trn_map[$dat]\")' style='font-size:10px;width:100px;' class='btn btn-warning .btn-sm'>View Summary</button>
                                    </td>
                                </tr>";
            $sl++;
        }
        $ret_data[$dat] .= "</tr>
                                </table>";    
    }

    return $ret_data;
}

function calc_disp($star_map,$lim){
    $ret_map = array();
    $ret_map['WS'] = array();
    $ret_map['WB'] = array();
    $ret_map['PS'] = array();
    $ret_map['PB'] = array();

    #print_r($star_map);

    foreach($star_map as $star => $res){
        if(count($ret_map['WS']) < $lim){
            $val  = $star_map[$star]['RES']['SELL']['WP'];
            $ret_map['WS'][$star] = $val;
        }else{
            $val  = $star_map[$star]['RES']['SELL']['WP'];
            $ret_map['WS'] = check_best($ret_map['WS'],$star,$val);
        }

        if(count($ret_map['WB']) < $lim){
            $val  = $star_map[$star]['RES']['BUY']['WP'];
            $ret_map['WB'][$star] = $val;
        }else{
            $val  = $star_map[$star]['RES']['BUY']['WP'];
            $ret_map['WB'] = check_best($ret_map['WB'],$star,$val);

        }

        if(count($ret_map['PS']) < $lim){
            $val  = $star_map[$star]['RES']['SELL']['PR'];
            $ret_map['PS'][$star] = $val;
        }else{
            $val  = $star_map[$star]['RES']['SELL']['PR'];
            $ret_map['PS'] = check_best($ret_map['PS'],$star,$val);
        }

        if(count($ret_map['PB']) < $lim){
            $val  = $star_map[$star]['RES']['BUY']['PR'];
            $ret_map['PB'][$star] = $val;
        }else{
            $val  = $star_map[$star]['RES']['BUY']['PR'];
            $ret_map['PB'] = check_best($ret_map['PB'],$star,$val);
        }
    }

    return $ret_map;

}

function check_best($check,$star,$val){
    $can   = $star;
    $least = $val;
    foreach($check as $ck=>$vl){
        if($vl < $least){
            $least = $vl;
            $can   = $ck;
        }
    }
    $check[$star] = $val;
    unset($check[$can]);
    arsort($check);
    return $check;
}

function get_star_det($star_id,$trn){
    $sim_data = get_sim_data($star_id,$trn);
    $ret  =  "<br><center><table style='width:80%;vertical-align:middle;' class='table table-hover'>
                    <tr>
                        <th colspan='8' style='font-size:12px;text-align:center;font-weight:bold;'>Simulation Details For Scrip $sim_data[scrip] With Strategy $star_id For $trn Transaction</th>
                    </tr>
                    <tr>
                        <th style='font-size:12px;text-align:center;font-weight:bold;'>Sim Type</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;'>Entry Capital</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;'>Simulations</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;'>Wins</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;'>Losses</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;'>Success Rate</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;'>Profit</th>
                        <th style='font-size:12px;text-align:center;font-weight:bold;'>Exit Capital</th>
                    </tr>";
    ksort($sim_data['SUM']);
    $cmp = array("RAN" => "danger","ACT"=>"success");
    foreach($sim_data['SUM'] as $st=> $sv){
        $ret .= "<tr>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='$cmp[$st]'>$st</td>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='$cmp[$st]'>$sv[EC]</td>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='$cmp[$st]'>$sv[S]</td>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='$cmp[$st]'>$sv[W]</td>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='$cmp[$st]'>$sv[L]</td>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='$cmp[$st]'>$sv[SR]%</td>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='$cmp[$st]'>$sv[P]</td>
                        <td style='font-size:12px;text-align:center;font-weight:bold;' class='$cmp[$st]'>$sv[XC]</td>
                 </tr>";
    }       
    $ret .=  "</table>";
    $ret  .=  "<br><table style='width:98%;vertical-align:middle;' class='table table-hover'>
                            <tr>
                                <th colspan='11' style='font-size:12px;text-align:center;font-weight:bold;'>Individual Simulation Details For Actual Simulations</th>
                            </tr>
                            <tr>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Sl No</th>
                               
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Date</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Entry</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Exit</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Volume</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Entry Price</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>T1 Details</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>T2 Details</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>SL Details</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>SQ Details</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Profit</th>
                            </tr>";
    $ctr = 1;
    arsort($sim_data['ACT']['S']);
    foreach($sim_data['ACT']['S'] as $rd=>$rv){
        $vlm   = ($sim_data['ACT']['D'][$rd]['target1_vol']+$sim_data['ACT']['D'][$rd]['target2_vol']);
        $prf   = $sim_data['ACT']['D'][$rd]['PF'];
        $st    = $sim_data['ACT']['D'][$rd]['ST'];
        $ep    = $sim_data['ACT']['D'][$rd]['EP'];
        $std   = array(
                        "T1"=>"",
                        "T2"=>"",
                        "SL"=>"",
                        "SQ"=>"",
                      );
        foreach(array("T1","T2","SL","SQ") as $t){
            if($st[$t]['V']){
                $std[$t] = $st[$t]['V']." ".$st[$t]['X']." ".date("H:i",$st[$t]['F']); 
            }else{
                $std[$t] = "NONE";
            }
        }
        $cls   = "danger";
        if($prf > 0){
            $cls   = "success";
        }
        $ret  .=  "<tr>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='warning'>$ctr</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".date("d/M/Y",$sim_data['ACT']['D'][$rd]['start_time'])."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".date("H:i",$sim_data['ACT']['D'][$rd]['start_time'])."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".date("H:i",$sim_data['ACT']['D'][$rd]['end_time'])."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>$vlm</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>$ep</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".$std['T1']."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".$std['T2']."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".$std['SL']."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".$std['SQ']."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>$prf</td>
                  </tr>";
        $ctr++;          
    }
    $ret  .=  "</table>";
    $ret  .=  "<br><table style='width:98%;vertical-align:middle;' class='table table-hover'>
                            <tr>
                                <th colspan='11' style='font-size:12px;text-align:center;font-weight:bold;'>Individual Simulation Details For Random Simulations</th>
                            </tr>
                            <tr>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Sl No</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Sim ID</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Date</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Entry Time</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Exit Time</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Volume</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>T1 Details</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>T2 Details</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>SL Details</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>SQ Details</th>
                                <th style='font-size:12px;text-align:center;font-weight:bold;'>Profit</th>
                            </tr>";
    $ctr = 1;
    arsort($sim_data['RAN']['S']);
    foreach($sim_data['RAN']['S'] as $rd=>$rv){
        $vlm   = ($sim_data['RAN']['D'][$rd]['target1_vol']+$sim_data['RAN']['D'][$rd]['target2_vol']);
        $prf   = $sim_data['RAN']['D'][$rd]['PF'];
        $st    = $sim_data['RAN']['D'][$rd]['ST'];
        $ep    = $sim_data['RAN']['D'][$rd]['EP'];
        $std   = array(
                        "T1"=>"",
                        "T2"=>"",
                        "SL"=>"",
                        "SQ"=>"",
                      );
        foreach(array("T1","T2","SL","SQ") as $t){
            if($st[$t]['V']){
                $std[$t] = $st[$t]['V']." ".$st[$t]['X']." ".date("H:i",$st[$t]['F']); 
            }else{
                $std[$t] = "NONE";
            }
        }
        $cls   = "danger";
        if($prf > 0){
            $cls   = "success";
        }
        $ret  .=  "<tr>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='warning'>$ctr</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".date("d/M/Y",$sim_data['RAN']['D'][$rd]['start_time'])."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".date("H:i",$sim_data['RAN']['D'][$rd]['start_time'])."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".date("H:i",$sim_data['RAN']['D'][$rd]['end_time'])."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>$vlm</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>$ep</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".$std['T1']."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".$std['T2']."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".$std['SL']."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>".$std['SQ']."</td>
                        <td style='font-size:11px;text-align:center;font-weight:bold;' class='$cls'>$prf</td>
                  </tr>";
        $ctr++;          
    }
    $ret .=  "</table></center>";
    #print_r($sim_data['ACT']['S']);
    return $ret;
}

function get_sim_data($star_id,$trn){
    $s_query = "SELECT * FROM stocki.sim_tracker WHERE strategy_id='$star_id' AND transaction='$trn'";
    $ret_map = array();
    $sim_map = array();
    $sim_ids = "";
    if(nr($s_query)){
        $result = mysqli_query(create_con(), $s_query);
		while($row = mysqli_fetch_assoc($result)){
            $ret_map['scrip'] = $row['scrip'];
            $sim_map[$row['sim_id']] = $row;
            $sim_map[$row['sim_id']]['end_time'] = 0;
            $sim_map[$row['sim_id']]['ST'] = array();
            $sim_map[$row['sim_id']]['PF'] = 0;
            $sim_map[$row['sim_id']]['EP'] = 0;
            $sim_map[$row['sim_id']]['ST']['T1']['V'] = 0;
            $sim_map[$row['sim_id']]['ST']['T2']['V'] = 0;
            $sim_map[$row['sim_id']]['ST']['SL']['V'] = 0;
            $sim_map[$row['sim_id']]['ST']['SQ']['V'] = 0;

            $sim_map[$row['sim_id']]['ST']['T1']['E'] = 0;
            $sim_map[$row['sim_id']]['ST']['T2']['E'] = 0;
            $sim_map[$row['sim_id']]['ST']['SL']['E'] = 0;
            $sim_map[$row['sim_id']]['ST']['SQ']['E'] = 0;

            $sim_map[$row['sim_id']]['ST']['T1']['X'] = 0;
            $sim_map[$row['sim_id']]['ST']['T2']['X'] = 0;
            $sim_map[$row['sim_id']]['ST']['SL']['X'] = 0;
            $sim_map[$row['sim_id']]['ST']['SQ']['X'] = 0;

            $sim_map[$row['sim_id']]['ST']['T1']['S'] = 0;
            $sim_map[$row['sim_id']]['ST']['T2']['S'] = 0;
            $sim_map[$row['sim_id']]['ST']['SL']['S'] = 0;
            $sim_map[$row['sim_id']]['ST']['SQ']['S'] = 0;

            $sim_map[$row['sim_id']]['ST']['T1']['F'] = 0;
            $sim_map[$row['sim_id']]['ST']['T2']['F'] = 0;
            $sim_map[$row['sim_id']]['ST']['SL']['F'] = 0;
            $sim_map[$row['sim_id']]['ST']['SQ']['F'] = 0;

            $sim_map[$row['sim_id']]['ST']['T1']['R'] = 0;
            $sim_map[$row['sim_id']]['ST']['T2']['R'] = 0;
            $sim_map[$row['sim_id']]['ST']['SL']['R'] = 0;
            $sim_map[$row['sim_id']]['ST']['SQ']['R'] = 0;

            $sim_ids .= $row['sim_id']."','";
        }
    }
    $col_map = array();
    $col_map['T1H']           = "T1";
    $col_map['T2H']           = "T2";
    $col_map['SLH']           = "SL";
    $col_map['SQF']           = "SQ";
    $sim_det = "SELECT * FROM stocki.sim_results WHERE sim_id IN ('$sim_ids')";
    if(nr($sim_det)){
        $result = mysqli_query(create_con(), $sim_det);
		while($row = mysqli_fetch_assoc($result)){
            $sim_map[$row['sim_id']]['ST'][$col_map[$row['status']]]['S'] = $row['start'];
            $sim_map[$row['sim_id']]['ST'][$col_map[$row['status']]]['F'] = $row['end'];
            $sim_map[$row['sim_id']]['ST'][$col_map[$row['status']]]['V'] = $row['volume'];
            $sim_map[$row['sim_id']]['ST'][$col_map[$row['status']]]['R'] = $row['result'];
            $sim_map[$row['sim_id']]['ST'][$col_map[$row['status']]]['E'] = $row['entry_price'];
            $sim_map[$row['sim_id']]['ST'][$col_map[$row['status']]]['X'] = $row['exit_price'];
            $sim_map[$row['sim_id']]['EP'] = $row['entry_price'];
            if($sim_map[$row['sim_id']]['end_time'] < $row['end']){
                $sim_map[$row['sim_id']]['end_time'] = $row['end'];
            }
            $sim_map[$row['sim_id']]['PF'] += $row['result'];
        }
    }
    #Seperate Random And Actual
    $ran_sum = array();
    $act_sum = array();
    $sum_det = array();
    
    foreach (array('RAN','ACT') as $tp){
        foreach(array('EC','S','W','L','SR','P','XC') as $pr){
            $sum_det[$tp][$pr] = 0;
        }
    }

    $ran_start = array();
    $act_start = array();
    foreach($sim_map as $sm=>$sv){
        if($sim_map[$sm]['type'] == "RAN"){
            $ran_sum[$sm]   = $sv;
            $ran_start[$sm] = $sv['start_time'];
            $dat_map = sortdata($sim_map[$sm]['ST'],$trn);
            $sum_det['RAN']['S'] ++;
            $sum_det['RAN']['EC'] += $dat_map['EC'];
            $sum_det['RAN']['XC'] += $dat_map['XC'];
            $sum_det['RAN']['W']  += $dat_map['W'];
            $sum_det['RAN']['L']  += $dat_map['L'];
            $sum_det['RAN']['P']  += $dat_map['P'];
        }elseif($sim_map[$sm]['type'] == "ACT"){
            $act_sum[$sm]   = $sv;
            $act_start[$sm] = $sv['start_time'];
            $dat_map = sortdata($sim_map[$sm]['ST'],$trn);
            $sum_det['ACT']['S'] ++;
            $sum_det['ACT']['EC'] += $dat_map['EC'];
            $sum_det['ACT']['XC'] += $dat_map['XC'];
            $sum_det['ACT']['W']  += $dat_map['W'];
            $sum_det['ACT']['L']  += $dat_map['L'];
            $sum_det['ACT']['P']  += $dat_map['P'];
        }
    }


    if($sum_det['RAN']['W'] == 0){
        $sum_det['RAN']['SR'] = 0;
    }elseif($sum_det['RAN']['L'] == 0){
        $sum_det['RAN']['SR'] = 100;
    }else{
        $sum_det['RAN']['SR'] = round((($sum_det['RAN']['W']/$sum_det['RAN']['S'])*100),2);
    }

    if($sum_det['ACT']['W'] == 0){
        $sum_det['ACT']['SR'] = 0;
    }elseif($sum_det['ACT']['L'] == 0){
        $sum_det['ACT']['SR'] = 100;
    }else{
        $sum_det['ACT']['SR'] = round((($sum_det['ACT']['W']/$sum_det['ACT']['S'])*100),2);
    }
    krsort($ran_start);
    krsort($act_start);
    $ret_map['SUM'] = $sum_det;
    $ret_map['RAN']['D'] = $ran_sum;
    $ret_map['ACT']['D'] = $act_sum;
    $ret_map['RAN']['S'] = $ran_start;
    $ret_map['ACT']['S'] = $act_start;

    return $ret_map;
}

function sortdata($sim_data,$trn){
    $ret_map = array(
                        "W"  => 0,
                        "L"  => 0,
                        "XC" => 0,
                        "EC" => 0,
                        "P"  => 0
                    );
    $trs = 0;
    foreach($sim_data as $sd =>$sv){
        if($trn == "BUY"){
            $ret_map['XC'] += $sv['V'] * $sv['X'];
            $ret_map['EC'] += $sv['V'] * $sv['E'];
        }else{
            $ret_map['XC'] += $sv['V'] * $sv['E'];
            $ret_map['EC'] += $sv['V'] * $sv['X'];
        }
        $ret_map['P'] += $sv['R'];
    }
    if($ret_map['P'] > 0){
        $ret_map['W'] = 1 ;
    }else{
        $ret_map['L'] = 1 ;
    }
   return $ret_map;
}
?>