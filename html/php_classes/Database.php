<?php
require_once(dirname(__FILE__) . "/../initialize.php");
class Database
{



	public function EscapeString($str)
	{
		return SQLite3::escapeString($str);
	}

	public function DBSelectQuery($query)
	{

		global $SYSTEM_SETTING;

		$db = new SQLite3($SYSTEM_SETTING["DB_File"]);

		$results = $db->query($query);

		//$db->close();

		return $results;

	}

	public function DBUpdateQuery($query)
	{

		global $SYSTEM_SETTING;

		$db = new SQLite3($SYSTEM_SETTING["DB_File"]);

		$results = $db->exec($query);

		$db->close();

		return $results;

	}

	public function GetCount($table_name)
	{
		$query="select count(*) from " . $table_name;
		$result=$this->DBSelectQuery($query);
		if($row=$result->fetchArray())
		{
			return $row['count(*0)'];
		}
		else
		{
			return 0;
		}
	}



	public function ResetCount($table_name)
	{
		$query="UPDATE sqlite_sequence SET seq=0  WHERE name='" . $table_name . "'";
		return $this->DBUpdateQuery($query);
	}
	
	public function GetDBVersion()
	{
		$query="select * from system_setting where system_setting='version'";

		$result=$this->DBSelectQuery($query);

		if($row=$result->fetchArray())
		{
			if( isset($row['value']) && strcmp($row['value'],"")!=0 ) {
			  return floatval($row['value']);
			}
		}
		return 0;
	}

	public function UpgradeDB()
	{
		if($this->GetDBVersion()==1) {
			$this->UpgradeDB1To2();
		}
	}

	public function UpgradeDB1To2()
	{
		global $SYSTEM_SETTING;
		$db_upgrade_script_filename = $SYSTEM_SETTING["factory_default_config_directory"] . "/db_upgrade_scripts/v2.sql";
		$query = file_get_contents($db_upgrade_script_filename);
		$this->DBUpdateQuery($query);
	}
}


?>
