<?php include '../boilerplate.php';
checkProduction();
include '../dbconnect.php';

$data = json_decode(getattribute("data"));
$token = $data->token;

$a= new User();
if($result = $a->verifyToken($token)){
	$a->fromSession();
	$x= new stdClass();
	$x->result="success";
	$x->data=$a;
	exit(json_encode($x));


}else{
	exit(json_encode($result));
}

