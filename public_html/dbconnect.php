<?php



###Database Connections
###DB Parameters Loaded from config file
global $conn;

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8")) {
	 printf("Error loading character set utf8: %s\n", $conn->error);
	exit();
}


### PHP Functions

class Secure{
	### Class used to authorize all API interaction
	### methods isUser() isSales() isRest() isAdmin() return true or die.
	public $is_sales;
	public $is_rest;
	public $is_admin;
	public $user_id;
	
	function isUser(){
		if($this->user_id > 0){
			return true;
		}else{
			$x = new stdClass();
			$x->result = "failure";
			$x->error = "BAD_USER";
			exit(json_encode($x));
		}
	}
	
	function isRest(){
		if($this->is_sales){
			return true;
		}else{
			$x = new stdClass();
			$x->result = "failure";
			$x->error = "NOT_REST";
			exit(json_encode($x));
		}
	}
	
	function isSales(){
		if($this->is_rest){
			return true;
		}else{
			$x = new stdClass();
			$x->result = "failure";
			$x->error = "NOT_SALES";
			exit(json_encode($x));
		}
	}
	
	function isAdmin(){
		if($this->is_admin){
			return true;
		}else{
			$x = new stdClass();
			$x->result = "failure";
			$x->error = "NOT_ADMIN";
			exit(json_encode($x));
		}
	}
	
	function __construct () {
		global $conn;
		
		if(isset($_SESSION['user_id'])){
			$this->user_id = $_SESSION['user_id'];
			
			$sql = "SELECT is_sales, is_rest, is_admin FROM users WHERE user_id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $this->user_id);
			$stmt->execute();
			$stmt->bind_result($this->is_sales, $this->is_rest, $this->is_admin);
			$stmt->fetch();
			$stmt->close();
			
		}else{
			$this->user_id = 0;
			$this->is_rest = 0;
			$this->is_admin = 0;
			$this->is_sales = 0;
						
		}
		
	 
	}


}


Class Schedule {
	### Helper Class for Initializing a New Schedule
	public $monday_open = "11:00";
	public $tuesday_open = "11:00";
	public $wednesday_open = "11:00";
	public $thursday_open = "11:00";
	public $friday_open = "11:00";
	public $saturday_open = "11:00";
	public $sunday_open = "11:00";
	public $monday_close = "23:00";
	public $tuesday_close = "23:00";
	public $wednesday_close = "23:00";
	public $thursday_close = "23:00";
	public $friday_close = "23:00";
	public $saturday_close = "23:00";
	public $sunday_close = "23:00";
}

Class Address {
	###Contains Methods to Store and Retrieve Addresses
	###Also contains Google API for finding GeoCoords of Address
	public $id = -1;
	public $user_id = "";
	public $street = "";
	public $address = "";
	public $city = "";
	public $appt = "";
	public $buzz = "";
	public $province = "";
	public $postcode = "";
	public $comment = "";
	public $lat =0;
	public $lng =0; 
	
	function fromId($addressId){
		global $conn;
		$sql = "SELECT user_id, address, postcode, appt, buzz, lat, lng, type, comment FROM user_address WHERE id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $addressId);
		if(!$stmt->execute()){
			$result = new stdClass();
			$result->result = "fail";
			$result->error = "Connection: ". $conn->error . " Statement: ". $stmt->error; 
			$stmt->close();
			echo json_encode($result);
			exit();
		}
		$stmt->bind_result($user_id, $address, $postcode, $appt, $buzz, $lat, $lng, $type, $comment);
		while($stmt->fetch()){
			$this->id = $addressId;
			$this->user_id = $user_id;
			$this->address = $address;
			$this->postcode = $postcode;
			$this->comment = $comment;
			$this->appt = $appt;
			$this->buzz = $buzz;
			$this->lat =$lat;
			$this->lng = $lng;
			$this->type =$type;
					
		}
		$stmt->close(); 
	}
		
	function getLatLng (){
		$googleQuery = $this->street . ', ' . $this->city . ', '. $this->province. ', '.$this->postcode;
		$googleQuery = str_replace (" ", "+", $googleQuery);
		$googleQuery = utf8_encode( $googleQuery );
				
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='. rawurlencode($googleQuery) .'&key=AIzaSyCmMnZ4ZQrCCXcwUSYXOkqmU9tMjK5lxxs&sensor=false';
				
		$cURL = curl_init();
		
		if(!curl_setopt($cURL, CURLOPT_URL, $url)){echo 'error curl_setopt:CURLOPT_URL';} 
		if(!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1)){echo 'error curl_setopt:CURLOPT_RETURNTRANSFER';}
		if(!curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false)){echo 'error curl_setopt:CURLOPT_SSL_VERIFYPEER';}
		$result = json_decode(curl_exec($cURL), true);
		
		curl_close($cURL);
		
		if ($result['status']=="OK"){
		
		$this->lat = $result['results'][0]['geometry']['location']['lat'];
		$this->lng = $result['results'][0]['geometry']['location']['lng'];
		
		$x = new stdClass();
		$x->result = "success";
		$x->error = "";
		return $x;
		
		}else{
		$x->result = "fail";
		$x->error = "geocode_fail";
		$x->data = $result; 
		return $x;	
		}
				
		
	}
	
	function putToDB (){
		global $conn;
		$sql = "INSERT INTO user_address (user_id, street, city, appt, buzz, province, postcode, lat, lng, comment) VALUES (?,?,?,?,?,?,?,?,?,?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("isssssssss", $this->user_id, $this->street, $this->city, $this->appt, $this->buzz, $this->province, $this->postcode, $this->lat, $this->lng, $this->comment);
		$stmt->execute();
		$addr_id =  $stmt->insert_id;
		$stmt->close(); 
		
		$sql = "UPDATE users SET active_address = ? WHERE user_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("ii", $addr_id, $this->user_id);
	}

}
	
