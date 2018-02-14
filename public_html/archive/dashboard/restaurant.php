<?php include '../boilerplate.php'; 
if(!isset($_SESSION['pass'])||!$_SESSION['pass']){
	header('Location: ../');
}

checkProduction();
include '../dbconnect.php';
include '../braintree_init.php'; 
html_Header("Restaurant Settings"); 


$community = $_SESSION['community'];
$rest_id = $_SESSION['rest_id'];

$rest = new Restaurant();
$rest->grabRest($rest_id);
include 'header.php';
$new_rest = getattribute('new_rest');
if($new_rest){
	echo "<script>new_rest=1</script>";
}else{
	echo "<script>new_rest=0</script>";
}

?>
<script src="../scripts/restaurant.js"></script>
<script src="../scripts/basic.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="https://js.braintreegateway.com/js/braintree-2.27.0.min.js"></script>
<script src="../wickedpicker/src/wickedpicker.js"></script>
<link rel="stylesheet" href="../wickedpicker/stylesheets/wickedpicker.css">

<!--Icons-->
<script src="js/lumino.glyphs.js"></script>

<!--[if lt IE 9]>
<script src="js/html5shiv.js"></script>
<script src="js/respond.min.js"></script>
<![endif]-->

<div class="main sidebar col-sm-3 col-lg-2 sidebar" style="margin-left:10px;">
	<?php include 'sidebar_content.php' ?>
