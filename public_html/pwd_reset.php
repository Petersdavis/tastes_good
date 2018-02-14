<?php include 'boilerplate.php'; 
checkProduction();
include 'dbconnect.php';
html_Header("Password Reset");
include 'header.php';

$key=getattribute('key');
$user_id=getattribute('user_id');

?>
<script>
user_id = <?php echo $user_id; ?>;
key = "<?php echo $key; ?>";
</script>	

<div class = "container main" style="background-color:rgb(172, 232, 213); padding:0;width:90%;min-width:300px;max-width:1050px;">

	<div class="text-center" style="border-bottom:none;background-color:rgb(172, 232, 213);padding-top:2px;">
		<h2 style = "margin:0;padding:10px; background-color:rgba(245, 240, 250, 0.6);">Reset Your Password</h2>
	</div>
	
	<form id="login" name="login" class = "form-inline text-center"  style="border-radius:6px; padding:10px;margin-left:10%;margin-right:10%;margin-bottom:15px;" > 
		  <div class = "form-group">
			  <label for="password" data-icon="u" >Password:</label>
			  <input class = "form-control" id="password" name="password" type="password"/>
		  </div>
		  <div class = "form-group">
			  <label for="confirm" style="padding-left:10px;" data-icon="p">Confirm:</label>
			  <input id="confirm" class = "btn form-control" name="confirm" required="required" type="password" /> 
		  </div>
		  
		  <input type="button" class= "btn btn-default" onclick = "pwdReset()" value="Submit" /> <br>
		  
		  
		  <h3 id = "failedLogin" class = "text-danger" style = "display:none;"> Password Mismatch! </h3>
		
	</form>
	
	
</div>

<script>

function pwdReset(){
	var email = $('#email_recovery').val();
		if($('#password').val() == $('#confirm').val()){
		//verify that email does not already exist in local storage.  
		
		var formData = new FormData ();
		formData.append ('password', $('#password').val());
		formData.append ('key', key);	
		formData.append ('user_id', user_id);	
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);
			var response = JSON.parse(xhr.response);
			if(response.result == "NO_KEY"){
				alert("Invalid Key--Or Key did not match User Id");
			}else if(response.result == "KEY_EXPIRED"){
				alert("Key Expired");
			
			}else if(response.result=="success"){
				alert("Password Reset!");
				window.location="/"
			}
				
		};
	
	
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname + '/uploader/email_reset.php', true);
		xhr.send(formData);
		
	}else{
		alert ("passwords don't match!");
		
	}

	
	
}

</script>



<?php include "footer.php"; ?>