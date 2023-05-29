
<?php
require_once("initialize.php");

$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}


$form_message="";


if(isset($_POST['ready_to_submit']) && $_POST['ready_to_submit']==1)
{
  if(isset($_POST['post_action']))
  {

    if(strcmp($_POST['post_action'],'restore_settings')==0)
    {
      RestoreConfigBackupFile();
    }
    elseif(strcmp($_POST['post_action'],'factory_reset')==0)
    {
      Factory_Reset();
    }

  }
}

ShowHeader("Backup");

DisplayJavaScript();

echo '
<div class="span10">
			<div class="hero-unit">
				<form class="form-horizontal" name="backup_form" id="backup_form" action="backup.php" method="post" enctype="multipart/form-data" >
<fieldset>

<!-- Form Name -->
<legend><b>Backup</b> <small id="form_message" style="color:red;">' . $form_message . '</small></legend>
';

DisplayBackupButton();


echo '
</fieldset>
<input type="hidden" name="ready_to_submit" value="1">
<input type="hidden" name="post_action" id="post_action" value="">
</form>



			</div>
		</div>
	</div>
</div>

';

ShowFooter();

//=========================================================================================

function RestoreConfigBackupFile()
{

  global $SYSTEM_SETTING;
  global $form_message;

  //echo var_dump($_FILES);

  $backup_filename=$_FILES["restore_settings_file_button"]["tmp_name"];


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
    }
  }

  //create the new one
  mkdir($SYSTEM_SETTING["config_backup_directory_path"],0755,true);
  $command="cd " . $SYSTEM_SETTING["config_backup_temp_directory"] . "; openssl aes-256-cbc -md sha256 -d -a -salt -pass pass:"
          . $SYSTEM_SETTING["config_file_encryption_key"]
          . " -in " . $backup_filename
          . " -out " . $SYSTEM_SETTING["config_backup_filename"] . " 2>&1";

  $result=`$command`;
  #decrypt the old version (v1.0) of the backup
  if(str_contains($result, "bad decrypt")) {
    $command="cd " . $SYSTEM_SETTING["config_backup_temp_directory"] . "; openssl aes-256-cbc -md md5 -d -a -salt -pass pass:"
          . $SYSTEM_SETTING["config_file_encryption_key"]
          . " -in " . $backup_filename
          . " -out " . $SYSTEM_SETTING["config_backup_filename"] . " 2>&1";

    $result=`$command`;
  }
  
  if(str_contains($result, "bad decrypt")) {
    $form_message="The backup configuration file password is incorrect!";
    return;
  }

  $command="cd " . $SYSTEM_SETTING["config_backup_temp_directory"] . "; tar -xzf " . $SYSTEM_SETTING["config_backup_filename"] . " -C " . $SYSTEM_SETTING["config_backup_temp_directory"];

  $result=`$command`;

  if( CheckForCorrectBackupConfigVersion()==false )
  {
    $form_message="The backup file is not compatible with the current firmware version";
    return;
  }

  $command="cp -f " . $SYSTEM_SETTING["config_backup_directory_path"] . "/database/simple_radius.db" . " " . $SYSTEM_SETTING["DB_File"];
  $result=`$command`;

  global $CURRENT_DB;
  $CURRENT_DB=new Database();
  $CURRENT_DB->UpgradeDB();
  (new Encryption())->GetEncryptionConfig();

  $freeradius = new FreeRadius();
	$freeradius->CreateClientConfig();
	$freeradius->CreateUserConfig();
	$freeradius->CreateSiteConfigs();
  $freeradius->CreateMods();
  
  $ssl_certs = new SSLCerts();
  $ssl_certs->SetSSLCertType('https');
  $ssl_certs->SaveSSLCertsToFile();
  
  $ssl_certs = new SSLCerts();
  $ssl_certs->SetSSLCertType('radius');
  $ssl_certs->SaveSSLCertsToFile();

  (new SimpleRadius())->Run_Background_Command("System_Restore");
  header("Location: redirect_message.php?type=system_restore");

}

//=========================================================================================
function CheckForCorrectBackupConfigVersion()
{
  global $SYSTEM_SETTING;
  global $CURRENT_DB;
  
  $backup_db_file = $SYSTEM_SETTING["config_backup_directory_path"] . "/database/simple_radius.db";
  
  if( file_exists($backup_db_file) ) {
    $db = new SQLite3($backup_db_file);
    $query="select * from system_setting where system_setting='version'";

		$result=$db->query($query);

		if($row=$result->fetchArray())
		{
        if( isset($row['value']) && strcmp($row['value'],"")!=0 ) {
            $backup_db_version =floatval($row['value']);
            if ($backup_db_version<=$CURRENT_DB->GetDBVersion())
            {
              return true;
            }
        }     
		}
    $db->close();
  }
  
   return false;
  
  
}
//=========================================================================================


