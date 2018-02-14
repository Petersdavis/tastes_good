//functions:

$(document).ready(function () {
		App = {};
		
		
});

/* Unit Integration Testing:

1. Building Restaurant Methods:
  a) send invitation
  b) approve prospect
  c) submit information
  d) upload menu
  e) download menu
  f) open restaurant
  g) approve restaurant opening
  
2. Create User
	a) submit user details
	b) user logout
	c) user login

3. Place Order
	a)


  
*/
//global variable declarations




//1 a) invitation

function firstInvite(){
	
	var payload = {title: "Test", address: ", Canada", phone: "111-111-1111", email: "tastesgoodtest1@gmail.com", community: undefined};
	var target = "https://www.tastes-good.com/uploader/sales_new_prospect.php";
	var callback = function(response){
	App.rest_id = response.data.rest_id;
	App.rest_token = response.data.token;
	
	console.log("tastesgoodtest1@gmail.com PROSPECT CREATED");
	approveInvite(response.data);
		
};

ajaxRequest(target, payload, callback);
}

//OTHER CASES REJECT PROSPECT;
function approveInvite(data){
	var payload = undefined;
	var target = "https://www.tastes-good.com/uploader/admin_approve_prospect.php?rest_id="+data.rest_id+"&auth="+data.token;
	var callback = function(response){
		console.log("tastesgoodtest1@gmail.com PROSPECT APPROVED");
		submitNewRestUser(response.data);
			
	};
	
	ajaxRequest(target, payload, callback);
		
}
 
//OTHER CASES OLD USER

function submitNewRestUser(data){
	var payload = {"user_id":0,"fname":"Peter","lname":"Davis","email":"tastesgoodtest1@gmail.com","phone":"111-111-1111","password":"one2III","verify":"","addresses":[],"order_count":null,"order_total":null,"credit":null,"fb_id":0,"goog_id":0,"push_id":[],"is_sales":0,"is_rest":0,"is_admin":0,"restaurants":[{"rest_id":data.rest_id,"token":data.token, "image":"/images/avatars/default.gif","sm_image":"/images/avatars/default.gif","title":"Test Restaurant","address":"44 Columbia, Ave. Waterloo ON","menu":null,"orders":[],"lat":"56.1303660000","lng":"-99.9999999999","community":null,"points":0,"status":"PROSPECT","phone":"111-111-1111","email":"tastesgoodtest1@gmail.com","schedule":{"monday_open":"11 : 00","tuesday_open":"11 : 00","wednesday_open":"11 : 00","thursday_open":"11 : 00","friday_open":"11 : 00","saturday_open":"11 : 00","sunday_open":"11 : 00","monday_close":"23 : 00","tuesday_close":"23 : 00","wednesday_close":"23 : 00","thursday_close":"23 : 00","friday_close":"23 : 00","saturday_close":"23 : 00","sunday_close":"23 : 00"},"type":"American","coupons":[],"offers_delivery":1,"delivery_rate":"0.25","delivery_base":"5.00","delivery_email":"","owner":null,"balance":null,"closed":0,"open":1,"pos_review":0,"neg_review":0,"owner_id":0,"timezone":"America/Toronto","open_time":"11:00 AM","close_time":"11:00 PM"}]}
	var target = "https://www.tastes-good.com/api_scripts/new_restaurant_save.php?rest_id="+data.rest_id
	var callback = function(data){
		saveMenu(data);
			
	};
	
	ajaxRequest(target, payload, callback);
}

function createMenu(data){
	
}

function createCoupon(data){
	
}

function fetchHistory(data){
	
}



//Clean UP:

//Login as ADMIN

//Delete Restaurant

//Delete Users

//Delete Orders
	


	