Class User {
	###Contains Login Handling.  Security Authentication for Token Login
	###Firebase Cloud Messenging API
	###Can Edit User or Adjust Credit
	
	public $user_id = 0;
	public $fname = "";
	public $lname = "";
	public $email = "";
	public $phone = "";
	public $password = "";
	public $verify = "";
	public $addresses = [];
	public $order_count;
	public $order_total;
	public $credit;
	public $fb_id = 0;
	public $goog_id = 0;
	public $push_id = [];
	public $is_sales = 0;
	public $is_rest = 0;
	public $is_admin = 0;
	public $restaurants = [];
	
	
	function createToken(){
		global $conn;
		$x = new stdClass();
		$x->token = $this->user_id. "!". substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 32);
		$x->expires = time() + 31*24*60*60;
		$token = serialize($x);
		
		$sql = "UPDATE users SET login_token = ? WHERE user_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("si", $token, $this->user_id);
		$stmt->execute();
		$stmt->close();
			
		return($x->token);
	}
	
	function verifyToken($token){
		global $conn;
		$this->user_id = explode("!", $token)[0];
		
		
		$sql = "SELECT login_token FROM users WHERE user_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("i", $this->user_id);
		$stmt->execute();
		$stmt->bind_result($serial);
		$stmt->fetch();
		$stmt->close();
		if($serial){
		$good_token = unserialize($serial);
		
		if(gettype($good_token)=="object" && $good_token->expires > time()){
			if($token == $good_token->token){
				$_SESSION['user_id']= $this->user_id;
				return true;
			}		
		}else{
			$x= new stdClass();
			$x->result = "failure";
			$x->error = "expired_token";
			return $x;
		}}else{
			$x= new stdClass();
			$x->result = "failure";
			$x->error = "no_token";
			return $x;
				}
	}
	
	function getPushId(){
		global $conn;
		$sql = "SELECT push_id FROM users WHERE user_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("i", $this->user_id);
		$stmt->execute();
		$stmt->bind_result($serial);
		if($serial !== 0){
			$this->push_id = unserialize($serial); 
		}else{
			$this->push_id = [];
		}
		
		
		
	}
	
	
	function sendPush($msg){
		$results = [];
		foreach($this->pushId as $target){
			$fields = array	(
				'registration_ids'=> $target,
				'data'	=> $msg);
			 
			$headers = array(
				'Authorization: key=' . $GCM_Key,
				'Content-Type: application/json');
			 
			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			
			$result = curl_exec($ch );
			
			array_push($results, $result);
			curl_close( $ch );
		}
		return $result;
	}

	function storePushId($id){
		global $conn;
		
		$this->getPushId();
		
		foreach($this->pushId as $pushId){
			if($pushId == $id){
				$x = new stdClass();
				$x->result = "failure";
				$x->error = "ID_EXISTS";
				return $x;
			}
		}
		
		array_push($this->pushId, $id);
		$serial = serialize($this->pushId);
		
		$sql = "UPDATE users SET push_id = ? WHERE user_id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("si", $serial, $this->user_id);
		$stmt->execute();
		$stmt->close();
		
		$x = new stdClass();
		$x->result = "success";
		return $x;
		
			
	
	}

	function newFB ($user){
		$this->fb_id = $user->fb_id;
		$this->fromJSON($user);
			
	}
	
	function fromFB($fb_id, $email){
		global $conn; 
		//check if user exists with facebook id?
		$sql = "SELECT email FROM users WHERE fb_id= ?";
		if(!$stmt = $conn->prepare($sql)){$x=new stdClass(); $x->result="error";$x->error=$conn->error; exit(json_encode($x));}
		$stmt->bind_param("s", $fb_id);
		if(!$stmt->execute()){$x=new stdClass(); $x->result="error"; $x->error=$stmt->error; exit(json_encode($x));}
		$stmt->bind_result($email);
		if($stmt->fetch()){
			//fb_id exists get user
			
			$stmt->close();
			$this->fromSession($email);
		} else {
			$stmt->close();
			
			$sql = "SELECT user_id FROM users WHERE email= ?";
			if(!$stmt = $conn->prepare($sql)){$x=new stdClass(); $x->result="error"; $x->error=$conn->error; exit(json_encode($x));}
			$stmt->bind_param("s", $email);
			if(!$stmt->execute()){$x=new stdClass(); $x->result="error"; $x->error=$stmt->error; exit(json_encode($x));}
			$stmt->bind_result($user_id);
		 	if($stmt->fetch()){
		 	 	//user exists but first time loging with facebook
		 	 	$stmt->close();
		 	 	
		 	 	$sql = "UPDATE users SET fb_id = ? WHERE user_id = ?";
		 	 	if(!$stmt = $conn->prepare($sql)){$x=new stdClass(); $x->result="error"; $x->error=$conn->error; exit(json_encode($x));}
				$stmt->bind_param("s", $fb_id);
				if(!$stmt->execute()){$x=new stdClass(); $x->result="error"; $x->error=$stmt->error; exit(json_encode($x));}
				$stmt->close();
				
				$this->fromSession($email);
		 	 	
		 			 	
		 	} else {
		 	$stmt->close();
		 	}
			
				
	
		}
	}
	
	function getAddresses(){
	global $conn; 

	$sql = "SELECT id, address, postcode, appt, buzz, lat, lng, type, comment FROM user_address WHERE user_id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $this->user_id);
	if(!$stmt->execute()){
		$result = new stdClass();
		$result->result = "fail";
		$result->error = "Connection: ". $conn->error . " Statement: ". $stmt->error; 
		$stmt->close();
		echo json_encode($result);
		exit();
	}
	$stmt->bind_result($id, $address, $postcode, $appt, $buzz, $lat, $lng, $type, $comment);
	
	
	$addresses = [];
	while($stmt->fetch()){
		$addr = new Address();
		$addr->id = $id;
		$addr->user_id = $this->user_id;
		$addr->address = $address;
		$addr->postcode = $postcode;
		$addr->comment = $comment;
		$addr->appt = $appt;
		$addr->buzz = $buzz;
		$addr->lat =$lat;
		$addr->lng = $lng;
		$addr->type =$type;
		array_push($this->addresses, $addr);
	}
			
	
	}

			
	
	
	
	function fromLogin ($cred){
		global $conn;
				
		$password = $cred->password;
		$email = $cred->email;
		
		$sql = "Select password, user_id FROM users WHERE email = ?";              
		$stmt = $conn->prepare($sql);                                                                
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->bind_result($password_hash, $this->user_id);
		if($stmt->fetch()){
		$stmt->close();
		if(password_verify($password, $password_hash)){
		
			$this->auth_token = $this->createToken();	
			$this->fromSession();
			
			$_SESSION['user_id']= $this->user_id;
			$_SESSION['user_name']=$this->fname . " " . $this->lname;
			$_SESSION['user_email']=$this->email; 
			$_SESSION['user_phone']=$this->phone; 
						
			$x = new stdClass();
			$x->result = "success";
			$x->error = "";
				
			return $x;
								
			
			
		}else{
						
			$x = new stdClass();
			$x->result = "failure";
			$x->error = "pwd_wrong";
			return $x;
			
			//passwords don't match.  
			
		}
				
			
		}else {
			$stmt->close();
			
			$x = new stdClass();
			$x->result = "failure";
			$x->error = "pwd_wrong";
			return $x;
			//email does not exist	
		}
		
		
	}
	
	function fromId($id){
		global $conn;
					
		$sql = "SELECT email FROM users WHERE user_id= ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->bind_result($this->email);
		$stmt->fetch();
		$stmt->close();
		
		$this->fromSession($this->email);
		
	}
	
	function fromSession(){
		global $conn;
		
		if(!$this->user_id){
			$this->user_id =  $_SESSION['user_id'];
		}
					
		$sql = "SELECT email, fname, lname, phone, fb_id, google_id, credit, is_sales, is_admin, is_rest, login_token FROM users WHERE user_id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $this->user_id);
		$stmt->execute();
		$stmt->bind_result($this->email, $this->fname, $this->lname, $phone, $this->fb_id, $this->google_id, $this->credit, $this->is_sales, $this->is_admin, $this->is_rest, $auth_token);
		$stmt->fetch();
		$stmt->close();
		
		if($auth_token){
		$auth_token = unserialize($auth_token);
		$this->auth_token = $auth_token->token;
		}
		$this->phone = PrettyPhone($phone);
		
		$this->getAddresses();
		
		if($this->is_sales){
						
			$sql="SELECT COUNT(rest_id), COUNT(user_id), COUNT(is_prospect) FROM sales_junction WHERE sales_id = ? AND commission_term > ?";
			$stmt=$conn->prepare($sql);
			$time = time();
			$stmt->bind_param("ii", $user_id, $time);
			$stmt->execute();
			$stmt->bind_result($restaurants, $customer, $prospect);
			$stmt->fetch();
			$stmt->close();
			
			$this->sales = new stdClass();
			$this->sales->active = $restaurants - $prospect;
			$this->sales->prospect = $prospect;
			$this->sales->customer = $customer;
			
					
		}
		
		if($this->is_rest){
			$sql = "SELECT rest_id FROM restaurants WHERE owner_id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $this->user_id);
			$stmt->execute();
			$stmt->bind_result($rest_id);
			$rests = [];
			while($stmt->fetch()){
				$x = new Restaurant();
				$x->rest_id = $rest_id;
				array_push($rests, $x);
			}
			$stmt->close();
			
			foreach($rests as $rest){
				$rest->grabRest($rest->rest_id);		
			}
			
			$this->restaurants = $rests;
		}
		
					
	}
	
	function checkPwd($user){
	        $password = $user->mergePwd;
		$email = $user->email;
		
		$sql = "Select password FROM users WHERE email = ?";              
		$stmt = $conn->prepare($sql);                                                                
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->bind_result($password_hash);
		if($stmt->fetch()){
		$stmt->close();
		if(password_verify($password, $password_hash)){
			return;
		 }else{
		 	$x=new stdClass();
			$x->result = "failure";
			$x->error = "pwd_wrong";
			exit(json_encode($x));
		 }
				
		} else {
			//email does not exist
			$x=new stdClass();
			$x->result = "failure";
			$x->error = "did not find merge email";
			exit(json_encode($x));
		
		}
	
	}
	
	function fromJSON ($user){
		                
		$this->fname = $user->userDetails->fname;
		$this->lname = $user->userDetails->lname;
		$this->email = $user->userDetails->email;
		$this->phone = $user->userDetails->phone;
				
		$pattern = '/[\D]/';
		$this->phone = preg_replace ( $pattern , "" , $this->phone );
		$this->password = password_hash($user->userDetails->password, PASSWORD_BCRYPT); 
		
		$result = $this->putNewToDB();
		$this->phone = PrettyPhone($this->phone);
		
		if($result->result=="success"){
			$_SESSION['user_id']= $this->user_id;
			$_SESSION['user_name']=$this->fname . " " . $this->lname;
			$_SESSION['user_email']=$this->email; 
			$_SESSION['user_phone']=$this->phone; 
			$_SESSION['verify']=$this->verify; 
		}
		
		return $result;	
				
	
	}
	
	function putNewToDB(){
	global $conn;
	//first test if user is a new user
	$sql = "SELECT user_id FROM users WHERE email = ?";
	
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $this->email);
	$stmt->execute();
	$stmt->store_result();
	
	if($stmt->num_rows==0){
		$stmt->free_result();
		$stmt->close();	
		
		$sql = "INSERT INTO users (fname, lname, email, password, phone, is_sales, is_rest, is_admin, fb_id, google_id) VALUES (?,?,?,?,?,?,?,?,?,?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ssssiiiiss", $this->fname, $this->lname, $this->email, $this->password, $this->phone, $this->is_sales, $this->is_rest, $this->is_admin, $this->fb_id, $this->goog_id);
		$stmt->execute();
		$this->user_id = $stmt->insert_id;
		$stmt->close();
		
		$x = new stdClass();
		$x->result = "success";
		$x->data = $this;
		$x->error = "";
		return $x;
		
	 } else {
	 	$stmt->free_result();
		$stmt->close();	
		
		
		if($this->fb_id!= 0 && isset($this->merge_ok)){
			//user must include password to merge existing accounts.
			$sql = "UPDATE users SET fb_id = ? WHERE email = ?";
			$stmt = $conn->prepare($sql);                                                                
			$stmt->bind_param("s",  $this->email);
			$stmt->execute();
			
			$x = new stdClass();
			$x->result = "success";
			$x->data = $this;
			$x->error = "";
			return $x;
			
			
			} else {
				$x = new stdClass();
				$x->result = "error";
				$x->data = $this;
				$x->error = "duplicate_email";
				exit(json_encode($x));
			
					
		 	}
		 	 
		 }
	}
	
	function deltaCredit($amount){
	global $conn;
	$this->credit = $this->credit + $amount;
	
	$sql = "UPDATE users SET credit = ? WHERE user_id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("si", $this->credit, $this->user_id);
		$stmt->execute();
		$stmt->close();
	
	}
}

	
	
	



