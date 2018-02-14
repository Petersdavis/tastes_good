<?php include '../boilerplate.php'; 

session_start();
if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}


session_destroy();

echo "success";

?>
