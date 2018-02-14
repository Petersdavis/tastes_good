<?php 
include '../boilerplate.php';

if(!isset($_SESSION['pass'])||!$_SESSION['pass']){
	//go to rest--login screen
	header('Location: ../');
}



error_reporting(E_ALL);
ini_set('display_errors', 'On');

checkProduction();
include '../dbconnect.php'; 

require_once("../braintree_init.php");
BTconfig($BTproduct);

html_Header("Restaurant Dashboard");
$file = __FILE__;
$community = $_SESSION['community'];
$rest_id = $_SESSION['rest_id'];


$rest= new Restaurant();
$rest->grabRest($rest_id);

include 'header.php';
srcJavascript("../scripts/dashboard.js");



/*
srcJavascript("js/chart.min.js");
srcJavascript("js/chart-data.js");
*/
//get the page hits. 

	



$comm=new Community();
$comm->grabComm($community);




$sql = "SELECT count(*) FROM page_hits WHERE rest_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->bind_result($hitcount);
$stmt->fetch();
$stmt->close();


$sql = "SELECT COUNT(*), SUM(order_total) FROM restaurant_orders WHERE rest_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->bind_result($count_orders, $sales_total);
$stmt->fetch();
$stmt->close();


$users = [];
$sql= "SELECT user_id, count(*), SUM(order_total)  FROM restaurant_orders WHERE rest_id =? GROUP BY user_id ORDER BY SUM(order_total)";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->store_result();
$user_count = $stmt->num_rows;
$stmt->bind_result($user_id, $order_count, $order_total);
while($stmt->fetch()){
	$a= new User ();
	$a->user_id = $user_id;
	$a->order_count = $order_count;
	$a->order_total = $order_total;
	array_push($users, $a);
}	
$stmt->close();

foreach($users as $user){
	$user->fromId($user->user_id);
	echo $conn->error;
}

$last_ten = [];
$sql="SELECT request_date, request_time, link, coupon, order_id, user_id, order_time, payment_type, order_total, order_delivery, delivery_charge, tip, addr_id, discount, payment_fee, commission, tg_points, confirmed FROM restaurant_orders WHERE rest_id = ?  ORDER BY order_time DESC LIMIT 10";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->bind_result($request_date, $request_time, $link, $coupon, $order_id, $user_id, $order_time, $payment_type, $order_total, $order_delivery, $delivery_charge, $tip, $addr_id, $discount, $payment_fee, $commission, $tg_points, $confirmed);
while($stmt->fetch()){
	$a=new Order();
	$a->order_id=$order_id;
	$a->user_id = $user_id;

	$a->confirmed = $confirmed;
	$a->addr_id= $addr_id;
	
	$a->request_date = 	$request_date;
	$a->request_time = 	$request_time;
	$a->deliveryOption=$order_delivery;
	$a->deliveryCharge = $delivery_charge;
	$a->tip = $tip;
	$a->total = $order_total;
	$a->timestamp=$order_time;
	$a->paymentType= $payment_type;
	$a->coupon = $coupon;
	$a->discount =$discount;
	$a->tg_points= $tg_points;
	$a->link=$link;

	array_unshift($last_ten, $a);
}
$stmt->close();

foreach($last_ten as $a){
	if($a->user_id){
		$b = new User();
		$b->fromId($a->user_id);
		echo $conn->error;
		$a->user = $b;
	}
	if($a->addr_id){	
		$b = new Address();
		$b->fromId($a->addr_id);
		echo $conn->error;
		$a->address = $b;
	}
}

/*
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
    	$date = date('dmY', $row['date']);
    	$hit = new stdClass;
        $hit->user_id = $row['user_id'];
        $hit->hit_date = $date;
        array_push($pageHits, $hit);
        }
} 

echo "<script> pagehits = ".json_encode($pageHits)." </script>";
*/
$new_rest = getattribute('new_rest');
if($new_rest){
	echo "<script>new_rest=1</script>";
}else{
	echo "<script>new_rest=0</script>";
}



if(isset($_GET['confirm_order'])){
  $confirm_order = $_GET['confirm_order'];
}else{
$confirm_order = -1;
}


?> 

