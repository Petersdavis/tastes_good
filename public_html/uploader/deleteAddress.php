<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php

$address_id = getattribute('address_id');
$sql = "DELETE FROM user_address WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $address_id);
$stmt->execute();
$stmt->close();


$conn->close();
exit();
