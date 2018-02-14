<?php 
include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 

$data= json_decode(getattribute("data"));
$rest_id = $data->rest_id;
$coupon = $data->coupon;

$a = new Restaurant();
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


$a->grabCoupons($rest_id);

$x =new stdClass();
$x->rest = $a;

foreach($a->coupons as $coup){
if($coup->code == $coupon){
	$x->coupon = $coup;
	$x->result = "success";
	exit(json_encode($x));
}
}

$x->result= "failure";
$x->error= "NO_COUP";
echo json_encode($x);


