<?php srcJavascript("scripts/footer.js");?>

<div id="loading_gif"  style = "border-width:0.8px;border-style:solid;display:none; position:fixed; height:150px; top:35%; width:50%; left:25%; border-radius:8px; box-shadow: 2px 2px 4px darkgrey;z-index:10000;background-color:white">
<img src="images/loading.gif" style ="height:140px;margin:5px;"/>
<strong style="font-size:25px;"> Loading... </strong>
</div>


<nav class="bottom-navbar">
<div class="container container-fluid">
   <div class="navbar-inner">
		<div class="nav navbar-header">
			
			<button type="button" class="btn btn-default navbar-toggle" data-toggle="collapse" data-target="#bottomNavbar">
			<span class="glyphicon glyphicon-align-justify" style="font-size:30px;" aria-hidden="true"></span>
			</button>
			
		</div>
		
		
		
	   <div class="collapse navbar-collapse " id="bottomNavbar" >
				<div class="nav navbar-nav" style="padding-left:10px;padding-right:10px;">
					<button  class="btn btn-default" data-toggle="modal" data-target="#About" style="height:35px; font-size:15px;font-family: 'Gorditas', cursive; color: #C37848;">About:</button>
				</div>
				<div class="nav navbar-nav" style="padding-left:10px;padding-right:10px;">
					<button   class="btn btn-default" data-toggle="modal" data-target="#Contact" style="height:35px; font-size:15px;font-family: 'Gorditas', cursive; color: #C37848;">Contact Us:</button>
				</div>
				<div class="nav navbar-nav" style="padding-left:10px;padding-right:10px;">
					<a  href="/termsconditions.php" class="btn btn-default" style="height:35px; font-size:15px;font-family: 'Gorditas', cursive; color: #C37848;" >Terms/Conditions:</a>
				</div>
				<div class="nav navbar-nav" style="padding-left:10px;padding-right:10px;">
					<a  href="/privacy.php" class="btn btn-default" style="height:35px; font-size:15px;font-family: 'Gorditas', cursive; color: #C37848;" >Privacy Policy:</a>
				</div>
				<div class="nav navbar-nav" style="padding-left:10px;padding-right:10px;float:right;">
					<a href ="#"><img src="images/icons/AppStore.svg" style="width:120px;"/> </a>
				</div>
				<div class="nav navbar-nav" style="padding-left:10px;padding-right:10px;float:right;">
					 <a href ="#"><img src="images/icons/GooglePlay.svg" style="width:120px;" /></a>
				</div>
			
			
			
		
		</div><!--/.nav-collapse -->

  </div>
</div>
</nav>


  

