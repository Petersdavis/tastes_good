<?php include 'boilerplate.php'; ?>

<?php html_Header("Welcome");
srcJavascript("scripts/login.js");
?>

<div class="container main">

	<form id="login" name="login" method="post" autocomplete="off"> 
		 <h1>Log in</h1> 
		 <p> 
			  <label for="username" data-icon="u" >Username:</label>
			  <input id="username" name="username" required="required" type="text" placeholder="Username"/>
		 </p>
		 <p> 
			  <label for="password" data-icon="p">Password:</label>
			  <input id="password" name="password" required="required" type="password" /> 
		 </p>
	<?php
	   if($gattempt > 500){
		  echo '<input id=block required="required" disabled ="disabled" class="hidden">';
		  echo '<h2>too many log-in attempts [..] restart your browser to continue </h2></input>';  	  
	  }
	
	?>
		 
		 <div class="login button"> 
			  <input type="button" onclick = "SubmitForm()" value="Login" /> 
		 </div>
	</form>
	
</div>
  