<?php
header("Access-Control-Allow-Origin: *");

error_reporting(E_ALL);
ini_set('display_errors', 'On');

## Basic boilerplate stuff

$gVersion     = 1;
$gAdminEmail  = 'petersdavis@gmail.com';
$gEmailSender = 'petersdavis@gmail.com';
$gMachine     = $_SERVER['SERVER_NAME'];	// 192.169.1.9
$gSoftware    = substr($_SERVER['SCRIPT_NAME'],1);
$gi           = strpos($gSoftware, '/');
$gLanguage    = substr($gSoftware, $gi+1);
$gSoftware    = substr($gSoftware, 0, $gi);	// textserver
$gi           = strpos($gLanguage, '/');
$gSystem      = substr($gLanguage, $gi+1);
$gLanguage    = substr($gLanguage, 0, $gi);	// eng
$gi           = strpos($gSystem,   '/');
$gSystem      = substr($gSystem,   0, $gi);	// dev


$gHost= gethostname();
$gIp = gethostbyname($gHost);
$gProd = 0;
$error = [];

require_once ( dirname(dirname(__FILE__)) . "/env.php");

ini_set('date.timezone', 'UTC');

//check if website is being hosted on BlueHost IP  or Local Server
if($gHost !== "box6176.bluehost.com"){
		$gProd = 0;
	}else{
		$gProd = 1;
	}


	


if(!isset($_SESSION)) {
  session_start();
}

//initialize key variables to null

$gUserid = 0;
$gPass = 0;
$user_rest = 0;
$gattempt = 0;

//start session
if (isset($_SESSION['user_id'])) {
  $gUserid = $_SESSION['user_id'];
}


 

if (isset($_SESSION['pass'])) {
 $gPass = $_SESSION['pass'];

}

if (isset($_SESSION['attempt'])) {
 $gattempt = $_SESSION['attempt'];
}

function checkProduction(){
	
	global $gProd;
	//if it is production --> check pwd?   fail?  bounce to loginform.php.
	//Any Logic specific to Production Environment
}



function base_path()
{
  return implode('/',array_slice(explode('/',$_SERVER['PHP_SELF']),0,-2));
}



function base_url() {
  $prot = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https://' : 'http://');
  $host = $_SERVER['SERVER_NAME'];
  $path = base_path();
  return $prot.$host.$path;   
}       

function short_base_url() {
  $host = $_SERVER['SERVER_NAME'];
  $path = base_path();
  return '//'.$host.$path;   
}  

//printer functions
define('START_TIME', 1451624400);	// 1-January-2016
define('SALT0', 530413);
define('SALT1', 840321);

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

##Function to Clean Data
function getgetcleanup($value)
{
  $ret = $value;
  $lth = strlen($ret);
  if ($lth > 1) {
    $c   = $ret{0};
    if ($c == $ret{$lth-1}) {
      switch ($c) {
      case '\'':
      case '"':
        $ret = substr($ret, 1, $lth-2);
  } } }

  $ret = urldecode($ret);
  $ret = trim($ret);
  if ($ret != '') {
    return $ret;
  }
  return null;
}

function getget($name)
{
  if (isset($_GET[$name])) {
    $ret = $_GET[$name];
    if (is_array($ret)) {
      foreach ($ret as $index => $value) {
        $ret[$index] = getgetcleanup($value);
      }
    } else {
      $ret = getgetcleanup($ret);
    }
    return $ret;
  }
  return null;
}

function getpost($name)
{
  if (isset($_POST[$name])) {
    $ret = $_POST[$name];
    if (is_array($ret)) {
      foreach ($ret as $index => $value) {
        $ret[$index] = trim($value);
      }
      return $ret;
    }
    $ret = trim($_POST[$name]);
    if ($ret != '') {
      return $ret;
  } }
  return null;
}

function getattribute($name)
{
  if (isset($_POST[$name])) {
    return getpost($name);
  } 
  if (isset($_GET[$name])) {
    return getget($name);
  }
  return null;
}

