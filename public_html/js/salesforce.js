
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
    	document.addEventListener('deviceready', onDeviceReady, false);
    } else {
    	onDeviceReady();
    }
});


function onDeviceReady() {
		
	window.onpopstate = function(event) {
	 checkUser();
	 
	};
	
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
	
	initGlobalVars();
	localStorageShim();	
    initErrors();
    checkUser();
   
    initButtons();
    initModals();
    initKeystrokes();
    setupPush();
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

function initGlobalVars(){
	hostname = location.hostname
	Community = {};
	List = [];
	Restaurant = {rest_id:0};
	User = {user_id:0, fb_id:""};
	active_div = 0;
}
function checkHash(){
	
	return false;
}

function checkState(state){
	var cur_time, list, community, restaurant;
	cur_time = + new Date();
	
	if(window.location.hash.length>0|| window.location.search.length>0){
		if(checkHash()){
			return;	
		}
	}
	
	if(state !== null){
	
	switch(state.page) {
		case "tutorial":
			transitions($("#tutorial"));
			break;
		case "resources":
			transitions($("#resources"));
			break;
		case "select_community":
			getCommunity();
			break;
		case "prospects":
			communities = JSON.parse(localStorage.getItem("communities"));
			getProspects(state.community);
			break;
		case "active":
			getMyProspects();
			break;
		
		   	   
		default:
			loadDashboard();
		
			}
	} else {
	loadDashboard();
	
	}   
//Get user from server.         
}
function loadDashboard(){
	transitions($('#dashboard'));	
}



function initButtons(){
	$("#province").on("change", function(){refreshCommunity();});
	 
    $("#getList").click(function(){	getProspects($('#community').val());});
    
    //dashboard:
	$("#dash_help").click(function(){
			var state;
			transitions($("#tutorial"));
			state = {page:"tutorial"}
			setState(state)
	});
	$("#dash_prospect").click(function(){getCommunity();});
	$("#dash_active").click(function(){	getMyProspects();});
	$("#dash_resource").click(function(){
			var state
			transitions($("#resources"));
			state = {page:"resources"}
			setState(state)
	});
	
	
	//login:
			
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
	
	$('#submit_login').click(function() {
		var payload, target, callback;
		
		//verify that email does not already exist in local storage.  
		
		var payload = {'email':$('#userId').val(), 'password':$('#userPwd').val()};	
		
		callback = function(response){
			if(response.result == "success"){
				User = response.data;
				if(User.is_sales){
					refreshUser();
					refreshDashboard();
					checkState(history.state);
					//update menu's
					
					//refresh errors
					$("#wrong_pwd").hide();
					$("#no_sales").hide();
										
				} else {
					transitions($("#userLogin"));
					$("#no_sales").show();
				}
			}else{
				transitions($("#userLogin"));
				$("#wrong_pwd").show();
			}
		}
		// Open the connection.
		target = "https://www.tastes-good.com/uploader/userLogin.php"
		ajaxRequest(target, payload, callback);
	});
	
	//logout
	
	$("#dropdown_logout").on("click", function(){
			var target, payload, callback;
			
			callback = function(){
				window.location = "main.html"
			}
			
			payload = "";
			target = "https://www.tastes-good.com/uploader/user_logout.php";
			ajaxRequest(target, payload, callback);
			
	});
	
	//dropdown menu
	$('#dropdown_btn').click(function(){
		$('#navbar_dropdown').fadeToggle();	
	})
	
	//claim prospects
	$("#prospect_cancel").click(function(){
		$("#confirm_prospect").modal("hide");
		$("#claim_prospect").modal("show");
	});
	
	$("#prospect_claim").click(function(){
		$("#claim_prospect").modal("hide");
		$("#confirm_prospect").modal("show");
	});
	
	
	$("#new_prospect").click(function(){
		$("#new_restaurant").modal("show");	
	});
	
	$("#prospect_new").click(function(){
		var target, payload, callback;
		var pattern;
		var address;
		
		address = $("#new_address").val()
		if(address.search(Community.community)==-1){address = address + ", " +Community.community;}
		if(address.search(Community.province)==-1 && address.split(",").length < 3){address = address + ", " +Community.province;}
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
				$("#new_restaurant").modal("hide");	
				$("#successMsg").text("Data Received! After we review the restaurant details they will be invited to join Tastes-Good.com.");
				$("#Success").modal("show");	
				
			}else{
				alert(response.error);
			}
			
		}
		
		target="https://www.tastes-good.com/uploader/sales_new_prospect.php";
		ajaxRequest(target, payload, callback);
	});
	
	
}

