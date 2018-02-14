<?php 
include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 

//  Get the correct list of restaurants
// From PostCode

	$community = new stdClass();
	$community->community = getattribute('community');
	
	
	$sql = "SELECT time_offset, province, lat, lng, timezone FROM community WHERE name = ?";
	$stmt=$conn->prepare($sql);
	$stmt->bind_param("s", $community->community);
	$stmt->execute();
	$stmt->bind_result($time_offset, $province, $lat, $lng, $timezone);
	$stmt->fetch();
	
	$community->timezone = $timezone;
	$community->lat = $lat;
	$community->province = $province;
	$community->name = $community->community;
	$stmt->close();
	
	$sql = 'SELECT rest_id, points, status FROM restaurants WHERE community = ? AND status != "NEW" ORDER BY points';
	$stmt = $conn->prepare($sql);
	$restIDs = [];
	$testingIDs = [];
	
	$stmt->bind_param('s', $community->community);
	$stmt->execute();
	$stmt->bind_result($rest_id, $points, $status);
	
	
	while($stmt->fetch()){	
		if($status == "ACTIVE"){
			array_push($restIDs, $rest_id);
		}elseif($status == "TESTING"){
			array_push($testingIDs , $rest_id);
		}
	}
	
	
	$stmt->close();
	
		
	$restaurants = [];
	$closed_restaurants = [];
	$testing_restaurants = [];
	
	date_default_timezone_set($timezone);
	
	$local_time = new DateTime();
	$local_time_stamp = $local_time->getTimestamp();
	$week_day = $local_time->format("l");
		
	foreach($restIDs as $id){
		$restaurant= new restaurant();
		$restaurant->grabRest($id);
                $restaurant->phone = str_replace("-", "", $restaurant->phone);
		$restaurant->grabCoupons($id);
		$restaurant->checkOpen($community->timezone);
		
		
		if($restaurant->open){
			array_push($restaurants, $restaurant);
			
		}else {
			array_push($closed_restaurants, $restaurant);
		}
	}
	
	foreach($testingIDs as $id){
		$restaurant= new restaurant();
		$restaurant->grabRest($id);
                $restaurant->phone = str_replace("-", "", $restaurant->phone);
		$restaurant->grabCoupons($id);
		$restaurant->checkOpen($community->timezone);
		array_push($testing_restaurants , $restaurant);
	}
	
$x = new stdClass();
$x->result = "success";
$x->data = new stdClass();
$x->data->restaurants = $restaurants;
$x->data->closed = $closed_restaurants;
$x->data->testing = $testing_restaurants;
			
	
 echo json_encode($x); 
 $conn->close();  ?>
 