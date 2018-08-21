<?php

function create_con(){
			$servername = "localhost";
			$username = "root";
			$password = "";

			// Create connection
			$conn = new mysqli($servername, $username, $password);

			// Check connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			}

				return $conn;
}

function update_mysql($sql){
		insert_mysql($sql);
}

function insert_mysql($sql){
		mysqli_query(create_con(),$sql);
}

function select_mysql($sql,$col){
		$ret_arr = array();
		if(nr($sql)){
						$result = mysqli_query(create_con(), $sql);
						 while($row = mysqli_fetch_assoc($result)){
										array_push($ret_arr,$row[$col]);
						}
		}
		return $ret_arr;
}

function select_single($sql,$col){
		$ret_str = "";
		if(nr($sql)){
						$result = mysqli_query(create_con(), $sql);
						 while($row = mysqli_fetch_assoc($result)){
										$ret_str = $row[$col];
										break;
						}
		}
		return $ret_str;
}

function select_hash_mysql($sql,$key,$value){
		$ret_hash = array();
		if(nr($sql)){
						$result = mysqli_query(create_con(), $sql);
						 while($row = mysqli_fetch_assoc($result)){
										$ret_hash[$row[$key]] = $row[$value];
						}
		}
		return $ret_hash;
}
function sql_hash($sql,$key,$value){
		$ret_hash = array();
		if(nr($sql)){
						$result = mysqli_query(create_con(), $sql);
						 while($row = mysqli_fetch_assoc($result)){
										$ret_hash[$row[$key]] = $row[$value];
						}
		}
		return $ret_hash;
}

function select_array($sql,$key){
		$ret_hash = array();
		if(nr($sql)){
						$result = mysqli_query(create_con(), $sql);
						 while($row = mysqli_fetch_assoc($result)){
										array_push($ret_hash,$row[$key]);
						}
		}
		return $ret_hash;
}	
	
function sql_array($sql,$key){
		$ret_hash = array();
		if(nr($sql)){
						$result = mysqli_query(create_con(), $sql);
						 while($row = mysqli_fetch_assoc($result)){
										array_push($ret_hash,$row[$key]);
						}
		}
		return $ret_hash;
}

function nr($sql){
		$select_result = mysqli_query(create_con(),$sql);
        if(mysqli_num_rows($select_result)){
                return 1;
        }
		else{
				return 0;
		}
	}

function sql_table($sql){
		$ret_hash = array();
		$result   = mysqli_query(create_con(), $sql);
		if(nr($sql)){
				while($row = mysqli_fetch_assoc($result)){
						array_push($ret_hash,$row);
				}
		}
		return $ret_hash;
}
?>
