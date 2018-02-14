<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php
$coupon = json_decode(getattribute('coupon'));
$extras = json_decode(getattribute('extras'));
$rest_id = getattribute('rest_id');

$a = new Restaurant();
$a->grabRest($rest_id);

//update the Extras
$a->grabSerial($rest_id);
$a->menu->extras = $extras;
$a->putSerial($rest_id);

//Build Coupon
$expires = time() + ($coupon->expire *31*24*60*60);
$coupon->expires = date("F j, Y", $expires);
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
		$check = 1;
	}
	$stmt->free_result();
	$stmt->close();
}

$coupon->code = $coupon_code;

$coupon->link = "/order.php?rest_id=".$rest_id."&coupon=".$coupon_code;

//Store the Coupon

$sql = "INSERT INTO coupons (code, link, rest_id, type, timestamp, discount, expires, public, title, price, extras) values (?,?,?,?,?,?,?,?,?,?,?)";
$stmt=$conn->prepare($sql);			

echo $conn->error;
$stmt->bind_param("ssissssssss", $coupon_code, $coupon->link, $rest_id, $coupon->type, $timestamp, $coupon->discount, $expires, $coupon->public, $coupon->title, $coupon->price, $ext);


$stmt->execute();
$coupon->id = $stmt->insert_id;
$stmt->close();

//Return the Coupon

$x = new stdClass();
$x->result = "success";
$x->coupon = $coupon;

echo json_encode($x);