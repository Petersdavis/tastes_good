<?php include '../boilerplate.php'; 
checkProduction();
if(!isset($_GET['show_terms']) && (!isset($_SESSION['pass'])||!$_SESSION['pass'])){
	header('Location: ../');
}

include '../dbconnect.php'; 
html_Header("Restaurant Dashboard");

$community = $_SESSION['community'];
$rest_id = $_SESSION['rest_id'];


$rest = new Restaurant();
$rest->grabRest($rest_id);
$file = "new_user";
include 'header.php';

?>


<!--Icons-->
<script src="js/lumino.glyphs.js"></script>
<script src="../scripts/newrest.js"></script>
<script src="../scripts/basic.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="../wickedpicker/src/wickedpicker.js"></script>
<link rel="stylesheet" href="../wickedpicker/stylesheets/wickedpicker.css">
<script> Restaurant = <?php echo json_encode($rest); ?> </script>

<!--[if lt IE 9]>
<script src="js/html5shiv.js"></script>
<script src="js/respond.min.js"></script>
<![endif]-->


<div  class="main sidebar col-sm-3 col-lg-2" style="margin-left:10px;">	
	<?php include 'sidebar_content.php' ?>

</div>

<div class = "row">	
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2" style = "padding-left:45px; padding-right:40px;">		
		<div class="row" style = "margin-top:0; margin-bottom:10px;">
			<div class="col-xs-12 container main" style = "padding:0;background-color:rgb(172,232,213);">
				<h3 style = "margin:10px; width:100%;text-align:center;font-size:30px;">Welcome to Tastes-Good.com</h3> 
			</div>		
		</div>
	</div>
</div>


<div class = "row" id = "sales_pitch">	
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2" style = "padding-left:45px; padding-right:40px;">	
		<div class = "row" style = "margin-top:0; margin-bottom:10px;">
			<div class="col-xs-12 container main" style="background-color:rgb(172,232,213);">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">
				Our Goal:
				<h2  style = "background-color:#f0ffff; margin-top:0; padding:10px; width:100%;text-align:center;font-size:20px;">
				An affordable and easy to use platform for restaurants to capture online orders.
				</h2>
								
				
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">
				Current Features Let You Easily:
				</h3>
				<h2  style = "background-color:#f0ffff; margin-top:0; padding-top:0; width:100%;text-align:center;font-size:20px;">
				<ul class = "list-group">
					<li class = "list-group-item">Customize and manage your own menu.</li>
					<li class = "list-group-item">Use coupons to promote specials and use store credit to resolve disputes</li>
					<li class = "list-group-item">Automated printing of orders in store.</li>
					<li class = "list-group-item">Set your own delivery rates.</li>
					<li class = "list-group-item">Accept online payments with Paypal and Credit Cards.</li>
				</ul>
				</h2>
				
				
				
				<div class = "jumbotron" style = "background-color:rgba(210,245,225,0.8);">
					<h3  style = "margin-left:0; margin-right:0; margin-top:0; margin-bottom:0; padding-top:0; padding-bottom:10px;width:100%;text-align:center;font-size:20px;">The Bottom Line:</h3>
					<div class = "row">
						<div class="col-sm-12 col-md-4">
							<div class="panel-widget">
								<div class="row no-padding">
									<div class="col-sm-3 col-lg-5 widget-left" style="background-color:rgb(172,232,213);">
										<h3 style = "width:100%;text-align:center;font-size:25px;"> $0.00 </h3>
									</div>
									<div class="col-sm-9 col-lg-7 widget-right">
										<h2  style = "background-color:#f0ffff; margin-top:5px; padding-top:0; width:100%;text-align:center;font-size:20px;line-height:30px;vertical-align:middle;">Monthly Fees</h2>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-4">
							<div class="panel-red panel-widget">
								<div class="row no-padding">
									<div class="col-sm-3 col-lg-5 widget-left" style="background-color:rgb(172,232,213);">
										<h3 style = "width:100%;text-align:center;font-size:25px;"> 3.5% </h3>
									</div>
									<div class="col-sm-9 col-lg-7 widget-right">
										<h2  style = "background-color:#f0ffff; margin-top:5px; padding-top:0; width:100%;text-align:center;font-size:20px;line-height:30px;vertical-align:middle;">Commission</h2>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-md-4">
							<div class="panel-red panel-widget">
								<div class="row no-padding">
									<div class="col-sm-3 col-lg-5 widget-left" style="background-color:rgb(172,232,213);">
										<h3 style = "width:100%;text-align:center;font-size:25px;"> +2.5% </h3>
									</div>
									<div class="col-sm-9 col-lg-7 widget-right">
										<h2  style = "background-color:#f0ffff; margin-top:5px; padding-top:0; width:100%;text-align:center;font-size:20px;line-height:30px;vertical-align:middle;">Pay-Online</h2>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<h3 style = "margin-left:0; margin-right:0; margin-top:10; margin-bottom:0; padding-top:0; padding-bottom:10px;width:100%;text-align:center;font-size:20px;"> <span class="glyphicon glyphicon-star"></span> Marketing Deployments: At Cost +5.0% <span class="glyphicon glyphicon-star"></span></h3>
					
				</div>
				
			
		
		
		<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">
				Video Demonstrations:
				</h3>
				<h2  style = "background-color:#f0ffff; margin-top:0; padding-top:0; width:100%;text-align:center;font-size:20px;">
				<div class = "row" style="padding:5px;">
					<div class="col-xs-6">
						<button class="btn btn-info" id="vid_menu" style="width:100%;max-width:500px;"> 
							Menu Builder  <span class="glyphicon glyphicon-film" style="float:right;"></span>
						</button>
					</div>
					<div class="col-xs-6">
						<button class="btn btn-info" id="vid_user" style="width:100%;max-width:500px;"> 
							Customer Experience  <span class="glyphicon glyphicon-film" style="float:right;"></span>
						</button>
					</div>
					<div class="col-xs-6">
						<button class="btn btn-info" id="vid_account" style="width:100%;max-width:500px;"> 
							Accounts and Payments <span class="glyphicon glyphicon-film" style="float:right;"></span>
						</button>
					</div>
					<div class="col-xs-6">
						<button class="btn btn-info" id="vid_coupons" style="width:100%;max-width:500px;"> 
							Coupons and Store Credit  <span class="glyphicon glyphicon-film" style="float:right;"></span>
						</button>
					</div>
					
				</ul>
				</h2>
			</div>
		</div>
		<div class="row" style = "margin-top:0;">
			<div class="col-xs-12 container main" style = "padding:0;background-color:rgb(172,232,213);">
				<h3 class="page-header" style = "width:100%;text-align:center;font-size:30px;">Have we got your attention?</h3>
				
				<div class="row" style="margin-left:10px; margin-right:10px;">
					<div class="col-sm-12 col-md-6">
						<button id="btn_continue" class =  "btn btn-lg btn-block btn-primary">Begin Free Trial. </button>
					</div>
					<div class="col-sm-12 col-md-6">
						<button id="btn_decline" class =  "btn btn-lg btn-block btn-warning">No Thanks, Take me off your list. </button>
					</div>
				</div>
				<br><br>
			</div>
		</div>
		
	</div>
