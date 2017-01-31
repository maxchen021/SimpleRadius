#!/usr/bin/php

<?php


$simple_radius_config['default_admin_username'] = "admin";
$simple_radius_config['default_admin_password'] = "password";

$simple_radius_config['main_directory']='/etc/SimpleRadius';

$simple_radius_config['config_directory'] = $simple_radius_config['main_directory'] . "/configs";
$simple_radius_config["factory_default_config_directory"] = $simple_radius_config['main_directory'] . "/factory_default";
$simple_radius_config['freeradius_config_directory'] = $simple_radius_config['config_directory'] . "/freeradius";
$simple_radius_config['freeradius_user_config'] = $simple_radius_config['freeradius_config_directory'] . "/users";
$simple_radius_config['freeradius_router_config'] = $simple_radius_config['freeradius_config_directory'] . "/clients.conf";

$simple_radius_config['https_ssl_cert_directory'] = $simple_radius_config['config_directory'] . "/https_ssl_certs";
$simple_radius_config['https_ssl_cert_public_key'] = $simple_radius_config['https_ssl_cert_directory'] . "/server.pem";
$simple_radius_config['https_ssl_cert_private_key'] = $simple_radius_config['https_ssl_cert_directory'] . "/server.key";
$simple_radius_config['https_ssl_cert_ca_cert'] = $simple_radius_config['https_ssl_cert_directory'] . "/ca.pem";


$simple_radius_config['radius_ssl_cert_directory'] = $simple_radius_config['config_directory'] . "/radius_ssl_certs";
$simple_radius_config['radius_ssl_cert_public_key'] = $simple_radius_config['radius_ssl_cert_directory'] . "/server.pem";
$simple_radius_config['radius_ssl_cert_private_key'] = $simple_radius_config['radius_ssl_cert_directory'] . "/server.key";
$simple_radius_config['radius_ssl_cert_ca_cert'] = $simple_radius_config['radius_ssl_cert_directory'] . "/ca.pem";
$simple_radius_config['radius_dh_parameters'] = $simple_radius_config['radius_ssl_cert_directory'] . "/dh";


if ( is_file('/etc/arch-release') )
{
	$freeradius_config['config_directory']="/etc/freeradius";
	$freeradius_config['freeradius_user']="radiusd";
	$freeradius_config['freeradius_group']="radiusd";
	$freeradius_config['pid_file']="/var/run/radiusd/radiusd.pid";
	$freeradius_config['restart_service_command']="if [ -f " . $freeradius_config['pid_file'] . " ]; then kill -9 `cat " . $freeradius_config['pid_file'] . "`; else pkill -9 radiusd ; fi; /usr/bin/radiusd -d /etc/raddb";
	$freeradius_config['reload_service_command']='kill -1 `cat ' . $freeradius_config['pid_file'] . '`';
	$freeradius_config['validate_config_command']='radiusd -XC';

	$freeradius_config['user_config']=$freeradius_config['config_directory'] . "/mods-config/files/authorize";

	$apache_config['config_directory']="/etc/httpd";
	$apache_config['apache_user']="http";
	$apache_config['apache_group']="http";
	$apache_config['restart_service_command']="apachectl restart";
	$apache_config['reload_service_command']="apachectl graceful";
	$apache_config['validate_config_command']='apachectl -t';

}



$freeradius_config['router_config']=$freeradius_config['config_directory'] . "/clients.conf";
$freeradius_config['ssl_cert_directory']=$freeradius_config['config_directory'] . "/certs";
$freeradius_config['ssl_cert_public_key']=$freeradius_config['ssl_cert_directory'] . "/server.pem";
$freeradius_config['ssl_cert_private_key']=$freeradius_config['ssl_cert_directory'] . "/server.key";
$freeradius_config['ssl_cert_ca_cert']=$freeradius_config['ssl_cert_directory'] . "/ca.pem";
$freeradius_config['dh_parameters']=$freeradius_config['ssl_cert_directory'] . "/dh";
$freeradius_config["radius_log_file"]="/var/log/radius/radius.log";

