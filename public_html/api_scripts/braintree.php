<?php 
header("Access-Control-Allow-Origin: *");
include '../boilerplate.php'; 
checkProduction();
require_once("../braintree_init.php");
$status = getattribute('status');

if($status == "TESTING"){
BTconfig($BTsandbox);
}else{
BTconfig($BTproduct);
}


$x =new stdClass();
$x->data = Braintree\ClientToken::generate();
$x->result = "success";

exit(json_encode($x));
?>