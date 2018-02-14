<?php
include '../../boilerplate.php';
checkProduction();
include '../../dbconnect.php'; 


//Security Check
$secure = new Secure();
$secure->isAdmin();


$data = json_decode(getattribute("data"));

$sql="SELECT t1.id, t1.user_id, t1.timestamp, t1.balance, t1.amount, t2.fname, t2.lname FROM payout AS t1, users AS t2 WHERE t1.user_id = t2.user_id ";

//check if search is limited to user/restaurant/order:

if(isset($data->user_id)){
	$sql = $sql . " AND t1.user_id = ? ORDER BY t1.id DESC LIMIT 200";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $data->user_id);
	
}elseif(isset($data->payment_id)){
	$sql = $sql . " AND t1.id = ? ORDER BY t1.id DESC LIMIT 200";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $data->order_id);
}else{
	$sql = $sql . " ORDER BY t1.id DESC LIMIT 200";
	$stmt = $conn->prepare($sql);
}

$stmt->execute();

$stmt->bind_result($id, $user_id, $timestamp, $balance, $amount, $fname, $lname);


$payments = [];

while($stmt->fetch()){
	$payment=new stdClass();
	$payment->id=$id;
	$payment->user_id = $user_id;
	$payment->timestamp = $timestamp;
	$payment->balance = $balance;
	$payment->amount= $amount;
	$payment->fname = $fname;
	$payment->lname = $lname;
	
	array_push($payments, $payment);
}	

//return the results:
$x = new stdClass();
$x->result = "success";
$x->data= $payments;
exit(json_encode($x));


