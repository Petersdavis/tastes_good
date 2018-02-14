<?php 
include '../boilerplate.php';


//Security CHECK

if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_session";
	exit(json_encode($x));
}

checkProduction();
include '../dbconnect.php'; 
$user_id = $_SESSION['user_id'];

$orders = [];

$sql="SELECT request_date, request_time, link, coupon, order_id, rest_id, order_time, payment_type, order_total, order_delivery, delivery_charge, tip, addr_id, discount, confirmed FROM restaurant_orders WHERE user_id = ? ORDER BY order_time ASC";

if(!$stmt=$conn->prepare($sql)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $conn->error;
	exit(json_encode($x));
}

if(!$stmt->bind_param("i", $user_id)){
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
	
if(!$stmt->bind_result($request_date, $request_time, $link, $coupon, $order_id, $rest_id, $order_time, $payment_type, $order_total, $order_delivery, $delivery_charge, $tip, $addr_id, $discount, $confirmed)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
}


while($stmt->fetch()){
	$a=new Order();
	$a->order_id=$order_id;
	$a->rest_id= $rest_id;

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
	$a->link=$link;
	
	$a->class = "order";

	array_unshift($orders, $a);
}
}
$stmt->close();

foreach($orders as $order){
	$b = new Restaurant();
	$b->grabRest($order->rest_id);
	$order->rest = $b;

}


$x = new stdClass();
$x->result = "success";
$x->data = new stdClass();
$x->data->orders = $orders;


exit(json_encode($x));