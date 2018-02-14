<?php 
include '../boilerplate.php';


//Security CHECK

if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}



checkProduction();
include '../dbconnect.php'; 

$a = new User();
$a->fromSession();

foreach($a->restaurants as $b){
$rest_id = $b->rest_id;


$sql="SELECT request_date, request_time, link, coupon, order_id, user_id, order_time, payment_type, order_total, order_delivery, delivery_charge, tip, addr_id, discount, payment_fee, commission, tg_points, confirmed FROM restaurant_orders WHERE rest_id = ?  ORDER BY order_time DESC LIMIT 5";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->bind_result($request_date, $request_time, $link, $coupon, $order_id, $user_id, $order_time, $payment_type, $order_total, $order_delivery, $delivery_charge, $tip, $addr_id, $discount, $payment_fee, $commission, $tg_points, $confirmed);
$last_five = [];
while($stmt->fetch()){
	$c=new Order();
	$c->order_id=$order_id;
	$c->user_id = $user_id;
	$c->rest_id = $rest_id;
	$c->confirmed = $confirmed;
	$c->addr_id= $addr_id;
	
	$c->request_date = 	$request_date;
	$c->request_time = 	$request_time;
	$c->deliveryOption=$order_delivery;
	$c->deliveryCharge = $delivery_charge;
	$c->tip = $tip;
	$c->total = $order_total;
	$c->timestamp=$order_time;
	$c->paymentType= $payment_type;
	$c->coupon = $coupon;
	$c->discount =$discount;
	$c->tg_points= $tg_points;
	$c->link=$link;

	array_unshift($last_five, $c);
	
}


$stmt->close();

foreach($last_five as $c){
	
	$d = new User();
	$d->fromId($c->user_id);
	$c->user = $d;
	
	if($c->addr_id){	
		$d = new Address();
		$d->fromId($c->addr_id);
		$c->address = $d;
	}
}

$b->last_five= $last_five;
}



$x = new stdClass();
$x->result = "success";
$x->data = $a;
echo json_encode($x);

?>