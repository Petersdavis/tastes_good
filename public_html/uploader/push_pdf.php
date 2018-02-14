<?php include '../boilerplate.php'; 
include '../dbconnect.php'; 


if(!isset($_SESSION['user_id'])||!$_SESSION['user_id']){
	$x=new stdClass();
	$x->result = "failure";
	$x->error = "bad_user_id";
	exit(json_encode($x));
}


$a = new User();
$a->fromSession();

$order_id = getattribute('order_id');
$sql = "SELECT rest_id, user_id From restaurant_orders Where order_id = ?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($rest_id, $user_id);
$stmt->fetch();
$stmt->close();



$pass=0;
foreach($a->restaurants as $b){
	if($b->rest_id == $rest_id){
		$pass=1;
	}
}	

if ($pass == 0 && $user_id != $a->user_id){
	echo "<html><body style ='background-color:white; height:100%;width:100%;padding:auto;'><h2 style='font-family:courier; font-size:30;margin-top:100px;'>You do not have access to this Order.</h2></body></html>";
}




function printExtra($extra, $indent){
foreach($extra->selected as $select){
echo "<tr>";       	
echo "<td style='width:80%;padding-left:".$indent.";'>".$select->name."</td>";
echo "<td style='width:20%;'>".$select->price."</td>";
echo "</tr>";
	        
		if(sizeof($select->extras)>0){
			foreach($select->extras as $ext){
				if($ext->selected[0]->select_id == -1)
					continue;
				else{
					printExtra($ext, $indent+8);	
				}
			}
		}
				
	}

}



if(getattribute('isphone')){

$a = new Restaurant();
$a->grabRest($rest_id);

$b = new User();
$b->fromId($user_id);

$c = new Order();
$c->getFromDb($order_id);
 
if($c->addr_id > 0){ 
$d= new Address();
$d->fromId($c->addr_id);
}

echo "<html><body style ='background-color:white;font-size:25px;padding-top:75px;'>";


echo "<div><strong>" .$a->title . "</strong></div>";

echo "<div>Order # ". $order_id . "</div>";
echo "<div>". $a->phone . "</div>";
echo "<br>";
echo "<div><strong>Customer: " .$b->fname . " ". $b->lname . "</strong></div>";
echo "<div>". $b->email . "</div>";
echo "<div>". $b->phone . "</div>";
echo "<br>";

if($c->deliveryOption){$delivery = "Delivery";}else{$delivery = "Pickup"; }
echo "<div><strong>Order Payment: " . $c->paymentType . "</strong></div>";
echo "<div><strong>Order Type:" . $delivery. "</strong></div>";
echo "<br>";
echo "<div><strong>Date/Time: " . $c->requestDate . "  " . $c->requestTime."</strong></div>";
echo "<br><hr><br>";

foreach($c->items as $item){
$indent = 0;
echo "<table style='font-size:1em;width:100%;'><tr><strong>";
echo "<td style='width:80%;'>".$item->product."</td>";
echo "<td style='width:20%;'>$".$item->price."</td>";
echo "</strong></tr>";
if(sizeof($item->extras)>0){
	foreach($item->extras as $extra){
		if($extra->selected[0]->select_id == -1)
			continue;
		else{
			$indent = 8;
			printExtra($extra, $indent);
		}
							
	}
}

}
echo "</table>";
echo "<br><hr><br>";
echo "<table style='font-size:1em;width:100%;'><tr><strong>";
echo "<td style='width:80%;'>Subtotal:</td>";
echo "<td style='width:20%;'>$". round($c->subtotal, 2)."</td>";
echo "</strong></tr>";


if($c->discount>0){

	echo "<tr><strong>";
	echo "<td style='width:80%;'>Discount:</td>";
	echo "<td style='width:20%;'>$". round($c->discount,2)."</td>";
	echo "</strong></tr>";
	
	}
	

if($c->deliveryOption){
	echo "<tr><strong>";
	echo "<td style='width:80%;'>Delivery (". round($c->distance, 2) ." km):</td>";
	echo "<td style='width:20%;'>$". round($c->deliveryCharge,2)."</td>";
	echo "</strong></tr>";
	
	echo "<tr><strong>";
	echo "<td style='width:80%;'>Tax:</td>";
	echo "<td style='width:20%;'>$". round($c->tax,2)."</td>";
	echo "</strong></tr>";
	
	echo "<tr><strong>";
	echo "<td style='width:80%;'>Delivery Tip:</td>";
	echo "<td style='width:20%;'>$". round($c->tip,2)."</td>";
	echo "</strong></tr>";
	
	
} else {
	
	echo "<tr><strong>";
	echo "<td style='width:80%;'>Tax:</td>";
	echo "<td style='width:20%;'>$". round($c->tax,2)."</td>";
	echo "</strong></tr>";	
}
echo "</table>";
echo "<br><hr><br>";
echo "<table style='font-size:1.3em;width:100%;'><tr><strong>";
echo "<td style='width:80%;'>Total:</td>";
echo "<td style='width:20%;'>$". round($c->total,2)."</td>";
echo "</strong></tr>";
echo "</table>";
echo "<br>";

if(isset($c->comments)&&strlen($c->comments)>0){
echo "<div><strong>Comments:</strong></div>";
echo "<div>".$c->comments."</div>";
}

if($c->deliveryOption && strlen($d->comment)){

echo "<div><strong>Address Instructions:</strong></div>";
echo "<div>".$d->comment."</div>";

}



echo "</body></html>";



}else{


$filename = "order_". $order_id	.".pdf";
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename='.$filename);

// The PDF source is in original.pdf
$pdf = readfile('../../orders/'.$order_id.'.pdf');
echo $pdf;
	
}
	
?>
	

