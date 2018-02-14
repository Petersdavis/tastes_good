/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
 
 

 
$(document).ready(function () {
		
	//if protocol http:// set to https://
    if (document.URL.indexOf('http://') === 0 ) {
      location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
    }
	
    
    // are we running in native app or in a browser?
    window.isphone = false;
       
    if (document.URL.indexOf('http://') === -1 && document.URL.indexOf('https://') === -1) {
        window.isphone = true;
    }

    if (window.isphone) {
    	loadPathsPhone();
        document.addEventListener('deviceready', onDeviceReady, false);
    } else {
    	loadPathsWeb();
        onDeviceReady();
    }
});

function onDeviceReady() {
	//Application INIT.
	
	//Facebook Initialization
	/*
	window.fbAsyncInit = function() {
	  FB.init({
	    appId      : '1890437671201988',
	    cookie     : true,  // enable cookies to allow the server to access 
	                        // the session
	    xfbml      : true,  // parse social plugins on this page
	    version    : 'v2.8' // use graph api version 2.8
	  });
	}
	*/
	BoilerplateInit();
	initGlobalVars();
	localStorageShim();	
    initErrors();
    checkUser();
   	checkState(history.state);
    initButtons();
    initModals();
    initKeystrokes();
    initDateTimePickers();
    setupPush();
}

/*  To do list : 
1. Set up SalesForce.
1) communities
2) restaurants
3) details  --> get phonenumber;
    ==>input email
4) first email. ==> Register.
5) second email ==> Menu.
6) third email ==> Coupons.
7) fourth email ==> Terms of Service  ==> Launch Restaurant.

2. get coupon list
3. apply coupons
4. handle restaurant open//close
5.on restore state config menus
*/	

function loadPathsPhone(){
 paths = {};
 paths.biz_card = "img/biz_card_blank.png"
 

}

function loadPathsWeb(){
 paths = {};
 paths.biz_card = "images/biz_card_blank.png"
}

function checkUser(){
	var callback, payload, target;
	payload = "";
	
	callback = function(response) {
		if(response.result == "success"){
		User = response.data;
		refreshUser();
		checkHash();
		}
	};  
		  
			
	// Open the connection.
	target = 'https://www.tastes-good.com/api_scripts/user.php';
	ajaxRequest(target, payload, callback);
		
}
 
 
function fb_statusChangeCallback(response) {
    console.log('statusChangeCallback');
    console.log(response);
    // The response object is returned with a status field that lets the
    // app know the current login status of the person.
    // Full docs on the response object can be found in the documentation
    // for FB.getLoginStatus().
        
    if (response.status === 'connected') {
    	//should login and create 
    	fb_login();
       console.log('Person is logged in..');
         } 
    else if (response.status === 'not_authorized') {
          
       console.log('Please log into this app.');
    } else {
      console.log('Please log into facebook.')
     
    }
  }
 
 
function goog_login(googleUser){
	var profile = googleUser.getBasicProfile();
	var email = profile.getEmail();
	var fname = profile.getGivenName();
	var lname = profile.getFamilyName();
	var goog_id =  profile.getId();
	
	
	goog_details = {'email':email, 'fname':fname, 'lname':lname, 'goog_id':goog_id};	
	//var id_token = googleUser.getAuthResponse().id_token;
	
	
	var formData = new FormData ();
	formData.append ('user_details',  JSON.stringify(goog_details));
	console.log("ID TOKEN: ".id_token);
	var xhr = new XMLHttpRequest();
	
	xhr.onload = function() {
		console.log(xhr.response);
		if (xhr.response == "USER_EXISTS"){
			$('#main_login').hide();
			$('#existing_user_btn').hide();
			$('#googPwd').show();
			$('#googPwd_submit').click(function(){
			  goog_pwd();
			});
			
		
		}else if (xhr.response == "USER_DNE"){
			$('#main_login').hide();
			$('#existing_user_btn').hide();
			$('#googNew').show();
			$('#googFName').val(goog_details.fname)
			$('#googLName').val(goog_details.lname)
			$('#googPhone').val("");
			$('#googSubmit').click(function(){
			 goog_new();
			});
		
		}	
		
		
		
		
	}
	
	xhr.open('POST', 'https://www.tastes-good.com/uploader/goog_login.php', true);
	xhr.send(formData);

}
  
function googNew(){
	goog_details.fname=$("#googFName").val()
	goog_details.lname=$("#googLName").val()
	goog_details.phone=$("#googPhone").val()
	goog_details.password = $('#googUserPwd').val()
	
	if(goog_details.password != $('#googUserPwdConfirm').val()){
		alert ("Warning: Passwords don't match!");
		return;
		}
		
	if(goog_details.password.length < 5){
		alert ("Warning: Passwords is too short!");
		return;
		}
	
	
	var pattern =  /[0-9]{3}[\D]*[0-9]{3}[\D]*[0-9]{4}/
		
	if (!pattern.test(goog_details.phone)){
		alert ("Please enter a 10 digit phone number.");
		return;
	}
	
	var formData = new FormData ();
	formData.append ('user_details',  goog_details);
	var xhr = new XMLHttpRequest();
	xhr.onload = function() {
		console.log(xhr.response);
		result = JSON.parse(xhr.response);
		
		if(result.result == "success"){
			goog_details = {};
			User = response.data;
			//hide modal and
			$('#userLogin').modal('hide');
			
			$('#dropdown_login').hide();
			$('#dropdown_logout').show();
			$('#dropdown_past_orders').show();
			
			//page specific functions
			if($('#restaurants').length>0 && User.addresses.length>0){
				user_sortList(User);
			}
		}
	}
	xhr.open('POST', 'https://www.tastes-good.com/uploader/goog_new.php', true);
	xhr.send(formData);

}

function googPwd(){
	goog_details.pwd = $('#goog_pwd').val();
	var formData = new FormData ();
	formData.append ('user_details',  goog_details);
	var xhr = new XMLHttpRequest();
	xhr.onload = function() {
		console.log(xhr.response);
		result = JSON.parse(xhr.response);
		
		if(response.result == "success"){
			goog_details = {};
			User = response.data;
			//hide modal and
			$('#userLogin').modal('hide');
			
			$('#dropdown_login').hide();
			$('#dropdown_logout').show();
			$('#dropdown_past_orders').show();
			
			
			//page specific functions
			if($('#restaurants').length>0 && User.addresses.length>0){
				user_sortList(User);
			}
		}
	}
	
	
	xhr.open('POST', 'https://www.tastes-good.com/uploader/goog_pwd.php', true);
	xhr.send(formData);
}
  
function fb_login() {
    //must create session
    FB.getLoginStatus(function(response) {
      if(response.status=="connected"){
      	var formData = new FormData ();
			
	formData.append ('fb_Login', JSON.stringify(response.authResponse));
	
	console.log(JSON.stringify(response.authResponse));
	
	var xhr = new XMLHttpRequest();
	
	xhr.onload = function() {
		console.log(xhr.response);
		var response = JSON.parse(xhr.response);
		if(response.result == "success"){
			User = response.data;
			//hide modal and
			$('#userLogin').modal('hide');
			
			$('#dropdown_login').hide();
			$('#dropdown_logout').show();
			$('#dropdown_past_orders').show();
			
			
			//page specific functions
			if($('#restaurants').length>0 && User.addresses.length>0){
				user_sortList(User);
			}
			
			
			
		}else if(response.result == "new_user"){
			
			//registration:
			
			User = response.data;
			$('#main_login').hide();
			$('#existing_user_btn').hide();
			
			$('#mergeDetails').show();
			
					
			if(User.fname.length > 0){
			 $('#mergeFName').val(User.fname);
			}
			
			if(User.lname.length > 0){
			$('#mergeLName').val(User.lname);
			}
			
			$('#mergeSubmit').click(newFB());
			
			
			
			} else if (response.result == "no_email"){
			User = response.data;			
			//get email address:
			$('#main_login').hide();
			$('#existing_user_btn').hide();
			
			
			$('#mergeEmail').show();
			$('#merge_email_submit').click(function(){
			emailFB();
			
			});
			
			
			
		
		} else if (response.result == "error"){
			console.log (response.error);
		} else {
			console.log (JSON.stringify(response));
		}	    
			
	};
	
	
	// Open the connection.
	xhr.open('POST', 'https://www.tastes-good.com/uploader/fb_login.php', true);
	xhr.send(formData);
	
	
	
      }
    });
	
	
}


function newFB(){
	//Validate Form
	
			
	
	var fname = $('#mergeFName').val();
	var lname = $('#mergeLName').val();
	
	var telephone = $('#mergePhone').val();
	
				
	//telephone number
	var password = + new Date()
	var pattern =  /[0-9]{3}[\D]*[0-9]{3}[\D]*[0-9]{4}/
	
	if (!pattern.test(telephone)){
		alert ("Please enter a 10 digit phone number.");
		return;
	}
						
						
	
						
	//create a temporary user
	var newUser = {						
		'fname':fname, 
		'lname':lname, 
		'phone':telephone, 
		'fb_id' : User.fb_id, 
		'email' : User.email,
		'password' : password
	};
			
	var formData = new FormData ();
	formData.append ('newUser', JSON.stringify(newUser));
	var xhr = new XMLHttpRequest();
	
	xhr.onload = function() {
		console.log(xhr.response);
		var response = JSON.parse(xhr.response);
		
		if(response.result == "success"){
		
			User = response.data;
			$('#userLogin').modal('hide');
			
			//update menus
			$('#dropdown_login').hide();
			$('#dropdown_logout').show();
			$('#dropdown_past_orders').show();
			
			
			//page specific functions
			if($('#restaurants').length>0 && User.addresses.length>0){
				user_sortList(User);
			}
			
		}  else if (response.result == "error"){
			console.log (response.error);
		} else {
			console.log ("uncaught error!! "  + JSON.stringify(response));
		}			

	};
	
	
	
	// Open the connection.
	xhr.open('POST', 'https://www.tastes-good.com/uploader/fb_new.php', true);
	xhr.send(formData);
	
	
	
      }

