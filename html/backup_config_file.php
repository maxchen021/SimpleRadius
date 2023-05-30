
<?php
require_once("initialize.php");

$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}

if(isset($_POST['ready_to_submit']) && $_POST['ready_to_submit']==1)
{
  if(isset($_POST['post_action']))
  {

    if(strcmp($_POST['post_action'],'backup_settings')==0)
    {
      GenerateConfigBackupFile();
    }
    else
    {
      header("Location: login.php");
    }

  }
}
else
{
  header("Location: login.php");
}


//=========================================================================================

function GenerateConfigBackupFile()
{
  global $SYSTEM_SETTING;
  global $form_message;

  //remove the old backup
  $command="rm -rf " . $SYSTEM_SETTING["config_backup_directory_path"];
  $result=`$command`;
  $command="rm " . $SYSTEM_SETTING["config_backup_temp_directory"] . "/" . $SYSTEM_SETTING["config_backup_filename"];
  $result=`$command`;
  $command="rm " . $SYSTEM_SETTING["config_backup_temp_directory"] . "/" . $SYSTEM_SETTING["encrypted_config_backup_filename"];
  $result=`$command`;

  if( isset($_POST['backup_config_file_password']) )
  {
    $backup_config_file_password = trim($_POST['backup_config_file_password']);
    if ( strcmp($backup_config_file_password,"")!=0 ) {
      $SYSTEM_SETTING["config_file_encryption_key"] = $backup_config_file_password;
      SaveBackupConfigFilePassword($backup_config_file_password);
    }
  }

  //create the new one
  mkdir($SYSTEM_SETTING["config_backup_directory_path"],0755,true);
  mkdir($SYSTEM_SETTING["config_backup_directory_path"]."/database");


  $command="cp " . $SYSTEM_SETTING["DB_File"] . " " . $SYSTEM_SETTING["config_backup_directory_path"]. "/database/simple_radius.db";
  $result=`$command`;

  $command="cd " . $SYSTEM_SETTING["config_backup_temp_directory"] . "; tar -czf " . $SYSTEM_SETTING["config_backup_filename"] . " " . $SYSTEM_SETTING["config_backup_directory_name"];
  $result=`$command`;

  $command="cd " . $SYSTEM_SETTING["config_backup_temp_directory"] . "; openssl aes-256-cbc -md sha256 -e -a -salt -pass pass:"
          . $SYSTEM_SETTING["config_file_encryption_key"]
          . " -in " . $SYSTEM_SETTING["config_backup_filename"]
          . " -out " . $SYSTEM_SETTING["encrypted_config_backup_filename"];
  $result=`$command`;

   header("Content-type: application/octet-stream");
   header("Content-Disposition: attachment; filename=backup.cfg");

   $command="cat " . $SYSTEM_SETTING["config_backup_temp_directory"] . "/" . $SYSTEM_SETTING["encrypted_config_backup_filename"];
   $str=`$command`;
   echo $str;



}

function SaveBackupConfigFilePassword($backup_config_file_password)
{
  global $CURRENT_DB;

  $backup_config_file_password=(new Encryption())->Encrypt($backup_config_file_password);

  $query="update system_setting set value='"
  . $backup_config_file_password . "' where system_setting='backup_config_file_password'";

  $result=$CURRENT_DB->DBUpdateQuery($query);

}


?>
