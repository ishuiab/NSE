<?php
require_once("config/config.php");
extract($_POST);
if($request =="getscrip"){
    print get_scrips($param);
}elseif($request=="getbulkids"){
    print get_bulk_ids($param);
}elseif($request=="bulkstat"){
    print get_bulk_stat($param);
}elseif($request=="stardet"){
    print get_star_det($param,$trn);
}

?>