function emailFB(){
	//Validate Form
	
	var email  = $('#merge_email').val()
	var pattern = /@/
			
	if(!pattern.test(email)){
		alert("invalid Email address");
		return;
				
	}
	
	var formData = new FormData ();
			
			
	formData.append ('email', email);
	formData.append ('fb_id', User.fb_id );
	var xhr = new XMLHttpRequest();
	
	xhr.onload = function() {
		console.log(xhr.response);
		
		if(xhr.response == "EMAIL_OK"){
			$('#mergeEmail').hide();
			//Get Details
						
			$('#mergeDetails').show();
			
					
			if(User.fname.length > 0){
			 $('#mergeFName').val(User.fname);
			}
			
			if(User.lname.length > 0){
			$('#mergeLName').val(User.lname);
			}
			
			$('#mergeSubmit').click(function(){newFB();});
			
			
		
			
		} else if (xhr.response == "EMAIL_MERGE"){
			$('#mergeEmail').hide();
			$('#mergePassword').show();
			$('#mergePwd_submit').click(function(){pwdFB();});
		} else {
			console.log ("uncaught error!! "  + JSON.stringify(response));
		}	    
			
	};
	
	
	// Open the connection.
	xhr.open('POST', 'https://www.tastes-good.com/uploader/emailCheck.php', true);
	xhr.send(formData);
	
	
	
      }      
      
function pwdFB(){
	var formData = new FormData ();
	var email  = $('#merge_email').val();
	var password =  $('#mergePwd').val();
	formData.append ('email', email);
	formData.append ('fb_id', User.fb_id );
	formData.append ('password', password);
	var xhr = new XMLHttpRequest();
	
	xhr.onload = function() {
		console.log(xhr.response);
		var response = JSON.parse(xhr.response);
		
		if(response.result == "success"){
		
			User = response.data;
			$('#userLogin').modal('hide');
			
			//update menus
			$('#dropdown_login').hide();
			$('#dropdown_logout').show();
			$('#dropdown_past_orders').show();
			
			//page specific functions
			if($('#restaurants').length>0 && User.addresses.length>0){
				user_sortList(User);
			}
			
		}  else if (response.result == "error"){
			console.log (response.error);
		} else {
			console.log ("uncaught error!! "  + JSON.stringify(response));
		}			
	};
	
	
	// Open the connection.
	xhr.open('POST', 'https://www.tastes-good.com/uploader/fb_merge.php', true);
	xhr.send(formData);
	
	
}


window.onpopstate = function(event) {
 checkUser();
 checkState(event.state);
};

function checkHash(){
	var rest_id, coupon_code;
	
	if(!window.location.hash.length && !window.location.search.length){
		return false;	
	}
	if( window.location.hash.length){		
		params = window.location.hash.split('#')[1];
		params = params.split('$');
	}else{
		params = [];
	}
	if( window.location.search.length){		
		action = window.location.search.split('?',2)[1];
	}else{
		action = ""
	}
	switch(action){
	case "invite":
		//?invite#id
		invite = params[0];
		history.pushState("", document.title, window.location.pathname)
		return false;
		break;	
		
	case "review":
		$("#message_recieved").modal("show");
		history.pushState("", document.title, window.location.pathname)
		return true;
		break;
	
	case "contact":
		$("#Contact").modal("show");
		history.pushState("", document.title, window.location.pathname)
		return true;
		break;
	
	case "coupon":
		rest_id = params[0];
		coupon_code = params[1];
		
		var payload, callback, target;
		
		payload = {'rest_id':rest_id, 'coupon':coupon_code};
		
		callback = function(response) {
			var data;
			
			if(response.result == "success"){
				Restaurant = response.rest;
				Order.coupon = response.coupon;
				Order.rest_id = Restaurant.rest_id;
				renderRest();
				
				history.pushState("", document.title, window.location.pathname + window.location.search)
				
			}else if(response.error == "NO_COUP"){
				Restaurant= response.rest;
				Order.rest_id = Restaurant.rest_id;
				renderRest();
				//show error that coupon could not be found. 
				
			}else{
				console.log(response.error);
				
			}
		}
		
		target = 'https://www.tastes-good.com/api_scripts/coupon.php';
		ajaxRequest(target, payload, callback);
		return true;	
		break;	
	
		
	default:
		return false;
	}
			
}

function checkState(state){
var cur_time, list, community, restaurant;
cur_time = + new Date();

if(state !== null){

switch(state.page) {
	case "list":
    	$('#xs_navbar').hide();	
         list = JSON.parse(localStorage.getItem("list"));
         Community = list.community;  
         if(Community.community == state.community){
	         	         
	         if(list.timestamp + 172800000 > cur_time){
	            List = list.list;
	            renderList();
	         } else {
	         	getList(Community.community)
	         }
         
         } else {
         	
         	communities = JSON.parse(localStorage.getItem("communities"));
         	getList(state.community);
          
         }
        break;
    case "order":
     $('#xs_navbar').show();	   
     Restaurant = JSON.parse (localStorage.getItem("restaurant"));
     Community = Restaurant.community;
	 Order.rest_id = Restaurant.rest_id
     OrderTotal = 0
     getRest();
    
             
        break;
       
       
    default:
        getCommunity();
        $('#xs_navbar').hide();	
    
        }
} else {
getCommunity();
}   
}


function braintreeSetup(btToken){
	 braintree.setup(btToken, "custom", {
			id: "braintreeForm",
			paypal:{
				container: "paypal_container",
				paymentMethodNonceInputField: "payment_nonce",
				amount: Order.total,
				currency: "CAD",
				onSuccess:function(payload){
					window.nonce = {"nonce":payload, "type":"paypal"};
					$('#credit_div').hide();
					$('#payment_method_div').show();
					$('#offline_buttons').show()
					
					//show the button
					
					
					
					
				},
				onCancelled:function(){
					window.nonce = {};
					$('#credit_div').show();
					$('#offline_buttons').hide()
					$('#orderConfirm').find('.modal-content').scrollTop(10000);
				}
			},
			hostedFields: {
			  number: {
				selector: "#card-number"
			  },
			  cvv: {
				selector: "#cvv"
			  },
			  expirationMonth: {
				selector: "#expiration-month"
			  },
			  expirationYear: {
				selector: "#expiration-year"
			  },
			  
			  styles: {
			  	'input': {
				      'font-size':'1.2em',
				      'font-family': 'Courier New',
				      'color': '#343238',
				 },
							  
				  
				  ".invalid": { color: "#990000" },
			  }
			},
			onReady:function(integration){
				checkout = integration;	
				$('#orderConfirm').on('hidden.bs.modal', function(e){
					checkout.teardown(function () {
						checkout = null;
						$('#paypal_prepend').show()
					});
					$(this).unbind(e);
				});		
				$('#payment_method_div').hide();
				$('#credit_div').show();
			},
			onPaymentMethodReceived:function(payload){
				nonce = payload
				$('#credit_div').hide();
				
				$('#payment_method_div').show();
							
				$('#offline_buttons').show()			
				$('#orderConfirm').find('.modal-content').scrollTop(10000);
				return false;
		
		  }
	}); 
}

function fetchBraintree(){
var payload, target, callback;	
	
if(btToken == 0){
	
	var xhr = new XMLHttpRequest();
	
	callback = function(response) {
		if(response.result == "success"){
			btToken = response.data;
			braintreeSetup(btToken);
  		
		}
		
	};  
	  
	target = "https://www.tastes-good.com/api_scripts/braintree.php?status="+Restaurant.status;	
	ajaxRequest(target, payload, callback);
	
}else{
	braintreeSetup(btToken);	
}
}


function getUserOrders(){
	var payload, callback, target;
	if(User.user_id == 0){
		return;	
	}
	payload = "";
	
	callback = function(response) {
	
			if(response.result == "success"){
				user_orders = response.data;
													
				renderUserOrders();
				
			}else{
				console.log(response.error)
			}
			
			
		}
	
	target ='https://www.tastes-good.com/api_scripts/user_orders.php';
	ajaxRequest(target, payload, callback);
}


