<?php

//a couple generic functions
include '../boilerplate.php';

//sets environment variables for local or production platform
checkProduction();

//this file handles the database connection
include '../dbconnect.php'; 

//checks that the restaurant has logged in and has a valid id.
$secure = new Secure();
if(!$secure->user_id){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}

$rest_id = getattribute("rest_id");

$a = new User();
$a->fromSession();

foreach($a->restaurants as $b){
	if($b->rest_id == $rest_id){
		$b->grabSerial($b->rest_id);
				
		$x= new stdClass();
		$x->result = "success";
		$x->data = $b->menu;
		exit(json_encode($x));
			
	}
}
$x= new stdClass();
$x->result = "failure";
$x->error= "bad_user_rest";
exit(json_encode($x));

?>