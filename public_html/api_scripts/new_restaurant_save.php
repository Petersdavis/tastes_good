<?php
require_once '../boilerplate.php';
checkProduction();
require_once '../dbconnect.php'; 

$user = json_decode(getattribute("User"));
if(!($auth = getattribute("auth"))){
	
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "no_auth";
	exit(json_encode($x));

}


$a = new User();

if($user->type == "new_user"){
	
	$a->fname = $user->fname;
	$a->lname = $user->lname;
	$a->email = $user->email;
	
	
	$pattern = '/[\D]/';
	$user->phone = preg_replace ( $pattern , "" , $user->phone );
	if(preg_match( '/(\d{10,11})/', $user->phone,  $matches)){
		$a->phone = $matches[0];
	}
	
	$a->password = password_hash($user->password, PASSWORD_BCRYPT);
	
	
	$a->is_sales = 0;
	$a->is_rest = 1;
	
	
	$sql = "SELECT user_id FROM users WHERE email = ?";
		
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $a->email);
	$stmt->execute();
	$stmt->store_result();
	
	if($stmt->num_rows==0){
		
	
		$stmt->free_result();
		$stmt->close();	
				
		$result = $a->putNewToDB();
		if($result->result=="success"){
			$_SESSION['user_id']= $a->user_id;
			$_SESSION['user_email']=$a->email; 
		}
		
		
	
	}else{
		$x = new stdClass();
		$x->result = "failure";
		$x->error = "EMAIL_EXISTS";
		exit(json_encode($x));
		
		
	}
	



}elseif($user->type == "old_user"){
	$cred = new stdClass();
	$cred->password = $user->password;
	$cred->email = $user->email;
	$result = $a->fromLogin($cred);
	
	if($result->result == "failure"){
		
		$x = new stdClass();
		$x->result = "failure";
		$x->error = "BAD_PWD";
		exit(json_encode($x));
	
	}else{
	$sql = "UPDATE users SET is_rest = 1 WHERE email = ?";
	if(!$stmt=$conn->prepare($sql)){
		echo $conn->error;
	}
	if(!$stmt->bind_param("s", $a->email)){
		echo $stmt->error;
	}
	if(!$stmt->execute()){
		echo $stmt->error;
	}
	$stmt->close();	
	
	}



}

//create user






function compress($source, $destination, $quality) {

    $info = getimagesize($source);

    if ($info['mime'] == 'image/jpeg') 
        $image = imagecreatefromjpeg($source);

    elseif ($info['mime'] == 'image/gif') 
        $image = imagecreatefromgif($source);

    elseif ($info['mime'] == 'image/png') 
        $image = imagecreatefrompng($source);

    if(imagejpeg($image, $destination, $quality)){
    	return true;
    }else{
    	return false;
    }
    
    
}


function LoadFiles($file_name, $folder){
	global $rest_id;
	
	if(isset($_FILES[$file_name]['name'])){
			if(!$_FILES[$file_name]['error']){
			
			$validextensions = array("jpeg", "jpg", "png", "gif");
			$temporary = explode(".", $_FILES[$file_name]["name"]);
			$file_extension = end($temporary);
			$fname = $rest_id . "_logo";
			$sm_fname = $rest_id. "_sm_logo";
			$fname= $fname . ".jpeg";
			$sm_fname= $sm_fname . ".jpeg";
			
			
			if ((($_FILES[$file_name]["type"] == "image/png") || ($_FILES[$file_name]["type"] == "image/jpg") || ($_FILES[$file_name]["type"] == "image/jpeg")||($_FILES[$file_name]["type"] =="image/gif")) && ($_FILES[$file_name]["size"] < 100000) && in_array($file_extension, $validextensions)) {
				if (file_exists("../upload/logo/" . $fname)) {
					unlink("../upload/logo/". $fname);
				}
				if (file_exists("../upload/logo/" . $sm_fname)) {
					unlink("../upload/logo/". $sm_fname);
				}
				
				$sourcePath = $_FILES[$file_name]['tmp_name']; // Storing source path of the file in a variable
				$targetPath = "../upload/".$folder."/". $fname; // Target path where file is to be stored
				$sm_targetPath = "../upload/".$folder."/". $sm_fname;
				
				if(compress($sourcePath,$targetPath, 300)){ // Moving Uploaded file
						
						if(compress($sourcePath, $sm_targetPath, 100)){
							$path = new stdClass();
							$path->large =  "https://www.tastes-good.com/upload/logo/". $fname;
							$path->small =  "https://www.tastes-good.com/upload/logo/". $sm_fname;
							$x = new stdClass(); $x->result = "success"; $x->data = $path; 
							return $x;
						}
					
						
																
				} else {$x = new stdClass(); $x->result = "fail"; $x->error = "move_uploaded_file Error"; return $x;}
				
				}
			} else {$x = new stdClass(); $x->result = "fail"; $x->error = $_FILES[$file_name]["error"]; return $x;} 
		} else {$x = new stdClass(); $x->result = "fail"; $x->error = "NO FILE"; return $x;}
}



