
 
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

	window.fbAsyncInit = function() {
	  FB.init({
	    appId      : '1890437671201988',
	    cookie     : true,  // enable cookies to allow the server to access 
	                        // the session
	    xfbml      : true,  // parse social plugins on this page
	    version    : 'v2.8' // use graph api version 2.8
	  });
	}
	window.onpopstate = function(event) {
		hidePDF();
		checkState(event.state);
	};
	
	
	initGlobalVars();
	localStorageShim();	
    initErrors();
    initButtons();
    initModals();
    initKeystrokes();
    initDateTimePickers();
    checkHash();
    checkUser();
      
    
}
function loadPathsPhone(){


}

function loadPathsWeb(){
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
	});
}

function initGlobalVars(){
	active_div = 0;
	recentOrders = [];
	User = {user_id:0}
	
}
	
function initErrors(){
	
	
}
function checkUser(){
	
	var payload, target, callback;
	payload = ""
	callback = function (response){
		if(response.result == "success"){
			User=response.data;
			if(!User.is_rest){
				$('#userLogin').modal('show');
				$('#userNew').hide();
				$("#wrong_pwd").hide();					
				$("#email_noexist").hide();
				$("#email_sent").hide();
				$("#forgotPwd").hide();
				$("#not_rest").show();
								
			}else{
			
			checkState(history.state);	
			
			}
		}else{
			User = {user_id:0}
			if(auth_token = localStorage.getItem('auth_token')){
				tokenLogin(localStorage.getItem('auth_token'), function(){ checkState(history.state);})	
			}else{		
						
					$('#userLogin').modal('show');
					$('#userNew').hide();
					$("#wrong_pwd").hide();
					$("#not_rest").hide();
					$("#email_noexist").hide();
					$("#email_sent").hide();
					$("#forgotPwd").hide();
					
				
			}
		}
		refreshUser();
		
	}
	target = 'https://www.tastes-good.com/api_scripts/restaurant_user.php';
	ajaxRequest(target, payload, callback);
}


function checkHash(){
	//USER NOT GUARENTEED TO BE LOADED
	
	var order_id;
	var auth, rest_id;
	var params, action;
	
	if(!window.location.hash.length || !window.location.search.length){
		return false;	
	}
	
	params = window.location.hash.split('#')[1];
	params = params.split('$');
	action = window.location.search.split('?',2)[1];
	
	switch(action){
	case "confirm_order":
		// ?confirm_order#order_id$user_id$user_token
		var order_id, user_id, token;
		confirmOrder.order_id = params[0];
		token = params[1];
		
		callback = function(response){
			confirmOrder();
			history.pushState("", document.title, window.location.pathname)
			
		}
		
			
		if(typeof(User)!="object" || User.user_id == 0 || User.user_id != user_id){
		  tokenLogin(token, callback)	
		}else{
		  callback();
		}
				
		break;
	
	case "reject_order":
		// ?reject_order#order_id$user_id$user_token
		var order_id, user_id, token;
		rejectOrder.order_id = params[0];
		token = params[1];
		
		
		callback = function(response){
			rejectOrder();
			history.pushState("", document.title, window.location.pathname);
			
		}
		
		if(typeof(User)!="object" || User.user_id == 0 || User.user_id != user_id){
		  tokenLogin(token, callback)	
		}else{
		  callback();
		}
				
		break;
	
	case "registration":
		Auth = params[0];
		
		callback = function(response){
			if(response.result = "success"){
				User = response.data;
				Restaurant = User.restaurants[0];
				Restaurant.token = Auth;
				initNewRest();	
			}else{
				alert("Invalid or Expired Token");
				Restaurant = {rest_id:0}
				initNewRest();
			}
		}
		
		payload = {auth:Auth};
		
		ajaxRequest(target, payload, callback);
		
		break;
		
	case "activation":
		break;
	
	case "edit_menu":
		break;		
		
	default:
		return false;
	
	}
	return true;
}



function checkState(state){
var cur_time, list, community, restaurant;
cur_time = + new Date();


if(window.location.hash.length>0|| window.location.search.length>0){
	if(checkHash()){
		return;	
	}
}

if(User.user_id == 0){
	$("#restaurant_login").modal("show");
	return;	
}



if(state !== null && getRest(state.rest_id)){
	switch(state.page) {
		case "menu_categories":
			getMenu(renderCategories);
			break;
		case "menu_items":
			getMenu(renderItems);
			break;
		case "menu_extras":
			getMenu(renderExtras);
			break;
		case "restaurant_details":
			getRestDetails();
			break;
		case "restaurant_history":
			getHistory();
			break;
		case "restaurant_coupons":
			getCoupons();
			break;
		default:
			loadDashboard();
		}
	} else {
	loadDashboard();
}

if(checkUnsavedRest()){
	return;
}


}
   