Class Order {
	###Storing//Retriving//Deleting Orders
	###Pays commission to sales Agents
     public $order_id;
	 public $rest_id;
	 public $user_id;
	 public $addr_id;
	 public $deliveryOption;
	 public $requestDate;
	 public $requestTime;
	 public $subtotal = 0; 
	 public $distance;
	 public $deliveryCharge = 0;
	 public $tax = 0;
	 public $tip = 0;
	 public $total = 0;
	 public $timestamp;
	 public $paymentType;
	 public $coupon;
	 public $discount;
	 public $tg_points;
	 public $commission;
	 public $rest_balance;
	 public $rest_delta;
	 public $amount_paid;
	 public $payment_fee;
	 public $link;
         public $confirmed;
         public $comments;
         public $env;
         public $transact_id;
	 public $owner_id;
	 function remove(){
	 	global $conn;
	 	$sql = "DELETE FROM restaurant_orders WHERE order_id = ?";
	 	if(!$stmt = $conn->prepare($sql)){
		 	 echo "error". $conn->error ;
		 }
	 	$stmt->bind_param("i", $this->order_id);
	 	$stmt->execute();
	 }
	 function getFromDb($order_id){
	  	global $conn;
	  	$sql = "SELECT order_subtotal, distance, tip, tax, coupon, order_delivery, delivery_charge, request_date, request_time, user_id, rest_id, addr_id, order_time, order_total, payment_type, discount, tg_points, amount_paid, commission, payment_fee, order_serial, comment, env, transaction, rest_balance, rest_delta FROM restaurant_orders WHERE order_id = ?";
	  	$stmt=$conn->prepare($sql);
		$stmt->bind_param("i", $order_id);
		$stmt->execute();
		$stmt->bind_result($this->subtotal, $this->distance, $this->tip, $this->tax, $this->coupon, $this->deliveryOption, $this->deliveryCharge, $this->requestDate, $this->requestTime, $this->user_id, $this->rest_id, $this->addr_id, $this->timestamp, $this->total, $this->paymentType, $this->discount, $this->tg_points, $this->amount_paid, $this->commission, $this->payment_fee, $serial, $this->comments, $this->env, $this->transact_id, $this->rest_balance, $this->rest_delta);
		$stmt->fetch();
		$this->items = unserialize($serial);
			 
	 
	 }
	 
	 function storeToDb($serial){
	 	 global $conn;
	 	 $sql = "INSERT INTO restaurant_orders (order_subtotal, distance, tip, tax, coupon, order_delivery, delivery_charge, request_date, request_time, user_id, rest_id, addr_id, order_time, order_total, payment_type, discount, tg_points, amount_paid, commission, payment_fee, order_serial, comment, env, transaction, rest_balance, rest_delta) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	 	 if(!$stmt = $conn->prepare($sql)){
		 	 echo "error". $conn->error ;
		 }
		
		 if($this->coupon){
		 	 $code= $this->coupon->code;
		 }else{
		 	 $code= 0;
		 }
		 
		 if(!$stmt->bind_param("ddddsidssiiisdsdidddssisdd", $this->subtotal, $this->distance, $this->tip, $this->tax, $code, $this->deliveryOption, $this->deliveryCharge, $this->requestDate, $this->requestTime, $this->user_id, $this->rest_id, $this->addr_id, $this->timestamp, $this->total, $this->paymentType, $this->discount, $this->tg_points, $this->amount_paid, $this->commission, $this->payment_fee, $serial, $this->comments, $this->env, $this->transact_id, $this->rest_balance, $this->rest_delta)){
		 	 echo "error". $conn->error . $stmt->error;
		 	}
		 
		 $stmt->execute();
		 $this->order_id =$stmt->insert_id; 	 
		 $stmt->close();
		 
	 }
	 
	 function payCommissions(){
	 	global $conn;
	 	
	 	$sql = "SELECT order_id FROM restaurant_orders WHERE user_id = ?";
	 	$stmt= $conn->prepare($sql);
	 	$stmt->bind_param("i", $this->user_id);
	 	$stmt->execute();
	 	$stmt->store_result();
	 	if($stmt->num_rows == 0){
	 		$stmt->close();
			$expires = time() + (4 * 7 * 24 * 60 * 60);
			$sql = "INSERT INTO sales_junction (sales_id, user_id, commission_term) VALUES (?,?,?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param('iii', $this->owner_id, $this->user_id, $expires);
			$stmt->execute();
			$stmt->close ();

	 	}else{
	 	
	 	$stmt->close();
	 	}
	 	
	 	
	 	//existing contracts
	 	$sql  = "SELECT id, sales_id, commission_rate, commission_term FROM sales_junction WHERE user_id = ? OR rest_id = ?";
	 	$stmt = $conn->prepare($sql);
	 	$stmt->bind_param("ii", $this->user_id, $this->rest_id);
	 	$stmt->execute();
	 	$stmt->bind_result($id, $sales_id, $commission_rate, $commission_term);
	 	$contracts = [];
	 	
	 	
	 	while($stmt->fetch()){
	 		if($commission_term > time()){
	 			$contract = new stdClass();
	 			$contract->id = $id;
	 			$contract->sales_id = $sales_id;
	 			$contract->rate = $commission_rate;	 		
	 		
	 		}
	 	
	 	}
	 	
	 	$stmt->close();
	 	
	 	foreach($contracts as $contract){
	 		$amount  = $this->commission * $contract->rate;
	 		
	 		$a = new User();
	 		$a->user_id = $contract->sales_id;
	 		$a->deltaCredit($amount);
	 		
	 		$sql = "UPDATE sales_junction SET total_commission += ? WHERE id = ?";
	 		$stmt = $conn->prepare($sql);
	 		
	 		$stmt->bind_param("di", $amount, $contract->id);
	 		$stmt->execute();
	 		$stmt->close();
	 	
	 	}
	 	
	 	//check rest_id
	 	
	 
	 }
		
	 		 
}


