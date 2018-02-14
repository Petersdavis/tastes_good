<?php require_once '../boilerplate.php'; 
require_once '../dbconnect.php'; 

include '../../swiftmailer/swift_required.php';

//get rest_id
$rest_id = getattribute("rest_id");

//get token;
$token= getattribute("auth");

//verify token

$rest = new Restaurant();
$rest->rest_id = $rest_id;
$rest->verifyToken($token);
$rest->grabRest($rest_id);


//set Prospect
$sql = "UPDATE restaurants SET status = 'PROSPECT' WHERE rest_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $rest_id);
$stmt->execute();
$stmt->close ();


//Update prospect contract for sales_agent

$expires = time() + (4 * 7 * 24 * 60 * 60);
$sql = "UPDATE sales_junction SET commission_term = ?, is_pending = 0, is_prospect = 1 WHERE rest_id = ?";
$stmt=$conn->prepare($sql);		
$stmt->bind_param('ii', $expires, $rest_id);
$stmt->execute();
$stmt->close();

//Get Sales User:

$sql = "SELECT sales_id FROM sales_junction WHERE rest_id = ?";
$stmt=$conn->prepare($sql);		
$stmt->bind_param('i', $rest_id);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

$a= new User();
$a->user_id = $user_id;

$a->fromSession();



//$token = $rest->createToken();

$link = "https://www.tastes-good.com/dashboard.html?registration#".$token ;


$transport = Swift_SmtpTransport::newInstance('box6176.bluehost.com', 465, 'ssl')
	->setUsername($email_user)
	->setPassword($email_pwd);
	
$mailer = Swift_Mailer::newInstance($transport);

$body                = file_get_contents('../email_template/invitation_template.html');
$body                = preg_replace('/sales_name/',$a->fname . " ". $a->lname, $body);
$body                = preg_replace('/salesforce_link/', $link, $body);

$message = Swift_Message::newInstance()
->setEncoder(Swift_Encoding::get8BitEncoding())
->setSubject("Invitation to www.Tastes-Good.com")
->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
->addTo($rest->email, $rest->title)
->addTo("corporate@tastes-good.com", "Tastes-Good.com");




$contact_us = $message->embed(Swift_Image::fromPath('../images/email/privacy.png'));
$terms = $message->embed(Swift_Image::fromPath('../images/email/contact_us.png'));
$privacy = $message->embed(Swift_Image::fromPath('../images/email/termsconditions.png'));
$tg_image = $message->embed(Swift_Image::fromPath('../images/logo/tg.png'));

$body= preg_replace('/tg_image/', $tg_image  ,$body);
$body= preg_replace('/contact_us_img/', $contact_us ,$body);
$body= preg_replace('/terms_img/', $terms ,$body);
$body= preg_replace('/privacy_img/', $privacy ,$body);
$body=str_replace('\"','"',$body);
$message->setBody($body, 'text/html')
->addPart('Enable HTML email to view email.', 'text/plain');


$mailer->send($message);

$x = new stdClass();
$x->result = "success";

$y = new stdClass();
$y->rest_id =$rest_id;
$y->token = $token;
$x->data = $y;
exit(json_encode($x));

?>
