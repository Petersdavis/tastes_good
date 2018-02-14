<?php include 'boilerplate.php';?>
<?php checkProduction();?>
<?php include 'dbconnect.php'; ?> 
<?php html_Header("Tastes-Good Restaurants");

/* This is an example of an early page.  I thought it was a good idea to generate the entire page in PHP which you can see starting at line 120. 
Now I am thinking that it is better to write the pages as basic html pages-- and just use php to handle database interaction.  The initial reason for this was that it is that I found it very difficult to transport a php application into a mobile application.  The php generates a dynamic website--but the point of the mobile app is to download a static file and then only to communicate with the server for transfering data.

Also I have moved to using a single page instead of multiple php scripts so that page transistions can be handled smoothly without a refresh  


*/

include 'header.php';
//  Get the correct list of restaurants
// From PostCode
	$community = getattribute('community');
	$sql = "SELECT time_offset, province, lat, lng, timezone FROM community WHERE name = ?";
	$stmt=$conn->prepare($sql);
	$stmt->bind_param("s", $community);
	$stmt->execute();
	$stmt->bind_result($time_offset, $province, $lat, $lng, $timezone);
	$stmt->fetch();
	$stmt->close();
	
	$sql = 'SELECT rest_id, points FROM restaurants WHERE community = ? AND status = "ACTIVE" ORDER BY points';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $community);
	$stmt->execute();
	$stmt->bind_result($rest_id, $points);
	$restIDs = [];
	
	while($stmt->fetch()){
		
		array_push($restIDs, $rest_id);
	}
	$stmt->close();
	
	if(isset($_SESSION['rest_id'])){
		$sql = 'SELECT rest_id, points FROM restaurants WHERE community = ? AND status = "TESTING" ORDER BY points';
		$stmt = $conn->prepare($sql);
		$stmt->bind_param('s', $community);
		$stmt->execute();
		$stmt->bind_result($rest_id, $points);
		
		while($stmt->fetch()){	
			array_push($restIDs, $rest_id);
		}
		$stmt->close();
	}
	
	$restaurants = [];
	$closed_restaurants = [];
	date_default_timezone_set($timezone);
	
	
	$local_time = new DateTime();
	
	$local_time_stamp = $local_time->getTimestamp();
	$week_day = $local_time->format("l");
	
	foreach($restIDs as $id){
		
		$restaurant= new restaurant();
		$restaurant->grabRest($id);
		$restaurant->grabCoupons($id);
		$restaurant->checkOpen($timezone);
		
		if($restaurant->open){
		array_push($restaurants, $restaurant);
		}else{
		array_push($closed_restaurants, $restaurant);
		}
		
	}
	
	$restaurants = array_merge($restaurants, $closed_restaurants);		
	
		
	
	if(isset($_SESSION['user_email'])){
	$email = $_SESSION['user_email'];	
	$user = new User();
	$user->fromSession($email);
	$user->user_name = $_SESSION['user_name'];
	}else {
		$user = new User();
	}
	
	?>
	<script>restaurants = <?php echo json_encode($restaurants); ?> ;
			user = <?php echo json_encode($user); ?>;
			province = "<?php echo $province; ?>" ;			
			geocoder = new google.maps.Geocoder();
			geoBounds = {lat: <?php echo $lat; ?>, lng: <?php echo $lng; ?>};
			<?php
			if(isset($_GET['lat'])&& isset($_GET['lng'])){
				echo 'position = {coords:{latitude:'.$_GET['lat'].', longitude:'.$_GET['lng'].', accuracy:0}};';
			}else{
				echo 'position = {coords:{latitude:0, longitude:0, accuracy:0}}';
			}
			?>			
	</script>
	
	<?php srcJavascript("/scripts/list.js");?>
	<?php srcJavascript("/scripts/basic.js");?>
	
	
	
<div id="list_buttons" class = "row container-fluid" >
<div class="col-xs-6">
	<button id="set_address" class = "btn btn-block btn-primary" >Set Location</button>
</div>

<div id="s_coupon_btn" class="col-xs-6">
	<button id="show_coupons" class = "btn btn-block btn-primary">Use Coupon</button>
</div>
<div id="h_coupon_btn" class="col-xs-6" style="display:none;">
	<button id="hide_coupons" class = "btn btn-block btn-primary" >Hide Coupons</button>
