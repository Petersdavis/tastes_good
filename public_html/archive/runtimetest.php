<?php include 'boilerplate.php';
include 'dbconnect.php';
include '../fpdf/fpdf.php';
include 'call_em_all.php';



function sendPush($target, $msg){
	//$msg = []
	//
		
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
	curl_close( $ch );
	
	return $result;
}



function runTime(){
      $mtime = microtime(); 
      $mtime = explode(' ', $mtime); 
      $mtime = $mtime[1] + $mtime[0]; 
      return $mtime; 
}  




// creating bulk menus from just eat data. 
/*

$sql = "SELECT id, title, address FROM resto_3";
$stmt=$conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($id, $title, $address);

$restos = [];
while($stmt->fetch()){
	$x = new Restaurant();
	$x->temp_id = $id;
	$x->title = $title;
	$x->address = $address;
	$x->menu = new Menu();
	array_push ($restos, $x);
	}

$stmt->close();

foreach($restos as $x){
$category_count = 0;
$item_count = 0;
$sql = "SELECT category FROM restaurant_category WHERE id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $x->temp_id);
$stmt->execute();
$stmt->bind_result($category);

while($stmt->fetch()){
  $z = new Category();
  $z->category = $category;
  $category_count=$category_count+1;
  $z->id = $category_count;
  array_push($x->menu->categories, $z);
}
$stmt->close();


$sql = "SELECT category, product, description, price FROM restaurant_item WHERE id = ?";
$stmt=$conn->prepare($sql);

$stmt->bind_param("i", $x->temp_id);
$stmt->execute();
$stmt->bind_result($category, $product, $description, $price);
while($stmt->fetch()){
 foreach($x->menu->categories as $cat){
 	if($cat->category == $category){
 	$z = new Item();
 	$item_count = $item_count+1;
 	$z->id = $item_count;
 	$z->product = $product;
 	$z->category = $category;
 	$z->description = $description;
 	$pattern = '/[\d]+[.]{0,1}[\d]{0,2}/';
	preg_match($pattern, $price, $matches);
 	
 	$z->price = $matches [0];
 	array_push($cat->items, $z);
 	break;
 	}

	}



}
$stmt->close();
}
	

$resta = [];

$sql = 'SELECT rest_id FROM restaurants WHERE (community = "Waterloo" OR community = "Kitchener") AND rest_id != 3259 AND rest_id != 3309 AND rest_id !=24';
$stmt=$conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($id);

$match_tot  = 0 ;
while($stmt->fetch()){
	$x = new Restaurant();
	$x->rest_id = $id;
	array_push($resta, $x);
	   
	}
$stmt->close();


foreach($resta as $x){
	
	$x->grabRest($x->rest_id);
	$x->grabSerial($x->rest_id);
	
	if(1==1){
		$match = 0 ;
		foreach($restos as $z){
		    if($z->title == $x->title){
			echo "MATCH ON:  " . $x->title . "<br>";
		   	$x->menu = $z->menu;
			echo json_encode($x->menu). "<br><br>";  
		    	$x->putSerial($x->rest_id);
			$match = 1;
		    }
		   }
		    if(!$match){
		    echo "<strong>Failed to match ". $x->title. "</strong><br>";
		    }
		   $match_tot = $match_tot + $match;
		  }
		


}

echo "Total Matches: ". $match_tot . "  Community has: ".count($resta) . " Out of Menus:  " . count($restos) . "<br><br>";
*/

/*
$sql= "Select phone FROM restaurants WHERE phone is not null;";
$stmt= $conn->prepare($sql); 
$stmt->execute();
$stmt->bind_result($phone);
while($stmt->fetch()){

if( strlen($phone) > 10 ) { $phone = substr( $phone, 1 ); } 
echo $phone . "<br>";


}



$stmt->close();


*/




/* Get Address List

$sql = "SELECT address, rest_id, phone, user_name, temp_password, title FROM restaurants WHERE community = 'Waterloo'";
$stmt= $conn->prepare($sql);

$stmt->execute();
$stmt->bind_result($address, $rest_id, $phone, $user_name, $password, $title);

while ($stmt->fetch()){
echo "<strong>". $title . "   " . $address . "      ". $phone . "</strong><br>". $user_name . "     ".$password. "<br><br>";

}
*/

