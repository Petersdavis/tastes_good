document.addEventListener("DOMContentLoaded", function(event) {
if(typeof(user)=="object"){
	$('#name').val(user.user_name);
	$('#email').val(user.email);
	$('#order_id').val(user.order_id);
}


$('#submit_comment').click(function(){
	var formData = new FormData ();
		
		formData.append ('name', $('#name').val());
		formData.append ('email', $('#email').val());
		formData.append ('order_id', $('#order_id').val());
		formData.append ('comment', $('#message').val());
		formData.append ('reason', $('#reason').val());
		
						
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			
			
			if (xhr.responseText=="success"){
				
				$('#Success').modal('show')
				
			}else{
				alert("SERVER PROBLEMS: Sorry!  We could not process your message.  Please try again later.")				
				console.log(xhr.response);
			}
			
	
		};
	
	
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname +  '/uploader/submitComment.php', true);
		xhr.send(formData);	
		
});

		
		
})