$apache_config['ssl_cert_directory']=$apache_config['config_directory'] . "/certs";
$apache_config['ssl_cert_public_key']=$apache_config['ssl_cert_directory'] . "/server.pem";
$apache_config['ssl_cert_private_key']=$apache_config['ssl_cert_directory'] . "/server.key";
$apache_config['ssl_cert_ca_cert']=$apache_config['ssl_cert_directory'] . "/ca.pem";
$apache_config['public_html_dir']="/var/www/SimpleRadius";
$apache_config['php_encryption_config_file']=$apache_config['public_html_dir'] . "/encryption_config.php";


$SCRIPT_NAME="simple_radius";


#----------------------------------------------------------------------------
# Validate Arguments
#----------------------------------------------------------------------------

$options=$argv[1];

switch($options) {
	case "Update_Radius_User_Config":
		Update_Radius_User_Config();
		break;
	case "Update_Radius_Router_Config":
		Update_Radius_Router_Config();
	  break;
	case "Update_Radius_SSL_Certs":
		Update_Radius_SSL_Certs();
	  break;
	case "Update_Apache_SSL_Certs":
		Update_Apache_SSL_Certs();
	  break;
	case "Validate_FreeRadius_Configs":
		Validate_FreeRadius_Configs();
	  break;
	case "Reload_FreeRadius_Config":
		Reload_FreeRadius_Config();
	  break;
	case "Restart_FreeRadius_Service":
		Restart_FreeRadius_Service();
	  break;
	case "Validate_Apache_Configs":
		Validate_Apache_Configs();
	  break;
	case "Reload_Apache_Config":
		Reload_Apache_Config();
	  break;
	case "Restart_Apache_Service":
		Restart_Apache_Service();
	  break;
	case "Generate_Encryption_Config":
	  Generate_Encryption_Config();
	  break;
	case "Create_Admin_User":
	  Create_Admin_User();
	  break;
	case "System_Restore":
	  System_Restore();
	  break;
	case "Restart_System":
	  Restart_System();
	  break;
	case "Factory_Reset":
	  Factory_Reset();
	  break;
	case "Get_FreeRadius_Log_File":
	  Get_FreeRadius_Log_File();
	  break;
  default:
    echo "usage: ./$SCRIPT_NAME.php options\n";
		echo "your options: " . $options . "\n";
    exit;
}



#----------------------------------------------------------------------------
#----------------------------------------------------------------------------
function Update_Radius_User_Config()
{
  global $simple_radius_config, $freeradius_config, $apache_config;
  $command="mv " . $simple_radius_config['freeradius_user_config'] . " " . $freeradius_config['user_config'];
  #echo $command . "\n";
  echo `$command`;

  #fix permission, radius conf and apache conf are owned by root to ensure it wouldn't get overriden by radius and apache program itself
  $command="chown root:" . $freeradius_config['freeradius_group'] . " " . $freeradius_config['user_config'];
  echo `$command`;
  $command="chmod 640 " . $freeradius_config['user_config'];
  echo `$command`;
}
#----------------------------------------------------------------------------
function Update_Radius_Router_Config()
{
  global $simple_radius_config, $freeradius_config, $apache_config;
  $command="mv " . $simple_radius_config['freeradius_router_config'] . " " . $freeradius_config['router_config'];
  #echo $command . "\n";
  echo `$command`;

  #fix permission
  $command="chown root:" . $freeradius_config['freeradius_group'] . " " . $freeradius_config['router_config'];
  echo `$command`;
  $command="chmod 640 " . $freeradius_config['router_config'];
  echo `$command`;
}
#----------------------------------------------------------------------------
function Update_Radius_SSL_Certs()
{
  global $simple_radius_config, $freeradius_config, $apache_config;
	CreateSSLCertDirectories();
  $command="mv " . $simple_radius_config['radius_ssl_cert_public_key'] . " " . $freeradius_config['ssl_cert_public_key'];
  echo `$command`;
  $command="mv " . $simple_radius_config['radius_ssl_cert_private_key'] . " " . $freeradius_config['ssl_cert_private_key'];
  echo `$command`;
  $command="mv " . $simple_radius_config['radius_ssl_cert_ca_cert'] . " " . $freeradius_config['ssl_cert_ca_cert'];
  echo `$command`;
	$command="mv " . $simple_radius_config['radius_dh_parameters'] . " " . $freeradius_config['dh_parameters'];
  echo `$command`;

  #fix permission
  $command="chown root:" . $freeradius_config['freeradius_group'] . " " . $freeradius_config['ssl_cert_public_key'];
  echo `$command`;
  $command="chmod 640 " . $freeradius_config['ssl_cert_public_key'];
  echo `$command`;
  $command="chown root:" . $freeradius_config['freeradius_group'] . " " . $freeradius_config['ssl_cert_private_key'];
  echo `$command`;
  $command="chmod 640 " . $freeradius_config['ssl_cert_private_key'];
  echo `$command`;
  $command="chown root:" . $freeradius_config['freeradius_group'] . " " . $freeradius_config['ssl_cert_ca_cert'];
  echo `$command`;
	$command="chown root:" . $freeradius_config['freeradius_group'] . " " . $freeradius_config['dh_parameters'];
  echo `$command`;
  $command="chmod 640 " . $freeradius_config['ssl_cert_ca_cert'];
  echo `$command`;
}
#----------------------------------------------------------------------------

