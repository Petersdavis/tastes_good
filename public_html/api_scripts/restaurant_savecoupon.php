<?php 
include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 


//Security CHECK
$secure = new Secure();
if(!$secure->user_id){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}


$user = new User();
$user->fromSession();

$coupon = json_decode(getattribute("data"));
$rest_id = getattribute("rest_id");

$pass = 0;
foreach($user->restaurants as $rest){
	if($rest->rest_id == $rest_id){
		$pass=1;
		break;
	}
}

if($pass==0){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "user_not_own_rest";
	exit(json_encode($x));
}


$expires = $coupon->expire;
$timestamp = time();
$ext = json_encode($coupon->extras);
$check = 0;

while($check==0){
	$coupon_code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
		
	$sql = "SELECT * FROM coupons WHERE code = '". $coupon_code . "'";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0){
		$coupon->code = $coupon_code;
		$check = 1;
	}
	$stmt->free_result();
	$stmt->close();
}


$sql = "INSERT INTO coupons (code, link, rest_id, type, timestamp, discount, expires, public, title, price, extras) values (?,?,?,?,?,?,?,?,?,?,?)";
if(!$stmt=$conn->prepare($sql)){
	echo $conn->error;

}			


if(!$stmt->bind_param("ssissssssss", $coupon->code, $coupon->link, $rest_id, $coupon->type, $coupon->timestamp, $coupon->discount, $expires, $coupon->public, $coupon->title, $coupon->price, $ext)){
	echo $stmt->error;

}			


$stmt->execute();
$stmt->close();


$x = new stdClass();
$x->result = "success";

echo json_encode($x);

?>