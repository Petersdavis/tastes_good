<?php
include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 


//Security Check
if(isset($_SESSION['user_id'])){
	$user_id = $_SESSION['user_id'];
}else{
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "BAD_USER";
	exit(json_encode($x));
}


$restaurants = [];
$customers = [];

$sql = "SELECT rest_id, user_id, commission_term, commission_rate, total_commission FROM sales_junction WHERE sales_id =?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($rest_id, $cust_id, $commission_term, $commission_rate, $total_commission);

while($stmt->fetch()){
	
	
	if($rest_id > 0){
		$a =  new Restaurant();
		$a->rest_id = $rest_id;
		$a->commission_term = $commission_term;
		$a->commission_rate = $commission_rate;
		$a->total_commission= $total_commission;
		array_push($restaurants, $a);
	}elseif($cust_id > 0){
		$a = new User();
		$a->user_id = $cust_id;
		$a->commission_term = $commission_term;
		$a->commission_rate = $commission_rate;
		$a->total_commission= $total_commission;
		array_push($customers, $a);
	}
	
}

$stmt->close();


foreach($restaurants as $a){
	$a->grabRest($a->rest_id);
	
	if($a->status == "ACTIVE"){
		$sql =  "SELECT SUM(restaurant_orders.order_subtotal), COUNT(restaurant_orders.order_id) FROM restaurant_orders WHERE rest_id = ? AND order_time < ? GROUP BY rest_id";
		$stmt =$conn->prepare($sql);
		$time = $a->commission_term *1000;
		$stmt->bind_param("ii", $a->rest_id, $time);
		$stmt->execute();
		$stmt->bind_result($a->sales_total, $a->sales_count);
		$stmt->fetch();
		$stmt->close();
	}
}

foreach($customers as $a){
	$sql = "SELECT users.fname, users.lname, SUM(restaurant_orders.order_subtotal), COUNT(restaurant_orders.order_id) FROM users LEFT JOIN restaurant_orders ON users.user_id = restaurant_orders.user_id WHERE users.user_id = ? GROUP BY users.user_id";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $a->user_id);
	$stmt->execute();
	$stmt->bind_result($a->fname, $a->lname, $a->sales_total, $a->sales_count);
	$stmt->fetch();
	$stmt->close();
	
}


$x = new stdClass();
$x->result = "success";
$x->restaurants= $restaurants;
$x->customers = $customers;

exit(json_encode($x));
?>



 