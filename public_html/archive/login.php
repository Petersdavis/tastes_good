<?php
include ("boilerplate.php");
include ("dbconnect.php");
	
if(!($stmt = $conn->prepare("SELECT rest_id, password, status, community FROM restaurants WHERE user_name = ?"))){
	echo "Error Preparing Statement: (" . $conn->errno . ") " . $conn->error;
}


$user_name = getAttribute("user_name");
$user_pwd = getAttribute("user_pwd");

if (!$stmt->bind_param("s", $user_name)) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}

if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
	
$out_pwd = null;

if (!$stmt->bind_result($rest_id, $out_pwd, $status, $community)) {
    echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}

while($stmt->fetch()){
	if(password_verify($user_pwd, $out_pwd)){
		$_SESSION['pass']=True;
		$_SESSION['rest_id']= $rest_id;
		$_SESSION['community']=$community;
		
		$x = new stdClass();
		$x->result = "success";
		exit(json_encode($x));
	
		//redirect to main
		
	} else {
		$gattempt = $gattempt +1;
		$_SESSION['attempt']=$gattempt;
		$x = new stdClass();
		$x->result = "failure";
		$x->error = "bad_password_username";
		exit(json_encode($x));
		
	}
}
//check that passwords match

//set session variables

$conn->close();
?>