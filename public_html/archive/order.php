<?php include 'boilerplate.php'; 
checkProduction();
include 'dbconnect.php'; 
require_once("braintree_init.php");
include("client_ping.php"); 
 

//"https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"  and  "http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
$rest_id = getattribute('rest_id');
$code = getattribute('coupon');


$sql="SELECT status FROM restaurant_client WHERE rest_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->bind_result($print_status);
$stmt->fetch();
$stmt->close();
if($print_status){
    	$result=PingServer($rest_id);
        echo "<script>console.log('".json_encode($result)."');</script>";
}


$a=new restaurant();
$a->grabRest($rest_id);
$a->grabSerial($rest_id);
$a->checkOpen();

if($a->status == "ACTIVE"){
	BTconfig($BTproduct);
} else {
	BTconfig($BTsandbox);
}

if(strlen($code)>0){
$a->grabCoupons($rest_id);

foreach($a->coupons as $coupon){
if($coupon->code == $code){
	break;
}
}

}else {
$coupon = 0;	


}


html_Header($a->title);
include 'header.php';

function hitPage($rest_id){
	global $conn;
	global $gUserid;
	$date = $_SERVER['REQUEST_TIME'];
	
	
	$sql = "INSERT INTO page_hits (rest_id, user_id, date) VALUES (?,?,?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("iis", $rest_id, $gUserid, $date);
	$stmt->execute();
	$stmt->close();
	
	$sql = "UPDATE restaurants SET page_hits = page_hits+1 WHERE rest_id = ?";
	$stmt=$conn->prepare($sql);
	echo $conn->error;
	$stmt->bind_param("i", $rest_id);
	$stmt->execute();
	$stmt->close();
}

hitPage($rest_id);

$menu=$a->menu;

if(isset($_SESSION['user_email'])){
	$email = $_SESSION['user_email'];	
	$user = new User();
	$user->fromSession($email);
	$user->user_name = $_SESSION['user_name'];	
}else {
	$user = new User();
}
		

?>

<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="https://js.braintreegateway.com/js/braintree-2.30.0.min.js"></script>


<script>
	user = <?php echo json_encode($user); ?>;
	community = "<?php echo $a->community ?>";
	geocoder = new google.maps.Geocoder();
	geoBounds = {lat: <?php echo $a->lat; ?>, lng: <?php echo $a->lng; ?>};
	btToken = '<?php  echo(Braintree\ClientToken::generate()); ?>'
	Categories = <?php echo json_encode($a->menu->categories); ?>;
	Extras = <?php echo json_encode($a->menu->extras); ?>;
	Rest = {"id":"<?php echo $a->rest_id; ?>", "title":"<?php echo htmlspecialchars_decode($a->title); ?>","address":"<?php echo htmlspecialchars_decode($a->address);?>", "lat":" <?php echo $a->lat; ?>","lng":"<?php echo $a->lng; ?>", "open":<?php echo $a->open; ?>, "closed":<?php echo $a->closed; ?>};
	Order = {"time":"<?php echo microtime(true)*1000;?>", "rest_id" : Rest.id, "user":{}, "pref":{},  "items":[], "driverTip":0, "paymentType":"offline", "discount_rate":0, "coupon":<?php echo json_encode($coupon); ?>, "subTotal":0, "total":0};
	OrderTotal = 0
	evtChain = [];
	theExtra = {};
	extra_id = 0;
</script>

<?php srcJavascript("scripts/basic.js", "scripts/ordermenu.js", "wickedpicker/src/wickedpicker.js"); 
srcStylesheet ("wickedpicker/stylesheets/wickedpicker.css");
?>

<div class= "hidden-sm hidden-md hidden-lg" style="position:fixed;height:30px;width:48%;top:92px;left:45%;z-index:100;">
	<button id="xs_total_btn" class="btn btn-warning" style="width:100%;font-size:18px"></span>Order Total: <span id="xs_total" style="margin-right:5px">$0.00</span>[...]</button>
