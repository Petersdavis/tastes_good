<?php require_once '../boilerplate.php'; 
require_once '../dbconnect.php'; 

require_once '../../swiftmailer/swift_required.php';


//Security Check
if(isset($_SESSION['user_id'])){
	$user_id = $_SESSION['user_id'];
}else{
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "BAD_USER";
	exit(json_encode($x));
}


$errors= [];

$data = json_decode(getattribute('data'));

$pattern = '/[\D]/';
$data->phone = preg_replace ( $pattern , "" , $data->phone );

//check that email does not belong to an existing user (or) restaurant
$sql = "SELECT rest_id, title, phone, email FROM restaurants WHERE email = ? OR phone = ?";

$stmt=$conn->prepare($sql);
$stmt->bind_param("si", $data->email, $data->phone);
$stmt->execute();
$stmt->bind_result($existing_id, $existing_title, $existing_phone, $existing_email);
while($stmt->fetch()){
	$error=new stdClass();
	$error->msg = "Found Similar Restaurant: ". $existing_id .". ". $existing_title . "(" .$existing_phone ."/".  $existing_email .")";
	array_push($errors, $error);		
}
$stmt->close();

$sql = "SELECT user_id, fname, lname, phone, email FROM users WHERE email = ? OR phone = ?";

$stmt=$conn->prepare($sql);
$stmt->bind_param("si", $data->email, $data->phone);
$stmt->execute();
$stmt->bind_result($existing_id, $existing_fname, $existing_lname, $existing_phone, $existing_email);
while($stmt->fetch()){
	$error=new stdClass();
	$error->msg = "Found Similar User: ". $existing_id .". ". $existing_fname ." ". $existing_lname . "(" .$existing_phone ."/".  $existing_email .")";
	array_push($errors, $error);		
}
$stmt->close();


//get the google address & place;


$googleQuery =  $data->address;
$googleQuery = str_replace (" ", "+", $googleQuery);
$googleQuery = utf8_encode( $googleQuery );



$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='. rawurlencode($googleQuery) .'&key=AIzaSyCmMnZ4ZQrCCXcwUSYXOkqmU9tMjK5lxxs&sensor=false';



$cURL = curl_init();

if(!curl_setopt($cURL, CURLOPT_URL, $url)){echo 'error curl_setopt:CURLOPT_URL';} 
if(!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1)){echo 'error curl_setopt:CURLOPT_RETURNTRANSFER';}
if(!curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false)){echo 'error curl_setopt:CURLOPT_SSL_VERIFYPEER';}
$result = json_decode(curl_exec($cURL), true);

curl_close($cURL);

if ($result['status']=="OK"){
	
	$lat = $result['results'][0]['geometry']['location']['lat'];
	$lng = $result['results'][0]['geometry']['location']['lng'];
	$data->address = $result['results'][0]['formatted_address'];
	
	
	$sql = "INSERT INTO restaurants (title, address, community, lat, lng, phone, email, status) VALUES (?,?,?,?,?,?,?,'PENDING')";
	$stmt=$conn->prepare($sql);		
	$stmt->bind_param('sssddis',$data->title, $data->address, $data->community, $lat, $lng, $data->phone, $data->email);
	$stmt->execute();
	
	$rest_id = $stmt->insert_id;
}else{

	$sql = "INSERT INTO restaurants (title, address, community, phone, email, status) VALUES (?,?,?,?,?,?,?,'PENDING')";
	$stmt=$conn->prepare($sql);		
	$stmt->bind_param('sssddis',$data->title, $data->address, $data->community, $data->phone, $data->email);
	$stmt->execute();
	
	$rest_id = $stmt->insert_id;
	
	$error = new stdClass();
	$error->msg = "Google Maps API could not find the address for lat/lng.";
	array_push($errors, $error);
}

$rest = new Restaurant();
$rest->rest_id = $rest_id;
$token=$rest->createToken();

//Create Pending contract for sales_agent
$time = time();
$sql = "INSERT INTO sales_junction (sales_id, rest_id, commission_term, is_pending) VALUES (?,?,?,1);";
$stmt=$conn->prepare($sql);	
	echo $stmt->error;
$stmt->bind_param('iii', $user_id, $rest_id, $time);
echo $stmt->error;
$stmt->execute();
echo $stmt->error;

$stmt->close();

//get admin emails
$emails = [];
$sql = "SELECT email FROM users WHERE is_admin = 1";
$stmt=$conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($email);
while($stmt->fetch()){
	array_push($emails, $email);
}

//Send email to admin.
$transport = Swift_SmtpTransport::newInstance('box6176.bluehost.com', 465, 'ssl')
	->setUsername($email_user)
	->setPassword($email_pwd);
	
	
$mailer = Swift_Mailer::newInstance($transport);

$headline = "New Prospect Pending:";
$content = "";
$content = $content . "<strong>Title: </strong> ". $data->title . "<br>";
$content = $content . "<strong>Address: </strong> ". $data->address . "<br>";
$content = $content . "<strong>Phone: </strong> ". $data->phone . "<br>";
$content = $content . "<strong>Email: </strong> ". $data->email . "<br><br>";

$content = $content . "<h2>Errors: </h2> ";
foreach($errors as $error){
$content = $content . "<strong>".$error->msg."</strong><br>" ;
}

$href1 = "https://www.tastes-good.com/uploader/admin_approve_prospect.php?rest_id=".$rest_id."&auth=".$token;
$href2 = "https://www.tastes-good.com/uploader/admin_reject_prospect.php?rest_id=".$rest_id."&auth=".$token;
$link = "<a href = '".$href1."'>Approve Prospect </a><br><a href = '".$href2."'>Reject Prospect </a>";


$body                = file_get_contents('../email_template/blank_template.html');
$body                = preg_replace('/template_headline/', $headline, $body);
$body                = preg_replace('/template_content/',$content, $body);
$body                = preg_replace('/template_link/', $link, $body);

$message = Swift_Message::newInstance()
->setEncoder(Swift_Encoding::get8BitEncoding())
->setSubject("New Prospect Pending:")
->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'));

foreach($emails as $email){
	$message->addTo($email, "Systems Administrator");
}

$contact_us = $message->embed(Swift_Image::fromPath('../images/email/privacy.png'));
$terms = $message->embed(Swift_Image::fromPath('../images/email/contact_us.png'));
$privacy = $message->embed(Swift_Image::fromPath('../images/email/termsconditions.png'));
$tg_image = $message->embed(Swift_Image::fromPath('../images/logo/tg.png'));

$body= preg_replace('/tg_image/', $tg_image  ,$body);
$body= preg_replace('/contact_us_img/', $contact_us ,$body);
$body= preg_replace('/terms_img/', $terms ,$body);
$body= preg_replace('/privacy_img/', $privacy ,$body);
str_replace('\"','"',$body);


$message->setBody($body, 'text/html')
->addPart('Enable HTML email to view email.  Pdf Order receipt is attached below.', 'text/plain');

$mailer->send($message);


$conn->close();

$x = new stdClass();
$x->result = "success";
$x->errors = $errors;

$y = new stdClass();
$y->token = $token;
$y->rest_id = $rest_id;

$x->data = $y;
exit(json_encode($x));
?>