//=========================================================================================

//=========================================================================================
function Factory_Reset()
{
  (new SimpleRadius())->Run_Background_Command("Factory_Reset");
  header("Location: redirect_message.php?type=factory_reset");
}

function GetBackupConfigFilePassword()
{
  $query="select * from system_setting where system_setting='backup_config_file_password'";

  global $CURRENT_DB;

  $result=$CURRENT_DB->DBSelectQuery($query);

  $backup_config_file_password="";

  if($row=$result->fetchArray())
  {
    if( isset($row['value']) && strcmp($row['value'],"")!=0 ) {
      $backup_config_file_password=(new Encryption())->Decrypt($row['value']);
    }
  }

  return $backup_config_file_password;
}
//=========================================================================================
function DisplayBackupButton()
{

$backup_config_file_password = GetBackupConfigFilePassword();
echo '
<!-- Text input-->
<div class="control-group">
<label class="control-label" for="backup_config_file_password">Backup Password</label>
<div class="controls">
  <input id="backup_config_file_password" name="backup_config_file_password" type="password" value="' . $backup_config_file_password . '" class="col-md-1" maxlength="100">

</div>
</div>

<!-- Button -->
<div class="control-group">
 <label class="control-label" for="btn_backup_settings">Backup Settings</label>
  <div class="controls" >
    <button id="btn_backup_settings" name="btn_backup_settings" class="btn btn-primary" onclick="Btn_Backup_Settings();">Backup</button>
  </div>
</div>

<!-- Button -->
<div class="control-group">
 <label class="control-label" id="restore_settings_label" for="btn_restore_settings">Restore Settings</label>
  <div class="controls" >
   <button id="btn_restore_settings" name="btn_restore_settings" class="btn btn-primary" onclick="return Btn_Restore_Settings();" >Choose File</button>
  <input id="restore_settings_file_button" name="restore_settings_file_button" type="file" style="visibility: hidden;" onchange="Restore_Settings_File_Selected();"/>

  </div>

</div>

<!-- Button -->
<div class="control-group">
 <label class="control-label" for="btn_factory_reset">Factory Reset</label>
  <div class="controls" >
     <button id="btn_factory_reset" name="btn_factory_reset" class="btn btn-danger" onclick="return Btn_Factory_Reset();" >Reset</button>
  </div>
</div>';


}


//=========================================================================================
function DisplayJavaScript()
{
echo '
<script language="javascript">


function Btn_Backup_Settings()
   {
     document.getElementById("backup_form").setAttribute("action","backup_config_file.php");
     document.getElementById("post_action").setAttribute("value","backup_settings");
   }



function Restore_Settings_File_Selected()
{
     if(document.getElementById("restore_settings_file_button").files.length > 0 || document.getElementById("restore_settings_file_button").value !="")
     {
      document.getElementById("btn_restore_settings").innerHTML = "Restore";
      document.getElementById("restore_settings_label").innerHTML="Restore Settings From: " + document.getElementById("restore_settings_file_button").value;
     }

}

function Btn_Restore_Settings()
   {
    if( document.getElementById("btn_restore_settings").innerHTML == "Choose File" )
    {
      document.getElementById("restore_settings_file_button").click();
      return false;
    }
    else
    {
        swal({
          title: "Please Confirm!", 
          text: "Are you sure you want to do a restore?", 
          type: "warning",
          showCancelButton: true,
          confirmButtonText: "Restore",
          confirmButtonColor: "#ec6c62"
        }, function (answer) {                      
              if (answer==true)
                {
                  DisplayWaitMessage();
                  document.getElementById("backup_form").setAttribute("action","backup.php");
                  document.getElementById("post_action").setAttribute("value","restore_settings");
                  document.getElementById("backup_form").submit();
                }
                          
         });
                
        //this is needed to ensure dialog does not auto close
        return false;       

    }
   }



function Btn_Factory_Reset()
   {
     
        swal({
          title: "Please Confirm!", 
          text: "Are you sure you want to do a factory reset?", 
          type: "warning",
          showCancelButton: true,
          confirmButtonText: "Reset",
          confirmButtonColor: "#ec6c62"
        }, function (answer) {                      
              if (answer==true)
                {
                  DisplayWaitMessage();
                  document.getElementById("backup_form").setAttribute("action","backup.php");
                  document.getElementById("post_action").setAttribute("value","factory_reset");
                  document.getElementById("backup_form").submit();
                }
                          
         });
                
        //this is needed to ensure dialog does not auto close
        return false;          
  
        
   }


</script>

';
}

//=========================================================================================
?>
