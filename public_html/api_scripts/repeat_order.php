<?php 

$error = [];
include '../boilerplate.php';
include '../dbconnect.php'; 


//Security CHECK
$secure = new Secure();
if(!$secure->user_id){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_session";
	exit(json_encode($x));
}


$user_id = $secure->user_id;
$order_id = getattribute('order_id');


$sql="SELECT coupon, user_id, rest_id,  order_serial, comment FROM restaurant_orders WHERE order_id = ?";

if(!$stmt=$conn->prepare($sql)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $conn->error;
	exit(json_encode($x));
}

if(!$stmt->bind_param("i", $order_id)){
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
	
if(!$stmt->bind_result($coupon, $usr_id, $rest_id, $serial, $comment)){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = $stmt->error;
	exit(json_encode($x));
}


if($stmt->fetch()){
	$stmt->close();
	
	if($usr_id !== $user_id){
		$x=new stdClass();
		$x->result = "failure";
		$x->error = "access_denied";
		exit(json_encode($x));
	} else {
	$user = new User();
	$user->fromId($user_id);
	
	
	//get Restaurant
	$a = new Restaurant();
	$a->grabRest($rest_id);
	$a->grabCoupons($rest_id);
	$a->grabSerial($rest_id);
		
	//make new Order
	$b =new stdClass();
	$b->time = microtime();
	$b->user = $user;
	$b->rest_id = $rest_id;
	$b->comment = $comment;
	$b->items = unserialize($serial);
	
	//Check Coupon
	
	if($coupon!= 0){
		$found_coup = 0;
		
		foreach($a->coupons as $coup){
			if($coup->code == $coupon){
			$b->coupon = $coup;
			$found_coup = 1;
			}
			
		}
		
		if($found_coup !== 1){
		$x = new stdClass();
		$x->error = "BAD_COUP";
		$x->id = $coupon;
		array_push($error, $x);
		
		}
				
	}

			
		//Check Items
		
	//rebuild the orders
	
	$b->subtotal = 0;
	//check all prices :
	foreach($b->items as $item){
	
		$b->subtotal = fixPrice($item, $a->menu, $a->coupons, $b->subtotal);
	}
	
	if(sizeof($error)>0){
		$x=new stdClass();
		$x->result = "menu_error";
		$x->data = new stdClass();
		$x->data->errors = $error;
		$x->data->order = $b;
		$x->data->rest = $a;
		exit(json_encode($x));
	}

	}
}


$x = new stdClass();
$x->result = "success";
$x->data = new stdClass();
$x->data->order = $b;
$x->data->rest = $a;


exit(json_encode($x));
}else{

$x=new stdClass();
$x->result = "failure";
$x->error = "NO_ORDER";
exit(json_encode($x));
	
}

