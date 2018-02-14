<?php
include '../../boilerplate.php';
checkProduction();

/*
include '../../dbconnect.php'; 


//Security Check  -- Only Admin can authorize payment
$secure = new Secure();
$secure->isAdmin();

$data = json_decode(getattribute("data"));

if(!isset($data->user_id)){
	$x= new stdClass();
	$x->result = "failure";
	$x->error = "REQUIRES_user_id";
	exit(json_encode($x));
}

$a = new User();
$a->user_id = $data->user_id;

$a->fromSession();


*/

$a = new stdClass();
$a->credit = 1;
$a->email = "anne.r.crowe@gmail.com";

//set up paypal

require_once "../../../PayPal-PHP-SDK/autoload.php";


$ppContext = new \PayPal\Rest\ApiContext(
        new \PayPal\Auth\OAuthTokenCredential(
            $pp_product_client,     // ClientID
            $pp_product_secret     // ClientSecret
        )
);


$payouts = new \PayPal\Api\Payout();
$senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();
$senderBatchHeader->setSenderBatchId(uniqid())
    ->setEmailSubject("Payment pending from Tastes-Good.com");
    
$senderItem1 = new \PayPal\Api\PayoutItem();
$senderItem1->setRecipientType('Email')
    ->setNote('Thank you.')
    ->setReceiver($a->email)
    ->setSenderItemId("ITM".uniqid())
    ->setAmount(new \PayPal\Api\Currency('{
                        "value":'.$a->credit.',
                        "currency":"CAD"
                    }'));

echo $senderItem1->sender_item_id;

$payouts->setSenderBatchHeader($senderBatchHeader)
    ->addItem($senderItem1);
  
try {
    $output = $payouts->create(null, $ppContext);
    
    echo "success? </br>";
    //set user credits == 0;
    exit(json_encode($output ));
    
    
    
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
   exit(var_dump($ex));
}    


?>