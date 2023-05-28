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
	       $str.="ipaddr = " . $router_ip . "\n\n";

	       $str.= "secret = " . (new Encryption())->Decrypt($row['radius_secret']) . " \n\n";
	       //. "shortname = " . base64_decode($row['router_ip']) . "\n\n"

		   $str.= base64_decode($row['additional_settings']) . "\n\n"
	       . "} \n\n" ;

	        fwrite($filehandle,$str);

	        $count++;
	     }

	     fclose($filehandle);

	     (new SimpleRadius())->Run_Command("Update_Radius_Router_Config");
	     (new SimpleRadius())->Run_Command("Restart_FreeRadius_Service");
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
		   $username = base64_decode($row['username']);
	       $str = '"' . $username . '"'
	       . ' NT-Password := "'
	       . (new FreeRadius())->GetNTHash((new Encryption())->Decrypt($row['password'])). '"'
		   . "\n   ". $this->createEmptySpaces($username) . base64_decode($row['additional_settings'])
		   . "\n\n" ;

	        fwrite($filehandle,$str);
	     }

	     fclose($filehandle);

	     (new SimpleRadius())->Run_Command("Update_Radius_User_Config");
	     (new SimpleRadius())->Run_Command("Restart_FreeRadius_Service");
	}
	//=========================================================================================
	public function createEmptySpaces($inputString) {
		$length = strlen($inputString);
		$emptySpaces = str_repeat(" ", $length);
		return $emptySpaces;
	}
	//=========================================================================================
	//convert plain text string to nt hash
	public function GetNTHash($str)
	{
		if( isset($str) && strcmp($str,"")!=0 ) {		
			$str="'" . (new Misc())->EscapeSingleQuote($str) . "'";
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
	public function CreateSiteConfigs()
	{
		global $CURRENT_DB;
		global $SYSTEM_SETTING;

		$query="select * from freeradius_configs";

  		$result=$CURRENT_DB->DBSelectQuery($query);

		while($row = $result->fetchArray())
		{
			$filename=$SYSTEM_SETTING["freeradius_config_directory"] . "/site/" . $row['config_name'];

			$filehandle = fopen($filename, 'w') or die("can't open file");
			$value = base64_decode($row['value']);

			fwrite($filehandle,$value);
			fclose($filehandle);
		}

		(new SimpleRadius())->Run_Command("Update_Radius_Site_Configs");
		(new SimpleRadius())->Run_Command("Restart_FreeRadius_Service");
	}
	//=========================================================================================
	public function SaveConfigToDB($config_name,$config_value)
	{
		global $CURRENT_DB;
		global $SYSTEM_SETTING;

		$query="update freeradius_configs set value='" . base64_encode($config_value) .  "' where config_name='" . $config_name . "'" ;
		$result=$CURRENT_DB->DBUpdateQuery($query);
	}
	
	//=========================================================================================
	public function GetConfigFromDB($config_name)
	{
		global $CURRENT_DB;
		global $SYSTEM_SETTING;
		
		$query="select * from freeradius_configs where config_name='" . $config_name . "'";

		$result=$CURRENT_DB->DBSelectQuery($query);

		$value="";
		if($row=$result->fetchArray())
		{
			if( isset($row['value']) && strcmp($row['value'],"")!=0 ) {
			  $value=base64_decode($row['value']);
			}
		}
		return $value;
	}
}


?>
