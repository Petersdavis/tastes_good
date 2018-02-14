var hostname = location.hostname



function sendEmail (){
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
				
				$('#Contact').modal('hide').delay(250);
				$('#Success').delay(250).modal('show')
				
			}else{
				var failedMsg = "";
				failedMsg = document.getElementById("failedMsg");
				failedMsg.style.display = "inline-block";
				
				
				var pre = document.createElement('pre');
				pre.innerHTML = xhr.response;
				failedMsg.appendChild(pre);
			}
			
	
		};
	
	
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname +  '/uploader/submitComment.php', true);
		xhr.send(formData);
		
	
}	

function addAddress(address, target){
	var fullAddress = ""
	var location = {"lat":address.lat, "lng":address.lng};
	
	
	
	if(address.appt !== "na"){
		fullAddress = address.appt + "-"+ address.address;
	}else{fullAddress = address.address}
	
   var div = $('<div class="list-group-item row"></div>').attr('id', 'address_'+address.id );
   var address_div = $('<div class="col-xs-10"><label for="address_radio_'+address.id+'"><input type="radio" name="Order_Address" id= "address_radio_'+address.id+'" value="'+address.id+'"/>'+fullAddress+'</label></div>')

   var delete_button = $('<button class="col-xs-2 btn btn-xs" ><span class="glyphicon glyphicon-trash" aria-hidden="true" ></span></button>').attr('id', 'delete_address_'+address.id );
   $(div).append(address_div).append(delete_button)
   
   if (address.comment){
   	   var details = $('<div class = "col-xs-12">'+address.comment+'</div>');
   	   $(div).append(details)
   }
   
   if(pagename == "order.php"){
   	   var distance = calcDistance(location, geoBounds);
		distance = Math.round(10*distance)/10
		
		
		var delivery = 4 + (Math.floor(distance/0.25) * 0.2);
		
		
		for(y=0;y<user.addresses.length;++y){
			   if(user.addresses[y].id ==  address.id){
			   user.addresses[y].distance = distance
			   user.addresses[y].delivery = delivery
			   }
		}
	
		delivery = validatePrice(delivery, delivery);
	
   	   
	   var distance = $('<div class = "col-xs-12">Distance to Restaurant: '+distance+' km Delivery Charge: '+delivery+'</div>');
	   $(div).append(distance);
   }
   
   $(target).append(div);
   
   
  
  
   $('#delete_address_'+address.id).on("click", {'address_id':address.id}, function(e){
	   e.stopPropagation();
   	  
	   for(y=0;y<user.addresses.length;++y){
	   	   if(user.addresses[y].id ==  e.data.address_id){
	   	   user.addresses.splice(y, 1); 
	   	   }
	   }
	   
	   var formData = new FormData ();
	   formData.append ('address_id', e.data.address_id);
	   
	   var xhr = new XMLHttpRequest();
	   xhr.onload = function() {
	   	   		
				
			  };  
	  
		
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/deleteAddress.php', true);
		xhr.send(formData);	
		
		
		$('#address_'+e.data.address_id).remove();
   })
   
 
     
  
} 	

function geocodeAddress(geocoder) {
 	address = document.getElementById('order_newAddress').value;
 	geocoder.geocode({'address': address,  componentRestrictions: {country: 'Canada'}}, function(results, status) {        		
        		
          if (status === 'OK') {
          	  var x, y, distance, min_distance, formatted_address, postcode, address_lat, address_lng, comments, type, appt, buzz;
          	  min_distance = 50;
          	  y = -1;
          	  
          	  for(x=0; x<results.length;++x){
          	  	  var location = {}
          	  	  location.lat=results[x].geometry.location.lat();
          	  	  location.lng=results[x].geometry.location.lng();
          	  	     distance = calcDistance(location, geoBounds)
          	  	  if (distance< min_distance){
          	  	  	  min_distance = distance
          	  	  	  y = x;
          	  	  }
          	  	}
          	  	  
          	  
          	  if (y!=-1){
          	  
          	  formatted_address = results[y].formatted_address;
          	  for(x=0;x<results[y].address_components.length;++x){
				  if(results[y].address_components[x].types[0]="postal_code"){
					  postcode = results[y].address_components[x].long_name;
				  }
          	  }
          	  address_lat = results[y].geometry.location.lat();
          	  address_lng = results[y].geometry.location.lng();
          	  
          	  type = $('input[name=addressType]:checked').val()
          	  if (type == "appt"){
          	  	  appt = $('#apptNumber').val();
          	  	  buzz = $('#apptBuzz').val()
          	  }else{
          	  	 appt = "na";
          	  	 buzz ="na";
          	 }
          	 
          	 var comments = $('#addressComments').val();
          	  	  
          	  	  
          	  
          	 currentAddress = {'formatted_address':formatted_address, 'postcode':postcode, 'type':type,'appt':appt,'buzz':buzz, 'comments':comments, 'lat':address_lat, 'lng':address_lng}
          	
          	  	
			
			          	
		var formData = new FormData ();
				
		formData.append ('address', JSON.stringify(currentAddress));
		formData.append ('user', user.user_id);
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
				 
				console.log(xhr.response);
				var result = JSON.parse(xhr.response);
				 if(result.result == "success"){
					 					
					 user.addresses = result.data
					 
					 $('#order_AddressList').empty();
					 					  
					 for(x=0;x<user.addresses.length;++x){
					 	 addAddress(user.addresses[x], $('#order_AddressList'));
					 }
					
					 					 
					$('#address_radio_'+user.addresses[0].id).prop("checked", true);
					$('#order_AddressList').show();
					$('#newAddress').show();
					$('#saveAddress').hide();
					$('#newAddress_Cancel').hide();
					$('#delivery_address_new').hide();
					 
				 }
				 
				  
				 };  
          	
		// Open the connection.
		xhr.open('POST', 'https://'+ hostname + '/uploader/saveAddress.php', true);
		xhr.send(formData);	
			
          } else {
            alert('We could not find an address within this community.  Please provide more address information or try a different address.');
          }
         }else {
         	 alert('Whoops, our address lookup failed: ' + status);
         }
	});
}

