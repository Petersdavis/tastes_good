<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 

include '../../swiftmailer/swift_required.php';



//Security Check
if(isset($_SESSION['user_id'])){
	$user_id = $_SESSION['user_id'];
}else{
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "BAD_USER";
	exit(json_encode($x));
}

$a = new User();
$a->user_id = $user_id;
$a->fromSession();

if(!$a->is_sales){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "NOT_SALES_AUTH";
	exit(json_encode($x));
}


$data = json_decode(getattribute("data"));
$email = $data->email;
$rest_id = $data->rest_id;

$b= new Restaurant();
$b->rest_id = $rest_id;

$sql = "SELECT status FROM restaurants WHERE rest_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $rest_id);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();

if($status !== "NEW"){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "BAD_REST_STATUS: " . $status;
	exit(json_encode($x));
}

//update the restaurant details

$sql = "UPDATE restaurants SET email = ?, status = 'PROSPECT' WHERE rest_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $email, $rest_id);
$stmt->execute();
$stmt->close ();

$b->grabRest($rest_id);


//create prospect contract:

$expires = time() + (4 * 7 * 24 * 60 * 60);
$sql = "INSERT INTO sales_junction (sales_id, rest_id, commission_term, is_prospect) VALUES (?,?,?,1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $a->user_id, $rest_id, $expires);
$stmt->execute();
$stmt->close ();

//email the restaurant;

$token = $b->createToken();
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
->addTo($email, $b->title)
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
exit(json_encode($x));

?>