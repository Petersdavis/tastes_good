<?php include 'boilerplate.php'; ?>
<?php include 'dbconnect.php'; ?>
<?php include '../fpdf/fpdf.php';?>
<?php include '../PHPMailer/class.phpmailer.php';?>
<?php include '../PHPMailer/class.pop3.php';?>
<?php require_once("braintree_init.php"); ?>
<?php
//var declarations

$form_type = getattribute ('form_type');

//functions
function verifyPassword($rest_id, $password){
	global $conn;
	$sql = 'SELECT password FROM restaurants WHERE rest_id = ?';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('i', $rest_id);
	$stmt->execute();
	$stmt->bind_result($hash);
	while($stmt->fetch()){
		if(password_verify($password, $hash)){
			$stmt->close();
			return true;
		}
	}
	$stmt->close();
	return false;
}

function compress($source, $destination, $quality) {

    $info = getimagesize($source);

    if ($info['mime'] == 'image/jpeg') 
        $image = imagecreatefromjpeg($source);

    elseif ($info['mime'] == 'image/gif') 
        $image = imagecreatefromgif($source);

    elseif ($info['mime'] == 'image/png') 
        $image = imagecreatefrompng($source);

    if(imagejpeg($image, $destination, $quality)){
    	return true;
    }else{
    	return false;
    }
    
    
}


function LoadFiles($file_name, $folder){
	global $rest_id;
	
	if(isset($_FILES[$file_name]['name'])){
			if(!$_FILES[$file_name]['error']){
			
			$validextensions = array("jpeg", "jpg", "png", "gif");
			$temporary = explode(".", $_FILES[$file_name]["name"]);
			$file_extension = end($temporary);
			$fname = $rest_id . "_logo";
			$sm_fname = $rest_id. "_sm_logo";
			$fname= $fname . "." . $file_extension;
			$sm_fname= $sm_fname . "." . $file_extension;
			
			
			if ((($_FILES[$file_name]["type"] == "image/png") || ($_FILES[$file_name]["type"] == "image/jpg") || ($_FILES[$file_name]["type"] == "image/jpeg")||($_FILES[$file_name]["type"] =="image/gif")) && ($_FILES[$file_name]["size"] < 100000) && in_array($file_extension, $validextensions)) {
				if (file_exists("upload/logo/" . $fname)) {
					unlink("upload/logo/". $fname);
				}
				
				$sourcePath = $_FILES[$file_name]['tmp_name']; // Storing source path of the file in a variable
				echo $sourcePath;
				$targetPath = "upload/".$folder."/". $fname; // Target path where file is to be stored
				$sm_targetPath = "upload/".$folder."/". $sm_fname;
				
				if(compress($sourcePath,$targetPath, 250)){ // Moving Uploaded file
						
						if(compress($targetPath, $sm_targetPath, 90)){
							$path = new stdClass();
							$path->large = "/". $targetPath;
							$path->small = "/". $sm_targetPath;
							$x = new stdClass(); $x->result = "success"; $x->data = $path; 
							return $x;
						}
					
						
																
				} else {$x = new stdClass(); $x->result = "fail"; $x->error = "move_uploaded_file Error"; return $x;}
				
				}
			} else {$x = new stdClass(); $x->result = "fail"; $x->error = $_FILES[$file_name]["error"]; return $x;} 
		} else {$x = new stdClass(); $x->result = "fail"; $x->error = "NO FILE"; return $x;}
}



