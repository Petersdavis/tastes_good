<?php include '../boilerplate.php'; 
if(!isset($_SESSION['pass'])||!$_SESSION['pass']){
	header('Location: ../');
}

checkProduction();?>
<?php include '../dbconnect.php'; ?> 
<?php include '../braintree_init.php'; ?>
<?php html_Header("Marketing Materials"); 
include '../header.php';?>
<?php  $rest_id = $_SESSION['rest_id'];
$rest = new Restaurant;
$rest->grabRest($rest_id);


?>

<!--Icons-->
<script src="js/lumino.glyphs.js"></script>

<!--[if lt IE 9]>
<script src="js/html5shiv.js"></script>
<script src="js/respond.min.js"></script>
<![endif]-->


<script> btToken = '<?php  echo(Braintree\ClientToken::generate()); ?>'</script>
<script> rest_id = '<?php  echo $rest_id; ?>'</script>
<script src="../scripts/marketing.js"></script>
<script src="../scripts/basic.js"></script>
<script src="../scripts/jcanvas.min.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="https://js.braintreegateway.com/js/braintree-2.27.0.min.js"></script>



<div class="main sidebar col-sm-3 col-lg-2 sidebar" style="margin-left:10px;">
	<?php include 'sidebar_content.php' ?>
</div><!--/.sidebar-->
		
<div class="row">
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
		
		<div class ="container main" style="width:95%">
		
			<ul class="pagination pagination-lg" style="width:100%">
			  <li id="basic_materials"  class="active"><a href="#">Basic Materials</a></li>
			  <li id="facebook"><a href="#">Facebook Advertising</a></li>
			  <li id="coupons"><a href="#">Coupons/Discounts</a></li>
			</ul>
		</div>
	
		<div class = "container main" style="width:95%" id = "basic_page">
			<div class= "row">
				<div class="col-xs-12">
					<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">"We're Moving!"  Cards </h3>
					<h2  style="background-color:#f0ffff; margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:30px;">Increase customer conversion by sending out these classy cards with every order.  The cards are linked to a unique TG coupon code that expires three months after launching. 
						<div class = "row no-padding">
							<div class = "col-lg-7 no-padding">
								<div style = "background-color:#f0ffff; height:400px;">
									<img style = "height:300px; width:525px;position:absolute;left:25px;top:25px;" src="/images/biz_card.svg"/>
									<h3 style = "font-size:40px; position:absolute; left:280px; top:170px;">N4DJ7AFS</h3>
									<img style = "height:170px; width:170px;position:absolute;left:30px;top:90px;" src="<?php echo $rest->image; ?> "/>
								</div>
							</div>
							<div class = "col-lg-5 no-padding" >
								<h2  style="vertical-align:middle;background-color:#f0ffff; margin-top:0; padding-top:30px; width:100%;text-align:center;font-size:30px;"><strong> 500 Cards for $12.95 </strong></h2>
								<h3  style="vertical-align:middle;background-color:#f0ffff; margin-top:0; padding-top:30px; width:100%;text-align:center;font-size:30px;"> Bonus: </h3>
								<h2  style="vertical-align:middle;background-color:#f0ffff; margin-top:0; padding-top:30px; width:100%;text-align:center;font-size:30px;"><strong>Whenever your customer uses our coupon your restaurant earns:</strong>
								<ul class = "list-group"><li class = "list-group-item">$0.50 commission credits</li><li class = "list-group-item"> 1<span class="glyphicon glyphicon-star" style="font-size:20px;"></span>TG-Points.</li></ul></h2>
							
							</div>
							
						</div>
					
					</h2> 
				</div>
				<div class = "col-xs-12">
					<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Window Sticker:</h3>
					<h2  style="background-color:#f0ffff; margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:30px;">Static-cling window stickers advertise that your business is accepting online orders. 
					<div class = "row no-padding">
						<div class = "col-lg-7 no-padding">
							<div style = "background-color:#f0ffff; height:400px;">
								<img style = "height:300px; width:525px;position:absolute;left:25px;top:25px;" src="/images/tg_banner.svg"/>
								
							</div>
						</div>
						<div class = "col-lg-5 no-padding" >
							<h2  style="vertical-align:middle;background-color:#f0ffff; margin-top:0; padding-top:30px; width:100%;text-align:center;font-size:30px;"><strong> Large Window Sticker $19.95 <strong></h2>
							<h3  style="vertical-align:middle;background-color:#f0ffff; margin-top:0; padding-top:30px; width:100%;text-align:center;font-size:30px;"> Bonus: </h3>
							<h2  style="vertical-align:middle;background-color:#f0ffff; margin-top:0; padding-top:30px; width:100%;text-align:center;font-size:30px;"><strong>When you order a window sticker your restaurant earns:</strong>
							<ul class = "list-group"><li class = "list-group-item">$5.00 commission credits</li><li class = "list-group-item"> 10<span class="glyphicon glyphicon-star" style="font-size:20px;"></span>TG-Points.</li></ul></h2>
						</div>
						
					</div>
					
					</h2> 
				</div>
				<div class= "col-xs-12" style="background-color:#f0ffff;">
					<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:35px;">Basic Marketing Materials:</h3>
					
					 <div class= "row">
						<div class= "col-xs-6">
							<label for="biz_cards" style="font-size:25px;line-height:50px;"> 3.5" x 2" Business Cards x500 </label>
						</div>
						<div class="col-xs-3">
							<select class="form-control no-padding" style = "height:50px; font-size:25px;" id="biz_cards">
								<option value="0">No Thanks</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
							</select>
						</div>
						<div class="col-xs-3" >
							<h3 id="biz_card_total" style="width:100%;text-align:right;font-size:25px;">$0.00</h3>
						</div>
					 </div>
					 <div class="row">
						<div class= "col-xs-6">
							<label for="biz_cards" style="font-size:25px;line-height:50px;"> 8" x 20" Window Sticker </label>
						</div>
						<div class="col-xs-3">
							<select class="form-control no-padding" style = "height:50px; font-size:25px;" id="window_sticker">
								<option value="0">No Thanks</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
							</select>
						</div>
						<div class="col-xs-3">
							<h3 id="win_sticker_total" style="width:100%;text-align:right;font-size:25px;">$0.00</h3>
						</div>
					 </div>
					 <br><br><hr><br>
					 
					 <br><hr><br>
					 <div class = "row">
						<div class = "col-xs-9">
						 <h2 style="font-size:25px;line-height:50px;"><strong> Total: </strong></h2>
						
						</div>
						<div class = "col-xs-3">
							<h2 id="basic_total" style="font-weight:bold; width:100%;text-align:right;font-size:30px;line-height:50px;">$0.00</h2>
						</div>
					 </div>
					 <div class= "row">
						<div class = "col-xs-12">
							<button class="btn btn-block btn-primary" id="submit_basic" style="font-weight:bold;font-size:30px;line-height:50px;">Confirm Order</button>
						</div>
					 </div>
				</div>
			</div>
		</div>
		<div class = "container main" id = "facebook_page">
		</div>
	
		<div class = "container main" id = "coupons_page">
		</div>
	</div>