function initButtons(){
	//new restaurant
	
	$("[name=new_rest_user]").change(function() {
		if(this.value == "new_user"){
			$("#new_rest_user_login").hide();
			$("#new_rest_create_user").slideDown();
			
		}else {
			$("#new_rest_create_user").hide();
			$("#new_rest_user_login").slideDown();
		}
			
			
	});
	
	//dashboard menu
	$("#show_terms").on("click", function(){
			showText("./termsconditions.html");
	});
	
	$("#show_privacy").on("click", function(){
			showText("./privacy.html");
	});
	
	
	
	$('#dropdown_login').click(function(){
		 initRestLogin()
		transitions($("#rest_login"));
		$('#userNew').hide();
		
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
				window.location = "https://www.tastes-good.com";
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
	
	$('#dashboard_sales').click(function(){
		window.location='https://www.tastes-good.com/salesforce.html';
	});
	//login frame
	
	$('#forget_pwd').click(function() {
		$('#forgotPwd').show(500);
		$('#userNew').hide();	
		$('#existing_user_btn').hide();
		
	});
	
	$('#forgot_pwd_Cancel').click(function() {
		$('#forgotPwd').hide();	
		$('#existing_user_btn').show(250);;	
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
	
	$('#submit_login').click(function() {
		restLogin();
		
	});
			
	
	//subnavbar 
	$('#new_cat').click(function(){initNewCategory();});
	$('[name="manage_extras"]').click(function(){getMenu(renderExtras);});
	$('#new_xtr').click(function(){initNewExtra();})
	$("[name='save_menu_btn']").click(function(){saveMenu();});
	
	$('#make_coupon').on("click", function(){initMakeCoupon();});
	
	$('#get_history').click(function(){
		getHistory();
		$('#history_report').show();
	});
	
	
	//extra
	$('#extra_typeOne').click(function(){
		$('#extra_typeOne').removeClass("btn-default").addClass("btn-primary")	
		$('#extra_typeTwo').removeClass("btn-primary").addClass("btn-default")	
		$('#ExtraType').val("1");
		generatePreview();
	});
		
	$('#extra_typeTwo').click(function(){
		$('#extra_typeTwo').removeClass("btn-default").addClass("btn-primary")	
		$('#extra_typeOne').removeClass("btn-primary").addClass("btn-default")	
		$('#ExtraType').val("2");
		generatePreview();
	});
	
	$('#extra_moreOptions').click(function(){
		extraMoreOptions();	
		generatePreview();
	});
	
	$('#ExtraQuestion').on("change", function(){
		generatePreview();	
	});
	
	//rest_details
	$("#rest_logo").change(function(){
        readURL(this);
    });	
    
    
	
	$('[name="rest_details"]').change(function(){
	
		requestRestSave();
	});
	
	$('#offers_delivery').change(function(){
		if($('#offers_delivery').is(':checked')){
			$('#delivery_base').prop('disabled', false);
			$('#delivery_variable').prop('disabled', false);
			$('#delivery_email').prop('disabled', false);
			Restaurant.offers_delivery = 1;
			requestRestSave();
		}else{
			$('#delivery_base').prop('disabled', true);
			$('#delivery_variable').prop('disabled', true);
			$('#delivery_email').prop('disabled', true);
			Restaurant.offers_delivery = 0;
			requestRestSave();
		}		
	});
	$('#delivery_base').on("change", function(){Restaurant.delivery_base =  /[\d]+[.]{0,1}[\d]{0,2}/.exec($('#delivery_base').val())[0];});
	$('#rest_phone').on("change",function(){Restaurant.phone=$('#rest_phone').val();});
	$('#rest_title').on("change",function(){Restaurant.title = $('#rest_title').val()});
	$('#user_fname').on("change",function(){User.fname=$('#user_fname').val()});
	$('#user_lname').on("change",function(){User.lname=$('#user_lname').val()});
	$('#user_phone').on("change",function(){User.phone=$('#user_phone').val()});
	$('#user_email').on("change",function(){User.email=$('#user_email').val()});
	$('#rest_type').on("change",function(){Restaurant.type=$('#rest_type').val()})
	$('#rest_address').on("change",function(){Restaurant.address=$('#rest_address').val()});
	$('#rest_email').on("change",function(){Restaurant.email=$('#rest_email').val()});
		
	
	$('[name="delivery_details"]').change(function(){
		
		requestRestSave(); 
	});
    $('#save_details').click(function(){saveRest();});
    $('#save_password').click(function(){savePwd();});
    
    //coupons
       
    $('input:radio[name=coupon_type]').click(function() { 
		var value = $('input:radio[name=coupon_type]:checked').val();
		if (value=="discount"){
			$('#discount').show();
			$('#item').hide();
			
		}else{
			$('#discount').hide();
			$('#item').show();
		}
					
	});
	
	$("#create_coupon").click(function(){saveCoupon();});
	
	
			$('input:radio[name=discount_percent]').click(function() { 
			var value = $('input:radio[name=discount_percent]:checked').val();
			var percent = value * 100;
			percent = percent + "%";
			
			$('#discount_percent').val(percent)
						
		});
	
	$("#addCouponExtra").on("click", function(){initCouponExtra();});		
			
	//modals
	
	
	//pdfs
		
	$('#hide_pdf_preview').click(function(){hidePDF();});
	$('#print_pdf_preview').click(function(){printPDF();});
	
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
		
}



function initModals(){
	$("#credit_amount").change(function(){
		$("#credit_amount").val(validatePrice($("#credit_amount").val()));		
	});
	
	
}
function initKeystrokes(){
	
}

function initDateTimePickers(){
	$('#history_begin').datepicker({ hideIfNoPrevNext: true, minDate:"-2Y", maxDate:"+0"}).datepicker( "setDate", "-1M" ).datepicker( "hide" )
		.datepicker("option", "dateFormat", "mm/dd/yy");
	
	$('#history_end').datepicker({ hideIfNoPrevNext: true, minDate:"-2Y", maxDate:"+0"}).datepicker( "setDate", "+0" ).datepicker( "hide" )
		.datepicker("option", "dateFormat", "mm/dd/yy");
}

function initNewRest(){	
	$('#new_rest_phone').val(Restaurant.phone);
	$('#new_rest_title').val(Restaurant.title);
	$('#new_rest_address').val(Restaurant.address);
	$('#new_rest_email').val(Restaurant.email);
	$('#new_user_phone').val(Restaurant.phone);
	$('#new_user_email').val(Restaurant.email);
	
	
	$('#new_offers_delivery').val(1).on("change", function(){
		if($('#offers_delivery').is(':checked')){
			$('#new_delivery_base').prop('disabled', false);
			Restaurant.offers_delivery = 1;
			
		}else{
			$('#new_delivery_base').prop('disabled', true);
			Restaurant.offers_delivery = 0;
			
	}});
	
	$('#new_delivery_base').val("$5.00");
	
	$('#new_monday_open').wickedpicker({now:Restaurant.schedule.monday_open, minutesInterval: 14,twentyFour: true});	
	$('#new_monday_close').wickedpicker({now:Restaurant.schedule.monday_close, minutesInterval: 14,twentyFour: true});
	$('#new_tuesday_open').wickedpicker({now:Restaurant.schedule.tuesday_open, minutesInterval: 14,twentyFour: true});	
	$('#new_tuesday_close').wickedpicker({now:Restaurant.schedule.tuesday_close, minutesInterval: 14,twentyFour: true});
	$('#new_wednesday_open').wickedpicker({now:Restaurant.schedule.wednesday_open, minutesInterval: 14,twentyFour: true});	
	$('#new_wednesday_close').wickedpicker({now:Restaurant.schedule.wednesday_close, minutesInterval: 14,twentyFour: true});
	$('#new_thursday_open').wickedpicker({now:Restaurant.schedule.thursday_open, minutesInterval: 14,twentyFour: true});	
	$('#new_thursday_close').wickedpicker({now:Restaurant.schedule.thursday_close, minutesInterval: 14,twentyFour: true});
	$('#new_friday_open').wickedpicker({now:Restaurant.schedule.friday_open, minutesInterval: 14,twentyFour: true});	
	$('#new_friday_close').wickedpicker({now:Restaurant.schedule.friday_close, minutesInterval: 14,twentyFour: true});
	$('#new_saturday_open').wickedpicker({now:Restaurant.schedule.saturday_open, minutesInterval: 14,twentyFour: true});	
	$('#new_saturday_close').wickedpicker({now:Restaurant.schedule.saturday_close, minutesInterval: 14,twentyFour: true});
	$('#new_sunday_open').wickedpicker({now:Restaurant.schedule.sunday_open, minutesInterval: 14,twentyFour: true});	
	$('#new_sunday_close').wickedpicker({now:Restaurant.schedule.sunday_close, minutesInterval: 14,twentyFour: true});
	
	$('.timepicker').off("change").on("change", function(){
    		var schedule 
			schedule = {"monday_open":$('#new_monday_open').val(),"tuesday_open":$('#new_tuesday_open').val(),"wednesday_open":$('#new_wednesday_open').val(),"thursday_open":$('#new_thursday_open').val(),"friday_open":$('#new_friday_open').val(),"saturday_open":$('#new_saturday_open').val(),"sunday_open":$('#new_sunday_open').val(), "monday_close":$('#new_monday_close').val(),"tuesday_close":$('#new_tuesday_close').val(),"wednesday_close":$('#new_wednesday_close').val(),"thursday_close":$('#new_thursday_close').val(),"friday_close":$('#new_friday_close').val(),"saturday_close":$('#new_saturday_close').val(),"sunday_close":$('#new_sunday_close').val()}
			Restaurant.schedule = schedule;
	});
	
	$("#submit_new_restaurant").off("click").on("click", function(){
		saveNewRest();	
	});
	
	$("#new_rest_logo").change(function(){
        readURL(this);
    });	
    
	transitions($("#new_restaurant"));
}

function saveNewRest(){
	Restaurant.phone = $('#new_rest_phone').val();
	Restaurant.address = $('#new_rest_address').val();
	Restaurant.email = $('#new_rest_email').val();
	Restaurant.title = $('#new_rest_title').val();
	
	
	
	
	switch( $("[name=new_rest_user]:checked").val() ){
		
	case "new_user":
		User.type = "new_user"
		User.phone = $('#new_user_phone').val();
		User.email = $('#new_user_email').val();
		User.fname = $('#new_user_fname').val();
		User.lname = $('#new_user_lname').val();
		
		break;
	case "old_user":
		User.type = "old_user"
		User.email = $('#new_login_email').val();
		User.password = $('#new_login_password').val();
		break;
	}
			
	Restaurant.delivery_base =$('#new_delivery_base').val();
			
	var target, payload, callback;
	if(!$("#read_terms").is(':checked') || !$("#read_privacy").is(':checked')){
		alert("Please accept the Terms and Conditions and Privacy Policy before continuing");	
	}
		

	if($("#new_password").val() == $("#new_password_confirm").val()){
		if($("#new_password").val().length > 5){		
			User.password=$("#new_password").val();
			console.log(User.password);
		}else{
			alert("passwords is too short!");
			return;
		}
	}else{
		alert("passwords don't match!");
		return;
	}
	
	pattern =  /[0-9]{0,1}[\D]*[0-9]{3}[\D]*[0-9]{3}[\D]*[0-9]{4}/
	
	if (!pattern.test(Restaurant.phone)){
		alert ("Invalid business phone number: please enter 10 or 11 digit number");
		return;
	}
	
	if (!pattern.test(User.phone)){
		alert ("Invalid personal phone number: please enter 10 or 11 digit number");
		return;
	}
	var formData = new FormData ();
	formData.append('User', JSON.stringify(User));
	formData.append('auth', Auth);
	
	if($("#new_rest_logo")[0].files.length > 0 && $("#new_rest_logo")[0].files[0].size < 50000){
		 	 formData.append('rest_logo',$("#new_rest_logo")[0].files[0], $("#new_rest_logo")[0].files[0].name)
		 	 Restaurant.image_attached = 1;
		 }
	var xhr = new XMLHttpRequest();
	
	xhr.onload = function() {
		if(response=JSON.parse(xhr.response)){
			if(response.result =="success"){
				User = response.data;
				localStorage.setItem("Restaurant", "");
				history.pushState(null, "", "dashboard.html");
				checkUser();				
			}else{
			switch ( response.error ){
			case  "EMAIL_EXISTS":
				alert("An account exists for this email address.  If you would like to manage your restaurant from this account please enter your password; or provide a different email address");
				$("#new_rest_old_user").click();
				break;
			case "EMAIL_NO_EXISTS":
				alert("An account does not exists for this email address. Please fill out your user information; or provide a different email address");
				$("#new_user_email").val($("#new_login_email").val());
				$("#new_rest_new_user").click();
				break;
			case "BAD_PWD":
				alert("Incorrect User Name/Password Please Try Again.");
				break;
			default:
				alert("Unknown Error: " + response.error )
				break;
			}
				
			
		}
		}
	}
	
	target = "https://www.tastes-good.com/api_scripts/new_restaurant_save.php?rest_id="+Restaurant.rest_id
	
	xhr.open('POST', target, true);
	xhr.send(formData);
		
}


function getRest(rest_id){
	var x;
	
	for(x=0;x < User.restaurants.length;++x){
		if(User.restaurants[x].rest_id == rest_id){
			Restaurant = User.restaurants[x];
			return true;
		}
	}
	return false;
	
}

function OpenClose(rest){
	var closed;
	if(rest.closed){
		closed = 0;
		rest.closed = 0;
	}
	else {
		closed = 1;	
		rest.closed =1;
	}
	
		
	
	payload = {rest_id:rest.rest_id, closed:closed};
	callback = function (response){
		refreshDashboard();
		console.log(response);		
	}
	
	target = 'https://www.tastes-good.com/uploader/open_close.php?' + closed;
	ajaxRequest(target, payload, callback);	
			
}


function getCoupons(){
	var payload, target, callback;
	payload = ""
	callback = function (response){
		if(response.result=="success"){
			Restaurant = response.data
			renderCoupons();
		}else{
				console.log(response);
			} 
	}

	target = "https://www.tastes-good.com/api_scripts/restaurant_coupons.php?rest_id="+Restaurant.rest_id;
	ajaxRequest(target, payload, callback);
 
}

function renderCoupons(){
	state = {page:"restaurant_coupons", rest_id:Restaurant.rest_id}
	setState(state)
	
	if(!$("#coupon_navbar").is(":visible")){
		$("#sub_navbar").children("div").hide();
		$("#coupon_navbar").slideDown();
	}	
	
	$("#coupon_builder").hide();
	
	frag = document.createDocumentFragment();
	
	if(Restaurant.coupons.length == 0){
		var placeholder
		placeholder = $("<li>", {class:"list-group-item", style:"text-align:center;"}).append("<h2 style='font-size:16px; padding:15px;'>No Coupons</h2>");
		$(frag).append(placeholder);
		
	}
	
	
	for(x=0;x<Restaurant.coupons.length;++x){
		var li, div1, div2, line1, line2, line3, delete_btn, link, title, img, logo, code, h2, href;
		var coupon, str;
		coupon = Restaurant.coupons[x];
		
		href="./main.html?coupon#"+Restaurant.rest_id+"$"+coupon.code;
		link = $("<a>", {style:"position:absolute; top:0; left:0; width:175px; height:100px; z-index:1000;", href:href});
		
		if(coupon.type == "discount"){
			title_text =  coupon.title
		}else if (coupon.type == "item"){
			title_text =  coupon.title + '<span style="color: #633E26;">'+validatePrice(coupon.price)+'</span>';
		}
		title=$("<h2>", {style:"position:absolute; top:0px;line-height:15px; left:5px;font-size:15px; width:160px; text-align:center;"}).append(title_text);
		img = $("<img>", {style:"position:absolute; top:0; left:0; width:175px; height:100px;", src:"./img/biz_card_blank.png"});
		logo = $("<img>", {style:"position:absolute; top:22px; left:0;height:56px;width:56px;", src:Restaurant.image});
		code = $("<h3>", {style:"position:absolute; top:55px; margin-top:0; left:67px;font-size:15px; width:100px; text-align:center;"}).append(coupon.code);
		h2 = $("<h2>", {style:"position:absolute; top:35px; margin-top:0; left:30px;font-size:15px; width:175px; text-align:center;"}).append("Coupon Code:");
				
		div1 = $("<div>", {style:"position:relative; width:175px;"}).append(link).append(img).append(title).append(logo).append(code).append(h2);
		
		coupon.expire_date = formatDate(new Date(parseInt(coupon.expires)).toISOString());
		line1 = $("<div>").append('<strong> Expires: </strong>'+coupon.expire_date);
		
		if(coupon.public){
			str = "PUBLIC";
		}else{
			str = "PRIVATE";
		}
		line2 = $("<div>").append('<strong> Coupon Is: </strong>'+str)
		line3 = $("<div>").append('<strong> Code: </strong>'+coupon.code)
		delete_btn = $("<button>", {class:"btn btn-default btn-sm"}).append('<span class="glyphicon glyphicon-trash"></span> Coupon')
			.on("click", {coupon:coupon}, function(event){initDeleteCoupon(event.data.coupon);});
		
		div2 = $("<div>", {style:"margin-left:180px; height:100px;"}).append(line1).append(line2).append(line3).append(delete_btn);
		
		li = $("<li>", {class:"list-group-item", style:"height:130"}).append(div1).append(div2);
		
		$(frag).append(li);
		
	}
	
	$("#coupon_list").empty().append(frag);
	
	$("#expire_3M").attr("value", + new Date() +3*31*24*60*60*1000);
	$("#expire_6M").attr("value", + new Date() +6*31*24*60*60*1000);
	$("#expire_9M").attr("value", + new Date() +9*31*24*60*60*1000);
	$("#expire_12M").attr("value", + new Date() +12*31*24*60*60*1000);
	
	var value = $('input:radio[name=coupon_type]:checked').val();
	if (value=="discount"){
		$('#discount').show();
		$('#item').hide();
		
	}else{
		$('#discount').hide();
		$('#item').show();
	}
	
	var value = $('input:radio[name=discount_percent]:checked').val();
	var percent = value * 100;
	percent = percent + "%";
	$('#discount_percent').val(percent)
	
	
	
	transitions($("#rest_coupons"));
	
}

function initMakeCoupon(){
	$("#coupon_builder").show();
}

function initDeleteCoupon(coupon){
		
	$("#ConfirmDeleteCoup").modal("show");
	$("#delete_coupon_name").text(coupon.title)
	$("#confirm_delete_coup").off("click").on("click", {coupon:coupon}, function(event){deleteCoupon(event.data.coupon)});
	
}

function deleteCoupon(coupon){
	var payload, target, callback;
	payload = "";
	callback = function (response){
		if (response.result=="success"){
			getCoupons();
		}else{
			alert(response.error);
		}
	}
	
	target = "https://www.tastes-good.com/uploader/delete_coupon.php?coupon="+coupon.id;
	ajaxRequest(target, payload, callback);
 
}

function saveCoupon(){
	var payload, target, callback;
	var coupon = {};
	coupon.rest_id = Restaurant.rest_id;
	coupon.type = $('input:radio[name=coupon_type]:checked').val();
	if(coupon.type == "discount"){
	coupon.title =  $("#discount_percent").val() + " Discount"
	coupon.price = 0
	coupon.discount =  $('input:radio[name=discount_percent]:checked').val();
	coupon.extras = [];
	
	} else {
	coupon.discount = 0
	coupon.title = $('#coupon_title').val();
	coupon.price = $('#coupon_price').val();
	coupon.price = /[\d]+[.]{0,1}[\d]{0,2}/.exec(coupon.price)[0]
	
	coupon.extras = [];
	
	$.each($('#coupon_extras').find('li').not('.placeholder'), function(key, item){
			var extraId = $(this).attr("name")
			extraId=extraId.match(/\d+/g);
			if(extraId.length > 0){
				coupon.extras.push(extraId[0]);
			}
		});
	
	}
	
	if( $('input:radio[name=public_coupon]:checked').val()=="public"){
	coupon.public =  1;
	}else{coupon.public =  0;}
	
	coupon.expire = parseInt($('input:radio[name=coupon_expire]:checked').val())
	coupon.timestamp = +new Date();
	
	payload = coupon; 
	callback = function (response){
		if (response.result=="success"){
			getCoupons();
		}else{
			alert(response.error);
		}
	}
	target = "https://www.tastes-good.com/api_scripts/restaurant_savecoupon.php?rest_id="+Restaurant.rest_id;
	ajaxRequest(target, payload, callback);
	
}

function getHistory(){
	var begin, end, formdata;
	var payload, target, callback;
	var rest_id;
	
	begin = new Date($('#history_begin').val());
	end = new Date($('#history_end').val());
	
	end.setDate(end.getDate()+1)
	begin = begin.getTime();
	end = end.getTime();
	
	
	payload = {begin:begin, end:end, rest_id:Restaurant.rest_id}
	callback = function (response){
		if (response.result=="success"){
			History = response.data
			renderHistory();
		}else{
			alert(response.error);
		}
	}
	target = "https://www.tastes-good.com/api_scripts/restaurant_history.php";
	ajaxRequest(target, payload, callback);
}	
 
 
 

function renderHistory(){
	var x, y, combined;
	var div, info, balance;
	var data;
	var str1, str2, btn1, btn2, col1, col2, row, frag;
	var link;
	var bal_init, bal_end;
	
	state = {page:"restaurant_history", rest_id:Restaurant.rest_id}
	setState(state)
	
	if(!$("#history_navbar").is(":visible")){
		$("#sub_navbar").children("div").hide();
		$("#history_navbar").slideDown();
	}	
	
	combined = [];
	x= 0
	
	while(combined.length < History.orders.length + History.payments.length){
		if(History.orders.length && History.payments.length){		
		if(History.orders[0].timestamp > History.payments[0].timestamp){
			combined.splice(combined.length, 0, History.payments.splice(0,1)[0])
			continue;
		}else{
			combined.splice(combined.length, 0, History.orders.splice(0,1)[0])
			continue;
		}
		}
		if (History.orders.length){
			combined.splice(combined.length, 0, History.orders.splice(0,1)[0])
			continue;
		}
		
		if(History.payouts.length){
			combined.splice(combined.length, 0, History.payments.splice(0,1)[0])
			continue;		
		}
		
		x = x+1 
		if(x>1000){break;}
	}
	
	frag = document.createDocumentFragment();
	
	if(combined.length == 0){
		var placeholder
		placeholder = $("<li>", {class:"list-group-item", style:"text-align:center;"}).append("<h2 style='font-size:16px; padding:15px;'>No Account Activity</h2>");
		$(frag).append(placeholder);
		
	}
	
	for(x=0;x<combined.length;++x){
		data = combined[x];
		
		row =$("<div>", {class:"row no-padding"});
		
		if(data.class=="payout"){
		
			str1 == "Payment Sent: " + validatePrice(data.amount);
			str2 = validatePrice(data.balance);
				
			btn1= $("<button>", {class:"btn btn-info btn-block"}).append(str1);
			btn2= $("<button>", {class:"btn btn-info btn-block"}).append(str2);
			col1 = $("<div>", {class:"col-xs-8 no-padding"}).append(btn1);
			col2 = $("<div>", {class:"col-xs-4 no-padding"}).append(btn2);
			$(row).append(col1).append(col2);						
		
		} else if (data.class=="order"){			
			
			str1 = "Order #"+data.order_id+"  Total: "+ validatePrice(data.total);
			str2 = validatePrice(data.balance);
									
			if(data.paymentType == "online"){
				str1 = str1 + "  PAID"
				btn1= $("<button>", {class:"btn btn-success btn-block"}).append(str1);
				btn2= $("<button>", {class:"btn btn-success btn-block"}).append(str2);
					
			} else {
				str1 = str1 + "  CASH"
				btn1= $("<button>", {class:"btn btn-warning btn-block"}).append(str1);
				btn2= $("<button>", {class:"btn btn-warning btn-block"}).append(str2);
				
			}
			
			col1 = $("<div>", {class:"col-xs-8 no-padding"}).append(btn1);
			col2 = $("<div>", {class:"col-xs-4 no-padding"}).append(btn2);
			link = "https://www.tastes-good.com/uploader/push_pdf.php?order_id="+data.order_id
			if(window.isphone){
				link = link + "&isphone=1";	 
			  }
			
			$(row).append(col1).append(col2)
				.on("click", {link:link}, function(event){showPDF(event.data.link)});
		}
		$(frag).append(row)
	}
	$("#history_list").empty().append(frag);
	
	if(combined.length>0){
		bal_init = combined[0].balance
		bal_end = combined[combined.length - 1].balance
	} else {
		bal_init = History.balance;
		bal_end =History.balance;
	}
	//Set the date.
	$("[name='period_start']").text($("#history_begin").val());
	$("[name='period_stop']").text($("#history_end").val());
	$("[name='final_balance']").text(validatePrice(bal_end));
	$("[name='initial_balance']").text(validatePrice(bal_init));
	
	transitions($('#order_history'));
	
}

function checkUnsavedRest(){
	if(localStorage.getItem("User") || localStorage.getItem("User")){
		$('#rest_save_changes').modal("show");
		
		$("#rest_confirm_changes").click(function(event){
			var x;				
			User=JSON.parse(localStorage.getItem("User"));
			saveRest();				
				
			$("#rest_save_changes").modal("hide");
		});
		
		$("#rest_discard_changes").click(function(event){
				localStorage.setItem("Restaurant", "");
				$("#rest_save_changes").modal("hide");
		});
		return true;
		
	}else{
		
		return false;
	}
}

function getRestDetails(){
	var payload, target, callback;
	var rest_id;
	rest_id = Restaurant.rest_id
	
	
	
	renderRestDetails();
}

function renderRestDetails(){
	state = {page:"restaurant_details", rest_id:Restaurant.rest_id}
	setState(state)
	
	
	if(!$("#detail_navbar").is(":visible")){
		$("#sub_navbar").children("div").hide();
		$("#detail_navbar").slideDown();
	}	
	
	$("#image_preview").attr("src", Restaurant.image);
	$('#rest_phone').val(Restaurant.phone)
	$('#rest_title').val(Restaurant.title)
	$('#user_fname').val(User.fname)
	$('#user_lname').val(User.lname)
	$('#user_phone').val(User.phone)
	$('#user_email').val(Restaurant.email)
	$('#rest_type').val(Restaurant.type)
	$('#rest_address').val(Restaurant.address)
	$('#rest_email').val(Restaurant.email)
	
	if(Restaurant.offers_delivery){	
		$('#offers_delivery').attr("checked", true);
		$('#delivery_base').prop('disabled', false);
	}else{
	
		$('#offers_delivery').attr("checked", false);
		$('#delivery_base').prop('disabled', true);
	}
	
	
	
	$('#delivery_base').val(Restaurant.delivery_base)
	
	$('#monday_open').wickedpicker({now:Restaurant.schedule.monday_open, minutesInterval: 14,twentyFour: true});	
	$('#monday_close').wickedpicker({now:Restaurant.schedule.monday_close, minutesInterval: 14,twentyFour: true});
	$('#tuesday_open').wickedpicker({now:Restaurant.schedule.tuesday_open, minutesInterval: 14,twentyFour: true});	
	$('#tuesday_close').wickedpicker({now:Restaurant.schedule.tuesday_close, minutesInterval: 14,twentyFour: true});
	$('#wednesday_open').wickedpicker({now:Restaurant.schedule.wednesday_open, minutesInterval: 14,twentyFour: true});	
	$('#wednesday_close').wickedpicker({now:Restaurant.schedule.wednesday_close, minutesInterval: 14,twentyFour: true});
	$('#thursday_open').wickedpicker({now:Restaurant.schedule.thursday_open, minutesInterval: 14,twentyFour: true});	
	$('#thursday_close').wickedpicker({now:Restaurant.schedule.thursday_close, minutesInterval: 14,twentyFour: true});
	$('#friday_open').wickedpicker({now:Restaurant.schedule.friday_open, minutesInterval: 14,twentyFour: true});	
	$('#friday_close').wickedpicker({now:Restaurant.schedule.friday_close, minutesInterval: 14,twentyFour: true});
	$('#saturday_open').wickedpicker({now:Restaurant.schedule.saturday_open, minutesInterval: 14,twentyFour: true});	
	$('#saturday_close').wickedpicker({now:Restaurant.schedule.saturday_close, minutesInterval: 14,twentyFour: true});
	$('#sunday_open').wickedpicker({now:Restaurant.schedule.sunday_open, minutesInterval: 14,twentyFour: true});	
	$('#sunday_close').wickedpicker({now:Restaurant.schedule.sunday_close, minutesInterval: 14,twentyFour: true});
	
	$('.timepicker').off("change").on("change", function(){
    		var schedule 
			$('#save_schedule').removeClass("btn-default");
			$('#save_schedule').addClass("btn-warning");
			
			schedule = {"monday_open":$('#monday_open').val(),"tuesday_open":$('#tuesday_open').val(),"wednesday_open":$('#wednesday_open').val(),"thursday_open":$('#thursday_open').val(),"friday_open":$('#friday_open').val(),"saturday_open":$('#saturday_open').val(),"sunday_open":$('#sunday_open').val(), "monday_close":$('#monday_close').val(),"tuesday_close":$('#tuesday_close').val(),"wednesday_close":$('#wednesday_close').val(),"thursday_close":$('#thursday_close').val(),"friday_close":$('#friday_close').val(),"saturday_close":$('#saturday_close').val(),"sunday_close":$('#sunday_close').val()}
			Restaurant.schedule = schedule;
			requestRestSave();
	});
	
	transitions($('#restaurant_details'));
}

function requestRestSave(){
	
	localStorage.setItem("User", JSON.stringify(User));
	$('#save_details').addClass("btn-warning");
	$('#save_details').removeClass("btn-default");	
	
}

function saveRest(){
		
	var formData = new FormData ();
	telephone = Restaurant.phone
	pattern =  /[0-9]{0,1}[\D]*[0-9]{3}[\D]*[0-9]{3}[\D]*[0-9]{4}/
	
	if (!pattern.test(telephone)){
		alert ("Invalid business phone number: please enter 10 or 11 digit number");
		return;
	}
	
	telephone =  User.phone
	if (!pattern.test(telephone)){
		alert ("Invalid personal phone number: please enter 10 or 11 digit number");
		return;
	}
			
	formData.append('User', JSON.stringify(User));
	
	if($("#rest_logo")[0].files.length > 0 && $("#rest_logo")[0].files[0].size < 50000){
		 	 formData.append('rest_logo',$("#rest_logo")[0].files[0], $("#rest_logo")[0].files[0].name)
		 }
		 
		 
	var xhr = new XMLHttpRequest();
	
	xhr.onload = function() {
		if(result=JSON.parse(xhr.response)){	
			if(result.result=="success"){
				$('#save_details').addClass("btn-default");
				$('#save_details').removeClass("btn-warning");		
			
				localStorage.setItem("User", "");
			}
		}else{
			console.log(xhr.response);
		}
		
	};
	// Open the connection.
	xhr.open('POST', 'https://www.tastes-good.com/api_scripts/restaurant_savedetail.php', true);
	xhr.send(formData);		
		
}
	


function savePwd(){
	if($('#new_pwd').val()==$('#confirm_pwd').val()){	
		var formData = new FormData ();
		password = {"old_pwd":$('#old_pwd').val(), "new_pwd":$('#new_pwd').val()};
		
		payload = {password:password, user_id:User.user_id};
		
		callback = function (response){
			if (response.result=="success"){
				$('#password_modal').modal("hide");
			}else{
				alert(response.error);
			}
		}
		
		target = "https://www.tastes-good.com/uploader/change_password.php";
		ajaxRequest(target, payload, callback);
				
		}else{ alert("passwords don't match!");}	
	
}


function saveMenu(){
	
	//check local storage for menu backups [timestamp, timestamp, timestamp]
	var backups = [0];
	var x;
	var rest_id;
	rest_id = Restaurant.rest_id
		
	if(x=localStorage.getItem("menu_backups")){
		backups = JSON.parse(x);
	}
	
	var cur_time = + new Date();
	if(backups[0]< + cur_time - 86400000){
		//we don't have a backup in local storage from last 24 hrs.
		backups.splice(0,0,cur_time);
		localStorage.setItem("Menu_"+cur_time, JSON.stringify(Restaurant));
		localStorage.setItem("menu_backups", JSON.stringify(backups));
		
	}
		
	var payload, callback, target;
	payload = Restaurant.menu;
	
	callback = function(response) {
			if(response.result == "success"){
				localStorage.setItem("Menu", "")
			} else {
				console.log(response.error);
		}
	}
	target = "https://www.tastes-good.com/api_scripts/restaurant_savemenu.php?rest_id="+rest_id;
	ajaxRequest(target, payload, callback);
	
}

function initNewCategory(){
	$('#category_action').text("New ");
	$('#NewCategoryName').val("New Category Name");
    $('#NewCategory').modal('show') 
    $('#save_category').off("click").on("click", function(){saveNewCategory();});
    $('#NewCategory').modal('show');
}

function initEditCategory(category){
	$('#category_action').text("Edit ");
	$('#NewCategoryName').val(category.category);
    $('#NewCategory').modal('show') 
    $('#save_category').off("click").on("click", {category:category}, function(event){saveEditCategory(event.data.category);});
    $('#NewCategory').modal('show');
}

function saveNewCategory(){
 var category = {};
 category.category = $('#NewCategoryName').val();
 Restaurant.menu.lastCategory = Restaurant.menu.lastCategory + 1;
 category.id = Restaurant.menu.lastCategory;
 category.extras = [];
 category.items = [];
 
 Restaurant.menu.categories.push(category);
 saveMenu()
 renderCategories();
 
}

function saveEditCategory(category){
	category.category = $('#NewCategoryName').val();
	saveMenu()
	renderCategories();

}

function initNewItem(category){
	$('#newItem_product').val("Item Name");
	$('#newItem_price').val("0.00");
	$('#newItem_description').val("");
	$('#save_item').off("click").on("click", {category:category}, function(event){saveNewItem(event.data.category);});
	$('#NewItem').modal('show');
}

function initEditItem(category, item){
	$('#newItem_product').val(item.product);
	$('#newItem_price').val(validatePrice(item.price));
	$('#newItem_description').val(item.description);
	$('#save_item').off("click").on("click", {category:category, item:item}, function(event){saveEditItem(event.data.category, event.data.item);});
	$('#NewItem').modal('show')
}

function saveNewItem(category){
	var newItem = {}
	newItem.category = category.category;
	newItem.product = $('#newItem_product').val();
	newItem.price = $('#newItem_price').val();
	newItem.description = $('#newItem_description').val();
	Restaurant.menu.lastItem = Restaurant.menu.lastItem + 1;
	newItem.id = Restaurant.menu.lastItem;
	newItem.extras = [];
	
	category.items.push(newItem);
	saveMenu()
	renderItems(category);
	
	
}



function saveEditItem(category, item){
	item.product = $('#newItem_product').val();
	item.price = $('#newItem_price').val();
	item.description = $('#newItem_description').val();
	saveMenu()
	renderItems(category);	
}

function initEditExtra(extra){
	var x;
	var extra;
	
	$('#xtr_action').text("Edit ");
	
	$('#ExtraName').val(extra.name);
	$('#ExtraQuestion').val(extra.question);
	$('#ExtraError1').hide();
	//set type;
	$('#ExtraType').val(extra.type);
	
	if(extra.type ==1){
		$('#extra_typeOne').removeClass("btn-default").addClass("btn-primary");
		$('#extra_typeTwo').removeClass("btn-primary").addClass("btn-default");	
	}else if(extra.type == 2){
		$('#extra_typeTwo').removeClass("btn-default").addClass("btn-primary");
		$('#extra_typeOne').removeClass("btn-primary").addClass("btn-default");		
	}

	$('#ExtraOptions').empty();
	
	for(x=0;x<extra.options.length;++x){
		extraMoreOptions(extra.options[x]);	
		
	}
	
	generatePreview();
	
	$('#NewExtra').modal('show');
		
	$('#save_extra').off("click").on("click", {extra:extra}, function(event){
		saveEditExtra(event.data.extra);
		
	});
}

function extraGeneratePreview(){
	$('#preview_select').empty().hide();
	$('#preview_check').empty().hide();
	$('#preview_question').text("");

}

function initNewExtra(){
	var x;
	$('#xtr_action').text("New ");
	
	$('#ExtraError1').addClass('hidden');
	
	$('#ExtraName').val("");
	$('#ExtraQuestion').val("");
	$('#ExtraError1').hide();
	//set type;
	$('#ExtraType').val("1");
	$('#typeOne').removeClass("btn-default").addClass("btn-primary");
	$('#typeTwo').removeClass("btn-primary").addClass("btn-default");	
			

	$('#ExtraOptions').empty();
	
	for(x=0;x<2;++x){
		extraMoreOptions();	
	}
	
	generatePreview();
	$('#NewExtra').modal('show');
	$('#save_extra').off("click").on("click", function(){
		saveNewExtra();	
	});
	
}

function saveNewExtra(){
	var extra = {};
	
	Restaurant.menu.lastExtra += 1
	extra.id = Restaurant.menu.lastExtra;
	Restaurant.menu.extras.push(extra);
	
	saveEditExtra(extra)
	
	
}

function saveEditExtra(extra){
	var option, options, x, y;
	
	extra.question = $('#ExtraQuestion').val()
	if(extra.question.length == 0){
		alert("A Question is Required!");
		return;
	}
	extra.type = $('#ExtraType').val()
	extra.options = [];
	extra.name = $("#ExtraName").val();
	if(extra.name.length == 0){
		alert("Reference Tag is Required!");
		return;
	}
	options = $('#ExtraOptions').find("li");	
	
	for(x=0;x<options.length;++x){
		option = {};
		
		option.name = $(options[x]).find("[name='name']").val();
		option.price = $(options[x]).find("[name='price']").val();
		option.extras = [];
		
		option_extras = $(options[x]).find("[name='extraExtras']").children();
		
		for (y=0; y<option_extras.length;++y){
		
			option.extras.push($(option_extras[y]).attr("name"));
					
		}
		extra.options.push(option)
	}
	
	$("#NewExtra").modal("hide");
	saveMenu();
	checkState(history.state);
	
}

function generatePreview(){
	var frag, options;
	frag = document.createDocumentFragment();
	$('#preview_question').text($('#ExtraQuestion').val());
	options = $('#ExtraOptions').find("li");	
	
	
	if($('#ExtraType').val() == 1){
		$('#preview_continue').text("No Thanks");
		for(x=0;x<options.length;++x){
			var extra_btn = $('<button></button>')
			$(extra_btn).addClass("btn btn-block btn-default").text($(options[x]).find("[name='name']").val()).append('<strong style="float:right; margin-right:20px;">' +$(options[x]).find("[name='price']").val() +'</strong>');
			$(frag).append($(extra_btn));
					
		}
			
		
	}else if($('#ExtraType').val() == 2){
		$('#preview_continue').text("Continue");
		
		for(x=0;x<options.length;++x){
			li = $('<div>', {style:"display:inline-block; height:35px; min-width:175px;margin:auto;"});
			label = $('<label>', {class:"extra-chk-label"});
			span = $('<span>', {class:"extra-chk-span"}).append('<strong>'+$(options[x]).find("[name='price']").val() +'</strong>')
			chk = $('<input>', {class:"extra-chk-chk", type:"checkbox"});
			
			label.append(chk).append($(options[x]).find("[name='name']").val()).append(span)
			li.append(label)
			$(frag).append(li)
					
		}
				
				
	}
	
	$('#preview_options').empty().append(frag);
}

function extraMoreOptions(option){
	var option = option || 0;
	var x, y, z;
	
	var li, extra_div, extra;
	
	extra_div = $("<div>", {name:"extra_div", style:"display:none;background-color:lightgrey;border-style: dashed;"})
	for(z=0;z<Restaurant.menu.extras.length;++z){
		extra = Restaurant.menu.extras[z]
		btn = $("<button>",{class:"btn btn-default btn-block"}).append(extra.name)
			.click({extra:extra}, function(event){ 
			var extra = event.data.extra;
			var li = upTo(this, "li");
			$(li).find("[name='extraExtras']").append($("<div>", { style:"overflow:auto;", name:extra.id, class:"list-group-item"}).append(extra.name)
				.append($("<button>", {style:"float:right; padding:5px; width:20%;"}).append('<span class="glyphicon glyphicon-trash"></span>').on("click", function(){$(this).parent().remove()})))
			
			});
					
		$(extra_div).append(btn);
	}
	if(typeof(option)=="object"){
	ul = $("<ul>", {name:"extraExtras", class:"list-group"})
		for(z=0;z<option.extras.length;++z){
		  extra = lookupExtra(option.extras[z]);
			$(ul).append($("<div>", { style:"overflow:auto;", name:extra.id, class:"list-group-item"}).append(extra.name)
				.append($("<button>", {style:"float:right; padding:5px; width:20%;"}).append('<span class="glyphicon glyphicon-trash"></span>').on("click", function(){$(this).parent().remove()})))
			
		}
	
	
	li = $("<li>", {class:"list-group-item", style:"padding:5px"})
				.append('<span style="width:15%;display:inline-block;text-align:center">Name: </span>')
				.append($('<input>', {style:"width:30%;display:inline-block;", name:"name", type:"text", class:"form-control", placeholder:"Option"}).val(option.name).on("change", function(){
					generatePreview();}))
				.append('<span style="width:15%;display:inline-block;text-align:center;" >Price: </span>')
				.append($('<input>',{ style:"width:26%;display:inline-block;", name:"price", type:"text", class:"form-control", placeholder:"$0.00"}).val(option.price).on("change", function(){
					$(this).val(validatePrice($(this).val()));
					generatePreview();}))
				.append($("<button>", {style:"height:32px;margin-top:-7px;width:12%;border-radius:2px;padding:4px;;display:inline-block;"}).append('<span class="glyphicon glyphicon-trash"></span>').on("click", function(){$(this).parent().remove()}))
			.append($("<div>", {class:"well", style:"overflow:auto;"}).append("<h2>This Option links to: </h2>").append(ul)				
				.append($("<button>", {style:"display:none;float:right;", class:"btn btn-primary", name:"hideExtra"}).append("Hide Extra").click(function(){
					var div = upTo(this, "div");
					$(this).hide();
					$(div).find("[name='extra_div']").hide();
					$(div).find("[name='addExtra']").show();
				}))
				.append($("<button>", {class:"btn btn-primary", name:"addExtra", style:"float:right;"}).append("Add Extra").click(function(){
					var div = upTo(this, "div");
					$(this).hide();
					$(div).find("[name='extra_div']").show();
					$(div).find("[name='hideExtra']").show();
				})).append("<br>")
				.append($(extra_div).clone(true)));
	} else if (option == 0){
		li = $("<li>", {class:"list-group-item", style:"padding:5px"})
				.append('<span style="width:15%;display:inline-block;text-align:center">Name: </span>')
				.append($('<input>', {style:"width:30%;display:inline-block;", name:"name", type:"text", class:"form-control", placeholder:"Option"}).on("change", function(){
					generatePreview();}))
				.append('<span style="width:15%;display:inline-block;text-align:center;" >Price: </span>')
				.append($('<input>',{ style:"width:26%;display:inline-block;", name:"price", type:"text", class:"form-control"}).val("$0.00").on("change", function(){
					$(this).val(validatePrice($(this).val()));
					generatePreview();}))
				.append($("<button>", {style:"height:32px;margin-top:-7px;width:12%;border-radius:2px;padding:4px;;display:inline-block;"}).append('<span class="glyphicon glyphicon-trash"></span>').on("click", function(){$(this).parent().remove()}))
			.append($("<div>", {class:"well", style:"overflow:auto;"}).append("<h2>This Option links to: </h2>").append($("<ul>", {name:"extraExtras", class:"list-group"}))
				.append($("<button>", {style:"display:none;float:right;", class:"btn btn-primary", name:"hideExtra"}).append("Hide Extra").click(function(){
					var div = upTo(this, "div");
					$(this).hide();
					$(div).find("[name='extra_div']").hide();
					$(div).find("[name='addExtra']").show();
				}))
				.append($("<button>", {class:"btn btn-primary", name:"addExtra", style:"float:right;"}).append("Add Extra").click(function(){
					var div = upTo(this, "div");
					$(this).hide();
					$(div).find("[name='extra_div']").show();
					$(div).find("[name='hideExtra']").show();
				})).append("<br>")
				.append($(extra_div).clone(true)));
		
	}
	$('#ExtraOptions').append(li);
	return li;
}


function loadDashboard(){
	
	$('#sub_navbar').children("div").hide();
	
	refreshDashboard();
	transitions($('#dashboard_main'))
	
}
function refreshDashboard(){
	var x;
	var frag, row, col1, col2, btn1, btn2;
	var rest
	var total = User.credit;
	
	//sumary div
	
	$("#dash_total_credit").text("$" + User.credit);
	
	
	//each restaurant:
	
	frag = document.createDocumentFragment();
	
	for(x=0;x<User.restaurants.length;++x){
		rest = User.restaurants[x];
		
		
		
		div = $("#dashboard_prototype_restaurant").clone().attr("id", "").show();
		
		if(rest.status == "TESTING"){
			activate_btn = $("<button>", {class:"btn-block btn-info", style:"font-size:25px;padding:12px"}).append("Activate Restaurant");
			activate_btn.on("click", {rest:rest}, function(e){
				var rest_id = e.data.rest.rest_id
				activateRestaurant(rest_id);
			})
			$(div).prepend(activate_btn);
		}
		
		
		$(div).find("[name='rest_title']").text(rest.title);
		
		open_close = $(div).find("[name='close_switch']");
		open_close_status = $(div).find("[name='switch_status']");
		close_switch_label = $(div).find("[name='close_switch_label']");
		
		if(rest.closed){
			$(open_close).prop('checked', true);
			$(open_close_status).text("CLOSED BY OWNER");
			$(close_switch_label).text("OPEN RESTAURANT");
		}else{
			if(rest.status == "TESTING"){
				$(open_close).prop('checked', false);
				$(open_close_status).text("TESTING");
				$(close_switch_label).text("TESTING");
			}else if(rest.open){
				$(open_close).prop('checked', false);
				$(open_close_status).text("OPEN");
				$(close_switch_label).text("CLOSE RESTAURANT");
			}else{
				$(open_close).prop('checked', false);
				$(open_close_status).text("CLOSED FOR NIGHT");
				$(close_switch_label).text("CLOSE RESTAURANT");
			}
		}
				
		$(open_close).on("change", {rest:rest}, function(e){
			OpenClose(e.data.rest);	
		});
		
		last_five = renderRecent(rest.last_five);
		
		$(div).find("[name='recent_order']").append(last_five);
		
		
		$(div).find("[name='btn_menu']").on("click", {rest:rest}, function(e){
				Restaurant = e.data.rest;
				getMenu(renderCategories);
		});	
		$(div).find("[name='btn_orderhistory']").on("click", {rest:rest}, function(e){
				Restaurant = e.data.rest;
				getHistory();
		});	
		$(div).find("[name='btn_restdetails']").on("click", {rest:rest}, function(e){
				Restaurant = e.data.rest;
				getRestDetails();
		});	
		$(div).find("[name='btn_coupons']").on("click", {rest:rest}, function(e){
				Restaurant = e.data.rest;
				getCoupons();
		});
		
		
		$(frag).append(div);
	}
	
	$("#restaurants_list").empty().append(frag);
			
}

function activateRestaurant(rest_id){
	var payload, target, callback;
	
	payload = {"rest_id":rest_id};
	
	callback = function(response){
		if(response.result == "success"){
			
			makeToast("Success:", "Great! Your online restaurant is now visible to the public.");
		}else{
			console.log(response.error);	
		}
		checkUser();
	}
	
	target="https://www.tastes-good.com/uploader/activate_restaurant.php";
	ajaxRequest(target, payload, callback);	
}

function promoteCategory(category){
	var x
	//find category
	for(x=0;x<Restaurant.menu.categories.length;++x){
		if(Restaurant.menu.categories[x].id == category.id){
		 if(x==0){
		 	 //top of list cannot be promoted
		 	 return;
		 } else {
		 	  Restaurant.menu.categories.splice(x-1, 0, Restaurant.menu.categories.splice(x, 1)[0]);
		 	 renderCategories();
		 	 saveMenu();
		 	  return;
		 	 }
		}
 
	}
}

function demoteCategory(category){
	var x
	//find category
	for(x=0;x<Restaurant.menu.categories.length;++x){
		if(Restaurant.menu.categories[x].id == category.id){
		 if(x==Restaurant.menu.categories.length-1){
		 	 //bottom of list cannot be demoted
		 	 return;
		 } else {
		 	 Restaurant.menu.categories.splice(x+1, 0, Restaurant.menu.categories.splice(x, 1)[0]);
		 	 renderCategories();
		 	 saveMenu();
		 	 return;
		 	}
		
		}
 	}
}

function promoteItem(category, item){
	var x;
	for(x=0;x<category.items.length;++x){
		if(category.items[x].id == item.id){
		 if(x==0){
		 	 //top of list cannot be promoted
		 	 return;
		 } else {
		 	 category.items.splice(x-1, 0, category.items.splice(x, 1)[0]);
		 	 renderItems(category);
		 	 saveMenu();
		 	 return;
		 	}
		
		}
 	}
}

function demoteItem(category, item){
	var x;
	for(x=0;x<category.items.length;++x){
		if(category.items[x].id == item.id){
		 if(x==category.items.length-1){
		 	 //bottom of list cannot be demoted
		 	 return;
		 } else {
		 	 category.items.splice(x+1, 0, category.items.splice(x, 1)[0]);
		 	 renderItems(category);
		 	 saveMenu();
		 	 return;
		 	}
		
		}
 	}
}

function initDeleteExtra(extra){
	var item_count, cat_count, xtr_count, x, y, z;
	item_count = 0;
	cat_count = 0;
	xtr_count = 0;
	
	for(x=0;x<Restaurant.menu.extras.length;++x){
		for(y=0;y<Restaurant.menu.extras[x].options.length;++y){
		  for(z=0;z<Restaurant.menu.extras[x].options[y].extras.length;++z){
		  	  if(Restaurant.menu.extras[x].options[y].extras[z] == extra.id){
		  	  xtr_count+=1
		  	  }
		  }
		}
		
	}
	
	
	for(x=0;x<Restaurant.menu.categories.length;++x){
		for(y=0;y<Restaurant.menu.categories[x].extras.length;++y){
			if(Restaurant.menu.categories[x].extras[y] == extra.id){
				cat_count = cat_count+1;
			}
		}
		
		for(y=0;y<Restaurant.menu.categories[x].items;++y){
			for(z=0;z<Restaurant.menu.categories[x].items[y].extras.length;++z){
				if(Restaurant.menu.categories[x].items[y].extras[z] == extra.id){
					item_count = item_count+1;
				}
			}	
		}
	}
		
	$('#extra_category_count').text(cat_count);
	$('#extra_item_count').text(item_count);
	$('#extra_extra_count').text(xtr_count);
	$('#extra_name').text(extra.name);
	
	$("#ConfirmDeleteXtr").modal("show");
	$("#confirm_delete_extra").off("click").on("click", {extra:extra}, function(event){deleteExtra(event.data.extra);});
	
}

function deleteExtra(extra){
	var item_count, cat_count, x, y, z;
	item_count = 0;
	cat_count = 0
	for(x=0;x<Restaurant.menu.extras.length;++x){
		if(Restaurant.menu.extras[x].id == extra.id){
		  Restaurant.menu.extras.splice(x,1);
		  x=x-1;
		  continue;
		}
		
		for(y=0;y<Restaurant.menu.extras[x].options.length;++y){
		  for(z=0;z<Restaurant.menu.extras[x].options[y].extras.length;++z){
		  	  if(Restaurant.menu.extras[x].options[y].extras[z] == extra.id){
		  	  	  Restaurant.menu.extras[x].options[y].extras.splice(z,1);
		  	  	  z=z-1;
		  	  }
		  }
		}
	}
	
	
	
	for(x=0;x<Restaurant.menu.categories.length;++x){
		for(y=0;y<Restaurant.menu.categories[x].extras.length;++y){
			if(Restaurant.menu.categories[x].extras[y] == extra.id){
				 Restaurant.menu.categories[x].extras.splice(y,1);
				 y=y-1
			}
		}
		
		for(y=0;y<Restaurant.menu.categories[x].items;++y){
			for(z=0;z<Restaurant.menu.categories[x].items[y].extras.length;++z){
				if(Restaurant.menu.categories[x].items[y].extras[z] == extra.id){
					Restaurant.menu.categories[x].items[y].extras.splice(z,1);
					z=z-1
				}
			}	
		}
	}
	saveMenu();
	renderExtras();
}


function initDeleteItem(category, item){
	$("#ConfirmDeleteItm").modal("show");
	$("#item_name").text(item.product)
	$("#confirm_delete_item").off("click").on("click", {category:category, item:item}, function(event){deleteItem(event.data.category, event.data.item);});
	
}

function deleteItem(category, item){
	for(x=0;x<category.items.length;++x){
		if(category.items[x].id == item.id){
			 category.items.splice(x, 1);
			 x = x-1;
		 	 renderItems(category);
		 	 saveMenu();
		 }
	}
}

function initDeleteCategory(category){
	$("#ConfirmDeleteCat").modal("show");
	$("#category_contents").text(category.items.length)
	$("#category_name").text(category.category)
	$("#confirm_delete_cat").off("click").on("click", {category:category}, function(event){deleteCategory(event.data.category)});
}


function deleteCategory(category){
	var x
	//find category
	for(x=0;x<Restaurant.menu.categories.length;++x){
		if(Restaurant.menu.categories[x].id == category.id){
		 
		 	 Restaurant.menu.categories.splice(x, 1);
		 	 x=x-1;
		 	 renderCategories();
		 	 saveMenu();
		 	
		}
 
	}
}
	
function initAddCatExtra(category){
	var frag, extra, btn;
	$('#add_extra_target').text("all "+ category.category + ":")
	$('#ChooseExtra').modal("show");
	var frag =  document.createDocumentFragment();
	
	if(Restaurant.menu.extras.length == 0){
		var placeholder
		placeholder = $("<button>", {class:"btn btn-block btn-primary", style:"text-align:center;"}).append("<span style='font-size:16px; padding:15px;'>Use Extra Manager to Create Extras</span>")
			.on("click", function(){
			$('#ChooseExtra').modal("hide");
			renderExtras();});
		$(frag).append(placeholder);
		
	}
	
	
	for(x=0;x<Restaurant.menu.extras.length;++x){
		extra = Restaurant.menu.extras[x];	
		btn = $("<button>", {class:"btn btn-block btn-default"}).append(extra.name)
			.on("click", {extra:extra, category:category}, function(event){
			$('#ChooseExtra').modal("hide"); 
			addCatExtra(event.data.category, event.data.extra); 
			
			})
		$(frag).append(btn);		
	}
	
	$('#add_extra_list').empty().append(frag);
	
	$('#link_extra').off("click").on("click", {category:category}, function(event){addCatExtra(category)});
}

function initAddItemExtra(category, item){
	var frag, extra, btn;
	
	$('#add_extra_target').text(item.product + "("+ category.category + "):")
	
	frag =  document.createDocumentFragment();
	
	if(Restaurant.menu.extras.length == 0){
		var placeholder
		placeholder = $("<button>", {class:"btn btn-block btn-primary", style:"text-align:center;"}).append("<span style='font-size:16px; padding:15px;'>Use Extra Manager to Create Extras</span>")
			.on("click", function(){
			$('#ChooseExtra').modal("hide");
			renderExtras();});
		$(frag).append(placeholder);
		
	}
	
	for(x=0;x<Restaurant.menu.extras.length;++x){
	extra = Restaurant.menu.extras[x];	
	btn = $("<button>", {class:"btn btn-block btn-default"}).append(extra.name)
		.on("click", {extra:extra, category:category, item:item}, function(event){
			$('#ChooseExtra').modal("hide"); 
			addItemExtra(event.data.category, event.data.item, event.data.extra);
			
		})
		$(frag).append(btn);		
	}
	
	$('#add_extra_list').empty().append(frag);
	
	$('#ChooseExtra').modal("show");
}
function initCouponExtra(){
var frag, extra, btn;
	
	$('#add_extra_target').text("Special Item Coupon")
	
	frag =  document.createDocumentFragment();
	
	if(Restaurant.menu.extras.length == 0){
		var placeholder
		placeholder = $("<button>", {class:"btn btn-block btn-primary", style:"text-align:center;"}).append("<span style='font-size:16px; padding:15px;'>Use Extra Manager to Create Extras</span>")
			.on("click", function(){
			$('#ChooseExtra').modal("hide");
			renderExtras();});
		$(frag).append(placeholder);
		
	}
	
	for(x=0;x<Restaurant.menu.extras.length;++x){
	extra = Restaurant.menu.extras[x];	
	btn = $("<button>", {class:"btn btn-block btn-default"}).append(extra.name)
		.on("click", {extra:extra}, function(event){
			var li, row
			li=$("<li>", {class:"list-group-item", name:event.data.extra.id})
			row = $("<div>", {class:"row"})
				  .append($("<div>", {class:"col-xs-8 no-padding"}).append($("<button>", {class:"btn btn-default btn-block"}).append(event.data.extra.name)))
				  .append($("<div>", {class:"col-xs-4 no-padding"}).append($("<button>", {class:"btn btn-default btn-block"}).append("Remove Extra")
					 .click(function(){$(upTo(this, "li")).remove();})));
			$(li).append(row);
			
			$("#coupon_extras").append(li);
			$('#ChooseExtra').modal("hide"); 
			
			
		})
		$(frag).append(btn);		
	}
	
	
	
	$('#add_extra_list').empty().append(frag);
	
	$('#ChooseExtra').modal("show");
	
}

function addCatExtra(category, extra){
	category.extras.push(extra.id)
	renderItems(category);
	saveMenu();
}

function addItemExtra(category, item, extra){
	item.extras.push(extra.id)
	renderItems(category);
	saveMenu();
}

	
function renderCategories(){
	var cat, btn1, btn2, btn3, btn4, btn5, btn6, extra_btn1, extra_btn2, current_div, ul, li, extra, link, item;
	var frag =  document.createDocumentFragment();
	
	state = {page:"menu_categories", rest_id:Restaurant.rest_id}
	setState(state);
	
	
	if(!$("#cat_navbar").is(":visible")){
		$("#sub_navbar").children("div").hide();
		$("#cat_navbar").slideDown();
	}
	
	if(Restaurant.menu.categories.length == 0){
		var placeholder
		placeholder = $("<li>", {class:"list-group-item", style:"text-align:center;"}).append("<h2 style='font-size:16px; padding:15px;'>No Categories in Menu</h2>");
		$(frag).append(placeholder);
		
	}
	
	
	for(x=0; x<Restaurant.menu.categories.length; ++x){
		cat = Restaurant.menu.categories[x];
		
	
		
		btn1 = $('<button>', {class:"btn btn-default btn-block"}).append($("<h3>").text(cat.category)).append($("<h2>").text("CLICK HERE TO ADD/EDIT ITEMS"))
			.click({category:cat}, function(event){renderItems(event.data.category);});
		
		btn2 = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append($("<span>", {class:"glyphicon glyphicon-arrow-up"}))
			.click({category:cat}, function(event){ promoteCategory(event.data.category); });
		btn3 = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append($("<span>", {class:"glyphicon glyphicon-arrow-down"}))
			.click({category:cat}, function(event){ demoteCategory(event.data.category); });
		btn4 = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append($("<span>", {class:"glyphicon glyphicon-pencil"}))
			.click({category:cat}, function(event){ initEditCategory(event.data.category); });
		btn5 = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append($("<span>", {class:"glyphicon glyphicon-trash"}))
			.click({category:cat}, function(event){ initDeleteCategory(event.data.category); });
		
		var category_items = document.createDocumentFragment();
		
		if(cat.items.length == 0){
				var placeholder
				placeholder = $("<li>", {class:"list-group-item", style:"text-align:center;"}).append("<h2 style='font-size:16px; padding:0px;'>Category Has No Items</h2>");
				$(category_items).append(placeholder);
				
		}
			
			
		for(y=0; y<cat.items.length; ++y){
				item = cat.items[y];
				
				
				var product =  $("<div>", {class:"col-xs-8"}).append("<h2>"+item.product+"</h2>");
				var price = $("<div>", {class:"col-xs-4"}).append("<h2>"+validatePrice(item.price)+"</h2>");
				var item_row = $("<div>", {class:"row", style:"border-width:.5px;border-color:darkgrey;border-style:solid;margin:0px;margin-top:10px;"}).append(product).append(price);
				$(category_items).append(item_row);
				
			}
		$(btn1).append(category_items);
		current_div = $("<div>", {class:"row no-padding"}).append($("<div>", {class:"col-xs-3 no-padding"}).append(btn2))
			.append($("<div>", {class:"col-xs-3 no-padding"}).append(btn3))
			.append($("<div>", {class:"col-xs-3 no-padding"}).append(btn4))
			.append($("<div>", {class:"col-xs-3 no-padding"}).append(btn5));
			
		
			
		current_div = $("<div>", {class:"modal-dialog", style:"border-style: solid; border-color: darkgrey; border-width:0.8px; border-radius:6px; width:95%;max-width:900px;margin:auto; margin-top:10px;"}).append(btn1).append(current_div);	
		
		
		$(frag).append(current_div)
	}
	
	$('#categories_list').empty().append(frag);
	transitions($('#menu_categories'))
}
function lookupExtra(extra_id){
	for(k=0;k<Restaurant.menu.extras.length;++k){
		if(Restaurant.menu.extras[k].id == extra_id){
			return Restaurant.menu.extras[k];
		}
	}
}
function lookupCategory(category_id){
	for(k=0;k<Restaurant.menu.categories.length;++k){
		if(Restaurant.menu.categories[k].id == category_id){
			return Restaurant.menu.categories[k];
		}
	}
}


function removeCatExtra(extra_position, category){
	var x;
	category.extras.splice(extra_position, 1);
	saveMenu();
	renderItems(category);
}

function removeItmExtra(extra_position, category, item){
	var x;
	item.extras.splice(extra_position, 1);
	saveMenu();
	renderItems(category);
}



function renderItems(category){
	var item, extra, y;
	
	
	if(typeof(category)!="object"){
		var category_id = history.state.category_id
		category = lookupCategory(category_id);
	}
	
	state = {page:"menu_items", category_id:category.id, rest_id:Restaurant.rest_id}
	setState(state);
	
	var frag =  document.createDocumentFragment();
	if(!$("#item_navbar").is(":visible")){
		$("#sub_navbar").children("div").hide();
		$("#item_navbar").slideDown();
	}
	
	$('#new_item').click(function(){initNewItem(category);});
	$("[name='items_category']").text(category.category);
	div = $("<div>", {style:"padding-left:15px; padding-right:15px;border-radius:0px"});
	
	if(category.extras.length == 0){
		var placeholder
		placeholder = $("<button>", {class:"btn btn-block btn-default", style:"text-align:center;"}).append("<h2 style='font-size:16px; padding:0px;'>Category Has No Extras</h2>");
		$(div).append(placeholder);
		
	}
	
	for(y=0; y<category.extras.length;++y){
		extra = lookupExtra(category.extras[y]);
	  row = $("<div>", {class:"row"})
		  .append($("<div>", {class:"col-xs-8 no-padding"}).append($("<button>", {class:"btn btn-default btn-block"}).append(extra.name)
			  .click({extra:extra}, function(event){initEditExtra(event.data.extra)})))
		  .append($("<div>", {class:"col-xs-4 no-padding"}).append($("<button>", {class:"btn btn-default btn-block"}).append("Remove Extra")
			 .click({extra_position:y, category:category}, function(event){removeCatExtra(event.data.extra_position, event.data.category)})));
	  $(div).append(row);
	
	}
	
	
	
	
	link = $('<button>', {class:"btn btn-primary btn-block", style:"border-radius:0px;"}).append($('<span>', {class:"glyphicon glyphicon-link"}).append(" Add Extras to Category"))
		.click({category:category}, function(event){initAddCatExtra(event.data.category)});
		
	$('#items_category_extras').empty().append(div).append(link);
	
	if(category.items.length == 0){
		var placeholder
		placeholder = $("<li>", {class:"list-group-item", style:"text-align:center;"}).append("<h2 style='font-size:16px; padding:0px;'>Category Has No Items</h2>");
		$(frag).append(placeholder);
		
	}
	
	
	for(x=0; x<category.items.length; ++x){
		item = category.items[x];
		
		
		product =  $("<div>", {class:"col-xs-8"}).append("<h3>"+item.product+"</h3>");
		price = $("<div>", {class:"col-xs-4"}).append("<h3>"+validatePrice(item.price)+"</h3>");
		row1 = $("<div>", {class:"row"}).append(product).append(price)
			.click({item:item, category:category}, function(event){ initEditItem(event.data.category, event.data.item); });
		
		description = $("<div>", {class:"col-xs-12"}).append("<h2>"+item.description+"</h2>");
		row2 = $("<div>", {class:"row"}).append(description)
			.click({item:item, category:category}, function(event){ initEditItem(event.data.category, event.data.item); });
		
		btn1 = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append($("<span>", {class:"glyphicon glyphicon-arrow-up"}))
			.click({item:item, category:category}, function(event){ promoteItem(event.data.category, event.data.item); });
		btn2 = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append($("<span>", {class:"glyphicon glyphicon-arrow-down"}))
			.click({item:item, category:category}, function(event){ demoteItem(event.data.category, event.data.item); });
		btn3 = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append($("<span>", {class:"glyphicon glyphicon-pencil"}))
			.click({item:item, category:category}, function(event){ initEditItem(event.data.category, event.data.item); });
		btn4 = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append($("<span>", {class:"glyphicon glyphicon-trash"}))
			.click({item:item, category:category}, function(event){ initDeleteItem(event.data.category, event.data.item); });
		
		row3 = $("<div>", {class:"row", style:"margin:0px;margin-top:10px;"}).append($("<div>", {class:"col-xs-3 no-padding"}).append(btn1))
			.append($("<div>", {class:"col-xs-3 no-padding"}).append(btn2))
			.append($("<div>", {class:"col-xs-3 no-padding"}).append(btn3))
			.append($("<div>", {class:"col-xs-3 no-padding"}).append(btn4))
					
		
		div = $("<div>", {style:"padding-left:15px; padding-right:15px;border-radius:0px"});
		$(div).append("<h3>Extras linked to <span style='color:#633E26;'>"+item.product+"</span></h3>");
		
		if(item.extras.length == 0){
			var placeholder
			placeholder = $("<button>", {class:"btn btn-block btn-default", style:"text-align:center;"}).append("<h2 style='font-size:16px; padding:0px;'>Item Has No Extras</h2>");
			$(div).append(placeholder);
			
		}
		
		for(y=0; y<item.extras.length;++y){
			extra = lookupExtra(item.extras[y]);
			row = $("<div>", {class:"row"})
			  .append($("<div>", {class:"col-xs-8 no-padding"}).append($("<button>", {class:"btn btn-default btn-block"}).append(extra.name)
				  .click({extra:extra}, function(event){initEditExtra(event.data.extra)})))
			  .append($("<div>", {class:"col-xs-4 no-padding"}).append($("<button>", {class:"btn btn-default btn-block"}).append("Remove Extra")
				 .click({extra_position:y, category:category, item:item}, function(event){removeItmExtra(event.data.extra_position, event.data.category, event.data.item)})));
		  $(div).append(row);
		
		}
		
		link = $('<button>', {class:"btn btn-primary btn-block",  style:"border-radius:0px;"}).append($('<span>', {class:"glyphicon glyphicon-link"}).append(" Add Extras to Item"))
			.click({category:category, item:item}, function(event){initAddItemExtra(event.data.category, event.data.item)});
		
		div2 = $("<div>", {style:"background-color:white;margin-top:10px;margin-bottom:10px;padding:10px; border-radius:6px; border-style:solid; border-width:0.8px; border-color:dark-grey;"}).append(row1).append(row2).append(row3).append(div).append(link);
		$(frag).append(div2);
		
	}
		$('#item_list').empty().append(frag);
		transitions($('#menu_items'));
}		
	




function renderExtras(){
	var frag, xtr;
	state = {page:"menu_extras", rest_id:Restaurant.rest_id}
	setState(state);
	
	
	if(!$("#extras_navbar").is(":visible")){
		$("#sub_navbar").children("div").hide();
		$("#extras_navbar").slideDown();
	}	
	
	
	frag =  document.createDocumentFragment();
	
	if(Restaurant.menu.extras.length == 0){
		var placeholder
		placeholder = $("<li>", {class:"list-group-item", style:"text-align:center;"}).append("<h2 style='font-size:16px; padding:15px;'>Add Extras Here</h2>");
		$(frag).append(placeholder);
		
	}
	
	for(x=0; x<Restaurant.menu.extras.length; ++x){
		extra = Restaurant.menu.extras[x];
		
		title =  $("<div>", {class:"col-xs-12"}).append("<h3>"+extra.name+"</h3>");
		
		if(extra.type==1){
			xtr_type = "Choose Only One"
		}else if(extra.type==2){
			xtr_type = "Can Choose Many"	
		}
		
		type = $("<div>", {class:"col-xs-12"}).append($("<h2>").append("<strong>Extra Type: </strong>").append(xtr_type));
		question = $("<div>", {class:"col-xs-12"}).append($("<h2>").append("<strong>Extra Question: </strong>").append(extra.question));
		row = $("<div>", {class:"row"}).append(title).append(type).append(question)
			
			
		btn = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append(row)
			.click({extra:extra}, function(event){ initEditExtra(event.data.extra); });
				
		btn2 = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append($("<span>", {class:"glyphicon glyphicon-pencil"}))
			.click({extra:extra}, function(event){ initEditExtra(event.data.extra); });
		btn3 = $('<button>', {class:"btn btn-default btn-block", style:"border-radius:0px;"}).append($("<span>", {class:"glyphicon glyphicon-trash"}))
			.click({extra:extra}, function(event){ initDeleteExtra(event.data.extra); });
		
		row = $("<div>", {class:"row", style:"margin:0px;margin-top:0px;"}).append($("<div>", {class:"col-xs-6 no-padding"}).append(btn2))
			.append($("<div>", {class:"col-xs-6 no-padding"}).append(btn3));
			
			
		
		div2 = $("<div>", {style:"background-color:white;margin-top:10px;margin-bottom:10px;padding:0px; border-radius:6px; border-style:solid; border-width:0.8px; border-color:dark-grey;"}).append(btn).append(row);
		$(frag).append(div2);
		
	}
	
	$('#extra_list').empty().append(frag);
	transitions($('#menu_extras'))
}


function getMenu(callbackFunction){
	var payload, callback, target;
	var rest_id
	
	rest_id = Restaurant.rest_id
	
	
	if(Restaurant.menu !== null){
		//handle existing menu 
		callbackFunction();
		return;
	}
	
	callback = function(response){
		if(response.result=="success"){
			Restaurant.menu = response.data;
			this.callback.callback();	
		}else{
			alert(response.error);
		}
		
	}
	callback.callback = callbackFunction;	
	target="https://www.tastes-good.com/api_scripts/restaurant_menu.php?rest_id="+rest_id;
	ajaxRequest(target, payload, callback);

}


function renderRecent(recentOrders){
	var frag, result, order;
	var button, row, id, name, payment, delivery, date, time;
	var extend, h2,  phone, email, address, charge, tip, extend_btn, pdf, confirm, reject, credit, cancel, refund;
	var btn_status, payment_type, delivery;
	frag =  document.createDocumentFragment();
	if (recentOrders.length == 0){
		$(frag).append("<h2>No Recent Orders Found.</h2>")
		$('#recent_order').empty().append(frag);
		return;	
	}
	for(x=0; x<recentOrders.length; ++x){
		order = recentOrders[x];
		
		switch(order.confirmed){
			case 1:
				btn_status = "btn-default";
				break;
			case -1:
				btn_status = "btn-danger";
				break
			default:
				btn_status = "btn-warning";
			}
			
			
		button = $("<button>", {class:"btn-block "+btn_status});
		row = $("<div>", {class:"row no-padding"});
		id = $("<div>", {class:"col-xs-1 no-padding"}).text(order.order_id);
		name = $("<div>", {class:"col-xs-2 no-padding"}).text(order.user.fname + " "+ order.user.lname);
		if (order.paymentType == "online"){payment_type = "PAID";}else{payment_type = "CASH";};
			
		payment = $("<div>", {class:"col-xs-2 no-padding"}).text(payment_type);
		
		if (order.deliveryOption){delivery = "DELIVERY";} else {delivery = "PICKUP"; }
		
		delivery =$("<div>", {class:"col-xs-2 no-padding"}).text(delivery);
		date =$("<div>", {class:"col-xs-3 no-padding"}).text(order.request_date);
		time = $("<div>", {class:"col-xs-2 no-padding"}).text(order.request_time);
		
		$(row).append(id).append(name).append(payment).append(delivery).append(date).append(time);
		$(button).append(row)
			.click({order_id:order.order_id}, function(event){
				$("#order_details_"+event.data.order_id).toggle();	
			});
		
		extend = $("<div>", {name:"order_expand", id:"order_details_"+order.order_id, style:"display:none; background-color:#f0ffff; margin:0;padding:15px; border-width:0 2px 0 2px; border-color:#633E26;;border-style:solid;"});
		h2 = $("<h2>", {class:"row", style:"line-height:20px;font-size:15px"});					
		phone = $("<div>", {class:"col-xs-12 no-padding"}).append($("<strong>Customer Phone: </strong>")).append(order.user.phone);
		$(h2).append(phone);
		
		if(order.deliveryOption){
		charge  = $("<div>", {class:"col-xs-6 no-padding"}).append($("<strong>Delivery Charge: </strong>")).append(validatePrice(order.deliveryCharge));
		tip  = $("<div>", {class:"col-xs-6 no-padding"}).append($("<strong>Delivery Tip: </strong>")).append(validatePrice(order.tip));
		address  = $("<div>", {class:"col-xs-12 no-padding"}).append($("<strong>Delivery Address: </strong>")).append(order.address.address);
		$(h2).append(charge).append(tip).append(address);
		}
		$(extend).append(h2)
		
		extend_btn = $("<div>", {class:"row"});
		
		var link 
		link = "https://www.tastes-good.com/uploader/push_pdf.php?order_id="+order.order_id
		if(window.isphone){
			link = link + "&isphone=1";	 
		  }
		
		pdf = $("<div>", {class:"col-xs-12"}).append($("<a>", {class:"btn btn-default btn-block"}).append("Order PDF"))
				.on("click", {link:link}, function(event){showPDF(event.data.link);});
		
		switch(order.confirmed){
		case 0:
			pdf = $("<div>", {class:"col-xs-12"}).append($("<a>", {class:"btn btn-default btn-block"}).append("Order PDF"))
				.on("click", {link:link}, function(event){showPDF(event.data.link);});
			confirm = $("<div>", {class:"col-xs-6"}).append($("<button>", {class:"btn btn-success btn-block"}).append("Confirm Order"))
				.click({order_id: order.order_id}, function(event){confirmOrder(event.data.order_id);});
			reject = $("<div>", {class:"col-xs-6"}).append($("<button>", {class:"btn btn-danger btn-block"}).append("Reject Order"))
				.click({order_id: order.order_id}, function(event){rejectOrder(event.data.order_id);});
			
			$(extend_btn).append(pdf).append(confirm).append(reject);	
			break;
		case -1:
			$(extend_btn).append($("<div>", {class:"col-xs-12"}).append($("<button>", {class:"btn btn-danger btn-block"}).append("Order is Rejected!")));
				
			
			break;
		case 1:
			pdf = $("<div>", {class:"col-xs-3"}).append($("<a>", {class:"btn btn-default btn-block"}).append("Order PDF"))
				.on("click", {link:link}, function(event){showPDF(event.data.link);});
			credit =  $("<div>", {class:"col-xs-3"}).append($("<button>", {class:"btn btn-default btn-block"}).append("Store Credit"))
				.click({order_id: order.order_id}, function(event){storeCredit(event.data.order_id);});
			cancel = $("<div>", {class:"col-xs-3"}).append($("<button>", {class:"btn btn-default btn-block"}).append("Cancel Order"))
				.click({order_id: order.order_id}, function(event){cancelOrder(event.data.order_id);});
			refund = $("<div>", {class:"col-xs-3"}).append($("<button>", {class:"btn btn-default btn-block"}).append("Partial Refund"))
				.click({order_id: order.order_id}, function(event){refundOrder(event.data.order_id);});	
			$(extend_btn).append(credit).append(cancel).append(refund).append(pdf);						
			break;
		}
		$(extend).append(extend_btn);
		$(frag).prepend($(button).append(extend));
	}
	return frag;
}


function confirmOrder(){
	var target, payload, callback;
	payload="";
	
	callback = function(response) {
		if (response.result == "success"){
		//show success modal
		 $('#order_confirmed').modal('show');
					  
		}else{
		 alert(response.error); 
		
		}
		 checkUser();
	}
    
    target = 'https://www.tastes-good.com/uploader/order_confirm.php?orderId='+confirmOrder.order_id;
	ajaxRequest(target, payload, callback);
   

}
function restLogin(){
		
		var payload, target, callback;
		
		//verify that email does not already exist in local storage.  
		
		var payload = {'email':$('#userId').val(), 'password':$('#userPwd').val()};	
		
		callback = function(response){
			if(response.result == "success"){
				User = response.data;
				localStorage.setItem('auth_token', User.auth_token)
				checkUser();
				
			}else{
				$('#userLogin').modal('show');
				$('#userNew').hide();
								
				$("#email_noexist").hide();
				$("#email_sent").hide();
				$("#forgotPwd").hide();
				$("#not_rest").hide();
				$("#wrong_pwd").show();
			}
		}
		// Open the connection.
		target = "https://www.tastes-good.com/uploader/userLogin.php"
		ajaxRequest(target, payload, callback);
}

function rejectOrder(){
	var payload, target, callback;
	payload = ""
	callback = function(response) {
          	if (response.result == "success"){
          		//show success modal
          		$('#order_rejected').modal('show');
          		
          	  }else{
          	  	  console.log(response.error);
          	  }
          	   checkUser();
            
    }
    
    target = 'https://www.tastes-good.com/uploader/order_reject.php?orderId='+rejectOrder.order_id;
	ajaxRequest(target, payload, callback);
}
function storeCredit(order_id){
	
	var x;
	var order;
	
	for(x=0; x<recentOrders.length; ++x){
		if(order_id == recentOrders[x].order_id){
			order = recentOrders[x];
		}
	}
	
	$('#credit').modal('show');
	$('#credit_id').text(order.order_id);
	$('#credit_name').text(order.user.fname + " " +order.user.lname);
	$('#credit_phone').text(order.user.phone);
	$('#credit_total').text(order.total);

	
	
	$('#submit_credit').unbind("click").on('click', {order_id:order.order_id}, function(event){
		
		
		var credit = /[\d]+[.]{0,1}[\d]{0,2}/.exec($('#credit_amount').val())[0];
		var message = $('#credit_message').val();
		
		if(credit>0){
			var payload, target, callback;
			var rest_id;
			payload = {order_id:event.data.order_id, credit:credit, message:message};
			
			callback = function (response){
				if (response.result=="success"){
					setTimeout(function(){ $('#credit_order_received').modal("show"); }, 1000);
				}else{
					alert(response.error);
				}
			}
			target = "https://www.tastes-good.com/uploader/credit_order.php";
			ajaxRequest(target, payload, callback);	
						
		
			$('#credit').modal("hide");	
		}
			
	});

}
function cancelOrder(order_id){
	
	var x;
	var order;
	
	for(x=0; x<recentOrders.length; ++x){
		if(order_id == recentOrders[x].order_id){
			order = recentOrders[x];
		}
	}
			
	$('#cancel_order').modal('show');
	$('#cancel_id').text(order.order_id);
	$('#cancel_name').text(order.user.fname + " " +order.user.lname);
	$('#cancel_phone').text(order.user.phone);
	$('#cancel_total').text(order.total);
	$('#submit_cancel').unbind("click").on('click', {order_id:order.order_id}, function(event){
		var target, payload, callback;
		payload = {order_id:event.data.order_id, reason: $('#cancel_reason').val()};
		
		callback = function (response){
			setTimeout(function(){ $('#cancel_order_received').modal("show"); }, 1000);
			console.log(response);	
		}
			
		target = "https://www.tastes-good.com/uploader/cancel_order.php";
		ajaxRequest(target, payload, callback);
		
		$('#cancel_order').modal("hide");	
			
	});
}
function refundOrder(order_id){
	var order_id = $(event.target).data("order_id");
			var x;
			var order;
			
			for(x=0; x<orders.length; ++x){
				if(order_id == orders[x].order_id){
					order = orders[x];
				}
			}
			
			$('#refund').modal('show');
			$('#refund_id').text(order.order_id);
			$('#refund_name').text(order.user.fname + " " +order.user.lname);
			$('#refund_phone').text(order.user.phone);
			$('#refund_total').text(order.total);
			$('#max_refund').text(order.total);
			
			
			
			$('#submit_refund').unbind("click").on('click', {order_id:order.order_id}, function(event){
				var payload, target, callback;
				var max, refund, reason; 
				
				
				max = $('#max_refund').text();
				var refund = /[\d]+[.]{0,1}[\d]{0,2}/.exec($('#refund_amount').val())[0];
				if(refund > max || !refund ){
					alert("You must input a valid refund!");
					return;
				}
				payload = {order_id:event.data.order_id, refund:refund, reason:$('#refund_reason').val()};
				callback = function(response){
					if(response.result == "success"){
					console.log(response);
					setTimeout(function(){ $('#refund_order_received').modal("show"); }, 1000);
					}else{
						alert(response.error);
					}
				}
							
				
				target = "https://www.tastes-good.com/uploader/refund_order.php";
				ajaxRequest(target, payload, callback);
						$('#refund').modal("hide");	
			});
}