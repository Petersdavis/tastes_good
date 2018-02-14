<?php

$dir = dirname(__FILE__);
require_once($dir . '/include.php');

define('UPLOAD',13);

error_reporting(E_ALL);
$verbose = false;

/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE) {
  echo "socket_create() failed.
Reason: " . socket_strerror(socket_last_error()) . "
";
  goto no_socket;
}
if ($verbose) {
  echo "Socket Created OK.
";
}

$port    = intval("53413l");
$address = "192.168.1.53";

if ($verbose) {
  echo "Attempting to connect to '$address' on port '$port'...
";
}
$result = socket_connect($socket, $address, $port);
if ($result === false) {
  echo "socket_connect() failed.
Reason: " . socket_strerror(socket_last_error($socket)) . "
";
  goto no_connect;
}
if ($verbose) {
  echo "Socket connected OK.
";
}

$timestamp  = encodetime();
$timestamp1 = ($timestamp >> 32);
$timestamp2 = ($timestamp & 0xFFFFFFFF);
$checksum   = input_checksum($timestamp);

if (isset($argv[1])) {
  $clientno = $argv[1];
} else {
  $clientno   = 15;
}

$major    = 1;
$minor    = 0;
$version  = 1;

$filename = "print_winsock_client2.exe"; 
$contents = file_get_contents($filename);
$lth      = strlen($contents);

$messagelth = $lth + 37;

// Silliness .. my version of PHP doesn't support J format
$header = pack("NNNNNCNNNN", $messagelth, $timestamp1, $timestamp2, ($checksum>>32), ($checksum & 0xFFFFFFFF), UPLOAD, $clientno, $major, $minor, $version);

$message = $header . $contents;

if ($verbose) {
  echo "Sending message...
";
}
$result = socket_message($socket, $message, $messagelth);
if ($result === true) {
  if ($verbose) {
    echo "OK.
";
  }
}
if ($result === false) {
  echo "socket_write() failed.
Reason: " . socket_strerror(socket_last_error($socket)) . "
";
  goto no_connect;
}

if ($verbose) {
  echo "OK.
";
}

no_connect:
if ($verbose) {
  echo "Closing socket...
";
}
socket_close($socket);
if ($verbose) {
  echo "Socket closed OK.

";
}
no_socket:

?> 


