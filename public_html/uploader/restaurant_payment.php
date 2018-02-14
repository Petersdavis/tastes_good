<?php include '../boilerplate.php'; 
include '../dbconnect.php';
include '../../fpdf/fpdf.php';
include '../../swiftmailer/swift_required.php';
require_once("../braintree_init.php"); 


BTconfig($BTproduct);

$nonce = json_decode(getattribute('nonce'));
$payed = getattribute('payment_choice');

$rest_id = $_SESSION['rest_id'];

$a= new Restaurant();
$a->grabRest($rest_id);

//check that restaurant has not already placed order.
$sql = "SELECT * FROM marketing_order WHERE rest_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0) {
 	$x = new stdClass();
 	$x->result = "fail";
 	$x->error = "Entry already exists";
	echo json_encode($x);
	
	exit();
	
}


//create record
$order = new stdClass();
$order->rest_id = $rest_id;
$order->product = "biz_card and window sticker";
$order->timestamp = time();
$order->address = $a->address;
$order->first_name = $a->first_name;
$order->last_name = $a->last_name;
$order->pdf = "../upload/biz_card/".$a->rest_id.".pdf";
$order->total = 29.95;	

$order->transact = "";
if($payed && isset($nonce->nonce)){

$result = Braintree\Transaction::sale([
		'amount' => 29.95,
		'paymentMethodNonce' => $nonce->nonce
	]);
	
	if ($result->success || !is_null($result->transaction)) {
		$order->status = "Paid";		
		$order->braintree = "success";
		$order->transact = $BTresult->transaction->id;
		
	} else {
		$errorString = "";
	
		foreach($result->errors->deepAll() as $error) {
			$errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
		}
		
		$x = new stdClass();
		$x->result = "fail";
		$x->error = "could not process braintree";
		$x->errorString = json_encode($errorString);
		echo json_encode($x);
		exit();	
	}

}else{
$order->status = "Unpaid";


}


//Create Order
$sql = "INSERT INTO marketing_order (rest_id, timestamp, total, product, status, link, transact) values (?,?,?,?,?,?,?)";
if(!$stmt = $conn->prepare($sql)){
	$x = new stdClass();
	$x->result = "fail";
	$x->error = $conn->error;
	echo json_encode($x);
	exit();	
}

if(!$stmt->bind_param("isdssss", $a->rest_id, $order->timestamp, $order->total, $order->product, $order->status, $order->pdf, $order->transact)){
	$x = new stdClass();
	$x->result = "fail";
	$x->error = $stmt->error;
	echo json_encode($x);
	exit();	
}
//$stmt->execute();
$stmt->close();
				
//create pdf
$pdf = new FPDF("L", "in", [3.625,2.125]);
$pdf->AddPage();
$pdf->Image('../images/were_moving.png',0,0,3.625,2.125);
$pdf->Image($a->image, 0.125, 0.54,1.045,1.045);
$pdf->Output("F", $order->pdf);


	