function getimage($name){
 
	
	$return = "";
	if(isset($_POST["submit"])) {  //form was submitted
		if($_FILES['userfile']['size'] !== 0){  //file exists
		$files=$_FILES[$name];
		$check = getimagesize($_FILES[$name]["tmp_name"]);
			if($check !== false) {  //file is an image
				$return = 'loaded image correctly';
			} else { $return = "File is not an image.";}; 
		} else { $return ='file size ==0';}
		
	} else {$return = '$POST not set';}
	
	if($return = ""){
	return "could not load image";
	} else {return $return;};
}


function PrettyPhone($phone){
	
	if(strlen($phone)==11){
		if(  preg_match( '/(\d{1})(\d{3})(\d{3})(\d{4})/', $phone,  $matches )){
			$phone =  $matches[1] . '-' .$matches[2] . '-' . $matches[3] . '-' .$matches [4];
    }} elseif (strlen($phone) == 10){
		if(  preg_match( '/(\d{3})(\d{3})(\d{4})/', $phone,  $matches )){
			$phone = $matches[1] . '-' .$matches[2] . '-' . $matches[3];
	}}
    	return $phone;
    
}
	

##Call Style Sheets
function srcStylesheet()
{
  global $gVersion;

  $args = func_num_args();
  for ($i = 0; $i < $args; ++$i) {
    echo '<link rel="stylesheet" href="',
		 func_get_arg($i),'?',$gVersion,'" />
';
  }
}

##Call Java Scripts
function srcJavascript()
{
  global $gVersion;

  $args = func_num_args();
  for ($i = 0; $i < $args; ++$i) {
    echo '<script type="text/javascript" language="Javascript" src="',
         func_get_arg($i),'?',$gVersion, '"></script>';
  }
}

$gInJavascript = false;

##Start Java Script
function StartScript()
{
  global $gInJavascript;

  if (!$gInJavascript) {
    echo '<script type="text/javascript" language="JavaScript">';
    $gInJavascript = true;
	} }

##Stop Java Script
function StopScript()
{
  global $gInJavascript;

  if ($gInJavascript) {
    echo '</script>';
    $gInJavascript = false;
} }

function html_Header($title)
{
  global $gMachine;
  global $gUserid;
  global $gPass;
  global $user_rest;
  global $gattempt;
  global $gProd

?>

<!--start HTML --> 

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?php echo $title; ?></title>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />


<?php
  $cache = getattribute('cache');
  if (!isset($cache)) {
?>
	<!-- Telling Browser not to Cache -->
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Expires" content="0" />
	
<?php 
  } 
	
$rest_id=getattribute('rest_id');


//jquery must load before bootstrap	
srcJavascript("https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js");	

//then bootstrap
?>

<!-- Bootstrap compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<!-- Bootstrap Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
<!-- Bootstrap minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD32Gk7NNgIQL30INQdSP2iTFxaDo0To-g&libraries=places"></script>
<?php

srcStylesheet ("https://fonts.googleapis.com/css?family=Acme|Gorditas:700");
//local scripts:
srcStylesheet("/css/tastegood.css");





echo ' <!--[if lt IE 9]>';
echo '    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>';
echo '    <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>';
echo '  <![endif]-->';

echo '</head>';

echo '<body>';



} 

##Generic Functions.

function fixPriceExtra ($extra, $menu, $subtotal){
	global $error;
	$foundXtr = 0;
	$foundOpt = 0;
	foreach($menu->extras as $ext){
		if($ext->id == $extra->extra_id){
			$foundXtr = 1;
			foreach($extra->selected as $select){
				$foundOpt = 0;
				foreach($ext->options as $option){
					if($option->name == $select->name){
						$foundOpt = 1;	
						if($option->price == $select->price){
							if(sizeof($select->extras)>0){
								foreach($select->extras as $q){
								  if($q->selected[0]->select_id !== -1){	
									  $subtotal = fixPriceExtra($q, $menu, $subtotal);    
								  }
							  	}
							}
							$subtotal = $subtotal + $option->price;	
						}else{
							$err = new stdClass();
							$err->error = "BAD_OPTION_PRICE";
							$err->id = $select->name;
							array_push($error, $x);
							
						}
						break 1;
					}
				
			}
			if($foundOpt == 0){
				$err = new stdClass();
				$err->error = "BAD_OPTION";
				$err->id = $select->name;
				array_push($error, $x);
				
			}
		}
	}
		if($foundXtr){
			break 1;	
		}
	}	
	if($foundXtr==0){
		$err = new stdClass();
		$err->error = "BAD_EXTRA";
		$err->id = $extra->extra_id;
		array_push($error, $x);
	}
	return $subtotal;
}


