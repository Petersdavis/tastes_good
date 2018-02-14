<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php include '../php-graph-sdk-5.0.0/src/Facebook/autoload.php'; ?>
<?php

//put keys in .env
$fb = new Facebook\Facebook([
  'app_id' => $FBApp, // Replace {app-id} with your app id
  'app_secret' => $FBSecret,
  'default_graph_version' => 'v2.2',
  ]);


$creds = json_decode(getattribute("fb_Login"));

$accessToken = $creds->accessToken;			
$_SESSION['facebook_access_token'] = $accessToken;

$fb->setDefaultAccessToken($accessToken);


try {
  $response = $fb->get('/me?locale=en_US');
  $fb_user= $response->getGraphUser();



} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  $x= new stdClass();
  $x->result = "error";
  $x->error = "Graph returned an error: " . $e->getMessage();
  echo json_encode($x);	
  exit;

} catch(Facebook\Exceptions\FacebookSDKException $e) {
 // When validation fails or other local issues
  $x= new stdClass();
  $x->result = "error";
  $x->error = 'Facebook SDK returned an error: ' . $e->getMessage();
  echo json_encode($x);	
  exit;
}

$fb_fname =  $fb_user['first_name'];
$fb_lname =  $fb_user['last_name'];
$fb_email = $fb_user['email'];
$fb_id = $creds->userID;


$user = new User();

//check if user exists with facebook id?
$sql = "SELECT email FROM users WHERE fb_id= ?";
if(!$stmt = $conn->prepare($sql)){$x=new stdClass(); $x->result="error";$x->error=$conn->error; exit(json_encode($x));}
$stmt->bind_param("s", $fb_id);
if(!$stmt->execute()){$x=new stdClass(); $x->result="error"; $x->error=$stmt->error; exit(json_encode($x));}
$stmt->bind_result($email);
if($stmt->fetch()){
	//fb_id exists get user
	
	$stmt->close();
	$user->fromSession($email);
	
								
	$_SESSION['user_id']= $user->user_id;
	$_SESSION['user_name']=$user->fname . " " . $user->lname;
	$_SESSION['user_email']=$user->email; 
	$_SESSION['user_phone']=$user->phone; 
	$_SESSION['verify']=$user->verify; 
	
	$x = new stdClass();
	$x->result = "success";
	$x->data = $user;
	echo json_encode($x);
	exit;
	
	
} else {
	$stmt->close();
	
	if(strlen($fb_email)>0){
	$sql = "SELECT user_id FROM users WHERE email= ?";
	if(!$stmt = $conn->prepare($sql)){$x=new stdClass(); $x->result="error"; $x->error=$conn->error; exit(json_encode($x));}
	$stmt->bind_param("s", $email);
	if(!$stmt->execute()){$x=new stdClass(); $x->result="error"; $x->error=$stmt->error; exit(json_encode($x));}
	$stmt->bind_result($user_id);
 	if($stmt->fetch()){
 		//email exists
 	
 	 	$stmt->close();
 	 	
 	 	$sql = "UPDATE users SET fb_id = ? WHERE user_id = ?";
 	 	if(!$stmt = $conn->prepare($sql)){$x=new stdClass(); $x->result="error"; $x->error=$conn->error; exit(json_encode($x));}
		$stmt->bind_param("s", $fb_id);
		if(!$stmt->execute()){$x=new stdClass(); $x->result="error"; $x->error=$stmt->error; exit(json_encode($x));}
		$stmt->close();
		
			
		
		$user->fromSession($email);
		
		
		$_SESSION['user_id']= $user->user_id;
		$_SESSION['user_name']=$user->fname . " " . $user->lname;
		$_SESSION['user_email']=$user->email; 
		$_SESSION['user_phone']=$user->phone; 
		$_SESSION['verify']=$user->verify; 
	
	
		$x = new stdClass();
		$x->result = "success";
		$x->data = $user;
		echo json_encode($x);
		exit;
 	 	
 			 	
 	} else {
 	
 	//EMAIL DNE --> Register New Account
 	$stmt->close();
 	$x = new stdClass();
	$x->result = "new_user";
	
	if(isset($fb_id)){$user->fb_id = $fb_id;}
	if(isset($fb_fname)){$user->fname = $fb_fname;}
	if(isset($fb_lname)){$user->lname = $fb_lname; }
	if(isset($fb_email)){ $user->email= $fb_email; }	
	$x->data = $user;
	echo json_encode($x);
	exit;
	
 	
 	}
	
	}else{
	//NO EMAIL  --> Get Email
	$x = new stdClass();
	$x->result = "no_email";
	
	if(isset($fb_id)){$user->fb_id = $fb_id;}
	if(isset($fb_fname)){$user->fname = $fb_fname;}
	if(isset($fb_lname)){$user->lname = $fb_lname; }
	if(isset($fb_email)){ $user->email= $fb_email; }	
	$x->data = $user;
	echo json_encode($x);
	exit;
		

	}
}


?>