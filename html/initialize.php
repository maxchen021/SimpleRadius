<?php

require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/php_classes/Database.php");
require_once(dirname(__FILE__) . "/php_classes/Encryption.php");
require_once(dirname(__FILE__) . "/php_classes/SimpleRadius.php");
require_once(dirname(__FILE__) . "/php_classes/FreeRadius.php");
require_once(dirname(__FILE__) . "/php_classes/Misc.php");
require_once(dirname(__FILE__) . "/php_classes/Authentication.php");
require_once(dirname(__FILE__) . "/php_classes/User.php");
require_once(dirname(__FILE__) . "/php_classes/SSLCerts.php");

//third party
require_once(dirname(__FILE__) . "/php_classes/Mobile_Detect.php");

global $CURRENT_DB;
$CURRENT_DB=new Database();


global $SYSTEM_SETTING;

Encryption::GetEncryptionConfig();




require_once(dirname(__FILE__) . "/header.php");
require_once(dirname(__FILE__) . "/footer.php");


?>