<div class="modal fade" id="chooseAddress" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
		<div class="modal-header">
			<h3 class= "modal-title">Address Information:</h3>
			  
			<button type="button" class="close" onclick="$('#addressNew').modal('hide');"  aria-label="Close"><span aria-hidden="true">&times;</span></button>
			
		</div>
     
      	<div class="modal-body">
      		<div id="order_AddressList" class="list-group">
														
			</div>
					
			<div id="new_address" class = "container-fluid main" style="width:100%;background-color:rgba(0,0,0,.1);">
				<label for="newUserStreet"><h3> Add New Address </h3></label>
				<div class="row">
					<div class = "col-xs-12">
						<input id="newUserStreet" class="form-control" type="text" placeholder="Your Address"></input>
					</div>
				</div>
				<div class = "row" style="line-height:30px;">
					<div class = "col-xs-1 col-xs-offset-1"><input type="radio" name="dwellingType" value="house" checked="checked"></input> </div>
					<div class = "col-xs-4">house</div>
					<div class = "col-xs-1 col-xs-offset-1"><input type="radio" name="dwellingType" value="appartment"></input> </div>
					<div class = "col-xs-4">appartment</div>
				</div>
				<div id="newUserApptDetails" style="display:none; line-height:30px;" class="row">
					<div class="col-sm-2 col-xs-12">Appartment: </div>
					<div class="col-sm-4 col-xs-12">   
						<input id="newUserApptNumber" class="form-control" type="text" ></input>
					</div>
					
					<div class="col-sm-2 col-xs-12">Buzz: </div>
					<div class="col-sm-4 col-xs-12">   
						<input id="newUserApptBuzz" class="form-control" type="text" ></input>
					</div>
				</div>
				<br>
				<div class = "row">
					<div class = "col-sm-2 col-xs-4">Province: </div>
					<div class = "col-sm-4 col-xs-8">
						<select id="newUserProvince">
						  <option value="Alberta">Alberta</option>
						  <option value="BC">British Columbia</option>
						  <option value="Manitoba">Manitoba</option>
						  <option value="New Brunswick">New Brunswick</option>
						  <option value="Newfoundland">Newfoundland</option>
						  <option value="NT">Northwest Territories</option>
						  <option value="Nova Scotia">Nova Scotia</option>
						  <option value="NU">Nunavut</option>
						  <option value="Ontario">Ontario</option>
						  <option value="PEI">Prince Edward Island</option>
						  <option value="Quebec">Quebec</option>
						  <option value="Saskatchewan">Saskatchewan</option>
						  <option value="YT">Yukon Territory</option>
						</select>
					</div>
					<div class = "col-sm-2 col-xs-4">Postal Code: </div>
					<div class = "col-sm-4 col-xs-8">
						<input id="newUserPostCode" class="form-control" type="text" ></input>
					</div>
				</div>
				<label for="newUserComments" data-icon="p">Comments About Address:</label>
				<textarea id="newUserComments" rows = "3" class="form-control" ></textarea>
				<br>
				<button id = "submit_new_address" class = "btn btn-block btn-default">Create Address</button><br>
				
			</div>		
		</div>
		
      	<div class="modal-footer">
			<div class = "row">	
				<div class = "col-xs-6">
					<button onclick = "$('#addressNew').modal('hide');" class = "btn-block btn btn-primary">Back</button>
				</div>
				<div class = "col-xs-6">
					<button id="submit_newAddress" class = "btn-block btn btn-primary">Submit</button>
				</div>
				
			</div>
		</div>    
    </div>
  </div>
</div>


