<?php include '../boilerplate.php'; 
include '../dbconnect.php';

if(isset($_SESSION['user_id'])){
	
$sql = "UPDATE users SET login_token = NULL WHERE user_id = ?";
$stmt=$conn->prepare($sql);
echo ($conn->error);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

}
session_destroy();


$x = new stdClass();
$x->result = "success";
exit(json_encode($x));
?>
