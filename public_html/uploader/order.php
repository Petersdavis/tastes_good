<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 
include '../../fpdf/fpdf.php';
include '../../swiftmailer/swift_required.php';
include '../client_print.php';
require_once("../braintree_init.php"); 
include("../call_em_all.php");

error_reporting(E_ALL);
ini_set('display_errors', 'On');



if($data = getattribute("data")){
	$order = json_decode($data);
}else{
	$x = new stdClass();
	$x->result = "failure";
	$x->error = "NO_ORDER";
	exit(json_encode($x));
}





$ord = new Order ();
$ord->rest_id = $order->rest_id;
$ord->user_id = $order->user->user_id;
//loose ends
$ord->paymentType = $order->paymentType;
$ord->deliveryOption = $order->pref->deliveryOption;
$ord->requestDate =  $order->pref->requestDate;
$ord->requestTime =  $order->pref->requestTime;
$ord->comments = $order->comments;
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
	$x->error_code = "DOUBLE_SUBMIT";
	echo json_encode($x);
	exit();
}
	


//user
$user = new User ();
$user->user_id = $order->user->user_id;
$user->fromSession();
		
//rest
$rest = new Restaurant();
$rest->grabRest($ord->rest_id);


$ord->owner_id  = $rest->owner_id;

$owner= new User();
$owner->user_id = $ord->owner_id;
$owner->fromSession();

$ord->rest_balance = $owner->credit;

if($rest->status == "ACTIVE"){
	$ord->env = 1;
	BTconfig($BTproduct);
	if ($order->paymentType == "online") {
		$nonce = getattribute('nonce');
		$nonce = json_decode($nonce);
	}

} else {
	$ord->env = 0;
	BTconfig($BTsandbox);
	if ($order->paymentType == "online") {
		$nonce = new stdClass();
		$nonce->nonce = "fake-valid-visa-nonce";
	
	}

}

	

//check that restaurant is open.
if(!$rest->open || $rest->closed){
	$x = new stdClass();
	$x->result = "fail";
	$x->error = "restaurant is closed.";
	$x->error_code = "REST_CLOSED";
	echo json_encode($x);
	exit();
}

//check that the restaurant is Printable

$printer = new stdClass();

$sql= "SELECT width, height, status FROM restaurant_client WHERE rest_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $ord->rest_id);
$stmt->execute();
$stmt->store_result();


if($stmt->num_rows == 0){
	$printer->status = 0;
	$printer->width = 5;
}else{
	$stmt->bind_result($width, $height, $status);
	$stmt->fetch();
	if($status){
	      
		$printer->status = 1;
		$printer->width = $width*0.00393701;
		$printer->height = $height*0.00393701;
	}else{
		$printer->status = 0;
		$printer->width = 5;
	}
}

