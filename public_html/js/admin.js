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
	
	initGlobalVars();
	localStorageShim();	
    initErrors();
    checkUser();
   	initButtons();
    initModals();
    initKeystrokes();
    setupPush();
}

function initKeystrokes(){
	
}
function initGlobalVars(){
	active_div = 0;
	recentOrders = [];
}


function initErrors(){
	
}

function initRestLogin(){
	$('#main_login').show();
	$("#wrong_pwd").hide();
	$("#not_rest").hide();
	$("#email_noexist").hide();
	$("#email_sent").hide();
	$("#forgotPwd").hide();
	
	$('#existing_user_btn').show();

}


function initButtons(){
	$('#dropdown_btn').click(function(){
		$('#navbar_dropdown').fadeToggle();	
	})
	
	$("#manage_orders").on("click", function(){
		getOrders(renderOrders);
	});
	$("#manage_users").on("click", function(){
		getUsers(renderUsers);
	});
	$("#manage_restaurants").on("click", function(){
		getRestaurants(renderRestaurants);
	});
	$("#manage_payments").on("click", function(){
		getPayments(renderPayments);
	});
	$("#manage_messages").on("click", function(){
		getMessages(renderMessages);
	});
	$('#dropdown_login').on("click",function(){
		$('#navbar_dropdown').hide();	
		initRestLogin();
		transitions($("#admin_login"));
	});
	
	$('#dropdown_logout').click(function(){
		//google signout
		//var auth2 = gapi.auth2.getAuthInstance();
		//auth2.signOut()
		
		//facebook signout:
		//FB.logout()
		$('#navbar_dropdown').hide();
			
		var payload, target, callback;
		
		payload = ""
		callback = function(response) {
			if(response.result=="success"){
			
				User={user_id:0};
				refreshUser();
				initRestLogin();
				transitions($("#admin_login"));
			}else{
				console.log(response.error);
			}
		}
	
		target = 'https://www.tastes-good.com/uploader/user_logout.php';
		ajaxRequest(target, payload, callback);
		
			
	});
	
	$('#submit_login').click(function() {
		var payload, callback, target;
		
		payload = {email:$('#userId').val(), password:$('#userPwd').val()}	
		
		callback = function(response) {
			if(response.result == "success"){
				User = response.data;
				refreshUser();
				checkState(history.state);
				
			
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
}

function initModals(){
	
	
}

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
		if(User.user_id){
			checkState(history.state);
		}else{
			initRestLogin();
			transitions($("#admin_login"));
		}
		}
	};  
				
	// Open the connection.
	target = 'https://www.tastes-good.com/api_scripts/user.php';
	ajaxRequest(target, payload, callback);
		
}


function checkHash(){
	var rest_id, coupon_code;
	
	if(!window.location.hash.length && !window.location.search.length){
		return false;	
	}
	
	params = window.location.hash.split('#')[1];
	params = params.split('$');
	action = window.location.search.split('?',2)[1];
	
	switch(action){
		
	default:
		return false;
	}
			
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
	case "message" :
		getMessages(renderMessages);
		break;
	case "restaurant":
		getRestaurants(renderRestaurants);
		break;
	case "order":
		getOrders(renderOrders);
		break;
	case "user":
		getUsers(renderUsers);
		break;
	case"payment":
		getPayments(renderPayments);
		break;
	
	
    default:
        loadDashboard();
           
        }
} else {
loadDashboard();
}   
}


function loadDashboard(){
	
	transitions($("#dashboard_main"));
}