</div>
<div class= "hidden-sm hidden-md hidden-lg" style="position:fixed;height:30px;width:38%;top:92px;left:5%;z-index:100;">
	<button id="xs_confirm_btn" class="btn btn-default" style="width:100%;font-size:18px"> Send Order</button>
</div>



<div class= "hidden-sm hidden-md hidden-lg" style="height:30px;"></div>

<div class="container-fluid row">
	<div class="col-xs-12">
		<div class= "jumbotron main">
			<div class="row">
				<div class="col-xs-3">
					<img  class="image img-rounded" src="<?php echo $a->image;?>">
				</div>
				<div class="col-xs-6">
					<h3><?php echo $a->title;?> </h3>
					<h2><?php echo $a->address;?> </h2>
					<h2><?php echo $a->phone;?></h2>
					<h2><?php echo $a->email;?></h2>
				</div>
				
			</div>
		</div>
	</div>
</div>



<div class="container-fluid row">
	<div id = "menu"  class = "col-sm-7">
		<div class="jumbotron main">
			<ol id = "rest_categories" class= "list-group">
				<?php 
				$itemCount = 0;
				foreach($menu->categories as $category){
					
					echo '<li class= "list-group-item">';
					echo '<button type="button" class="btn-block btn-md" data-toggle="collapse" data-target="#cat'.$category->id.'_items" id = "category['.$category->id.']" name="categories"> ';
					echo '<div id="cat'.$category->id.'_label" > '.$category->category.':';		
					echo '</div></button>'; 
					echo '<div id="cat'.$category->id.'_items" class = "panel-collapse collapse" style="padding-top:10px;padding-bottom:10px;padding-right:15px;padding-left:15px;"> ';
						
						foreach($category->items as $item){
										
							echo '<div id = "item['.$item->id.']" class="row"  name = "item">';
							echo '		<div id="item_'.$item->id.'_product" class = "col-xs-6 "><b>'.$item->product.'</b></div>';
							echo '		<div id="item_'.$item->id.'_price" class="col-xs-3 no-padding">$'.$item->price.'</div>';
							echo '		<button type="button" class = "btn col-xs-3 no-padding" onclick = "AddOrder('.$item->id.')">Order IT</button>';

							echo '</div>';
							echo '<div class="row" style="margin-bottom:15px;">';
								echo '<span class = "col-xs-12" id="item_'.$item->id.'_description" >'.$item->description.'</span>';
							echo '</div>';
							
						}
					echo '</div>';
					echo '</li>';
			
				}?>
			</ol>
		</div>
		
	</div>	




	<div id="orders" class = " col-sm-5" >
		<div class="container-fluid main">
			
			<h3 style="margin-top:5px;font-size:20px;">Order:</h3>
			
			<ul class = "list-group" id = "current_orders">
			<li class = "list-group-item" name = "order_placeholder">There are no items in your order.</li>
			</ul>
			
			<ul class = "list-group" id = "preview_totals">
				<li class= "list-group-item">  Sub-Total: <span class = "badge" id="order_subTotal">$0.00</span> </li>
				<li class= "list-group-item <?php if(!$coupon||$coupon->type=="item"){echo 'hidden">'; }else {echo '">  Discount ('.($coupon->discount * 100).'%):'; } ?>   <span class = "badge" id="order_discount">$0.00</span></li>
				<li class= "list-group-item">  Tax: <span class = "badge" id="order_tax">$0.00</span> </li>
				<br>
				<li class= "list-group-item"><h3 style="margin-top:0;">  Total:  </h3 ><h3 style="float:right;margin-top:0;" id="order_total">$0.00</h3></li>
				<br>
			</ul>
			<button class ="btn btn-block btn-default" style="margin-top:-10px;" name="submit" id = "submit_Order">Order Confirmation</button>
			<br>
			
		</div>
	</div>
</div>