function initModals(){
	//login
	$("#forgotPwd").hide();
	$("#existing_user_btn").show()
}

function initErrors(){
	//user_login:
	$('#invalid_email').hide();
	$('#email_noexist').hide();
	$('#email_sent').hide();
	$('#wrong_pwd').hide();
	$('#no_sales').hide();
	
}

function initKeystrokes(){
}


function checkUser(){
	var callback = function(response){
		if(response.result == "success"){
			User = response.data
			
		if(User.user_id >0 && User.is_sales){
		
			refreshUser();
			checkState(history.state);
			getSalesTotals();
					
		}else{
			transitions($("#userLogin"));
			
		}
		}else{
			alert(response.error);
		}
	}
	
	var target = "https://www.tastes-good.com/api_scripts/user.php";
	
	if(User.user_id >0 && User.is_sales){
		refreshUser();
		checkState(history.state);
		getSalesTotals();
	}else{
		ajaxRequest(target, "", callback);
	}
}

function getSalesTotals(){
	var payload, callback, target;
	payload = ""
	
	callback = function(response){
	 	if(response.result == "success"){
	 		User.active = response.data.active;
	 		User.prospect = response.data.prospect;
	 		User.customer = response.data.customer;
	 		refreshDashboard();
	 		
	 	}else{
	 		alert(response.error);
	 	}
	}
	
	target = "https://www.tastes-good.com/api_scripts/sales_summary.php";
	ajaxRequest(target, payload, callback);
}

function refreshDashboard(){
		$('#sales_active').text(User.sales.active);
		$('#sales_customer').text(User.sales.customer);
		$('#sales_prospect').text(User.sales.prospect);
		$('#sales_credit').text(validatePrice(User.credit));
}

function getCommunity(){
	var target, callback;
 	var cur_time = + new Date();
 	
 	if (localStorage.getItem("communities") !== null) {
		communities = localStorage.getItem("communities")
		if(communities.timestamp + 86400000 > cur_time){
			renderCommunity()
			
			return;
		}
	}
	var callback = function(response){
		if (response.result == "success"){
			 communities = response.data
			 communities.timestamp = +new Date();
			 localStorage.setItem("communities",JSON.stringify(communities));
			 renderCommunity();
		}else{
			//NO COMMUNITIES
			console.log(response);
			renderCommunity();
		 }
		
	}
	
	target = "https://www.tastes-good.com/api_scripts/communities.php?inactive=1";
	
	ajaxRequest(target, "", callback);
	
}

function refreshCommunity(){
	$('#community').empty();
	
	for(x=0; x<communities.length;++x){
		if(communities[x].province == $("#province").val()){
		$('#community').append("<option value='"+communities[x].community+"'>"+communities[x].community+"</option>");
		}
	}
	
	if(communities.length == 0){
		$('#community').append("<option value=''>No Communities Found</option>");
	}
}


function renderCommunity(){
	refreshCommunity();
	transitions($('#select_community'));
	var state = {page: "select_community"};
	setState(state);	
}


function getProspects(community){
	var target, data, callback;
	if(Community.community !== community){
	
	for(x=0;x<communities.length;++x){
	  if(communities[x].community == community){
	    Community = communities[x];
	    break;
	   }
	 }
	}  
	
	callback = function(response){
		if(response.result=="success"){
			
		Prospect = response.data;
		Prospect.sort(function(a, b){if(b.title > a.title){return -1}else{return 1}});
		
		renderProspects();
		var state = {page: "prospects", community:Community.community};
		setState(state);
		transitions("#select_prospect");
		
		}else{
			console.log(response.error);
		}
	}
	
	target = "https://www.tastes-good.com/api_scripts/open_prospects.php?community="+community;
	ajaxRequest(target, "", callback);
		
}


