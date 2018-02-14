<?php
include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 

$data = json_decode(getattribute("data"));
$rest_id = $data->rest_id;
$code = $data->coupon;


$a=new restaurant();
$a->grabRest($rest_id);
$a->grabSerial($rest_id);

if(!isset($_SESSION['timezone'])){
$sql = "Select timezone FROM community WHERE name = ?";
	$stmt=$conn->prepare($sql);
	$stmt->bind_param("s", $a->community);
	$stmt->execute();
	$stmt->bind_result($timezone);
	$stmt->fetch();
	$stmt->close();
	
	$_SESSION['timezone'] = $timezone;
		
} else {$timezone = $_SESSION['timezone'];}

$a->checkOpen($timezone);

 
if($code!=0){
$a->grabCoupons($rest_id);

foreach($a->coupons as $coupon){
	if($coupon->code == $code){
	 	$a->coupon = $coupon;
		break;
	}
}
}else {
$coupon = 0;	
}

function hitPage($rest_id){
	global $conn;
	global $gUserid;
	$date = $_SERVER['REQUEST_TIME'];
	
	
	$sql = "INSERT INTO page_hits (rest_id, user_id, date) VALUES (?,?,?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("iis", $rest_id, $gUserid, $date);
	$stmt->execute();
	$stmt->close();
	
	$sql = "UPDATE restaurants SET page_hits = page_hits+1 WHERE rest_id = ?";
	$stmt=$conn->prepare($sql);
	echo $conn->error;
	$stmt->bind_param("i", $rest_id);
	$stmt->execute();
	$stmt->close();
}

hitPage($rest_id);
$x = new stdClass();
$x->data = $a;
$x->result = "success";

echo json_encode($x);
?>