<script src="https://js.braintreegateway.com/js/braintree-2.30.0.min.js"></script>

<script>btToken = '<?php echo Braintree\ClientToken::generate(); ?>' 
	rest_id = <?php echo $rest->rest_id; ?> ;
         orders = <?php echo json_encode($last_ten); ?>;
         confirm_order = <?php echo $confirm_order; ?>;

</script>
<!--Icons-->
<script src="js/lumino.glyphs.js"></script>

<!--[if lt IE 9]>
<script src="js/html5shiv.js"></script>
<script src="js/respond.min.js"></script>
<![endif]-->



	<div id="sidebar-collapse" class="col-sm-3 col-lg-2 sidebar">
		
		<?php include 'sidebar_content.php' ?>

	</div><!--/.sidebar-->
		
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">			
			
		<div class="row" style = "background-color:rgba(210,245,225,1);margin-left:5px;margin-right:5px;" >
			<div class="col-xs-12 ">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Dashboard: <span style="color: #633E26;font-size:25px;"><?php echo $rest->title; ?></span>  <button onclick = "$('#tutorial').modal('show');" class = "btn btn-primary" style="float:right;font-size:25px" >?</button></h3>
				<h2 style = "margin-top:0; padding-top:10px; width:100%;text-align:left;font-size:20px;"> 
				 	<div class = "row" style="background-color:#f0ffff">
						<div class = "col-xs-12 col-sm-6">
							
							<div title = "In TESTING mode your restaurant will 
