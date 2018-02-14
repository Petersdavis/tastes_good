<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 
$request_users = getattribute("users");
$request_users = json_decode($request_users);

$users = [];
$results = [];

foreach($request_users as $request){
	$x = new user();
	$result = $x->fromLocalStorage($request->user_id, $request->verify);
	array_push($users, $x);
	array_push($results, $result);
							
}

$z = new stdClass();
$z->results = $results;
$z->data = $users;
echo json_encode ($z);
