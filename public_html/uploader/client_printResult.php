<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 

if (!isset($_POST["json"])) {
  echo "No JSON " . print_r($_POST);
  exit();
}
$json = $_POST["json"];

$actions = json_decode(urldecode($json));
if (!isset($actions)) {
  echo 'Can\'t decode ' . $json;
  exit();
}

if (!valid_sender($actions)) {
  echo "Bad";
  exit();
} else {
  echo "OK";
}

if($actions->print !== 0){
	$sql = "INSERT INTO printer_errors (timestamp, origin, code, order_id) VALUES (?,?,?,?)";
	$stmt=$conn->prepare($sql);
	$timestamp = time();
	$origin = "client_printResult.php";
	$stmt->bind_param("isii", $timestamp, $origin, $actions->print, $actions->orderno);
	$stmt->execute();
	$stmt-close();
	
	
	
	//printer error
	//manually call to confirm order
	
	
}


if($actions->printed){
	//Some logic.
}

?>
