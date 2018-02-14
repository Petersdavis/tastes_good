<?php

$dir = dirname(__FILE__);
require_once($dir . '/include.php');
/*
function ping($host, $port, $timeout) 
{ 
  $tB = microtime(true); 
  $fP = fSockOpen($host, $port, $errno, $errstr, $timeout); 
  if (!$fP) { return $errstr; } 
  $tA = microtime(true); 
  return round((($tA - $tB) * 1000), 0)." ms"; 
}

echo ping("209.183.141.235", 53413, 10);
*/

define('PING', 5);

error_reporting(E_ALL);

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE) {
  echo "socket_create() failed: 
Reason: " . socket_strerror(socket_last_error()) . "
";
  goto no_socket;
} 
echo "Socket Created OK.
";
if(!socket_set_option($socket,  getprotobyname("SOL_SOCKET"), 1, 1)){ 
   exit ("could not set option");
}
/*
if(!socket_bind( $socket , "50.116.82.99", 53413 )){
echo "socket_bind failed";
}
*/

$port    = intval("53413");
$address = "209.183.141.235";

echo "Attempting to connect to '$address' on port '$port'...
";

if(!socket_set_nonblock($socket)){
      exit("Unable to set nonblock on socket\n");
      }
      
 $time = time();
    while (!@socket_connect($socket, $address, $port))
    {
      $err = socket_last_error($socket);
      if ($err == 115 || $err == 114)
      {
        if ((time() - $time) >= 10)
        {
         
        $x= new stdClass();
	$x->result = "fail";
	$x->error_location = "socket_connect()";
	$x->error_message = "socket connection timed out";
	socket_close($socket);
	echo json_encode($x);
	exit();
        }
        sleep(1);
        continue;
      }
      $x= new stdClass();
	$x->result = "fail";
	$x->error_location = "socket_connect()";
	$x->error_message = socket_strerror($err);
	socket_close($socket);
	echo json_encode($x);
	exit();
      }

   if(!socket_set_block($this->socket)){
   exit("Unable to set block on socket\n");
   }      



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
  $clientno  = 21;
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


