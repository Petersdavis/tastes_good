<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php

if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}

if($data = json_decode(getattribute('data'))){

$address = $data->address;
$comm = $data->community;

if (!preg_match("/Canada/i", $address->address)) {
   $address->address = $address->address . ", Canada";
}



$user_id = $_SESSION['user_id'];


$googleQuery =  $address->address;
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


	if(isset($comm->lat)&&isset($comm->lng)){
	
		$minD = 100;
		$curD = 0;
		foreach($result['results'] as $res){
		

			
			
			
			$curD = calculateDistance($comm->lat, $comm->lng, $res['geometry']['location']['lat'], $res['geometry']['location']['lng']);
			if ($curD<$minD){
				$minD = $curD;
				$best_result = $res;
				
			}
			
			
		}
	} else{
	$best_result = $result['results'][0];
	}
}else{

	$x = new stdClass();
	$x->result = "failure";
	$x->error = "GOOGLE_FAIL";
	exit(json_encode($x));
}


$address->lat = $best_result['geometry']['location']['lat'];
$address->lng = $best_result['geometry']['location']['lng'];
$address->formatted_address = $best_result['formatted_address'];


$user = new User();
$user->user_id= $user_id;



$sql = "INSERT INTO user_address (user_id, address, postcode, appt, buzz, lat, lng, type, comment) VALUES (?,?,?,?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
echo $conn->error;
$stmt->bind_param("issssssss", $user->user_id, $address->formatted_address, $address->postcode, $address->appt, $address->buzz, $address->lat, $address->lng, $address->type, $address->comments); 
if(!$stmt->execute()){
	$stmt->close();
	$result = new stdClass();
	$result->result = "fail";
	$result->error = "Connection: ". $conn->error . " Statement: ". $stmt->error; 
	echo json_encode($result);
	exit();
} 
$stmt->close();


$user->GetAddresses();
if(sizeof($user->addresses > 0)){
	$result = new stdClass();
	$result->result = "success";
	$result->error = "";
	$result->data = $user->addresses;
}else{
	$result = new stdClass();
	$result->result = "fail";
	$result->error = "no_addresses";
}


echo json_encode($result);


$conn->close();
exit();

}else{

$x = new stdClass();
$x->result = "failure";
$x->error = "BAD_DATA";
exit(json_encode($x));


}
?>
