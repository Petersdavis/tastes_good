<?php include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 


if(isset($_SESSION['user_email'])){
	$email = $_SESSION['user_email'];	
	$user = new User();
	$user->fromSession($email);
	$user->user_name = $_SESSION['user_name'];
	
	if(getattribute("sales")){
		$sql="SELECT COUNT(rest_id), COUNT(user_id), COUNT(is_prospect) FROM sales_junction WHERE sales_id = ? AND commission_term > ?";
		$stmt=$conn->prepare($sql);
		$time = time();
		$stmt->bind_param("ii", $a->user_id, $time);
		$stmt->execute();
		$stmt->bind_result($restaurants, $customer, $prospect);
		$a->active = $restaurants - $prospect;
		$a->prospect = $prospect;
		$a->customer = $customer;
		
	}

	
		
}else {
	$user = new User();
}
$x = new stdClass();
$x->data = $user;
$x->result = "success";
echo json_encode($x);


?>