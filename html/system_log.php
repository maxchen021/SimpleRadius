
<?php
require_once("initialize.php");

$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}

ShowHeader("System Log");

global $SYSTEM_SETTING;

$mobile_detect = new Mobile_Detect;
  if ( $mobile_detect->isMobile() ) {
      $textarea_width_style='style="min-width: 100%"';
	  $textarea_rows="30";
  }
  else
  {     
      $textarea_width_style='style="width:900px;"';
	  $textarea_rows="50";
  }

$system_log=SimpleRadius::Run_Command_With_Output("Get_FreeRadius_Log_File");

echo '<div class="span10">
			<div class="hero-unit">
				
<fieldset>

<!-- Form Name -->
<legend><b>System Log</b></legend>

<!-- Textarea -->
<div class="form-group">                   
    <textarea readonly class="form-control" id="system_log" name="system_log" rows="' .$textarea_rows. '" ' .$textarea_width_style. '>' . $system_log . '</textarea>
</div>

</fieldset>


			</div>
		</div>
	</div>
</div>

';

ShowFooter();






?>