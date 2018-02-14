<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 
include '../../swiftmailer/swift_required.php';
require_once("../braintree_init.php"); 


if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}

$user = new User();
$user->fromSession();

$order_id = getattribute('order_id');
$reason = getattribute ('reason');
$time = time();


if(!$reason){
	$reason = "NONE GIVEN";
}

$sql = "Select rest_id from restaurant_orders where order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($rest_id);
if($stmt->fetch();){
$stmt->close();

$pass = 0;
foreach($user->restaurants as $a){
	if($a->rest_id == $rest_id){
		$pass=1;
		break;
	}
}

if($pass==0){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "user_not_own_rest";
	exit(json_encode($x));
}


$sql = "INSERT INTO order_cancel (order_id, timestamp, reason) Values (?,?,?)";
$stmt=$conn->prepare($sql);
$stmt->bind_param("iis", $order_id, $time, $reason);

$stmt->execute();
$stmt->close();
if($gProd==1){

	error_reporting(E_STRICT);
	
	$transport = Swift_SmtpTransport::newInstance('box6176.bluehost.com', 465, 'ssl')
	->setUsername($email_user)
	->setPassword($email_pwd);
	
	$mailer = Swift_Mailer::newInstance($transport);
	
	$body                = "<body><h3> ORDER ID :". $order_id .". REQUESTS CANCEL BY RESTAURANT</h3></body>";
	
	$message = Swift_Message::newInstance();
	->setSubject("Order Cancelation Requested: Order #". $ord->order_id)
	->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->addTo('petersdavis@gmail.com', "Peter Davis")
	->setBody($body, 'text/html')
	->addPart('Enable HTML email to view email.  Pdf Order receipt is attached below.', 'text/plain')
	->attach(Swift_Attachment::fromPath("../../orders/".$order_id.".pdf"));
	
	if($mailer->send($message)){
		echo "success: email sent";
	}
	
	 
	

}

