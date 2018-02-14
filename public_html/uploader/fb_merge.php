<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php

$a = new User ();
$a->email= getattribute("email");
$a->fb_id= getattribute("fb_id");
$password = getattribute("password");

$sql = "Select password FROM users WHERE email = ?";              
$stmt = $conn->prepare($sql);                                                                
$stmt->bind_param("s", $a->email);
$stmt->execute();
$stmt->bind_result($password_hash);
if($stmt->fetch()){	
	$stmt->close();
	if(password_verify($password, $password_hash)){
		//get the user		
		$sql = "SELECT user_id, fname, lname, phone FROM users WHERE email= ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $a->email);
		$stmt->execute();
		$stmt->bind_result($user_id, $fname, $lname, $phone);
		$stmt->fetch();
		$stmt->close();
		
		$a->user_id = $user_id;
		$a->fname = $fname;
		$a->lname = $lname;
		$a->phone = PrettyPhone($phone);
		$a->verify = password_hash($email, PASSWORD_BCRYPT);
		$a->getAddresses();
					
		//Set the Session Variables										
		$_SESSION['user_id']= $a->user_id;
		$_SESSION['user_name']=$a->fname . " " . $a->lname;
		$_SESSION['user_email']=$a->email; 
		$_SESSION['user_phone']=$a->phone; 
		$_SESSION['verify']=$a->verify; 
		
		//Update the Database
		
		$sql = "UPDATE users SET fb_id = ? WHERE email = ?";
		$stmt = $conn->prepare($sql);                                                                
		$stmt->bind_param("ss", $a->fb_id, $a->email);
		$stmt->execute();
		$stmt->close();
		
		
		
		$x = new stdClass();
		$x->result = "success";
		$x->data = $a;
		$x->error = "";
			
		exit(json_encode($x));
		
	}else{
			
			
		$stmt->free_result();
		$stmt->close();
		
		$x = new stdClass();
		$x->result = "error";
		$x->error = "pwd_wrong";
		return $x;
		
		//passwords don't match.  
		
	}
			
			
}else {
	//email does not exist	
	$stmt->close();
	$x = new stdClass();
	$x->result = "error";
	$x->error = "NO_EMAIL";
	return $x;
	
}
		
		
		
if ($result->result == "success"){
	$result->data = $a;
}
echo json_encode($result);