Class Option { 
	public $choice;
	public $price;
	public $extras = [];
}

Class Extra {
	public $id;
	public $name;
	public $question;
	public $type;
	public $options=[];
}

Class Item {
	public $id;
	public $category;
	public $product;
	public $description;
	public $price;
	public $extras =[];
		
	function byAssignment($id,  $product,  $price, $category = "", $description="", $extras){
		$this->id = $id;
		$this->category = $category;
		$this->product = $product;
		$this->description = $description;
		$this->price = $price;
		$this->extras = $extras;
	
	}
	
	function PrintMe(){
	
		echo '<table><tr><td>'. $this->product . '<td>'. $this->price .'</tr></table>';
		echo '<table><tr><td>'. $this->description .'</tr></table>';
		
	}
	

}

Class Category {
	public $id;
	public $category;
	public $items = [];
	public $extras = [];
	//public $extras;
	
	function grabItems($rest_id){
		global $conn;
		$this->items = [];
		$this->extras =[];
		$itm_extras = [];
		$stmt = $conn->prepare('SELECT * From restaurant_item WHERE rest_id= ? AND category= ?');
		$stmt->bind_param("is", $rest_id, $this->category);
		$stmt->execute();
		$stmt->bind_result($item_id, $abc, $category, $product, $description, $price);
		while ($stmt->fetch()) {
			$item = new item; 
			$item->byAssignment($item_id, $product, $price, $category, $description, $itm_extras);
			array_push($this->items, $item);
		}
		$stmt->close();
			
				
	}
			
		

}	

