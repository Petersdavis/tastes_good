<?php
//Update 3.0.4
//New script to rebuild symbolic links

require 'wampserver.lib.php';

$newPhpVersion = $_SERVER['argv'][1];

linkPhpDllToApacheBin($newPhpVersion);

?>