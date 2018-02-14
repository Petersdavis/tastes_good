<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 
include '../../swiftmailer/swift_required.php';

//var declarations
$data = json_decode(getattribute('data'));
$name=$data->name;
$email=$data->email;
$reason=$data->reason;
$comment=$data->comment;
$rest_id = $data->rest_id;

if(isset($_SESSION['user_id'])){
$user_id= $_SESSION['user_id'];
}else{
$user_id=0;
}


$time=time();

$sql = "INSERT INTO comments (rest_id, user_id, name, email, reason, comment, timestamp) VALUES (?,?,?,?,?,?,?)";
$stmt= $conn->prepare($sql);
$stmt->bind_param('iissssi', $rest_id, $user_id, $name, $email, $reason, $comment, $time);
$stmt->execute();


$stmt->close();	


if($gProd==1){

	error_reporting(E_STRICT);

	$transport = Swift_SmtpTransport::newInstance('box6176.bluehost.com', 465, 'ssl')
	->setUsername($email_user)
	->setPassword($email_pwd);
	
	$mailer = Swift_Mailer::newInstance($transport);
	
	$body                = "<body><h3> Comment Received : </h3><strong>Reason: </strong>". $reason . "<br><strong>Message: </strong>".$comment ."<br><strong>Email: </strong>".$email ."<br></body>";
	
	$message = Swift_Message::newInstance()
	->setSubject("Comment Received--".$reason)
	->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->addTo('petersdavis@gmail.com', "Peter Davis")
	->setBody($body, 'text/html')
	->addPart('Enable HTML email to view email.  Pdf Order receipt is attached below.', 'text/plain');
	
	if($order_id > 0){
		$targetPath = "../../orders/".$order_id.".pdf";
		$message->attach(Swift_Attachment::fromPath($targetPath));
	}
	
	if($mailer->send($message)){
		$x = new stdClass();
		$x->result = "success";
		exit(json_encode($x));
	}else{
	
		$x = new stdClass();
		$x->result = "failure";
		$x->error = "EMAILER_FAILURE";
		exit(json_encode($x));
	
	}
	
	
	 
	

}


