<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php
$impression = getattribute("thumbs");
$order_id = getattribute("order_id");
$email = getattribute("email");

$sql = "SELECT rest_id, user_id FROM restaurant_orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($rest_id, $user_id);
$stmt->fetch();
$stmt->close();

//confirm that rest_id and user_id are correct.
		
		
$sql="SELECT order_id, impression FROM restaurant_reviews WHERE order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($order_id, $old_impression);

if($stmt->num_rows == 0){
	$stmt->close();
	
	$sql = "INSERT INTO restaurant_reviews (order_id, user_id, rest_id, impression, timestamp) VALUES (?,?,?,?,?)"; 
	$stmt=$conn->prepare($sql);
	$time = time();
	$stmt->bind_param("iiiii", $order_id, $user_id, $rest_id, $impression, $time);
	
	$stmt->execute();
	$stmt->close();
	
	if($impression >0 ){
		$sql ="UPDATE restaurants SET pos_review += 1 WHERE rest_id = ?";
	}else{
		$sql ="UPDATE restaurants SET neg_review += 1 WHERE rest_id = ?";
	}
	$stmt->bind_param("i", $rest_id);
	$stmt->execute();
	$stmt->close();
	
	
	
} else {
	if($old_impression !== $impression){
	$sql = "UPDATE restaurant_reviews SET impression = ? WHERE order_id = ?"; 
	$stmt=$conn->prepare($sql);
	$stmt->bind_param("ii", $impression, order_id);
	$stmt->execute();
	$stmt->close();
	
	if($impression >0 ){
		$sql ="UPDATE restaurants SET pos_review += 1, neg_review -= 1 WHERE rest_id = ?";
	
	}else{
		$sql ="UPDATE restaurants SET pos_review -= 1, neg_review += 1 WHERE rest_id = ?";
	}
	
	$stmt=$conn->prepare($sql);
	$stmt->bind_param("i", $rest_id);
	$stmt->execute();
	$stmt->close();
	
	}
}


header('Location: https://www.tastes-good.com/main.html?review');