function BoilerplateInit (){
	geocoder = new google.maps.Geocoder();		
}

function localStorageShim(){
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
}}


function makeToast(title, content){

	$("#toast_title").text(title);
	$("#toast_content").empty().append(content);	
	$("#toast").modal("show");	
}

function validatePrice(value, placeholder){
		placeholder = placeholder || 0;			
			if(/[\d]/.test(value)){
				var goodvalue = "$"
				
				if(value < 0){
					goodvalue = goodvalue + "-";
				}
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


function ajaxRequest(target, payload, callback){
	var formData, xhr;
	
	xhr = new XMLHttpRequest();
	
	if(typeof callback == "function"){
		xhr.callback =  callback;	
	}else{
		xhr.callback = function(){};
	}
		
	xhr.onload = function(){
		if(data = JSON.parse(xhr.response)){		
			xhr.callback(data);
		}else{
			console.log(xhr.response);
		}
	}
	
	if(typeof payload == "object"){
		formData = new FormData ();
		formData.append('data', JSON.stringify(payload));
		xhr.open('POST', target, true);
		xhr.send(formData)
	}else{
		xhr.open('POST', target, true);
		xhr.send()
	}
}


function setState(state){
	if(history.state){
		if(history.state.page != state.page){
		history.pushState(state, "www.Tastes-Good.com")	;
		}
		
	}else{
		history.pushState(state, "www.Tastes-Good.com");	
	}
		
}


function setupPush(){
	if(window.isphone){
		console.log('calling push init');
		push_handler = PushNotification.init({
			"android": {
				"senderID": "1042635026617"
			},
			"browser": {},
			"ios": {
				"sound": true,
				"vibration": true,
				"badge": true
			},
			"windows": {}
		});
		console.log('after init');
	
		push_handler.on('registration', function(data) {
			console.log('registration event: ' + data.registrationId);
	
			var oldRegId = localStorage.getItem('pushId');
			if (oldRegId !== data.registrationId) {
				// Save new registration ID
				localStorage.setItem('pushId', data.registrationId);
				// Post registrationId to your app server as the value has changed
			}
				
		});
	
		push_handler.on('error', function(e) {
			console.log("push error = " + e.message);
		});
	
		push_handler.on('notification', function(data) {
			console.log('notification event');
			navigator.notification.alert(
				data.message,         // message
				null,                 // callback
				data.title,           // title
				'Ok'                  // buttonName
			);
	   });
	}else{
		
	 $.getScript("https://www.gstatic.com/firebasejs/3.7.8/firebase.js", function() {
			var config = {
			apiKey: "AIzaSyBrKj_cSq4OleEkcdZjuM0iP_Yf_AeLZBk",
			authDomain: "tastes-good-7599.firebaseapp.com",
			databaseURL: "https://tastes-good-7599.firebaseio.com",
			projectId: "tastes-good-7599",
			storageBucket: "tastes-good-7599.appspot.com",
			messagingSenderId: "1042635026617"
		  };
			 firebase.initializeApp(config);
			firebase_instance = firebase.messaging();
			//Documentation for JS client
			//https://firebase.google.com/docs/cloud-messaging/js/client
		});
		
	}
}

function formatDate(date_string, style){
	var style = style || 1;
	var d, str;
    // "2010-10-30T00:00:00+05:30".
	d = date_string.slice(0, 10).split('-');   
    str = d[1] +'/'+ d[2] +'/'+ d[0].slice(2,4); // 10/30/2010
    return str;
}


function calcDistance(pointA, pointB){
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

function sendEmail (){
		var payload, target, callback;
		var rest_id;
		rest_id = Restaurant.rest_id || 0;
		payload = {name:$('#footer_name').val(), email:$('#footer_email').val(), comment:$('#footer_message').val(), reason:$('#footer_reason').val(), rest_id:rest_id};
		callback = function (response){
			if (response.result=="success"){
				$('#Contact').modal('hide').delay(250);
				$('#Success').delay(250).modal('show')
			}else{
				alert(response.error);
			}
		}
		target = "https://www.tastes-good.com/uploader/submitComment.php";
		ajaxRequest(target, payload, callback);
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


function upTo(el, tagName) {
  tagName = tagName.toLowerCase();

  while (el && el.parentNode) {
    el = el.parentNode;
    if (el.tagName && el.tagName.toLowerCase() == tagName) {
      return el;
    }
  }
}

function readURL(input) {
	if (input.files && input.files[0]) {
		
		if(input.files[0].size < 2500000){
		 $('#file_size_error').hide();		
		var reader = new FileReader();
		reader.onload = function (e) {
			$('#image_preview').attr('src', e.target.result);
			$('#new_image_preview').attr('src', e.target.result);
		}		
		reader.readAsDataURL(input.files[0]);
		$('#save_details').prop("disabled", false);
		requestRestSave()
		
		}else{
		 $('#file_size_error').show();	
		 $('#save_details').prop("disabled", true);	
		}
	}
}

function hidePDF(){
	$('html, body').css({
		overflow: 'auto',
		height: 'auto'
	});
	$("#pdf_preview")[0].contentWindow.location.replace("")
	$("#pdf_preview").hide();
	$("#pdf_preview_btns").hide();
}
function printPDF(){
	window.frames["pdf_preview"].focus();
	window.frames["pdf_preview"].print();
}


function addAddress(address, target){
	var fullAddress = ""
	var location = {"lat":address.lat, "lng":address.lng};
	
	
	
	if(address.type == "appt"){
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
   	  
	   for(y=0;y<User.addresses.length;++y){
	   	   if(User.addresses[y].id ==  e.data.address_id){
	   	   	   User.addresses.splice(y, 1); 
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
} 

function showText(address){
	showPDF(address)
	$("#order_review").hide();
	$("#order_repeat").hide();
	$("#hide_pdf_preview").off("click").on("click", function(){
			hidePDF();
			$("#order_review").show();
			$("#order_repeat").show();
	});
	
}
function showPDF(link){
	window.scrollTo(0, 0)
	$('html, body').css({
		overflow: 'hidden',
		height: '100%'
    });
	
	$("#pdf_preview")[0].contentWindow.location.replace(link)
	$("#pdf_preview").show();
	$("#pdf_preview_btns").show();
}

String.prototype.shuffle = function () {
    var a = this.split(""),
        n = a.length;

    for(var i = n - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var tmp = a[i];
        a[i] = a[j];
        a[j] = tmp;
    }
    return a.join("");
}


function refreshUser(){
	
		if(User.user_id >0){
			$('#dropdown_login').hide();
			$('#dropdown_btn').show();
			
			$('#top_user_name').text(User.fname + " " + User.lname);
			$('#top_credit').text(validatePrice(User.credit));
			
			$('#dropdown_btn').off("click").on("click", function(){
				$('#navbar_dropdown').fadeToggle();	
			});
			$('#dropdown_login').off("click");
			
			$('#userLogin').modal('hide');
			$("#show_prospects").show();
		}
		
		if(User.user_id == 0){
			$('#dropdown_btn').hide();
			$('#dropdown_login').show();
			$('#dropdown_btn').off("click")
			$("#show_prospects").hide();
			$('#dropdown_login').off("click").on("click", function(){
				$('#userLogin').modal('show');
				$('#userNew').hide();
				$("#wrong_pwd").hide();
				$("#not_rest").hide();
				$("#email_noexist").hide();
				$("#email_sent").hide();
				$("#forgotPwd").hide();
				
			});
		
			User.addresses = [];
						
			//fb_checkLoginState();
		}
		
		if(User.is_sales){
			$('#dropdown_sales').show();
			$('#navigator_sales').show();
		}else{
			$('#dropdown_sales').hide();
			$('#navigator_sales').hide();
		}
		
		if(User.is_admin){
			$('#dropdown_admin').show();
			
		}else{
			$('#dropdown_admin').hide();
			
		}
		
		if(User.is_rest){
			var x, rest, button, frag;
			$('#dropdown_rest').show();
		
			
			frag = document.createDocumentFragment();
			for(x=0;x<User.restaurants.length;++x){
				rest = User.restaurants[x];
				button = $("<button>", {class:"btn btn-default btn-block"}).append("Manage: "+rest.title).on("click", {rest:rest}, function(e){
					window.location = "https://www.tastes-good.com/dashboard.html?manage#"+e.data.rest.rest_id;	
				});
				
				button2 = $("<button>", {class:"btn btn-default btn-block"}).append("Go To: "+rest.title).on("click", {rest:rest}, function(e){
					window.location = "https://www.tastes-good.com/main.html?coupon#"+e.data.rest.rest_id +"$0000";	
				
					getRest();				
				});
				
				$(frag).append(button).append(button2);
			}
			$("#dropdown_rest").empty().append(frag);
				
		}else{
			$("#dropdown_rest").empty()
		}
		
		
		if (User.addresses.length>0 && $('#restaurants').length>0){
			user_sortList(User)	   	   		
		}  	  
	
}


function tokenLogin(token, callbackFunction){
	var payload, target, callback;
	payload = {token:token};
	
	callback = function(response){
		if(response.result == "success"){
			User = response.data;
			if(typeof(this.callback.callbackFunction) == "function"){
				this.callback.callbackFunction();
			}
		}else{
			switch (response.error){
			 case "expired_token":
			 	 alert("Access Token  expired: Please login.");
			 	 break;
			 case "bad_token":
			 	 alert("Access Token used or invalid: Please login.");
			 	 break;
			 default:
			 	  alert("Access Token Failed:  Please Login.");
			 }
			 
			initRestLogin();
			transitions($("#rest_login"));
		}
		
		
	}
	callback.callbackFunction = callbackFunction || null;
	
	target = "https://www.tastes-good.com/api_scripts/token_login.php";
	ajaxRequest(target, payload, callback);
	
}


function saveAddress(address, callback) {
	var community = {};
	
	if(typeof(Community)=="object"){
		community = Community;
	}
		
 	payload = {address:address, community:community}
 	
 	target = "https://www.tastes-good.com/uploader/saveAddress.php" 
 	ajaxRequest(target, payload, callback);
 	
	
}