only be visible to you and other restaurant partners. 
You must be logged in to see your restaurant."  style = "text-align:left;margin-left:15%;">
							<h3 style="font-size:25px">Status: </h3><br>
							<strong>Community: </strong> <?php echo $community; ?> <br> 
							<strong>Community Status: </strong> <?php echo $comm->status; ?> <br> 
							<strong >Restaurant Status: </strong> <?php echo $rest->status; ?> <br> 
							</div>
							
							<h3 style="text-align:left;font-size:25px;margin-left:15%;">Current Balance: </h3><br>
							<div style="margin-bottom:10px;">
							<h2 style="text-align:left;line-height:25px;display:inline-block;font-size:25px;margin:5px;margin-left:15%;"><?php echo "$". round($rest->balance, 2); ?></h2>
							<button title= "When your account balance is greater than $100 you will have the option to payout your account." id="account_cashout" class  = "btn btn-primary <?php if($rest->balance<100){echo 'disabled';} ?>" <?php if($rest->balance<100){echo 'disabled="true"';} ?> style="display:inline-block;margin-left:10px;margin-top:-9px;"> Cashout Account <span class="glyphicon glyphicon-piggy-bank" style="padding:10px;" aria-hidden="true"></span></button>
							</div>
							<?php if($rest->credit > 0){  ?>
							<div>
							<h3 style="text-align:left;font-size:25px;margin-left:15%;">Current Credit: </h3><br>
							<h2 style = "text-align:left;line-height:25px;display:inline-block;font-size:25px;margin:5px;margin-left:15%;">
								<?php echo "$". round($rest->credit,2); ?> Credit
							</h2>
							</div>
							<?php } ?>
							
							
						</div>
						<div class = "col-xs-12 col-sm-6">
							<div style="margin-left:15%;">
							<h3 style="font-size:25px;">Open/Close Switch: </h3><br>
							<h2 style = "background-color:#f0ffff; margin-top:0; padding-top:0px; width:100%;text-align:left;font-size:20px;"> 
							<label class="switch">
							  <input id = "close_switch" type="checkbox" <?php if($rest->closed){echo "checked";} ?> >
							  <div class="slider"></div>
							</label> <br>
							<strong><span id="switch_status">CLOSED</span></strong>
							</h2>
							</div>
							<?php if($rest->status =="TESTING"){  ?>
							<div style="margin-left:15%;">
							<h3 style="font-size:25px;margin-top:5px;">Activate Account: </h3><br>
							<button onclick = "$('#activate_modal').modal('show');" class="btn btn-primary" style="padding:10px;">Request Account Activation</button>
							
							</div>
							
							<?php } ?>
							
						</div>
						<div  class= "col-xs-6">
							
								
							
						</div>
						
				 	</div>
				
				</h2>
			</div>
		</div><!--/.row-->
		
		
		<div class="row" style = "background-color:rgba(210,245,225,1);margin-top:10px; margin-left:5px;margin-right:5px;padding-bottom:10px;">
			<div class="col-xs-12 " style = "margin-bottom:10px;">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Statistics:</h3>
			</div>
			<div class="col-xs-12 col-md-3" style="background-color:#f0ffff">
				<div class="panel-blue panel-widget ">
					<div class="row no-padding">
						<div class="col-sm-3 col-lg-5 widget-left">
							<svg class="glyph stroked bag"><use xlink:href="#stroked-bag"></use></svg>
						</div>
						<div class="col-sm-9 col-lg-7 widget-right">
							<h2><strong> <?php if($order_count >0){echo $order_count; } else {echo "0"; } ?></strong>
							<div class="text-muted">Orders</div></h2>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-md-3" style="background-color:#f0ffff">
				<div class="panel-orange panel-widget">
					<div class="row no-padding">
						<div class="col-sm-3 col-lg-5 widget-left">
							<svg class="glyph stroked line-graph"><use xlink:href="#stroked-line-graph"></use></svg>
						</div>
						<div class="col-sm-9 col-lg-7 widget-right">
							<h2><strong>$ <?php if($sales_total >0){echo $sales_total; }else{ echo "0.00";}  ?></strong>
							<div class="text-muted">Sales Total</div></h2>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-md-3" style="background-color:#f0ffff">
				<div class="panel-teal panel-widget">
					<div class="row no-padding">
						<div class="col-sm-3 col-lg-5 widget-left">
							<svg class="glyph"><use xlink:href="#stroked-male-user"></use></svg>
						</div>
						<div class="col-sm-9 col-lg-7 widget-right">
							<h2><strong><?php if($user_count >0){echo $user_count; }else{ echo "0";}  ?></strong>
							<div class="text-muted">Customers</div></h2>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-md-3" style="background-color:#f0ffff">
				<div class="panel-red panel-widget">
					<div class="row no-padding">
						<div class="col-sm-3 col-lg-5 widget-left">
							<svg class="glyph"><use xlink:href="#stroked-app-window-with-content"></use></svg>
						</div>
						<div class="col-sm-9 col-lg-7 widget-right">
							<h2><strong><?php  if($hitcount >0){echo $hitcount; }else{ echo "0";} ?></strong>
							<div class="text-muted">Page Views</div></h2>
						</div>
					</div>
				</div>
			</div>
		</div><!--/.row-->
	
	
		<div class="row" style = "background-color:rgb(210,245,225);margin-top:10px; margin-left:5px;margin-right:5px;padding-bottom:10px;">
			<div class="col-xs-12 " style = "margin-bottom:10px;">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Recent Orders:</h3>
				<button class = "btn-block">
				<div class = "row">
					<div class = "col-xs-1 no-padding">
						ID
					</div>
					<div class = "col-xs-2 no-padding">
						User Name
					</div>
					<div class = "col-xs-2 no-padding">
						Payment Method
					
					</div>
					<div class = "col-xs-2 no-padding">
						Delivery
					</div>
					<div class = "col-xs-3 no-padding">
						Date
					</div>
					<div class = "col-xs-2 no-padding">
						Time
					</div>
				</div>
				</button>
			<?php foreach($last_ten as $x){
			
				/*
				
				*/
				
				?>
				<button class = "btn <?php if($x->confirmed==1){echo "btn-default";}else if($x->confirmed==-1){echo "btn-danger";}else{echo "btn-warning";} ?> btn-block" id="order_btn_<?php echo $x->order_id; ?>" data-toggle="collapse" data-target="#order_ext_<?php echo $x->order_id; ?>">
				<div class = "row">
					<div class = "col-xs-1 no-padding">
						<?php echo $x->order_id ."."; ?>
					</div>
					<div class = "col-xs-2 no-padding">
						<?php echo $x->user->fname ." ". $x->user->lname; ?>
					</div>
					<div class = "col-xs-2 no-padding">
						<?php if($x->paymentType=="online"){echo "PAID"; }else{ echo "CASH";} ?>
					
					</div>
					<div class = "col-xs-2 no-padding">
						<?php if($x->deliveryOption){echo "DELIVERY"; }else{ echo "PICKUP";} ?>
					</div>
					<div class = "col-xs-3 no-padding">
						<?php echo $x->request_date; ?>
					</div>
					<div class = "col-xs-2 no-padding">
						<?php echo $x->request_time; ?>
					</div>
				</div>
				</button>
				<div id = "order_ext_<?php echo $x->order_id; ?>" class="collapse" name = "order_expand" style = "background-color:#f0ffff; margin-top:0;padding-top:15px; padding-left:20px; padding-right:20px; margin-bottom:10px; border-width:0 2px 0 2px; border-color:#633E26;;border-style:solid;">
					<h2 style="line-height:20px;font-size:15px">
					<div class = "row">
						<div class = "col-xs-12 no-padding" >
							<strong> Customer Phone: </strong>  <?php echo $x->user->phone; ?>
						</div>
						
						<?php if($x->deliveryOption){ ?>
							<div class = "col-xs-6 no-padding">
							 	<strong>Delivery Charge:</strong>$ <?php echo $x->deliveryCharge; ?>
							</div>
							<div class = "col-xs-6 no-padding">
							 	<strong>Delivery Tip:</strong> $ <?php echo $x->tip; ?>
							</div>
							<div class = "col-xs-12 no-padding">
							 	<strong>Delivery Address:</strong>  <?php echo $x->address->address; ?>
							</div>	
						<?php } ?>	
							
					</div>
					</h2>
<div class = "row" <?php if($x->confirmed!=0){echo 'style = "display:none;"';} ?> id="<?php echo 'confirm_'.$x->order_id; ?>" >

<div class = "col-xs-12"> 
<a href="<?php echo "../uploader/push_pdf.php?order_id=". $x->order_id; ?>" class = "btn btn-default btn-block">Order PDF</a>
</div>
<div class = "col-xs-6">
<button class = "btn btn-success btn-block" name = "confirm_order" data-order_id = "<?php echo $x->order_id; ?>" >Confirm Order </button>
</div>
<div class = "col-xs-6">
<button class = "btn btn-danger btn-block" name = "reject_order" data-order_id = "<?php echo $x->order_id; ?>" >Reject Order </button>
</div>
</div>

<div class = "row" <?php if($x->confirmed!=-1){echo 'style = "display:none;"';} ?> id="<?php echo 'confirm_'.$x->order_id; ?>" >
<div class = "col-xs-12">
<button class = "btn btn-danger btn-block" >Order is Rejected!</button>
</div>
</div>


					<div class = "row" <?php if($x->confirmed!=1){echo 'style = "display:none;"';} ?>  id = "<?php echo 'modify_'.$x->order_id; ?>" >
						<div class = "col-xs-3 no-padding">
							<button class = "btn btn-default btn-block" name="store_credit" data-order_id = "<?php echo $x->order_id; ?>" >Store Credit</button>
						</div>
						<div class = "col-xs-3 no-padding">
							<button class = "btn btn-default btn-block" name="cancel_order" data-order_id = "<?php echo $x->order_id; ?>" >Cancel Order</button>
						</div>
						<div class = "col-xs-3 no-padding">
							<button class = "btn btn-default btn-block" name="refund_order" data-order_id = "<?php echo $x->order_id; ?>" title="Only PAID orders can be refunded" <?php if($x->paymentType=="offline"){echo "disabled"; } ?> >Partial Refund</button>
						</div>
						<div class = "col-xs-3 no-padding">
							<a href="<?php echo "../uploader/push_pdf.php?order_id=". $x->order_id; ?>" class = "btn btn-default btn-block">Order PDF</a>
						</div>	
					</div>
				
				
				</div>
				
			<?php }
			?>
			
			</div>
		</div><!--/.row-->
	</div><!--/.main frame -->
	
