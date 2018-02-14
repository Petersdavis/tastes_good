<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php

if (!isset($_POST["json"])) {
  echo "No JSON " . print_r($_POST);
  return;
}
$json = $_POST["json"];

$actions = json_decode(urldecode($json));
if (!isset($actions)) {
  echo 'Can\'t decode ' . $json;
  return;
}

if (!valid_sender($actions)) {
  echo "Bad";
  exit();
} else {
	if($actions->ping > 0){
		$sql = "UPDATE restaurant_client SET status = 0 WHERE rest_id= ?";
		$stmt= $conn->prepare($sql);
		$stmt->bind_param("i", $actions->rest_id);
		$stmt->execute();
		$stmt->close();
	
	}
	
	
  echo "OK";
}


?>