function renderUserOrders(){
	var x;
	var button;
	var order, link;
	var frag;
	var div1, div2, div3, div4;
	
	
	frag = document.createDocumentFragment();
	for(x=0;x<user_orders.orders.length;++x){
		//order_id date restaurant;
		//click to load pdf. 1)review rest.  2)repeat 3)close.
		
		order=user_orders.orders[x];
		order.link = "https://www.tastes-good.com/uploader/push_pdf.php?order_id="+order.order_id
		 if(window.isphone){
		 	order.link = order.link + "&isphone=1";	 
		  }
		 
		div1 = $("<div>", {class:"col-xs-2"}).append(order.order_id + "#");
		div2 = $("<div>", {class:"col-xs-3"}).append(order.request_date);
		div3 = $("<div>", {class:"col-xs-4"}).append(order.rest.title);
		div4 = $("<div>", {class:"col-xs-3"}).append(validatePrice(order.total));
		
				
		
		button = $("<button>", {class:"btn btn-block btn-default row"}).append(div1).append(div2).append(div3).append(div4)
		.on("click", {order:order}, function(event){
				showPDF(event.data.order.link);
								
				$('#User_Orders').modal("hide");
				$("#order_repeat").off("click").on("click", {order:event.data.order}, function(event){
					hidePDF();
					repeatOrder(event.data.order);	
				});
				
				$("#order_review").off("click").on("click", {order:event.data.order}, function(event){
					reviewOrder(event.data.order);	
				});
									
		});
		
		$(frag).append(button);
				
	}
	$('#user_orders_list').append(frag);
	$('#User_Orders').modal("show");
}

function rebuildOrder(){
	var x = 0;
	var y = 0;
	var categories=Restaurant.menu.categories;
	var extras = Restaurant.menu.extras
	$('#current_order').find("li").not("#order_placeholder").remove();

	for(x=0; x<Rebuild.items.length; ++x){
		order_Item = Rebuild.items[x]
		getExtra();
	}
		
}


function repeatOrder(order){
	var target, payload, callback;
	payload = "";
	
	Restaurant.rest_id = order.rest_id
		
	callback = function(response) {
		hidePDF();
		
			if(response.result == "success"){
				Restaurant = response.data.rest;
				
				Rebuild = response.data.order;
				renderRest();
				Order = {"time":+ new Date(), "rest_id" : order.rest_id, "user":User, "pref":{},  "items":[], "driverTip":order.tip, "paymentType":order.paymentType, "discount_rate":0, "coupon":order.coupon, "subTotal":0, "total":0};
				$('#order_comments').val(Rebuild.comments);
				rebuildOrder();
				$('#menu_notchanged').modal("show");								
			}else if(response.result == "menu_error"){
				
				
				Restaurant = response.data.rest;
				Rebuild = response.data.order;
				renderRest();
				$('#order_comments').val(Rebuild.comment);
				Order = {"time":+ new Date(), "rest_id" : order.rest_id, "user":User, "pref":{},  "items":[], "driverTip":order.tip, "paymentType":order.paymentType, "discount_rate":0, "coupon":order.coupon, "subTotal":0, "total":0};
				
				$('#menu_changed').modal("show");
				
			} else{
				console.log(response.error)
			}
			
			
		
	}
	
	target = 'https://www.tastes-good.com/api_scripts/repeat_order.php?order_id='+order.order_id;
	ajaxRequest(target, payload, callback);

}

function initDetails(){
if (User.user_id !== 0){
	
	fetchBraintree();
			
	Order.user = User;
	
	$('#Cust_name').text(User.fname + " "+ User.lname);
	$('#Cust_email').text(User.email);
	$('#Cust_phone').text(User.phone);
	
	$('#order_AddressList').empty();
	if(User.addresses.length==0){
		var placeholder= $("<div>Address is required for deliveries.</div>");
		$(placeholder).attr("id", "address_placeholder");
		$('#order_AddressList').append(placeholder);
	}
	
	 for(x=0;x<User.addresses.length;++x){
		 addAddress(User.addresses[x], $('#order_AddressList'));
		 
	 }
	 if (User.addresses.length>0){
	 	 $('#address_radio_'+User.addresses[0].id).prop("checked", true);
	}

	$('#orderDetails').modal('show');
	
	
	var driverTip = (parseFloat(Order.subTotal) * parseFloat($("[name=deliveryTip]:checked").val())).toFixed(2);	
	
	driverTip = validatePrice(driverTip, "$0.00");
	
	$('#deliveryTip').val(driverTip);
	
	$('#delivery_address_new').hide();
	$('#saveAddress').hide();
	$('#newAddress_Cancel').hide();
	$('#newAddress').show();
	
					
	
	$('#order_Pickup').prop("checked",true);
	$('#delivery_address').hide();
	$('#delivery_address_buttons').hide();
	$('#driver_tip').hide();
	$('[name = "addressType"]').change(function(e){
			var addressType = $("[name=addressType]:checked").val();
			if(addressType == "appt"){
				$('#appt_details').show();
			} else {
				$('#appt_details').hide();
			}
	});
	
}else{
	$('#userLogin').modal('show').on('hidden.bs.modal', function(e){
			if(User.user_id != 0){
				initDetails();
			}
			$(this).unbind(e);
		});		
		
}

}


function TreeSearch(object){
	var y;
	var z;
	
	for(y=0;y<object.selected.length;++y){
		for(z=0;z<object.selected[y].extras.length;++z){
		  if(object.selected[y].extras[z].selected.length==0){
		  	theExtra=object.selected[y].extras[z];
		  	extra_id=object.selected[y].extras[z].extra_id;
		  	}else{
		  	   TreeSearch(object.selected[y].extras[z]);  
			}		  
	     
			if(extra_id!==0){
			  break;
			}
		
		} 
		if(extra_id!==0){
		  break;
		}  	  
	}
}

function calcPrice(extra){
	var i;
	var h;
	var this_price = 0;
	
	for (i=0;i<extra.selected.length;++i){
		this_price = (parseFloat(this_price) + parseFloat(extra.selected[i].price)).toFixed(2);
		if(extra.selected[i].extras.length > 0 ){
			for(h=0;h<extra.selected[i].extras.length;++h){
			  this_price = (parseFloat(this_price) + parseFloat(calcPrice(extra.selected[i].extras[h]))).toFixed(2);	
			}
	    }  
	}
	return this_price;
	
}

function getExtra(){
	extra_id = 0
	theExtra = {}
	//get the first empty extra
	for(x=0;x<order_Item.extras.length;++x){
		if(order_Item.extras[x].selected.length==0){
		extra_id=order_Item.extras[x].extra_id;
		theExtra=order_Item.extras[x]
		break;
		
		}else{
			TreeSearch(order_Item.extras[x]);
		}
	
		if(extra_id!==0){
			break;
		} 
	}
	
	if(extra_id!==0){
		$('#ExtraModal').modal('show');
		launchModal(extra_id, theExtra);
		
	}else{
		var li, h2, button, product, price, ul;
		
		// Print the Order
		++OrderTotal;
	        Order.items.push(order_Item);
	        		
		$('#order_placeholder').hide();
		
		var indent = 0;
		
		
		button = $("<button>", {class:"glyphicon glyphicon-minus", style:"color:#633E26;", type:"button"})
		product = $("<span>").text(order_Item.product);
		price = $("<span>", {class:"badge", style:"float:right;"}).text('$'+order_Item.price);
		
		h2 = $("<h2>", {style:"width:100%"}).append(button).append(product).append(price);
		ul = $("<ul>", {class:"list-group", style:"margin-top:10px;"});
		
		li = $("<li>", {class:"list-group-item", id:"Order_"+order_Item.id}).append(h2).append(ul);
		
		$('#current_order').append(li);
		
			if (order_Item.extras.length == 0){
				$(ul).hide();
			}else{
				for(x=0;x<order_Item.extras.length;++x){
					if(order_Item.extras[x].selected[0].select_id == -1){
						continue;
					}		
					indent=indent+10;
					$(ul).append(printTree(order_Item.extras[x], indent));
					indent=indent-10;
				}
			}
			
		refreshTotals();
					
		
		$(button).click({id:order_Item.id, li:$(li)}, function(event){
			var x;
			$(li).remove();
			
			for(x=0;x<Order.items.length;++x){
				if(event.data.id == Order.items[x].id){
				Order.items.splice(x, 1);
				break;
				}
			}
			
		
			if(Order.items.length==0){
					$('#order_placeholder').show();
			}
			
			refreshTotals();
					
		});
		
		
		//clear the extra
		$('#ExtraModal').modal('hide');	
		
		
		
		}
							 
		
}

function refreshTotals(){
	var subtotal = 0;
	var k;
			
			for(k=0;k<Order.items.length;++k){
				subtotal = (parseFloat(subtotal)+parseFloat(Order.items[k].price)).toFixed(2);
				
				if (Order.items[k].extras.length >0){
					for(j=0;j<Order.items[k].extras.length;++j){
						var extra_total = calcPrice(Order.items[k].extras[j])
						subtotal = (parseFloat(subtotal)+parseFloat(extra_total)).toFixed(2);
					}
				}
			}
			
			Order.subTotal = subtotal;
			$('#sub_total').text(validatePrice(Order.subTotal));
			var cur_discount = 0
			if(Order.discount_rate){
				var cur_discount = parseFloat(Order.subTotal*Order.discount_rate).toFixed(2);
				$('#order_discount').text(validatePrice(cur_discount));
			}
			var tax = parseFloat((Order.subTotal - cur_discount)*0.13).toFixed(2);
			$('#tax').text('$'+tax);	
			var total = (parseFloat(Order.subTotal) + parseFloat(tax) - parseFloat(cur_discount)).toFixed(2);
			$('#total').text(validatePrice(total));	
			$('#xs_total').text(validatePrice(total));
}

