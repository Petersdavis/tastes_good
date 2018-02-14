<?php
include '../boilerplate.php';
checkProduction();
include '../dbconnect.php'; 

$user_id = $_SESSION['user_id'];
$push_id = getattribute('push_id');

$user = new User();
$user->user_id = $user_id;

$result = $user->storePushId($push_id);

echo json_encode($result);
?>

