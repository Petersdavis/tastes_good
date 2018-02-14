<?php 
include '../boilerplate.php';

checkProduction();
include '../dbconnect.php'; 

//Security CHECK

if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}



$a = new User();
$a->fromSession();


$data= json_decode(getattribute("data"));
$begin = $data->begin;
$end = $data->end;
$rest_id = $data->rest_id;


$pass=0;
foreach($a->restaurants as $b){
	if($b->rest_id == $rest_id){
		$pass=1;
	}
}	

if ($pass == 0){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_rest";
	exit(json_encode($x));
}



$orders = [];

$sql="SELECT request_date, request_time, link, coupon, order_id, user_id, order_time, payment_type, order_total, order_delivery, delivery_charge, tip, addr_id, discount, payment_fee, commission, tg_points, confirmed, rest_balance, rest_delta FROM restaurant_orders WHERE rest_id = ? AND order_time > ? AND order_time < ? ORDER BY order_time DESC";

if(!$stmt=$conn->prepare($sql)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $conn->error;
	exit(json_encode($x));
}

if(!$stmt->bind_param("iii", $rest_id, $begin, $end)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
}

if(!$stmt->execute()){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
}

$stmt->store_result();
if($stmt->num_rows > 0){
	
if(!$stmt->bind_result($request_date, $request_time, $link, $coupon, $order_id, $user_id, $order_time, $payment_type, $order_total, $order_delivery, $delivery_charge, $tip, $addr_id, $discount, $payment_fee, $commission, $tg_points, $confirmed, $balance, $rest_delta)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
}


while($stmt->fetch()){
	$a=new Order();
	$a->order_id=$order_id;
	$a->user_id = $user_id;

	$a->confirmed = $confirmed;
	$a->addr_id= $addr_id;
	
	$a->request_date = $request_date;
	$a->request_time = $request_time;
	$a->deliveryOption=$order_delivery;
	$a->deliveryCharge = $delivery_charge;
	$a->tip = $tip;
	$a->total = $order_total;
	$a->timestamp=$order_time;
	$a->paymentType= $payment_type;
	$a->coupon = $coupon;
	$a->discount =$discount;
	$a->tg_points= $tg_points;
	$a->link=$link;
	$a->balance = $balance;
	$a->rest_delta = $rest_delta;
	$a->class = "order";

	array_unshift($orders, $a);
}
}
$stmt->close();

$payments = [];


$sql="SELECT id, timestamp, balance, amount FROM payout WHERE user_id = ? AND timestamp > ? AND timestamp < ? ORDER BY timestamp DESC";

if(!$stmt=$conn->prepare($sql)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $conn->error;
	exit(json_encode($x));
}

if(!$stmt->bind_param("iii", $a->user_id, $begin, $end)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
}

if(!$stmt->execute()){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
}


$stmt->store_result();
if($stmt->num_rows > 0){
	
if(!$stmt->bind_result($id, $timestamp, $balance, $amount)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
}


while($stmt->fetch()){
	$a=new stdClass();
	$a->id=$id;
	$a->timestamp = $timestamp;

	$a->balance = $balance;
	$a->amount= $amount;
	$a->class = "payout";
	
	array_unshift($payments, $a);
}
}
$stmt->close();

if(sizeof($orders)==0 && sizeof($payments)==0){
  //No orders or payments during this period.
  //lookup init and final balance.  
  
  //records before period
 $sql='SELECT timestamp, balance FROM (SELECT timestamp, balance FROM payout T1 WHERE rest_id = ? AND timestamp < ? UNION ALL SELECT order_time AS "timestamp", rest_balance AS "balance" FROM restaurant_orders T2 WHERE rest_id = ? AND order_time < ?) T3 ORDER BY timestamp DESC';


if(!$stmt=$conn->prepare($sql)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $conn->error;
	exit(json_encode($x));
}

if(!$stmt->bind_param("iiii", $rest_id, $begin, $rest_id, $begin)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
}

if(!$stmt->execute()){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
}


$stmt->store_result();
if($stmt->num_rows > 0){
	//use the last available balance.
	
	if(!$stmt->bind_result($timestamp, $balance)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
	}
	
	if(!$stmt->fetch()){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
	}
	
	
	$x=new stdClass();
	$x->result = "success";
	$x->data = new stdClass();
	$x->data->orders = $orders;
	$x->data->payments = $payments;
	$x->data->balance = $balance;
	exit(json_encode($x)); 
} else {

	//no orders.
	$stmt->close();
	
	$x=new stdClass();
	$x->result = "success";
	$x->data = new stdClass();
	$x->data->orders = $orders;
	$x->data->payments = $payments;
	$x->data->balance = 0.00;
	exit(json_encode($x)); 


}



}

$x = new stdClass();
$x->result = "success";
$x->data = new stdClass();
$x->data->orders = $orders;
$x->data->payments = $payments;

exit(json_encode($x));