function printTree(extra, indent){
	var y;
	var z;
	var dom_object = $("<li>", {class:'list-group-item no-padding no-border'});
	
	
	for (y=0;y<extra.selected.length;++y){
		if(extra.selected[y].select_id == -1){
			continue;	
		} else {
		var product = $("<span>").text(extra.selected[y].name)
		var price = $("<span>", {style:"color:#633E26;float:right;padding-right:10px;"}).text("$"+extra.selected[y].price)
		var curr_div = $("<div>", {style:'padding-left:'+indent+'px;'}).append(product).append(price)	
		$(dom_object).append(curr_div);

		if(extra.selected[y].extras.length > 0 ){
			var extras = $("<ul>", {class:'list-group no-padding no-border'});	
			for(z=0;z<extra.selected[y].extras.length;++z){
			  indent=indent+10;
			  $(extras).append(printTree(extra.selected[y].extras[z], indent));	
			  
			  $(dom_object).append($(extras));
			  indent=indent-10;
			}
			
	     
	    }  
	    
	   }
	}
	
	return dom_object;
}

function launchModal(extra_id, theExtra){

	
	//initialize the modal
	$('#extraSelect').empty().addClass('hidden');
	$('#extraCheck').empty().addClass('hidden');
	
	//set the question:
	$('#extraQuestion').text(theExtra.question );
	$('#parentItem').text(order_Item.product); 
	//set the options
	switch (theExtra.type){
		case "1":
			
			// buttons
			$('#extraSelect').removeClass('hidden');
			$('#extraContinue').text('No Thanks');
			$('#extraContinue').hide();
			//change continue button
			
			for(x=0;x<theExtra.options.length;++x){
				var extra_btn = $('<button></button>')
				$(extra_btn).addClass("btn btn-block btn-default").text(theExtra.options[x].name).append('<strong style="float:right; margin-right:20px;">$' +theExtra.options[x].price +'</strong>');
				$('#extraSelect').append($(extra_btn));
				
				$(extra_btn).click({option:theExtra.options[x], id:x}, function(event){
					
					var selection = {"select_id":event.data.id , "price":event.data.option.price, "name":event.data.option.name, "extras":[]};
					var y;
					for(y=0;y<event.data.option.extras.length;++y){
						selectExtra = $.grep(Restaurant.menu.extras, function(e) {
								return e.id == event.data.option.extras[y];
							})[0];
						selection.extras.push({'extra_id':selectExtra.id, 'selected':[],'options':selectExtra.options, 'question':selectExtra.question, 'type':selectExtra.type});
						
					}
				
				theExtra.selected.push(selection);
				getExtra();
				});
			
			}
			
			break;
		case "2":
			$('#extraCheck').removeClass('hidden');
			
			var label, span, chk, li;
			
			for(x=0;x<theExtra.options.length;++x){
				
				li = $('<div>', {style:"display:inline-block; height:35px; min-width:175px;margin:auto;"});
				label = $('<label>', {class:"extra-chk-label"});
				span = $('<span>', {class:"extra-chk-span"}).append('<strong>'+theExtra.options[x].price  +'</strong>')
				chk = $('<input>', {class:"extra-chk-chk", type:"checkbox"});
				
				label.append(chk).append(theExtra.options[x].name).append(span)
				li.append(label)
				$('#extraCheck').append(li)
			}
			
			$('#extraContinue').text('Continue').show();	
			$('#extraContinue').off('click').click({extra:theExtra}, function(event){
				var x;
				for(x=0;x<event.data.extra.options.length;++x){
				   if($($('#extraCheck').find("input")[x]).prop('checked')){
				   	   var theOption_id = x;
				   	   var theOption= event.data.extra.options[x];
				   	   var selection={'select_id':x, 'price':theOption.price, 'name':theOption.name, 'extras':[]};
				   	   
				   	   for(z=0;z<theOption.extras.length;++z){
							selectExtra = $.grep( Restaurant.menu.extras, function(e) {
									return e.id == theOption.extras[z];
							})[0];
							
							selection.extras.push({'extra_id':selectExtra.id, 'selected':[], 'options':selectExtra.options, 'question':selectExtra.question, 'type':selectExtra.type});
							
						}
						
						theExtra.selected.push(selection);
					}
				}
				
				if(theExtra.selected.length==0){
					selection={'select_id':-1, 'price':0, 'name':"", 'extras':[]};
					theExtra.selected.push(selection);
				}
				getExtra();	
						
			});
			//options
			break;
	}	
}


function AddOrder(item_id){
	//get theCategory and theItem
	var x = 0;
	var y = 0;
	var categories=Restaurant.menu.categories;
	var extras = Restaurant.menu.extras
	
	theCategory = ""
	theItem = ""
	theExtra = {}
	order_Item = {}
	
	for(x=0;x<categories.length;++x){
		for(y=0;y<categories[x].items.length;++y){
			if(categories[x].items[y].id == item_id){
				theCategory = categories[x];
				theItem = categories[x].items[y];
				break;
			}			
		}
		if(theCategory !== ""){
			break;
		}
	}
	
		
	order_Item = {'id':OrderTotal+1, 'item_id':theItem.id, 'product':theItem.product, 'price':theItem.price, 'extras':[], 'subtotal':theItem.price};
		
	// build the Extra Cue
	
	if(theCategory.extras){
		for(x=0;x<theCategory.extras.length;++x){
			theExtra = $.grep( extras, function(e) {
					return e.id == theCategory.extras[x];
			})[0];
			
			order_Item.extras.push({'extra_id':theExtra.id, 'selected':[], 'options':theExtra.options, 'question':theExtra.question, 'type':theExtra.type});
		}
	}
	
	if(theItem.extras){
		for(x=0;x<theItem.extras.length;++x){
			theExtra = $.grep(extras, function(e) {
					return e.id == theItem.extras[x];
			})[0];
			
			order_Item.extras.push({'extra_id':theExtra.id, 'selected':[], 'options':theExtra.options, 'question':theExtra.question, 'type':theExtra.type});
			
		}
	}
	
	
	getExtra();
	
	
}


function applyCoupon(){

		
	if(Order.coupon != 0){
	 	 if(Order.coupon.type == "discount"){
	 	 	 
	 	 	 Order.discount_rate = Order.coupon.discount;
	 	 	 //Order.discount
	 	 	 
	 	 	 //Show discount div. 
	 	 	 
	 	 	 
	 	 }else if (Order.coupon.type == "item"){
	 	 	 //Add the Item to the Menu
	 	 	 var coupon_item = {"category":"Coupons","description":"","id":"C"+Order.coupon.id,"product":Order.coupon.title +": $"+ Order.coupon.price, "price":Order.coupon.price, "extras":Order.coupon.extras};
	 	 	 Categories.push({"category":"Coupons", "items":[coupon_item],"extras":[]});
	 	 	 
	 	 	 
	 	 	 setTimeout( function(){AddOrder("C"+Order.coupon.id)}, 2000 ); 
	 	 	
	 	 	 
	 	 	 
	 	 }
	 	 
	 	 
	 
	 	 
	 	 
	 }

}






function nearestCommunity(position){
	if(position.coords.accuracy < 50000){
		var locate = {"lat":position.coords.latitude, "lng":position.coords.longitude}
		var distance = 25000;
		var myCommunity = "";
		for(x=0; x<communities.length; ++x){
			communities[x].distance = calcDistance(locate, communities[x])
			if(communities[x].distance < distance){
				distance = communities[x].distance
				Community = communities[x]
			}		
		}
		
		if(Community.community == ""){
			alert("Sorry!  We are still getting started..  We don't have any restaurants near your community");
		}else{
		
			getList(Community.community);
			sortList(position);	
			
		}
		
		
		
	}
	
}



function sortList(position){
	if(position.coords.accuracy < 2500){
		var locate = {"lat":position.coords.latitude, "lng":position.coords.longitude}
		for(x=0; x<List.length; ++x){
			List[x].distance = calcDistance(locate, List[x])
			List[x].distance = Math.round(10*List[x].distance)/10
			List[x].charge = parseFloat(List[x].delivery_base) + parseFloat(List[x].delivery_rate * Math.floor(List[x].distance/0.25))
			
			
		}
		List.sort(function(a, b){return b.distance-a.distance});
					
		if($("#restaurants").length > 0 ){
			for(x=0;x<List.length; ++x){
				if(List[x].open && ! List[x].closed && List[x].status == "ACTIVE"){
					$('#restaurants').prepend($('#rest_'+List[x].rest_id))
				}
			
			}
		}
		
	}
	
}


function locateFail(positionError){
	console.log(positionError.message);
	alert("Geolocation failed.  Have you enabled geolocation from in your browser settings?");
}
	

function locationSearch(){
	if(navigator.geolocation){
			navigator.geolocation.getCurrentPosition(nearestCommunity, locateFail);
	}	
}

function renderCommunity(){
	$('#community').empty();
			 
	for(x=0; x<communities.length;++x){
		$('#community').append("<option value='"+communities[x].community+"'>"+communities[x].community+"</option>");
	}
	transitions($('#select_community'));
	
}

