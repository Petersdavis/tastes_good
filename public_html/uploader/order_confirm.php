<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 
include '../../fpdf/fpdf.php';
include '../../swiftmailer/swift_required.php';
require_once("../braintree_init.php"); 

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

if ($owner_id = $_SESSION['user_id'] || $secure->is_admin()){

$sql = "Update restaurant_orders Set confirmed = 1 Where order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();




//braintree process the payment.


$sql = "SELECT env, payment_type, transaction FROM restaurant_orders WHERE order_id = ?";
$stmt= $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($env, $payment_type, $transact);
if($stmt->fetch()){
$stmt->close();

if($payment_type == "online" && $env==1){

BTconfig($BTproduct);

$result = Braintree_Transaction::submitForSettlement($transact);

if ($result->success) {

//get customer and restaurant details;
$sql = "Select rest_id, user_id From restaurant_orders where order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($rest_id, $user_id);
$stmt->fetch();
$stmt->close();

$rest = new Restaurant();
$rest->grabRest($rest_id);

$z= new User();
$user->fromId($user_id);



//send email to customer
    $transport = Swift_SmtpTransport::newInstance('box6176.bluehost.com', 465, 'ssl')
	->setUsername($email_user)
	->setPassword($email_pwd);
    $mailer = Swift_Mailer::newInstance($transport);
    
	$body                = file_get_contents('../email_template/email_order.html');
	$body                = preg_replace('/restaurant_title/',$rest->title,$body);
	$body                = preg_replace('/restaurant_phone/',$rest->phone,$body);
	$body                = preg_replace('/restaurant_address/',$rest->address,$body);
	$body                = preg_replace('/order_id/', $order_id ,$body);
	$body                = preg_replace('/user_email/', $user->email ,$body);
	$message = Swift_Message::newInstance()
	->setEncoder(Swift_Encoding::get8BitEncoding())
	->setSubject("Order Confirmation: Order #". $order_id)
	->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->addTo($user->email, $user->fname . " ". $user->lname);
	$thumbs_up = $message->embed(Swift_Image::fromPath('../images/email/thumbs_up.jpg'));
	$thumbs_down = $message->embed(Swift_Image::fromPath('../images/email/thumbs_down.jpg'));
	$contact_us = $message->embed(Swift_Image::fromPath('../images/email/privacy.png'));
	$terms = $message->embed(Swift_Image::fromPath('../images/email/contact_us.png'));
	$privacy = $message->embed(Swift_Image::fromPath('../images/email/termsconditions.png'));
	$tg_image = $message->embed(Swift_Image::fromPath('../images/logo/tg.png'));
	
	$body= preg_replace('/tg_image/', $tg_image  ,$body);
	$body= preg_replace('/thumbs_up_img/', $thumbs_up ,$body);
	$body= preg_replace('/thumbs_down_img/', $thumbs_down ,$body);
	$body= preg_replace('/contact_us_img/', $contact_us ,$body);
	$body= preg_replace('/terms_img/', $terms ,$body);
	$body= preg_replace('/privacy_img/', $privacy ,$body);
	str_replace('\"','"',$body);
	
	
	$message->setBody($body, 'text/html')
	->addPart('Enable HTML email to view email.  Pdf Order receipt is attached below.', 'text/plain')
	->attach(Swift_Attachment::fromPath($targetPath));
	
	$mailer->send($message);
    
    
    //update transaction
    $settledTransaction = $result->transaction;
} else {
//error handling logic
// print_r($result->errors);
    
    
}

}

$x =new stdClass();
$x->result = "success";
exit(json_encode($x));
}else{

$x = new stdClass();
$x->result = "failure";
$x->error = "NO_ORDER";
exit(json_encode($x));
}

}else{

$x = new stdClass();
$x->result = "failure";
$x->error = "AUTH_ERROR";
exit(json_encode($x));
}



$conn->close();