function renderProspects(){
 var frag, x, str, title, details, address, phone, email, button;
	 $('#prospects').empty();
				
	 $('[name="active_community"]').text(Community.community);
	 $('#rest_count').text(Prospect.length);
	 
	 frag = document.createDocumentFragment();
	 
	 for(x=0;x<Prospect.length;++x){
	   if(Prospect[x].status == "NEW"){
	   	   
	   	   str = Prospect[x].title + "---STATUS: AVAILABLE"
	   	   title = $("<div>").append(str)
	   	   address = $("<div>").append(Prospect[x].address)
	   	   
	   	   button =  $("<button>", {class:"btn btn-block btn-primary"}).append(title).append(address).on("click", {prospect:Prospect[x]}, function(e){
	   	   		initClaimProspect(e.data.prospect);	   
	   	   });	   
	   	   	   	   	   	
	   }else if(Prospect[x].status == "PROSPECT"){
	   	    if(Prospect[x].user_id == User.user_id){
	   	    	str =  Prospect[x].title + "---STATUS: YOUR PROSPECT (expires:" +Prospect[x].expires+")"; 
	   	    } else {
	   	    	str =  Prospect[x].title + "---STATUS: ANOTHER AGENT'S PROSPECT (expires:" +Prospect[x].expires+")"; 	   	    
	   	   }
	   	   title = $("<div>").append(str)
	   	   address = $("<div>").append(Prospect[x].address)
	   	   
	   	   button =  $("<button>", {class:"btn btn-block btn-warning"}).append(title).append(address);
	   	    
	   	    
	   	  	   	    
	   	} else {
	   		
	   	   str = Prospect[x].title + "---STATUS: ACTIVE RESTAURANT"
	   	   title = $("<div>").append(str)
	   	   address = $("<div>").append(Prospect[x].address)
	   	   
	   	   button =  $("<button>", {class:"btn btn-block btn-success"}).append(title).append(address)
	   			   	 
	   	}
	   	  
	   	$(frag).append(button);  
	    
	 }
	 
	 $("#prospects").empty().append(frag);
	 transitions($("#select_prospect"));
}

function initClaimProspect(prospect){
	$("#prospect_address").text(prospect.address);
	if(typeof(prospect.phone) == "object"){
		lookupPhone(prospect)	
	}else if(prospect.phone == 0){
		$("#prospect_phone").text("unknown");
		$("#prospect_phone_input").show();
	}else{
		$("#prospect_phone_input").hide();
		$("#prospect_phone").text(prospect.phone);
	}
	
	
	$("#prospect_confirm").off("click").on("click", {prospect:prospect}, function(e){
			claimProspect(e.data.prospect);
	});
	
	$("#save_prospect_phone").off("click").on("click", {prospect:prospect}, function(e){
			savePhone(e.data.prospect);		
	});
	
	$("#claim_prospect").modal("show");
	
}

function claimProspect(prospect){
	var target, payload, callback;
	
	var pattern = /@/
			
	if(!pattern.test( $("#prospect_email").val())){
		alert("invalid Email address");
		return;
	}
	
	callback = function(response){
		var x, prospect;
		
		if(response.result == "success"){
			$("#confirm_prospect").modal("hide");
			
			for(x=0;x<Prospect.length;++x){
			 	if(Prospect[x].rest_id == this.rest_id){
			 		prospect = Prospect[x];
			 		Prospect[x].status = "PROSPECT";
			 		Prospect[x].user_id = User.user_id;
			 		renderProspects();
			 		$("#prospect_email").val("")
			 	}
			}
			
			$("#Success").modal("show");
			$("#successMsg").text("Congratulations: " + prospect.title +" is now your prospect.  If they activate their account in the next 60 days you can start claiming commissions!");
			
		}else{
			alert(response.error);
		}
	}
	
	callback.rest_id = prospect.rest_id
	
	payload = {email: $("#prospect_email").val(), rest_id:prospect.rest_id}
	
	target = "https://www.tastes-good.com/uploader/sales_claim_prospect.php";
	ajaxRequest(target, payload, callback);
	
}