//call em all testing

/*
$broadcast_name = "test1";
$phone = "5198841629|Ian|Davis";
$TTSText = "this is a test call from tastes-good.com. press 1 to accept the call.   press 2 to reject the call.";
$msgID = "ae9289";

$ret = createCall($call_username, $call_pin, $call_staging, $broadcast_name, $phone, $msgID);

echo "<br><br> RETURN:  ". json_encode($ret);
*/







//google places api.

/*

$restaurants = [];
$sql="SELECT rest_id from restaurants where community='Kitchener'";
$stmt=$conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($rest_id);
while($stmt->fetch()){
array_push($restaurants, $rest_id);

}
$stmt->close();

foreach($restaurants as $rest_id){

$rest = new Restaurant();
$rest->grabRest($rest_id);

$googleLocation =  $rest->lat . ",". $rest->lng ;
$googleName = $rest->title;

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
 		$phone = preg_replace ( $pattern , "" , $phone );
 		echo $rest->title . "   ". $phone . "<br>";
 		$Multisql=$Multisql. "UPDATE restaurants SET phone = '".$phone."' WHERE rest_id = ". $rest_id."; ";
 		
 		
	
	}else{
		echo "failed to get details: ". $rest->title . "<br>";
	}
	
	
}else{
	echo "failed to get place: ". $rest->title . "<br>";	
	
}
	
}	


$conn->multi_query($sql)

	
*/

/*
$datetime2 = new DateTime('10:30');
$interval = $datetime1->diff($datetime2);
echo $interval->format('%R%a days');

echo $date->format('H:i:s') . "\n";

$date->setTimestamp(1171502725);
echo $date->format('U = Y-m-d H:i:s') . "\n";

var_dump($interval);

$date1 = new DateTime("now");
$date2 = new DateTime("tomorrow");

var_dump($date1 == $date2);
var_dump($date1 < $date2);
var_dump($date1 > $date2);
*/

//get data on all communities
/*
$start = runTime();
$rest_id=[];
$sql = "SELECT name, lat, lng FROM community";
$stmt = $conn->prepare($sql);
$stmt->execute();

$stmt->bind_result($community, $lat, $lng);
$communities = [];
	
class community{
	public $name;
	public $lat;
	public $lng;
	public $count = 0;
	public $timezone;
	public $time_offset;
}

while($stmt->fetch()){
	$a = new community();
	$a->name = $community;
	
	
	$googleQuery =  $a->name . ", Canada";
	$googleQuery = str_replace ("--", ", ", $googleQuery);
	$googleQuery = str_replace ("-", "+", $googleQuery);
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
		
		$a->province = "unknown";
		foreach($result['results'][0]['address_components'] as $comp){
			foreach($comp['types'] as $type){
				if($type=="administrative_area_level_1"){
					$a->province = $comp["long_name"];
					break 2;
				}
			}
		}
		
	
		$a->lat = $result['results'][0]['geometry']['location']['lat'];
		$a->lng = $result['results'][0]['geometry']['location']['lng'];
		
		$url = 'https://maps.googleapis.com/maps/api/timezone/json?location='. rawurlencode($a->lat).','.rawurlencode($a->lng).'&timestamp=1331161200&key=AIzaSyCmMnZ4ZQrCCXcwUSYXOkqmU9tMjK5lxxs&sensor=false';
	
		$cURL = curl_init();
		
		if(!curl_setopt($cURL, CURLOPT_URL, $url)){echo 'error curl_setopt:CURLOPT_URL';} 
		if(!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1)){echo 'error curl_setopt:CURLOPT_RETURNTRANSFER';}
		if(!curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false)){echo 'error curl_setopt:CURLOPT_SSL_VERIFYPEER';}
		$result = json_decode(curl_exec($cURL), true);
		
		curl_close($cURL);
		
		if ($result['status']=="OK"){
			$a->time_offset = $result['rawOffset'];
			$a->timezone = $result['timeZoneName'];
		} else {
		echo "failed to get timezone data for: ". $a->name . "<br>";	
			
		}
	
		
	}else{
		echo "failed to get lat lng on community: ". $a->name . "<br>";	
		
	}
	
	array_push($communities, $a);
	
}
$stmt->close();
echo "<b>results:</b> <br><br>";

foreach($communities as $comm){
	echo $comm->province;
	echo "<br>";
	
	$sql = "UPDATE community SET province = ? WHERE name = ?";
	if(!$stmt = $conn->prepare($sql)){
	 echo $conn->error . "<br>";
	}
	if(!$stmt->bind_param("ss", $comm->province, $comm->name)){
		echo $stmt->error . "<br>";
	}
		
	$stmt->execute();
	
}
*/
//calculate distance
/*
foreach ($rest as $x){
	$minD = 1000;
	$curD = 0;
	$rest_comm = "";
	foreach ($communities as $comm){
		$curD = calculateDistance($comm->lat, $comm->lng, $x->lat, $x->lng);
		if ($curD<$minD){
			$minD = $curD;
			$rest_comm = $comm->name;
		}

	}
	$x->community = $city . "--" . $rest_comm;
	         
	$sql = "UPDATE restaurant.restaurants3 SET community = '". $x->community ."' WHERE rest_id = " .$x->rest_id;
	$conn->query($sql);
	
	
	foreach($communities as $comm){
		if($comm->name == $rest_comm){
			$comm->count += 1	;
		}
	}
	
}
*/	



