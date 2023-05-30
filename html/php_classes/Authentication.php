<?php
require_once(dirname(__FILE__) . "/../initialize.php");

class Authentication
{


	//=========================================================================================
	//return true if username and password is correct
	public function AuthenticateUser($userid,$password)
	{
		$user=new User();
		$user->GetUserInfoByUserID($userid);
		$uid=$user->GetUserID();

		if(is_null($uid) || $uid=="")
		{

			return false;
		}


		$salt=$user->GetSalt();



		$password=(new Encryption())->EncryptPassword($password,$salt);

		//check if password hash is the same
		if(strcmp($password,$user->GetPassword())!=0)
		{
			return false;
		}

		return true;

	}

	//=========================================================================================
	public function CreateNewSession($userid)
	{
		global $SYSTEM_SETTING;

		$this->DeleteCurrentSession();
		$this->DeleteExpiredSession();

		$str=(new Misc())->GenerateRandomString(20);
		$session_id=crypt($str,$SYSTEM_SETTING["system_sha512_salt"]);

		//make sure the new session id does not already existed
		while($this->CheckForExistingSessionID($session_id))
		{
		$str=(new Misc())->GenerateRandomString(20);
		$session_id=crypt($str,$SYSTEM_SETTING["system_sha512_salt"]);
		}

		$userid=base64_encode($userid);
		$client_ip=base64_encode(getenv("REMOTE_ADDR"));
		$client_user_agent=base64_encode(getenv("HTTP_USER_AGENT"));
		$session_expiration_time=time()+rand($SYSTEM_SETTING["min_session_time"],$SYSTEM_SETTING["max_session_time"]);




	if(!setcookie("SESSIONID",$session_id,$session_expiration_time,"/"))
	{

	return false;
	}


	//store the normal session id on the client side and store the base64 version of the session id on the db to prevent possible sql injection
	$session_id=base64_encode($session_id);

		$query="INSERT INTO ".$SYSTEM_SETTING["DB_Session_Table"]." (User_ID,Client_IP,Client_User_Agent,Session_ID,Expiration_Time) VALUES (" .
		"'" . $userid . "'," .
		"'" . $client_ip . "'," .
		"'" . $client_user_agent . "'," .
		"'" . $session_id . "'," .
			  $session_expiration_time . ")";



	global $CURRENT_DB;
	$result=$CURRENT_DB->DBUpdateQuery($query);
	return $result;


	}

	//=========================================================================================
	//return false if the current session is invalid
	public function CheckUserSession()
	{
		$this->DeleteExpiredSession();
		if(!isset($_COOKIE['SESSIONID']))
		{
			return false;
		}


		$session_id=base64_encode($_COOKIE['SESSIONID']);
		$client_ip=base64_encode(getenv("REMOTE_ADDR"));
		$client_user_agent=base64_encode(getenv("HTTP_USER_AGENT"));

		global $SYSTEM_SETTING;
		$user_table=$SYSTEM_SETTING["DB_User_Table"];
		$session_table=$SYSTEM_SETTING["DB_Session_Table"];

		$query="SELECT ". $session_table . '.User_ID,'.$session_table.'.Expiration_Time FROM '.$session_table.
		 ' JOIN '. $user_table.' ON '. $session_table . '.User_ID='.$user_table . '.User_ID WHERE ' .
		$session_table.".Client_IP='" . $client_ip ."' AND " .
		$session_table.".Client_User_Agent='" . $client_user_agent . "' AND " .
		$session_table.".Session_ID='" . $session_id . "'" ;



		global $CURRENT_DB;
	 $result=$CURRENT_DB->DBSelectQuery($query);
	 $row = $result->fetchArray();

	 if(!isset($row) || is_null($row) || $row['User_ID']=="")
	 {
	 	return false;
	 }

	 if(time()>=$row['Expiration_Time'])
	 {
	 	return false;
	 }



	 if($row['Expiration_Time']-time()<=rand($SYSTEM_SETTING["min_session_renewal_time"],$SYSTEM_SETTING["max_session_renewal_time"]))
	 {

	 	$this->ExtendSession($session_id);
	 }

	 return true;




	}

