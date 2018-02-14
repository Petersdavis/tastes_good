<?php

function createCall($username, $pin, $proxy, $broadcast_name, $phone, $TTSText){
 
// =====================
// 1 = Announcement
// 2 = Survey
// 3 = SMS 
// =====================
$broadcast_type = "1";
 
// =================================
// 1 = listID
// 2 = CSV Binary attachment
// 3 = CommaDelimited Phonenumbers
// =================================
$phone_number_source = "3";
 
$phone = sanitizePhone($phone);

 
// ========================================
// leave blank to use default from account
// ========================================
$caller_id = "5194965294";


$client = new SoapClient($proxy, array("trace" => true));
 
$request = array (
                  "username" => $username,
                  "pin" => $pin,
                  "broadcastType" => $broadcast_type,
                  "phoneNumberSource" => $phone_number_source,
                  "broadcastName" => $broadcast_name,
                  "phoneNumberCSV" => "",
                  "launchDateTime" => "",
                  "checkCallingWindow" => "0",
                  "callerID" => $caller_id,
                  "commaDelimitedPhoneNumbers" => $phone,
                  "TTSText" => $TTSText,
                  "TTSTextVM" => $TTSText
                 );
                                               
$response = $client->ExtCreateBroadcast(array("myRequest" => $request));


//checkErrors($response);

return $response->ExtCreateBroadcastResult->broadcastID;


}



function callResult($username, $pin, $proxy, $broadcastID){
$getBroadcastDetails =1;
$surveyResultFilter = "*";
$client = new SoapClient($proxy, array("trace" => true));

$request = array (
                  "username" => $username,
                  "pin" => $pin,
                  "broadcastID" => $broadcastID,
                  "getBroadcastDetails" => $getBroadcastDetails,
                  "surveyResultFilter" => $surveyResultFilter
                 );

echo json_encode($response);
$response = $client->GetBroadcastResult(array("myRequest" => $request));

//checkErrors($response);

return $response->GetBroadcastResult->CallDetail;

}

function sanitizePhone($phone){
	$pattern = '/[\D]/';
	$phone = preg_replace ($pattern , "" , $phone);
	if( strlen($phone) > 10 ) { $phone = substr( $phone, 1 ); } 
	return $phone;
}



function checkErrors ($response){
	echo json_encode($response);
}
?>