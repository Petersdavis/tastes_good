<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php
$categories = getattribute('categories');
$extras = getattribute('extras');
$rest_id = getattribute('rest_id');
$user_rest = $_SESSION['rest_id'];
if($user_rest!= -1 && $rest_id != $user_rest){
	exit('you do not have permission to edit this menu');
}


$a=new restaurant();
$a->menu = new menu();

$a->menu->fromJSON($categories, $extras, $rest_id);
$a->putSerial($rest_id);

