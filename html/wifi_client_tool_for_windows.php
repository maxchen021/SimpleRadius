<?php
require_once("initialize.php");


$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}


$wifi_client_tool_for_windows_filename = "wifi_client_tool_for_windows.exe";

if(isset($_POST['ready_to_submit']) && $_POST['ready_to_submit']==1)
{
  if(isset($_POST['post_action']))
  {

    if(strcmp($_POST['post_action'],'download')==0)
    {

        $wifi_ssid=trim($_POST['wifi_ssid']);

        if(!isset($wifi_ssid) || $wifi_ssid == "" )
        {
          header("Location: wifi_client_tool.php");
        }
        else
        {
          CreateWifiClientTool($wifi_ssid);
          OutputFileForDownload();

        }

    }

  }
}


//=========================================================================================
function OutputFileForDownload()
{
  global $SYSTEM_SETTING, $wifi_client_tool_for_windows_filename;
  $exe_filename = $SYSTEM_SETTING["wifi_client_tool_for_windows_temp_directory_path"] . "/" . $wifi_client_tool_for_windows_filename;

  header('Content-Description: File Transfer');
  header("Content-Type: application/octet-stream");
  header("Content-Length: ". filesize($exe_filename));
  header("Content-Disposition: attachment; filename=$wifi_client_tool_for_windows_filename");
  header("Content-Transfer-Encoding: binary");
  //echo readfile($exe_filename);
  ob_clean();
    flush();
    readfile($exe_filename);
}

//=========================================================================================
function CreateWifiClientTool($wifi_ssid)
{
  global $SYSTEM_SETTING, $wifi_client_tool_for_windows_filename;

 //remove the old one
  $command="rm -rf " . $SYSTEM_SETTING["wifi_client_tool_for_windows_temp_directory_path"];
  $result=`$command`;

  //create the new one
  mkdir($SYSTEM_SETTING["wifi_client_tool_for_windows_temp_directory_path"],0755,true);

  $ca_cert=GetCACert();
  $ca_cert_fingerprint=GetCACertFingerprint();

  chdir($SYSTEM_SETTING["wifi_client_tool_for_windows_source_code_directory"]);

  $file_contents = file_get_contents("Form1.cs.template");
  $file_contents = str_replace('private string wifi_ssid = "";', 'private string wifi_ssid = "' . $wifi_ssid . '";' ,$file_contents);
  $file_contents = str_replace('private string wifi_cert_fingerprint = "";', 'private string wifi_cert_fingerprint = "' . $ca_cert_fingerprint . '";' ,$file_contents);
  $file_contents = str_replace('private string ca_cert = "";', 'private string ca_cert = "' . $ca_cert . '";' ,$file_contents);
  file_put_contents("Form1.cs",$file_contents);

  $command = 'mcs *.cs -pkg:dotnet -target:winexe -out:' . $SYSTEM_SETTING["wifi_client_tool_for_windows_temp_directory_path"] . "/" . $wifi_client_tool_for_windows_filename;
  $result=`$command`;
}

//=========================================================================================
 function GetCACertFingerprint()
  {
    global $SYSTEM_SETTING;
    $ca_cert_filename = $SYSTEM_SETTING["wifi_client_tool_for_windows_temp_directory_path"] . "/" . $SYSTEM_SETTING['ssl_cert']['ca_cert_name'];
  
    $command = "openssl x509 -noout -in " . $ca_cert_filename . " -fingerprint -sha1";
    $result=`$command`;

    $result=trim($result);
    $temp=explode("=",$result);
    $fingerprint=$temp[1];
    $fingerprint=str_replace(":"," ",$fingerprint);

    return $fingerprint;
  }

  //=========================================================================================
  
  function GetCACert()
  {
     global $SYSTEM_SETTING;
    global $CURRENT_DB;
    $ca_cert_filename = $SYSTEM_SETTING["wifi_client_tool_for_windows_temp_directory_path"] . "/" . $SYSTEM_SETTING['ssl_cert']['ca_cert_name'];

    $ssl_certs = new SSLCerts();
    
     //save ca cert to temp file first
		 $query="select * from ssl_certs where key_name='radius_ssl_cert_ca_cert'";
  	 $result=$CURRENT_DB->DBSelectQuery($query);
	   $ssl_certs->SaveSSLCertsKeyDataToFile($result,$ca_cert_filename);

    $ca_cert="";

    if(file_exists($ca_cert_filename))
    {

      $file = fopen($ca_cert_filename, "r") or exit("Unable to open file!");
      //Output a line of the file until the end is reached
      while(!feof($file))
        {
        $ca_cert.=trim(fgets($file));
        }
      fclose($file);
    }

    return $ca_cert;
  }


?>