<div class="modal fade" id="credit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title">Send Store Credit: </h3>
      	<button type="button" class="close" onclick="$('#credit').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      		<div class = "row" style="font-size:18px;">
				<div class="col-xs-12 col-sm-6"><strong>Order ID:</strong> <span id = "credit_id"> </span> </div>
				<div class="col-xs-12 col-sm-6"><strong>Name:</strong> <span id = "credit_name"> </span> </div>
				<div class="col-xs-12 col-sm-6"><strong>Phone:</strong> <span id = "credit_phone"> </span> </div>
				<div class="col-xs-12 col-sm-6"><strong>Total:</strong>$ <span id = "credit_total"> </span> </div>
			</div>
			<br>
      		<div class = "row" style="line-height:30px; font-size:15px;">
				<div class ="col-xs-3">
					<label for="credit_amount">Credit Amount:</label>
				</div>
				<div class ="col-xs-5">
					<input type="text" id = "credit_amount" class = "form-control" placeholder="0.00"/>
				</div>
			</div>
			<div>
				<label for="credit_message" style="line-height:30px; font-size:15px;">Message:  </label>
				<textarea id="credit_message" class = "form-control" rows=4 placeholder="Message to Customer"></textarea>
			</div>
	  </div>
      <div class="modal-footer">
			<button class="btn btn-primary" id = "submit_credit" type="button">Submit</button>
			<button class="btn btn-primary" id = "Cancel" onclick="$('#credit').modal('hide')" type="button">Cancel</button>
      </div>    
    </div>
  </div>
