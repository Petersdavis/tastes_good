<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php

$cred = json_decode(getattribute("data"));


$a = new User ();
$result = $a->fromLogin ($cred);
$a->createToken();
$result->data = $a;
exit(json_encode($result));
?>