function getOrders(callbackFunction, condition){
	var payload, callback, target;
	if(typeof(condition)=="object"){
		payload = condition
	}else{
		payload = "";
	}
	callback = function(response){
		if(response.result=="success"){
			Orders = response.data;
			this.callback.callbackFunction();	
		}else{
			alert(response.error);
		}
		
	}
	callback.callbackFunction = callbackFunction;
	target="https://www.tastes-good.com/api_scripts/admin/get_orders.php";
	ajaxRequest(target, payload, callback);
}
function getUsers(callbackFunction, condition){
	var payload, callback, target;
	if(typeof(condition)=="object"){
		payload = condition
	}else{
		payload = "";
	}
	
	callback = function(response){
		if(response.result=="success"){
			Users = response.data;
			this.callback.callbackFunction();	
		}else{
			alert(response.error);
		}
		
	}
	callback.callbackFunction = callbackFunction;
	target="https://www.tastes-good.com/api_scripts/admin/get_users.php";
	ajaxRequest(target, payload, callback);
	
}
function getRestaurants(callbackFunction, condition){
	var payload, callback, target;
	if(typeof(condition)=="object"){
		payload = condition
	}else{
		payload = "";
	}
	callback = function(response){
		if(response.result=="success"){
			Restaurants = response.data;
			this.callback.callbackFunction();	
		}else{
			alert(response.error);
		}
		
	}
	callback.callbackFunction = callbackFunction;
	target="https://www.tastes-good.com/api_scripts/admin/get_restaurants.php";
	ajaxRequest(target, payload, callback);
	
}
function getPayments(callbackFunction, condition){
	var payload, callback, target;
	if(typeof(condition)=="object"){
		payload = condition
	}else{
		payload = "";
	}
	callback = function(response){
		if(response.result=="success"){
			Payments = response.data;
			this.callback.callbackFunction();	
			
		}else{
			alert(response.error);
		}
		
	}
	callback.callbackFunction = callbackFunction;
	target="https://www.tastes-good.com/api_scripts/admin/get_payments.php";
	ajaxRequest(target, payload, callback);
	
}
function getMessages(callbackFunction, condition){
	var payload, callback, target;
	if(typeof(condition)=="object"){
		payload = condition
	}else{
		payload = "";
	}
	callback = function(response){
		if(response.result=="success"){
			Messages = response.data
			this.callback.callbackFunction();		
		}else{
			alert(response.error);
		}
		
	}
	callback.callbackFunction = callbackFunction;
	target="https://www.tastes-good.com/api_scripts/admin/get_messages.php";
	ajaxRequest(target, payload, callback);
	
}