function lookupPhone(prospect){
	var target, payload, callback;
	callback = function(response){
		if(response.result == "success"){
			if(response.data != 0){
				$("#prospect_phone").text(response.data);
				$("#prospect_phone_input").hide();
			}else{
				$("#prospect_phone").text("unknown");
				$("#prospect_phone_input").show();
			}
		}else{
			console.log(response.error);
		}
	}
	
	payload = "";
	target = "https://www.tastes-good.com/api_scripts/sales_lookup_phone.php?rest_id="+prospect.rest_id;
	ajaxRequest(target, payload, callback);
}

function savePhone(prospect){
	var target, payload, callback;
	
	pattern =  /[0-9]{3}[\D]*[0-9]{3}[\D]*[0-9]{4}/
			
	if (!pattern.test($("#prospect_phone_input").val())){
		alert ("Please enter 10 digit phone number.");
		return;
	}
			
			
	
	callback = function(response){
		if(response.result == "success"){
			$("#prospect_phone_input").hide();
			$("#prospect_phone").text($("#prospect_phone_input").val());
		} else {
			alert(response.error);
		}
	}
	payload = {rest_id:prospect.rest_id, phone:$("#prospect_phone_input").val()}
	
	target = "https://www.tastes-good.com/uploader/sales_save_phone.php";
	ajaxRequest(target, payload, callback);
	
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
	var x, title, total, expires, row, button, li;
	var active = MyProspects.active
	var prospects = MyProspects.prospects
	var customers = MyProspects.customers
	var frag1, frag2, frag3;
	
	frag1 =  document.createDocumentFragment();
	frag2 =  document.createDocumentFragment();
	frag3 =  document.createDocumentFragment();
	
	$("#active_count").text(active.length);
	if(active.length == 0){
		button = $("<button>", {class:"btn btn-default btn-block"}).append("No Active Restaurants");
		li = $("<li>", {class:"list-group-item"}).append(button);
		$(frag1).append(li);
	}
	
	
	for(x=0;x<active.length;++x){
		title = $("<div>", {class:"col-xs-6 no-padding"}).append(active[x].title);
		total = $("<div>", {class:"col-xs-3 no-padding"}).append(active[x].sales_total);
		expires = $("<div>", {class:"col-xs-3 no-padding"}).append(active[x].commission_term);
		row = $("<div>", {class:"row no-padding"}).append(title).append(total).append(expires);
		button = $("<button>", {class:"btn btn-default btn-block"}).append(row);
		
		li = $("<li>", {class:"list-group-item"}).append(button);
		$(frag1).append(li);
	}
	$("#prospect_count").text(prospects.length);
	if(prospects.length == 0){
		button = $("<button>", {class:"btn btn-default btn-block"}).append("No Current Prospects");
		li = $("<li>", {class:"list-group-item"}).append(button);
		$(frag2).append(li);
	}
	
	for(x=0;x<prospects.length;++x){
		title = $("<div>", {class:"col-xs-8 no-padding"}).append(prospects[x].title);
		expires = $("<div>", {class:"col-xs-4 no-padding"}).append(prospects[x].commission_term);
		row = $("<div>", {class:"row no-padding"}).append(title).append(expires);
		button = $("<button>", {class:"btn btn-default btn-block"}).append(row);
		
		li = $("<li>", {class:"list-group-item"}).append(button);
		$(frag2).append(li);
		
	}
	$("#customer_count").text(customers.length);
	if(customers.length == 0){
		button = $("<button>", {class:"btn btn-default btn-block"}).append("No Active Customers");
		li = $("<li>", {class:"list-group-item"}).append(button);
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
	
	$("#active_restaurants").append(frag1);
	$("#prospect_restaurants").append(frag2);
	$("#active_customers").append(frag3);
	 transitions($("#my_prospect"));
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