</div><!--/.sidebar-->
<div class = "row">	
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2" style = "padding-left:45px; padding-right:40px;">			
			
		<div class="row" style = "background-color:rgba(210,245,225,1);margin-left:5px;margin-right:5px;" >
			<div class="col-xs-12 ">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:30px;">Restaurant Details: <button onclick = "$('#tutorial').modal('show');" class = "btn btn-primary" style="float:right;font-size:25px" >?</button> </h3> 
				
				<h2 style = "margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:20px;"> 
					<div class = "row" style="background-color:#f0ffff">
					<div class="col-xs-12 container main">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">Contact Details:  <span style="float:right;margin-left:-86px;"><button id="save_details" class = "btn btn-default" style="padding:5px;font-size:20px">Save Details</button></span></h3>
				<h2  style = "background-color:#f0ffff; margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:20px;">
					<div class = "row">
						<div class = "col-xs-3" >
							Preview: <br>
							<img id = "image_preview" src= "<?php echo $rest->image;?>" style="width:120px; height:120px; padding:5px;background-color:lightgrey;border-style: dashed;" />
							<label class="btn btn-default btn-file">Business Logo: <input name = "rest_details" id="rest_logo" type = "file"></label><br>
												
						</div>
						<div class = "col-xs-9" style="padding-top:15px;">
							<div class = "row">
								<div class = "col-xs-3">
									<label for="rest_title" style = "line-height:30px;"> Title: </label>
								</div>
								<div class = "col-xs-9">
									<input name = "rest_details" type="text"  id="rest_title" class = "form-control" placeholder="<?php echo $rest->title; ?>" />
								</div>
							</div>
							<div class = "row">
								<div class = "col-xs-3">
									<label for="rest_fname" style = "line-height:30px;"> First Name: </label>
								</div>
								<div class = "col-xs-3">
									<input name="rest_details" type="text"  id="rest_fname" class = "form-control" placeholder="<?php echo $rest->first_name; ?>" />
								</div>
								<div class = "col-xs-3">
									<label for="rest_lname" style = "line-height:30px;"> Last Name: </label>
								</div>
								<div class = "col-xs-3">
									<input name="rest_details" type="text"  id="rest_lname" class = "form-control" placeholder="<?php echo $rest->last_name; ?>" />
								</div>
								
							</div>
							<div class = "row">
								<div class = "col-xs-3">
									<label for="rest_type" style = "line-height:30px;"> Style: </label>
								</div>
								<div class = "col-xs-9">
									<select id="rest_type" name="rest_details" class = "form-control">
									<?php $sql = "select type from restaurant_type";
									$stmt=$conn->prepare($sql);
									$stmt->execute();
									$stmt->bind_result($type);
									while($stmt->fetch()){
										echo '<option value="'.$type.'"';
											if($type == $rest->type){ echo "selected";}
										echo '>'.$type.'</option>';	
									}
									?>
									
									</select>
									
								</div>
							</div>
							<div class = "row">
								<div class = "col-xs-3">
									<label for="rest_address" style = "line-height:30px;"> Address: </label>
								</div>
								<div class = "col-xs-9">
									<input name = "rest_details" type="text"  id="rest_address" class = "form-control" placeholder="<?php echo $rest->address; ?>" />
								</div>
							</div>
							<div class = "row">
								<div class = "col-xs-3">
									<label for="rest_phone" style = "line-height:30px;"> Phone: </label>
								</div>
								<div class = "col-xs-9">
									<input name = "rest_details" type="text"  id="rest_phone" class = "form-control" placeholder="<?php echo $rest->phone; ?>" />
								</div>
							</div>
							<div class = "row">
								<div class = "col-xs-3">
									<label for="rest_email" style = "line-height:30px;"> Email: </label>
								</div>
								<div class = "col-xs-9">
									<input name = "rest_details" type="text"  id="rest_email" class = "form-control" placeholder="<?php echo $rest->email; ?>" />
								</div>
							</div>
							<div class = "row">
								<div class = "col-xs-12">
									<button class="btn btn-block" data-toggle="modal"  data-target="#password_modal"> Change Password </button>
								</div>
								
							</div>
							<br>
							<div id = "file_size_error" class="alert alert-danger no-padding" style="display:none;">
							  <strong>Whoops!</strong> filesize exceeds 50kb maximum.
							</div>
							
						</div>
						<div class="col-xs-12 container main">
							<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">Delivery Settings: <span style="float:right;margin-left:-86px;"><button id="save_delivery" class = "btn btn-default" style="padding:5px;font-size:20px">Save Delivery</button></span></h3>
							<h2  style = "background-color:#f0ffff; margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:20px;">
							<div class = "row">
								<div class = "col-xs-9">
									<label for="offers_delivery" style = "line-height:30px;"> Does your restaurant offer delivery? </label>
								</div>
								<div class = "col-xs-3">
									<input name = "delivery_details" type="checkbox"  id="offers_delivery" class = "form-control" checked = "<?php if($rest->offers_delivery){echo "true";}else{echo "false";} ?>" />
								</div>
								<div class = "col-xs-9">
									<label for="delivery_base" style = "line-height:30px;"> How much do you charge  the customer for delivery? </label>
								</div>
								<div class = "col-xs-3">
									<input name = "delivery_details" type="text"  id="delivery_base" class = "form-control" <?php if(!$rest->offers_delivery){echo "disabled='true'";} ?>   value="$<?php echo $rest->delivery_base ; ?>"></input>
								</div>
								
								<div class = "col-xs-9">
									<label for="delivery_email" style = "line-height:30px;"> Notify delivery driver by email when restaurant confirms a delivery order? </label>
								</div>
								<div class = "col-xs-3">
									<input name = "delivery_details" type="text"  id="delivery_email" class = "form-control" <?php if(!$rest->offers_delivery){echo "disabled='true'";} ?>  placeholder='Delivery Driver Email' <?php if(isset($rest->delivery_email)){echo "value = '".$rest->delivery_email."'";} ?> />
								</div>
								
						</div>
							
							
							</h2>
						</div>	
						
						<div class="col-xs-12 container main">
							<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">Schedule: <span style="float:right;margin-left:-86px;"><button id="save_schedule" class = "btn btn-default" style="padding:5px;font-size:20px">Save Schedule</button></span></h3>
							<h2  style = "background-color:#f0ffff; margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:20px;">
								<div class = "row">
									<div class = "col-xs-4 col-xs-offset-4" >
										Open:											
									</div>
									<div class = "col-xs-4" >
										Close:											
									</div>
								</div>
								<div class = "row">
									<div class = "col-xs-4" >
										Monday:											
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "monday_open" style="width:100% !important;" type = "text"></input>							
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "monday_close"  style="width:100% !important;" type = "text"></input>										
									</div>
								</div>
								<div class = "row">
									<div class = "col-xs-4" >
										Tuesday:											
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "tuesday_open"  style="width:100% !important;" type = "text"></input>							
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "tuesday_close"  style="width:100% !important;" type = "text"></input>										
									</div>
								</div>
								<div class = "row">
									<div class = "col-xs-4" >
										Wednesday:											
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "wednesday_open"  style="width:100% !important;" type = "text"></input>							
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "wednesday_close"  style="width:100% !important;" type = "text"></input>										
									</div>
								</div>
								<div class = "row">
									<div class = "col-xs-4" >
										Thursday:											
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "thursday_open"  style="width:100% !important;" type = "text"></input>							
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "thursday_close"  style="width:100% !important;" type = "text"></input>										
									</div>
								</div>
								<div class = "row">
									<div class = "col-xs-4" >
										Friday:											
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "friday_open"  style="width:100% !important;" type = "text"></input>							
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "friday_close"  style="width:100% !important;" type = "text"></input>										
									</div>
								</div>
								<div class = "row">
									<div class = "col-xs-4" >
										Saturday:											
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "saturday_open"  style="width:100% !important;" type = "text"></input>							
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "saturday_close"  style="width:100% !important;" type = "text"></input>										
									</div>
								</div>
								<div class = "row">
									<div class = "col-xs-4" >
										Sunday:											
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "sunday_open"  style="width:100% !important;" type = "text"></input>							
									</div>
									<div class = "col-xs-4" >
										<input class="timepicker" name="timepicker" id= "sunday_close"  style="width:100% !important;" type = "text"></input>										
									</div>
								</div>
							<br><br>**We suggest closing your online store 15 to 30 minutes earlier than actual closing time**
							<br><br>**You can also close your online store for holidays and unscheduled closures through the dashboard**
							</h2>			
							
						</div>
						
						<div class="col-xs-12 container main">
							<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">How we pay you: </h3>
							<h2  style = "background-color:#f0ffff; margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:20px;">
							We use paypal's bulk payments method to settle accounts on a bi-weekly basis.  You will receive an email every second week with the balance between online-payments collected and outstanding commissions and processing fees.
							</h2>
						</div>
					  </div>

					</h2>			
					
				</div>
					</div>
				</h2>
				
			</div>
		</div>
		
		
	</div>
