<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php

if (!isset($_POST["json"])) {
  echo "No JSON " . print_r($_POST);
  return;
}

$creds = json_decode(getattribute("json"));
if (!isset($creds)) {
  echo 'Can\'t decode ' . $json;
  return;
}


if (!valid_sender($creds)) {
  exit("Bad");
}

$sql = "Select rest_id FROM restaurant_client WHERE rest_id = ?";

if(!$stmt= $conn->prepare($sql)){
	$x->result= "fail";
	$x->error = "SQL ERROR:" . $conn->error;
	echo $x->error;
	exit();	
}


$stmt->bind_param("i", $creds->rest_id);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows == 0){
	$x->result= "fail";
	$x->error = "BAD_CLIENT" . $creds->rest_id;
	echo $x->error;
	exit();	
}else{
	$stmt->close();
	
	$sql = "UPDATE restaurant_client SET status = 0 WHERE rest_id = ?";
	if(!$stmt=$conn->prepare($sql)){
		$x->result= "fail";
		$x->error = "SQL ERROR:" . $conn->error;
		echo $x->error;
		exit();	
	}	
			
	$stmt->bind_param("i", $creds->rest_id);
	$stmt->execute();
	echo "OK";
	exit();
		
}
$conn->close(); 