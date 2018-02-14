<?php

$dir = dirname(__FILE__);
require_once($dir . '/include.php');

define('PING', 5);

error_reporting(E_ALL);

/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE) {
  echo "socket_create() failed: 
Reason: " . socket_strerror(socket_last_error()) . "
";
  goto no_socket;
}
echo "Socket Created OK.
";

$port    = intval("53413");
$address = "209.183.141.235";

echo "Attempting to connect to '$address' on port '$port'...
";
$result = socket_connect($socket, $address, $port);
if ($result === FALSE) {
  echo "socket_connect() failed.
Reason: " . socket_strerror(socket_last_error($socket)) . "
";
  goto no_connect;
}
echo "Socket connected OK.
";

$timestamp = encodetime();
$checksum  = input_checksum($timestamp);

if (isset($argv[1])) {
  $clientno = $argv[1];
} else {
  $clientno  = 15;
}
$messagelth = 25;

// Silliness .. my version of PHP doesn't support J format
$message = pack("NNNNNCN", $messagelth, ($timestamp>>32), ($timestamp & 0xFFFFFFFF), ($checksum>>32), ($checksum & 0xFFFFFFFF), PING, $clientno);

echo "Sending message...
";
$result = socket_message($socket, $message, $messagelth);
if ($result === true) {
  if ($verbose) {
    echo "OK.
";
  }
}
if ($result === FALSE) {
  echo "socket write() failed.
Reason: " . socket_strerror(socket_last_error($socket)) . "
";
  goto no_connect;
}

echo "OK.
";

no_connect:

echo "Closing socket...
";
socket_close($socket);
echo "Socket closed OK.

";
no_socket:

?>

