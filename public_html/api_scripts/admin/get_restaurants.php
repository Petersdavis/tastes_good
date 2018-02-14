<?php
include '../../boilerplate.php';
checkProduction();
include '../../dbconnect.php'; 


//Security Check
$secure = new Secure();
$secure->isAdmin();


$data = json_decode(getattribute("data"));

$sql = 'SELECT rest_id, closed, title, address, image, lat, lng, community, phone, email, points, status, schedule, type, sm_image, offers_delivery, delivery_base, delivery_rate, delivery_email, credit, balance, pos_review, neg_review, owner_id From restaurants';


if(isset($data->user_id)){
	$sql = $sql . " WHERE owner_id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $data->user_id);
	
}elseif(isset($data->rest_id)){
	$sql = $sql . " WHERE rest_id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $data->rest_id);
}elseif(isset($data->status)){
	$sql = $sql . " WHERE status = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $data->status);

}elseif(isset($data->order_id)){
	$sql =  "SELECT t1.rest_id, t1.closed, t1.title, t1.address, t1.image, t1.lat, t1.lng, t1.community, t1.phone, t1.email, t1.points, t1.status, t1.schedule, t1.type,  t1.sm_image, t1.offers_delivery, t1.delivery_base, t1.delivery_rate, t1.delivery_email, t1.credit, t1.balance, t1.pos_review, t1.neg_review, t1.owner_id FROM (SELECT rest_id, closed, title, address, image, lat, lng, community, phone, email, points, status, schedule, type, sm_image, offers_delivery, delivery_base, delivery_rate, delivery_email, credit, balance, pos_review, neg_review, owner_id From restaurants) t1 inner join (SELECT rest_id, order_id FROM restaurant_orders) t2 ON t1.rest_id = t2.rest_id WHERE t2.order_id=?";
	
	$stmt = $conn->prepare($sql);
	$stmt->bind_params("i", $data->order_id);
}elseif(isset($data->community)){
	$sql = $sql . " WHERE community = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_params("s", $data->community);

}else{
	$sql = $sql . " ORDER BY rest_id ASC LIMIT 100";
	$stmt = $conn->prepare($sql);
}

$stmt->execute();
$stmt->bind_result($rest_id, $closed, $title, $address, $image, $lat, $lng, $community, $phone, $email, $points, $status, $schedule, $type, $sm_image, $offers_delivery, $delivery_base, $delivery_rate, $delivery_email, $credit, $balance, $pos_review, $neg_review, $owner_id);

$restaurants = [];

while ($stmt->fetch()) {
	$restaurant = new Restaurant();
	$restaurant->rest_id = $rest_id;
	$restaurant->closed = $closed;
	$restaurant-> title = $title;
	$restaurant-> address = $address;
	$restaurant-> image = $image;
	$restaurant->lat = $lat;
	$restaurant->lng = $lng;
	$restaurant->community = $community;
	$restaurant->balance = $balance;
	
	$phone = PrettyPhone($phone);
					
	$restaurant->phone = $phone;
	$restaurant->email = $email;
	$restaurant->points = $points;
	$restaurant->status = $status;
	if(is_null($schedule)){
		$restaurant->schedule = new Schedule();
	}else {$restaurant->schedule = unserialize($schedule);}
	$restaurant->type = $type;
	$restaurant->sm_image = $sm_image;
	$restaurant->offers_delivery = $offers_delivery;
	$restaurant->delivery_base = $delivery_base;
	$restaurant->delivery_rate = $delivery_rate;
	$restaurant->delivery_email = $delivery_email;
	$restaurant->credit = $credit;
	$restaurant->pos_review = $pos_review;
	$restaurant->neg_review = $neg_review;
	$restaurant->owner_id = $owner_id;
	
	array_push($restaurants, $restaurant);
	
}

//return the results:
$x = new stdClass();
$x->result = "success";
$x->data= $restaurants;
exit(json_encode($x));

