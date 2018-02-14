<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 

error_reporting(E_ALL);
ini_set('display_errors', 'On');


//Security Check
if(isset($_SESSION['user_id'])){
	$user_id = $_SESSION['user_id'];
}else{
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "BAD_USER";
	exit(json_encode($x));
}

$sql = "SELECT is_sales FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($is_sales);
$stmt->fetch();
$stmt->close();

if(!$is_sales){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "NOT_SALES_AUTH";
	exit(json_encode($x));
}




$sql="SELECT COUNT(rest_id), COUNT(user_id), COUNT(is_prospect) FROM sales_junction WHERE sales_id = ? AND commission_term > ?";
$stmt=$conn->prepare($sql);
$time = time();
$stmt->bind_param("ii", $user_id, $time);
$stmt->execute();
$stmt->bind_result($restaurants, $customer, $prospect);

$a = new stdClass();
$a->active = $restaurants - $prospect;
$a->prospect = $prospect;
$a->customer = $customer;

$x = new stdClass();
$x->data=$a;
$x->result = "success";
exit(json_encode($x));

		
	