function renderOrders(){
	var pending, confirmed, rejected;
	var flag_pending = flag_confirmed = flag_rejected = 0;
	pending = document.createDocumentFragment();
	confirmed = document.createDocumentFragment();
	rejected = document.createDocumentFragment();
	
	
	
	for(x=0;x<Orders.length;++x){
	 	var order = Orders[x];
	 	var button, row, id, date, restaurant, user, total;
	 	
	 	id = $("<div>", {class:"col-xs-1 no-padding"}).append(order.order_id +".");
	 	date= $("<div>", {class:"col-xs-3 no-padding"}).append(order.requestDate + " " +order.requestTime);
	 	restaurant= $("<div>", {class:"col-xs-3 no-padding"}).append(order.rest_title);
	 	user= $("<div>", {class:"col-xs-3 no-padding"}).append(order.user_name);
	 	total= $("<div>", {class:"col-xs-2 no-padding"}).append(validatePrice(order.total));
	 	
	 	row = $("<div>", {class:"row no-padding"}).append(id).append(date).append(restaurant).append(user).append(total);
	 	button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
	 	
	 	$(button).on("click", {order_id:order.order_id}, function(e){
	 		initOrderDetail(e.data.order_id);	
	 	});
	 	
	 	switch (order.confirmed){
	 	case 0:
	 		flag_pending = 1;
	 		$(pending).append(button);
	 		break;
	 	case -1:
	 		flag_rejected = 1;
	 		$(rejected).append(button);
	 		break;
	 	case 1:
	 		flag_confirmed =1;
	 		$(confirmed).append(button);
	 		break;
	 	default:
	 		alert("unknown order confirmation code");
	 	}
	}
	
	if (flag_pending == 0 ){
		button = $("<button>", {class:"btn btn-default btn-block"}).append("Found No Pending Orders");
		$(pending).append(button);
	}
	
	if (flag_rejected == 0 ){
		button = $("<button>", {class:"btn btn-default btn-block"}).append("Found No Rejected Orders");
		$(rejected).append(button);
	}
	
	if (flag_confirmed == 0 ){
		button = $("<button>", {class:"btn btn-default btn-block"}).append("Found No Confirmed Orders");
		$(confirmed).append(button);
	}
	
	$("#pending_orders").empty().append(pending);
	$("#rejected_orders").empty().append(rejected);
	$("#confirmed_orders").empty().append(confirmed);
	
	state = {page:"order"};
	setState(state);	
	transitions($("#orders_div"));
}
function renderUsers(){
	var user_list;	
	user_list = document.createDocumentFragment();
	for(x=0;x<Users.length;++x){
	 	var user = Users[x];
	 	var button, row, id, name, email, sales, admin, rest;
	 	
	 	id = $("<div>", {class:"col-xs-2 no-padding"}).append(user.user_id +".");
	 	name= $("<div>", {class:"col-xs-3 no-padding"}).append(user.fname + " " +user.lname);
	 	email= $("<div>", {class:"col-xs-4 no-padding"}).append(user.email);
	 	
	 	if(user.is_sales){
	 		sales= $("<div>", {class:"col-xs-1 no-padding"}).append($("<span>", {class:"glyphicon glyphicon-ok"}));
	 	}else{
	 		sales= $("<div>", {class:"col-xs-1 no-padding"}).append($("<span>", {class:"glyphicon glyphicon-remove"}));	
	 	}
	 	
	 	if(user.is_admin){
	 		admin= $("<div>", {class:"col-xs-1 no-padding"}).append($("<span>", {class:"glyphicon glyphicon-ok"}));
	 	}else{
	 		admin= $("<div>", {class:"col-xs-1 no-padding"}).append($("<span>", {class:"glyphicon glyphicon-remove"}));	
	 	}
	 	
	 	if(user.is_rest){
	 		rest= $("<div>", {class:"col-xs-1 no-padding"}).append($("<span>", {class:"glyphicon glyphicon-ok"}));
	 	}else{
	 		rest= $("<div>", {class:"col-xs-1 no-padding"}).append($("<span>", {class:"glyphicon glyphicon-remove"}));	
	 	}
	 		 	
	 	row = $("<div>", {class:"row no-padding"}).append(id).append(name).append(email).append(sales).append(admin).append(rest);
	 	button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
	 	
	 	$(button).on("click", {user_id:user.user_id}, function(e){
	 		initUserDetail(e.data.user_id);	
	 	});
	 	
	 	$(user_list).append(button);
	 	
	}
	
	$("#user_list").empty().append(user_list);
	
	state = {page:"user"};
	setState(state);
	transitions($("#users_div"));	
}
function renderRestaurants(){
	var pending_flag = active_flag = prospect_flag = testing_flag = 0;
	var active_rest, pending_rest, prospect_rest, other_rest, testing_rest;
	
	active_rest = document.createDocumentFragment();
	pending_rest = document.createDocumentFragment();
	prospect_rest = document.createDocumentFragment();
	other_rest = document.createDocumentFragment();
	testing_rest = document.createDocumentFragment();
	
	for(x=0;x<Restaurants.length;++x){
	 	var rest = Restaurants[x];
	 	var button, row, id, title, phone, email, status;
	 	
	 	id = $("<div>", {class:"col-xs-1 no-padding"}).append(rest.rest_id +".");
	 	title = $("<div>", {class:"col-xs-3 no-padding"}).append(rest.title);
	 	phone = $("<div>", {class:"col-xs-3 no-padding"}).append(rest.phone);
	 	email= $("<div>", {class:"col-xs-3 no-padding"}).append(rest.email);
	 	status = $("<div>", {class:"col-xs-2 no-padding"}).append(rest.status)
	 	
	 		 	
	 	row = $("<div>", {class:"row no-padding"}).append(id).append(title).append(phone).append(email).append(status);
	 	button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
	 	
	 	$(button).on("click", {rest_id:rest_id}, function(e){
	 		initRestDetail(e.data.rest_id);	
	 	});	 	
	 	
	 	switch(rest.status){
	 	case "ACTIVE":
	 		active_flag = 1;
	 		$(active_rest).append(button);
	 		break;
	 	case "PROSPECT":
	 		prospect_flag =1;
	 		$(prospect_rest).append(button);
	 		break;
	 	case "PENDING":
	 		pending_flag = 1;
	 		$(pending_rest).append(button);
	 		break;
	 	case "TESTING":
	 		testing_flag = 1;
	 		$(testing_rest).append(button);
	 		break;
	 	default:
	 		$(other_rest).append(button);
	 		break;
	 	 		
	 	}
	}
	 	
	if (pending_flag == 0 ){
		button = $("<button>", {class:"btn btn-default btn-block"}).append("Found No Pending Restaurants");
		$(pending_rest).append(button);
	}
	
	if (active_flag == 0 ){
		button = $("<button>", {class:"btn btn-default btn-block"}).append("Found No Active Restaurants");
		$(active_rest).append(button);
	}
	
	if (prospect_flag == 0 ){
		button = $("<button>", {class:"btn btn-default btn-block"}).append("Found No Prospective Restaurants");
		$(prospect_rest).append(button);
	}
	
	if (testing_flag == 0 ){
		button = $("<button>", {class:"btn btn-default btn-block"}).append("Found No Testing Restaurants");
		$(testing_rest).append(button);
	}
	
	$("#rest_active").empty().append(active_rest);
	$("#rest_pending").empty().append(pending_rest);
	$("#rest_prospect").empty().append(prospect_rest);
	$("#rest_testing").empty().append(testing_rest);
	$("#rest_other").empty().append(other_rest);
	
	
	state = {page:"restaurant"};
	setState(state);
	transitions($("#restaurant_div"));	
}
function renderPayments(){
	var payment_list;
	
	
	callbackFunction = function(){
	    var user_list;
	    var user
	    var button, row, id, name, credit;
	    
	    user_list= document.createDocumentFragment();
	    
	    for(x=0;x<Users.length;++x){
			user = Users[x];
				
			id = $("<div>", {class:"col-xs-2 no-padding"}).append(user.user_id +".");
			name= $("<div>", {class:"col-xs-6 no-padding"}).append(user.fname + " " +user.lname);
			credit= $("<div>", {class:"col-xs-4 no-padding"}).append(user.credit);
							
			row = $("<div>", {class:"row no-padding"}).append(id).append(name).append(credit);
			button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
			
			$(button).on("click", {user:user}, function(e){
				initUserDetail(e.data.user);	
			});
			
			$(user_list).append(button);
	 	
		}
		$("#payment_pending").empty().append(user_list);
	}
	
	condition = {payment_pending:1}
	getUsers(callbackFunction, condition);
	
	payment_list = document.createDocumentFragment();
	var payment;
	var button, row, id, timestamp, name, amount;
	
	for(x=0;x<Payments.length;++x){
	payment = Payments[x];
	id = $("<div>", {class:"col-xs-2 no-padding"}).append(payment.id +".");
	timestamp = $("<div>", {class:"col-xs-2 no-padding"}).append(payment.timestamp);
	name = $("<div>", {class:"col-xs-2 no-padding"}).append(payment.fname + " " +payment.lname);
	amount = $("<div>", {class:"col-xs-2 no-padding"}).append(validatePrice(payment.amount));
	
	
	row = $("<div>", {class:"row no-padding"}).append(id).append(timestamp).append(name).append(amount);
	button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
	
	$(button).on("click", {payment_id:payment.id}, function(e){
		initPaymentDetail(e.data.payment_id);	
	});
	
	$(payment_list).append(button);
	
	}
	
	
	$("#payment_recent").empty().append(payment_list);
	state = {page:"payment"};
	setState(state);
	
	transitions($("#payments_div"));	
}

