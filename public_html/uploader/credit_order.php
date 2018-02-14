<?php 
include '../boilerplate.php'; 
include '../dbconnect.php'; 
include '../../swiftmailer/swift_required.php';

$order_id = getattribute('order_id');
$credit = getattribute('credit');
$message = getattribute('message');


if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}

$user = new User();
$user->fromSession();


$sql = "Select rest_id, user_id, link from restaurant_orders where order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($rest_id, $user_id, $targetPath);
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



$sql = "Select email, fname, lname from users where user_id = ?"
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $fname, $lname);
$stmt->fetch();
$user_name = $fname . " ". $lname;

$stmt->close();

$coupon = new stdClass();

//Build Coupon
$expires = time() + (12*31*24*60*60);
$coupon->expires = date("F j, Y", $expires);
$timestamp = time();
$ext = json_encode($coupon->extras);
$check = 0;

while($check==0){
	$coupon_code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
		
	$sql = "SELECT * FROM coupons WHERE code = '". $coupon_code . "'";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0){
		$check = 1;
		$coupon->code = $coupon_code;
	}
	$stmt->free_result();
	$stmt->close();
}



$coupon->link = "https://www.tastes-good.com/order.php?rest_id=".$rest_id."&coupon=".$coupon_code;
$coupon->type = "item";
$coupon->public = 0;
$coupon->title = "$".$credit ." Store Credit";
$coupon->price = (-1)* $credit;
//Store the Coupon

$sql = "INSERT INTO coupons (code, link, rest_id, type, timestamp, expires, public, title, price) values (?,?,?,?,?,?,?,?,?)";
$stmt=$conn->prepare($sql);			

echo $conn->error;
$stmt->bind_param("ssissssss", $coupon->code, $coupon->link, $rest_id, $coupon->type, $timestamp, $expires, $coupon->public, $coupon->title, $coupon->price);


$stmt->execute();
$coupon->id = $stmt->insert_id;
$stmt->close();

//Email the Coupon

$x = new stdClass();
$x->result = "success";
$x->coupon = $coupon;

echo json_encode($x);
//send an email
if($gProd==1){

	error_reporting(E_STRICT);
	$transport = Swift_SmtpTransport::newInstance('box6176.bluehost.com', 465, 'ssl')
	->setUsername($email_user)
	->setPassword($email_pwd);
	
	$mailer = Swift_Mailer::newInstance($transport);
	
	$body                = file_get_contents('../email_template/email_order_credit.html');
	$body                = preg_replace('/user_name/',$user_name, $body);
	$body                = preg_replace('/coupon_title/', $coupon->title,$body);
	$body                = preg_replace('/coupon_link/',$coupon->link,$body);
	$body                = preg_replace('/coupon_image/', $a->image, $body);
	$body                = preg_replace('/coupon_code/', $coupon->code, $body);
	$body                = preg_replace('/coupon_price/', $credit, $body);
	$body                = preg_replace('/restaurant_message/', $message, $body);
	$body                = preg_replace('/order_id/', $order_id, $body);
	
	$message = Swift_Message::newInstance();
	->setSubject("Credit From Restaurant Re: Order #". $ord->order_id )
	->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->addTo($user->email, $user->fname . " ". $user->lname)
	->setBody($body, 'text/html')
	->addPart('Enable HTML email to view email.  Pdf Order receipt is attached below.', 'text/plain')
	->attach(Swift_Attachment::fromPath($targetPath));
	
	$result = $mailer->send($message);
	
	
  } else {
	echo "NOT_PRODUCTION_NO_EMAIL_SUPPORT";
  }


}
$conn->close();