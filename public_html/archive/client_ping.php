<?php
function PingServer($rest_id){

 

define('PING', 5);

error_reporting(E_ALL);

/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE) {
	
	$x= new stdClass();
	$x->result = "fail";
	$x->error_location = "socket_create()";
	$x->error_message = socket_strerror(socket_last_error());
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

$timestamp = encodetime();
$checksum  = input_checksum($timestamp);

$clientno = $rest_id;

$messagelth = 25;

// Silliness .. my version of PHP doesn't support J format
$message = pack("NNNNNCN", $messagelth, ($timestamp>>32), ($timestamp & 0xFFFFFFFF), ($checksum>>32), ($checksum & 0xFFFFFFFF), PING, $clientno);

$result = socket_message($socket, $message, $messagelth);
if ($result === true) {
  if ($verbose) {
    echo "OK.
";
  }
}
if ($result === FALSE) {
	$x= new stdClass();
	$x->result = "fail";
	$x->error_location = "socket_write()";
	$x->error_message = socket_strerror(socket_last_error($socket));
	return $x;
}

$x= new stdClass();
	$x->result = "success";
	$x->error_location = "";
	$x->error_message = "";
	return $x;

socket_close($socket);
}

?> 


