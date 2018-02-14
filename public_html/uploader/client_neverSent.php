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
} else {
  echo "OK";
}
?>