$result = LoadFiles ("rest_logo", "logo");	
		
if($result->result == "success"){
	$image = $result->data->large;
	$sm_image = $result->data->small;
	$sql = "UPDATE restaurants SET image = ?, sm_image = ? WHERE rest_id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ssi", $image,  $sm_image, $rest_id);
	$stmt->execute();
	$stmt->close();
}


$rest=  $user->restaurants[0];
$rest ->last_five = [];
$rest->owner_id = $a->user_id;

$b= new Restaurant();
$b->grabRest($rest->rest_id);
$b->verifyToken($auth);





if($b->address !== $rest->address){
	$googleQuery =  $rest->address;
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
		$rest->lat = $result['results'][0]['geometry']['location']['lat'];
		$rest->lng = $result['results'][0]['geometry']['location']['lng'];
		$rest->address = $result['results'][0]['formatted_address'];
		
		$sql =  "UPDATE restaurants SET address = ?, lat = ?, lng=?  WHERE rest_id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sssi", $rest->address, $rest->lat, $rest->lng, $rest->rest_id);
		$stmt->execute();
		$stmt->close();
	}
}else{
	$rest->lat =$b->lat;
	$rest->lng =$b->lng;
	$rest->address =$b->address;
}


if(!isset($rest->community)){

	//Get All Communities
	$sql = "SELECT name, lat, lng FROM community";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	
	$stmt->bind_result($community, $lat, $lng);
	$communities = [];
		
	
	while($stmt->fetch()){
		$comm= new community();
		$comm->name = $community;
		$comm->lat = $lat;
		$comm->lng = $lng;
		array_push($communities, $comm);
		
	}
	$stmt->close();
	
	//Calculate Nearest Community
	
	$minD = 1000;
	$curD = 0;
	$rest_comm = "";
	foreach ($communities as $comm){
		$curD = calculateDistance($comm->lat, $comm->lng, $rest->lat, $rest->lng);
		if ($curD<$minD){
			$minD = $curD;
			$rest_comm = $comm->name;
		}
	
	}
	$rest->community = $rest_comm;
	
}
	
$pattern = '/[\D]/';
$rest->phone = preg_replace ( $pattern , "" , $rest->phone );
if(preg_match( '/(\d{10,11})/', $rest->phone,  $matches)){
	$b->phone = $matches[0];
}


$c = new Schedule ();
$c->monday_open = $rest->schedule->monday_open;
$c->monday_close = $rest->schedule->monday_close;
$c->tuesday_open = $rest->schedule->tuesday_open;
$c->tuesday_close = $rest->schedule->tuesday_close;
$c->wednesday_open = $rest->schedule->wednesday_open;
$c->wednesday_close = $rest->schedule->wednesday_close;
$c->thursday_open = $rest->schedule->thursday_open;
$c->thursday_close = $rest->schedule->thursday_close;
$c->friday_open = $rest->schedule->friday_open;
$c->friday_close = $rest->schedule->friday_close;
$c->saturday_open = $rest->schedule->saturday_open;
$c->saturday_close = $rest->schedule->saturday_close;
$c->sunday_open = $rest->schedule->sunday_open;
$c->sunday_close = $rest->schedule->sunday_close;
$serial = serialize($c);
	
		
$sql = "UPDATE restaurants SET community = ?, owner_id = ?, schedule = ?, offers_delivery = ?, delivery_base = ?, title = ?, type = ?, phone=?, email=?, status = 'TESTING' WHERE rest_id = ?";	
if(!$stmt=$conn->prepare($sql)){
	echo $conn->error;
}
if(!$stmt->bind_param("sisidssisi", $rest->community, $a->user_id, $serial, $rest->offers_delivery, $rest->delivery_base, $rest->title, $rest->type, $b->phone, $rest->email,  $rest->rest_id)){
	echo $stmt->error;
}
if(!$stmt->execute()){
	echo $stmt->error;
}
$stmt->close();



$a->fromSession();

$_SESSION['user_id']= $a->user_id;
$_SESSION['user_name']=$a->fname . " " . $a->lname;
$_SESSION['user_email']=$a->email; 
$_SESSION['user_phone']=$a->phone; 


$x = new stdClass();
$x->result = "success";
$x->data = $a;
exit(json_encode($x));







?>