//$total = runTime() - $start;

/*
foreach($rest as $restaurant){
$start = runTime();


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


$community = "";
$type = "";

foreach ($result['results'][0]['address_components'] as $x){
	foreach ($x['types'] as $y){
		if($y=="locality"){
			$community = $x['short_name'];
			$type = "locality";
			break;
		}
		elseif($y=="administrative_area_level_2"){
			$community = $x['short_name'];
			$type = "administrative_area_level_2";
		}
	}
	
	if($type == "locality"){
		break;
	}
}

$sql = "UPDATE restaurants SET lat= ?, lng= ?, community = ? WHERE rest_id = ?"; 
$stmt=$conn->prepare($sql);		

echo $conn->error . "   ". $stmt->error;
$stmt->bind_param('ddsi',$lat, $lng, $community, $restaurant->rest_id);
$stmt->execute();
$stmt->close();
}
}
*/


//Create a payment 
// # CreatePaymentSample
//
// This sample code demonstrate how you can process
// a direct credit card payment. Please note that direct 
// credit card payment and related features using the 
// REST API is restricted in some countries.
// API used: /v1/payments/payment
/*
function createPPPayment(){
	require __DIR__ . '/../bootstrap.php';
	use PayPal\Api\Amount;
	use PayPal\Api\CreditCard;
	use PayPal\Api\Details;
	use PayPal\Api\FundingInstrument;
	use PayPal\Api\Item;
	use PayPal\Api\ItemList;
	use PayPal\Api\Payer;
	use PayPal\Api\Payment;
	use PayPal\Api\Transaction;
	// ### CreditCard
	// A resource representing a credit card that can be
	// used to fund a payment.
	$card = new CreditCard();
	$card->setType("visa")
		->setNumber("4669424246660779")
		->setExpireMonth("11")
		->setExpireYear("2019")
		->setCvv2("012")
		->setFirstName("Joe")
		->setLastName("Shopper");
	// ### FundingInstrument
	// A resource representing a Payer's funding instrument.
	// For direct credit card payments, set the CreditCard
	// field on this object.
	$fi = new FundingInstrument();
	$fi->setCreditCard($card);
	// ### Payer
	// A resource representing a Payer that funds a payment
	// For direct credit card payments, set payment method
	// to 'credit_card' and add an array of funding instruments.
	$payer = new Payer();
	$payer->setPaymentMethod("credit_card")
		->setFundingInstruments(array($fi));
	// ### Itemized information
	// (Optional) Lets you specify item wise
	// information
	$item1 = new Item();
	$item1->setName('Ground Coffee 40 oz')
		->setDescription('Ground Coffee 40 oz')
		->setCurrency('USD')
		->setQuantity(1)
		->setTax(0.3)
		->setPrice(7.50);
	$item2 = new Item();
	$item2->setName('Granola bars')
		->setDescription('Granola Bars with Peanuts')
		->setCurrency('USD')
		->setQuantity(5)
		->setTax(0.2)
		->setPrice(2);
	$itemList = new ItemList();
	$itemList->setItems(array($item1, $item2));
	// ### Additional payment details
	// Use this optional field to set additional
	// payment information such as tax, shipping
	// charges etc.
	$details = new Details();
	$details->setShipping(1.2)
		->setTax(1.3)
		->setSubtotal(17.5);
	// ### Amount
	// Lets you specify a payment amount.
	// You can also specify additional details
	// such as shipping, tax.
	$amount = new Amount();
	$amount->setCurrency("USD")
		->setTotal(20)
		->setDetails($details);
	// ### Transaction
	// A transaction defines the contract of a
	// payment - what is the payment for and who
	// is fulfilling it. 
	$transaction = new Transaction();
	$transaction->setAmount($amount)
		->setItemList($itemList)
		->setDescription("Payment description")
		->setInvoiceNumber(uniqid());
	// ### Payment
	// A Payment Resource; create one using
	// the above types and intent set to sale 'sale'
	$payment = new Payment();
	$payment->setIntent("sale")
		->setPayer($payer)
		->setTransactions(array($transaction));
	// For Sample Purposes Only.
	$request = clone $payment;
	// ### Create Payment
	// Create a payment by calling the payment->create() method
	// with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
	// The return object contains the state.
	try {
		$payment->create($apiContext);
	} catch (Exception $ex) {
		// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
		ResultPrinter::printError('Create Payment Using Credit Card. If 500 Exception, try creating a new Credit Card using <a href="https://ppmts.custhelp.com/app/answers/detail/a_id/750">Step 4, on this link</a>, and using it.', 'Payment', null, $request, $ex);
		exit(1);
	}
	// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
	 ResultPrinter::printResult('Create Payment Using Credit Card', 'Payment', $payment->getId(), $request, $payment);
	return $payment;

}
*/