Class Menu {
	public $categories = [];
	public $extras = [];
	
	//vars to keep track of Id Auto-Increment
	public $lastCategory = 0;
	public $lastItem = 0;
	public $lastExtra = 0;
	//public $extras;
	
	
	function fromJSON ($json_categories, $json_extras, $rest_id){
		global $conn;
		$this->categories = [];
		$this->extras = [];
		
		$extras = json_decode($json_extras);
		$categories =json_decode($json_categories);
			
			foreach($categories as $category){
			$b = new category();
			$b->id = $category->id;
			$b->category = $category->category;
			$b->extras = $category->extras;
			foreach($category->items as $item){
				//validate option price
				$pattern = '/[\d]+[.]{0,1}[\d]{0,2}/';
				preg_match($pattern, $item->price, $matches);
												
				$c = new item;
				$c->byAssignment($item->id, $item->product, $matches[0], $b->category, $item->description, $item->extras);
				array_push($b->items, $c);
			}
			$b->itemTotal = sizeof($b->items);	
			array_push($this->categories, $b);
		}
		
		foreach($extras as $extra){
			$b= new extra();
			$b->id = $extra->id;
			$b->name = $extra->name;
			$b->question =$extra->question;
			$b->type = $extra->type;
			
			foreach($extra->options as $option){
				$c=new option;
				$c->name = $option->name;
				//validate option price
				$pattern = '/[\d]+[.]{0,1}[\d]{0,2}/';
				preg_match($pattern, $option->price, $matches);
				$c->price = $matches[0];
				$c->extras = $option->extras;
				array_push($b->options, $c);
			}
			array_push($this->extras, $b);
		}
	}
	
	function storeToDB ($rest_id){
		global $conn;
		
		foreach($this->categories as $category){
			foreach($category->items as $item){
				
			
				
				if ($category->id == "delete"){
					$item->id == "delete";
				}
					
				if($item->id == "delete"){
					$sql = 'DELETE FROM restaurant_item WHERE item_id= ?';
					$stmt=$conn->prepare($sql);
					$thisid = str_replace("delete", "", $item->id);	
					$stmt->bind_param('i', $thisid);
					$stmtDelete->execute();
					$stmt->close;
					
				}elseif ($item->id == "new"){
					
					$sql = "INSERT into restaurant_item (rest_id, category, product, description, price) VALUES (?,?,?,?,?)";
					$stmt=$conn->prepare($sql);	
					$stmtNew->bind_param('isssd', $rest_id, $item->category, $item->product, $item->description, $item->price);
					$stmtNew->execute();
					$stmt->close;
				
				}else{
									
					$sql = "UPDATE restaurant_item SET product = ?, description=?, price=? WHERE item_id=?";
					$stmt=$conn->prepare($sql);				
					$stmt->bind_param('sssi', $item->product, $item->description, $item->price, $item->id);
					$stmt->execute();
					$stmt->close();
					
					}	
			
			
			}
			
			
			if($category->id == "delete"){
			
				$sql = 'DELETE FROM restaurant_category WHERE category_id= ?';
				$stmt=$conn->prepare($sql);			
				$thisid = str_replace("delete", "", $category->id);	
				$stmt->bind_param('i', $thisid);
				$stmt->execute();
				$stmt->close();
				
			
			}elseif ($category->id == "new"){
				$sql = "INSERT into restaurant_category (rest_id, category) VALUES (?,?)";
				$stmt=$conn->prepare($sql);			
				$stmt->bind_param('is', $rest_id, $category->category);
				$stmt->execute();
				$stmt->close();
			
			}else{
				
				$sql = "UPDATE restaurant_category SET category = ?, WHERE category_id =?";
				$stmt=$conn->prepare($sql);			
				$stmt->bind_param('si', $category->category, $category->hierarchy, $category->id);
				$stmt->execute();
				$stmt->close();
			}
		}
		
		
		$this->GrabMenu($rest_id);
		$this->putSerial($rest_id);
	}
		
			
	
	
	function grabMenu($rest_id) {
		global $conn;
		$this->categories = [];
		$this->extras = [];
		### GET CATEGORIES
		
		$sql = 'SELECT category_id, category From restaurant_category Where rest_id= ?';
		$stmt = $conn->prepare($sql);
		$stmt->bind_param('i', $rest_id);
		$stmt->execute();
		$stmt->bind_result($id, $cat);
		
		
		while($stmt->fetch()){	
			$category = new category ();
			$category->id = $id;
			$category->category = $cat;
			array_push($this->categories, $category);
			
		}
		$stmt->close();
		
		foreach($this->categories as $category){
			$category->grabItems($rest_id);
		}
		
	} 
	
	function PrintMe(){
		foreach ($this->categories as $category){
			$category -> PrintMe();
			##Edit Category
			##Delete Empty Category
			##New Category
			##Promote Demote
			
			foreach ($category->items as $item){
				$item->PrintMe();
			##EditItem
			##Delete Item
			##New Category 
			##Promote/Demote
			##Attach Existing Extra
			
			
			
			}		
			
	
		}
		### Create New Extra
		
		
	}  ##end print
}

