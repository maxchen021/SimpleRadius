
<?php
require_once("initialize.php");

$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}

global $SYSTEM_SETTING;

$form_message="";


if(isset($_POST['ready_to_submit']) && $_POST['ready_to_submit']==1)
{
  if(isset($_POST['post_action']))
  {
    
    if(strcmp($_POST['post_action'],'save')==0)
    {
      SaveConfigs();
    }

  }
}

ShowHeader("Radius Configs");

DisplayJavaScript();

echo '<div class="span10">
			<div class="hero-unit">
				<form class="form-horizontal" name="radius_configs_form" id="radius_configs_form" action="radius_configs.php" method="post">
<fieldset>

<!-- Form Name -->
<legend><b>Radius Configs</b> <small id="form_message" style="color:red;">' . $form_message . '</small></legend>
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
function SaveConfigs()
{
  $eap_config=trim($_POST['eap_config']);
  $default_config=trim($_POST['default_config']);
  $inner_tunnel_config=trim($_POST['inner_tunnel_config']);


  global $SYSTEM_SETTING;
  global $form_message;

  $freeradius = new FreeRadius();
  $freeradius->SaveConfigToDB("eap", $eap_config);
  $freeradius->SaveConfigToDB("default", $default_config);
  $freeradius->SaveConfigToDB("inner-tunnel", $inner_tunnel_config);
  $freeradius->CreateSiteConfigs();
  $freeradius->CreateMods();

  $form_message="Radius configs saved successfully";

}


//=========================================================================================
function DisplayInput()
{
  global $SYSTEM_SETTING;

  $freeradius = new FreeRadius();
  $eap_config=$freeradius->GetConfigFromDB('eap');
  $default_config=$freeradius->GetConfigFromDB('default');
  $inner_tunnel_config=$freeradius->GetConfigFromDB('inner-tunnel');
  
  $mobile_detect = new Mobile_Detect;
  if ( $mobile_detect->isMobile() ) {
      $textarea_width_style="";
  }
  else
  {     
      $textarea_width_style='style="width:800px;"';
  }
  

echo '
  <!-- Textarea -->
<div class="control-group">
  <label class="control-label" for="eap_config">eap</label>
  <div class="controls" >                     
    <textarea id="eap_config" name="eap_config" rows="20" ' .$textarea_width_style. '>' . $eap_config . '</textarea>
  </div>
</div>

<!-- Textarea -->
<div class="control-group">
  <label class="control-label" for="default_config">default site</label>
  <div class="controls" >                     
    <textarea id="default_config" name="default_config" rows="20" ' .$textarea_width_style. '>' . $default_config . '</textarea>
  </div>
</div>

<!-- Textarea -->
<div class="control-group">
  <label class="control-label" for="inner_tunnel_config">inner-tunnel site</label>
  <div class="controls" >                     
    <textarea id="inner_tunnel_config" name="inner_tunnel_config" rows="20" ' .$textarea_width_style. '>' . $inner_tunnel_config . '</textarea>
  </div>
</div>

';


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

                <button id="btn_cancel" name="btn_cancel" class="btn btn-info" onclick="Btn_Cancel();">Cancel</button>

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
     document.getElementById("radius_configs_form").setAttribute("action","radius_configs.php");
     document.getElementById("post_action").setAttribute("value","save");
   }

function Btn_Cancel()
   {
     document.getElementById("radius_configs_form").setAttribute("action","radius_configs.php");
     document.getElementById("post_action").setAttribute("value","cancel");
   }

</script>

';
}

//=========================================================================================
?>