<div id="prototypes" class = "hidden">
<li id = "order_proto" class = " list-group-item">
<h2 style="width:100%">
<button type="button" style="color:#633E26;" name="delete_order" class = "glyphicon glyphicon-minus"></button>
<span name = "item_id" class = "hidden" disabled = "disabled"></span>
<span name = "item_product"></span>
<span class = "badge" style="float:right;" name = "item_price"></span>
</h2>

	<ul class= "list-group" name = "item_extras" style = "margin-top:10px;">
	
	</ul>
</li>

</ul>
</li>
</div>
<div class="modal fade" id="order_success" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title" style="font-size:25px;">Success:</h3>
          
      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
        	<img src="images/email/thumbs_up.jpg" style ="height:100px;margin:5px;"/>
			<strong style="font-size:20px; ;">Order sent to <?php echo $a->title; ?>. </strong>
		
	  </div>
      <div class="modal-footer">
			<button data-dismiss="modal" aria-label="Close" class = "btn-block btn btn-primary" style="width:33%;float:right">Back</button>
				
			
	  </div>    
    </div>
  </div>
</div>
<div class="modal fade" id="order_error" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title" style="font-size:25px;">Whoops:</h3>
          
      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
        	<img src="images/email/thumbs_down.jpg" style ="height:100px;margin:5px;"/>
			<strong style="font-size:20px;"> We were unable to process the order. </strong>
		
	  </div>
      <div class="modal-footer">
			<button data-dismiss="modal" aria-label="Close" class = "btn-block btn btn-primary" style="width:33%;float:right">Back</button>
				
			
	  </div>    
    </div>
  </div>
</div>

<div class="modal fade" id="card_error" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title" style="font-size:25px;">Whoops:</h3>
          
      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
        	<img src="images/email/thumbs_down.jpg" style ="height:100px;margin:5px;"/>
			<strong style="font-size:20px;"> There was a problem processing your credit card. </strong>
		
	  </div>
      <div class="modal-footer">
			<button data-dismiss="modal" aria-label="Close" class = "btn-block btn btn-primary" style="width:33%;float:right">Back</button>
				
			
	  </div>    
    </div>
  </div>
</div>


<div class="modal fade" id="rest_closed" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title">Restaurant is Closed:</h3>
          
      	<button type="button" onclick="$('#rest_closed').modal('hide')" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
        	<h2> The restaurant is not able to receive your orders at the moment.  You may if you wish examine the menu and you are welcome to contact the restaurant by phone: </h2>  
			<h2> <?php echo $a->phone ?> </h2>			
			
		
	  </div>
      <div class="modal-footer">
			<button onclick="$('#rest_closed').modal('hide')" class = "btn-block btn btn-primary" style="width:33%;float:right">Back</button>
				
			
	  </div>    
    </div>
  </div>
</div>

