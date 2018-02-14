<?php 
include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 


$secure = new Secure();
if(!$secure->user_id){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "BAD_USER";
	exit(json_encode($x));
}


$a = new User();
$a->fromSession();


$rest_id =getattribute('rest_id');


$pass=0;
foreach($a->restaurants as $rest){
	if($rest->rest_id == $rest_id){
		$pass=1;
		break;
	}
}	

if ($pass == 0){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_rest";
	exit(json_encode($x));
}

$rest->grabCoupons($rest_id);
$rest->grabSerial($rest_id);


$x = new stdClass();
$x->result = "success";
$x->data = $rest;

echo json_encode($x);



?>