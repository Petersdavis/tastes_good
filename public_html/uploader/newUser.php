<?php include '../boilerplate.php';
include '../dbconnect.php';


$newUser = json_decode(getattribute('newUser'));		
$a = new User();
$result = $a->fromJSON($newUser);
if($result->result == "success"||$result->error == "no_address"){
	$result->data = $a;
					
} 


echo json_encode($result);

?>
