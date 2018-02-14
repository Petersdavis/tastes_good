<?php include '../boilerplate.php'; ?>
<?php include '../dbconnect.php'; ?>
<?php
$schedule=json_decode(getattribute('schedule'));
$rest_id =getattribute('rest_id');
$a = new Schedule ();
$a->monday_open = $schedule->monday_open;
$a->monday_close = $schedule->monday_close;
$a->tuesday_open = $schedule->tuesday_open;
$a->tuesday_close = $schedule->tuesday_close;
$a->wednesday_open = $schedule->wednesday_open;
$a->wednesday_close = $schedule->wednesday_close;
$a->thursday_open = $schedule->thursday_open;
$a->thursday_close = $schedule->thursday_close;
$a->friday_open = $schedule->friday_open;
$a->friday_close = $schedule->friday_close;
$a->saturday_open = $schedule->saturday_open;
$a->saturday_close = $schedule->saturday_close;
$a->sunday_open = $schedule->sunday_open;
$a->sunday_close = $schedule->sunday_close;
$serial = serialize($a);

$sql = "UPDATE restaurants SET schedule = ? WHERE rest_id =?";
if(!$stmt = $conn->prepare($sql)){
	exit($conn->error);
}
$stmt->bind_param("si",$serial, $rest_id); 	
$stmt->execute();
$stmt->close();
