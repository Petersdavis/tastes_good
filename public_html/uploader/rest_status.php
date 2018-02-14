<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php
//var declarations

$form_type = getattribute ('form_type');

$status=getattribute('status');
$status=json_decode($status);
$rest_id = $_SESSION['rest_id'];

//create community log 
$community = $_SESSION['community'];
$timestamp = time();
$type = "ACTIVITY";
switch($status){
case "REGISTRATION":
	$content = "IS LOGGED IN.";	
	break;
case "DECLINED SERVICE":
	$content = "HAS DECLINED TO JOIN.";
	break;
default:
}
	

$sql = "INSERT INTO community_logs (content, timestamp, community, rest_id, type) VALUES (?,?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssis", $content, $timestamp, $community, $rest_id, $type);
if(!$stmt->execute()){
	echo $stmt->error;
}
$stmt->close();


//update the status
$sql="UPDATE restaurants SET status = ? WHERE rest_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("si", $status->status, $rest_id);
if(!$stmt->execute()){
	echo $stmt->error;
}
$stmt->close();


$conn->close();
exit();
