<?php
require_once(dirname(__FILE__) . "/../initialize.php");
class User
{
private $userid="";

private $password="";
private $salt="";

private $password_changed=0;




public function GetUserID(){return $this->userid;}
public function SetUserID($id){$this->userid=$id;}


public function GetPassword(){return $this->password;}
public function SetPassword($p){$this->password=$p;$this->password_changed=1;}
public function GetSalt(){return $this->salt;}
//public function SetSalt($s){$this->salt=$s;}



function __construct() {


   }

//=========================================================================================
//get the user info from the sql result
public function GetUserInfoFromSQLResult($row)
{
	$this->userid=base64_decode($row['User_ID']);
  	$this->password=$row['Password'];
    $this->salt=$row['Salt'];
}

//=========================================================================================
//retrieve user info from sql query
private function GetUserInfoFromSQLQuery($query)
{

 global $CURRENT_DB;
 $result=$CURRENT_DB->DBSelectQuery($query);
 if($row = $result->fetchArray())
  {
 	$this->GetUserInfoFromSQLResult($row);
  }

}


//=========================================================================================
//generate a salt for password hash
private function GenerateSalt() {
	$length=13;

	$str='$6$'. (new Misc())->GenerateRandomString($length);

	return $str;
}


//=========================================================================================
//retrieve user info by id
public function GetUserInfoByUserID($id)
 {
 	global $SYSTEM_SETTING;
 	$query="SELECT * FROM " . $SYSTEM_SETTING["DB_User_Table"] . " WHERE User_ID='".base64_encode($id)."'";

 	$this->GetUserInfoFromSQLQuery($query);
 }


//=========================================================================================
//Check if the current user id already exist in the db
 private function CheckForExistingUserID()
 {
 	global $CURRENT_DB;
 	global $SYSTEM_SETTING;
 	$query="select * from " . $SYSTEM_SETTING["DB_User_Table"] . " where User_ID='".$this->userid."'";

 	$result=$CURRENT_DB->DBSelectQuery($query);
    if($row = $result->fetchArray())
     {
     	//return true if user name exist
 	  return true;
     }
     else
     {
     return false;
     }
 }


 //=========================================================================================
 //Encrypt the password
private function EncryptPassword()
 {


 	$this->password=(new Encryption())->EncryptPassword($this->password,$this->salt);
 }

 //=========================================================================================
 //create new user
 public function CreateNewUser()
 {



 	$this->EncodeData();
 	//check for duplicate user name
 	if($this->CheckForExistingUserID())
	{

	return false;
	}

 	$this->salt=$this->GenerateSalt();

 	$this->EncryptPassword();

 	global $SYSTEM_SETTING;
 	$query="insert into " . $SYSTEM_SETTING["DB_User_Table"] . " (User_ID,Password,Salt) values (".
 	"'" . $this->userid . "'," .
 	"'" . $this->password . "'," .
 	"'" . $this->salt . "')" ;

 	global $CURRENT_DB;
	$result=$CURRENT_DB->DBUpdateQuery($query);
	return $result;
 }

 //=========================================================================================
 //update user
 public function UpdateUser()
 {


 	$this->EncodeData();

 	if($this->password_changed)
 	{
 		$this->EncryptPassword();
 	}

 	global $SYSTEM_SETTING;

 	$query="update " . $SYSTEM_SETTING["DB_User_Table"] . " set " .
 	"User_ID='" . $this->userid . "', " .
 	"Password='" . $this->password . "' " .
 	"where User_ID='".$this->userid."'";


 	global $CURRENT_DB;
	$result=$CURRENT_DB->DBUpdateQuery($query);
	return $result;

 }

//=========================================================================================
private function EncodeData()
{
	$this->userid=base64_encode($this->userid);


}
//=========================================================================================

//=========================================================================================

}
?>
