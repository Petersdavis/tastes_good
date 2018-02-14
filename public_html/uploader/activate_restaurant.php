<?php 
include '../boilerplate.php';
include '../dbconnect.php'; 


if($data = getattribute('data')){
if($data = json_decode($data)){

$rest_id = $data->rest_id;

$a = new User();
$a->fromSession();

$found_rest = 0;
foreach($a->restaurants as $rest){
if ($rest->rest_id = $rest_id){

$found_rest = 1;


//update restaurant
$sql = "UPDATE restaurants SET status = 'ACTIVE' WHERE rest_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->close();

//update restaurant_orders
$sql = "DELETE FROM restaurant_orders WHERE rest_id = ? and env = 0";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->close();

//create sales contract

$expires = time() + (4 * 356 * 24 * 60 * 60);
$sql = "UPDATE sales_junction SET commission_term = ?, is_pending = 0, is_prospect = 0 WHERE rest_id = ?";
$stmt=$conn->prepare($sql);		
$stmt->bind_param('ii', $expires, $rest_id);
$stmt->execute();
$stmt->close();

//success
$x = new stdClass();
$x->result="success";
exit(json_encode($x));


}
}

if ($found_rest = 0){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "Bad Session (or) Rest_Id";
	echo json_encode($x);
	exit();
}

}else{
$x = new stdClass();
$x->result = "failure";
$x->error = "BAD_DATA";
exit(json_encode($x));
}
}else{

$x = new stdClass();
$x->result = "failure";
$x->error = "NO_DATA";
exit(json_encode($x));
}
$conn->close();
?>