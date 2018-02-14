var hostname = location.hostname;
function warn_on_exit(){
	window.onbeforeunload = function() {
  		return 'You have not yet saved your work.Do you want to continue? Doing so, may cause loss of your work' ;
	}
	
}
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


$(function(){ 
	 $("#rest_logo").change(function(){
        readURL(this);
    });	
     
     PlaceHolder();
	//set timepickers	
	
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
	
	$('.timepicker').change(function(){
			$('#save_schedule').removeClass("btn-default");
			$('#save_schedule').addClass("btn-warning");
			warn_on_exit();
	});
	
	
	$('[name="rest_details"]').change(function(){
		$('#save_details').removeClass("btn-default");
		$('#save_details').addClass("btn-warning");	
		warn_on_exit();
	});
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
	
	$('[name="delivery_details"]').change(function(){
		$('#save_delivery').removeClass("btn-default");
		$('#save_delivery').addClass("btn-warning");	
		warn_on_exit();
	});
	//init submit buttons
	
	$('#save_schedule').click(function(){
		var formData = new FormData ();
		var schedule = {"monday_open":$('#monday_open').val(),"tuesday_open":$('#tuesday_open').val(),"wednesday_open":$('#wednesday_open').val(),"thursday_open":$('#thursday_open').val(),"friday_open":$('#friday_open').val(),"saturday_open":$('#saturday_open').val(),"sunday_open":$('#sunday_open').val(), "monday_close":$('#monday_close').val(),"tuesday_close":$('#tuesday_close').val(),"wednesday_close":$('#wednesday_close').val(),"thursday_close":$('#thursday_close').val(),"friday_close":$('#friday_close').val(),"saturday_close":$('#saturday_close').val(),"sunday_close":$('#sunday_close').val()}
		formData.append ('rest_id', Restaurant.rest_id);
		formData.append ('schedule', JSON.stringify(schedule));
		
		
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);
			$('#save_schedule').removeClass("btn-warning");
			$('#save_schedule').addClass("btn-default");
			$('#lg_details').data('checked', true);
			
			var checklist = JSON.parse(localStorage.getItem("TGchecklist"));
			checklist.details = 1;
			localStorage.setItem("TGchecklist", JSON.stringify(checklist));
			window.onbeforeunload = null;
			
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/edit_schedule.php', true);
		xhr.send(formData);	
	});
	
	$('#save_details').click(function(){
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
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			$('#save_details').addClass("btn-default");
			$('#save_details').removeClass("btn-warning");		
			
			var checklist = JSON.parse(localStorage.getItem("TGchecklist"));
			checklist.details = 1;
			localStorage.setItem("TGchecklist", JSON.stringify(checklist));
			window.onbeforeunload = null;
			
			console.log(xhr.response);				
			
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/edit_restaurant.php', true);
		xhr.send(formData);		
	})
	
	
	
	$('#save_delivery').click(function(){
		var formData = new FormData ();
		
		var offers_delivery = $('#offers_delivery').val();
		var delivery_base = $('#delivery_base').val();
		delivery_base = /[\d]+[.]{0,1}[\d]{0,2}/.exec(delivery_base)[0];
			
		
		var delivery_email  = $('#delivery_email').val();
		 	 
		formData.append ('offers_delivery', offers_delivery);
		formData.append ('delivery_base', delivery_base);
		
		formData.append ('delivery_email', delivery_email);
		formData.append ('rest_id', Restaurant.rest_id);
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);
			$('#save_delivery').addClass("btn-default");
			$('#save_delivery').removeClass("btn-warning");		
			
			var checklist = JSON.parse(localStorage.getItem("TGchecklist"));
			checklist.details = 1;
			localStorage.setItem("TGchecklist", JSON.stringify(checklist));
			
			console.log(xhr.response);				
			window.onbeforeunload = null;
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/edit_delivery.php', true);
		xhr.send(formData);		
	})
	
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
	
});