</div>


<div class="modal fade" id="payment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title">Payment Method: </h3>
          
      	<button type="button" class="close" onclick="$('#payment').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
		<form  id="braintreeForm" action="checkout.php">
				<div id = "paypal_div">
					<div class = "row" style = "height:50px;text-align:left;width:100%;line-height:30px;vertical-align: middle;display: inline-block;">
						<div class = "col-xs-4" id="paypal_prepend"><h3> Paywith </h3> </div>
						<div class = "col-xs-8"><div id = "paypal_container"></div> </div>
					</div>
				</div>
				<div id="credit_div" class = "container main" style="width:85%;">	
					<div class = "row" style = " text-align:left;width:100%;line-height:35px;vertical-align: middle;display: inline-block;">
						<h3 class = "col-xs-12">(OR) Authorize credit card transaction:</h3>
					</div>
					
					<div class = "row" style = " text-align:left;width:100%;line-height:35px;vertical-align: middle;display: inline-block;">
						<div class = "col-xs-3">
							<label for ="card-number">Credit Card: </label>
						</div>
						<div class = "col-xs-9">
							<div id = "card-number" class = "form-control" autocomplete="off" type="text" placeholder = "XXXX XXXX XXXX XXXX"></div>
						</div>
					</div>
				
					<div class = "row" style = "text-align:left;width:100%;line-height:35px;vertical-align: middle;display: inline-block;">
						<div class = "col-xs-3">
							<label for="expiration-month">Exp: </label>
						</div>
						<div class =  "col-xs-2">
							<div id = "expiration-month"  autocomplete="off" class = "col-xs-6 form-control" type = "text" placeholder = "MM"></div>
						</div>
						<h2 class = "col-xs-1"> / </h3>
						<div class = "col-xs-2">
							<div id = "expiration-year"  autocomplete="off" class = "col-xs-6 form-control" type = "text" placeholder = "YY"></div>
						</div>
						<div class = "col-xs-2">
							<label for="cvv"> CVV: </label>
						</div>
						<div class = "col-xs-2">
							<div id = "cvv"  autocomplete="off" type = "text" class = "col-xs-6 form-control" type = "text" placeholder = "CVV"></div>
						</div>
						<div class = "col-xs-12">
							<input type="submit" class = "btn btn-primary btn-block" id="authorize_credit" value="Pay with Visa" />
						</div>
					</div>
					<input type = "hidden" id="payment_nonce"/>
				</div>
				
			</form>	
		
	  </div>
      <div class="modal-footer">
			<div class= "row" id = "payment_buttons" style = " margin-top:20px;text-align:left;width:100%;line-height:35px;vertical-align: middle;display: inline-block;">
				<div class = "col-xs-6">
					<button class="btn btn-block btn-primary" id = "confirm_payment" type="button" disabled>Make Payment</button>
				</div>
				<div class = "col-xs-6">
					<button class="btn btn-block btn-primary" onclick = "$('#payment').modal('hide');"  id = "orderBack" type="button">Cancel</button>
				</div>
			</div>
      </div>    
    </div>
  </div>
</div>