function getCommunity(){
 	var cur_time;
 	var payload, callback, target;
 	cur_time = + new Date();
 	
 	if (localStorage.getItem("communities") !== null) {
            communities = localStorage.getItem("communities")
            if(communities.timestamp + 86400000 > cur_time){
            	renderCommunity();
            	return;
            }
        }
    
    payload = ""
    callback = function(response) {
		if (response.result=="success"){
			communities = response.data
			communities.timestamp = +new Date();
			localStorage.setItem("communities",JSON.stringify(communities));
			renderCommunity();
					 			
			}else{
				console.log(response);
			}
				
			
		}
	
	// Open the connection.
	target= 'https://www.tastes-good.com/api_scripts/communities.php';
	ajaxRequest(target, payload, callback);
}


function getList(community){
	var payload, callback, target;
	payload = "";
	
	if(Community.community !== community){
	
	for(x=0;x<communities.length;++x){
	  if(communities[x].community == community){
	    Community = communities[x];
	    break;
	   }
	 }
	}  
		
	callback = function(response) {
			if(response.result == "success"){
			List = []
			List = List.concat(response.data.restaurants).concat(response.data.closed).concat(response.data.testing);
							
				
			if (User.addresses.length>0){
   	   			user_sortList(User)	   	   		
	   		}  	
			
			renderList();
			
			//change state
			var restaurants = {};
			restaurants.list = List;
			restaurants.community = Community;
			restaurants.timestamp = +new Date();
			
			localStorage.setItem("list", JSON.stringify(restaurants));
			localStorage.setItem("community", JSON.stringify(Community));
			
			var state = {page: "list", community:Community.community};
			setState(state);
			
						 			
			}
	}
			
		
	
	
	
	// Open the connection.
	target="https://www.tastes-good.com/api_scripts/restaurants.php?community="+community;
	ajaxRequest(target, payload, callback);
}

function renderList(){
	var frag, title, address, open, delivery, img, img2, col1, col2, div, anchor, li, h2, button, span;
	var open_list, closed_list, testing_list;
	 $('#restaurants').empty();
				
	 $('[name="active_community"]').text(Community.community);
	 $('#rest_count').text(List.length);
	 
	  frag = document.createDocumentFragment();
	  open_list = document.createDocumentFragment();
	  closed_list = document.createDocumentFragment();
	  testing_list = document.createDocumentFragment();
	 
	 for(x=0; x<List.length;++x){
		  title = $("<h3>"+List[x].title+"</h3>");
		  address = $("<h2>", {style:"white-space: pre-wrap;"}).append(List[x].address);
		  open = $("<h2></h2>").append("<strong>Open: </strong>" + List[x].open_time).append("<strong style='margin-left:10px;'>Close: </strong>"+List[x].close_time);
		  delivery = $("<strong>Delivery Charge</strong>").append(validatePrice(List[x].delivery_base)); 
		  img= $("<img>", {class:"image-rounded", style:"height:75px;width:75px;margin-top:20px;", src:List[x].image});
		  col1 = $("<div></div>").addClass("col-xs-4").append(img);
		  col2 = $("<div></div>").addClass("col-xs-8").append(title).append(address).append(open).append(delivery);
		  
		  var div = $("<div></div>").addClass("row").append(col1).append(col2);
		  
		  if(List[x].status == "ACTIVE"){
		  if(List[x].open && !List[x].closed){
			   anchor = $("<a></a>").addClass("btn btn-block").click({restaurant:List[x]}, function(event){
				 Restaurant = event.data.restaurant;
				 getRest();
			  
			  }).append(div);
							  
			  li = $("<li>", {class:"list-group-item", id:"rest_"+List[x].rest_id}).addClass("list-group-item").append(anchor);
			  
			  $(open_list).append(li);
		  }else {
		  	  
		   anchor = $("<a></a>", {title:"RESTAURANT IS CLOSED", disabled:"disabled"}).addClass("btn btn-block").click({restaurant:List[x]}, function(event){
		     Restaurant = event.data.restaurant;
		     getRest();
		  	  }).append(div);
		   li = $("<li>", {class:"list-group-item", id:"rest_"+List[x].rest_id}).addClass("list-group-item").append(anchor);
		   $(closed_list).append(li);
		  }}else if (List[x].status == "TESTING"){
		  	anchor = $("<a></a>", {title:"COMING SOON!", disabled:"disabled"}).addClass("btn btn-block").click({restaurant:List[x]}, function(event){
		     Restaurant = event.data.restaurant;
		     getRest();
		  	  }).append(div);
		   li = $("<li>", {class:"list-group-item", id:"rest_"+List[x].rest_id}).addClass("list-group-item").append(anchor);
		   $(testing_list).append(li);
		  }
		  
		  $(frag).append(open_list).append(closed_list).append(testing_list);
	  }
	  
	 
	  $('#restaurants').append(frag);
	  
	 frag = document.createDocumentFragment();
	  
	 for(x=0; x<List.length;++x){
		 if(List[x].open && !List[x].closed  && List[x].status != "TESTING"){
			  for(y=0; y<List[x].coupons.length; ++y){
			  if(List[x].coupons[y].public){
			  
				  img = $("<img>", {style:"position:absolute; top:0; left:0; width:175px; height:100px;", src:paths.biz_card});
				  title = $("<h2>", {style:"position:absolute; top:0px;line-height:15px; left:5px;font-size:15px; width:160px; text-align:center;"}).append(List[x].coupons[y].title)
				  
				  if(List[x].coupons[y].type=="item"){
				   span =$("<span>",{style:"color: #633E26;"}).text("$"+List[x].coupons[y].price);
				   $(title).append(span);		
				  }
				  
				  code=$("<h3>", {style:"position:absolute; top:55px; margin-top:0; left:67px;font-size:15px; width:100px; text-align:center;"}).append(List[x].coupons[y].code)
				  h2= $("<h2>", {style:"position:absolute; top:35px; margin-top:0; left:30px;font-size:15px; width:175px; text-align:center;"}).append("Coupon Code:");
				  img2 = $("<img>", {class:"image-rounded", style:"position:absolute; top:22px; left:0;height:56px;width:56px;", src:List[x].image});
				  					  
				  button = $("<a>", {style:"position:relative;height:105px;", class:"btn btn-block"}).append(img).append(img2).append(title).append(code).append(h2);
				  $(button).click({coupon:List[x].coupons[y], restaurant:List[x]}, function(event){
				   Restaurant = event.data.restaurant;
				   Order.coupon = event.data.coupon
				   getRest();
				   });
				 
				  div = $("<div>", {style:"display:inline-block;width:175px;height:100px;margin:10px"}).append(button)
				  $(frag).append(div);
			  
			  }
			  }
		 }
	 }
	 
	 $('#coupons').append(frag);
	 transitions($('#select_restaurant'));
	 
}

function renderRest(){
	var frag, li, button, ul, li2, row1, row2, product, price, button2, description;
	
	//initialize restaurant info
	$('[name="active_community"]').text(Restaurant.community.community);
	$('#rest_image').attr("src", Restaurant.image);
	$('[name="rest_title"]').text(Restaurant.title);
	$('#rest_phone').text(Restaurant.phone);
	$('#rest_address').text(Restaurant.address);
	$('#rest_open').text(Restaurant.open_time);
	$('#rest_close').text(Restaurant.close_time);
	$('#rest_delivery').text(validatePrice(Restaurant.delivery_base));
	
	//initialize menu
	$('#menu').empty();
	frag =  document.createDocumentFragment(); 
	
	for(x=0;x<Restaurant.menu.categories.length;++x){
		var cat = Restaurant.menu.categories[x];
		
		ul = $("<ul>", {class:"list-group", style:"display:none; padding-top:10px;padding-bottom:10px;padding-right:15px;padding-left:15px;", id:"cat" + cat.id + "_items"})
		
		for(y=0; y<cat.items.length;++y){
		var item = cat.items[y];
		product=$("<div>", {class:"col-xs-6"}).text(item.product);
		price=$("<div>", {class:"col-xs-3"}).text(validatePrice(item.price));
		button2 = $("<button>", {class:"btn col-xs-3"}).text("Order It").click({item:item.id}, function(event){
			AddOrder(event.data.item);
		});
		description =$("<div>", {class:"col-xs-12"}).text(item.description);
		
		row1 =$("<div>", {class:"row"}).append(product).append(price).append(button2);
		row2 =$("<div>", {class:"row", style:"margin-bottom:15px"}).append(description);
		li2 = $("<li>", {class:"list-group-item"}).append(row1).append(row2)
		$(ul).append(li2)
		
		}
		
		
		button =$("<button>", {class:"btn-block btn-md"}).text(cat.category).click({cat:cat.id}, function(event){
		  $("#cat" + event.data.cat + "_items").toggle();
		}); 
		
		
		li = $("<li>", {class:"list-group-item"}).append(button).append(ul);
	
		$(frag).append(li);		
	}
	
	$('#menu').append(frag);
	
	transitions($('#place_order'));		 
	$('#xs_navbar').show();
	
	if(Order.coupon !== 0){
		applyCoupon();
	}
		

}

function getRest(coupon){
var payload, callback, target;
coupon = coupon || 0;
var rest_id = Restaurant.rest_id;
payload = {'rest_id':rest_id, 'coupon':coupon};


callback = function(response) {
	if (response.result == "success"){
		Restaurant = response.data			
				
		Order.time = +new Date(); 
		OrderTotal = 0
		Order.rest_id = Restaurant.rest_id
		renderRest();
		
		//save state
		state = {page:"order", rest_id:Restaurant.rest_id}
		setState(state);
		Restaurant.timestamp = + new Date();
		Restaurant.community = Community;
		localStorage.setItem('restaurant', JSON.stringify(Restaurant));
		
		
	}
}
// Open the connection.
target ="https://www.tastes-good.com/api_scripts/menu.php";
ajaxRequest(target, payload, callback);

return true;
}