function renderMessages(){
	
	state = {page:"message"};
	setState(state);
	transitions($("#message_div"));
}

function searchUser(){
	var condition;
	if($("#search_user_id").val()){
	  condition = {user_id:$("#search_user_id").val()}
	}else if($("#search_user_email").val()){
	  condition = {email:$("#search_user_email").val()}
	}else if($("#search_user_rest").val()){
	  condition = {rest_id:$("#search_user_rest").val()}
	}else if($("#search_user_order").val()){
	  condition = {order_id:$("#search_user_order").val()}
	}else {
		condition = ""
	}
	getUsers(renderUsers, condition);
}

function searchOrder(){
	var condition;
	if($("#search_order_id").val()){
	  condition = {order_id:$("#search_order_id").val()}
	}else if($("#search_order_rest").val()){
	  condition = {rest_id:$("#search_order_rest").val()}
	}else if($("#search_order_user").val()){
	  condition = {user_id:$("#search_order_user").val()}
	}else {
		condition = ""
	}
	getOrders(renderOrders, condition);
}

function searchRest(){
	
	var condition;
	if($("#search_rest_id").val()){
	  condition = {rest_id:$("#search_rest_id").val()}
	}else if($("#search_rest_email").val()){
	  condition = {email:$("#search_rest_email").val()}
	}else if($("#search_rest_user").val()){
	  condition = {user_id:$("#search_rest_user").val()}
	}else if($("#search_rest_order").val()){
	  condition = {order_id:$("#search_rest_order").val()}
	}else {
		condition = ""
	}
	getRestaurants(renderRestaurants, condition);
	
}

