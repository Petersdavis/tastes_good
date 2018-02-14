<?php

function PrintRequest($clientno, $orderno, $targetPath){

$flags    = 0x30;   /* MessageBox flags */
$text     = "The \xC2\xA320.39 order has printed\x00"; /* MessageBox Text */


	
define('PRINT_PDF',7);

error_reporting(E_ALL);
$verbose = false;

/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE) {
	$x= new stdClass();
	$x->result = "fail";
	$x->error_location = "socket_create()";
	$x->error_message = socket_strerror(socket_last_error($socket));
	return $x;
 
}


$port    = intval("53413");
$address = "209.183.141.235";

socket_set_nonblock($socket)
      or die("Unable to set nonblock on socket\n");
      
 $time = time();
    while (!@socket_connect($socket, $address, $port))
    {
      $err = socket_last_error($socket);
      if ($err == 115 || $err == 114)
      {
        if ((time() - $time) >= 15)
        {
         
        $x= new stdClass();
	$x->result = "fail";
	$x->error_location = "socket_connect()";
	$x->error_message = "socket connection timed out";
	socket_close($socket);
	return $x;
        }
        sleep(1);
        continue;
      }
      $x= new stdClass();
	$x->result = "fail";
	$x->error_location = "socket_connect()";
	$x->error_message = socket_strerror($err);
	socket_close($socket);
	return $x;
      }

    socket_set_block($socket)
      or die("Unable to set block on socket\n");      


$timestamp  = encodetime();
$timestamp1 = ($timestamp >> 32);
$timestamp2 = ($timestamp & 0xFFFFFFFF);
$checksum   = input_checksum($timestamp);

$filename = $targetPath;
$contents = file_get_contents($filename);
$lth      = strlen($text)+strlen($contents);

$messagelth = $lth + 41;


// Silliness .. my version of PHP doesn't support J format
$header = pack("NNNNNCNNNNN", $messagelth, $timestamp1, $timestamp2, ($checksum>>32), ($checksum & 0xFFFFFFFF), PRINT_PDF, $clientno, $orderno, $timestamp1, $timestamp2, $flags);


$message = $header . $text .  $contents;


$result = socket_message($socket, $message, $messagelth);
if ($result === true) {
  if ($verbose) {
    echo "OK.
";
  }
}
if ($result === false) {
	$x= new stdClass();
	$x->result = "fail";
	$x->error_location = "socket_write()";
	$x->error_message = socket_strerror(socket_last_error($socket));
	
	socket_close($socket);
	return $x;
}
$x= new stdClass();
$x->result = "success";
socket_close($socket);
return $x;
}


?>