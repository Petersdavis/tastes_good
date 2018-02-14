<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php

$user_details = json_decode(getattribute("user_details"));


$sql = "SELECT user_id FROM users WHERE email = ?; ";

if(!$stmt = $conn->prepare($sql)){$x=new stdClass(); $x->result="error";$x->error=$conn->error; exit(json_encode($x));}
$stmt->bind_param("s", $user_details->email);
if(!$stmt->execute()){$x=new stdClass(); $x->result="error"; $x->error=$stmt->error; exit(json_encode($x));}
$stmt->bind_result($user_id);
if($stmt->fetch()){
	
	exit("USER_EXISTS");
} else {
	exit("USER_DNE");

}


?>