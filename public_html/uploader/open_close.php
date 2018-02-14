<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php


if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_session";
	exit(json_encode($x));
}

$a = new User();
$a->fromSession();


$data=json_decode(getattribute('data'));
$rest_id = $data->rest_id;

	
foreach($a->restaurants as $b){
	if($b->rest_id == $data->rest_id){
	
		$sql = "UPDATE restaurants SET closed = ? WHERE rest_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("ii", $data->close, $data->rest_id);
		$stmt->execute();
		$stmt->close();
		
		$x= new stdClass();
		$x->result="success";
		exit(json_encode($x));	
		
	
	}
	
}

$x= new stdClass();
$x->result="failure";
$x->error="bad_user_rest";
exit(json_encode($x));	

