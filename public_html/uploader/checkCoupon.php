<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php
$coupon = getAttribute('coupon');
$timestamp = time();
$rest_id = 0;
$sql = "SELECT rest_id FROM coupons WHERE code = ? AND expires > ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("ss", $coupon, $timestamp);
$stmt->execute();
$stmt->bind_result($rest_id);
$stmt->store_result();
if($stmt->num_rows > 0){
	$stmt->fetch();
}
echo $rest_id;
$stmt->close();	
