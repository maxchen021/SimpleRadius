<?php
require_once(dirname(__FILE__) . "/../initialize.php");
class Encryption
{

	//=========================================================================================
		public function Encrypt($data_to_encrypt) {

			global $SYSTEM_SETTING;
			$encryption_key=$SYSTEM_SETTING["encryption_key"];
			if (isset($encryption_key)) {
				if (isset($data_to_encrypt) && !empty($data_to_encrypt)) {
					return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $encryption_key, $data_to_encrypt, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
				} else {
					throw new Exception('No data to be encrypted specified.');
				}
			} else {
				throw new Exception('No encryption key specified.');
			}
		}

		public function Decrypt($data_to_decrypt) {
			global $SYSTEM_SETTING;
			$encryption_key=$SYSTEM_SETTING["encryption_key"];
			if (isset($encryption_key)) {
				if (isset($data_to_decrypt) && !empty($data_to_decrypt)) {
					return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $encryption_key, base64_decode($data_to_decrypt), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
				} else {
					throw new Exception('No data to be decrypted specified.');
				}
			} else {
				throw new Exception('No encryption key specified.');
			}
		}
		
	//=========================================================================================
	//encrypt the given password with the given salt
	public function EncryptPassword($password,$salt)
	{

		global $SYSTEM_SETTING;
		$password=crypt($password,$SYSTEM_SETTING["system_md5_salt"]);

 		$password=crypt($password,$salt);

 		$password=crypt($password,$SYSTEM_SETTING["system_sha512_salt"]);

 		return $password;
	}
	
	public function GenerateEncryptionConfig()
	{
		global $SYSTEM_SETTING;
		global $CURRENT_DB;
		
		$system_md5_salt = '$1$' . GenerateRandomString(8);
      	$system_sha512_salt = '$6$' . GenerateRandomString(13);
      	$encryption_key = GenerateRandomString(32);
		  
		$query="update system_setting set value='" . $system_md5_salt .  "' where system_setting='system_md5_salt'" ;
		$result=$CURRENT_DB->DBUpdateQuery($query);
		
		$query="update system_setting set value='" . $system_sha512_salt .  "' where system_setting='system_sha512_salt'" ;
		$result=$CURRENT_DB->DBUpdateQuery($query);
		
		$query="update system_setting set value='" . $encryption_key .  "' where system_setting='encryption_key'" ;
		$result=$CURRENT_DB->DBUpdateQuery($query);
	}
	
	public function GetEncryptionConfig()
	{
		global $SYSTEM_SETTING;
		global $CURRENT_DB;
		
		$query="select * from system_setting where system_setting='system_md5_salt'";

		$result=$CURRENT_DB->DBSelectQuery($query);

		if($row=$result->fetchArray())
		{
			if( isset($row['value']) && strcmp($row['value'],"")!=0 ) {
			  $SYSTEM_SETTING["system_md5_salt"]= $row['value'];
			}
		}
		
		$query="select * from system_setting where system_setting='system_sha512_salt'";

		$result=$CURRENT_DB->DBSelectQuery($query);

		if($row=$result->fetchArray())
		{
			if( isset($row['value']) && strcmp($row['value'],"")!=0 ) {
			  $SYSTEM_SETTING["system_sha512_salt"]= $row['value'];
			}
		}
		
		$query="select * from system_setting where system_setting='encryption_key'";

		$result=$CURRENT_DB->DBSelectQuery($query);

		if($row=$result->fetchArray())
		{
			if( isset($row['value']) && strcmp($row['value'],"")!=0 ) {
			  $SYSTEM_SETTING["encryption_key"]= $row['value'];
			}
		}
		
		$query="select * from system_setting where system_setting='default_config_file_encryption_key'";

		$result=$CURRENT_DB->DBSelectQuery($query);

		if($row=$result->fetchArray())
		{
			if( isset($row['value']) && strcmp($row['value'],"")!=0 ) {
			  $SYSTEM_SETTING["config_file_encryption_key"]= $row['value'];
			}
		}

		
	}


}
?>
