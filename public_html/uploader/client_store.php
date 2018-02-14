<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php

$creds = json_decode(getattribute("json"));
if (!valid_sender($creds)) {
  exit("Bad");
}


$sql = "Select password FROM restaurants WHERE rest_id = ?";

if(!$stmt= $conn->prepare($sql)){
	echo "SQL ERROR:" . $conn->error;
	exit();	
}


if($creds->rest_id > 0){
	$sql = "Select password FROM restaurants WHERE rest_id = ?";
	
	if(!$stmt= $conn->prepare($sql)){
		echo "SQL ERROR:" . $conn->error;
		exit();	
	}
	
	
	$stmt->bind_params("i", $creds->rest_id);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0){
	 echo "BAD REST_ID restaurant ".$creds->rest_id "  not found."
	 exit();	
	}
	$stmt->bind_result($pwd_hash);
	$stmt->fetch();
	$stmt->close();


} elseif(isset($creds->id)){
	$sql = "Select password, rest_id FROM restaurants WHERE user_name = ? OR email = ?";	
		
	if(!$stmt= $conn->prepare($sql)){
		echo "SQL ERROR:" . $conn->error;
		exit();	
	}
	
	
	$stmt->bind_params("ss", $creds->id, $creds->id);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0){
		 echo "BAD USER ID: ".$creds->id "  not found."	
		 exit();	
	}
		
		 
	$stmt->bind_result($pwd_hash, $creds->rest_id);
	$stmt->fetch();
	$stmt->close();
	
}else{
	echo "Bad Credentials: NO USER_ID OR REST_ID"
	exit();
}

if(password_verify($creds->password, $pwd_hash)){
	
	$sql = "SELECT rest_id FROM restaurant_client WHERE rest_id = ?"
	if(!$stmt = $conn->prepare($sql)){
			echo "SQL ERROR:" . $conn->error;
			exit();	
		}
	$stmt->bind_params("i", $creds->rest_id);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0){
		$stmt->close();
		$sql = "INSERT INTO restaurant_client (rest_id, client_version, width, height, mac, ip_address) VALUES (?,?,?,?,?,?)";
		if(!$stmt = $conn->prepare($sql)){
			echo "SQL ERROR:" . $conn->error;
			exit();	
			
		}
		$mac = serialize($creds->mac)
		$stmt->bind_param("isddss", $creds->rest_id,  $creds->version, $creds->width, $creds->height, $creds->ip);
		$stmt->execute();
		$stmt->close();
		echo "OK".$creds->rest_id;
		exit();
	}else{
		$stmt->close()
		$sql = "UPDATE restaurant_client SET client_version = ?, width = ?, height = ?, mac=?, ip_address = ?, status=? WHERE rest_id = ?";
		if(!$stmt=$conn->prepare($sql)){
			echo "SQL ERROR:" . $conn->error;
			exit();	
		}	
		$status = "open"	
		$mac = serialize($creds->mac);
		$stmt->bind_param("sddssi",  $creds->version, $creds->width, $creds->height, $creds->ip, $status, $creds->rest_id);
		$stmt->execute();
		
		echo "OK".$creds->rest_id;
		exit();
	}	
	
} else {
	
	$sql = "SELECT mac FROM restaurant_client WHERE rest_id = ?"
	$stmt= $conn->prepare($sql);
	$stmt->bind_params("i", $creds->rest_id);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 1){
		$stmt->bind_result($mac_db)
		$stmt->fetch();
		$mac= serialize($creds->mac);
		if($mac == $mac_db){
			$stmt->close()
			$sql = "UPDATE restaurant_client SET client_version = ?, width = ?, height = ?, ip_address = ?, status=? WHERE rest_id = ?";
			if(!$stmt=$conn->prepare($sql)){
				echo "SQL ERROR:" . $conn->error;
				exit();	
			}	
			
			$status = "open"
			$stmt->bind_param("sddssi",  $creds->version, $creds->width, $creds->height, $creds->ip, $status, $creds->rest_id);
			$stmt->execute();
			
			echo "OK".$creds->rest_id;
			exit();
			
		} else {
			
			echo "PWD_AND_MAC_FAIL";
			exit();
		}
		
	} else {
		
		echo  "PWD_FAIL_MAC_NOT_FOUND";
		exit();	
		
	}
}
?>