</div>
			
			
<div class = "row" id = "rest_details" style="display:none;">	
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2" style = "padding-left:45px; padding-right:40px;">		
		<div class="row main" style = "margin-top:0; margin-bottom:10px;padding:0;background-color:rgb(172,232,213);">
			<div class="col-xs-12 container main" style = "background-color:rgb(172,232,213);padding:0;">
				<h3 style = "margin:10px; width:100%;text-align:center;font-size:30px;">Please provide restaurant information:</h3> 
			</div>
		</div>
		
		
		<div class="row" style = "background-color:rgba(210,245,225,1);margin-left:5px;margin-right:5px;" >
			<div class="col-xs-12 ">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px;width:100%;text-align:center;font-size:30px;">Restaurant Details:  </h3> 
				
				<h2 style = "margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:20px;"> 
					<div class = "row" style="background-color:#f0ffff">
					<div class="col-xs-12 container main">
				<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">Contact Details: </h3>
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
									<input name = "rest_details" type="text"  id="rest_title" class = "form-control" value="<?php echo $rest->title; ?>" />
								</div>
							</div>
							<div class = "row">
								<div class = "col-xs-3">
									<label for="rest_fname" style = "line-height:30px;"> First Name: </label>
								</div>
								<div class = "col-xs-3">
									<input name="rest_details" type="text"  id="rest_fname" class = "form-control" value="<?php echo $rest->fname; ?>" />
								</div>
								<div class = "col-xs-3">
									<label for="rest_lname" style = "line-height:30px;"> Last Name: </label>
								</div>
								<div class = "col-xs-3">
									<input name="rest_details" type="text"  id="rest_lname" class = "form-control" value="<?php echo $rest->lname; ?>" />
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
									<input name = "rest_details" type="text"  id="rest_address" class = "form-control" value="<?php echo $rest->address; ?>" />
								</div>
							</div>
							<div class = "row">
								<div class = "col-xs-3">
									<label for="rest_phone" style = "line-height:30px;"> Phone: </label>
								</div>
								<div class = "col-xs-9">
									<input name = "rest_details" type="text"  id="rest_phone" class = "form-control" value="<?php echo $rest->phone; ?>" />
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
							<h3 style = "margin-left:0; margin-right:0; margin-top:10px; margin-bottom:0; padding-top:10px; padding-bottom:10px; background-color:rgba(210,245,225,0.8);width:100%;text-align:center;font-size:20px;">Delivery Settings: <span style="float:right;margin-left:-86px;"></h3>
							<h2  style = "background-color:#f0ffff; margin-top:0; padding-top:10px; width:100%;text-align:center;font-size:20px;">
							<div class = "row">
								<div class = "col-xs-9">
									<label for="offers_delivery" style = "line-height:30px;"> Does your restaurant offer delivery? </label>
								</div>
								<div class = "col-xs-3">
									<input name = "delivery_details" type="checkbox"  id="offers_delivery" class = "form-control" checked = "true" />
								</div>
								<div class = "col-xs-9">
									<label for="delivery_base" style = "line-height:30px;"> How much will you charge  the customer for delivery? </label>
								</div>
								<div class = "col-xs-3">
									<input name = "delivery_details" type="text"  id="delivery_base" class = "form-control" value="$<?php echo $rest->delivery_base ; ?>"></input>
								</div>
								
							</div>
							
							
							</h2>
						</div>	
						
						
						
	
					  </div>

					</h2>			
					
				</div>
					</div>
				</h2>
				
			</div>
		</div>
		
		
		<div class = "main" style = "width:100%; margin-top:10px;background-color:#f0ffff">
			<button class = "btn btn-block" onclick="$('#terms_details').slideToggle();" style = "margin-bottom:10px;padding:0;background-color:rgb(172,232,213);">
						<h3 class="page-header" style = "width:100%;text-align:center;font-size:20px;margin:10px;">Terms and Conditions (Click to Read)</h3> 
			</button>
			<div class = "row" style ="display:none;padding-left:30px;padding-right:30px;" id="terms_details">
				<div class="col-xs-12 main">
					<?php include "../termsconditions_rest.html"; ?>
				</div>
			</div>
			
			<button class = "btn btn-block no-padding" onclick="$('#privacy_details').slideToggle();" style = "margin-bottom:10px;padding:0;background-color:rgb(172,232,213);">
						<h3 class="page-header" style = "width:100%;text-align:center;font-size:20px;margin:10px;">Privacy Policy  (Click to Read)</h3> 
			</button>
			<div class = "row" style ="display:none;padding-left:30px;padding-right:30px;" id="privacy_details">
				<div class="col-xs-12 main">
					<?php include "../privacy.html"; ?>
				</div>
			</div>
					
			<div class = "row main" style="font-size:22px;margin:10px;">
			
				<div class = "col-xs-8 ">
					<label for="terms_consent" style = "line-height:40px;">I have read and understood the website Terms and Conditions: </label> 
				</div>
				<div class = "col-xs-4">
					<input class = "form-control form-inline" name="consent" type="checkbox" id = "terms_consent"  />
				</div>
					
			</div>
			<div class = "row main" style="font-size:22px;margin:10px;">
			
				<div class = "col-xs-8 ">
					<label for="privacy_consent" style = "line-height:40px;">I have read and understood the website Privacy Policy </label> 
				</div>
				<div class = "col-xs-4">
					<input class = "form-control form-inline" name="consent" type="checkbox" id = "privacy_consent"  />
				</div>
					
			</div>
			
			<div class = "row">
				<div class="col-xs-12">
					<button class = "btn btn-primary btn-block disabled" disabled="disabled" id="consent_continue" style="font-size:25px;padding:15px;">Sign Up</button>
				</div>
			</div>
					
			
		</div>
	</div>
					
