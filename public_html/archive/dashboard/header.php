<?php   ?>

<div class = "container container-fluid navbar-fixed-top" style="width:100%;background-color:rgb(172, 232, 213);padding-top:5px;padding-bottom:5px;" >
   <div class="navbar-inner">
		<div class="nav navbar-header">
			<a href="/"> <img class="image" src ="../images/logo/tg.png" style="z-index:10001"> </image></a>
		</div>
		<button class ="btn hidden-xs" onclick="$('#Contact').modal('show');" id="top_contact_us" style="background-color:rgba(0,0,0,0);color;z-index:10000;position:absolute;top:0px;right:80px;">
			<img src="contact_us.png" class="image" style="max-width:300px;"  />	
		</button>
		<button class ="btn" id="dropdown_btn" style="background-color:rgba(0,0,0,0);color:rgba(60,38,38,100);position:absolute;top:10px;right:25px;"><span class="glyphicon glyphicon-align-justify" style="font-size:30px;" aria-hidden="true"></span></button>
		
	<div id="navbar_dropdown" class="container main col-xs-12" hidden style="background-color: rgba(240,240,240,.9);background-clip:padding-box; border-color: rgba(0, 0, 0, 0.2);box-shaddow:0 3px 8px rgba(0, 0, 0, .3); border-style:solid; border-width:0.8px; border-radius:6px; width:20%;right:0;min-width:100px;position:absolute;top:70px; z-index:5000;padding:12px;">
		<button class ="btn visible-xs btn-default btn-block" onclick="$('#Contact').modal('show');" id="dropdown_contact_us"> Contact Us </button>
		<?php if ($file !== "new_user"){  ?>
		<a class="btn btn-default btn-block" <?php echo 'href="../order.php?rest_id='. $rest_id .'" '; ?>  >My Page</a>	
		<?php } ?>
		
		<button id="dropdown_logout" class="btn btn-default btn-block">Sign Out</button>
		<?php if ($file !== "new_user"){  ?>
		<div class ="visible-xs">
		
		<hr style="border:0px; margin:5px;height:0px;border-top:0.8px solid darkgrey;">
		<a class="list-group-item" style = "padding:3px;font-size:12px;text-align:center;" href="index.php">Dashboard:</a>
		<a class="list-group-item" style = "padding:3px;font-size:12px;text-align:center;"  href="restaurant.php">Business:</a>
		<a class="list-group-item" style = "padding:3px;font-size:12px;text-align:center;"  href="orders.php">Accounts:</a>
		<a class="list-group-item" style = "padding:3px;font-size:12px;text-align:center;"  href="community.php">Community:</a>
		<a class="list-group-item" style = "padding:3px;font-size:12px;text-align:center;"  href="editMenu.php">Edit Menu: </a>
		<a class="list-group-item" style = "padding:3px;font-size:12px;text-align:center;"  href="coupons.php">Coupons:</a>
		<a class="list-group-item" style = "padding:3px;font-size:12px;text-align:center;"  href="printer.php">Printer:</a>
		
		
		</div>
		<?php } ?>
		
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
				<input  class = "form-control form-control-inline" id = "footer_name" type="text" value = <?php echo '"'. $rest->fname . ' '. $rest->lname . '"'; ?> placeholder= "Your Name" >
				
				</input>
			</div>
			<div class = "col-xs-12" style="margin-top:10px;">
				<label for="footer_email">Email:</label>
				<input class = "form-control form-control-inline" id = "footer_email" type="text" value = <?php echo '"'.$rest->email. '"';  ?> placeholder = "email@abc.com" />
			</div>
			<div class = "col-xs-12" style="margin-top:10px;">
				<label for="footer_reason">Contact Reason:</label>
				<select class ="form-control" id="footer_reason">
				  <option value="ORDER">Processing an Order</option>
				  <option value="BILLING">Account Payouts</option>
				  <option value="MENU">Menu Editor</option>
				  <option value="TECH">Technical Issues</option>
				  <option value="FEEDBACK" selected = "selected">Website Feedback</option>
				  <option value="FRAUD">Report Fraud</option>
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



<script>
function sendEmail(){
		var formData = new FormData ();
		
		formData.append ('name', $('#footer_name').val());
		formData.append ('email', $('#footer_email').val());
		formData.append ('comment', $('#footer_message').val());
		formData.append ('reason', $('#footer_reason').val());
		
		if(window.rest_id == window.rest_id){
			formData.append ('rest_id', window.rest_id);
		}
				
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.responseText);
			
			if (xhr.responseText=="success"){
				
				$('#Contact').modal('hide');
				$('#Success').delay(250).modal('show')
				
			}else{
				$('#Contact').modal('hide').delay(250);
			}
			
	
		};
	
	
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname +  '/uploader/submitComment.php', true);
		xhr.send(formData);
}	


$(function() {
  	$('#dropdown_btn').click(function(){
		$('#navbar_dropdown').fadeToggle();	
	})
	
	$('#dropdown_logout').click(function(){
		var formData = new FormData ();
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			 window.location= "/";
			
			
		}
	
		xhr.open('POST', 'https://'+ hostname + '/uploader/user_logout.php', true);
		xhr.send(formData);	
		
			
	});
	
	
	
});
	

</script>