function searchPayment(){
	var condition;
	if($("#search_payment_id").val()){
	  condition = {payment_id:$("#search_payment_id").val()}
	}else if($("#search_payment_user").val()){
	  condition = {user_id:$("#search_payment_user").val()}
	}else {
		condition = ""
	}
	getPayments(renderPayments, condition);
}

function initOrderDetail(order_id){
	
}

function initUserDetail(user_id){
	var payload, callback, target;
	payload = {user_id:user_id}
	
	callback = function (response){
		if(response.result == "success"){
			User = response.data;
			$("#user_detail_credit").text(validatePrice(User.credit));
					
			$("#user_detail_fname").val(User.fname);
			$("#user_detail_lname").val(User.lname);
			$("#user_detail_email").val(User.email);
			$("#user_detail_phone").val(User.phone);
			
			$("#user_address_div").empty();
			for(x=0;x<User.addresses.length;++x){
				addAddress(User.addresses[x], $("#user_address_div"));
			}
			
			
			$('#user_restaurant_div').empty();
			
			for(x=0;x<User.restaurants.length;++x){
				
				var rest = User.restaurants[x];
				var button, row, id, title, phone, email, status;
				
				id = $("<div>", {class:"col-xs-1 no-padding"}).append(rest.rest_id +".");
				title = $("<div>", {class:"col-xs-3 no-padding"}).append(rest.title);
				phone = $("<div>", {class:"col-xs-3 no-padding"}).append(rest.phone);
				email= $("<div>", {class:"col-xs-3 no-padding"}).append(rest.email);
				status = $("<div>", {class:"col-xs-2 no-padding"}).append(rest.status)
				
						
				row = $("<div>", {class:"row no-padding"}).append(id).append(title).append(phone).append(email).append(status);
				button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
				
				$(button).on("click", {rest_id:rest.rest_id}, function(e){
					$("#user_modal").modal("hide");
					initRestDetail(e.data.rest_id);	
				});	 	
		
				$('#user_restaurant_div').append(button);
				
			}
			
			$('#user_sales_div').empty();
			var sales = document.createDocumentFragment();			
			for(x=0; x<User.sales_rel.length; ++x){
				id = $("<div>", {class:"col-xs-2 no-padding"}).append(User.sales_rel[x].id +".");
				name = $("<div>", {class:"col-xs-4 no-padding"}).append(User.sales_rel[x].name);
				expires = $("<div>", {class:"col-xs-3 no-padding"}).append(User.sales_rel[x].term);
				
				switch(User.sales_rel[x].role){
					case "rep":
						role = $("<div>", {class:"col-xs-4 no-padding"}).append("SALES: REP");
						break;
					case "user":
						role = $("<div>", {class:"col-xs-4 no-padding"}).append("SALES: USER");
						break;
					case "rest":
						role = $("<div>", {class:"col-xs-4 no-padding"}).append("SALES: REST");
						break;
					default:
						role = $("<div>", {class:"col-xs-4 no-padding"}).append("SALES: ???");
						break;
				}
				
											
				row = $("<div>", {class:"row no-padding"}).append(id).append(name).append(expires).append(role)
				button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
				if(User.sales_rel[x].role == "rest"){
									
					$(button).on("click", {rest_id:User.sales_rel[x].id}, function(e){
						$("#user_modal").modal("hide");
						initRestDetail(e.data.rest_id);	
					});	 
					
				}else{
					$(button).on("click", {user_id:User.sales_rel[x].id}, function(e){
						$("#user_modal").modal("hide");
						initUserDetail(e.data.user_id);	
					});	 
					
				}
				
				$(sales).append(button);
			}
			$('#user_sales_div').append(sales);
			
			var orders = document.createDocumentFragment();
			$('#user_orders_div').empty();
			for(x=0;x<User.orders.length; ++x){
				var order = User.orders[x];
				var button, row, id, date, restaurant, user, total;
				
				id = $("<div>", {class:"col-xs-1 no-padding"}).append(order.order_id +".");
				date= $("<div>", {class:"col-xs-3 no-padding"}).append(order.requestDate + " " +order.requestTime);
				restaurant= $("<div>", {class:"col-xs-3 no-padding"}).append(order.rest_title);
				user= $("<div>", {class:"col-xs-3 no-padding"}).append(order.user_name);
				total= $("<div>", {class:"col-xs-2 no-padding"}).append(validatePrice(order.total));
				
				row = $("<div>", {class:"row no-padding"}).append(id).append(date).append(restaurant).append(user).append(total);
				button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
				
				$(button).on("click", {order_id:order.order_id}, function(e){
					initOrderDetail(e.data.order_id);	
				});
			
			}
			$('#user_orders_div').append(orders);
			
			$("#user_modal").modal("show");
		}
	}
	target = "https://www.tastes-good.com/api_scripts/admin/get_user_detail.php";
	
	ajaxRequest(target, payload, callback);
	
}

