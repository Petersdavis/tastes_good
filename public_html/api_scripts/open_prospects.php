<?php
include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 


//Security Check
$secure= new Secure();

if(!$secure->user_id){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "BAD_USER";
	exit(json_encode($x));
}

if(!$secure->isSales()){
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "NOT_SALES_AUTH";
	exit(json_encode($x));
}

$user_id = $secure->user_id;
$community = getattribute('community');


$sql = "SELECT restaurants.rest_id, restaurants.title, restaurants.address, restaurants.phone, restaurants.email, restaurants.status, sales_junction.commission_term, sales_junction.sales_id FROM restaurants LEFT JOIN sales_junction ON restaurants.rest_id = sales_junction.rest_id WHERE (restaurants.status = 'NEW' OR restaurants.status = 'PROSPECT' OR restaurants.status = 'ACTIVE') AND community = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("s", $community);
$stmt->execute();


$stmt->bind_result($rest_id, $title, $address, $phone, $email, $status, $expires, $user_id);

$list = [];
while($stmt->fetch()){
 $x = new stdClass();
 $x->rest_id = $rest_id; 
 $x->title= $title;
 $x->address= $address;
 $x->phone= $phone;
 $x->email= $email;
 $x->status= $status;
 $x->expires = $expires;
 $x->user_id = $user_id;
 array_push($list, $x);
}

$stmt->close();

foreach($list as $x){
 if($x->status == "PROSPECT" && $x->expires < time()){
 
 
 $x->status = "NEW"; 
 
 $sql = "UPDATE restaurants SET status = 'NEW' WHERE rest_id =?";
 $stmt=$conn->prepare($sql);
 $stmt->bind_param("i", $x->rest_id);
 $stmt->execute();
 $stmt->close();
 
 $sql = "DELETE FROM sales_junction WHERE rest_id = ?";
 $stmt = $conn->prepare($sql);
 $stmt->bind_param("i", $x->rest_id);
 $stmt->execute();
 $stmt->close();
 
 
 }

}



$z = new stdClass();
$z->result = "success";
$z->data = $list;

exit(json_encode($z));


?>
