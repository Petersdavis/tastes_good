<?php

define('START_TIME', 1451624400);	// 1-January-2016
define('SALT0', 530413);
define('SALT1', 840321);

function socket_message($socket, $message, $messagelth)
{
  $left = $message;
  $lth  = $messagelth;
  for (;;) {
    $sent = socket_write($socket, $left, $lth);
    if ($sent === false) {
      echo "socket_write() failed.
Reason: " . socket_strerror(socket_last_error()) . "
";
      return false;
    }
    if ($sent == $lth) {
      return true;
    }
    if ($lth < $sent) {
      echo "socket_write() failed.
Reason: socket wrote " . ($sent-$lth) . " bytes too many
      return false;
";
    }
    if ($sent != 0) {
      $lth -= $sent;
      $left = substr($left, $sent);
  } }
}

function encodetime()
{
  $time = gettimeofday();
  $sec  = intval($time['sec']);
  $usec = intval($time['usec']);

  return (($sec - START_TIME)<<20) | $usec;	// 1-January-2016
}

function checksum($value, $salt)
{
  $ret       = $value & 0x7FFFFFFFFFFFFFFF; // Ensure sign bit not set
  for ($phase = (($ret >> 3) & 0x03)+1; $phase; --$phase) {
    for ($remainder = $ret; $remainder != 0; ) {
     
       $shift       = (($remainder & 0x7) + 1);
       $remainder >>= 3;
       $ret ^= --$salt;
       $ret  = ($ret >> $shift) | ($ret << (63 - $shift)); // Don't rotate sign
       $ret &= 0x7FFFFFFFFFFFFFFF;	// Ensure sign bit not set
    }
    $remainder = $ret;
  }
  return $ret;
}

function input_checksum($value)
{
  return checksum($value, SALT0);
}

function output_checksum($value)
{
  return checksum($value, SALT1);
}

function valid_sender($actions)
{
  $time     = $actions->time;
  $checksum = $actions->checksum;

  if (!ctype_xdigit($time) || !ctype_xdigit($checksum)) {
    return FALSE;
  }
  return (hexdec($checksum) == output_checksum(hexdec($time)));
}
?>