#----------------------------------------------------------------------------
function Update_Apache_SSL_Certs()
{
  global $simple_radius_config, $freeradius_config, $apache_config;
	CreateSSLCertDirectories();
  $command="mv " . $simple_radius_config['https_ssl_cert_public_key'] . " " . $apache_config['ssl_cert_public_key'];
  echo `$command`;
  $command="mv " . $simple_radius_config['https_ssl_cert_private_key'] . " " . $apache_config['ssl_cert_private_key'];
  echo `$command`;
  $command="mv " . $simple_radius_config['https_ssl_cert_ca_cert'] . " " . $apache_config['ssl_cert_ca_cert'];
  echo `$command`;

  #fix permission
  $command="chown root:" . $apache_config['apache_group'] . " " . $apache_config['ssl_cert_public_key'];
  echo `$command`;
  $command="chmod 640 " . $apache_config['ssl_cert_public_key'];
  echo `$command`;
  $command="chown root:" . $apache_config['apache_group'] . " " . $apache_config['ssl_cert_private_key'];
  echo `$command`;
  $command="chmod 640 " . $apache_config['ssl_cert_private_key'];
  echo `$command`;
  $command="chown root:" . $apache_config['apache_group'] . " " . $apache_config['ssl_cert_ca_cert'];
  echo `$command`;
  $command="chmod 640 " . $apache_config['ssl_cert_ca_cert'];
  echo `$command`;
}
#----------------------------------------------------------------------------

#----------------------------------------------------------------------------
function Validate_FreeRadius_Configs()
{
  global $simple_radius_config, $freeradius_config, $apache_config;
	system($freeradius_config['validate_config_command'], $exit_code);
  echo $exit_code;
	return $exit_code;
}
#----------------------------------------------------------------------------
function Reload_FreeRadius_Config()
{
  global $simple_radius_config, $freeradius_config, $apache_config;
	if(Validate_FreeRadius_Configs()==0)
	{
		if( is_file($freeradius_config['pid_file']) )
		{
			$command=$freeradius_config['reload_service_command'];
			echo `$command`;
		}
	}
}
#----------------------------------------------------------------------------
function Restart_FreeRadius_Service()
{
	global $simple_radius_config, $freeradius_config, $apache_config;
	if(Validate_FreeRadius_Configs()==0)
	{
		$command=$freeradius_config['restart_service_command'];
		echo `$command`;
	}
}
#----------------------------------------------------------------------------

#----------------------------------------------------------------------------
function Validate_Apache_Configs()
{
	global $simple_radius_config, $freeradius_config, $apache_config;
	system($apache_config['validate_config_command'], $exit_code);
  echo $exit_code;
	return $exit_code;
}
#----------------------------------------------------------------------------
function Reload_Apache_Config()
{
	global $simple_radius_config, $freeradius_config, $apache_config;
	if(Validate_Apache_Configs()==0)
	{
		$command=$apache_config['reload_service_command'];
		echo `$command`;
	}
}
#----------------------------------------------------------------------------
function Restart_Apache_Service()
{
	global $simple_radius_config, $freeradius_config, $apache_config;
	if(Validate_Apache_Configs()==0)
	{
		$command=$apache_config['restart_service_command'];
		echo `$command`;
	}
}
#----------------------------------------------------------------------------

#----------------------------------------------------------------------------
function Restart_System()
{
		system('sleep 10');
		Restart_FreeRadius_Service();
		Restart_Apache_Service();		
}
#----------------------------------------------------------------------------