<div class="modal fade" id="userLogin" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
		<div class="modal-header">
			<h3 class= "modal-title">Existing User Login:   </h3>
			  
			<button type="button" class="close" onclick="$('#userLogin').modal('hide');"  aria-label="Close"><span aria-hidden="true">&times;</span></button>
			
		</div>
     
      	<div class="modal-body">
			<form id="login" method="post" autocomplete="on"> 
				<div class = "row">
					
					<div class = "col-xs-12 col-sm-6">
						<label for="userId" style = "display:none">Email Address:</label>
						<input id="userId" class="form-control" type="text" placeholder="Email Address"> </input>	
					</div>	
					<div class = "col-xs-12 col-sm-6">
						<label for="userPwd" style = "display:none">Password:</label>
						 <input id="userPwd" class="form-control" required="required" type="password" placeholder="password" ></input>
					</div>			  
					<div class = "col-xs-12">
						<button id = "forget_pwd" type = "button"  class="btn-block btn btn-link"  style = "color: #633E26;">Forgot Password?</button>
					</div>		
					<div class = "col-xs-12">
						<button type="button" id = "new_user"  class="btn-block btn btn-link" style = "color: #633E26;"> (Register New User)</button>
					</div>
				</div>
				<br><br>
				
			</form>
			<div id="forgotPwd" role = "document">
				<div class="modal-content">
									 
					<div class="modal-body">
						<div class = "row">
							<div class = "col-xs-12">
								<form id="login" method="post" autocomplete="on"> 
									<h3>Password Recovery: </h3>
									<div class="row">
										<div class = "col-xs-4"><label for="email_recovery">Email Address:</label></div>
										<div class = "col-xs-8">
											<input id="email_recovery" class="form-control"  type="text" ></input>
										</div>
										
									</div>	
									
									<h2 class = "bg-danger" style="padding-top:5px;padding-bottom:5px;padding-left:10px;border-radius:6px;" id="email_noexist">Account does not exist.  Check email address.</h2>
									<h2 class = "bg-success" style="padding-top:5px;padding-bottom:5px;padding-left:10px;border-radius:6px;" id="email_sent">Check your inbox for password recovery email.</h2>
								</form>
							</div>
						</div>			
					</div>	
				
				  <div class="modal-footer">
						<div class = "row" >	
							<div class = "col-xs-6">
								<button id = "forgot_pwd_Cancel"  class = "btn-block btn btn-primary">Cancel</button>
							</div>
							<div class = "col-xs-6">
								<button id="submit_forgot_pwd" class = "btn-block btn btn-primary">Recover Password</button>
							</div>
							
						</div>
					</div>    
				</div>
			</div>
			<div id="userNew" role="document">
				<div class="modal-content">
									 
					<div class="modal-body">
						<div class = "row">
							<div class = "col-xs-12">
								<form id="login" method="post" autocomplete="on"> 
									<h3> User Details: </h3>
									<div class="row">
										<div class = "col-xs-2">First Name:</div>
										<div class = "col-xs-4">
											<input id="newUserFName" class="form-control"  type="text" ></input>
										</div>
										<div class = "col-xs-2">Last Name:</div>
										<div class = "col-xs-4">
											<input id="newUserLName" class="form-control"  type="text" ></input>
										</div>
									</div>	
									
									<div class="row">
										<div class = "col-xs-2">Phone:</div>
										<div class = "col-xs-10">
											<input id="newUserPhone" class="form-control" type="text" > </input>
										</div>
									</div>
									
									<div class="row">
										<div class = "col-xs-2">Email:</div>
										<div class = "col-xs-10">
											<input id="newUserEmail" class="form-control" type="text" > </input>
										</div>
									
									</div>
									<h2 class = "bg-danger" style="padding-top:5px;padding-bottom:5px;padding-left:10px;border-radius:6px;" id="invalid_email">This email belongs to another account.</h2>
									
									<br><br>
									
									<h3>Password </h3>
									<div class="row">
										<div class = "col-sm-2 col-xs-4">Password:</div>
										<div class = "col-sm-4 col-xs-8">
											<input id="newUserPwd" class="form-control"  type="password" ></input>
										</div>
										<div class = "col-sm-2 col-xs-4">Confirm:</div>
										<div class = "col-sm-4 col-xs-8">
											<input id="newUserPwdConfirm" class="form-control"  type="password" ></input>
										</div>
									</div>
									<h2 class = "bg-danger" style="padding-top:5px;padding-bottom:5px;padding-left:10px;border-radius:6px;" id="short_password">
										Password must be between 7 and 32 characters long.
									</h2>
									
									<br>
									<h2> Please indicate that you have read our website's Terms &amp Conditions and Privacy Policy: </h2>
									<div class = "row">
										<div class = "col-xs-2"><input type="checkbox" name="legal" id="read_terms"/></div>
										<div class = "col-xs-4">
											<label for "read_terms" title="check to indicate that you have read and accept the website terms and conditions"><a target="_blank" href="/termsconditions.php">Terms &amp Conditions </a></label>
										</div>
										<div class = "col-xs-2"><input type="checkbox"  name="legal" id="read_privacy"/></div>
										<div class = "col-xs-4">
											<label for "read_privacy" title="check to indicate that you have read and accept the website's privacy policy"><a target="_blank" href="/Privacy.php">Privacy Policy</a></label>
										</div>
									
									</div>
									
									
								</form>
							</div>
						</div>			
					</div>	
				
				  <div class="modal-footer">
						<div class = "row">	
							<div class = "col-xs-6">
								<button id = "new_User_Cancel" class = "btn-block btn btn-primary">Cancel</button>
							</div>
							<div class = "col-xs-6">
								<button id="submit_newUser" class = "btn-block btn btn-primary disabled" disabled="disabled">Create User</button>
							</div>
							
						</div>
					</div>    
				</div>
			  </div>
			
						
				
			
			
		</div>	
	
      <div class="modal-footer">
			<div class = "row"  id = "existing_user_btn">	
				<div class = "col-xs-6">
					<button onclick = "$('#userLogin').modal('hide');" class = "btn-block btn btn-primary">Back</button>
				</div>
				<div class = "col-xs-6">
					<button id="submit_login" class = "btn-block btn btn-primary">Login</button>
				</div>
				
			</div>
			
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
     	<h2>1. What we do: </h2>
     	We are an online platform offering restaurants in Canada an affordable means to promote their menus online
     	<h2>2. Our Mission: </h2>
     	We believe in simplicity.  We are not concerned with too much glitz and flair.
     	Our goal is to give restaurants the tools they need to overcome the barriers selling their products online. 
     	<h2>3. A Strong Database Backend </h2>
     	Menu's can get pretty complicated.  Items can have options can have sub-options our unique integration handles this tree structure seemlessly so that restaurants can mimic the order-flow that occurs in the restaurant.
     	<h2>4. Payment Processing</h2>
     	Accepting credit cards online is a daunting task for an independent restaurants and can create additional liability.  Our goal is to offer restaurant partners online payment processing as soon as possible.
     	     	
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="Success" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
 <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title" id="myModalLabel" style="font-size:22px;">Success:</h3>
      </div>
      <div class="modal-body"> 
      	<h2 id = "successMsg" style="font-size:18px;"> Thank you for your feedback. <br> We will respond at the earliest opportunity! </h3>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Okay</button>
      </div>
    </div>
 </div>
