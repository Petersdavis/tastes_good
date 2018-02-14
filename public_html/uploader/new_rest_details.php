<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php

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


$details = json_decode(getattribute('details'));
$rest_id = getattribute('rest_id');
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
$a = new Restaurant();
$a->grabRest($rest_id);


if($details->address !== $a->address){
	$googleQuery =  $restaurant->address;
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
		$address = $result['results'][0]['formatted_address'];
		
		$sql =  "UPDATE restaurants SET address = ?, lat = ?, lng=?  WHERE rest_id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sssi", $address, $lat, $lng, $rest_id);
		$stmt->execute();
		$stmt->close();
	}
}
$phone = $details->phone;
$pattern = '/[\D]/';
$phone = preg_replace ( $pattern , "" , $phone );
if(preg_match( '/(\d{10,11})/', $phone,  $matches)){
	$phone = $matches[0];
}
	

$offers_delivery=getattribute('offers_delivery');
$delivery_base=getattribute('delivery_base');

$sql = "UPDATE restaurants SET offers_delivery = ?, delivery_base = ?, title = ?, first_name=?, last_name = ?, type = ?, phone=?, email=? WHERE rest_id = ?";	
if(!$stmt = $conn->prepare($sql)){
	$z = new stdClass();
	$z->success = false;
	$z->error = $conn->error;
	$z->sql = $sql;
	echo json_encode($z);
	return;

}
if(!$stmt->bind_param("idssssssi", $offers_delivery, $delivery_base,  $details->title, $details->first_name, $details->last_name, $details->type, $phone, $details->email, $rest_id)){
	$z = new stdClass();
	$z->success = false;
	$z->error = $stmt->error;
	$z->sql = $sql;
	echo json_encode($z);
	return;
}
$stmt->execute();
$stmt->close();



$z = new stdClass();
$z->sql = $sql;
$z->success = true;
echo json_encode($z);






