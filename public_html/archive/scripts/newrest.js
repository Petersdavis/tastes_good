var hostname = location.hostname

function readURL(input) {
	if (input.files && input.files[0]) {
		
		if(input.files[0].size < 2500000){
		 $('#file_size_error').hide();		
		var reader = new FileReader();
		reader.onload = function (e) {
			$('#image_preview').attr('src', e.target.result);
		}		
		reader.readAsDataURL(input.files[0]);
		$('#save_details').prop("disabled", false);
		
		}else{
		 $('#file_size_error').show();	
		 $('#save_details').prop("disabled", true);	
		}
	}
}




function saveDetails(){
var formData = new FormData ();
		
		
		telephone = $('#rest_phone').val()
		pattern =  /[0-9]{0,1}[\D]*[0-9]{3}[\D]*[0-9]{3}[\D]*[0-9]{4}/
		
		if (!pattern.test(telephone)){
			alert ("invalid phone number: please enter 10 or 11 digit number");
			return;
		}
		
		
		var details = {"title":$('#rest_title').val(), "first_name":$('#rest_fname').val(), "last_name":$('#rest_lname').val(), "type":$('#rest_type').val(), "address":$('#rest_address').val(),  "phone":$('#rest_phone').val(), "email":$('#rest_email').val()}
		 if($("#rest_logo")[0].files.length > 0 && $("#rest_logo")[0].files[0].size < 50000){
		 	 formData.append('rest_logo',$("#rest_logo")[0].files[0], $("#rest_logo")[0].files[0].name)
		 }
		 
		 
		
		formData.append ('rest_id', Restaurant.rest_id);
		formData.append ('details', JSON.stringify(details));
		
		var offers_delivery = $('#offers_delivery').val();
		var delivery_base = $('#delivery_base').val();
		delivery_base = /[\d]+[.]{0,1}[\d]{0,2}/.exec(delivery_base)[0];
		
		formData.append ('offers_delivery', offers_delivery);
		formData.append ('delivery_base', delivery_base);
		
		var xhr = new XMLHttpRequest();
				
		//validate form data. 
		
		xhr.onload = function() {
			console.log(xhr.response);	
			var ret = JSON.parse(xhr.response);
			if(ret.success){
				window.location = "index.php?new_rest=1";
			}
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/new_rest_details.php', true);
		xhr.send(formData);	
		
		
		
		

}


function submitChoice(choice){
	var formData = new FormData ();
	var status = {"status":choice};
	
	formData.append ('status', JSON.stringify(status));
									
			var xhr = new XMLHttpRequest();
			
			xhr.onload = function() {
				console.log(xhr.response);				
				
			};
			// Open the connection.
			xhr.open('POST', 'https://'+ hostname + '/uploader/rest_status.php', true);
			xhr.send(formData);	
	
	
}

function submitData(email, phone){
	var formData = new FormData ();
	var contact_details = {"email":email, "phone":phone};
	
	formData.append ('contact', JSON.stringify(contact_details));
									
			var xhr = new XMLHttpRequest();
			
			xhr.onload = function() {
				console.log(xhr.response);				
				
			};
			// Open the connection.
			xhr.open('POST', 'https://'+ hostname + '/uploader/newRest.php', true);
			xhr.send(formData);	
	
	
}

$(function(){ 
	 $("#rest_logo").change(function(){
	        readURL(this);
	    });	
	
	$('#monday_open').wickedpicker({now:Restaurant.schedule.monday_open, minutesInterval: 15,twentyFour: true});	
	$('#monday_close').wickedpicker({now:Restaurant.schedule.monday_close, minutesInterval: 15,twentyFour: true});
	$('#tuesday_open').wickedpicker({now:Restaurant.schedule.tuesday_open, minutesInterval: 15,twentyFour: true});	
	$('#tuesday_close').wickedpicker({now:Restaurant.schedule.tuesday_close, minutesInterval: 15,twentyFour: true});
	$('#wednesday_open').wickedpicker({now:Restaurant.schedule.wednesday_open, minutesInterval: 15,twentyFour: true});	
	$('#wednesday_close').wickedpicker({now:Restaurant.schedule.wednesday_close, minutesInterval: 15,twentyFour: true});
	$('#thursday_open').wickedpicker({now:Restaurant.schedule.thursday_open, minutesInterval: 15,twentyFour: true});	
	$('#thursday_close').wickedpicker({now:Restaurant.schedule.thursday_close, minutesInterval: 15,twentyFour: true});
	$('#friday_open').wickedpicker({now:Restaurant.schedule.friday_open, minutesInterval: 15,twentyFour: true});	
	$('#friday_close').wickedpicker({now:Restaurant.schedule.friday_close, minutesInterval: 15,twentyFour: true});
	$('#saturday_open').wickedpicker({now:Restaurant.schedule.saturday_open, minutesInterval: 15,twentyFour: true});	
	$('#saturday_close').wickedpicker({now:Restaurant.schedule.saturday_close, minutesInterval: 15,twentyFour: true});
	$('#sunday_open').wickedpicker({now:Restaurant.schedule.sunday_open, minutesInterval: 15,twentyFour: true});	
	$('#sunday_close').wickedpicker({now:Restaurant.schedule.sunday_close, minutesInterval: 15,twentyFour: true});
	
	$('#offers_delivery').change(function(){
		if($('#offers_delivery').is(':checked')){
			$('#delivery_base').prop('disabled', false);
			$('#delivery_variable').prop('disabled', false);
			$('#delivery_email').prop('disabled', false);
		}else{
			$('#delivery_base').prop('disabled', true);
			$('#delivery_variable').prop('disabled', true);
			$('#delivery_email').prop('disabled', true);
			
		}		
	});
	
	$('#save_password').click(function(){
		
		
		if($('#new_pwd').val()==$('#confirm_pwd').val()){	
		var formData = new FormData ();
		password = {"old_pwd":$('#old_pwd').val(), "new_pwd":$('#new_pwd').val()};
		password = JSON.stringify(password);
		
		formData.append ('password', password);
		formData.append ('rest_id', Restaurant.rest_id);
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);				
			
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/change_password.php', true);
		xhr.send(formData);	
		
		}else{ alert("passwords don't match!");}	
	
	});
	
	
	
	$('#vid_menu').click(function(){
		$('#Vid_Demo').modal('show');
		$('#demo_title').text("Menu Builder Demonstration:");
		$('#demo_vid').attr("src","https://www.youtube.com/embed/7HQY4mVwurg");
	
	});
	$('#vid_user').click(function(){
		$('#Vid_Demo').modal('show');
		$('#demo_title').text("User Experience Demonstration:");
		$('#demo_vid').attr("src","https://www.youtube.com/embed/puR0OuaqCBo");
	
	});
	$('#vid_account').click(function(){
		$('#Vid_Demo').modal('show');
		$('#demo_title').text("Account Summary Demonstration:");
		$('#demo_vid').attr("src","https://www.youtube.com/embed/nDR8viX9rvA");
	
	});
	$('#vid_coupons').click(function(){
		$('#Vid_Demo').modal('show');
		$('#demo_title').text("Coupons Demonstration:");
		$('#demo_vid').attr("src","https://www.youtube.com/embed/wqBD817F8EA");
	
	});
	
	$('#Vid_Demo').on('hidden.bs.modal', function () {
		$('#demo_vid').attr("src","");
	});
	
	if(showterms){
	$('#terms_conditions').show(); 
	$('#contact_details').hide();	
	}
	
	
	$('[name="consent"]').change(function(){
		if($('#privacy_consent').prop("checked") && $('#terms_consent').prop("checked"))
		{
			$('#consent_continue').prop("disabled", false).removeClass("disabled");
		} else {
			$('#consent_continue').prop("disabled", true).addClass("disabled");
		}
			
	});
		
	$('#left_nav').find("a").addClass("disabled").bind("click", function (e) {
		e.preventDefault();
   	});
			
	$('#save_details').click(function(){
		var validForm = 1;
		//email address	
		var pattern = /@/
			
		if(!pattern.test($('#email').val())){
			alert("invalid Email address");
			validForm = 0;
					
		}
		
		pattern =  /[0-9]{3}[\D]*[0-9]{3}[\D]*[0-9]{4}/
		
		if (!pattern.test($('#phone').val())){
			alert ("invalid phone");
			validForm = 0;
		}
		if(validForm == 1){
			submitData( $('#email').val(), $('#phone').val());
			$('#contact_details').hide(); 
			$('#sales_pitch').show();
			
		}

		
	});
	$('#btn_continue').click(function(){
		$('#sales_pitch').slideUp();
		$('#rest_details').show();
			
	});
	$('#save_details').click(function(){
		$('#rest_details').slideUp();
		$('#terms_conditions').show();
			
	});
	
	
	$('#consent_continue').click(function(){
		//change state -> Registration
		submitChoice("TESTING");
		
		//save details
		saveDetails();
		
		
		//create the checklist object
		checklist = {"login":1,"details":1,"menu":0, "print":0, "coupon":0, "launch":0}
		window.localStorage.setItem("TGchecklist", JSON.stringify(checklist));
		
		
	});
	
	$('#btn_decline').click(function(){
		submitChoice("DECLINED");
		$('#sales_pitch').hide();
		$('#exit').show();
		
	});		
});