function validateEmail() {
	$('#submit_newUser').text("Validating...").prop('disabled', true);
	$('#invalid_email').hide();
	var formData = new FormData ();
			
	formData.append ('email', $('#newUserEmail').val());
	/*
	if(User.fb_id.length > 0){
	formData.append ('fb_id', User.fb_id );
	}
	*/
	
	var xhr = new XMLHttpRequest();
	
	xhr.onload = function() {
		
			
		if(xhr.response == "EMAIL_OK"){
			$('#submit_newUser').text("Submit").prop('disabled', false);
			$('#invalid_email').hide();
			User.fb_merge = 0;
			
		}else if(xhr.response == "EMAIL_EXISTS"){
			$('#submit_newUser').text("Submit")
			$('#invalid_email').show();
		}else if(xhr.response == "EMAIL_MERGE"){
			$('#submit_newUser').text("Submit").prop('disabled', false);
			$('#invalid_email').hide();
			$('#merge_password').show();
			User.fb_merge = 1;
		
		
		}
			
			
		
	};
	// Open the connection.
	xhr.open('POST', 'https://www.tastes-good.com/uploader/emailCheck.php', true);
	xhr.send(formData);	
}
	
	
function applyCoupon(){
	
	if(Order.coupon.expires < +new Date()){
		//TODO Popup error message
		return;	
	}
	
	if(Order.coupon.type == "discount"){
 	 	 
 	 	 Order.discount_rate = Order.coupon.discount;
 	 	 $('#discount_coupon').show();
 	 	 
 	 	 //Order.discount
 	 	 //TODO SHOW DISCOUNT DIV
 	 	 
 	 	 
 	 }else if (Order.coupon.type == "item"){
 	 	 //Add the Item to the Menu
 	 	 var coupon_item = {"category":"Coupons","description":"","id":"C"+Order.coupon.id,"product":Order.coupon.title +": $"+ Order.coupon.price, "price":Order.coupon.price, "extras":Order.coupon.extras};
 	 	 Restaurant.menu.categories.push({"category":"Coupons", "items":[coupon_item],"extras":[]});
 	 	 
 	 	 
 	 	 setTimeout( function(){AddOrder("C"+Order.coupon.id)}, 2000 ); 
 	 	
 	 	 
 	 	 
 	 }


}

function user_sortList(user){
	var position =  {coords:{latitude:user.addresses[0].lat, longitude:user.addresses[0].lng, accuracy:0}};
	sortList(position);
}





function initGlobalVars(){

 //Global Variable Initializations

hostname = location.hostname
nonce = {}
pagename = "order.php";
Community = {};
List = [];
Restaurant = {rest_id:0};
User = {user_id:0, fb_id:""};
Order = {"time":+ new Date(), "rest_id" : 0, "user":{}, "pref":{},  "items":[], "driverTip":0, "paymentType":"offline", "discount_rate":0, "coupon":0, "subTotal":0, "total":0};


btToken = 0;
OrderTotal = 0
evtChain = [];
theExtra = {};
extra_id = 0;	
active_div = 0;	
	
}


function initDateTimePickers(){
	//initialize datepicker
	$('#deliveryDate').datepicker({ hideIfNoPrevNext: true, minDate:-0, maxDate:"+7D"}).datepicker( "setDate", "+0" ).datepicker( "hide" );
	$(".ui-datepicker-current").remove();
	$('#ui-datepicker-div').hide();
	
	//timepicker
    $('.timepicker').wickedpicker();
}

function initKeystrokes(){
	  $('#community').keypress(function(e) {
        if(e.keyCode=='13') {
        	e.preventDefault();
            searchRest();
        }
      });
    
    $('#username').keypress(function(e) {
        if(e.keyCode=='13') {
        	e.preventDefault();
            restLogin();
        }
      });
     $('#password').keypress(function(e) {
        if(e.keyCode=='13') {
        	e.preventDefault();
            restLogin();
        }
      });
}


function initModals(){
	 //initialize modals: 
    $('#userNew').hide();
	$('#forgotPwd').hide();
}

function initErrors(){
	$('#invalid_email').hide();
	$('#email_noexist').hide();
	$('#short_password').hide();	
	$('#email_sent').hide();
}

function initMyProspects(){
	$("#referal_user_credit").text(validatePrice(User.credit));
	$("#share_link").val("https://www.tastes-good.com/main.html?invite#"+User.user_id);
	getMyProspects();
		
	$("#my_prospect").modal("show");	
}

function getMyProspects(){
	var target, data, callback;
	callback = function(data){
			MyProspects = data;
			renderMyProspects();
			var state = {page: "my_prospects"};
			setState(state);
	}
	
	target = "https://www.tastes-good.com/api_scripts/user_prospects.php";
	ajaxRequest(target, "", callback);
}

function renderMyProspects(){
	var x, title, total, email, expires, row, button, li;
	var restaurants = MyProspects.restaurants
	var customers = MyProspects.customers
	var frag1, frag2, frag3;
	
	frag1 =  document.createDocumentFragment();
	frag2 =  document.createDocumentFragment();
	frag3 =  document.createDocumentFragment();
	
	
	
	
	for(x=0;x<restaurants.length;++x){
		
		if(restaurants[x].status=="ACTIVE"){
		title = $("<div>", {class:"col-xs-3 no-padding"}).append(restaurants[x].title);
		total = $("<div>", {class:"col-xs-3 no-padding"}).append(restaurants[x].sales_total);
		expires = $("<div>", {class:"col-xs-3 no-padding"}).append(new Date(restaurants[x].commission_term*1000).toDateString());
		credits = $("<div>", {class:"col-xs-3 no-padding"}).append(validatePrice(restaurants[x].total_commission));
		row = $("<div>", {class:"row no-padding"}).append(title).append(total).append(expires).append(credits);
		button = $("<button>", {class:"btn btn-block"}).append(row);
		
		li = $("<li>", {class:"list-group-item no-padding"}).append(button);
		$(frag1).append(li);
		
		}else{
			title = $("<div>", {class:"col-xs-8 no-padding"}).append(restaurants[x].title);
			email = $("<div>", {class:"col-xs-4 no-padding"}).append(restaurants[x].email);
			row = $("<div>", {class:"row no-padding"}).append(title).append(email);
			button = $("<button>", {class:"btn btn-block"}).append(row);
			
			li = $("<li>", {class:"list-group-item no-padding "}).append(button);
			$(frag2).append(li);
			
		}
	}
	
	if(frag1.children.length == 0){
		button = $("<button>", {class:"btn btn-block"}).append("No Active Restaurants");
		li = $("<li>", {class:"list-group-item no-padding"}).append(button);
		$(frag1).append(li);
	}
	
	
	if(frag2.children.length == 0){
		button = $("<button>", {class:"btn btn-block"}).append("No Prospects");
		li = $("<li>", {class:"list-group-item no-padding"}).append(button);
		$(frag2).append(li);
	}
	
	
	$("#customer_count").text(customers.length);
	if(customers.length == 0){
		button = $("<button>", {class:"btn btn-block"}).append("No Active Friends");
		li = $("<li>", {class:"list-group-item no-padding"}).append(button);
		$(frag3).append(li);
	}
	
	for(x=0;x<customers.length;++x){
		title = $("<div>", {class:"col-xs-6 no-padding"}).append(customers[x].user_id + ". " +customers[x].fname + " " + customers[x].lname);
		total = $("<div>", {class:"col-xs-3 no-padding"}).append(customers[x].sales_total);
		expires = $("<div>", {class:"col-xs-3 no-padding"}).append(customers[x].commission_term);
		row = $("<div>", {class:"row no-padding"}).append(title).append(total).append(expires);
		button = $("<button>", {class:"btn btn-default btn-block"}).append(row);
		
		li = $("<li>", {class:"list-group-item"}).append(button);
		$(frag3).append(li);
	}
	
	$("#active_restaurants").empty().append(frag1);
	$("#prospect_restaurants").empty().append(frag2);
	$("#active_customers").empty().append(frag3);
}

