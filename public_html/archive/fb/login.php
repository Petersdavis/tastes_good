<?php
include ("../boilerplate.php");
include("php-graph-sdk-5.0.0/src/Facebook/autoload.php");

$fb = new Facebook\Facebook([
  'app_id' => '1890437671201988', // Replace {app-id} with your app id
  'app_secret' => '51863faae43f6c93b32304aec61d5a5b',
  'default_graph_version' => 'v2.2',
  ]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; // Optional permissions
$loginUrl = $helper->getLoginUrl('https://www.tastes-good.com/fb/fb-callback.php', $permissions);

echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';

?>