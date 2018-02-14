<?php include '../boilerplate.php';
include '../dbconnect.php';

$newUser = json_decode(getattribute('newUser'));		
$a = new User();
$a->fname = $newUser->fname;
$a->lname = $newUser->lname;
$a->phone= $newUser->phone;
$a->password = $newUser->password;
$a->email = $newUser->email;
$a->fb_id = $newUser->fb_id;

$pattern = '/[\D]/';
$a->phone = preg_replace ( $pattern , "" , $a->phone );


$result = $a->putNewToDB();
if($result->result == "success"){

$_SESSION['user_id']= $a->user_id;
$_SESSION['user_name']=$a->fname . " " . $a->lname;
$_SESSION['user_email']=$a->email; 
$_SESSION['user_phone']=$a->phone; 

$x = new stdClass();
$x->result = "success";
$x->data = $a;
$x->error = "";
exit(json_encode($x));

}else{

$x = new stdClass();
$x->result = "error";
$x->data = $a;
$x->error = "";
exit(json_encode($x));

}	
?>
