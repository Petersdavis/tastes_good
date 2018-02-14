<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php
$contact=getattribute('contact');
$contact = json_decode($contact);
$rest_id = $_SESSION['rest_id'];

$sql = "UPDATE restaurants SET phone = ?, email = ? WHERE rest_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi",$contact->phone,$contact->email, $rest_id);
if(!$stmt->execute()){
	echo $stmt->error;
}
$stmt->close();
		