<div class="modal fade" id="orderDetails" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
		<div class="modal-header">
			<h3 class= "modal-title">Processing Order:</h3>
			  
			<button type="button" class="close" onclick="$('#orderDetails').modal('hide');"  aria-label="Close"><span aria-hidden="true">&times;</span></button>
			
		</div>
     
      	<div class="modal-body">
      			<div class="row">
      				<div class = "col-xs-12"><h3 style="margin-top:0;">Customer Information:</h3></div>
      				<div class = "col-xs-12"><h2>Name: <span id="Cust_name"></span></div>
      				<div class = "col-xs-12"><h2>Email: <span id="Cust_email"></span></div>
      				<div class = "col-xs-12"><h2>Phone: <span id="Cust_phone"></span></div>
      				<div class = "col-xs-6"><button id="change_user" class="btn btn-block btn-default">Change User</button></div>
      				
      			
      			</div>
			<div class="row">
				<div class = "col-xs-12"><h3>Pickup or Delivery?</h3></div>					
				<div class=" col-xs-2">
					<input id="order_Pickup" checked="checked" name="deliveryOption" class="form-control no-padding" type="radio" style="height:20px;width:20px" value="pickup">
				</div>
				<div class=" col-xs-3 no-padding" style="line-height:25px;vertical-align: middle;display: inline-block;"><label for="order_Pickup">Pick-Up: </label></div>
				
				
				<div class="col-xs-2">
					<input id="order_Delivery" name="deliveryOption" class="form-control" type="radio" style="height:20px;width:20px" value="delivery">
				</div>
				<div class="col-xs-5 no-padding" style="line-height:25px;vertical-align: middle;display: inline-block;"><label for="order_Delivery">Delivery: </label> </div>
			</div>
			<div class="row" id="delivery_address">
				<div class="col-xs-12"><h3>Delivery Address: </h3></div>
				<div class="col-xs-12">
					<div id="order_AddressList" class="list-group">
														
					</div>
					<div class="row" id="delivery_address_new">
						<div class="col-xs-12"><h3>New Address Details:</h3></div>
													
						<div class="col-xs-2" style="line-height:40px;vertical-align: middle;display: inline-block;"> Address:</div>
						<div class="col-xs-10">
							<input id="order_newAddress" class="form-control" type="text" placeholder="Enter a Location"> 
						</div>
						<div class="col-xs-10 col-xs-offset-2">  (Example:  123 Sesame St., Toronto, Ontario) </div>
						<div class="col-xs-1 col-xs-offset-1">
							<input type="radio" checked="checked" class="form-control" name="addressType" value="house" id="addressHouse">  
						</div>
						<div class="col-xs-3 " style="line-height:40px;vertical-align: middle;display: inline-block;">House: </div>
						
						<div class="col-xs-1 col-xs-offset-1">
							<input type="radio" class="form-control" name="addressType" value="appt" id="addressHouse">  
						</div>
						<div class="col-xs-5" style="line-height:40px;vertical-align: middle;display: inline-block;">Apartment: </div>
						<div class="col-xs-10 col-xs-offset-2">Select if dwelling is single or multiple residence.</div>
					
						<div class="col-xs-12" id="appt_details">
							<div class="row">
								<div class="col-xs-2" style="line-height:40px;vertical-align: middle;display: inline-block;">Appt:</div>
								<div class="col-xs-4"> <input type="text" id="apptNumber" class="form-control" placeholder="###"> </div>
								<div class="col-xs-2" style="line-height:40px;vertical-align: middle;display: inline-block;">Buzz:</div>
								<div class="col-xs-4"><input type="text" id="apptBuzz" class="form-control" placeholder="###"> </div>
							</div>
						</div>
						<div class="col-xs-12" style="line-height:40px;vertical-align: middle;display: inline-block;"> Instruction for Driver:</div>
						
						<div class="col-xs-12">
							<input id="addressComments" class="form-control" type="text" placeholder="Instructions for Driver"> 
						</div>
							
							
					</div>
					
					<div id="delivery_address_buttons">
							<button type="button" class="btn btn-block" id="newAddress">++ Add Address</button>			
							<button type="button" class="btn btn-block" id="saveAddress">Save Address</button>
							<button type="button" class="btn btn-block" id="newAddress_Cancel">Back</button>
							<br><br>
					</div>	
					<div class = "row" id="driver_tip">
						<div class = "col-xs-12"><h3>Driver Tip:</h3></div>
						<div class = "col-xs-12"><label for="deliveryTip" style="display:none;">Tip the Driver?</label><input class="form-control" id= "deliveryTip" type = "text" placeholder = "$0.00"></input></div>
						
						<div class = "col-xs-3 ">
						<input name="deliveryTip"  id= "deliveryTip0" value="0.00" style="height:20px;width:20px" type = "radio"></input><label for="deliveryTip0">No Tip</label>
						</div>
						<div class = "col-xs-3 ">
						<input name="deliveryTip" checked = "checked" id= "deliveryTip5" style="height:20px;width:20px" value="0.05" type = "radio"></input><label for="deliveryTip5">5 %</label>
						</div>
						<div class = "col-xs-3 ">
						<input name="deliveryTip" checked = "checked" id= "deliveryTip8" style="height:20px;width:20px" value="0.05" type = "radio"></input><label for="deliveryTip8">8 %</label>
						</div>
						<div class = "col-xs-3 ">
						<input name="deliveryTip" checked = "checked" id= "deliveryTip12" style="height:20px;width:20px" value="0.05" type = "radio"></input><label for="deliveryTip12">12 %</label>
						</div>
				
				
					</div>
				</div>
			</div>			  
					
			<div class = "row">
				<div class="col-xs-12"><h3>Requested Time:</h3></div>
				
				<div class = "col-xs-6">
					<input id= "deliveryDate" type = "text"></input>
				</div>
				<div class = "col-xs-6">
					<input class="timepicker" name="timepicker" id= "deliveryTime" type = "text"></input>
				</div>
			</div>
			<br>
			
			
		</div>  
			
      <div class="modal-footer">
			<div class = "row">	
				<div class = "col-xs-6">
					<button onclick="$('#orderDetails').modal('hide');" class = "btn-block btn btn-primary">Back</button>
				</div>
				<div class = "col-xs-6">
					<button id="confirm_Order" class = "btn-block btn btn-primary">Order Confirmation</button>
				</div>
				
			</div>
		</div>    
    </div>
  </div>
