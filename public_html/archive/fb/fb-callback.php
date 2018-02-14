<?php
include ("../boilerplate.php");
include("php-graph-sdk-5.0.0/src/Facebook/autoload.php");



$fb = new Facebook\Facebook([
  'app_id' => '1890437671201988', // Replace {app-id} with your app id
  'app_secret' => '51863faae43f6c93b32304aec61d5a5b',
  'default_graph_version' => 'v2.2',
  ]);
  
 $helper = $fb->getRedirectLoginHelper();
 
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (isset($accessToken)) {
  // Logged in!
  $_SESSION['facebook_access_token'] = (string) $accessToken;

  // Now you can redirect to another page and use the
  // access token from $_SESSION['facebook_access_token']
}

// OAuth 2.0 client handler
$oAuth2Client = $fb->getOAuth2Client();

// Exchanges a short-lived access token for a long-lived one
$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

echo $_SESSION['facebook_access_token'];
echo "<br>";
echo $longLivedAccessToken;
echo "<br>";


$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);

try {
  $response = $fb->get('/me?locale=en_US&fields=name,email');
  $userNode = $response->getGraphUser();

} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

echo 'Logged in as ' . $userNode->getName() . '<br>';
echo $userNode->getField('email') . " : " .  $userNode['email'];


?>