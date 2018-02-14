<?php
include '../../boilerplate.php';
checkProduction();
include '../../dbconnect.php'; 


//Security Check
$secure = new Secure();
$secure->isAdmin();


$data = json_decode(getattribute("data"));

$sql =  "SELECT user_id, email, fname, lname, phone, credit, is_sales, is_admin, is_rest FROM users";

if(isset($data->user_id)){
	$sql = $sql . " WHERE user_id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $data->user_id);
	
}elseif(isset($data->email)){
	$sql = $sql . " WHERE email = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $data->email);
}elseif(isset($data->rest_id)){
	$sql =  "SELECT  t1.email, t1.fname, t1.lname, t1.phone, t1.credit, t1.is_sales, t1.is_admin, t1.is_rest FROM (SELECT email, fname, lname, phone, credit, is_sales, is_admin, is_rest FROM users) t1 inner join (SELECT rest_id, owner_id FROM restaurants) t2 ON t1.user_id = t2.owner_id WHERE t2.rest_id=?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $data->rest_id);
}elseif(isset($data->payment_pending)){
	$sql = $sql . " WHERE credit > 50";

}else{
	//or just return the newest users
	$sql = $sql . " ORDER BY user_id DESC LIMIT 100";
	$stmt = $conn->prepare($sql);
}

$stmt->execute();
$stmt->bind_result($user_id, $email, $fname, $lname, $phone, $credit, $is_sales, $is_admin, $is_rest);

$users = [];

while ($stmt->fetch()) {
	$user = new User();
	$user->user_id = $user_id;
	$user->email = $email;
	$user->fname = $fname;
	$user->lname = $lname;
	
	$phone = PrettyPhone($phone);
	$user->phone = $phone;
	$user->credit = $credit;
	$user->is_sales = $is_sales;
	$user->is_admin = $is_admin;
	$user->is_rest = $is_rest;			
	
	array_push($users, $user);
	
}

//return the results:
$x = new stdClass();
$x->result = "success";
$x->data= $users;
exit(json_encode($x));
