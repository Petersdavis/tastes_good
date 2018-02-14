<?php include 'boilerplate.php'; 
checkProduction();
include 'dbconnect.php';

html_Header("Welcome");
include 'header.php';
srcJavascript("/scripts/contact_us.js");
if(isset($_POST['ord_id'])){
$order_id = $_POST['ord_id'];

$sql = "select user_id from restaurant_orders where order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

$sql = "select fname, lname, email from users where user_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fname, $lname, $email);
$stmt->fetch();
$stmt->close();

$user_name = $fname . " ". $lname;

echo '<script>user = {"order_id":'.$order_id.',"user_id":'.$user_id.',"user_name":"'.$user_name.'","email":"'.$email.'"} </script>';
}elseif(isset($_SESSION['user_id'])){
echo '<script>user = {"order_id":0,"user_id":'.$_SESSION['user_id'].',"user_name":"'.$_SESSION['user_name'].'","email":"'.$_SESSION['user_email'].'"} </script>';
	
}



?>

<div class = "container" style="background-color:rgb(172, 232, 213); padding:0;width:90%;min-width:300px;max-width:1050px;">

	<div  style="padding-left:15px;padding-top:5px;padding-bottom:5px;">
		<h3> Contact: <span style = "color:#633E26">www.Tastes-Good.com</span> </h3>
	</div>
	<div>
		
		<div class = "row no-padding" style="background-color:rgba(255,255,255,.4);">
			
			<div class = "col-xs-12" style="margin-top:10px;">
				<label for="name">Name:</label>
				<input class = "form-control form-control-inline" id = "name" type="text" placeholder = "Your Name" />
			</div>
			<div class = "col-xs-12" style="margin-top:10px;">
				<label for="email">Email:</label>
				<input class = "form-control form-control-inline" id = "email" type="text" placeholder = "email@abc.com" />
			</div>
			<div class = "col-xs-12" style="margin-top:10px;">
				<label for="reason">Contact Reason:</label>
				<select class ="form-control" id="reason">
				  <option value="ORDER">Problem with an Order</option>
				  <option value="BILLING">Billing//Receipts</option>
				  <option value="TECH">Technical Issue</option>
				  <option value="FEEDBACK">Website Feedback</option>
				  <option value="FRAUD">Report Fraud</option>
				  <option value="PRIVACY">Our Privacy Policy</option>
				  <option value="OTHER">Other </option>
				</select>
			</div>
			<div class = "col-xs-12" style="margin-top:10px;">
				<label for="message">Message:</label>
				<textarea class ="form-control" rows="4" id="message"></textarea>
			</div>
			
			<div class = "col-xs-12 " style="margin-top:10px; margin-bottom:20px;">
				<input type="hidden" id="order_id" value="0" />
				<button type="button" id = "submit_comment" class="btn-default btn-block" style="height:34px;width:37%;float:right;">Submit</button>
			</div>
		</div>
		
	</div>
</div>

<?php include "footer.php"; ?>
