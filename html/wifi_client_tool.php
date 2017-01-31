
<?php
require_once("initialize.php");


$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}


$wifi_ssid="";
$form_message="";


ShowHeader("Wi-Fi Client Tool");
DisplayJavaScript();

echo '<div class="span10">
			<div class="hero-unit">
				<form class="form-horizontal" name="wifi_client_tool_form" id="wifi_client_tool_form" action="wifi_client_tool.php" method="post">
<fieldset>

<!-- Form Name -->
<legend><b>Wi-Fi Client Tool</b> <small id="form_message" style="color:red;">' . $form_message . '</small></legend>
';

DisplayInput($wifi_ssid);
DisplayDownloadCancelButton();

echo '

<input type="hidden" name="ready_to_submit" value="1">
<input type="hidden" name="post_action" id="post_action" value="">
</fieldset>
</form>

			</div>
		</div>
	</div>
</div>

';

ShowFooter();


function DisplayInput($wifi_ssid)
{
  echo '
  <!-- Text input-->
<div class="control-group">
  <label class="control-label" for="wifi_ssid" data-toggle="tooltip" title="Wi-Fi SSID needed to generate the program that will import the CA cert to prevent cert warning on the client and potentially prevent certain attack">Wi-Fi SSID</label>
  <div class="controls">
    <input id="wifi_ssid" name="wifi_ssid" type="text" value="' . $wifi_ssid . '" class="input-xlarge" maxlength="30">

  </div>
</div>


';

}


function DisplayDownloadCancelButton()
{
  echo '
<table class="table">
        <thead>
          <tr>

<th>

    <button id="btn_download" name="btn_download" class="btn btn-primary" onclick="Btn_Download();">Download</button>

</th>

<th>

    <button id="btn_cancel" name="btn_cancel" class="btn btn-info" onclick="Btn_Cancel();">Cancel</button>

</th>


</tr>
  </thead>
      </table>';

}

function DisplayJavaScript()
{
echo '
<script language="javascript">


function Btn_Download()
   {
     //DisplayWaitMessage();
     document.getElementById("wifi_client_tool_form").setAttribute("action","wifi_client_tool_for_windows.php");
     document.getElementById("post_action").setAttribute("value","download");
   }

function Btn_Cancel()
   {
     document.getElementById("wifi_client_tool_form").setAttribute("action","wifi_client_tool.php");
     document.getElementById("post_action").setAttribute("value","cancel");
   }

</script>

';
}




?>