function fixPrice ($item, $menu, $coupons, $subtotal){
	global $error;
	$result = new stdClass();
	$result->error = "";
	$result->subtotal = 0;
	$failure = 0;
	$foundItem = 0;
	$pattern = '/[C]/';
	if(preg_match($pattern, $item->item_id)){
		foreach($coupons as $coupon){
			if ($coupon->code = $item->item_id){
			
				$foundItem = 1;
				if($item->price==$coupon->price){
					foreach($item->extras as $extra){
						  $subtotal = fixPriceExtra($extra, $menu, $subtotal);
						
					  }
					$subtotal = $subtotal  + $item->price;
					
				}else{
					$err = new stdClass();
					$err->error = "BAD_COUPON_PRICE";
					$err->id = $item->item_id;
					array_push($error, $err);
					
				}
		
		
		
			}	
		}
			
			
			
	}else{	
		foreach($menu->categories as $category){
			foreach($category->items as $x){
				if($x->id == $item->item_id){
					$foundItem = 1;
					if($x->price == $item->price){
						foreach($item->extras as $extra){
						  if($extra->selected[0]->select_id !== -1){
							  $subtotal = fixPriceExtra($extra, $menu, $subtotal);
						  }
						}
					 
					$subtotal = $subtotal  + $x->price;
					}else{
						$err = new stdClass();
						$err->error = "BAD_ITEM_PRICE";
						$err->id = $item->item_id;
						array_push($error, $x);
					}
					break 2;
				}
			}
		}
	}
	if($foundItem == 0){
		$err = new stdClass();
		$err->error = "BAD_ITEM";
		$err->id = $item->item_id;
		array_push($error, $x);
	}
	
	return $subtotal;
}

function pdfPrintExtra($extra, $spacer){
	global $pdf;
        global $printer;
	foreach($extra->selected as $select){
	       
	        $pdf->SetX($printer->width - 0.95);
	        $pdf->Cell(0.95, 0.2, $select->price);
		$pdf->SetX(0.25);
		$pdf->MultiCell($printer->width - 1.15, 0.2, $spacer . utf8_decode($select->name));
		if(sizeof($select->extras)>0){
			foreach($select->extras as $ext){
				if($ext->selected[0]->select_id == -1)
					continue;
				else{
					$currentspacer= $spacer . "   ";
					pdfPrintExtra($ext, $currentspacer);	
				}
			}
		}
				
	}
}

function invert($vector) { 
    $result = array(); 
    foreach($vector as $key1 => $value1) 
        foreach($value1 as $key2 => $value2) 
            $result[$key2][$key1] = $value2; 
    return $result; 
} 

function mysql_revert_string( $data ) {
    return str_replace(
        array( '\0'  , '\n', '\r', '\Z'  , '\"', '\\\'', '\\\\' ),
        array( "\x00", "\n", "\r", "\x1a", '"' , '\''  , '\\'   ),
        $data
    );
}





function calculateDistance($a1, $a2, $b1, $b2){
	  	$radlat1 = pi() * $a1/180;
        $radlat2 = pi() * $b1/180;
        $radlon1 = pi() * $a2/180;
        $radlon2 = pi()* $b2/180;
        $theta =  ((1000*$a2)-(1000*$b2))/1000;
        $radtheta =pi() * $theta/180;
        $dist = sin($radlat1) * sin($radlat2) + cos($radlat1) *cos($radlat2) * cos($radtheta);
        $dist = acos($dist);
        $dist = $dist * 180/pi();
        $dist = $dist * 60 * 1.1515;
        $dist = $dist * 1.609344 ;
        return $dist;
	
}

function sortCategories ($a, $b){
	return strnatcmp($a->hierarchy, $b->hierarchy);
	
}

?>