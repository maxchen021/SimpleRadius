
<?php
require_once("initialize.php");

$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}

global $SYSTEM_SETTING;

$form_message="";
$public_key_filename=$SYSTEM_SETTING['radius_ssl_certs_directory'] . "/" . $SYSTEM_SETTING['ssl_cert']['public_key_name'];
$private_key_filename=$SYSTEM_SETTING['radius_ssl_certs_directory'] . "/" . $SYSTEM_SETTING['ssl_cert']['private_key_name'];
$ca_cert_filename=$SYSTEM_SETTING['radius_ssl_certs_directory'] . "/" . $SYSTEM_SETTING['ssl_cert']['ca_cert_name'];
$dh_parameters_filename=$SYSTEM_SETTING['radius_ssl_certs_directory'] . "/" . $SYSTEM_SETTING['ssl_cert']['dh_parameters_name'];

$temp_public_key_filename=$public_key_filename . ".temp";
$temp_private_key_filename=$private_key_filename . ".temp";
$temp_ca_cert_filename=$ca_cert_filename . ".temp";
$temp_dh_parameters_filename=$dh_parameters_filename . ".temp";

if(isset($_POST['ready_to_submit']) && $_POST['ready_to_submit']==1)
{
  if(isset($_POST['post_action']))
  {
    
    if(strcmp($_POST['post_action'],'save')==0)
    {
      SaveSSLCert();
    }

  }
}

ShowHeader("Radius SSL Certificate");

DisplayJavaScript();

echo '<div class="span10">
			<div class="hero-unit">
				<form class="form-horizontal" name="radius_ssl_cert_form" id="radius_ssl_cert_form" action="radius_ssl_cert.php" method="post">
<fieldset>

<!-- Form Name -->
<legend><b>Radius SSL Certificate (PEM Format)</b> <small id="form_message" style="color:red;">' . $form_message . '</small></legend>
';

DisplayInput();
DisplaySaveCancelButton();

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
function SaveSSLCert()
{
  $public_key=ltrim($_POST['public_key']);
  $private_key=ltrim($_POST['private_key']);
  $ca_cert=ltrim($_POST['ca_cert']);
  $dh_parameters=ltrim($_POST['dh_parameters']);

  global $SYSTEM_SETTING;
  global $form_message;

  global $public_key_filename;
  global $private_key_filename;
  global $ca_cert_filename;
  global $dh_parameters_filename;

  global $temp_public_key_filename;
  global $temp_private_key_filename;
  global $temp_ca_cert_filename;
  global $temp_dh_parameters_filename;

  $ssl_certs = new SSLCerts();
  $ssl_certs->SetSSLCertType('radius');

  $filehandle = fopen($temp_public_key_filename, 'w') or die("can't open file ".$temp_public_key_filename);
  fwrite($filehandle,$public_key);
  fclose($filehandle);

  if($ssl_certs->ValidateSSLCert($temp_public_key_filename)==false)
  {
    $form_message="Invalid Public Key";
    return false;
  }

  $filehandle = fopen($temp_private_key_filename, 'w') or die("can't open file ".$temp_private_key_filename);
  fwrite($filehandle,$private_key);
  fclose($filehandle);

  
  if($ssl_certs->ValidateSSLKey($temp_private_key_filename)==false)
  {
    $form_message="Invalid Private Key";
    return false;
  }

  if(isset($ca_cert) || $ca_cert != "" )
  {    
    $filehandle = fopen($temp_ca_cert_filename, 'w') or die("can't open file ".$temp_ca_cert_filename);
    fwrite($filehandle,$ca_cert);
    fclose($filehandle);

    if($ssl_certs->ValidateSSLCert($temp_ca_cert_filename)==false)
    {
      $form_message="Invalid CA Cert";
      return false;
    }
  }
  
  //dh parameters
  $filehandle = fopen($temp_dh_parameters_filename, 'w') or die("can't open file " . $temp_dh_parameters_filename);
  fwrite($filehandle,$dh_parameters);
  fclose($filehandle);

  
  if($ssl_certs->ValidateDHParameters($temp_dh_parameters_filename)==false)
  {
    $form_message="Invalid DH Parameters";
    return false;
  }

  
  $ssl_certs->SaveCertToDB('radius_ssl_cert_public_key',$public_key);
  $ssl_certs->SaveCertToDB('radius_ssl_cert_private_key',$private_key);
  $ssl_certs->SaveCertToDB('radius_ssl_cert_ca_cert',$ca_cert);
  $ssl_certs->SaveCertToDB('radius_dh_parameters',$dh_parameters);

  $form_message="SSL certs saved successfully";

  
  $ssl_certs->SaveSSLCertsToFile();
  
  (new SimpleRadius())->Run_Command("Reload_FreeRadius_Config");
  


}