</div>

<div class="modal fade" id="orderConfirm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title">Review Your Order: </h3>
          
      	<button type="button" class="close" onclick="$('#orderConfirm').modal('hide')" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
     		<div class = "row" id = "rest_info">
     			<div class = "col-xs-12">
     			<?php if($a->status != "ACTIVE") {echo "<h2>NOTE: THIS RESTAURANT IS NOT CONSIDERED ACTIVE YET--ORDERS WILL NOT BE PROCESSED AND REAL CREDIT CARDS WILL NOT BE RECOGNIZED.  WHEN YOU ARE SATISFIED WITH YOUR SETUP YOU CAN ACTIVATE YOUR RESTAURANT THROUGH THE RESTAURANT DASHBOARD</h2>";} ?>
     			</div>
     			<div class = "col-xs-12"><h3>Restaurant: <span id="rest_title"  style="color:rgba(30,30,70,1);"><?php echo $a->title;?></span></h3></div>
     			<div class = "col-xs-12"><h2><?php echo $a->address;?></h2></div>
     			
			</div>
			<div class = "row" id = "user_info">
				<div class = "col-xs-12"><h3 style="width:100%;">Customer: <span id="cust_name" style="color:rgba(30,30,70,1);"></span></h3></div>
				<div class = "col-xs-6"><h2>Email: <span id = "cust_email"></span></h2></div>
				<div class = "col-xs-6"><h2>Phone: <span id = "cust_phone"></span></h2></div>
				<div id= "cust_address_div" class = "col-xs-12"><h2>Address: <span id = "cust_address"></span></h2></div>
			</div>
			<div class = "row">
				<div  id = "delivery_pref" class = "col-xs-12">
				</div>
			</div>
			<br><hr class="hr1"><br>
			<div class = "row" id = "order_info">
			</div>
			<div class = "row">			
				<ul class = "list-group col-xs-12" id = "preview_totals">
					<li class= "list-group-item">Sub-Total: <span class = "badge" id="confirm_subTotal">$0.00</span> </li>
					<li class= "list-group-item" style = "display:none;">Discount: <span class = "badge" id="confirm_discount">$0.00</span> </li>
					<li class= "list-group-item" id="confirm_delivery_div">Delivery Charge: <span class = "badge" id="confirm_delivery">$0.00</span> </li>
					<li class= "list-group-item" id="confirm_tax_div">Tax (13%): <span class = "badge" id="confirm_tax">$0.00</span> </li>
					<li class= "list-group-item" id="confirm_delivery_tip_div">Tip The Driver: <span class = "badge" id="confirm_tip">$0.00</span> </li>
					<br>
					<li class= "list-group-item"> <h3 style="margin-top:0;">  Total:  </h3 ><h3 style="float:right;" id="confirm_total">$0.00</h3></li>
									
				</ul>
			</div>
			<div class="row">
				<div class= "col-xs-12">
					<label for="order_comments"><h3>Message to Restaurant:</h3></label>
					<br>
					<textarea maxlength="350" id = "order_comments" class = "form-control" rows="3"></textarea>
				</div>
			</div>
			<div class = "row" id = "payment_method">
				<div class = "col-xs-12"><h3>Payment Method: </h3></div>
				<div class = " col-xs-6">
					<label class="radio-inline"><input id="payment_Offline" checked = "checked" name="payment_type" type="radio" value = "offline" >Cash Payment</label>
					
				</div>
				
				<div class = " col-xs-6">
					<label class="radio-inline"><input id="payment_Online" checked = "checked" name="payment_type" type="radio" value = "online" >Online Payment</label>
				</div>
				
			
			</div>
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
      <div class="modal-footer">
      			
			
			<div class = "row" style = "margin-top:15px; margin-left:15px;">
			<div class="col-xs-2">
			<input type="checkbox" class="form-control" id="terms_conditions"  style="height:20px;"/>
			</div>
			<div class="col-xs-10">
			<label for="terms_conditions" style="text-align:left;">By clicking "Send Order" I am authorizing payment of the displayed amount and indicating that I have read and understand the website <a target = "_blank" href="./termsconditions.php"> terms and conditions </a></label>
			</div>
			</div>
			
			<div class= "row" id = "offline_buttons" style = " margin-top:20px;text-align:left;width:100%;line-height:35px;vertical-align: middle;display: inline-block;">
				<div class = "col-xs-6">
					<button class="btn btn-block btn-primary" id = "orderProcess" type="button">Send Order</button>
				</div>
				<div class = "col-xs-6">
					<button class="btn btn-block btn-primary" onclick = "$('#orderConfirm').modal('hide');"  id = "orderBack"  type="button">Cancel</button>
				</div>
			</div>
      </div>    
    </div>
  </div>
