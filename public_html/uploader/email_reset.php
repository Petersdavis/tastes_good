<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php
$key = getattribute('key');
$user_id = getattribute('user_id');
$password = getattribute('password');


$sql = "SELECT reset_key, expires FROM user_pwd_reset WHERE user_id=?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows == 0){
	$x = new stdClass;
	$x->result = "fail";
	$x->error = "NO_KEY";
	echo json_encode($x);
	$stmt->close();
	exit();
}

$stmt->bind_result($key2, $expires);

while($stmt->fetch()){
	if($key2 == $key){
		
		if($expires > time()){
			$stmt->close();
			$pwd = password_hash($password, PASSWORD_BCRYPT);
			$sql="UPDATE users SET password = ? WHERE user_id = ?";
			$stmt= $conn->prepare($sql);
			$stmt->bind_param("si", $pwd, $user_id);
			$stmt->execute();
			$stmt->close();
			$_SESSION['user_id']= $user_id;
			
			$x = new stdClass();
			$x->result = "success";
			echo json_encode($x);
			exit();
		}else{
			$x = new stdClass();
			$x->result = "KEY_EXPIRED";
			echo json_encode($x);
			exit();
		}
			
	}
	
}
$x = new stdClass();
$x->result = "fail";
$x->error = "NO_KEY";
echo json_encode($x);
		