</div>

<div class="modal fade" id="password_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class= "modal-title">Change Password: </h3>
          
      	<button type="button" class="close" onclick="$('#password_modal').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
        	<div class = "row">
				<div class = "col-xs-3">
					<label for="old_pwd" style = "line-height:30px;"> Old Password: </label>
				</div>
				<div class = "col-xs-9">
					<input  type="password" autocomplete="off" id="old_pwd" class = "form-control" />
				</div>
			</div>
			<br><hr><br>
		
			<div class = "row">
				<div class = "col-xs-3">
					<label for="new_pwd" style = "line-height:30px;"> New Password: </label>
				</div>
				<div class = "col-xs-9">
					<input  type="password" autocomplete="off" id="new_pwd" class = "form-control" />
				</div>
			</div>
			<div class = "row">
				<div class = "col-xs-3">
					<label for="confirm_pwd"  style = "line-height:30px;"> Confirm Password: </label>
				</div>
				<div class = "col-xs-9">
					<input type="password"  id="confirm_pwd" autocomplete="off" class = "form-control" />
				</div>
			</div>
		
	  </div>
      <div class="modal-footer">
			<button class="btn btn-primary" id = "save_password" type="button">Save Password</button>
			<button class="btn btn-primary" id = "extraCancel" onclick="$('#password_modal').modal('hide');" type="button">Cancel</button>
      </div>    
    </div>
  </div>
</div>

<div class="modal fade" id="tutorial" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:rgb(210,245,225);">
        <h3 class= "modal-title" style="line-height:30px;font-size:25px;">Restaurant Details: </h3>
      	<button type="button" class="close" onclick="$('#tutorial').modal('hide');" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
      </div>
      <div class="modal-body">
      			<div>
      			<h3  style="line-height:25px;font-size:22px;"> 1. Logo: </h3>
      			<h2 style="line-height:25px;font-size:20px;">Upload your business logo.  Don't forget to click "save details" </h2>
      			<h3 style="line-height:25px;font-size:22px;"> 2. Restaurant Details: </h3>
      			<h2  style="line-height:25px;font-size:20px;">Except for your personal name--these details appear on the restaurant menu.  Again don't forget to save your changes!</h2>
      			<h3 style="line-height:25px;font-size:22px;"> 3. Change Password: </h3>
      			<h2  style="line-height:25px;font-size:20px;">This button pops up a dialogue allowing you to reset your password.</h2>
      			<h3 style="line-height:25px;font-size:22px;"> 4. Schedule: </h3>
      			<h2  style="line-height:25px;font-size:20px;">Scroll down to provide regular opening & closing times for each day of the week.</h2>
      			<h2  style="line-height:25px;font-size:20px;">Open Late? No Problem. If you set the close time to before the open time we automatically adjust the day</h2>
      			
      			</div>
      </div>
      <div class="modal-footer">
			<button class="btn btn-primary" onclick="$('#tutorial').modal('hide')" type="button">Back</button>
      </div>    
    </div>
  </div>
</div>


<script>
Restaurant = <?php echo json_encode($rest);?>;

</script>

</body>

</html>