switch ($form_type) {
	case "rest_review":
		$impression = getattribute("thumbs");
		$order_id = getattribute("order_id");
		$review = getattribute("review");
		$email = getattribute("email");
		
		$sql = "SELECT rest_id, user_id FROM restaurant_orders WHERE order_id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $order_id);
		$stmt->execute();
		$stmt->bind_result($rest_id, $user_id);
		$stmt->fetch();
		$stmt->close;
		
		//confirm that rest_id and user_id are correct.
				
		$sql = "INSERT INTO restaurant_reviews (order_id, user_id, rest_id, impression, review) VALUES (?,?,?,?,?)"; 
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("iiiis", $order_id, $user_id, $rest_id, $impression, $review);
		$stmt->execute();
		$stmt->close();
		header('Location: http://www.tastes-good.com/index.php?review=1');
		
		
		
		
	case "edit_restaurant":
		$details = json_decode(getattribute('details'));
		$rest_id = getattribute('rest_id');
		$result = LoadFiles ("rest_logo", "logo");
		if($result->result == "success"){
			$image = $result->data->large;
			$sm_image = $result->data->small;
			$sql = "UPDATE restaurants SET image = ?, sm_image = ? WHERE rest_id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("ssi", $image,  $sm_image, $rest_id);
			$stmt->execute();
			$stmt->close();
		}
		$a = new Restaurant();
		$a->grabRest($rest_id);
		
		if($details->address !== $a->address){
			$googleQuery =  $restaurant->address;
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
				$lat = $result['results'][0]['geometry']['location']['lat'];
				$lng = $result['results'][0]['geometry']['location']['lng'];
				$address = $result['results'][0]['formatted_address'];
				
				$sql =  "UPDATE restaurants SET address = ?, lat = ?, lng=?  WHERE rest_id = ?";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("sssi", $address, $lat, $lng, $rest_id);
				$stmt->execute();
				$stmt->close();
			}
		}
		$phone = $details->phone;
		$pattern = '/[\D]/';
		$phone = preg_replace ( $pattern , "" , $phone );
		if(preg_match( '/(\d{10,11})/', $phone,  $matches)){
			$phone = $matches[0];
		}
			
		
		$sql = "UPDATE restaurants SET title = ?, first_name=?, last_name = ?, type = ?, phone=?, email=? WHERE rest_id = ?";	
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ssssssi", $details->title, $details->first_name, $details->last_name, $details->type, $phone, $details->email, $rest_id);
		$stmt->execute();
		$stmt->close();
		break;
		
	case "delete_coupon":
		$coupon=getattribute('coupon');
		$rest_id=getattribute('rest_id');
		
		$sql="DELETE FROM coupons WHERE id = ? AND rest_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("ii", $coupon, $rest_id);
		$stmt->execute();
		$stmt->close();
		
		break;
	case "create_coupon":
		$coupon = json_decode(getattribute('coupon'));
		$extras = json_decode(getattribute('extras'));
		$rest_id = getattribute('rest_id');
		
		$a = new Restaurant();
		$a->grabRest($rest_id);
		
		//update the Extras
		$a->grabSerial($rest_id);
		$a->menu->extras = $extras;
		$a->putSerial($rest_id);
		
		//Build Coupon
		$expires = time() + ($coupon->expire *31*24*60*60);
		$coupon->expires = date("F j, Y", $expires);
		$timestamp = time();
		$ext = json_encode($coupon->extras);
		$check = 0;
		
		while($check==0){
			$coupon_code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
				
			$sql = "SELECT * FROM coupons WHERE code = '". $coupon_code . "'";
			$stmt = $conn->prepare($sql);
			$stmt->execute();
			$stmt->store_result();
			if($stmt->num_rows == 0){
				$check = 1;
			}
			$stmt->free_result();
			$stmt->close();
		}
		
		$coupon->code = $coupon_code;
		
		$coupon->link = "/order.php?rest_id=".$rest_id."&coupon=".$coupon_code;
		
		//Store the Coupon
		
		$sql = "INSERT INTO coupons (code, link, rest_id, type, timestamp, discount, expires, public, title, price, extras) values (?,?,?,?,?,?,?,?,?,?,?)";
		$stmt=$conn->prepare($sql);			
		
		echo $conn->error;
		$stmt->bind_param("ssissssssss", $coupon_code, $coupon->link, $rest_id, $coupon->type, $timestamp, $coupon->discount, $expires, $coupon->public, $coupon->title, $coupon->price, $ext);
		
		
		$stmt->execute();
		$coupon->id = $stmt->insert_id;
		$stmt->close();
		
		//Return the Coupon
	
		$x = new stdClass();
		$x->result = "success";
		$x->coupon = $coupon;
		
		echo json_encode($x);
		break;
		
		
	case "marketing_payment":
		$order = json_decode(getattribute('order'));
		$nonce = json_decode(getattribute('nonce'));
		
		$a= new Restaurant();
		$a->grabRest($order->rest_id);
		
		$biz_card = $order->items->biz_card;
		$win_sticker =  $order->items->win_sticker;
		
		if($biz_card->quantity > 0){
			$product = "biz_card";
			$check=0;
			while($check==0){
				$coupon_code = substr(str_shuffle("123456789ABCDEFGHIJKLMNPQRSTUVWXYZ"), 0, 8);
					
				$sql = "SELECT * FROM coupons WHERE code = ". $coupon_code;
				$stmt = $conn->prepare($sql);
				$stmt->execute();
				$stmt->store_result();
				if($stmt->num_rows == 0){
					$x = 1;
				}
				$stmt->free_result();
				$stmt->close();
			}
			
			$order->timestamp = time();
			$order->address = $a->address;
			$order->first_name = $a->first_name;
			$order->last_name = $a->last_name;
			$order->pdf = "/upload/biz_card/".$a->rest_id.".pdf";
			
			$expires = time() + (3*31*24*60*60);
			$discount = 0.1;
			$type = "discount";
			$public = 0;
			
			//Create Coupon
			$sql = "INSERT INTO coupons (coupon_code, rest_id, timestamp, discount, expires, type, public) values (?,?,?,?,?,?,?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("sisssss", $coupon_code, $a->rest_id, $order->timestamp, $discount, $expires, $type, $public);
			$stmt->execute();
			$stmt->close();
			
			//Create Order
			$sql = "INSERT INTO marketing_order (rest_id, timestamp, total, product, quantity, link) values (?,?,?,?,?,?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("isssss", $a->rest_id, $order->timestamp, $biz_card->subtotal, $product, $biz_card->quantity, $order->pdf);
			$stmt->execute();
			$stmt->close();
						
		
			$pdf = new FPDF("L", "in", [3.5,2]);
			$pdf->SetFont('Arial','B',14);
			$pdf->AddPage();
			$pdf->Image('images/invitation.jpeg',0,0,1.13,1.13);
			$pdf->Image($a->image, 0, 0.44,1.14,1.14);
			$pdf->Text(1.6,1.14, $coupon_code);
			$pdf->Output("F", $order->pdf);
		
		}
		if($win_sticker->quantity > 0){
			$link = "/images/tg_banner.svg";
			$order->timestamp = time();
			$product = "win_sticker";
			
			
			$sql = "INSERT INTO marketing_order (rest_id, timestamp, total, product, quantity, link) values (?,?,?,?,?,?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("isssss", $a->rest_id, $order->timestamp, $win_sticker->subtotal, $product, $win_sticker->quantity, $link);
			$stmt->execute();
			$stmt->close();
			
		}
				
		
		if($order->total > 0){
			
			$result = Braintree\Transaction::sale([
				'amount' => $order->total,
				'paymentMethodNonce' => $nonce->nonce
			]);
			
			if ($result->success || !is_null($result->transaction)) {
				echo json_encode($result);
			} else {
				$errorString = "";
			
				foreach($result->errors->deepAll() as $error) {
					$errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
				}
				echo json_encode($errorString);
						
			}
		
			
		}
		
		
		break;
		
	case "change_password":	
		$rest_id = getattribute('rest_id');
		$passwords = json_decode(getattribute('password'));
		
		if(verifyPassword($rest_id, $passwords->old_pwd)){
			
			$new_pwd = password_hash($passwords->new_pwd, PASSWORD_BCRYPT);
			$sql = "UPDATE restaurants SET password = ? WHERE rest_id = ?";
			$stmt=$conn->prepare($sql);
			$stmt->bind_param("si", $new_pwd, $rest_id);
			$stmt->execute();
			$stmt->close();
			
		} else {echo "NO MATCH";}
		break;
		
	case "edit_schedule":
		$schedule=json_decode(getattribute('schedule'));
		$rest_id =getattribute('rest_id');
		$a = new Schedule ();
		$a->monday_open = $schedule->monday_open;
		$a->monday_close = $schedule->monday_close;
		$a->tuesday_open = $schedule->tuesday_open;
		$a->tuesday_close = $schedule->tuesday_close;
		$a->wednesday_open = $schedule->wednesday_open;
		$a->wednesday_close = $schedule->wednesday_close;
		$a->thursday_open = $schedule->thursday_open;
		$a->thursday_close = $schedule->thursday_close;
		$a->friday_open = $schedule->friday_open;
		$a->friday_close = $schedule->friday_close;
		$a->saturday_open = $schedule->saturday_open;
		$a->saturday_close = $schedule->saturday_close;
		$a->sunday_open = $schedule->sunday_open;
		$a->sunday_close = $schedule->sunday_close;
		$serial = serialize($a);
		
		$sql = "UPDATE restaurants SET schedule = ? WHERE rest_id =?";
		if(!$stmt = $conn->prepare($sql)){
			exit($conn->error);
		}
		$stmt->bind_param("si",$serial, $rest_id); 	
		$stmt->execute();
		$stmt->close();
		
		break;
	
    case "menu":
    	
    	
    	$categories = getattribute('categories');
    	$extras = getattribute('extras');
    	$rest_id = getattribute('rest_id');
    	$user_rest = $_SESSION['rest_id'];
    	if($user_rest!= -1 && $rest_id != $user_rest){
    		exit('you do not have permission to edit this menu');
		}
    	
    	
		$a=new restaurant();
		$a->menu = new menu();
		
		$a->menu->fromJSON($categories, $extras, $rest_id);
		$a->putSerial($rest_id);
		
			
		break;
	case "open_close":
		$close=getattribute('close');
		$rest_id = $_SESSION['rest_id'];
		
		$sql = "UPDATE restaurants SET closed = ? WHERE rest_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("ii", $close, $rest_id);
		$stmt->execute();
		$stmt->close();
		exit("success");		
		break;
	case "email_reset":
		$key = getattribute('key');
		$user_id = getattribute('user_id');
		$password = getattribute('password');
		
		$sql = "SELECT key, expires FROM user_pwd_reset WHERE user_id=?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("i", $user_id);
		$stmt->execute();
		$stmt->store_result();
		
		if($stmt->num_rows == 0){
			$x = new stdClass;
			$x->result = "fail";
			$x->error = "NO_KEY";
			echo json_encode($x);
			$stmt->close();
			exit();
		}
		
		$stmt->bind_result($key2, $expires);
		
		while($stmt->fetch()){
			if($key2 == $key &&  $expires > time()){
				$stmt->close();
				$pwd = password_hash($password, PASSWORD_BCRYPT);
				$sql="UPDATE users SET password = ? WHERE user_id = ?";
				$stmt= $conn->prepare($sql);
				$stmt->bind_param("si", $pwd, $user_id);
				$stmt->execute();
				$stmt->close();
				$_SESSION['user_id']= $user_id;
				
				$x = new stdClass();
				$x->result = "success";
				$x->error = "";
				echo json_encode($x);
				exit();
			}
			
		}
		$x = new stdClass();
		$x->result = "fail";
		$x->error = "NO_GOOD_KEY";
		echo json_encode($x);
		break;	 
		
	case "email_recovery":
		$email = getattribute('email');
		$sql = "Select user_id, fname, lname FROM users WHERE email = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->store_result();
		if($stmt->num_rows == 0){
			$x = new stdClass;
			$x->result = "fail";
			$x->error = "NO_EMAIL";
			echo json_encode($x);
			$stmt->close();
			exit();
		}
		if($stmt->num_rows > 1){
			$x = new stdClass;
			$x->result = "fail";
			$x->error = "MULTI_EMAIL";
			echo json_encode($x);
			$stmt->close();
			exit();
		}	
		
		$stmt->bind_result($user_id, $fname, $lname);
		$stmt->fetch();
		
		$key = substr(str_shuffle("23456789ABCDEFGHJKLMNPQRSTUVWXYZ"), 0, 15);
		$expires = time() + 60*60*3;
		
		$stmt->close();
		
		$sql = "INSERT INTO user_pwd_reset (user_id, key, expires) VALUES (?,?,?)";
		$stmt= $conn->prepare($sql);
		$stmt->bind_param("iss", $user_id, $key, $expires);
		
		$user_name= $fname. " ".  $lname;
		$reset_url = "http://www.tastes-good.com/uploader.php?form_type=email_reset&key=".$key;
		
		$body                = file_get_contents('/email_template/email_pwd.html');
		$body                = preg_replace('/pwd_reset_url/',$reset_url ,$body);
		$body                = preg_replace('/user_name/',$user_name ,$body);
		
		if($gProd==1){
		
			error_reporting(E_STRICT);
			date_default_timezone_set('America/Toronto');
			
			//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded
			
			$mail                = new PHPMailer();
			
			$mail->IsSMTP(); // telling the class to use SMTP
			$mail->Host          = "mail.tastes-good.com";
			$mail->SMTPAuth      = true;                  // enable SMTP authentication
			$mail->SMTPKeepAlive = true;                  // SMTP connection will not close after each email sent
			$mail->Host          = "mail.tastes-good.com"; // sets the SMTP server
			$mail->Port          = 25;                    // set the SMTP port for the GMAIL server
			$mail->Username      = "corporate@tastes-good.com"; // SMTP account username
			$mail->Password      = "_:A=.h|Vc+F2}";        // SMTP account password
			$mail->SetFrom('corporate@tastes-good.com', 'Tastes-Good.com');
			$mail->AddReplyTo('corporate@tastes-good.com', 'Tastes-Good.com');
			$mail->AddAddress($email, $user_name);
			$mail->Subject       = "Password Reset Email";
			$mail->Body    =  $body; 
			$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; 
			$mail->MsgHTML(json_encode($ord));
			$mail->AddAttachment($targetPath);  
			
			if(!$mail->Send()) {
				$x = new stdClass();
				$x->result = "fail";
				$x->error = "MAIL_ERROR";
				$x->details = $mail->ErrorInfo;
				echo json_encode($x);
				exit();
				
			  } else {
				$x = new stdClass();
				$x->result = "success";
				$x->error = "";
				echo json_encode($x);
				exit();
			  }
		}
				
		break;
		
	case "rest_status":
		$status=getattribute('status');
		$status=json_decode($status);
		$rest_id = $_SESSION['rest_id'];
		
		//create community log 
		$community = $_SESSION['community'];
		$timestamp = time();
		$type = "ACTIVITY";
		switch($status){
		case "REGISTRATION":
			$content = "IS LOGGED IN.";	
			break;
		case "DECLINED SERVICE":
			$content = "HAS DECLINED TO JOIN.";
			break;
		default:
		}
			
		
		$sql = "INSERT INTO community_logs (content, timestamp, community, rest_id, type) VALUES (?,?,?,?,?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sssis", $content, $timestamp, $community, $rest_id, $type);
		if(!$stmt->execute()){
			echo $stmt->error;
		}
		$stmt->close();
		
		
		//update the status
		$sql="UPDATE restaurants SET status = ? WHERE rest_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("si", $status->status, $rest_id);
		if(!$stmt->execute()){
			echo $stmt->error;
		}
		$stmt->close();
		break;
		
	case "newRest":
		$contact=getattribute('contact');
		$contact = json_decode($contact);
		$rest_id = $_SESSION['rest_id'];
		
		$sql = "UPDATE restaurants SET phone = ?, email = ? WHERE rest_id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ssi",$contact->phone,$contact->email, $rest_id);
		if(!$stmt->execute()){
			echo $stmt->error;
		}
		$stmt->close();
		break;
	
	case "refund_order":
		$order_id = getattribute('order_id');
		$refund = getattribute('refund');
		$reason = getattribute('reason');
		$time = time();
		
		$sql="INSERT INTO order_refund (order_id, timestamp, reason, refund) VALUES (?,?,?,?)";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("iisd", $order_id, $time, $reason, $refund);
		$stmt->execute();
		$stmt->close();
		
		//bt refunds the order
		
		///LOGIC HERE///
		
		if($gProd==1){
		
			error_reporting(E_STRICT);
			date_default_timezone_set('America/Toronto');
			
			//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded
			
			$mail                = new PHPMailer();
			
			$body                = "<body><h3> ORDER ID :". $order_id .". REQUESTS CANCEL BY RESTAURANT</h3></body>";
						
			$mail->IsSMTP(); // telling the class to use SMTP
			$mail->Host          = "mail.tastes-good.com";
			$mail->SMTPAuth      = true;                  // enable SMTP authentication
			$mail->SMTPKeepAlive = true;                  // SMTP connection will not close after each email sent
			$mail->Host          = "mail.tastes-good.com"; // sets the SMTP server
			$mail->Port          = 25;                    // set the SMTP port for the GMAIL server
			$mail->Username      = "corporate@tastes-good.com"; // SMTP account username
			$mail->Password      = "_:A=.h|Vc+F2}";        // SMTP account password
			$mail->SetFrom('corporate@tastes-good.com', 'Tastes-Good.com');
			$mail->AddReplyTo('corporate@tastes-good.com', 'Tastes-Good.com');
			$mail->AddAddress('petersdavis@gmail.com', "Peter Davis");
			$mail->Subject       = "Order--Cancellation";
			$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; 
			$mail->MsgHTML($body);
			$mail->AddAttachment("/orders/".$order_id.".pdf");  
			
			if(!$mail->Send()) {
				echo "Mailer Error (" . $mail->ErrorInfo . ')';
			  } else {
				echo "Message sent";
			  }

		}
		
		break;
		
	case "cancel_order":
		$order_id = getattribute('order_id');
		$reason = getattribute ('reason');
		$time = time();
		if(!$reason){
			$reason = "NONE GIVEN";
		}
		
		$sql = "INSERT INTO order_cancel (order_id, timestamp, reason) Values (?,?,?)";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("iis", $order_id, $time, $reason);
		
		$stmt->execute();
		$stmt->close();
		if($gProd==1){
		
			error_reporting(E_STRICT);
			date_default_timezone_set('America/Toronto');
			
			//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded
			
			$mail                = new PHPMailer();
			
			$body                = "<body><h3> ORDER ID :". $order_id .". REQUESTS CANCEL BY RESTAURANT</h3></body>";
						
			$mail->IsSMTP(); // telling the class to use SMTP
			$mail->Host          = "mail.tastes-good.com";
			$mail->SMTPAuth      = true;                  // enable SMTP authentication
			$mail->SMTPKeepAlive = true;                  // SMTP connection will not close after each email sent
			$mail->Host          = "mail.tastes-good.com"; // sets the SMTP server
			$mail->Port          = 25;                    // set the SMTP port for the GMAIL server
			$mail->Username      = "corporate@tastes-good.com"; // SMTP account username
			$mail->Password      = "_:A=.h|Vc+F2}";        // SMTP account password
			$mail->SetFrom('corporate@tastes-good.com', 'Tastes-Good.com');
			$mail->AddReplyTo('corporate@tastes-good.com', 'Tastes-Good.com');
			$mail->AddAddress('petersdavis@gmail.com', "Peter Davis");
			$mail->Subject       = "Order--Cancellation";
			$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; 
			$mail->MsgHTML($body);
			$mail->AddAttachment("/orders/".$order_id.".pdf");  
			
			if(!$mail->Send()) {
				echo "Mailer Error (" . $mail->ErrorInfo . ')';
			  } else {
				echo "Message sent";
			  }
			
			 
			

		}
		
		
		break; 
	
	
	case "order":
		$order = getattribute('order');
		$order = json_decode($order);
				
		if ($order->paymentType == "online") {
			$nonce = getattribute('nonce');
			$nonce = json_decode($nonce);
		}
		
		$ord = new Order ();
		$ord->rest_id = $order->rest_id;
		$ord->user_id = $order->user->user_id;
		//loose ends
		$ord->paymentType = $order->paymentType;
		$ord->deliveryOption = $order->pref->deliveryOption;
		$ord->requestDate =  $order->pref->requestDate;
		$ord->requestTime =  $order->pref->requestTime;
		$ord->timestamp =  $order->time;
		$ord->payment_fee = 0;
		$ord->tg_points = 0;
		$ord->commission = 0;
		
		//check to see if user has already submitted order
		
		$sql = "SELECT order_id FROM restaurant_orders WHERE order_time = ? AND rest_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("si",$ord->timestamp, $ord->rest_id);
		$stmt->execute();
		$stmt->store_result();
		
		if($stmt->num_rows > 0){
			$x = new stdClass();
			$x->result = "fail";
			$x->error = "duplicate_timestamp";
			echo json_encode($x);
			exit();
		}
			
		
		
		//user
		$user = new User ();
		$user->fromId($order->user->user_id);
				
		//rest
		$rest = new Restaurant();
		$rest->grabRest($ord->rest_id);
		if($rest->credit > 0){
			$ord->commission -= $rest->credit;	
		}
		
		$rest->grabSerial($ord->rest_id);
		//coupon
		
		if($order->coupon){
			$foundCoupon = 0;
			$rest->grabCoupons($ord->rest_id);
			foreach($rest->coupons as $coupon){
				if ($coupon->code == $order->coupon->code){
					$ord->coupon = $coupon;
					$foundCoupon = 1;
					break;
				}
			}
			if($foundCoupon == 0){
				$x = new stdClass();
				$x->result = "error";
				$x->error = "Invalid Coupon";
				echo json_encode($x);
				exit();
			
			}else{
				if($coupon->tg_points){
					$ord->tg_points += $coupon->tg_points;
				}
				if($coupon->credits){
					$ord->commission -= $coupon->credits;
				}
				
			}
				
				
		}
		
		//address
		
		if($order->pref->deliveryOption!=0){
			$ord->addr_id = $order->pref->deliveryAddress->id;
			$addr =  new Address ();
			$addr->fromId($ord->addr_id);
			$ord->tip = $order->driverTip;
			
			$ord->distance = calculateDistance($addr->lat, $addr->lng, $rest->lat, $rest->lng);
			$ord->deliveryCharge = floor($ord->distance/0.25) * 0.2 +4;	
		}
		
				
		
		//rebuild the orders
		$ord->items = $order->items;
		$ord->subtotal = 0;
		//check all prices :
		foreach($ord->items as $item){
			$x = fixPrice($item, $rest->menu);
			 if($x->result !== "success"){
			 	 echo json_encode($x);
			 	 exit();
			 }else{
			 $ord->subtotal = $ord->subtotal + $x->subtotal;
			 }
		}
		$diff = ($order->subTotal - $ord->subtotal);
		$diff = $diff * $diff;
		if($diff>0.15){
			$x = new stdClass();
			$x->result = "fail";
			$x->error = "Too much difference between subtotals: ". $diff;
			echo json_encode($x);
			exit();
		}
			
		
		//calculate the totals
			$ord->discount = 0;
			if(isset($ord->coupon->discount)){
				$ord->discount = round($ord->subtotal * $ord->coupon->discount, 2);
			}
				
		
			$ord->tax = round(($ord->subtotal+$ord->deliveryCharge-$ord->discount)*0.13, 2);
			$ord->total = round($ord->subtotal-$ord->discount + $ord->tax + $ord->deliveryCharge + $ord->tip, 2);
		
		    $ord->status = "confirmed";
		    
		    $serial = base64_encode(serialize($ord));
		    //update coupon
		    if($ord->coupon){
		    	$sql = "UPDATE coupons SET hits +=1, sales_total += ? WHERE id = ?";
		    	$stmt=$conn->prepare($sql);
		    	$stmt->bind_param("si", $ord->total, $ord->coupon->id);
		    	$stmt->execute();
		    	$stmt->close();
		    }
			//update points, order_total, sales_total, credit, process fee, balance
		    $ord->tg_points += floor($ord->subtotal/10);
		    $ord->commission += round($ord->subtotal * 0.035, 2);
		    
		    if($ord->commission < 0){
		    	$credit = $ord->commission * (-1);	
		    	$ord->commission = 0;
		    }else {
		    	$credit = 0;
		    }
		    		    
			if ($ord->paymentType == "online") {
				$ord->amount_paid = $ord->total;
				$ord->payment_fee = round($ord->total*0.025,2);
				$ord->rest_balance = round(($ord->total - $ord->payment_fee) - $ord->commission, 2);
			}else{ 
				$ord->amount_paid = 0;
				$ord->process_fee = 0; 
				$ord->rest_balance =  round($ord->commission * (-1),2);
			}
			
			$sql="UPDATE restaurants SET order_total= order_total+ 1, sales_total = sales_total + ?, balance = balance + ?, credit = ?, points = points+? WHERE rest_id = ?";
			$stmt=$conn->prepare($sql);
			$stmt->bind_param("dddii", $ord->total, $ord->rest_balance, $credit, $ord->tg_points, $ord->rest_id);
			$stmt->execute();
			$stmt->close();
			
			//serial
			$serial = serialize($ord->items);
		    
			//link
			
			$ord->storeToDb($serial);
			
			$fname = $ord->order_id . ".pdf";
			$targetPath = "/orders/". $fname; // Target path where file is to be stored
			$ord->link = $targetPath;
			
			$sql = "UPDATE restaurant_orders SET link=? WHERE order_id = ?";
			$stmt=$conn->prepare($sql);
			$stmt->bind_param("si", $ord->link, $ord->order_id);
			$stmt->execute();
			$stmt->close();
		/*
		//Braintree Charges the User:
		if ($ord->paymentType == "online") {		
			$result = Braintree\Transaction::sale([
				'amount' => $ord->total,
				'paymentMethodNonce' => $nonce->nonce
			]);
			
			if ($result->success || !is_null($result->transaction)) {
				echo json_encode($result);
			} else {
				$errorString = "";
			
				foreach($result->errors->deepAll() as $error) {
					$errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
				}
				echo json_encode($errorString);
				exit();
						
			}
    	}
			*/
		//fpdf writer:
		
		$vert_space = 0.3;
		
		$pdf = new FPDF("P", "in", [8.3,11.7]);
		$pdf->AddPage();
		
		//restaurant info:
		$pdf->SetFont('Arial','B',12);
		$pdf->Write(0.2, $rest->title);
		$pdf->SetFont('Arial','B',10);
		$pdf->Write(0.2, "\n". $rest->address );
		
		//customer info:
		$pdf->SetY($pdf->GetY()+0.3, true);
		$pdf->SetFont('Arial','B',12);
		$pdf->Write(0.2, "Customer: ". $user->fname ." ". $user->lname);
		$pdf->SetFont('Arial','B',10);
		if($ord->deliveryOption == 1){
			$pdf->Write(0.2, "\n". $addr->address);
		}
		$pdf->Write(0.2, "\n". $user->email);
		$pdf->Write(0.2, "\n". $user->phone);
		$pdf->SetY($pdf->GetY()+0.3, true);
		
		//order info:
		$pdf->SetFont('Arial','B',12);
		if($ord->deliveryOption){$delivery = "Delivery";}else{$delivery = "Pickup"; }
		$pdf->Write(0.2, "Order Payment: " . $ord->paymentType . "--- Order is:" . $delivery);
		$pdf->SetFont('Arial','B',10);
		$pdf->Write(0.2, "\n Requested for: " . $ord->requestDate . "  " . $ord->requestTime);
		
		//order details:
		$pdf->SetFont('Arial','B',12);
		$pdf->Write(0.2, "\n\n Order Details: ");
		$pdf->SetFont('Arial','B',10);
		$pdf->SetY($pdf->GetY()+0.1, true);
		$pdf->Line(0, $pdf->getY()+0.2, 3.5, $pdf->getY()+0.2);
		
		
		foreach($ord->items as $item){
			$pdf->Write(0.2, "\n". $item->product);
			$pdf->SetX(3);
			$pdf->Write(0.2, $item->price);
			$pdf->SetFont('');
			if(sizeof($item->extras)>0){
				foreach($item->extras as $extra){
					    $spacer = "   ";
						pdfPrintExtra($extra, $spacer);
										
				}
			}
			$pdf->SetFont('Arial','B',10);	
					
		}
		
		
		// Totals:
		$pdf->Line(0, $pdf->getY()+0.2, 3.5, $pdf->getY()+0.2);
		$pdf->SetY($pdf->GetY()+0.4, true);
		$pdf->Write(0.2, " Subtotal:");
		$pdf->SetX(3);
		$pdf->Write(0.2, "$". $ord->subtotal);
		if($ord->discount){
			$pdf->Write(0.2, "\n Discount:");
			$pdf->SetX(3);
			$pdf->Write(0.2, "$". $ord->discount);
			}
		
		if($ord->deliveryOption == "delivery"){
			$pdf->Write(0.2, "\n Delivery (".$ord->distance." km):");
			$pdf->SetX(3);
			$pdf->Write(0.2, $ord->deliveryCharge);
			$pdf->Write(0.2,  "\n Tax: ");
			$pdf->SetX(3);
			$pdf->Write(0.2,  "$". $ord->tax);
			$pdf->Write(0.2, "\n Delivery Tip:");
			$pdf->SetX(3);
			$pdf->Write(0.2,  "$".$ord->tip);
		} else {
			$pdf->Write	(0.2, "\n Tax: ");
			$pdf->SetX(3);
			$pdf->Write(0.2, "$". $ord->tax);	
		}
		$pdf->Line(0, $pdf->getY()+0.2, 3.5, $pdf->getY()+0.2);
		$pdf->SetY($pdf->GetY()+0.4, true);
		$pdf->SetFont('Arial','B',12);
		$pdf->Write(0.2, "Total: ");	
		$pdf->SetX(3);
		$pdf->Write(0.2, "$". $ord->total);	
				
		$pdf->Output("F", $targetPath);
		
		
		//send an email
		if($gProd==1){
		
			error_reporting(E_STRICT);
			date_default_timezone_set('America/Toronto');
			
			//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded
			
			$mail                = new PHPMailer();
			
			$body                = file_get_contents('email_template/email_order.html');
			$body                = preg_replace('/restaurant_title/',$rest->title,$body);
			$body                = preg_replace('/restaurant_phone/',$rest->phone,$body);
			$body                = preg_replace('/restaurant_address/',$rest->address,$body);
			$body                = preg_replace('/order_id/', $ord->order_id ,$body);
			
			$mail->IsSMTP(); // telling the class to use SMTP
			$mail->Host          = "mail.tastes-good.com";
			$mail->SMTPAuth      = true;                  // enable SMTP authentication
			$mail->SMTPKeepAlive = true;                  // SMTP connection will not close after each email sent
			$mail->Host          = "mail.tastes-good.com"; // sets the SMTP server
			$mail->Port          = 25;                    // set the SMTP port for the GMAIL server
			$mail->Username      = "corporate@tastes-good.com"; // SMTP account username
			$mail->Password      = "_:A=.h|Vc+F2}";        // SMTP account password
			$mail->SetFrom('corporate@tastes-good.com', 'Tastes-Good.com');
			$mail->AddReplyTo('corporate@tastes-good.com', 'Tastes-Good.com');
			$mail->AddAddress($user->email, $user->fname . " ". $user->lname);
			$mail->AddAddress($rest->email, $rest->fname . " ". $rest->lname);
			$mail->Subject       = "PHPMailer Test Subject via smtp, basic with authentication";
			$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; 
			$mail->MsgHTML(json_encode($ord));
			$mail->AddAttachment($targetPath);  
			
			if(!$mail->Send()) {
				echo "Mailer Error (" . $mail->ErrorInfo . ')<br />';
			  } else {
				echo "Message sent";
			  }
		}
		
		break;
	
	case "userLogin":
		$creds = getattribute("userLogin");
			
		$a = new User ();
		$result = $a->fromLogin ($creds);
		if ($result->result == "success"){
			$result->data = $a;
		}
		echo json_encode($result);
		break;
	
	case "getUser":
		$request_users = getattribute("users");
		$request_users = json_decode($request_users);
		
		$users = [];
		$results = [];
		
		foreach($request_users as $request){
			$x = new user();
			$result = $x->fromLocalStorage($request->user_id, $request->verify);
			array_push($users, $x);
			array_push($results, $result);
									
		}
		
		$z = new stdClass();
		$z->results = $results;
		$z->data = $users;
		echo json_encode ($z);
		
		break;
	
	case "emailCheck":
		//simple script checks if email exists in database.  
		$email=getattribute("email");
		$sql = "SELECT user_id FROM users WHERE email = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->store_result();
		if($stmt->num_rows==0){
			echo "0";
		}else{
			echo "1";
		}
		$stmt->free_result();
		$stmt->close();
		break;	
		
	case "newUser":
		$newUser = getattribute('newUser');		
		$a = new User();
		$result = $a->fromJSON($newUser);
		if($result->result == "success"||$result->error == "no_address"){
			$result->data = $a;
							
		} 		
			
		echo json_encode($result);
		break;
		
		
	case "getAddress":
		$user = new user();
		$creds = json_decode(getattribute("user"));
		$user->user_id = $creds->user_id;
		$user->verify = $creds->verify;
		$user->getAddresses();
		
		if(sizeof($user->addresses > 0)){
			$result = new stdClass();
			$result->result = "success";
			$result->error = "";
			$result->data = $user->addresses;
		}else{
			$result = new stdClass();
			$result->result = "fail";
			$result->error = "no_addresses";
		}
		
		echo json_encode($result);		
		break;
	
	case "user_logout":
		session_destroy();
		break;
		
	case "checkCoupon":
		$coupon = getAttribute('coupon');
		$timestamp = time();
		$rest_id = 0;
		$sql = "SELECT rest_id FROM coupons WHERE code = ? AND expires > ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("ss", $coupon, $timestamp);
		$stmt->execute();
		$stmt->bind_result($rest_id);
		$stmt->store_result();
		if($stmt->num_rows > 0){
			$stmt->fetch();
		}
		echo $rest_id;
		$stmt->close();	
		
		break;
		
		
	case "saveAddress":
		$address = json_decode(getattribute('address'));
		$user = new user();
		
		$user_id = getattribute("user");
		$user->user_id = $user_id;
		
		
		$sql = "INSERT INTO user_address (user_id, address, postcode, appt, buzz, lat, lng, type, comment) VALUES (?,?,?,?,?,?,?,?,?)";
		
		$stmt = $conn->prepare($sql);
		echo $conn->error;
		$stmt->bind_param("issssssss", $user->user_id, $address->formatted_address, $address->postcode, $address->appt, $address->buzz, $address->lat, $address->lng, $address->type, $address->comments); 
		if(!$stmt->execute()){
			$stmt->close();
			$result = new stdClass();
			$result->result = "fail";
			$result->error = "Connection: ". $conn->error . " Statement: ". $stmt->error; 
			echo json_encode($result);
			exit();
		} 
		$stmt->close();
		$user->GetAddresses();
		if(sizeof($user->addresses > 0)){
			$result = new stdClass();
			$result->result = "success";
			$result->error = "";
			$result->data = $user->addresses;
		}else{
			$result = new stdClass();
			$result->result = "fail";
			$result->error = "no_addresses";
		}
		
		
	echo json_encode($result);
	break;
	
	case "deleteAddress":
	$address_id = getattribute('address_id');
	$sql = "DELETE FROM user_address WHERE id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('i', $address_id);
	$stmt->execute();
	$stmt->close();
	break;
		
		  
	case "submitComment":
		
		$name=getattribute('name');
		$email=getattribute('email');
		$comment=getattribute('comment');
		if(!$rest_id=getattribute('rest_id')){
			$rest_id=0;
		}
		
		$sql = "INSERT INTO comments (rest_id, name, email, comment) VALUES (?,?,?,?)";
		$stmt= $conn->prepare($sql);
		$stmt->bind_param('isss', $rest_id, $name, $email, $comment);
		if($stmt->execute()){
			echo 'success';
		}else{
			echo $stmt->error;
		}
		$stmt->close();	
		break;
		
		
    default:
        echo "Did not recognize form";
}

$conn->close();



?>
