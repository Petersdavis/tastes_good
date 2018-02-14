<?php require_once '../boilerplate.php'; 
require_once '../dbconnect.php'; 

$data = json_decode(getattribute("data"));

if(isset($data->auth)){
$auth = $data->auth;


$a=new Restaurant();
if($a->verifyToken($auth)){

$a->grabRest($a->rest_id);

$b = new User();
$b->email = $a->email;
$b->phone = $a->phone;
$b->restaurants = array($a);

$x = new stdClass();
$x->result = "success";
$x->data = $b;
exit(json_encode($x));

}
}else{
$x = new stdClass();
$x->result = "failure";
$x->error = "no_auth";
exit(json_encode($x));
}

