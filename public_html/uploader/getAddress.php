<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php
$user = new user();
$creds = json_decode(getattribute("user"));
$user->user_id = $creds->user_id;
$user->verify = $creds->verify;
$user->getAddresses();

if(sizeof($user->addresses > 0)){
	$result = new stdClass();
	$result->result = "success";
	$result->error = "";
	$result->data = $user->addresses;
}else{
	$result = new stdClass();
	$result->result = "fail";
	$result->error = "no_addresses";
}

echo json_encode($result);	