</div>
</div>
	
	
<div id = "rest_list" class = "container-fluid">
	<?php
	foreach($restaurants as $restaurant){
		?>
		
		<div id="<?php echo $restaurant->rest_id; ?>" style = "position:relative;" class="jumbotron main">
		<a href = "order.php?rest_id=<?php echo $restaurant->rest_id; ?>" id="<?php echo $restaurant->rest_id; ?>"  class = "btn btn-block <?php if(!$restaurant->open||$restaurant->closed){echo "disabled";} ?>" >
			<div class = "row">
				<div class = "col-xs-4 col-sm-4">
					<img src = "<?php echo $restaurant->image; ?>" class="image-rounded" height = "150" width = "150" style="margin-right:10px;"/>
				</div>
				<div class = "col-xs-8 col-sm-8">
					<h3><?php echo $restaurant->title; ?></h3>
					<h2><strong>Open: </strong> <?php echo $restaurant->open_time->format("h:i a"); ?>  <strong style="margin-left:10px;">Close: </strong> <?php echo $restaurant->close_time->format("h:i a"); ?></h2>
					
					<h2><?php echo $restaurant->address; ?></h2>
					<h2 name="distance" style="display:none"></h2>
					<h2 name="charge" style="display:none"></h2>
				</div>
			</div>
			
		</a>
		<?php if(!$restaurant->open||$restaurant->closed){echo "<div class= 'rotate alert alert-danger' style = 'position:absolute;top:30px;left:-20px;padding-left:50px;padding-right:50px;'> Closed </div> ";} ?>
		</div>
		
		<?php
  	}
  	?>
</div>

<div id = "coup_list" style = "display:none;" class="container-fluid">
	<div class= "row" style="padding-left:5%; padding-right:5%">
		<div class = "col-xs-12 main" style ="padding:10px;">
			<div class = "row">
				<div class = "col-xs-12 col-sm-4"><label for = "coupon_code" style="line-height:30px;font-size:20px;">Your Coupon Code: </label> </div>
				<div class = "col-xs-8 col-sm-4"><input id="coupon_code" type = "text" class="form-control"/> </div>
				<div class = "col-xs-4 col-sm-4 no-padding"><button id="coupon_code_btn" class="btn btn-default"> Search .. <span class="glyphicon glyphicon-search" aria-hidden="true"></span> </button></div>
			</div>
			<div id="coupon_error" class = "alert alert-warning" hidden style="margin:25px;"><h2 style="font-size:20px;">Whoops! That code is expired or invalid</h2> </div> 
		</div>
	
		<?php
		foreach($restaurants as $restaurant){
			
			foreach($restaurant->coupons as $coupon){
				if($coupon->public && $restaurant->open){
					?>
					<div style= "width:175px;margin:5px; display:inline-block;height:100px;">
					<div style="position:relative;">
						<a href = "<?php echo $coupon->link; ?>" style = "position:absolute; top:0; left:0; width:175px; height:100px; z-index:1000;"></a>
						<img style = "position:absolute; top:0; left:0; width:175px; height:100px;" src="/images/biz_card_blank.png"/>
						<h2 style = "position:absolute; top:0px;line-height:15px; left:5px;font-size:15px; width:160px; text-align:center;" > <?php echo $coupon->title;  if($coupon->type =="item"){echo ': <span style="color: #633E26;">$'. $coupon->price .'</span>';} ?> </h3>
						<img style = "position:absolute; top:22px; left:0;height:56px;width:56px;" src="<?php echo $restaurant->image; ?>"/>
						<h3 style  = "position:absolute; top:55px; margin-top:0; left:67px;font-size:15px; width:100px; text-align:center;"><?php echo $coupon->code; ?></h3>
						<h2 style  = "position:absolute; top:35px; margin-top:0; left:30px;font-size:15px; width:175px; text-align:center;">Coupon Code:</h2>
					
					</div>
					</div>
					<?php
				
				}
				
				
			}
				
				
			
			
		}
		?>
		<div class = "col-xs-12" style="clear: both;"></div>
	</div>
	
</div>

<div class="modal fade" id="chooseAddress" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
		<div class="modal-header">
			<h3 class= "modal-title">Find Nearby Restaurants:</h3>
			  
			<button type="button" class="close" onclick="$('#chooseAddress').modal('hide');"  aria-label="Close"><span aria-hidden="true">&times;</span></button>
			
		</div>
     
      	<div class="modal-body">
			<div class="row">
				<div class="col-xs-12"><h3> Choose Address: </h3></div>
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
						<div class="col-xs-5" style="line-height:40px;vertical-align: middle;display: inline-block;">Appartment: </div>
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
							<input id="addressComments" class="form-control" type="text" placeholder="Requests/Comments for the Driver"> 
						</div>
							
							
					</div>
					
					<div id="delivery_address_buttons">
							<button type="button" class="btn btn-block" id="newAddress">++ Add Address</button>			
							<button type="button" class="btn btn-block" id="saveAddress">Calculate Delivery Distance</button>
							<button type="button" class="btn btn-block" id="newAddress_Cancel">Back</button>
							<br><br>
					</div>				  
				</div>
		
			
				
			</div>	
			
			
			
		</div>  
			
      <div class="modal-footer">
			<div class = "row">	
				<div class = "col-xs-12">
					<button onclick = "$('#chooseAddress').modal('hide');" class = "btn-block btn btn-primary">Back</button>
				</div>
			</div>
		</div>    
    </div>
  </div>
</div>
<?php
 include "footer.php"; 
  	
 $conn->close();  ?>
 