function initRestDetail(rest_id){
	var payload, callback, target;
	payload = {rest_id:rest_id}
	
	callback = function (response){
		if(response.result == "success"){
			Rest = response.data;
								
			$("#user_detail_fname").val(User.fname);
			$("#user_detail_lname").val(User.lname);
			$("#user_detail_email").val(User.email);
			$("#user_detail_phone").val(User.phone);
			
			$("#user_address_div").empty();
			for(x=0;x<User.addresses.length;++x){
				addAddress(User.addresses[x], $("#user_address_div"));
			}
			
			
			$('#user_restaurant_div').empty();
			
			for(x=0;x<User.restaurants.length;++x){
				
				var rest = User.restaurants[x];
				var button, row, id, title, phone, email, status;
				
				id = $("<div>", {class:"col-xs-1 no-padding"}).append(rest.rest_id +".");
				title = $("<div>", {class:"col-xs-3 no-padding"}).append(rest.title);
				phone = $("<div>", {class:"col-xs-3 no-padding"}).append(rest.phone);
				email= $("<div>", {class:"col-xs-3 no-padding"}).append(rest.email);
				status = $("<div>", {class:"col-xs-2 no-padding"}).append(rest.status)
				
						
				row = $("<div>", {class:"row no-padding"}).append(id).append(title).append(phone).append(email).append(status);
				button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
				
				$(button).on("click", {rest_id:rest.rest_id}, function(e){
					$("#user_modal").modal("hide");
					initRestDetail(e.data.rest_id);	
				});	 	
		
				$('#user_restaurant_div').append(button);
				
			}
			
			$('#user_sales_div').empty();
			var sales = document.createDocumentFragment();			
			for(x=0; x<User.sales_rel.length; ++x){
				id = $("<div>", {class:"col-xs-2 no-padding"}).append(User.sales_rel[x].id +".");
				name = $("<div>", {class:"col-xs-4 no-padding"}).append(User.sales_rel[x].name);
				expires = $("<div>", {class:"col-xs-3 no-padding"}).append(User.sales_rel[x].term);
				
				switch(User.sales_rel[x].role){
					case "rep":
						role = $("<div>", {class:"col-xs-4 no-padding"}).append("SALES: REP");
						break;
					case "user":
						role = $("<div>", {class:"col-xs-4 no-padding"}).append("SALES: USER");
						break;
					case "rest":
						role = $("<div>", {class:"col-xs-4 no-padding"}).append("SALES: REST");
						break;
					default:
						role = $("<div>", {class:"col-xs-4 no-padding"}).append("SALES: ???");
						break;
				}
				
											
				row = $("<div>", {class:"row no-padding"}).append(id).append(name).append(expires).append(role)
				button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
				if(User.sales_rel[x].role == "rest"){
									
					$(button).on("click", {rest_id:User.sales_rel[x].id}, function(e){
						$("#user_modal").modal("hide");
						initRestDetail(e.data.rest_id);	
					});	 
					
				}else{
					$(button).on("click", {user_id:User.sales_rel[x].id}, function(e){
						$("#user_modal").modal("hide");
						initUserDetail(e.data.user_id);	
					});	 
					
				}
				
				$(sales).append(button);
			}
			$('#user_sales_div').append(sales);
			
			var orders = document.createDocumentFragment();
			$('#user_orders_div').empty();
			for(x=0;x<User.orders.length; ++x){
				var order = User.orders[x];
				var button, row, id, date, restaurant, user, total;
				
				id = $("<div>", {class:"col-xs-1 no-padding"}).append(order.order_id +".");
				date= $("<div>", {class:"col-xs-3 no-padding"}).append(order.requestDate + " " +order.requestTime);
				restaurant= $("<div>", {class:"col-xs-3 no-padding"}).append(order.rest_title);
				user= $("<div>", {class:"col-xs-3 no-padding"}).append(order.user_name);
				total= $("<div>", {class:"col-xs-2 no-padding"}).append(validatePrice(order.total));
				
				row = $("<div>", {class:"row no-padding"}).append(id).append(date).append(restaurant).append(user).append(total);
				button = $("<button>", {class: "btn btn-default btn-block"}).append(row);
				
				$(button).on("click", {order_id:order.order_id}, function(e){
					initOrderDetail(e.data.order_id);	
				});
			
			}
			$('#user_orders_div').append(orders);
			
			$("#user_modal").modal("show");
		}
	}
	target = "https://www.tastes-good.com/api_scripts/admin/get_rest_detail.php";
	
	ajaxRequest(target, payload, callback);
}

function initPaymentDetail(payment_id){
	
}
