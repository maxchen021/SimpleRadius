<?php
require_once(dirname(__FILE__) . "/../initialize.php");
class FreeRadius
{


	//=========================================================================================

	public function CreateClientConfig()
	{
		global $CURRENT_DB;
		global $SYSTEM_SETTING;

		$query="select * from wireless_routers";

  		$result=$CURRENT_DB->DBSelectQuery($query);

  		$filename=$SYSTEM_SETTING["freeradius_config_directory"] . "/clients.conf";

  		$filehandle = fopen($filename, 'w') or die("can't open file");

  		$count=0;
	    while($row = $result->fetchArray())
	     {

	       $str = "client \"" . base64_decode($row['router_name']) . "\" { \n\n" ;

	       $router_ip=base64_decode($row['router_ip']);
	       //if(substr_count($router_ip,".")>1 && substr_count($router_ip,":")==0 )
	       if(filter_var($router_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
	       {
	       	$str.="ipaddr = " . $router_ip . "\n\n";
	       }
	       else
	       {
	       	$str.="ipv6addr = " . $router_ip ."\n\n";
	       }


	       $str.= "secret = " . Encryption::Decrypt($row['radius_secret']) . " \n\n"
	       //. "shortname = " . base64_decode($row['router_ip']) . "\n\n"
	       . "} \n\n" ;

	        fwrite($filehandle,$str);

	        $count++;
	     }

	     fclose($filehandle);

	     SimpleRadius::Run_Command("Update_Radius_Router_Config");
	     SimpleRadius::Run_Command("Restart_FreeRadius_Service");
	}

	//=========================================================================================
	public function CreateUserConfig()
	{
		global $CURRENT_DB;
		global $SYSTEM_SETTING;

		$query="select * from radius_users";

  		$result=$CURRENT_DB->DBSelectQuery($query);

  		$filename=$SYSTEM_SETTING["freeradius_config_directory"] . "/users";

  		$filehandle = fopen($filename, 'w') or die("can't open file");

	    while($row = $result->fetchArray())
	     {

	       $str = '"' . base64_decode($row['username']) . '"'
	       . ' NT-Password := "'
	       . FreeRadius::GetNTHash(Encryption::Decrypt($row['password']))
	       . '"'. "\n\n" ;

	        fwrite($filehandle,$str);
	     }

	     fclose($filehandle);

	     SimpleRadius::Run_Command("Update_Radius_User_Config");
	     SimpleRadius::Run_Command("Restart_FreeRadius_Service");
	}
	//=========================================================================================

	//=========================================================================================
	//convert plain text string to nt hash
	public function GetNTHash($str)
	{
		if( isset($str) && strcmp($str,"")!=0 ) {		
			$str="'" . Misc::EscapeSingleQuote($str) . "'";
			$result=`smbencrypt $str`;
			$result=trim($result);
			$temp=explode("	",$result);
			return $temp[1];
		}
		else {
			return "";
		}
	}
	//=========================================================================================

}


?>