class Community{
	public $name;
	public $lat;
	public $lng;
	public $count = 0;
	public $timezone;
	public $time_offset;
	public $participation;
	public $launch;
	public $log;
	public $delivery_base;
	public $delivery_rate;
	public $status;
	
	function grabComm($community){
		global $conn;
		$sql = "Select lat, lng, time_offset, timezone, count, participation, status, province, launch_date, delivery_base, delivery_rate FROM community WHERE name = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("s", $community);
		$stmt->execute();
		$stmt->bind_result($lat, $lng, $time_offset, $timezone, $count, $participation, $status, $province, $launch, $delivery_base, $delivery_rate);
		$stmt->fetch();
		
		$this->name=$community;
		$this->lat=$lat;
		$this->lng=$lng;
		$this->count = $count;
		$this->timezone =$timezone;
		$this->time_offset = $time_offset;
		$this->participation = $participation;
		$this->launch = $launch;
		$this->delivery_base = $delivery_base;
		$this->delivery_rate = $delivery_rate;
		$this->status = $status;
		
		$stmt->close();
	

		
		
		
	}
	
}

Class Restaurant {
	public $rest_id = 0;
	public $image;
	public $sm_image;
	public $title = "default name";
	public $address = "default address";
	public $menu;
	public $orders = [];
	public $lat = 0;
	public $lng = 0;
	public $community = ""; 
	public $points = 0;
	public $status = "";
	public $phone;
	public $email;
	public $schedule;
	public $type;
	public $coupons = [];
	public $offers_delivery;
	public $delivery_rate;
	public $delivery_base;
	public $delivery_email;
	public $owner;
	public $balance;
	public $closed;
	public $open;
	public $pos_review =0;
	public $neg_review = 0;
	public $owner_id = 0;
	public $timezone = "America/Toronto";
	
	function createToken(){
		global $conn;
		$x = new stdClass();
		$x->token = $this->rest_id . "!". substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 32);
		$x->expires = time() + 30*24*60*60;
		$token = serialize($x);
		
		$sql = "UPDATE restaurants SET rest_token = ? WHERE rest_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("si", $token, $this->rest_id);
		$stmt->execute();
		$stmt->close();
			
		return($x->token);
	}
	
	function verifyToken($token){
		global $conn;
		if(!isset($token)){
			$x= new stdClass();
			$x->result = "failure";
			$x->error = "bad_token";
			exit (json_encode($x));
		}
		
		$this->rest_id = explode("!", $token)[0];
		
		
		$sql = "SELECT rest_token FROM restaurants WHERE rest_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("i", $this->rest_id);
		$stmt->execute();
		$stmt->bind_result($serial);
		$stmt->fetch();
		$stmt->close();
		
		$good_token = unserialize($serial);
		
		
		if($good_token->expires > time()){
			
			if($token == $good_token->token){
				return true;
			}else{
				$x= new stdClass();
				$x->result = "failure";
				$x->error = "bad_token";
				exit (json_encode($x));
			
			}		
		}else{
			$x= new stdClass();
			$x->result = "failure";
			$x->error = "expired_token";
			exit (json_encode($x));
		}
	}
	
	
	
	 function GooglePlace(){
	 	global $conn;
	 	
	 		 	
	 	if ($this->phone||$this->phone===0){
	 		return $this->phone;
	 	}
	 	
		
		$googleLocation =  $this->lat . ",". $this->lng ;
		$googleName = $this->title;
		
		$url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?radius=1000&type=restaurant&location='. rawurlencode($googleLocation) .'&name='.rawurlencode($googleName).'&key=AIzaSyCmMnZ4ZQrCCXcwUSYXOkqmU9tMjK5lxxs&sensor=false';
		
		
		$cURL = curl_init();
		
		if(!curl_setopt($cURL, CURLOPT_URL, $url)){echo 'error curl_setopt:CURLOPT_URL';} 
		if(!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1)){echo 'error curl_setopt:CURLOPT_RETURNTRANSFER';}
		if(!curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false)){echo 'error curl_setopt:CURLOPT_SSL_VERIFYPEER';}
		$result = json_decode(curl_exec($cURL), true);
		
		curl_close($cURL);
		
		if ($result['status']=="OK"){
		$place_id = $result['results'][0]['place_id'];
		$url = 'https://maps.googleapis.com/maps/api/place/details/json?placeid='.rawurlencode($place_id) .'&key=AIzaSyCmMnZ4ZQrCCXcwUSYXOkqmU9tMjK5lxxs&sensor=false';	
			
			
		$cURL = curl_init();	
		
		if(!curl_setopt($cURL, CURLOPT_URL, $url)){echo 'error curl_setopt:CURLOPT_URL';} 
		if(!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1)){echo 'error curl_setopt:CURLOPT_RETURNTRANSFER';}
		if(!curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false)){echo 'error curl_setopt:CURLOPT_SSL_VERIFYPEER';}
		$result = json_decode(curl_exec($cURL), true);
		if ($result['status']=="OK"){
			$phone= $result['result']["international_phone_number"];
			$pattern = '/[\D]/';
			$this->phone = preg_replace ( $pattern , "" , $phone );
			
			$sql="UPDATE restaurants SET phone = ? WHERE rest_id = ?"; 
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("ii", $this->phone, $rest_id);
			$stmt->execute();
						
			$this->phone = PrettyPhone($this->phone);
			return $this->phone;
		}else{
			$this->phone= 0;
			$sql="UPDATE restaurants SET phone = ? WHERE rest_id = ?"; 
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("ii", $phone, $rest_id);
			$stmt->execute();
			return 0;
		}
		
		
		}else{
			$this->phone= 0;
			$sql="UPDATE restaurants SET phone = ? WHERE rest_id = ?"; 
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("ii", $phone, $rest_id);
			$stmt->execute();
			return 0;
		}
		
	}	
		
	function getOrders($rest_id){
		global $conn;		
		$sql = 'SELECT (order_serial) From restaurant_orders Where rest_id = ?';
		$stmt=$conn->prepare($sql);
		
		
		
		$stmt->bind_param('i', $rest_id);
		
		$stmt->execute();
		$stmt->bind_result($col1);
		
		
		while ($stmt->fetch()) {
				
				$a = unserialize(base64_decode($col1));
				array_push($this->orders, $a);
				
			}
			
		$stmt->close();
			
	}
	
	function putSerial($rest_id){
		global $conn;		
		$s = base64_encode(serialize($this->menu));
		$sql = 'UPDATE restaurants SET serialize_menu = "'. $s . '" WHERE rest_id =' . intval($rest_id);
		if($conn->query($sql)){}else{echo $conn->error;};
				
	}
	
	function grabSerial ($rest_id){
		global $conn;
		
		
		$sql = 'SELECT serialize_menu FROM restaurants WHERE rest_id = ?';
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("i", $rest_id);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($s);
		$stmt->fetch();
		if(is_null($s)){
			$this->menu = new Menu ();
		}else{		
			$s= base64_decode($s);
			$s = unserialize($s);
			$this->menu = $s;
		}
		
		foreach($this->menu->categories as $category){
		 if($category->id > $this->menu->lastCategory){
			 $this->menu->lastCategory=$category->id;
		 }
		}
		
		foreach($this->menu->categories as $category){
		  foreach($category->items as $item){
			if($item->id > $this->menu->lastItem){
				 $this->menu->lastItem=$item->id;
			}
		  }
		}
	
		foreach($this->menu->extras as $extra){
			if($extra->id > $this->menu->lastExtra){
				 $this->menu->lastExtra=$extra->id;
			}
		}
	}
	
	function grabCoupons($rest_id){
		global $conn;
		$sql = "SELECT id, code, expires, link, public, discount, title, type, extras, price, tg_points, credits  FROM coupons WHERE rest_id = ? AND expires > ?";
		$stmt = $conn->prepare($sql);
		echo $conn->error;
		$now = time();
		$stmt->bind_param("ii",$this->rest_id, $now);
		$stmt->execute();
		$stmt->bind_result($id, $code, $expires, $link, $is_public, $discount, $title, $type, $extras, $price, $tg_points, $credits);
		while($stmt->fetch()){
			$x=new stdClass();
			$x->price = $price;
			$x->id = "C". $id;
			$x->code = $code;
			$x->expires = $expires;
			$x->link = $link;
			$x->public = $is_public;
			$x->discount = $discount;
			$x->title=$title;
			$x->type=$type;
			$x->extras = json_decode($extras);
			$x->tg_points = $tg_points;
			$x->credits = $credits;
			array_push($this->coupons, $x);
		}
		
		$stmt->close();
		
	}
	function checkOpen(){
		
		global $conn;
		
		date_default_timezone_set($this->timezone);
		$local_time = new DateTime();
		$local_time_stamp = $local_time->getTimestamp();
		$week_day = $local_time->format("l");
		if(gettype($this->schedule)!= "object"){
		 $this->schedule = new Schedule();
		}		
		
		switch ($week_day){
			case "Monday":
				$close2 = $this->schedule->sunday_close;
				$open = $this->schedule->monday_open;
				$close = $this->schedule->monday_close;
				break;
			case "Tuesday":
				$close2 = $this->schedule->monday_close;
				$open = $this->schedule->tuesday_open;
				$close = $this->schedule->tuesday_close;
				break;
			case "Wednesday":
				$close2 = $this->schedule->tuesday_close;
				$open = $this->schedule->wednesday_open;
				$close = $this->schedule->wednesday_close;
				break;
			case "Thursday":
				$close2 = $this->schedule->wednesday_close;
				$open = $this->schedule->thursday_open;
				$close = $this->schedule->thursday_close;
				break;
			case "Friday":
				$close2 = $this->schedule->thursday_close;
				$open = $this->schedule->friday_open;
				$close = $this->schedule->friday_close;
				break;
			case "Saturday":
				
				$close2 = $this->schedule->friday_close;
				$open = $this->schedule->saturday_open;
				$close = $this->schedule->saturday_close;
				break;
			case "Sunday":
				$close2 = $this->schedule->saturday_close;
				$open = $this->schedule->sunday_open;
				$close = $this->schedule->sunday_close;
				break;
		}
		 
		$open = preg_replace('/\s/',"",$open);
		$close = preg_replace('/\s/',"",$close);
		$close2 = preg_replace('/\s/',"",$close2);
		
		
		$open = new DateTime($open);
		$close = new DateTime($close);
		$close2  = new DateTime($close2);
		if($close < $open && $local_time>$open){$this->open = 1;}
		else if($close2 < $open && $close2 > $local_time){$this->open = 1;}
		else if($local_time>$open && $local_time < $close){$this->open = 1;}
		else {$this->open = 0; }
		
		$this->open_time =  date_format($open, 'g:i A'); 
		$this->close_time = date_format($close, 'g:i A'); 
	}
	

	
		
	function grabRest ($rest_id){
		global $conn;
				
		$this->rest_id = $rest_id;
		
		$sql = 'SELECT closed, title, address, image, lat, lng, community, phone, email, points, status, schedule, type, sm_image, offers_delivery, delivery_base, delivery_rate, delivery_email, pos_review, neg_review, owner_id From restaurants Where rest_id = ?';
		$stmt=$conn->prepare($sql);			
		
		if(!$stmt->bind_param('i', $rest_id)){echo $stmt->error;}
		
		$stmt->execute();
		$stmt->bind_result($closed, $title, $address, $image, $lat, $lng, $community, $phone, $email, $points, $status, $schedule, $type, $sm_image, $offers_delivery, $delivery_base, $delivery_rate, $delivery_email,  $pos_review, $neg_review, $owner_id);
		
		while ($stmt->fetch()) {
		
				$this->closed = $closed;
				$this-> title = $title;
				$this-> address = $address;
				$this-> image = $image;
				$this->lat = $lat;
				$this->lng = $lng;
				$this->community = $community;
								
				$phone = PrettyPhone($phone);
				$this->phone = $phone;
				$this->email = $email;
				$this->points = $points;
				$this->status = $status;
								
							
				if(!isset($schedule)||is_null($schedule)){
					
					$this->schedule = new Schedule();
					
					
				}else{
					$this->schedule = unserialize($schedule);
				}
				
				
				
				$this->type = $type;
				$this->sm_image = $sm_image;
				$this->offers_delivery = $offers_delivery;
				$this->delivery_base = $delivery_base;
				$this->delivery_rate = $delivery_rate;
				$this->delivery_email = $delivery_email;
				
				$this->pos_review = $pos_review;
				$this->neg_review = $neg_review;
				$this->owner_id = $owner_id;
				
				
			}
		
		$stmt->close();
		
		$sql = "SELECT timezone FROM community WHERE name = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("s", $this->community);
		$stmt->execute();
		$stmt->bind_result($this->timezone);
		$stmt->fetch();
		$stmt->close();
		
		
		
		$this->checkOpen();
	
	
	}	

}



### SUB-FUNCTIONS

?>