</div>

<div class = "row" style="display:none;" id = "exit">	
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2" style = "padding-left:45px; padding-right:40px;">			
			
		<div class="row">
			<div class="col-xs-12 container main" style = "margin-top:0; background-color:rgb(172,232,213);">
				<h3 class="page-header" style = "width:100%;text-align:center;font-size:30px;">No Problemo!</h3> 
			</div>
		</div>
		
		<div class = "row">
			<div class="col-xs-12 container main">
				<h2  style = "width:100%;text-align:center;font-size:20px;">Thank you for taking the time to check out our service.  We respect your decision and will not bother you with more marketing materials.  Our staff may contact you in the coming weeks to better understand how our service can meet your needs.  If you change your mind, you can reach out to us at corporate@tastes-good.com.  </h2>
			</div>
		</div>
	</div>	
</div>

<div class="modal fade" id="About" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title" id="myModalLabel">Our Service:</h3>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="Vid_Demo" data-category = "" data-categoryid = "" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document" style = "margin:auto;margin-top:25px;width:100%;max-width:1400px;">
    <div class="modal-content" style = "margin:auto;width:1350px;">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" ><span aria-hidden="true" style="font-size:25px;">&times;</span></button>
        <h3 class="modal-title" id="demo_title" style="font-size:25px;"></h3>
      </div>
      <div class="modal-body">
         <iframe width="1280" height="720" id="demo_vid" src="" frameborder="0" allowfullscreen></iframe>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"  style="font-size:25px;">Close</button>
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



<?php
if(isset($_GET['show_terms']) ){
echo "<script>showterms = 1</script>";	
}else{
echo "<script>showterms = 0</script>";	
}

$conn->close(); ?>