$stmt->close();

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
		$x->result = "fail";
		$x->error = "Invalid Coupon";
		echo json_encode($x);
		exit();
	
	}else{
		if($coupon->tg_points){
			$ord->tg_points += $coupon->tg_points;
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
	$ord->subtotal = fixPrice($item, $rest->menu, $rest->coupons, $ord->subtotal);
}

if(sizeof($error)>0){
	$x=new stdClass();
	$x->result = "failure";
	$x->data = $error;
	exit(json_encode($x));
}


$diff = ($order->subTotal - $ord->subtotal);
$diff = $diff * $diff;
if($diff>0.15){
	$x = new stdClass();
	$x->result = "fail";
	$x->error_code = "MISMATCH_PRICES";
	$x->error = "Too much difference between subtotals from Client: ".$order->subTotal." from Server: ". $ord->subtotal ." Difference^2:". $diff;
	echo json_encode($x);
	exit();
}
	

//calculate the totals
	$ord->discount = 0;
	if(isset($ord->coupon->discount)){
		$ord->discount = round($ord->subtotal * $ord->coupon->discount, 2);
	}
		
	$ord->tg_points += floor($ord->subtotal/10);
	$ord->commission = round($ord->subtotal * 0.035, 2);
	
		
	$ord->tax = round(($ord->subtotal+$ord->deliveryCharge-$ord->discount)*0.13, 2);
	$ord->total = round($ord->subtotal-$ord->discount + $ord->tax + $ord->deliveryCharge + $ord->tip, 2);

	if ($ord->paymentType == "online") {
		$ord->amount_paid = $ord->total;
		$ord->payment_fee = round($ord->total*0.025,2);
		$ord->rest_delta = round(($ord->total - $ord->payment_fee) - $ord->commission, 2);
	}else{ 
		$ord->amount_paid = 0;
		$ord->payment_fee = 0; 
		$ord->rest_delta =  round($ord->commission * (-1),2);
	}
	
	$ord->rest_balance += $ord->rest_delta; 
		
	$serial = serialize($ord->items);
	
	
	$ord->storeToDb($serial);
	
//Salesforce Comissions




//Update Rest Owners Balance:

$owner->deltaCredit($ord->rest_delta);




//BRAINTREE
if ($ord->paymentType == "online") {		
	$BTresult = Braintree\Transaction::sale([
		'amount' => $ord->total,
		'paymentMethodNonce' => $nonce->nonce
		]);
	
	
	if ($BTresult->success && !is_null($BTresult->transaction) && ($BTresult->transaction->status=="authorized" || $BTresult->transaction->status=="authorizing" )) {
		$ord->transact_id = $BTresult->transaction->id;
		$ord->transact_method = $BTresult->transaction->paymentInstrumentType;
		
		
		if($ord->transact_method == "credit_card"){
		
		  $ord->transact_bin =  $BTresult->transaction->creditCardDetails->bin;
		 
		  
		  $sql="INSERT INTO transactions (timestamp, order_id, transact_id, method, bin) VALUES (?,?,?,?,?);";
		  if(!$stmt= $conn->prepare($sql)){
		  	echo $conn->error; 
		  	exit();
		  }
		  if(!$stmt->bind_param("iissi", $ord->timestamp, $ord->order_id, $ord->transact_id, $ord->transact_method, $ord->transact_bin)){
		   	echo $stmt->error;
		   	exit();
		  }
		  if(!$stmt->execute()){
		   	echo $stmt->error;
		   	exit();
		   }
		  
		  $stmt->close();
		  
		 
		  
		}else if($ord->transact_method == "paypal_account"){
		 $ord->transact_email = $BTresult->transaction->paypalDetails->payerEmail;
		
		  $sql="INSERT INTO transactions (timestamp, order_id, transact_id, method, email) VALUES (?,?,?,?,?);";
		  if(!$stmt= $conn->prepare($sql)){
		  	echo $conn->error; 
		  	exit();
		  }
		  $stmt->bind_param("iissi", $ord->timestamp, $ord->order_id, $ord->transact_id, $ord->transact_method, $ord->transact_email);
		  $stmt->execute();
		  $stmt->close();
		 
		
		
		}
		
		
	} else {
		$ord->remove();
		$errorString = "";
	
		foreach($result->errors->deepAll() as $error) {
			$errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
		}
		$x = new stdClass();
		$x->result = "fail";
		$x->error = "TRANSACT_FAIL";
		$x->details= $errorString;
		echo json_encode($x);
		exit();
				
	}
}
	
	//update coupon
	if($ord->coupon){
		$sql = "UPDATE coupons SET hits = hits+1, sales_total = sales_total + ? WHERE id = ?";
		if(!$stmt=$conn->prepare($sql)){
			echo $conn->error;
		}
			
		$stmt->bind_param("si", $ord->total, $ord->coupon->id);
		$stmt->execute();
		$stmt->close();
	}
	//update points, order_total, sales_total, credit, process fee, balance
	
				
	
	if($ord->env == 1){
		$sql="UPDATE restaurants SET order_total= order_total+ 1, sales_total = sales_total + ?, points = points+? WHERE rest_id = ?";
		$stmt=$conn->prepare($sql);
		$stmt->bind_param("ddi", $ord->total,  $ord->tg_points, $ord->rest_id);
		$stmt->execute();
		$stmt->close();
		
		$ord->payCommissions()
				
	}
	//serial
	
	
	$fname = $ord->order_id . ".pdf";
	$targetPath = "../../orders/". $fname; // Target path where file is to be stored
	$ord->link = $targetPath;
	
	$sql = "UPDATE restaurant_orders SET link=? WHERE order_id = ?";
	$stmt=$conn->prepare($sql);
	$stmt->bind_param("si", $ord->link, $ord->order_id);
	$stmt->execute();
	$stmt->close();

	
//fpdf writer:

$calc_height=25;


for ($qx = 0; $qx < 2; $qx++) {
$vert_space = 0.3;


$pdf = new FPDF("P", "in", [$printer->width, $calc_height]);


$pdf->AddPage();

//restaurant info:
$pdf->SetFont('Arial','B',12);
$pdf->Write(0.2, utf8_decode($rest->title));
$pdf->SetFont('Arial','B',10);
$pdf->Ln();
$pdf->SetX(0.25);
$pdf->Cell($printer->width - 0.5, 0.2, "Order #". $ord->order_id );
$pdf->Ln();
$pdf->Line(0.25, $pdf->getY()+0.2, $printer->width - 0.25, $pdf->getY()+0.2);
$pdf->SetY($pdf->GetY()+0.2, true);

$pdf->SetX(0.25);
$pdf->MultiCell($printer->width - 0.5, 0.2, utf8_decode($rest->address) );

//customer info:
$pdf->SetY($pdf->GetY()+0.3, true);
$pdf->SetFont('Arial','B',12);
$pdf->SetX(0.25);
$pdf->Write(0.2, "Customer: ". $user->fname ." ". $user->lname);
$pdf->SetFont('Arial','B',10);
if($ord->deliveryOption == 1){
        $pdf->SetX(0.25);
	$pdf->MultiCell($printer->width - 0.5, 0.2, utf8_decode($addr->address));
}
$pdf->Ln();
$pdf->SetX(0.25);
$pdf->Cell($printer->width - 0.5, 0.2, utf8_decode($user->email));
$pdf->Ln();
$pdf->SetX(0.25);
$pdf->Cell($printer->width - 0.5, 0.2, utf8_decode($user->phone));
$pdf->SetY($pdf->GetY()+0.3, true);

//order info:
$pdf->SetFont('Arial','B',12);
if($ord->deliveryOption){$delivery = "Delivery";}else{$delivery = "Pickup"; }
$pdf->Ln();
$pdf->SetX(0.25);
$pdf->Cell($printer->width - 0.5, 0.2,  "Order Payment: " . $ord->paymentType );
$pdf->Ln();
$pdf->SetX(0.25);
$pdf->Cell($printer->width - 0.5, 0.2, "Order Type:" . $delivery);
$pdf->Ln();
$pdf->SetFont('Arial','B',10);
$pdf->Ln();
$pdf->SetX(0.25);
$pdf->Cell($printer->width - 0.5, 0.2, "Date/Time: " . $ord->requestDate . "  " . $ord->requestTime);

//order details:
$pdf->SetFont('Arial','B',12);
$pdf->SetY($pdf->GetY()+0.2, true);
$pdf->SetX(0.25);
$pdf->Cell($printer->width - 0.5, 0.2, "Order Details: ");
$pdf->SetFont('Arial','B',10);
$pdf->SetY($pdf->GetY()+0.1, true);
$pdf->Line(0.25, $pdf->getY()+0.2, $printer->width - 0.25, $pdf->getY()+0.2);
$pdf->SetY($pdf->GetY()+0.2, true);

foreach($ord->items as $item){
		
	$pdf->Ln();
	$pdf->SetX(($printer->width - 0.95));
	$pdf->Cell(0.95, 0.2, $item->price);
	$pdf->SetX(0.25);
	$pdf->MultiCell($printer->width - 1.2, 0.2, utf8_decode($item->product));
	$pdf->SetFont('');
	$pdf->SetFontSize(9);	
	if(sizeof($item->extras)>0){
		foreach($item->extras as $extra){
			if($extra->selected[0]->select_id == -1)
				continue;
			else{
				$spacer = "  ";
				pdfPrintExtra($extra, $spacer);
			}
								
		}
	}
	$pdf->SetFont('Arial','B',10);	
			
}


// Totals:
$pdf->Line(0.25, $pdf->getY()+0.2, $printer->width - 0.25, $pdf->getY()+0.2);
$pdf->SetY($pdf->GetY()+0.3, true);

$pdf->SetX(($printer->width -  1.2));
$pdf->Cell(1.2, 0.2, "$". round($ord->subtotal,2));
$pdf->SetX(0.25);
$pdf->Cell($printer->width - 1.45, 0.2, "Subtotal:");

if($ord->discount){
	$pdf->Ln();
	$pdf->SetX(($printer->width -  1.2));
	$pdf->Cell(1.2, 0.2, "$". round($ord->discount,2));
        $pdf->SetX(0.25);
	$pdf->Cell($printer->width -  1.45, 0.2, "Discount:");
	}

if($ord->deliveryOption){
	$pdf->Ln();
	$pdf->SetX(($printer->width -  1.2));
	$pdf->Cell(1.2, 0.2, "$". round($ord->deliveryCharge,2));
        $pdf->SetX(0.25);
	$pdf->Cell($printer->width -  1.45, 0.2, "Delivery (". round($ord->distance, 2) ." km):");	
	
	$pdf->Ln();
	$pdf->SetX(($printer->width -  1.2));
	$pdf->Cell(1.2, 0.2, "$". round($ord->tax,2));
        $pdf->SetX(0.25);
	$pdf->Cell($printer->width -  1.45, 0.2, "Tax: ");
	
	$pdf->Ln();
	$pdf->SetX(($printer->width -  1.2));
	$pdf->Cell(1.2, 0.2, "$". round($ord->tip,2));
        $pdf->SetX(0.25);
	$pdf->Cell($printer->width -  1.45, 0.2, "Delivery Tip:");
} else {
	
	$pdf->Ln();
	$pdf->SetX(($printer->width -  1.2));
	$pdf->Cell(1.2, 0.2, "$". round($ord->tax,2));
        $pdf->SetX(0.25);
	$pdf->Cell($printer->width -  1.45, 0.2, "Tax: ");	
}
$pdf->Line(0.25, $pdf->getY()+0.2, $printer->width - 0.25, $pdf->getY()+0.2);
$pdf->SetY($pdf->GetY()+0.3, true);
	
$pdf->SetX(($printer->width -  1.2));
$pdf->Cell(1.2, 0.2, "$". round($ord->total,2));
$pdf->SetX(0.25);
$pdf->Cell($printer->width -  1.45, 0.2, "Total: ");

if(isset($ord->comments)){
$pdf->ln();
$pdf->ln();
$pdf->SetX(0.25);
$pdf->Cell($printer->width -  0.5, 0.2, "Order Comments: ");
$pdf->SetFontSize(8.5);
$pdf->ln();
$pdf->SetX(0.25);
$pdf->MultiCell($printer->width - 0.5, 0.2, utf8_decode($ord->comments) );
$pdf->SetFontSize(10);
}
if($ord->deliveryOption && strlen($addr->comment)){
$pdf->ln();
$pdf->ln();
$pdf->SetX(0.25);
$pdf->Cell($printer->width -  0.5, 0.2, "Delivery Comments: ");
$pdf->SetFontSize(8.5);
$pdf->ln();
$pdf->SetX(0.25);
$pdf->MultiCell($printer->width - 0.5, 0.2, utf8_decode($addr->comment) );
$pdf->SetFontSize(10);
}
	
$calc_height= $pdf->GetY()+2.5;
}

$pdf->Output("F", $targetPath);

if($printer->status){
	$result= PrintRequest($ord->rest_id, $ord->order_id, $targetPath);


if($result->result == "fail"){
		$printer->status = 0;
		

	}
		
}
//send an email
if($gProd==1){
	
	$transport = Swift_SmtpTransport::newInstance('box6176.bluehost.com', 465, 'ssl')
	->setUsername($email_user)
	->setPassword($email_pwd);
	
	$mailer = Swift_Mailer::newInstance($transport);
	
	/*
	if(!$printer->status){
		$message = Swift_Message::newInstance()
		->setEncoder(Swift_Encoding::get8BitEncoding())
		->setSubject("Could Not Print: Order #". $ord->order_id)
		->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
		->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
		->addTo('corporate@tastes-good.com','Tastes-Good.com')
		->addCC('petersdavis@gmail.com','Peter Davis')
		->setBody('Could not send order to printer.  Call to confirm', 'text/html')
		->attach(Swift_Attachment::fromPath($targetPath));
		$mailer->send($message);
			
	}
	*/	
	
	$owner_token = $owner->createToken();
	
	$body                = file_get_contents('../email_template/email_order_rest.html');
	$body                = preg_replace('/customer_name/',$user->fname . " ". $user->lname,$body);
	$body                = preg_replace('/customer_phone/',$user->phone,$body);
	$body                = preg_replace('/order_id/', $ord->order_id ,$body);
	$body                = preg_replace('/auth_token/', $owner_token ,$body);
	
	$message = Swift_Message::newInstance()
	->setEncoder(Swift_Encoding::get8BitEncoding())
	->setSubject("Order Requested: Order #". $ord->order_id)
	->setFrom(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->setSender(array('corporate@tastes-good.com' => 'Tastes-Good.com'))
	->addTo($rest->email, $owner->fname. " ". $owner->lname)
	->addTo("corporate@tastes-good.com", "Tastes-Good.com");
	$thumbs_up = $message->embed(Swift_Image::fromPath('../images/email/thumbs_up.jpg'));
    	$thumbs_down = $message->embed(Swift_Image::fromPath('../images/email/thumbs_down.jpg'));
	$contact_us = $message->embed(Swift_Image::fromPath('../images/email/privacy.png'));
	$terms = $message->embed(Swift_Image::fromPath('../images/email/contact_us.png'));
	$privacy = $message->embed(Swift_Image::fromPath('../images/email/termsconditions.png'));
	$tg_image = $message->embed(Swift_Image::fromPath('../images/logo/tg.png'));
	
	$body= preg_replace('/tg_image/', $tg_image  ,$body);
	$body= preg_replace('/thumbs_up_img/', $thumbs_up ,$body);
	$body= preg_replace('/thumbs_down_img/', $thumbs_down ,$body);
	$body= preg_replace('/contact_us_img/', $contact_us ,$body);
	$body= preg_replace('/terms_img/', $terms ,$body);
	$body= preg_replace('/privacy_img/', $privacy ,$body);
	$body=str_replace('\"','"',$body);
	$message->setBody($body, 'text/html')
	->addPart('Enable HTML email to view email.  Pdf Order receipt is attached below.', 'text/plain')
	->attach(Swift_Attachment::fromPath($targetPath));
	
	$mailer->send($message);
	
	$x=new stdClass();
	$x->result = "success";
	$x->printResult = $printer->status;
		
	echo json_encode($x);
	
  } else {
	
	
	
	$x=new stdClass();
	$x->result = "success";
	$x->printResult = $printer->status;
		
	echo json_encode($x);
	
  }
 /*
$broadcast_name = "Order #". $ord->order_id;
$TTSText = "<voice name='callie'>This is a call from Tastes-Good.com.  <break strength='medium'/> You have received an order. Check your email to confirm the order details. <break strength='medium'/>  You can confirm or reject the order through your email.</voice>";

$ret = createCall($call_username, $call_pin, $call_product, $broadcast_name, $rest->phone, $TTSText);


$sql = "Update restaurant_orders SET broadcast_id = ? WHERE order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("si", $ret, $ord->order_id);
$stmt->execute();
$stmt->close();


*/
$conn->close();