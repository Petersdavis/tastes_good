//Global Variable Initializations

var hostname = location.hostname
var nonce = {}
var pagename = "order.php";
var Community = {};
var List = [];
var Restaurant = {rest_id:0};

var User = {user_id:0, fb_id:""};

var Order = {}; 
geocoder = new google.maps.Geocoder();
btToken = 0;
OrderTotal = 0
evtChain = [];
theExtra = {};
extra_id = 0;	
active_div = 0;	


  

/*  To do list :  

1. change states  --hide/ show elements
2. create state object  && store data


3. apply coupons
4. handle restaurant open//close


5.After Restoring State:
if(typeof(User)=="object" &&  user.user_id > 0){
		$('#footer_name').val(user.fname + " " +user.lname)
		$('#footer_email').val(user.email)
		$('#dropdown_login').hide();
	}	else{
		$('#dropdown_logout').hide();
	}

*/	

//Local Storage SHIM
if (!window.localStorage) {
  Object.defineProperty(window, "localStorage", new (function () {
    var aKeys = [], oStorage = {};
    Object.defineProperty(oStorage, "getItem", {
      value: function (sKey) { return sKey ? this[sKey] : null; },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "key", {
      value: function (nKeyId) { return aKeys[nKeyId]; },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "setItem", {
      value: function (sKey, sValue) {
        if(!sKey) { return; }
        document.cookie = escape(sKey) + "=" + escape(sValue) + "; expires=Tue, 19 Jan 2038 03:14:07 GMT; path=/";
      },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "length", {
      get: function () { return aKeys.length; },
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "removeItem", {
      value: function (sKey) {
        if(!sKey) { return; }
        document.cookie = escape(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
      },
      writable: false,
      configurable: false,
      enumerable: false
    });
    this.get = function () {
      var iThisIndx;
      for (var sKey in oStorage) {
        iThisIndx = aKeys.indexOf(sKey);
        if (iThisIndx === -1) { oStorage.setItem(sKey, oStorage[sKey]); }
        else { aKeys.splice(iThisIndx, 1); }
        delete oStorage[sKey];
      }
      for (aKeys; aKeys.length > 0; aKeys.splice(0, 1)) { oStorage.removeItem(aKeys[0]); }
      for (var aCouple, iKey, nIdx = 0, aCouples = document.cookie.split(/\s*;\s*/); nIdx < aCouples.length; nIdx++) {
        aCouple = aCouples[nIdx].split(/\s*=\s*/);
        if (aCouple.length > 1) {
          oStorage[iKey = unescape(aCouple[0])] = unescape(aCouple[1]);
          aKeys.push(iKey);
        }
      }
      return oStorage;
    };
    this.configurable = false;
    this.enumerable = true;
  })());
}

function validatePrice(value, placeholder){
		placeholder = placeholder || 0;			
			if(/[\d]/.test(value)){
				var goodvalue = "$"
				goodvalue = goodvalue + /[\d]+[.]{0,1}[\d]{0,2}/.exec(value)[0];
				
				if(!/[.]/.test(goodvalue)){
					goodvalue=goodvalue + ".00"
				}
				if(!/[.][\d]{2}/.test(goodvalue)){
					goodvalue=goodvalue + "0"
					if(!/[.][\d]{2}/.test(goodvalue)){
						goodvalue=goodvalue + "0"	
						
					}
				}
				
				return goodvalue
			} else {return placeholder;}	
}


function calcDistance (pointA, pointB){
	    var radlat1 = Math.PI * pointA.lat/180;
        var radlat2 = Math.PI * pointB.lat/180;
        var radlon1 = Math.PI * pointA.lng/180;
        var radlon2 = Math.PI * pointB.lng/180;
        var theta =  ((1000*pointA.lng)-(1000*pointB.lng))/1000;
        var radtheta = Math.PI * theta/180;
        var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
        dist = Math.acos(dist);
        dist = dist * 180/Math.PI;
        dist = dist * 60 * 1.1515;
        dist = dist * 1.609344 
        return dist
	
}


function checkUser(){
	if(localStorage.getItem("User")!==null){
	   User = JSON.parse(localStorage.getItem("User"));
	}
	
	var xhr = new XMLHttpRequest();
	xhr.onload = function() {
		User = JSON.parse(xhr.response)
		
		if(User.user_id >0){
		
			$('#dropdown_login').hide();
			$('#dropdown_logout').show();
			
		}
		if(User.user_id == 0){
			$('#dropdown_login').show();
			$('#dropdown_logout').hide();
			fb_checkLoginState();
			
		}
		
		localStorage.setItem("User", JSON.stringify(User));
		
		if (User.addresses.length>0 && $('#restaurants').length>0){
			user_sortList(User)	   	   		
		}  	   		
		
	};  
		  
			
	// Open the connection.
	xhr.open('POST', 'https://www.tastes-good.com/api_scripts/user.php', true);
	xhr.send();	
		
}

function fb_checkLoginState() {
    FB.getLoginStatus(function(response) {
      fb_statusChangeCallback(response);
    });
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
		
		if(response.result == "success"){
			goog_details = {};
			User = response.data;
			//hide modal and
			$('#userLogin').modal('hide');
			
			$('#dropdown_login').hide();
			$('#dropdown_logout').show();
			
			
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

function checkState(state){
var cur_time, list, community, restaurant;
cur_time = + new Date();
if(state !== null){
switch(state.page) {
    case "list":
       
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
        
     Restaurant = JSON.parse (localStorage.getItem("restaurant"));
     Community = Restaurant.community;
     Order = {"time":+ new Date(), "rest_id" : Restaurant.rest_id, "user":{}, "pref":{},  "items":[], "driverTip":0, "paymentType":"offline", "discount_rate":0, "coupon":0, "subTotal":0, "total":0};
	OrderTotal = 0
     
     if(Restaurant.timestamp + 172800000 > cur_time){
     	renderRest();
     
     } else {
     	getRest()
     }
             
        break;
       
       
    default:
        getCommunity();   
    
        }
} else {
getCommunity();

}   
//Get user from server.         


}


//Site Specific Functions

function fetchBraintree(){
if(btToken == 0){
	var xhr = new XMLHttpRequest();
	xhr.onload = function() {
		btToken = xhr.response
			
	};  
	  
		
	// Open the connection.
	xhr.open('POST', 'https://www.tastes-good.com/api_scripts/braintree.php', true);
	xhr.send();
}
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
		
		Order.subTotal = (parseFloat(Order.subTotal) + parseFloat(order_Item.price)).toFixed(2);
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
			
		$('#sub_total').text('$'+Order.subTotal);
		
		var cur_discount = 0
		if(Order.discount_rate){
			var cur_discount = parseFloat(Order.subTotal*Order.discount_rate).toFixed(2);
			$('#order_discount').text('$' + cur_discount);
		}
			
			
		
		var tax = parseFloat((Order.subTotal - cur_discount)*0.13).toFixed(2);
		$('#tax').text('$'+tax);	
		var total = (parseFloat(Order.subTotal) + parseFloat(tax) - parseFloat(cur_discount)).toFixed(2);
		$('#total').text('$'+total);	
		$('#xs_total').text('$'+total);
			
			
		
		$(button).click({id:order_Item.id, li:$(li)}, function(event){
			$(li).remove();
			removeByAttr(Order.items, 'id', event.data.id);				
			
			if(Order.items.length==0){
					$('#order_placeholder').show();
			}
			
			var subtotal = 0;
			
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
			$('#sub_total').text('$'+Order.subTotal);
			var cur_discount = 0
			if(Order.discount_rate){
				var cur_discount = parseFloat(Order.subTotal*Order.discount_rate).toFixed(2);
				$('#discount').text('$' + cur_discount);
			}
			var tax = parseFloat((Order.subTotal - cur_discount)*0.13).toFixed(2);
			$('#tax').text('$'+tax);	
			var total = (parseFloat(Order.subTotal) + parseFloat(tax) - parseFloat(cur_discount)).toFixed(2);
			$('#total').text('$'+total);	
			$('#xs_total').text('$'+total);
		
		
		});
		
		
		//clear the extra
		$('#ExtraModal').modal('hide');	
		
		
		
		}
							 
		
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
		
		
		Order.subTotal = (parseFloat(Order.subTotal) + parseFloat(extra.selected[y].price)).toFixed(2);
		
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

function addAddress(address, target){
	var fullAddress = ""
	var location = {"lat":address.lat, "lng":address.lng};
	
	
	
	if(address.appt !== "na"){
		fullAddress = address.appt + "-"+ address.address;
	}else{fullAddress = address.address}
	
   var div = $('<div>', {class:"list-group-item row", id:'address_'+address.id});
   
   var input = $("<input>", {type:"radio", name:"Order_Address", id:"address_radio_"+address.id, value:address.id});
   var label = $("<label>", {for:"address_radio_"+address.id}).append(input).append(fullAddress);
   var address_div = $('<div>', {class:"col-xs-10"}).append(label);
   
   
   var span = $("<span>", {class:"glyphicon glyphicon-trash"});
   var delete_button = $('<button>', {class:"col-xs-2 btn"}).append(span)
   $(delete_button).on("click", {'address_id':address.id}, function(e){
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
		xhr.open('POST', 'https://www.tastes-good.com/uploader/deleteAddress.php', true);
		xhr.send(formData);	
		
		
		$('#address_'+e.data.address_id).remove();
   });
   
   $(div).append(address_div).append(delete_button)
   
   if (address.comment){
   	   var details = $('<div>', {class:"col-xs-12"}).text(address.comment);
   	   $(div).append(details)
   }
   
   //fix the if statement
   if(history.state.page== "order"){
           var geoBounds = {lat:Restaurant.lat ,lng:Restaurant.lng}
   	   var distance = calcDistance(location, geoBounds);
		distance = Math.round(10*distance)/10
		
		
		var delivery = 4 + (Math.floor(distance/0.25) * 0.2);
		
		
		for(y=0;y<User.addresses.length;++y){
			   if(User.addresses[y].id ==  address.id){
			   User.addresses[y].distance = distance
			   User.addresses[y].delivery = delivery
			   }
		}
	
		delivery = validatePrice(delivery, delivery);
	
   	   
	   var distance = $('<div class = "col-xs-12">Distance to Restaurant: '+distance+' km Delivery Charge: '+delivery+'</div>');
	   $(div).append(distance);
   }
   
   $(target).append(div);
   
   
  
  
   $('#delete_address_'+address.id)
   
  
} 


function sendEmail (){
		var formData = new FormData ();
		
		formData.append ('name', $('#footer_name').val());
		formData.append ('email', $('#footer_email').val());
		formData.append ('comment', $('#footer_message').val());
		formData.append ('reason', $('#footer_reason').val());
		formData.append ('rest_id', Restaurant.rest_id);
		
				
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
		xhr.open('POST', 'https://www.tastes-good.com/uploader/submitComment.php', true);
		xhr.send(formData);
		
	
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
		List.sort(function(a, b){return a.distance-b.distance});
					
		if($("#restaurants").length > 0 ){
			for(x=0;x<List.length; ++x){						
				$('#restaurants').append($('#rest_'+List[x].rest_id))
			
			}
		}
		
	}
	
}



function restLogin(){		
	
		var formData = new FormData ();
		var user_name = document.getElementById("username").value			
		var user_pwd = document.getElementById("password").value	
		
		formData.append ('user_name', user_name);
		formData.append ('user_pwd', user_pwd);
		formData.append ('redirect', "dashboard");
				
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			
			
			if (xhr.response=="false"){
				$('#failedLogin').show();
				//TESTING:
				
					
				
			}else{
				//handle failed login
				
				console.log(xhr.response);
					
			}
			
	
		};
	
	
		// Open the connection.
		xhr.open('POST', 'login.php', true);
		xhr.send(formData);
		
	
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

function transitions (target){
  if(active_div==0){
	$(target).show(250);
	active_div = $(target); 
  }else{ 
   
  $(active_div).hide(0);
  $(target).show(250);
  active_div = $(target);
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
 	cur_time = + new Date();
 	
 	if (localStorage.getItem("communities") !== null) {
            communities = localStorage.getItem("communities")
            if(communities.timestamp + 86400000 > cur_time){
            	communities = communities.communities
            	renderCommunity()
            }
        }

	var xhr = new XMLHttpRequest();
	
	xhr.onload = function() {
		if (xhr.response.length>0){
			if(communities = JSON.parse(xhr.response)){
			 
			 localStorage.setItem("communities",JSON.stringify(communities));
			 renderCommunity();
			 
			 			
			} 
			
				
			
		}else{
			//NO COMMUNITIES
			console.log(xhr.response);
		 	
		
		}
		
	
	};
	
	
	// Open the connection.
	xhr.open('POST', 'https://www.tastes-good.com/api_scripts/communities.php', true);
	xhr.send();
}


function getList(community){
	
	
	if(Community.community !== community){
	
	for(x=0;x<communities.length;++x){
	  if(communities[x].community == community){
	    Community = communities[x];
	    break;
	   }
	 }
	}  
		
	var xhr = new XMLHttpRequest();
	xhr.onload = function() {
		
		if (xhr.response.length>0){
			if(List = JSON.parse(xhr.response)){
			
			
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
			history.pushState(state, Community.community);
			
						 			
			} 
			
				
			
		}else{
			//NO Restaurants
			console.log(xhr.response);
		 	
		
		}
		
	
	};
	
	
	// Open the connection.
	var url="https://www.tastes-good.com/api_scripts/restaurants.php?community="+community;
	xhr.open('POST', url, true);
	xhr.send();
}

function renderList(){
	var frag, title, address, open, delivery, img, col1, col2, div, anchor, li, h2, button, span;
	 $('#restaurants').empty();
				
	 $('[name="active_community"]').text(Community.community);
	 $('#rest_count').text(List.length);
	 
	  frag = document.createDocumentFragment();
	 
	 for(x=0; x<List.length;++x){
		  title = $("<h3>"+List[x].title+"</h3>");
		  address = $("<h2>"+List[x].address+"</h2>");
		  open = $("<h2></h2>").append("<strong>Open: </strong>" + List[x].open_time).append("<strong style='margin-left:10px;'>Close: </strong>"+List[x].close_time);
		  delivery = $("<strong>Delivery Charge</strong>").append(validatePrice(List[x].delivery_base)); 
		  img= $("<img>", {class:"image-rounded", height:100, width:100, src:List[x].image});
		  col1 = $("<div></div>").addClass("col-xs-4").append(img);
		  col2 = $("<div></div>").addClass("col-xs-8").append(title).append(address).append(open).append(delivery);
		  
		  var div = $("<div></div>").addClass("row").append(col1).append(col2);
		  
		  var anchor = $("<a></a>").addClass("btn btn-block").click({restaurant:List[x]}, function(event){
		     Restaurant = event.data.restaurant;
		     getRest();
		  
		  }).append(div);
		  
		  if(List[x].open==0){
		  	$(anchor).attr("disabled","disabled").addClass("disabled");
		  }
		  
		  
		  var li = $("<li>", {class:"list-group-item", id:"rest_"+List[x].rest_id}).addClass("list-group-item").append(anchor);
		  
		  			
		
		  $(frag).append(li);
	 
	 }
	 
	  $('#restaurants').append(frag);
	  
	 frag = document.createDocumentFragment();
	  
	 for(x=0; x<restaurants.length;++x){
		 if(restaurants[x].open==0){
			  for(y=0; y<restaurants[x].coupons.length; ++y){
			  if(restaurants[x].coupons[y].public){
			  
				  img = $("<img>", {style:"position:absolute; top:0; left:0; width:175px; height:100px;", src:"/images/biz_card_blank.png"});
				  title = $("<h2>", {style:"position:absolute; top:0px;line-height:15px; left:5px;font-size:15px; width:160px; text-align:center;"})
				  
				  if(restaurants[x].coupons[y].type=="item"){
				   span =$("<span>",{style:"color: #633E26;"}).text("$"+restaurants[x].coupons[y].price);
				   $(title).append(span);		
				  }
				  
				  code=$("<h3>", {style:"position:absolute; top:55px; margin-top:0; left:67px;font-size:15px; width:100px; text-align:center;"}).append(restaurants[x].coupons[y].code)
				  h2= $("<h2>", {style:"position:absolute; top:35px; margin-top:0; left:30px;font-size:15px; width:175px; text-align:center;"}).append("Coupon Code:");
				  
				  					  
				  button = $("<button>", {style:"position:relative;height:105px;", class:"btn btn-block"}).append(img).append(title).append(code).append(h2);
				  $(button).click({coupon:restaurants[x].coupons[y], restaurant:restaurants[x]}, function(event){
				   Restaurant = event.data.restaurant;
				   getRest();
				   
				   Order.coupon = event.data.coupon
				   applyCoupon();
				  });
				 
				  div = $("<div>", {class:"col-xs-6 col-sm-4"}).append(button)
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
	$('#rest_title').text(Restaurant.title);
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
		price=$("<div>", {class:"col-xs-3 no-padding"}).text(validatePrice(item.price));
		button2 = $("<button>", {class:"btn col-xs-3 no-padding"}).text("Order It").click({item:item.id}, function(event){
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
		

}

function getRest(){
var rest_id = Restaurant.rest_id;

var xhr = new XMLHttpRequest();
xhr.onload = function() {
	var frag, li, button, ul, li2, row1, row2, product, price, button2, description;
	if (xhr.response.length>0){
		if(Restaurant = JSON.parse(xhr.response)){
					
				
		Order = {"time":+ new Date(), "rest_id" : Restaurant.rest_id, "user":{}, "pref":{},  "items":[], "driverTip":0, "paymentType":"offline", "discount_rate":0, "coupon":0, "subTotal":0, "total":0};
		OrderTotal = 0
		
		renderRest();
					
		//save state
		
		state = {page:"order", rest_id:Restaurant.rest_id}
		history.pushState(state, Restaurant.title)
		Restaurant.timestamp = + new Date();
		Restaurant.community = Community;
		
		localStorage.setItem('restaurant', JSON.stringify(Restaurant));
		
		
		}
	}

}
// Open the connection.
var url="https://www.tastes-good.com/api_scripts/menu.php?rest_id="+rest_id;
xhr.open('POST', url, true);
xhr.send();

}

function validateEmail() {
	$('#submit_newUser').text("Validating...").prop('disabled', true);
	$('#invalid_email').hide();
	var formData = new FormData ();
			
	formData.append ('email', $('#newUserEmail').val());
	
	if(User.fb_id.length > 0){
	formData.append ('fb_id', User.fb_id );
	}
							
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
	if(Order.coupon.type == "discount"){
 	 	 
 	 	 Order.discount_rate = Order.coupon.discount;
 	 	 //Order.discount
 	 	 
 	 	 //Show discount div. 
 	 	 
 	 	 
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



document.addEventListener("DOMContentLoaded", function(event) {
	window.fbAsyncInit = function() {
	  FB.init({
	    appId      : '1890437671201988',
	    cookie     : true,  // enable cookies to allow the server to access 
	                        // the session
	    xfbml      : true,  // parse social plugins on this page
	    version    : 'v2.8' // use graph api version 2.8
	  });
	}
	

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
      
    //initialize errors:
	$('#invalid_email').hide();
	$('#email_noexist').hide();
	$('#short_password').hide();	
	$('#email_sent').hide();
      
    //initialize modals: 
    $('#userNew').hide();
	$('#forgotPwd').hide();
	
	//initialize datepicker
	
	$('#deliveryDate').datepicker({ hideIfNoPrevNext: true, minDate:-0, maxDate:"+7D"}).datepicker( "setDate", "+0" ).datepicker( "hide" );

	$(".ui-datepicker-current").remove();
	$('#ui-datepicker-div').hide();
	
	//timepicker
    $('.timepicker').wickedpicker();
      
    //initialize buttons 
    	
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
			
			if(response.result == "success"){
				User = response.data;
				//hide modal and
				$('#userLogin').modal('hide');
				
				//update menu's
				$('#dropdown_login').hide();
				$('#dropdown_logout').show();
				
				
				//page specific functions
				if($('#restaurants').length>0 && User.addresses.length>0){
					user_sortList(User);
				}
				
				
					
			
			
			} else if (response.error == "pwd_wrong"){
				alert ("wrong password");
			} else if (response.error == "no_user"){
				alert ("user id not found");
			}	    
				
		};
	
	
		// Open the connection.
		xhr.open('POST', 'https://www.tastes-good.com/uploader/userLogin.php', true);
		xhr.send(formData);
		
			
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
  		
  		$('#confirm_rest_title').text(Restaurant.title)
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
			$('#loading_gif').slideDown(200);
			Order.comments = $('#order_comments').val();
			var formData = new FormData ();
			formData.append ('order', JSON.stringify(Order));
			if(Order.paymentType == "online"){
				var Nonce_json = JSON.stringify(nonce);
				formData.append ('nonce', Nonce_json);
			}
			
			
									
			var xhr = new XMLHttpRequest();
			
			xhr.onload = function() {
				$('#loading_gif').hide();
				
				
				console.log(xhr.response);
				ret = JSON.parse(xhr.response)
				
				if(ret.result== "success"){
					$('#orderConfirm').modal("hide");
					$('#order_success').delay(300).modal("show");
					
				}else if(ret.result=="fail" && ret.error=="TRANSACT_FAIL") {
					$('#orderConfirm').modal("hide");
					$('#card_error').delay(300).modal("show");
				}else {
					$('#orderConfirm').modal("hide");
					$('#order_error').delay(300).modal("show");
				}
				
				
				
				
			};
			// Open the connection.
			xhr.open('POST', 'https://www.tastes-good.com/uploader/order.php', true);
			xhr.send(formData);	
			
			
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
	
	
	$('#dropdown_btn').click(function(){
		$('#navbar_dropdown').fadeToggle();	
	})
	$('#dropdown_login').click(function(){
		$('#userLogin').modal('show');
		$('#userNew').hide();
		$('#navbar_dropdown').fadeToggle();	
	});
	
	$('#dropdown_logout').click(function(){
		//google signout
		var auth2 = gapi.auth2.getAuthInstance();
		auth2.signOut()
		
		//facebook signout:
		FB.logout()
		
		
		$('#mergeDetails').hide();
		$('#mergePassword').hide();
		$('#mergeEmail').hide();
		$('#googPwd').hide();
		$('#googNew').hide();
		$('#main_login').show();
		$('#existing_user_btn').show();
		
		
		var formData = new FormData ();
		var xhr = new XMLHttpRequest();
		
		xhr.onload = function() {
			$('#navbar_dropdown').fadeToggle();	
			User={user_id:0};
			$('#dropdown_logout').hide();
			$('#dropdown_login').show();
			
			
		}
	
		xhr.open('POST', 'https://www.tastes-good.com/uploader/user_logout.php', true);
		xhr.send(formData);	
		
			
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
  
    
     //page load function
     if (location.protocol != 'https:')
	{
	 
	 location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
	} 
      
         
      
      checkUser();
         	
      checkState(history.state);
      
      
      
    
});