function initButtons(){
	
	$('#saveAddress').on("click", function(){
		var address = {};
		
		address.address = $("#order_newAddress").val(); 
		address.type = $("[name='addressType']:checked").val();
		if (address.type == "appt"){
			address.unit =$("#apptNumber").val();
			address.buzz = $("#apptBuzz").val();
		}
		address.comments = $("#addressComments").val();
		
		//TODO:  data verification. 
		callback = function(response){
			if(response.result = "success"){
			  var address = response.address;	
												
						 User.addresses = response.data
						 
						 $('#order_AddressList').empty();
											  
						 for(x=0;x<User.addresses.length;++x){
							 addAddress(User.addresses[x], $('#order_AddressList'));
						 }
						
											 
						$('#address_radio_'+User.addresses[0].id).prop("checked", true);
						$('#order_AddressList').show();
						$('#newAddress').show();
						$('#saveAddress').hide();
						$('#newAddress_Cancel').hide();
						$('#delivery_address_new').hide();
			}else if (response.error=="GOOGLE_FAIL"){
				alert("Error:  Google Could Not Locate Address--Please Include Community and PostCode");
				
			}else{
				console.log(response.error);
				alert("Error:  Google Could Not Locate Address--Please Include Community and PostCode");
			
			}
 		}
		
		
		
		saveAddress(address, callback);
			
		
	});
	
	
	$('#submit_forgot_pwd').on("click", function(){
		var payload, email, callback, target;
			
		email = $("#email_recovery").val();	
		payload={email:email};
		
		callback = function(response){
			$("#email_sent").show()
						
		}
		
		target = "https://www.tastes-good.com/uploader/email_recovery.php";
		ajaxRequest(target, payload, callback);
			
			
	});
	
	$("#show_terms").on("click", function(){
			showText("./termsconditions.html");
	});
	
	$("#show_privacy").on("click", function(){
			showText("./privacy.html");
	});
	
	$("#dropdown_my_prospects").on("click", function(){
		$("#navbar_dropdown").hide();
		initMyProspects();
	});
	
	$("#show_prospects").on("click", function(){
		initMyProspects();
	});
	
	$("#new_prospect").on("click", function(){
			$("#new_restaurant_details").fadeToggle();
	});    
	
	$("#prospect_new").on("click", function(){
			var target, payload, callback;
			var pattern;
			var address;
			
			address = $("#new_address").val()
			
			if(typeof(Community.community) != "undefined"){
				if(address.search(Community.community)==-1){address = address + ", " +Community.community;}
				if(address.search(Community.province)==-1 && address.split(",").length < 3){address = address + ", " +Community.province;}
			}
			if(address.search("Canada")==-1 && address.split(",").length < 4){address = address + ", " +"Canada";}
			
			
			payload = {title:$("#new_title").val(), address:address, phone:$("#new_phone").val(), email:$("#new_email").val(), community:Community.community};
			
			if(payload.title.length < 1  || payload.address.length < 1 ){
				alert("All Fields are Required!");	
				return;
			}
					
			pattern = /@/
			
			if(!pattern.test( payload.email)){
				alert("Invalid Email: Email address is required!");
				return;
			}	
			
			pattern =  /[0-9]{3}[\D]*[0-9]{3}[\D]*[0-9]{4}/
				
			if (!pattern.test(payload.phone)){
				alert ("Invalid Phone: Please enter 10 digit phone number.");
				return;
			}
					
			callback = function(response){
				if(response.result == "success"){
					$("#new_restaurant_details").slideUp();
					$('.modal').modal('hide');
					
					makeToast("Success:", "Thank You! After we review the restaurant details they will be invited to join Tastes-Good.com.");
							
					
					$("#toast").on('hidden.bs.modal', function () {
						initMyProspects();
					})	
					
										
					
				}else{
					alert(response.error);
				}
				
			}
			
			target="https://www.tastes-good.com/uploader/sales_new_prospect.php";
			ajaxRequest(target, payload, callback);
		});
		
			
	$("#share_link_click, #share_link").on("click", function(){
			$("#share_link").select();
			document.execCommand('copy');
			$("#share_link").blur();
			alert("Link Saved to Clipboard");
	});
	
	$('#dropdown_past_orders').on('click', function(){
			$('#navbar_dropdown').fadeToggle();	
			getUserOrders();
		});
	
	 $('#show_coupons').click(function(){
	 	$('#coupons').show();
	 	
	 	$('#restaurants').hide();
	 	$('#show_coupons').hide();
	 	$('#show_rest').show();
	 })
	 
	  $('#show_rest').click(function(){
	 	$('#coupons').hide();
	 	$('#restaurants').show();
	 	$('#show_coupons').show();
	 	$('#show_rest').hide();
	 })
	 		 
	 $('[name = "deliveryTip"]').change(function(e){
		 
		var currentTip =  (parseFloat($("[name=deliveryTip]:checked").val())*parseFloat(Order.subTotal)).toFixed(2);
		Order.driverTip = currentTip; 
		
		currentTip = validatePrice(currentTip, currentTip);
		$('#deliveryTip').val(currentTip);
	
	});
	
 	
   $('#deliveryTip').change(function(){
		var driverTip = /[\d]+[.]{0,1}[\d]{0,2}/.exec($('#deliveryTip').val())[0]
		Order.driverTip = driverTip;
	});
	
	$('#submit_newUser').click(function() {
		//Validate Form
			
			//email address
			var validForm = 1;
			
			var fname = $('#newUserFName').val();
			var lname = $('#newUserLName').val();
			var email = $('#newUserEmail').val();
			var telephone = $('#newUserPhone').val();
			
			if(!User.fb_id.length > 0){
				var password = $('#newUserPwd').val();
				var confirm = $('#newUserPwdConfirm').val();
			
			
			
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
			
					
			}else{
				password = +new Date();
			
			}
			
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
		
		
		if(User.fb_id.length > 0){
		   newUser.fb_id = User.fb_id
		  if(User.fb_merge ==1 ){
		   newUser.mergePwd = $('#mergePwd').val();
			   if(newUser.mergePwd.length == 0){
			   	alert ("Password is required to connect facebook to existing account.");
			   		   
			   }
		   }
		   
		}
				
		var formData = new FormData ();
				
		formData.append ('newUser', JSON.stringify(newUser));
		
								
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			console.log(xhr.response);
			
			var response = JSON.parse(xhr.response);
			
			var user_creds = {};
			
			if(response.result == "success"){
				User = response.data;
				$('#userLogin').modal('hide');
				
				//update menu's
				$('#dropdown_login').hide();
				$('#dropdown_logout').show();
				$('#dropdown_past_orders').show();
				
				
				//page specific functions
				if($('#restaurants').length>0 && User.addresses.length>0){
					user_sortList(User);
				}
				
			} 			
	
		};
	
		// Open the connection.
		xhr.open('POST', 'https://www.tastes-good.com/uploader/newUser.php', true);
		xhr.send(formData);
		
		}
		
	});
    	
    		
	$('#forgot_pwd_Cancel').click(function() {
		$('#forgotPwd').hide();	
		$('#existing_user_btn').show(250);;	
	});
	
	$('#new_User_Cancel').click(function() {
		$('#userNew').hide();	
		$('#existing_user_btn').show(250);
		$('#login').show();	
	});
	
	$('#forget_pwd').click(function() {
		$('#forgotPwd').show(500);
		$('#userNew').hide();	
		$('#existing_user_btn').hide();
		
	});
	
	$('#new_user').click(function() {
		$('#userNew').show(500);
		$('#login').hide();
		$('#forgotPwd').hide();
		$('#existing_user_btn').hide();
		$('#merge_password').hide();
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
		
	$('#newUserEmail').focusout(validateEmail());
	
	$('#submit_login').click(function() {
		var payload, callback, target;
		
		payload = {email:$('#userId').val(), password:$('#userPwd').val()}	
		
		callback = function(response) {
			if(response.result == "success"){
				User = response.data;
				//hide modal and
				$('#userLogin').modal('hide');
							
				refreshUser();
				
				
			
			} else if (response.error == "pwd_wrong"){
				alert ("wrong password");
			} else if (response.error == "no_user"){
				alert ("user id not found");
			}	    
				
		};
	
	
		// Open the connection.
		target = 'https://www.tastes-good.com/uploader/userLogin.php';
		ajaxRequest(target, payload, callback);
		
			
	});
	
    $('#xs_total_btn').click(function(){
		
		var order_info = $('#current_order').clone()
		$(order_info).attr("id", "xs_current_order");
		$('#xs_preview_body').empty().append(order_info);
		var preview_totals = $('#current_total').clone();
		$(preview_totals).attr("id", "xs_preview_totals");
		$('#xs_preview_totals').empty().append(preview_totals);
		
		$('#xs_preview_body').find('button').click(function(){
			var li_item = upTo($(this)[0], "li");
			var the_id = $(li_item).attr("id");
			$(li_item).remove();
			
			var the_btn = $('#current_order').find('#'+the_id).find('button').trigger("click");
			
			
			setTimeout( function(){				
			
			var preview_totals = $('#current_total').clone();
			$(preview_totals).attr("id", "xs_preview_totals");
			$('#xs_preview_totals').empty().append(preview_totals);
				}, 100 );	
			
		});
		
		 $('#xs_preview').modal('show'); 
		
		
		
		
	});
	
	
	
	$('#confirm_Order').click(function(){
  		if(Restaurant.status == "TESTING"){
	  		var h2 = $("<h2>").text("NOTE: THIS RESTAURANT IS NOT CONSIDERED ACTIVE YET--ORDERS WILL NOT BE PROCESSED AND REAL CREDIT CARDS WILL NOT BE RECOGNIZED.  WHEN YOU ARE SATISFIED WITH YOUR SETUP YOU CAN ACTIVATE YOUR RESTAURANT THROUGH THE RESTAURANT DASHBOARD")
	  		$('#rest_active_warning').append(h2)
  		}
  		
  		
  		$('#confirm_address').text(Restaurant.address)
  		
  		
  		if($("[name=deliveryOption]:checked").val()=="delivery"){
  			if(	$("[name=Order_Address]").length <=0){
  				
  				
  				alert("An address is required for deliveries.");
  				return true;
  					
  			}
  		}
  		
  		if($('#terms_conditions').is(':checked')){
  			
  			$('#orderProcess').removeAttr('disabled').removeClass("disabled")
  		}else{
  			$('#orderProcess').attr('disabled','disabled').addClass("disabled")
  		}
  			
  			
  			
  		$('#orderDetails').modal('hide');
  		$('#orderConfirm').modal('show');
  		
  		var cur_discount = 0
		if(Order.discount_rate){
			var cur_discount = parseFloat(Order.subTotal*Order.discount_rate).toFixed(2);
			$('#confirm_discount').text(validatePrice(cur_discount)).parent().show();
		}
		
		
		Order.discount = cur_discount;
  		
  		
  		
  		//store form-data to window.Order
  		if($('[name="deliveryOption"]:checked').val()=="delivery"){
  			Order.pref.deliveryOption = 1;
  			var address_id = $('[name="Order_Address"]:checked').val();
  			for(x=0;x<User.addresses.length;++x){
				if(User.addresses[x].id ==  address_id){
					Order.pref.deliveryAddress = User.addresses[x]
				}
			}
			var driverTip = /[\d]+[.]{0,1}[\d]{0,2}/.exec($('#deliveryTip').val())[0]
			Order.driverTip = driverTip;
			
			
			$('#cust_address_div').show();
			$('#cust_address').text(Order.pref.deliveryAddress.address);
			
			$('#confirm_tip_div').show();
			$('#confirm_delivery_div').show();
			
			$('#confirm_delivery').text(validatePrice(Order.pref.deliveryAddress.delivery));
			$('#confirm_tip').text($('#deliveryTip').val());
			Order.tax = ((parseFloat(Order.subTotal)+parseFloat(Order.pref.deliveryAddress.delivery) - parseFloat(Order.discount))*0.13).toFixed(2);
			
			Order.total = (parseFloat(Order.subTotal) + parseFloat(Order.tax) + parseFloat(Order.pref.deliveryAddress.delivery) + parseFloat(Order.driverTip) - parseFloat(Order.discount)).toFixed(2);
			
			$("#delivery_pref").empty();
			var delivery_pref =  $('<h3 style="text-transform: uppercase;"></h3>').text("DELIVERY");
			
		} else {
			Order.pref.deliveryOption = 0;
			Order.driverTip = 0;
			$('#confirm_delivery_tip_div').hide();
			$('#confirm_delivery_div').hide();
			$('#cust_address_div').hide();
			Order.tax = ((parseFloat(Order.subTotal)-parseFloat(Order.discount))*0.13).toFixed(2);
			
			Order.total = (parseFloat(Order.subTotal) + parseFloat(Order.tax)- parseFloat(Order.discount)).toFixed(2);
			
			
			var delivery_pref =  $('<h3 style="text-transform: uppercase;"></h3>').text("PICK-UP");
		}
		
		$('#confirm_subTotal').text(validatePrice(Order.subTotal));
		$('#confirm_tax').text(validatePrice(Order.tax));
		$('#confirm_total').text(validatePrice(Order.total));
  		
		Order.pref.requestDate = $('#deliveryDate').val();
		Order.pref.requestTime = $('#deliveryTime').val();
		
		//initialize Order Confirm
		
		var user_info, delivery_pref, order_info;
		$('#cust_name').text(User.fname + "  " + User.lname);
		$('#cust_email').text(User.email);
		$('#cust_phone').text(User.phone);
		
		
		
		
		
		
		var inputDate = new Date($('#deliveryDate').val());
		var todayDate = new Date();
		
		if (inputDate.setHours(0,0,0,0) == todayDate.setHours(0,0,0,0)){
			inputDate = "Today";
		} else {
			inputDate = $('#deliveryDate').val()
		}
		
		inputTime =  $('#deliveryTime').val();
		
		var deliver_datetime = $('<h2></h2>').text("Requested for "+inputDate+" @ "+inputTime);
		
		$("#delivery_pref").empty();
		$("#delivery_pref").append(delivery_pref).append(deliver_datetime);
		
		
		var order_info = $('#current_order').clone()
		
		$('#order_info').empty().append(order_info);
		$(order_info).prop("id", "confirm_order_items");
		
		$(order_info).addClass("col-xs-12");
		
		$('#order_info').find('[name="delete_order"]').remove();
		
  		
  		
  		confirm_tax
  		$('#payment_Offline').prop("checked", true);
  		
	  		
  		
  		  	
		$('#online_payments').hide();
		$('#offline_buttons').show();
		
		$('[name = "payment_type"]').change(function(e){
				var paymentType = $("[name=payment_type]:checked").val();
				if(paymentType == "offline"){
					$('#online_payments').hide();
					$('#offline_buttons').show()
				} else {
					
					$('#online_payments').show();
					$('#credit_div').show();
					
					$('#payment_method_div').hide();
					
					$('#orderConfirm').find('.modal-content').scrollTop(10000);
					$('#offline_buttons').hide()
				}
		});
  		
		
		
  		
  		
  	});
  
  	$('#terms_conditions').change(function(){
  		if($('#terms_conditions').is(':checked')){
  			
  			$('#orderProcess').removeAttr('disabled').removeClass("disabled")
  		}else{
  			$('#orderProcess').attr('disabled','disabled').addClass("disabled")
  		}
  	
  	
  	});
  	
	$('#orderProcess').click(function(){
			var payload, callback, target;
			$('#loading_gif').slideDown(200);
			Order.comments = $('#order_comments').val();
			
			payload = Order;
			
			
			var formData = new FormData ();
			formData.append ('order', JSON.stringify(Order));
			if(Order.paymentType == "online"){
				payload.nonce = nonce;
			}
			
			
			callback = function(response) {
				$('#loading_gif').hide();
				
							
				if(response.result== "success"){
					$('#orderConfirm').modal("hide");
					$('#order_success').delay(300).modal("show");
					
				}else if(response.error=="TRANSACT_FAIL") {
					$('#orderConfirm').modal("hide");
					$('#card_error').delay(300).modal("show");
				}else {
					$('#orderConfirm').modal("hide");
					$('#order_error').delay(300).modal("show");
				}
				
			};
			
			// Open the connection.
			target= 'https://www.tastes-good.com/uploader/order.php'
			ajaxRequest(target, payload, callback);
			
			
		});
	
	$('#submit_Order').click(function(){
		initDetails();		
	});
	
	$('#xs_confirm_btn').click(function(){
		initDetails();		
	});
	
	
	
	$('#change_user').click(function(){
		$('#orderDetails').modal('hide');
		$('#userLogin').modal('show').on('hidden.bs.modal', function(e){
			if(User.user_id != 0){
				initDetails();
			}
			$(this).unbind(e);
		});	
	
	});
		
	
	
	$('#dropdown_logout').click(function(){
		//google signout
		//var auth2 = gapi.auth2.getAuthInstance();
		//auth2.signOut()
		
		//facebook signout:
		//FB.logout()
		
		
		$('#mergeDetails').hide();
		$('#mergePassword').hide();
		$('#mergeEmail').hide();
		$('#googPwd').hide();
		$('#googNew').hide();
		$('#main_login').show();
		$('#existing_user_btn').show();
		
		var payload, target, callback;
		
		payload = ""
		callback = function(response) {
			if(response.result=="success"){
			
			$('#navbar_dropdown').fadeToggle();	
				User={user_id:0};
				refreshUser();
			}else{
				console.log(response.error);
			}
		}
	
		target = 'https://www.tastes-good.com/uploader/user_logout.php';
		ajaxRequest(target, payload, callback);
		
			
	});
	$('#dropdown_admin').click(function(){
		window.location='https://www.tastes-good.com/admin.html';	
	});
	
	$('#dropdown_sales').click(function(){
		window.location='https://www.tastes-good.com/salesforce.html';
	});
	
	$('#user_Continue').click(function() {
		initDetails();
	
	});
	
	$('[name = "deliveryOption"]').change(function(e){
				var deliveryOption = $("[name=deliveryOption]:checked").val();
				if(deliveryOption=="pickup"){
					$('#delivery_address').hide();
					$('#driver_tip').hide();
					$('#delivery_address_new').hide();
					$('#delivery_address_buttons').hide();
					
				}
				if(deliveryOption=="delivery"){
					$('#delivery_address').show();
					$('#driver_tip').show();
					$('#delivery_address_buttons').show();
					$('#saveAddress').hide();
					$('#newAddress_Cancel').hide();
					
					
				}
	});
	
	
	$('[name = "payment_type"]').change(function(e){
				Order.paymentType = $("[name=payment_type]:checked").val();
			});
	
	$('[name = "deliveryTip"]').change(function(e){
		 
		var currentTip =  (parseFloat($("[name=deliveryTip]:checked").val())*parseFloat(Order.subTotal)).toFixed(2);
		Order.driverTip = currentTip; 
		
		currentTip = validatePrice(currentTip, currentTip);
		$('#deliveryTip').val(currentTip);
	
	});
	
	$('#change_payment').click(function(){
		$('#payment_method_div').fadeOut(300);
		$('#credit_div').show();
		$('#orderConfirm').find('.modal-content').scrollTop(10000);
		$('#offline_buttons').hide()
		
	
	});	
	
	
    
    $("#getList").click(function(){
       	getList($('#community').val());
    });
    
    $('#hide_pdf_preview').click(function(){
    		hidePDF();
    		$('#User_Orders').modal("show");  		
    });
    
	$('#print_pdf_preview').click(function(){printPDF();});
}
