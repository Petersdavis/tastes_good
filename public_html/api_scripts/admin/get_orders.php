<?php
include '../../boilerplate.php';
checkProduction();
include '../../dbconnect.php'; 



//Security Check
$secure = new Secure();
$secure->isAdmin();


$data = json_decode(getattribute("data"));

$sql = "SELECT t1.request_date, t1.request_time, t1.link, t1.coupon, t1.order_id, t1.user_id, t1.rest_id, t1.order_time, t1.payment_type, t1.order_total, t1.order_delivery, t1.delivery_charge, t1.tip, t1.addr_id, t1.discount, t1.payment_fee, t1.commission, t1.tg_points, t1.confirmed, t1.rest_balance, t1.rest_delta, t1.comment, t2.fname, t2.lname, t3.title FROM restaurant_orders AS t1, users AS t2, restaurants AS t3 WHERE t1.user_id = t2.user_id AND t1.rest_id = t3.rest_id";

//check if search is limited to user/restaurant/order:

if(isset($data->user_id)){
	$sql = $sql . " AND t1.user_id = ? ORDER BY t1.order_id DESC LIMIT 200";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $data->user_id);
}elseif(isset($data->rest_id)) {
	$sql = $sql . " AND t1.rest_id = ? ORDER BY t1.order_id DESC LIMIT 200";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $data->rest_id);

}elseif(isset($data->order_id)){
	$sql = $sql . " AND t1.order_id = ? ORDER BY t1.order_id DESC LIMIT 200";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $data->order_id);
}else{
	$sql = $sql . " ORDER BY t1.order_id DESC LIMIT 200";
	$stmt = $conn->prepare($sql);
}

$stmt->execute();
$stmt->bind_result($request_date, $request_time, $link, $coupon, $order_id, $user_id, $rest_id, $order_time, $payment_type, $order_total, $order_delivery, $delivery_charge, $tip, $addr_id, $discount, $payment_fee, $commission, $tg_points, $confirmed, $balance, $rest_delta, $comment, $fname, $lname, $rest_title);


$orders = [];

while($stmt->fetch()){
	$a=new Order();
	$a->order_id=$order_id;
	$a->user_id = $user_id;
	$a->rest_id = $rest_id;

	$a->confirmed = $confirmed;
	$a->addr_id= $addr_id;
	
	$a->requestDate = $request_date;
	$a->requestTime = $request_time;
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
	$a->rest_balance = $balance;
	$a->rest_delta = $rest_delta;
	$a->comments = $comment;
	$a->user_name = $fname . " " .$lname;
	$a->rest_title = $rest_title;

	array_push($orders, $a);
}

//return the results:
$x = new stdClass();
$x->result = "success";
$x->data= $orders;
exit(json_encode($x));


