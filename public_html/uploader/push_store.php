<?php include '../boilerplate.php';
include '../dbconnect.php';

$id = getattribute('push_id');

if(isset($_SESSION['user_id'])){
	$user = new User();
	$user->user_id = $_SESSION['user_id'];
	$x = $user->storePushId($id);
	exit(json_encode($x));
	
}else{
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "NO_USER";
	exit(json_encode($x));

}

?>


