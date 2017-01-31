<?php



require_once(dirname(__FILE__) . "/php_classes/Database.php");
require_once(dirname(__FILE__) . "/php_classes/Encryption.php");
require_once(dirname(__FILE__) . "/php_classes/SimpleRadius.php");
require_once(dirname(__FILE__) . "/php_classes/FreeRadius.php");
require_once(dirname(__FILE__) . "/php_classes/Misc.php");
require_once(dirname(__FILE__) . "/php_classes/Authentication.php");
require_once(dirname(__FILE__) . "/php_classes/User.php");

global $SYSTEM_SETTING;

$SYSTEM_SETTING['simple_radius_main_directory']='/etc/SimpleRadius';
$SYSTEM_SETTING['simple_radius_config_directory']=$SYSTEM_SETTING['simple_radius_main_directory'] . '/configs';

$SYSTEM_SETTING["DB_File"]=$SYSTEM_SETTING['simple_radius_main_directory'] . '/database/simple_radius.db';
$SYSTEM_SETTING["DB_User_Table"]='admin_user';
$SYSTEM_SETTING["DB_Session_Table"]='admin_user_sessions';



$SYSTEM_SETTING["freeradius_config_directory"]=$SYSTEM_SETTING['simple_radius_config_directory'] . '/freeradius';
$SYSTEM_SETTING["https_ssl_certs_directory"]=$SYSTEM_SETTING['simple_radius_config_directory'] . '/https_ssl_certs';
$SYSTEM_SETTING["radius_ssl_certs_directory"]=$SYSTEM_SETTING['simple_radius_config_directory'] . '/radius_ssl_certs';;

$SYSTEM_SETTING['ssl_cert']['key_size'] = "2048";
$SYSTEM_SETTING['ssl_cert']['public_key_name'] = "server.pem";
$SYSTEM_SETTING['ssl_cert']['private_key_name'] = "server.key";
$SYSTEM_SETTING['ssl_cert']['ca_cert_name'] = "ca.pem";
$SYSTEM_SETTING['ssl_cert']['dh_parameters_name'] = "dh";
$SYSTEM_SETTING['ssl_cert']['subject']="/C=US/ST=Virginia/L=/O=SimpleRadius/CN=SimpleRadius";


$SYSTEM_SETTING["simple_radius_script"]=$SYSTEM_SETTING['simple_radius_main_directory'] . '/scripts/simple_radius.php';

$SYSTEM_SETTING["simple_radius_config_directory"]=$SYSTEM_SETTING['simple_radius_main_directory'] . '/configs';

$SYSTEM_SETTING["factory_default_config_directory"]=$SYSTEM_SETTING['simple_radius_main_directory'] . '/factory_default';


//config backup setting
//can't use /tmp due to PrivateTmp in systemd
$SYSTEM_SETTING["config_backup_temp_directory"]=$SYSTEM_SETTING['simple_radius_main_directory'] . '/tmp';
$SYSTEM_SETTING["config_backup_directory_name"]='simple_radius_config_backup';
$SYSTEM_SETTING["config_backup_directory_path"]=$SYSTEM_SETTING["config_backup_temp_directory"] . "/" . $SYSTEM_SETTING["config_backup_directory_name"];
$SYSTEM_SETTING["config_backup_filename"]='simple_radius_config_backup.tar.gz';
$SYSTEM_SETTING["encrypted_config_backup_filename"]='backup.cfg';

$SYSTEM_SETTING["wifi_client_tool_for_windows_source_code_directory"]=$SYSTEM_SETTING['simple_radius_main_directory'] . '/wifi_client_tool/wifi_client_tool_for_windows/wifi_client_tool_for_windows';
$SYSTEM_SETTING["wifi_client_tool_for_windows_temp_directory_path"]=$SYSTEM_SETTING['simple_radius_main_directory'] . '/tmp/wifi_client_tool_for_windows';




$SYSTEM_SETTING["title"]='Simple Radius';
$SYSTEM_SETTING['version']="1.0";

$SYSTEM_SETTING["min_session_time"]=1800;
$SYSTEM_SETTING["max_session_time"]=3600;

$SYSTEM_SETTING["min_session_renewal_time"]=300;
$SYSTEM_SETTING["max_session_renewal_time"]=600;


$SYSTEM_SETTING["basic_setting_menu"]=array("wireless_routers.php"=>"Wireless Routers","radius_users.php"=>"Users","wifi_client_tool.php"=>"Wi-Fi Client Tool");
$SYSTEM_SETTING["advanced_setting_menu"]=array("admin_user.php"=>"Admin User","https_ssl_cert.php"=>"HTTPS SSL Certificate","radius_ssl_cert.php"=>"Radius SSL Certificate","backup.php"=>"Backup","system_services.php"=>"System Services","system_log.php"=>"System Log");
$SYSTEM_SETTING["other_setting_menu"]=array("logout.php"=>"Logout");

//redirect message setting
$SYSTEM_SETTING["redirect_message"]["default"]["redirect_page"]='login.php';
$SYSTEM_SETTING["redirect_message"]["default"]["countdown_time"]='5';
$SYSTEM_SETTING["redirect_message"]["default"]["redirect_title"]='Incorrect Data!';
$SYSTEM_SETTING["redirect_message"]["default"]["redirect_message"]='You will be redirected to the login page in "+count+" seconds.';

$SYSTEM_SETTING["redirect_message"]["factory_reset"]["redirect_page"]='login.php';
$SYSTEM_SETTING["redirect_message"]["factory_reset"]["countdown_time"]='300';
$SYSTEM_SETTING["redirect_message"]["factory_reset"]["redirect_title"]='Factory reset is in progress, please wait.....';
$SYSTEM_SETTING["redirect_message"]["factory_reset"]["redirect_message"]='You will be redirected to the login page in "+count+" seconds.';

$SYSTEM_SETTING["redirect_message"]["https_ssl_cert_change"]["redirect_page"]='login.php';
$SYSTEM_SETTING["redirect_message"]["https_ssl_cert_change"]["countdown_time"]='30';
$SYSTEM_SETTING["redirect_message"]["https_ssl_cert_change"]["redirect_title"]='HTTPS SSL Certificate change is in progress, please wait.....';
$SYSTEM_SETTING["redirect_message"]["https_ssl_cert_change"]["redirect_message"]='You will be redirected to the login page in "+count+" seconds.';

$SYSTEM_SETTING["redirect_message"]["system_reboot"]["redirect_page"]='login.php';
$SYSTEM_SETTING["redirect_message"]["system_reboot"]["countdown_time"]='30';
$SYSTEM_SETTING["redirect_message"]["system_reboot"]["redirect_title"]='System restart is in progress, please wait.....';
$SYSTEM_SETTING["redirect_message"]["system_reboot"]["redirect_message"]='You will be redirected to the login page in "+count+" seconds.';

$SYSTEM_SETTING["redirect_message"]["system_restore"]["redirect_page"]='login.php';
$SYSTEM_SETTING["redirect_message"]["system_restore"]["countdown_time"]='120';
$SYSTEM_SETTING["redirect_message"]["system_restore"]["redirect_title"]='System restore is in progress, please wait.....';
$SYSTEM_SETTING["redirect_message"]["system_restore"]["redirect_message"]='You will be redirected to the login page in "+count+" seconds.';



?>
