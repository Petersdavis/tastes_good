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

$data = json_decode(getattribute('data'));

$order_id = $data->order_id;
$refund = $data->refund;
$reason = $data->reason;
$time = time();


$sql = "SELECT rest_id FROM restaurant_orders WHERE order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($rest_id);
$stmt->fetch();

$pass = 0;
foreach($user->restaurants as $a){
	if($a->rest_id == $rest_id){
		$pass=1;
	}
}

if($pass==0){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "user_not_own_rest";
	exit(json_encode($x));
}

$sql="INSERT INTO order_refund (order_id, timestamp, reason, refund) VALUES (?,?,?,?)";
$stmt=$conn->prepare($sql);
$stmt->bind_param("iisd", $order_id, $time, $reason, $refund);
$stmt->execute();
$stmt->close();

//bt refunds the order

///LOGIC HERE///

if($gProd==1){

	error_reporting(E_STRICT);
	
	$transport = Swift_SmtpTransport::newInstance('box6176.bluehost.com', 465, 'ssl')
	->setUsername($email_user)
	->setPassword($email_pwd);
	
	$mailer = Swift_Mailer::newInstance($transport);
	
	$body = "<body><h3> ORDER ID :". $order_id .". REQUESTS Refund BY RESTAURANT</h3></body>";
	$targetPath = "../../orders/".$order_id.".pdf";
	$message = Swift_Message::newInstance();
	->setSubject("Order Refund Requested: Order #". $order_id)
	->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->addTo('refunds@tastes-good.com', "Peter Davis")
	->setBody($body, 'text/html')
	->addPart('Enable HTML email to view email.  Pdf Order receipt is attached below.', 'text/plain')
	->attach(Swift_Attachment::fromPath($targetPath));
	
	if($mailer->send($message)){
		$x = new stdClass()
		$x->result = "success";
		exit(json_encode($x));
	}else{
		$x = new stdClass()
		$x->result = "failure";
		$x->error = "EMAIL_FAILURE";
		exit(json_encode($x));
	}
	

}
		
