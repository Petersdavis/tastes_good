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

$rest_id = getattribute("rest_id");

$rest = new Restaurant();
$rest->grabRest($rest_id);

$phone = $rest->GooglePlace();

$result = new stdClass();
$result->result = "success";
$result->data = $rest->phone;
exit(json_encode($result));
?>
