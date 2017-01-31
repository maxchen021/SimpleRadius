<?php
require_once("initialize.php");




$auth=new Authentication();
$auth->DeleteCurrentSession();


header("Location: login.php");

?>