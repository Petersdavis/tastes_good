<?php 
 
include '../boilerplate.php'; 
checkProduction();
include '../dbconnect.php';

$communities = [];

if(getattribute("inactive")){
$sql = "SELECT id, name, lat, lng, province FROM community";
}else{
$sql = "SELECT id, name, lat, lng, province FROM community WHERE status = 'ACTIVE'";
}

$stmt= $conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($id, $community, $lat, $lng, $province);
while($stmt->fetch()){
	$x = new stdClass();
        $x->id = $id;
	$x->community = $community;
	$x->lat = $lat;
	$x->lng = $lng;
	$x->province = $province;
	array_push($communities, $x);
}
$stmt->close();
$conn->close();

$x = new stdClass();
$x->result = "success";
$x->data = $communities;
echo json_encode($x);
?>