</div>	
	
<div class="modal fade" id="refund" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title">Refund Order: </h3>
      	<button type="button" class="close" onclick="$('#refund').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      		<div class = "row">
				<div class="col-xs-12 col-sm-6">Order ID: <span id = "refund_id"> </span> </div>
				<div class="col-xs-12 col-sm-6">Name: <span id = "refund_name"> </span> </div>
				<div class="col-xs-12 col-sm-6">Phone: <span id = "refund_phone"> </span> </div>
				<div class="col-xs-12 col-sm-6">Order Total: <span id = "refund_total"> </span> </div>
				
			</div>
      		<div class = "row" style="line-height:30px; font-size:15px;">
				<div class ="col-xs-3">
					<label for="refund_amount">Refund Amount:</label>
				</div>
				<div class ="col-xs-5">
					<input type="text" id = "refund_amount" class = "form-control" placeholder="0.00"/>
				</div>
				<div class ="col-xs-4">
					 Maximum Refund: <span id="max_refund"> </span>
				</div>
			</div>
			<div>
				<label for="refund_reason" style="line-height:30px; font-size:15px;">Reason:  </label>
				<textarea id="refund_reason" class = "form-control" rows=4 placeholder="Reason for Refund"></textarea>
			</div>
	  </div>
      <div class="modal-footer">
			<button class="btn btn-primary" id = "submit_refund" type="button">Submit</button>
			<button class="btn btn-primary" id = "Cancel" onclick="$('#refund').modal('hide')" type="button">Cancel</button>
      </div>    
    </div>
  </div>
</div>
<div class="modal fade" id="cancel_order" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title">Cancel Order: </h3>
      	<button type="button" class="close" onclick="$('#cancel_order').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      		<div class = "row">
      		<div class="col-xs-6 col-sm-4">Order ID: <span id = "cancel_id"> </span> </div>
      		<div class="col-xs-6 col-sm-4">Name: <span id = "cancel_name"> </span> </div>
      		<div class="col-xs-6 col-sm-4">Phone: <span id = "cancel_phone"> </span> </div>
      		<div class="col-xs-6 col-sm-4">Total: <span id = "cancel_total"> </span> </div>
      		<div class="col-xs-12">**If order cancelation is confirmed the customer will receive a full refund**</div>
			</div>
      		<div>
				<label for="cancel_reason" style="line-height:30px; font-size:15px;">Reason:  </label>
				<textarea id="cancel_reason" class = "form-control" rows=4 placeholder="Reason for Cancelation"></textarea>
			</div>
	  </div>
      <div class="modal-footer">
			<button class="btn btn-primary" id = "submit_cancel" type="button">Request Cancelation</button>
			<button class="btn btn-primary" onclick="$('#cancel_order').modal('hide')" type="button">Nevermind..</button>
      </div>    
    </div>
  </div>
