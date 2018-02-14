<?php
include '../../boilerplate.php';
checkProduction();
include '../../dbconnect.php'; 


//Security Check
$secure = new Secure();
$secure->isAdmin();


$data = json_decode(getattribute("data"));

if(!isset($data->rest_id)){
	$x= new stdClass();
	$x->result = "failure";
	$x->error = "REQUIRES_rest_id";
	exit(json_encode($x));
}


$a = new Restaurant();
$a->grabRest($data->rest_id);
$a->grabSerial($data->rest_id);

$sql = "SELECT t1.order_id, t1.order_time, t1.order_total, t1.user_id, t2.fname, t2.lname FROM restaurant_orders AS t1, users AS t2 WHERE t1.user_id= t2.user_id AND t1.rest_id= ? ORDER BY t1.order_time DESC";
$stmt= $conn->prepare($sql);
$stmt->bind_param("i", $a->rest_id);
$stmt->execute();
$stmt->bind_result($order_id, $timestamp, $total, $user_id, $fname, $lname);

$a->orders = [];
while($stmt->fetch()){
	$b = new stdClass();
	$b->order_id = $order_id;
	$b->timestamp = $timestamp;
	$b->total = $total;
	$b->user_id = $user_id;
	$b->user_name= $fname . " " . $lname;
	
	array_push($a->orders, $b);
}

$stmt->close();

$sql = "SELECT t1.sales_id, t1.rest_id, t1.commission_term, t1.is_prospect, t1.is_pending, t2.fname, t2.lname FROM sales_junction AS t1, users AS t2 WHERE t1.user_id = t2.user_id AND t1.rest_id= ?";
$stmt= $conn->prepare($sql);
$stmt->bind_param("i", $a->rest_id);
$stmt->execute();
$stmt->bind_result($sales_id, $rest_id, $term, $is_prospect, $is_pending, $fname, $lname);

if($stmt->fetch()){
	$a->sales_rep = new stdClass();
	$a->sales_rep->user_id = $sales_id;
	$a->sales_rep->name = $fname . " " . $lname;
	$a->sales_rep->term = $term;
}
$stmt->close();



$x = new stdClass();
$x->result = "success";
$x->data = $a;

exit(json_encode($x));