</div>

<div class="modal fade" id="Failure" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
 <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title" id="myModalLabel">Failure:</h3>
      </div>
      <div class="modal-body">
     	 <h3 id = "failedMsg" class = "text-danger"> Woops!  Something went wrong! </h3>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
 </div>
</div>

<div class="modal fade" id="Contact" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title" id="myModalLabel">Leave a Message:</h3>
      </div>
      <div class="modal-body">
      	<div class = "row no-padding" style="background-color:rgba(255,255,255,.4);">
			
			<div class = "col-xs-12" style="margin-top:10px;">
				<label for="footer_name">Name:</label>
				<input  class = "form-control form-control-inline" id = "footer_name" type="text" placeholder= "Your Name" >
				
				</input>
			</div>
			<div class = "col-xs-12" style="margin-top:10px;">
				<label for="footer_email">Email:</label>
				<input class = "form-control form-control-inline" id = "footer_email" type="text" placeholder = "email@abc.com" />
			</div>
			<div class = "col-xs-12" style="margin-top:10px;">
				<label for="footer_reason">Contact Reason:</label>
				<select class ="form-control" id="footer_reason">
				  <option value="ORDER">Problem with an Order</option>
				  <option value="BILLING">Billing/Receipts</option>
				  <option value="TECH">Technical Issue</option>
				  <option value="FEEDBACK" selected = "selected">Website Feedback</option>
				  <option value="FRAUD">Report Fraud</option>
				  <option value="PRIVACY">Our Privacy Policy</option>
				  <option value="PARTNER">Partnership Inquiry </option>
				  <option value="OTHER">Other </option>
				</select>
			</div>
			<div class = "col-xs-12" style="margin-top:10px;">
				<label for="footer_message">Message:</label>
				<textarea class ="form-control" rows="4" id="footer_message"></textarea>
			</div>			
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" class="btn-primary btn-block" style="height:34px;width:18%" data-dismiss="modal">back</button>
        <button type="button" onclick="sendEmail();" class="btn btn-primary btn-block" style="height:34px;width:18%;float:right;">Submit</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>
