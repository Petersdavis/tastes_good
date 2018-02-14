<?php include '../boilerplate.php';
include '../dbconnect.php'; 


$email=getattribute("email");
$sql = "SELECT user_id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows==0){
	$x=new stdClass();
	$x->result = "EMAIL_OK";
	echo $x->result;
}else{
	if($fb_id = getattribute("fb_id")){
		$x=new stdClass();
		$x->result = "EMAIL_MERGE";
		echo $x->result;
		exit;
	
	}

	$x=new stdClass();
	$x->result = "EMAIL_EXISTS";
	echo $x->result;
}
$stmt->free_result();
$stmt->close();
?>