document.addEventListener("DOMContentLoaded", function(event) {
		
	if(typeof(user)=="object" &&  user.user_id > 0){
		$('#footer_name').val(user.fname + " " +user.lname)
		$('#footer_email').val(user.email)
		$('#dropdown_login').hide();
	}	else{
		$('#dropdown_logout').hide();
	}
		
	$('#dropdown_btn').click(function(){
		$('#navbar_dropdown').fadeToggle();	
	})
	$('#dropdown_login').click(function(){
		$('#userLogin').modal('show');
		$('#navbar_dropdown').fadeToggle();	
	});
	
	$('#dropdown_logout').click(function(){
		var formData = new FormData ();
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			$('#navbar_dropdown').fadeToggle();	
			user={user_id:0};
			$('#dropdown_logout').hide();
			$('#dropdown_login').show();
			
			if ($('#user_list').length > 0){
				$('#user_list').empty();
			}
			
			
		}
	
		xhr.open('POST', 'https://'+ hostname + '/uploader/user_logout.php', true);
		xhr.send(formData);	
		
			
	});
	
	$('#newAddress_Cancel').click(function(){
					$('#newAddress_Cancel').hide();
					$('#saveAddress').hide();
					$('#newAddress').show();
					$('#delivery_address_new').hide();
			});
	
	$('#newAddress').click(function(){
				$('#delivery_address_new').show();	
				$('#newAddress').hide();
				$('#saveAddress').show();
				$('#newAddress_Cancel').show();
				$('#appt_details').hide();
			});
	
	$('[name = "dwellingType"]').change(function(e){
			var dwelling = $('[name="dwellingType"]:checked').val();
			if (dwelling == "house"){
				$('#newUserApptDetails').hide();
			}else{
				$('#newUserApptDetails').show();
			}
			
	});
	$('[name = "legal"]').change(function(e){
		if($('#read_terms').prop("checked")	&& $('#read_privacy').prop("checked")	){
			$('#submit_newUser').prop("disabled", false).removeClass("disabled");
		} else {
			$('#submit_newUser').prop("disabled", true).addClass("disabled");
		}
			
	});
			
	//intialize errors
	$('#invalid_email').hide();
	$('#email_noexist').hide();
	$('#short_password').hide();	
	$('#email_sent').hide();
	
	//initialize modals
	$('#userNew').hide();
	$('#forgotPwd').hide();
	
	//intialize buttons

	$('#user_LoginMenu').click(function() {
		$('#userLogin').modal('show');	
	});	
	
	$('#user_NewMenu').click(function() {
		$('#userNew').modal('show');	
	});
	$('#forgot_pwd_Cancel').click(function() {
		$('#forgotPwd').hide();	
		$('#existing_user_btn').show(250);;	
	});
	
	$('#new_User_Cancel').click(function() {
		$('#userNew').hide();	
		$('#existing_user_btn').show(250);;	
	});
	
	$('#forget_pwd').click(function() {
		$('#forgotPwd').show(500);
		$('#userNew').hide();	
		$('#existing_user_btn').hide();
		
	});
	
	$('#new_user').click(function() {
		$('#userNew').show(500);	
		$('#forgotPwd').hide();
		$('#existing_user_btn').hide();
	});
	
	$('#newUserEmail').focusout(function() {
		$('#submit_newUser').text("Validating...").prop('disabled', true);
		$('#invalid_email').hide();
		var formData = new FormData ();
				
		formData.append ('email', $('#newUserEmail').val());
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			
				
			if(xhr.response == "EMAIL_OK"){
				$('#submit_newUser').text("Submit").prop('disabled', false);
				$('#invalid_email').hide();
				
			}else if(xhr.response == "EMAIL_EXISTS"){
				$('#submit_newUser').text("Submit")
				$('#invalid_email').show();
				}
				
			
		};
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname + '/uploader/emailCheck.php', true);
		xhr.send(formData);	
	});
	
	$('#submit_new_address').click(function(){
			
			
	});
	
	$('#submit_login').click(function() {
		var email = $('#userId').val();
		
		//verify that email does not already exist in local storage.  
		
		var password = $('#userPwd').val();
		var userLogin = {'email':email, 'pwd':password};	
		var userLogin_json = JSON.stringify(userLogin);	
		var formData = new FormData ();
				
		formData.append ('userLogin', userLogin_json);
		
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);
			var response = JSON.parse(xhr.response);
			var user_creds = {};
			if(response.result == "success"){
				user = response.data;
				//hide modal and
				$('#userLogin').modal('hide');
				//update menu's
				
				$('#dropdown_login').hide();
				$('#dropdown_logout').show();
				
				
				//page specific functions
				if($('#rest_list').length>0 && user.addresses.length>0){
					user_sortList(user);
				}
				
				if ($('#user_list').length > 0){
					addUser(user.user_id, user.fname+ " " +user.lname, true);
					
				}
  				 
				
					
			
			
			} else if (response.error == "pwd_wrong"){
				alert ("wrong password");
			} else if (response.error == "no_user"){
				alert ("user id not found");
			}	    
				
		};
	
	
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname + '/uploader/userLogin.php', true);
		xhr.send(formData);
		
			
	});
	
	$('#submit_forgot_pwd').click(function() {
		var email = $('#email_recovery').val();
		
		//verify that email does not already exist in local storage.  
		
		var formData = new FormData ();
				
		formData.append ('email', email);
		
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);
			var response = JSON.parse(xhr.response);
			if(response.result == "NO_EMAIL"){
				$('#email_noexist').show();
				$('#email_sent').hide();
				$('#submit_forgot_pwd').disable();
			}else if(response.result == "EMAIL_SENT"){
				$('#email_sent').show();
				$('#email_noexist').hide();
				$('#submit_forgot_pwd').disable();
			}
			
				
				
				
		};
	
	
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname + '/uploader/email_recovery.php', true);
		xhr.send(formData);
		
			
	});
	
	$('#submit_newUser').click(function() {
		//Validate Form
			
			//email address
			var validForm = 1;
			var fname = $('#newUserFName').val();
			var lname = $('#newUserLName').val();
			var email = $('#newUserEmail').val();
			var telephone = $('#newUserPhone').val();
			
			var password = $('#newUserPwd').val();
			var confirm = $('#newUserPwdConfirm').val();
			
			
			var pattern = /@/
			
			if(!pattern.test(email)){
				alert("invalid Email address");
				validForm = 0;
						
			}
						
							
			//telephone number
			
			pattern =  /[0-9]{3}[\D]*[0-9]{3}[\D]*[0-9]{4}/
			
			if (!pattern.test(telephone)){
				alert ("Please enter 10 digit phone number.");
				validForm = 0;
			}
						
			//password
			
			if (password !== confirm){
				alert ('password mismatch')
				validForm = 0;
			}
			
			pattern = /.{6,32}/
			if(!pattern.test(password)){
				$('short_password').show();
				validForm = 0;
			} else {
				$('short_password').hide();
			}
			
			if(validForm==0){
				return;
			} else {
		//construct the user json
			$('#userLogin').modal('hide');
						
		//create a temporary user
		var newUser = {'userDetails': {
							'fname':fname, 
							'lname':lname, 
							'email':email, 
							'phone':telephone, 
							'password':password}, 
					};
		
		var newUser_json = JSON.stringify(newUser);	
				
		var formData = new FormData ();
				
		formData.append ('newUser', newUser_json);
		
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);
			
			var response = JSON.parse(xhr.response);
			
			var user_creds = {};
			
			if(response.result == "success"){
				user = response.data;
			
			
				if ($('#user_list').length > 0){
					addUser(user.user_id, user.fname+ " " +user.lname, true);
				}
			} 			
	
		};
	
		// Open the connection.
		xhr.open('POST', 'https://'+ location.hostname + '/uploader/newUser.php', true);
		xhr.send(formData);
		
		}
		
	});
});	