</div>
<div class="modal fade" id="credit_order_received" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title">Credit Sent: </h3>
      	<button type="button" class="close" onclick="$('#credit_order_received').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div style="line-height:30px; font-size:18px;">We've sent the customer an email with the store credit attached!</div>			
	  </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#credit_order_received').modal('hide')" type="button">Back</button>
      </div>    
    </div>
  </div>
</div>
<div class="modal fade" id="cancel_order_received" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title">Cancellation Requested: </h3>
      	<button type="button" class="close" onclick="$('#cancel_order_received').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div style="line-height:30px; font-size:18px;">Thank you for letting us know. <br> We will review the order and send a confirmation email when the cancellation is approved.</div>			
	  </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#cancel_order_received').modal('hide')" type="button">Back</button>
      </div>    
    </div>
  </div>
</div>

<div class="modal fade" id="refund_order_received" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title">Refund Requested: </h3>
      	<button type="button" class="close" onclick="$('#cancel_order_received').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div style="line-height:30px; font-size:18px;">The transaction has been refunded. <br>The customer has been informed and you should receive a confirmation email shortly.</div>			
	  </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#cancel_order_received').modal('hide')" type="button">Back</button>
      </div>    
    </div>
  </div>
</div>


<div class="modal fade" id="tutorial_new" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title">Website Tutorial: </h3>
      	<button type="button" class="close" onclick="$('#tutorial_new').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div style="line-height:30px; font-size:18px;">Welcome to Tastes-Good.com restaurant partners Dashboard. 
      			<br>  From the Dashboard you can:
      			<ul> 
					<li>open/close your restaurant</li>
					<li>track business performance</li>
					<li>perform actions on recent orders</li>
      			</ul>
      			We have prepared a tutorials to help you familiarize yourself with our interface.  
      			<br><br> Click the <img src = "tutorial/tutorial_icon.jpg" style="display:inline-block" /> buttons to learn more about the features of each page.
      			</div>
      </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#tutorial_new').modal('hide')" type="button">Okay</button>
      </div>    
    </div>
  </div>
</div>