//Print an Order to Pdf

//create mailer
/*
$pdf = new FPDF("L", "in", [7,5]);
$pdf->SetFont('Arial','B',14);

foreach($restaurants as $restaurant){
	$pdf->AddPage();
	$pdf->Image('images/biz_card.png',0,0,3.5,2);
	$pdf->Text(1.6, 4.4, $restaurant->title);
}
$pdf->Output();

//echo "Saving menu's  as Serials took \t$total seconds <br><br>";		
*/

//Create Rest Id's and Passwords

/*

$restaurants = [];

$sql="SELECT rest_id FROM restaurants WHERE community = 'Waterloo'";
$stmt=$conn->prepare($sql);
$stmt->execute();

$stmt->bind_result($rest_id);

$restaurants=[];
while($stmt->fetch()){
	$a = new Restaurant();
	$a->rest_id= $rest_id;
	array_push($Wrestaurants, $a);    
	
}
$stmt->close();


$Krestaurants = [];
$sql="SELECT rest_id FROM restaurants WHERE (community = 'Kitchener' OR community = 'Waterloo') AND (rest_id > 24)";
$stmt=$conn->prepare($sql);
$stmt->execute();

$stmt->bind_result($rest_id);

$restaurants=[];
while($stmt->fetch()){
	$a = new Restaurant();
	$a->rest_id= $rest_id;
	$a->seq = $rest_id + 1000;
	array_push($restaurants, $a);    
	
}
$stmt->close();

foreach($restaurants as $a){
	$sql = "SELECT user_name, temp_password FROM restaurants Where rest_id =". $a->rest_id;
	echo $conn->error;
	$stmt=$conn->prepare($sql);
	
	$stmt->execute();
	$stmt->bind_result($user, $pwd);
	$stmt->fetch();
	$a->user = $user;
	$a->pwd = $pwd;
	$stmt->close();
	echo $a->pwd . "<br>";

}
*/

