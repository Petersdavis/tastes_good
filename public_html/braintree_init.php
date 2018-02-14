<?php

include (realpath(dirname(__FILE__)). "/../braintree/lib/Braintree.php");

function BTconfig($BTenv){
Braintree\Configuration::environment($BTenv->environment);
Braintree\Configuration::merchantId($BTenv->merchant);
Braintree\Configuration::publicKey($BTenv->public);
Braintree\Configuration::privateKey($BTenv->private);
}
?>