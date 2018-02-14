<?php include '../boilerplate.php';
include '../dbconnect.php';
include '../../swiftmailer/swift_required.php';

$data= json_decode(getattribute('data'));

$email = $data->email;

$sql = "Select user_id, fname, lname FROM users WHERE email = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows == 0){
	$x = new stdClass;
	$x->result = "fail";
	$x->error = "NO_EMAIL";
	echo json_encode($x);
	$stmt->close();
	exit();
}
if($stmt->num_rows > 1){
	$x = new stdClass;
	$x->result = "fail";
	$x->error = "MULTI_EMAIL";
	echo json_encode($x);
	$stmt->close();
	exit();
}	

$stmt->bind_result($user_id, $fname, $lname);
$stmt->fetch();

$key = substr(str_shuffle("23456789ABCDEFGHJKLMNPQRSTUVWXYZ"), 0, 15);
$expires = time() + 60*60*3;

$stmt->close();

$sql = "INSERT INTO user_pwd_reset (user_id, reset_key, expires) VALUES (?,?,?)";
$stmt= $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $key, $expires);
$stmt->execute();
$stmt->close();

$user_name= $fname. " ".  $lname;
$reset_url = "https://www.tastes-good.com/pwd_reset.php?key=".$key."&user_id=".$user_id;


if($gProd==1){

	error_reporting(E_STRICT);
	$transport = Swift_SmtpTransport::newInstance('box6176.bluehost.com', 465, 'ssl')
	->setUsername($email_user)
	->setPassword($email_pwd);
	
	$mailer = Swift_Mailer::newInstance($transport);
	
	$message = Swift_Message::newInstance()
	->setSubject("Password Reset:")
	->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->addTo($email, $user_name);
	
	$contact_us = $message->embed(Swift_Image::fromPath('../images/email/privacy.png'));
	$terms = $message->embed(Swift_Image::fromPath('../images/email/contact_us.png'));
	$privacy = $message->embed(Swift_Image::fromPath('../images/email/termsconditions.png'));
	$tg_image = $message->embed(Swift_Image::fromPath('../images/logo/tg.png'));
	
	$body  = file_get_contents('../email_template/email_pwd.html');
	$body= preg_replace('/tg_image/', $tg_image  ,$body);
	$body = preg_replace('/pwd_reset_url/',$reset_url ,$body);
	$body = preg_replace('/user_name/',$user_name ,$body);
	$body= preg_replace('/contact_us_img/', $contact_us ,$body);
	$body= preg_replace('/terms_img/', $terms ,$body);
	$body= preg_replace('/privacy_img/', $privacy ,$body);
	str_replace('\"','"',$body);
	
	$message->setBody($body, 'text/html')
	->addPart('Enable HTML email to view email.  Pdf Order receipt is attached below.', 'text/plain');
	
	if( $mailer->send($message)){
	
		$x = new stdClass();
		$x->result = "success";
		$x->email = $email;
		exit(json_encode($x));
	}else{
		$x = new stdClass();
		$x->result = "failure";
		$x->error = "mailer_failed";
		$x->email = $email;
		exit(json_encode($x));
	}
	
	
}else{
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "not_production";
	$x->email = $email;
	exit(json_encode($x));

}
		
