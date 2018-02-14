 <?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php
function verifyPassword($user_id, $password){
	global $conn;
	$sql = 'SELECT password FROM users WHERE user_id = ?';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('i', $user_id);
	$stmt->execute();
	$stmt->bind_result($hash);
	while($stmt->fetch()){
		if(password_verify($password, $hash)){
			$stmt->close();
			return true;
		}
	}
	$stmt->close();
	return false;
}


if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}

$user_id = $_SESSION['user_id'];
$data=json_decode(getattribute('data'));
if($data->user_id !=  $user_id){

	$x=new stdClass();
	$x->result = "failure";
	$x->error = "mismatch_user_id";
	exit(json_encode($x));
}

$passwords = $data->password;

if(verifyPassword($user_id, $data->password->old_pwd)){
	
	$new_pwd = password_hash($data->password->new_pwd, PASSWORD_BCRYPT);
	$sql = "UPDATE users SET password = ? WHERE user_id = ?";
	$stmt=$conn->prepare($sql);
	$stmt->bind_param("si", $new_pwd, $user_id);
	$stmt->execute();
	$stmt->close();
	
	$x=new stdClass();
	$x->result = "success";
	
	exit(json_encode($x));
	
} else {

$x=new stdClass();
$x->result = "failure";
$x->error = "bad_pwd";
exit(json_encode($x));

}
