<?php
include '../../boilerplate.php';
checkProduction();
include '../../dbconnect.php'; 


//Security Check
$secure = new Secure();
$secure->isAdmin();


$data = json_decode(getattribute("data"));

if(!isset($data->user_id)){
	$x= new stdClass();
	$x->result = "failure";
	$x->error = "REQUIRES_user_id";
	exit(json_encode($x));
}


$a = new User();
$a->user_id = $data->user_id;

$a->fromSession();
$a->getAddresses();

$sql = "SELECT t1.order_id, t1.order_time, t1.order_total, t1.rest_id, t1.confirmed, t2.title FROM restaurant_orders AS t1, restaurants AS t2 WHERE t1.rest_id = t2.rest_id AND t1.user_id = ? ORDER BY t1.order_time DESC";
$stmt= $conn->prepare($sql);

$stmt->bind_param("i", $a->user_id);
$stmt->execute();
$stmt->bind_result($order_id, $timestamp, $total, $rest_id, $confirmed, $title);

$a->orders = [];
while($stmt->fetch()){
	$b = new stdClass();
	$b->order_id = $order_id;
	$b->timestamp = $timestamp;
	$b->total = $total;
	$b->rest_id = $rest_id;
	$b->confirmed = $confirmed;
	$b->title = $title;
	
	array_push($a->orders, $b);
}

$stmt->close();

//get sales contracts:
$sql = "SELECT t1.sales_id, t1.user_id, t1.rest_id, t1.commission_term, t1.is_prospect, t1.is_pending, t2.title, t3.fname, t3.lname FROM sales_junction AS t1, restaurants AS t2, users AS t3 WHERE t1.user_id = t3.user_id AND t1.rest_id = t2.rest_id AND (t1.user_id = ? OR t1.sales_id = ?)";
$stmt= $conn->prepare($sql);

$stmt->bind_param("ii", $a->user_id, $a->user_id);
$stmt->execute();
$stmt->bind_result($sales_id, $user_id, $rest_id, $term, $is_prospect, $is_pending, $title, $fname, $lname);


$a->sales_rel = [];

while($stmt->fetch()){
$b = new stdClass();

if($sales_id == $a->user_id){
  if($user_id > 0){
	$b->id = $user_id;
	$b->name = $fname . " " . $lname;
	$b->term = $term;
	$b->role = "user";
 	
 } else {
 	$b->id= $rest_id;
 	$b->name= $title;
 	$b->term = $term;
 	$b->role = "rest";
 	}



}else{

$b->id = $sales_id;
$b->name = $fname . " ". $lname;
$b->term = $term;
$b->role = "rep";
}

array_push($a->sales_rel, $b);
}

$stmt->close();


$x = new stdClass();
$x->result = "success";
$x->data = $a;

exit(json_encode($x));
