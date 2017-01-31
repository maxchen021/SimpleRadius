
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

    if(strcmp($_POST['post_action'],'restart_radius_service')==0)
    {
      RestartRadiusService();
    }
    elseif(strcmp($_POST['post_action'],'restart_system')==0)
    {
      RestartSystem();
    }

  }
}

ShowHeader("System Services");

DisplayJavaScript();

echo '<div class="span10">
			<div class="hero-unit">
				<form class="form-horizontal" name="services_form" id="services_form" action="system_services.php" method="post">
<fieldset>

<!-- Form Name -->
<legend><b>Services</b> <small id="form_message" style="color:red;">' . $form_message . '</small></legend>
';

DisplayRestartButton();


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
function RestartRadiusService()
{
  global $form_message;
  $result=SimpleRadius::Run_Command_With_Output("Validate_FreeRadius_Configs");
  $result=substr($result,-1,1);
  //if validation return 0, then config is correct
  if(strcmp($result,'0')==0)
  {
      SimpleRadius::Run_Command("Restart_FreeRadius_Service");
      $form_message="Radius service restarted successfully";
  }
  else
  {
      $form_message="Cannot restart due to incorrect config";
  }

}
//=========================================================================================
function RestartSystem()
{
 SimpleRadius::Run_Background_Command("Restart_System");
 header("Location: redirect_message.php?type=system_reboot");
}

//=========================================================================================
function DisplayRestartButton()
{

  global $SYSTEM_SETTING;

  echo '
  <!-- Button -->
  <div class="control-group">
   <label class="control-label" for="btn_restart_radius_service">Radius Service</label>
    <div class="controls" >
      <button id="btn_restart_radius_service" name="btn_restart_radius_service" class="btn btn-primary" onclick="return Btn_Restart_Radius_Service();">Restart</button>
    </div>
  </div>
  
    <!-- Button -->
  <div class="control-group">
   <label class="control-label" for="btn_restart_system">' . $SYSTEM_SETTING["title"]. ' System</label>
    <div class="controls" >
      <button id="btn_restart_system" name="btn_restart_system" class="btn btn-danger" onclick="return Btn_Restart_System();">Restart</button>
    </div>
  </div>';


}


//=========================================================================================
function DisplayJavaScript()
{
echo '
<script language="javascript">


function Btn_Restart_Radius_Service()
   {
     
     swal({
          title: "Please Confirm!", 
          text: "Are you sure you want to restart?", 
          type: "warning",
          showCancelButton: true,
          confirmButtonText: "Restart",
          confirmButtonColor: "#ec6c62"
        }, function (answer) {                      
              if (answer==true)
                {
                  DisplayWaitMessage();
                  document.getElementById("services_form").setAttribute("action","system_services.php");
                  document.getElementById("post_action").setAttribute("value","restart_radius_service");
                  document.getElementById("services_form").submit();
                }
                          
         });
                
        //this is needed to ensure dialog does not auto close
        return false; 
   }
   
   function Btn_Restart_System()
   {
     
     swal({
          title: "Please Confirm!", 
          text: "Are you sure you want to restart?", 
          type: "warning",
          showCancelButton: true,
          confirmButtonText: "Restart",
          confirmButtonColor: "#ec6c62"
        }, function (answer) {                      
              if (answer==true)
                {
                  DisplayWaitMessage();
                  document.getElementById("services_form").setAttribute("action","system_services.php");
                  document.getElementById("post_action").setAttribute("value","restart_system");
                  document.getElementById("services_form").submit();
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
