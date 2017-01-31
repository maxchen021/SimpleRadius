<?php
require_once(dirname(__FILE__) . "/../initialize.php");
class Misc
{
	//=========================================================================================
	//generate a random string with given length
	public function GenerateRandomString($length) {
	//$length=13;
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

	$str='';
	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}

	return $str;
	}


	


	//=========================================================================================
	public function EscapeSingleQuote($str)
	{
		return str_replace("'","\'",$str);
	}
}
?>
