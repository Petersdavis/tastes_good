<?php include '../boilerplate.php';  
checkProduction();
include '../dbconnect.php';


if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}

$user = new User();
$user->fromSession();

$coupon=getattribute('coupon');

		
$pattern = '/[\D]/';
$coupon = preg_replace ( $pattern , "" , $coupon);


$sql = "SELECT rest_id FROM coupons WHERE id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $coupon);
$stmt->execute();
$stmt->bind_result($rest_id);
if($stmt->fetch()){
	
	$pass=0;
	foreach($user->restaurants as $rest){
		if($rest->rest_id == $rest_id){
			$pass=1;
		}
	}	
	
	if ($pass == 0){
		$x = new stdClass();
		$x->result = "failure";
		$x->error = "bad_user_rest";
		exit(json_encode($x));
	}
	
}else{
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "NO_COUP";
	exit(json_encode($x));
}

$stmt->close();

$sql="DELETE FROM coupons WHERE id = ?";
$stmt=$conn->prepare($sql);
echo $conn->error;
$stmt->bind_param("i", $coupon);

$stmt->execute();

if($stmt->affected_rows == 0){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "NO_COUP";
	exit(json_encode($x));
}

$stmt->close();

$x = new stdClass();
$x->result="success";

echo json_encode($x);