<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php

$creds = json_decode(getattribute("json"));
if (!valid_sender($creds)) {
  echo getattribute("json");
  exit("Bad");
}

$creds->ip = serialize($creds->ip);

if($creds->restid > 0){
	$sql = "Select password FROM restaurants WHERE rest_id = ?";
	
	if(!$stmt= $conn->prepare($sql)){
		echo "SQL ERROR:" . $conn->error;
		exit();	
	}
	
	
	$stmt->bind_param("i", $creds->restid);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0){
	 echo "BAD REST_ID restaurant ".$creds->restid . "  not found.";
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
	
	
	if(!$stmt->bind_param("ss", $creds->id, $creds->id)){
		echo "SQL ERROR:" . $conn->error;
		exit();	
	}
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0){
		 echo "BAD USER ID: ".$creds->id. "  not found."	;
		 exit();	
	}
		
		 
	$stmt->bind_result($pwd_hash, $creds->restid);
	$stmt->fetch();
	$stmt->close();
	
}else{
	echo "Bad Credentials: NO USER_ID OR REST_ID";
	exit();
}
	

if(password_verify($creds->passwd, $pwd_hash)){

	
	$sql = "SELECT rest_id FROM restaurant_client WHERE rest_id = ?";
	if(!$stmt = $conn->prepare($sql)){
			echo "SQL ERROR:" . $conn->error;
			exit();	
		}
	$stmt->bind_param("i", $creds->restid);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0){
		$stmt->close();
		$sql = "INSERT INTO restaurant_client (rest_id, client_version, width, height, mac, ip_address) VALUES (?,?,?,?,?,?)";
		if(!$stmt = $conn->prepare($sql)){
			echo "SQL ERROR:" . $conn->error;
			exit();	
			
		}
		$mac = serialize($creds->mac);
		$stmt->bind_param("isddss", $creds->restid,  $creds->version, $creds->width, $creds->height, $mac, $creds->ip);
		$stmt->execute();
		$stmt->close();
		echo "OK".$creds->restid;
		exit();
	}else{
		$stmt->close();
		$sql = "UPDATE restaurant_client SET client_version = ?, width = ?, height = ?, mac=?, ip_address = ?, status=1 WHERE rest_id = ?";
		if(!$stmt=$conn->prepare($sql)){
			echo "SQL ERROR:" . $conn->error;
			exit();	
		}	
		$status = 1;	
		$mac = serialize($creds->mac);
		$stmt->bind_param("sddssi",  $creds->version, $creds->width, $creds->height, $mac, $creds->ip, $creds->restid);
		$stmt->execute();
		
		echo "OK".$creds->restid;
		exit();
	}	
	
} else {

	$sql = "SELECT mac FROM restaurant_client WHERE rest_id = ?";
	$stmt= $conn->prepare($sql);
	$stmt->bind_param("i", $creds->restid);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 1){
		$stmt->bind_result($mac_db);
		$stmt->fetch();
		$mac= serialize($creds->mac);
		if($mac == $mac_db){
			$stmt->close();
			$sql = "UPDATE restaurant_client SET client_version = ?, width = ?, height = ?, ip_address = ?, status=1 WHERE rest_id = ?";
			if(!$stmt=$conn->prepare($sql)){
				echo "SQL ERROR:" . $conn->error;
				exit();	
			}	
			
			$status = 1;
			$stmt->bind_param("sddsi",  $creds->version, $creds->width, $creds->height, $creds->ip, $creds->restid);
			$stmt->execute();
			
			echo "OK".$creds->restid;
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

