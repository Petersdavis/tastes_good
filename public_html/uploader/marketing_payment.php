<?php include '../boilerplate.php'; 
include '../dbconnect.php';
include '../fpdf/fpdf.php';
include '../../swiftmailer/swift_required.php';
require_once("braintree_init.php"); 

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
	$order->pdf = "../upload/biz_card/".$a->rest_id.".pdf";
	
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