//=========================================================================================
function DisplayInput()
{
  global $SYSTEM_SETTING;

  $ssl_certs = new SSLCerts();
  $ssl_certs->SetSSLCertType('radius');
  $public_key=$ssl_certs->GetCertFromDB('radius_ssl_cert_public_key');
  $private_key=$ssl_certs->GetCertFromDB('radius_ssl_cert_private_key');
  $ca_cert=$ssl_certs->GetCertFromDB('radius_ssl_cert_ca_cert');
  $dh_parameters=$ssl_certs->GetCertFromDB('radius_dh_parameters');
  
  $mobile_detect = new Mobile_Detect;
  if ( $mobile_detect->isMobile() ) {
      $textarea_width_style="";
  }
  else
  {     
      $textarea_width_style='style="width:600px;"';
  }

echo '
  <!-- Textarea -->
<div class="control-group">
  <label class="control-label" for="public_key">Public Key</label>
  <div class="controls" >                     
    <textarea id="public_key" name="public_key" rows="10" ' .$textarea_width_style. '>' . $public_key . '</textarea>
  </div>
</div>

<!-- Textarea -->
<div class="control-group">
  <label class="control-label" for="private_key">Private Key</label>
  <div class="controls">                     
    <textarea id="private_key" name="private_key" rows="10" ' .$textarea_width_style. '>' . $private_key . '</textarea>
  </div>
</div>

<!-- Textarea -->
<div class="control-group">
  <label class="control-label" for="ca_cert">CA Cert</label>
  <div class="controls">                     
    <textarea id="ca_cert" name="ca_cert" rows="10" ' .$textarea_width_style. '>' . $ca_cert . '</textarea>
  </div>
</div>

<!-- Textarea -->
<div class="control-group">
  <label class="control-label" for="dh_parameters">DH Parameters</label>
  <div class="controls">                     
    <textarea id="dh_parameters" name="dh_parameters" rows="10" ' .$textarea_width_style. '>' . $dh_parameters . '</textarea>
  </div>
</div>';


}

//=========================================================================================
function DisplaySaveCancelButton()
{
  echo '
  <div class="control-group">
  <label class="control-label" for="btn_save"></label>
  <div class="controls">
            <table class="table">
                    <thead>
                      <tr>

            <th>

                <button id="btn_add" name="btn_save" class="btn btn-primary" onclick="Btn_Save();">Save</button>
              
            </th>

            <th>

                <button id="btn_edit" name="btn_cancel" class="btn btn-info" onclick="Btn_Cancel();">Cancel</button>

            </th>


            </tr>
              </thead>
                  </table>
      </div>
  </div>';

}

//=========================================================================================
function DisplayJavaScript()
{
echo '
<script language="javascript">


function Btn_Save()
   {
     DisplayWaitMessage();
     document.getElementById("radius_ssl_cert_form").setAttribute("action","radius_ssl_cert.php");
     document.getElementById("post_action").setAttribute("value","save");
   }

function Btn_Cancel()
   {
     document.getElementById("radius_ssl_cert_form").setAttribute("action","radius_ssl_cert.php");
     document.getElementById("post_action").setAttribute("value","cancel");
   }

</script>

';
}

//=========================================================================================
?>