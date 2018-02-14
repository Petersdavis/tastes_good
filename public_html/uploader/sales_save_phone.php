<?php
include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 


//Security Check
if(isset($_SESSION['user_id'])){
	$user_id = $_SESSION['user_id'];
}else{
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "BAD_USER";
	exit(json_encode($x));
}

$sql = "SELECT is_sales FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($is_sales);
$stmt->fetch();
$stmt->close();

if(!$is_sales){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "NOT_SALES_AUTH";
	exit(json_encode($x));
}

$data = json_decode(getattribute("data"));
$pattern = '/[\D]/';
$data->phone = preg_replace ( $pattern , "" , $phone );

if(strlen((string)$data->phone)<10){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "NOT_ENOUGH_DIGITS";
	exit(json_encode($x));
}

$sql = "UPDATE restaurants SET phone = ? WHERE rest_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("ii", $data->phone, $data->rest_id)
$stmt->execute();
$stmt->close();

$x= new stdClass();
$x->result = "success";
exit(json_encode($x));
?>