#----------------------------------------------------------------------------
function Generate_Encryption_Config()
{
	  //need to change dir in order to work
		$current_dir=getcwd();
		global $simple_radius_config, $apache_config;
		chdir($apache_config['public_html_dir']);
		require($apache_config['public_html_dir'] . "/initialize.php");
		Encryption::GenerateEncryptionConfig();
		chdir($current_dir);

}

#----------------------------------------------------------------------------
function Create_Admin_User()
{
	//need to change dir in order to work
	$current_dir=getcwd();
  global $simple_radius_config, $apache_config;
	chdir($apache_config['public_html_dir']);
	require($apache_config['public_html_dir'] . "/initialize.php");
	$user=new User();
	$user->SetUserID($simple_radius_config['default_admin_username']);
  $user->SetPassword($simple_radius_config['default_admin_password']);
	$result=$user->CreateNewUser();
	if($result)
  {
		echo "Admin user created\n";
	}
	else {
		die("There's a problem creating admin user! " . $result . "\n");
	}
	chdir($current_dir);
}
#----------------------------------------------------------------------------

#----------------------------------------------------------------------------

#----------------------------------------------------------------------------
function System_Restore()
{
	global $simple_radius_config, $freeradius_config, $apache_config;

  //Update_Radius_SSL_Certs();
  //Update_Apache_SSL_Certs();

	#fix permission
  $command="chown -R " . $apache_config['apache_user'] . ":" . $apache_config['apache_group'] . " " . $simple_radius_config['main_directory'];
  echo `$command`;

  Restart_System();
	
}
#----------------------------------------------------------------------------

#----------------------------------------------------------------------------
function Factory_Reset()
{
	global $simple_radius_config, $freeradius_config, $apache_config;
	$command="rm -rf " . $simple_radius_config['config_directory'] . "/*";
	echo `$command`;

	$command="cp -r -f " . $simple_radius_config["factory_default_config_directory"] . "/* " . $simple_radius_config['main_directory'] . "/";
	echo `$command`;

  Generate_Encryption_Config();
	Create_Admin_User();

  $current_dir=getcwd();
	chdir($apache_config['public_html_dir']);
	require($apache_config['public_html_dir'] . "/initialize.php");
	$ssl_certs = new SSLCerts();
  $ssl_certs->SetSSLCertType('https');
	$ssl_certs->Generate_New_SSL_Certs();
	$ssl_certs->SaveSSLCertsToFile();
	$ssl_certs->SetSSLCertType('radius');
	$ssl_certs->Generate_New_SSL_Certs();
	//$ssl_certs->Generate_New_DH_Parameters();
	$ssl_certs->SaveSSLCertsToFile();
	chdir($current_dir);
	

  //Update_Radius_SSL_Certs();
  //Update_Apache_SSL_Certs();

	Update_Radius_Router_Config();
	Update_Radius_User_Config();

	#fix permission
  $command="chown -R " . $apache_config['apache_user'] . ":" . $apache_config['apache_group'] . " " . $simple_radius_config['main_directory'];
  echo `$command`;

  Restart_System();
}

#----------------------------------------------------------------------------

function Get_FreeRadius_Log_File()
{
	global $simple_radius_config, $freeradius_config, $apache_config;
	$command="tail -10000 " . $freeradius_config["radius_log_file"];
	echo `$command`;
}

#----------------------------------------------------------------------------



############################################################################################################################################
#Begin Internal Function
############################################################################################################################################
function GenerateRandomString($length) {

	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

	$str='';
	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}

	return $str;
}

function CreateSSLCertDirectories()
{
	global $simple_radius_config, $freeradius_config, $apache_config;
	if ( !is_dir($simple_radius_config['radius_ssl_cert_directory']) )
	{
		mkdir($simple_radius_config['radius_ssl_cert_directory'], 0755, true);
	}

	if ( !is_dir($simple_radius_config['https_ssl_cert_directory']) )
	{
		mkdir($simple_radius_config['https_ssl_cert_directory'], 0755, true);
	}

	if ( !is_dir($freeradius_config['ssl_cert_directory']) )
	{
		mkdir($freeradius_config['ssl_cert_directory'], 0755, true);
	}

	if ( !is_dir($apache_config['ssl_cert_directory']) )
	{
		mkdir($apache_config['ssl_cert_directory'], 0755, true);
	}
}
############################################################################################################################################
#End Internal Function
############################################################################################################################################

?>
