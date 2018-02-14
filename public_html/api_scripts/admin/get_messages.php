<?php
include '../../boilerplate.php';
checkProduction();
include '../../dbconnect.php'; 



//Security Check
$secure = new Secure();
$secure->isAdmin();

$data = json_decode(getattribute("data"));

$sql = "SELECT id, rest_id, user_id, order_id, name, email, comment, reason, timestamp, closed, system_flag FROM comments";

//check if search is limited to user/restaurant/order:

if(isset($data->user_id)){
	$sql = $sql . " WHERE user_id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_params("i", $data->user_id);
}elseif(isset($data->rest_id)) {
	$sql = $sql . " WHERE rest_id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_params("i", $data->rest_id);

}elseif(isset($data->order_id)){
	$sql = $sql . " WHERE order_id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_params("i", $data->order_id);
}else{
	$stmt = $conn->prepare($sql);
}


$stmt->execute();
$stmt->bind_result($id, $rest_id, $user_id, $order_id, $name,  $email, $comment, $reason, $timestamp, $closed, $system_flag);
$msgs = [];
while($stmt->fetch()){
	$msg = new stdClass();
	$msg->id = $id;
	$msg->rest_id = $rest_id;
	$msg->user_id = $user_id;
	$msg->order_id = $order_id;
	$msg->name = $name;
	$msg->email = $email;
	$msg->comment = $comment;
	$msg->reason = $reason;
	$msg->timestamp = $timestamp;
	$msg->closed = $closed;
	$msg->system_flag = $system_flag;
	array_push($msgs, $msg);
}


$x = new stdClass();
$x->result= "Success";
$x->data = $msgs;
exit(json_encode($x));