</div>

<div class="modal fade" id="ExtraModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title" style="width:70%;">Item: <span id="parentItem"  style="color:rgba(30,30,70,1);">The Item Name</span></h3>
          
      	<button type="button" class="close" onclick="$('#ExtraModal').modal('hide')" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
        <div class="modal-content">
			<img src ="/images/avatars/avatar1.jpg" />
			<h3 id="preview_question" class="modal-header">
				<div id = "extraQuestion"> The Question goes here. </div>
			</h3>
			<div  class="modal-body">
				<div id="extraSelect" class = "hidden">
				
				</div>
				<ul id = "extraCheck" style="max-width:350px;margin:auto;" class = "list-group hidden">
				
				</ul>  				
			</div>
			
		</div>	
	  </div>
      <div class="modal-footer">
			<button class="btn btn-primary" id = "extraContinue" type="button">Continue </button>
			<button class="btn btn-primary" id = "extraCancel" onclick="$('#ExtraModal').modal('hide')" type="button">Cancel</button>
      </div>    
    </div>
  </div>
</div>


<div class="modal fade" id="xs_preview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title">Order Details: </h3>
          
      	<button type="button" class="close" onclick="$('#xs_preview').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
        <div class="modal-content">
			<div id = "xs_preview_body" class="modal-body">
							
			</div>
			<hr>
			<div id = "xs_preview_totals" class="modal-body" >
			
			</div>
			
		</div>	
	  </div>
      <div class="modal-footer">
			<button class="btn btn-primary" id = "xs_preview_continue" onclick="$('#xs_preview').modal('hide');initDetails();" type="button">Checkout</button>
			<button class="btn btn-primary" onclick="$('#xs_preview').modal('hide')" type="button">Cancel</button>
      </div>    
    </div>
  </div>
</div>



			

<?php  include "footer.php"; ?>
<?php $conn->close(); ?>