	//=========================================================================================
	//Given a session id, check the db to see if it already exist or not
	private function CheckForExistingSessionID($session_id)
	{
		global $SYSTEM_SETTING;
		$session_id=base64_encode($session_id);
		$query="SELECT * FROM ".$SYSTEM_SETTING["DB_Session_Table"]." WHERE Session_ID='" . $session_id . "'";

		global $CURRENT_DB;
	 	$result=$CURRENT_DB->DBSelectQuery($query);

	    if($row = $result->fetchArray())
	     {
	     	//return true if session id exist
	 	  return true;
	     }
	     else
	     {
	     return false;
	     }
	}

	//=========================================================================================
	//Delete Expired Session
	private function DeleteExpiredSession()
	{
		global $SYSTEM_SETTING;
		$query="DELETE FROM ".$SYSTEM_SETTING["DB_Session_Table"]." WHERE Expiration_Time < " . time();
		global $CURRENT_DB;
	 	$result=$CURRENT_DB->DBUpdateQuery($query);
	 	return $result;
	}

	//=========================================================================================
	//delete the existing session from the db based on the session id stored on the user's browser cookie
	public function DeleteCurrentSession()
	{
		if(!isset($_COOKIE['SESSIONID']))
		{
			return true;
		}
		//$userid=base64_encode($userid);
		$client_ip=base64_encode(getenv("REMOTE_ADDR"));
		$client_user_agent=base64_encode(getenv("HTTP_USER_AGENT"));
		$session_id=base64_encode($_COOKIE['SESSIONID']);

		global $SYSTEM_SETTING;

		$query="DELETE FROM ".$SYSTEM_SETTING["DB_Session_Table"]." WHERE " .
		//"User_ID='" . $userid . "' AND " .
		"Client_IP='" . $client_ip . "' AND " .
		"Client_User_Agent='" . $client_user_agent . "' AND " .
		"Session_ID='" . $session_id . "'";

		global $CURRENT_DB;
	 	$result=$CURRENT_DB->DBUpdateQuery($query);

	 	setcookie("SESSIONID","",time()-3600,"/");

	 	return $result;

	}

	//=========================================================================================
	//get the current userid from the db based on the session id stored on the user's browser cookie
	public function GetUserIDFromCurrentSession()
	{
		if(!isset($_COOKIE['SESSIONID']))
		{
			return "";
		}

		$client_ip=base64_encode(getenv("REMOTE_ADDR"));
		$client_user_agent=base64_encode(getenv("HTTP_USER_AGENT"));
		$session_id=base64_encode($_COOKIE['SESSIONID']);

		global $SYSTEM_SETTING;

		$query="SELECT User_ID FROM ".$SYSTEM_SETTING["DB_Session_Table"]." WHERE " .
		//"User_ID='" . $userid . "' AND " .
		"Client_IP='" . $client_ip . "' AND " .
		"Client_User_Agent='" . $client_user_agent . "' AND " .
		"Session_ID='" . $session_id . "'";

		global $CURRENT_DB;
	 	$result=$CURRENT_DB->DBSelectQuery($query);

	 	if($row=$result->fetchArray())
	 	{
	 		$userid=base64_decode($row['User_ID']);

	 	}
	 	else
	 	{
	 		return "";
	 	}

	 	return $userid;
	}

	//=========================================================================================
	//Extend the current session by generating a new session id
	private function ExtendSession($session_id)
	{
		global $SYSTEM_SETTING;

		$str=(new Misc())->GenerateRandomString(20);
		$new_session_id=crypt($str,$SYSTEM_SETTING["system_sha512_salt"]);

		//make sure the new session id does not already existed
		while($this->CheckForExistingSessionID($new_session_id))
		{
		$str=(new Misc())->GenerateRandomString(20);
		$new_session_id=crypt($str,$SYSTEM_SETTING["system_sha512_salt"]);
		}


			//generate a random session expiration time between 30 min to 90 min
		$session_expiration_time=time()+rand(1800,5400);

		if(!setcookie("SESSIONID",$new_session_id,$session_expiration_time,"/"))
		{

		return false;
		}

		//store the normal session id on the client side and store the base64 version of the session id on the db to prevent possible sql injection
		$new_session_id=base64_encode($new_session_id);


		global $SYSTEM_SETTING;
		$query="UPDATE ".$SYSTEM_SETTING["DB_Session_Table"]." set " .
		"Session_ID='" . $new_session_id . "'," .
		"Expiration_Time=" . $session_expiration_time .
		" WHERE Session_ID='" . $session_id . "'";


		global $CURRENT_DB;
	 	$result=$CURRENT_DB->DBUpdateQuery($query);

	 	return $result;

	}
	//=========================================================================================

}
?>