<div class="modal fade" id="tutorial" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Your Dashboard: </h3>
      	<button type="button" class="close" onclick="$('#tutorial').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div>
      			<h3  style="line-height:25px;font-size:22px;"> 1. Main Switch: </h3>
      			<h2 style="line-height:25px;font-size:20px;"> This switch allows you to manually open/close your restaurant </h2>
      			<h3 style="line-height:25px;font-size:22px;"> 2. Manage Recent Orders: </h3>
      			<h2  style="line-height:25px;font-size:20px;"> When you start receiving orders they will look like this: </h2>
      			<img src = "tutorial/recent_orders.jpg" />
      			<br>
      			<h2  style="line-height:25px;font-size:20px;">Click an order will reveal the order details (e.g. Order #135)</h2>
      			<h2  style="line-height:25px;font-size:20px;">Once the order is expanded you can perform the following actions:</h2>
      			<ul  style="line-height:25px;font-size:20px;">
      				<li>Request Order Cancellation--For PAID orders the customer receives a full refund.</li>
      				<li>Partial Refund--Only for PAID orders. This button allows the restaurant to partially refund an order.</li>
      				<li>Order PDF--This button brings up the pdf document of the order.</li>
      			</ul>
      			
      			</div>
      </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#tutorial').modal('hide')" type="button">Okay</button>
      </div>    
    </div>
  </div>
</div>

<div class="modal fade" id="activate_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document"  style="width:80%">
    <div class="modal-content" >
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Confirm Account Activation: </h3>
      	<button type="button" class="close" onclick="$('#activate_modal').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div>
      			<h3  style="line-height:25px;font-size:22px;"> Account Launch: </h3>
      			<h2 style="line-height:25px;font-size:20px;">Once you click "confirm" your menu will be visible to the public and all test orders will be deleted. Before confirming make sure that you have completed the following tasks:</h2>
      			
      			<ul class="list-group" style="line-height:25px;font-size:20px;">
      				<li class="list-group-item">
      					<div class="checkbox">
						<label>
						<input id="chk1" name="activation_checklist" type="checkbox" value="">
						Your menu is online and up to date.  You are responsible to resolve any disputes that occur from errors in pricing or products being unavailable. 
						</label>
					</div>
      				
      					
      				</li>
      				<li class="list-group-item">
      					<div class="checkbox">
						<label>
						<input id="chk2" name="activation_checklist" type="checkbox" value="">
						Your schedule is accurate. You can change your schedule from the business details tab. 
						</label>
					</div>
      					
      				</li>
      				<li class="list-group-item">
      					<div class="checkbox">
						<label>
						<input id="chk3" name="activation_checklist" type="checkbox" value="">
						Your business details are accurate. Make sure that your logo, phone number, and email addresses are correct. 
						</label>
					</div>
      					
      				</li>
      				<li class="list-group-item">
      					<div class="checkbox">
						<label>
						<input id="chk4" name="activation_checklist" type="checkbox" value="">
						Printing in store.  If you are relying on our printer software make sure that it is installed and tested before activating your account.
						</label>
					</div>
      					
      					 
      				</li>
      				<li class="list-group-item">
      					<div class="checkbox">
						<label>
						<input id="chk5" name="activation_checklist" type="checkbox" value="">
						 <a href= "../termsconditions_rest.php"  target="_blank">Terms of Service</a> and <a href= "../privacy.php"  target="_blank">Privacy Policy</a>.  Make sure that you have read our terms of service--most importantly that you are primarily responsible for resolving disputes with customers and that if a dispute cannot be resolved www.Tastes-Good.com reserves to right to refund online payments (Chargebacks are Expensive!).
						</label>
					</div>
      					
      					 
      				</li>
      				
      			</ul>
      			
      			</div>
      </div>
      <div class="modal-footer">
			<button class="btn btn-danger" onclick="$('#activate_modal').modal('hide')" type="button" style="font-size:25px;padding:15px;padding-left:30px; padding-right:30px;">Cancel</button>
			<button class="btn btn-primary disabled" disabled="true" id = "submit_activation" type="button" style="font-size:25px;padding:15px;padding-left:30px; padding-right:30px;">Confirm</button>
      </div>    
    </div>
  </div>
</div>

<div class="modal fade" id="pay_for_marketing" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document" style="width:60%">
    <div class="modal-content"  >
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Account Launch Successful: </h3>
      	<button type="button" class="close" onclick="$('#pay_for_marketing').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div>
	      			<h3  style="line-height:25px;font-size:25px;"> Marketing Materials: </h3>
	      			<h2 style="line-height:25px;font-size:22px;">We want every partnership to be a success.  It is important to let your customers know that they can order online through our website. To this end we send all of our restaurant partners a set of 500 business cards and a static cling window sticker.  The cost of these marketing materials is $29.95 which is payed for by the restaurant partner.</h2>
	      			<h2 style="line-height:25px;font-size:22px;"></h2> 
	      			<h3  style="line-height:25px;font-size:25px;">Business Card Preview: </h3>
	      			<div style="position:relative; height:2.25in;width:100%;">
					<img style = "position:absolute; top:0; left:0; width:3.625in; height:2.125in;" src="../images/were_moving.png"/>
					<img style = "position:absolute; top:0.55in; left:0.125in;height:1.025in;width:1.025in;" src="<?php echo $rest->image; ?>"/>
									
				</div>
				
				<h3  style="line-height:25px;font-size:25px;">Payment Options: </h3>
	      			<h2 style="line-height:25px;font-size:22px;"> 
	      				Would you like to settle payment for these materials now? or would you prefer to deduct the amount from your account and start with a negative balance?
	      			</h2>
	      			
	      			<div id = "online_payments" style="position:relative; margin-top:30px;padding:10px;padding-top:30px;border:solid;border-width:0.8px;border-color:darkgrey"">
				<a href="https://www.braintreegateway.com/merchants/<?php echo $BTproduct->merchant; ?>/verified" target="_blank" style="position:absolute;top:-20px;left:10px;">
	    				<img src="https://s3.amazonaws.com/braintree-badges/braintree-badge-wide-dark.png" width="280px" height ="44px" border="0"/>
	   			</a>
	      			<form  id = "braintreeForm" action="checkout.php">
					<div id="payment_method_div" style="display:none;">
						<div style = "font-family:'Courier New';font-size:18px;margin-top:10px;margin-bottom:10px;" >Payment method encrypted by BRAINTREE.</div>
						<button id = "change_payment" class = "braintree-btn btn-block">Change Payment Method</button>
						
						
					</div>
					
					<div id="credit_div">					
						
						<div class = "row" style = " text-align:left;width:100%;line-height:35px;vertical-align: middle;display: inline-block;">
							
							<div class = "col-xs-12">
								<label for ="card-number" >Credit Card: </label>
								<div id = "card-number" class = "form-control" style="display:inline-block;width:auto;margin-left:20px;" autocomplete="off" type="text"></div>
							</div>
						</div>
					
						<div class = "row" style = "text-align:left;width:100%;line-height:35px;vertical-align: middle;display: inline-block;">
							<div class =  "col-xs-2 ">
								<label for="expiration-month" >Exp: </label>
							</div>
							<div class =  "col-xs-2 ">
								<div id = "expiration-month"  autocomplete="off" class = "form-control" type = "text" style="min-width: 30px;" placeholder = "MM"></div>
							</div>
							<div class =  "col-xs-2">
								<div id = "expiration-year"  autocomplete="off" class = "form-control" type = "text" style="min-width: 30px;" placeholder = "YY"></div>
								<span style = "position:absolute;top:-2px; font-size:20px; left:-2px;">/</span>
							</div>
							
							
							<div class = "col-xs-2 ">
								<label for="cvv"> CVV: </label>
							</div>
							<div class = "col-xs-4">
								<div id = "cvv"  autocomplete="off" type = "text" class = "form-control" type = "text" placeholder = "CVV"></div>
							</div>
							
							<div class = "col-xs-8">
								<input type="submit" class = "braintree-btn btn-block" id="authorize_credit" value="Pay with Visa" />
							</div>
							<div class = "col-xs-4">
								<div id = "paypal_div">
								<div id = "paypal_container"></div>
								</div>
							</div>
						</div>
						<input type = "hidden" id="payment_nonce"/>
						
					
					</div>
					
				</form>
      				</div>
      			
      			</div>
      </div>
      <div class="modal-footer">
			<button class="btn btn-warning" id = "no_payment"  type="button" style="font-size:25px;padding:15px;padding-left:30px; padding-right:30px;">Charge My Account</button>
			<button class="btn btn-primary disabled" disabled="true" id = "make_payment" type="button" style="font-size:25px;padding:15px;padding-left:30px; padding-right:30px;">
			Make Credit or Paypal Payment
			</button>
      </div>    
    </div>
  </div>
</div>


<div class="modal fade" id="order_confirmed" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Order Confirmed: </h3>
      	<button type="button" class="close" onclick="document.location.href='.'" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			
      			<h2  style="line-height:25px;font-size:22px;">Thank you for confirming the Order.  <br> Use the dashboard below to view or make changes to the Order.</h2>
      			
      			
      			
      </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="document.location.href='.'" type="button">Okay</button>
      </div>    
    </div>
  </div>
</div>

<div class="modal fade" id="order_rejected" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Order Rejected: </h3>
      	<button type="button" class="close" onclick="$('#order_rejected').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			
      			<h2 style="line-height:25px;font-size:22px;"> Order Rejected.. <br> If this was an error please email corporate@tastes-good.com ! </h2>
      			
      			
      			
      </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#order_rejected').modal('hide')" type="button">Okay</button>
      </div>    
    </div>
  </div>
</div>
	
	<?php /*
		<div class="row" style="margin-top:15px">
			<div class="col-lg-12">
				<div class="panel-default">
					<div class="panel-heading">Site Traffic Overview</div>
					<div class="panel-body jumbotron">
						<div class="canvas-wrapper">
							<canvas class="main-chart" id="line-chart" height="200" width="600"></canvas>
						</div>
					</div>
				</div>
			</div>
		</div><!--/.row-->

<script>
$(function (){
		
		var ctx = document.getElementById("line-chart").getContext("2d");
		window.Line_Chart =  new Chart(ctx, {
			type: 'line',
			data: window.pagehits
		});
		
		
});
</script>
*/ 
?>

</body>

</html>

<?php $conn->close(); ?>