/*
$last = getattribute('last') - 1;
$next = $last + 25;

$sql= "SELECT user_name, temp_password, rest_id, address, title FROM restaurants WHERE (community = 'Waterloo' OR community = 'Kitchener') AND rest_id>24;";
$stmt=$conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($user, $pwd, $id, $address, $title);
$restaurants = [];
while($stmt->fetch()){
$a = new stdClass();
$a->title = $title;
$a->address = $address;
$a->user = $user;
$id = $id +1000;
$a->seq = "Seq id: ".$id;
$a->pwd=$pwd;
 array_push($restaurants, $a);
}

$stmt->close();
$sql="";

*/
/*
foreach($restaurants as $restaurant){
	

	
	$pattern ='/[^A-Za-z]/';
	$string = preg_replace($pattern , "" , $restaurant->title );
	$string = substr ( $string , 0, min(7, strlen($string)));
	//$string = getUSERID($string);
	
	
	$sql = $sql . " Update restaurants Set user_name = '".$string."' Where rest_id = ".$restaurant->rest_id.";";
	$stmt= $conn->prepare($sql);
	
	
	
	$pwd =  "";
	
	$length = 6;
	$pwd = substr(str_shuffle("23456789ABCDEFGHJKLMNPQRSTUVWXYZ"), 0, $length);
	$pwd_hash = password_hash($pwd, PASSWORD_BCRYPT);
	$sql = $sql. "UPDATE restaurants SET password = '".$pwd_hash."', temp_password = '".$pwd."' WHERE rest_id = ". $restaurant . ";";
	
	
}
*/
/*
if ($conn->multi_query($sql) === TRUE) {
 if ($next < 4700){
   header('Location: http://www.tastes-good.com/runtimetest.php?last='.$next);
   }else{
   exit();
   }
   
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

*/

	/*
	$sql = "UPDATE restaurants SET user_name=?, password=?, temp_password=? WHERE rest_id = ?"; 
	$stmt=$conn->prepare($sql);	
	$stmt->bind_param('sssi', $string, $pwd_hash, $pwd, $restaurant->rest_id);
	$stmt->execute();
	$stmt->close();
	
	$restaurant->user_name = $string;
	$restaurant->temp_pwd  =$pwd;
	
	
}
*/
/*
function getUSERID($string){
	global $conn;
	$sql = "SELECT user_name FROM restaurants WHERE user_name = '".$string."'";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$stmt->store_result();
	
	if ($stmt->num_rows == 0){
		$stmt->close();
		return $string;
	} else {
	$stmt->close();	
	$string = $string . substr(str_shuffle("123456789"), 0, 1);
	echo $string;
	return getUSERID($string);	
	}
	
}


*/
//Pdf Printer

//POST CARDS
/*
$pdf = new FPDF("L", "in", [7,5]);
$pdf->SetFont('Courier','B',14);

foreach($restaurants as $restaurant){                          
	$pdf->AddPage();
	$pdf->Image('images/invitation.jpeg',0,0,7,5);
	$pdf->Text(2, 4.46, $restaurant->user);
	$pdf->Text(5.3, 4.46, $restaurant->pwd); 
	$pdf->SetFont('Courier','B',10);      
	$pdf->Text(5.8, 4.825, $restaurant->seq);  
	$pdf->SetFont('Courier','B',14);
}
$pdf->Output("D", 'KitWat.pdf');

*/



/*
//CSV
echo '"Restaurant_title";"Restaurant_address";"Restaurant_seq";"My_Title";"My_Address" <br>';
foreach($restaurants as $restaurant){                          
	echo '"'.$restaurant->title.'";"'.$restaurant->address.'";"'.$restaurant->seq.'";"www.Tastes-Good.com";"41 High St., Waterloo, ON, N2L 3X7" <br>';
	
}
*/

//LETTERS
/*
$pdf = new FPDF("L", "in", [7,5]);
$pdf->SetFont('Courier','B',14);

foreach($restaurants as $restaurant){                          
	$pdf->AddPage();
	$pdf->setXY(0.25, 0.25);
	$pdf->Cell(3,0.2, "www.Tastes-Good.com");
	$pdf->setXY(0.25, 0.5);
	$pdf->Cell(3,0.2, "41 High St. Waterloo");
	$pdf->setXY(0.25, 0.75);
	$pdf->Cell(3,0.2, "Ontario, Canada");
	$pdf->setXY(0.25, 1);
	$pdf->Cell(3,0.2, "N2L 3X7");
	
	$pdf->setXY(0,2);
	$pdf->Cell(7,0.2, $restaurant->title, 0, 0, 'C');
	$pdf->setXY(1.5,2.25);
	$pdf->MultiCell(4,0.2, $restaurant->address, 0,'C', false);
	
	
	$pdf->SetFont('Courier','B',10);      
	$pdf->Text(5.8, 4.825, $restaurant->seq);  
	
}
$pdf->Output("D", 'KitWat_envelope.pdf');	
*/
$conn->close();



 ?>