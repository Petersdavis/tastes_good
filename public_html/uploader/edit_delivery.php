<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 

$offers_delivery=getattribute('offers_delivery');
$delivery_base=getattribute('delivery_base');
$delivery_email=getattribute('delivery_email');
$rest_id =getattribute('rest_id'));

$sql="UPDATE restaurants SET offers_delivery = ?, delivery_base = ?, delivery_email = ? WHERE rest_id = ?;";
if(!$stmt=$conn->prepare($sql)){
	echo $conn->error;
}
if(!$stmt->bind_param("idsi", $offers_delivery, $delivery_base, $delivery_email, $rest_id)){
	echo $stmt->error;
}

if(!$stmt->execute()){
	echo $stmt->error;
}
$stmt->close();
?>

