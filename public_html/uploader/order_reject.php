<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 
include '../../fpdf/fpdf.php';
include '../../swiftmailer/swift_required.php';
require_once("../braintree_init.php"); 



error_reporting(E_STRICT);







$secure = new Secure();
if(!$secure->user_id){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}


if( !getattribute('orderId')){
	$x = new stdClass();
	$x->error = "No Order Id";
	$x->result = "failure";
	exit(json_encode($x));
	
}else{

$order_id = getattribute('orderId');

}

$sql = "SELECT restaurants.owner_id FROM restaurants INNER JOIN restaurant_orders ON restaurants.rest_id = restaurant_orders.rest_id WHERE restaurant_orders.order_id =?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($owner_id);
$stmt->fetch();
$stmt->close(); 

if($owner_id = $_SESSION['user_id'] || $secure->is_admin()){

$sql = "SELECT rest_id, user_id, env, payment_type, transaction FROM restaurant_orders WHERE order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i",$order_id);
$stmt->execute();
$stmt->bind_result($rest_id, $user_id, $env, $payment_type, $transact);
$stmt->fetch();
$stmt->close();


$sql = "Update restaurant_orders Set confirmed = -1 Where order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();


if($payment_type == "online" && $env==1){
BTconfig($BTproduct);
$result = Braintree_Transaction::void($transact);

if ($result->success) {
    //update transaction

    $settledTransaction = $result->transaction;
} else {
//error handling logic
// print_r($result->errors);
    
    
}

}

	

date_default_timezone_set('America/Toronto');

	
	$transport = Swift_SmtpTransport::newInstance('box6176.bluehost.com', 465, 'ssl')
	->setUsername($email_user)
	->setPassword($email_pwd);
	
	$mailer = Swift_Mailer::newInstance($transport);
	
	$body = "<h3>Restaurant Rejected Order.</h3><br> Contact Restaurant to Determine Reason"; 
	$targetPath = "../../orders/".$order_id .".pdf";
	
	
	$message = Swift_Message::newInstance()
	->setSubject("Order Rejection: Order #". $ord->order_id)
	->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->addTo('corporate@tastes-good.com',  'Peter Davis')
	->addCC('petersdavis@gmail.com',  'Peter Davis')
	->setBody($body, 'text/html')
	->addPart('Enable HTML email to view email.  Pdf Order receipt is attached below.', 'text/plain')
	->attach(Swift_Attachment::fromPath($targetPath));
	
	 $mailer->send($message);
	
	



$x = new stdClass();
$x->result="success";
exit(json_encode($x)); 
}else{

$x = new stdClass();
$x->result = "failure";
$x->error = "AUTH_ERROR";
exit(json_encode($x));
}


?>