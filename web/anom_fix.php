<?php
require_once("config/config.php");

$pre_id = str_replace("_",":",$_GET['cls']);
$param  = explode(";",$_GET['par']);
$map    = explode(";",$_GET['map']);

array_pop($param);
array_pop($map);

$map   = map_it($map);

foreach($param as $p){
    $p = explode(":",$p);
    $it = $map[$p[0]];
    $id = $p[1];
    
    if($id != "discard"){
        $query = "UPDATE lucifer.sales SET order_id='$id' WHERE order_id='$pre_id' AND item_descr='$it'";
        update_mysql($query);
    }
}

$query = "DELETE FROM lucifer.sales WHERE order_id='$pre_id'";
update_mysql($query);

function map_it($map){
    $ret  = array();
    foreach($map as $m){
        $m = explode(":",$m);
        $ret[$m[0]] = $m[1];
    }
    
    return $ret;
}


?>