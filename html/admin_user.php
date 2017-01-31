
<?php
require_once("initialize.php");

$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}

$current_password="";
$new_password="";
$new_password2="";
$form_message="";

if(isset($_POST['ready_to_submit']) && $_POST['ready_to_submit']==1)
{
  if(isset($_POST['post_action']))
  {

    if(strcmp($_POST['post_action'],'save')==0)
    {
      SaveEditedUser();
    }

  }
}

ShowHeader("Admin User");
DisplayJavaScript();

echo '<div class="span10">
			<div class="hero-unit">
				<form class="form-horizontal" name="admin_user_form" id="admin_user_form" action="admin_user.php" method="post">
<fieldset>

<!-- Form Name -->
<legend><b>Admin User</b> <small id="form_message" style="color:red;">' . $form_message . '</small></legend>
';

DisplayInput($current_password,$new_password,$new_password2);
DisplaySaveCancelButton();

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

function SaveEditedUser()
{


  global $current_password;
  global $new_password;
  global $new_password2;


  $current_password=trim($_POST['current_password']);
  $new_password=trim($_POST['new_password']);
  $new_password2=trim($_POST['new_password2']);

  global $form_message;

  if(!isset($current_password) || $current_password == "" )
  {

    $form_message="Please enter the current password";
    return;
  }

  if(!isset($new_password) || $new_password == "" )
  {

    $form_message="Please enter the new password";
    return;
  }

  if(!isset($new_password2) || $new_password2 == "" )
  {

    $form_message="Please enter the new password confirmation";
    return;
  }

  if(strcmp($new_password,$new_password2)!=0)
  {

    $form_message="New password do not match";
    return;
  }



  $user=new User();
  $auth=new Authentication();

  $userid=$auth->GetUserIDFromCurrentSession();


  $user->SetUserID($userid);
  $user->SetPassword($current_password);


  if( !$auth->AuthenticateUser($user->GetUserID(), $user->GetPassword()) )
  {
    $form_message="Current password is incorrect";
    return;
  }

  $user->GetUserInfoByUserID($userid);
  $user->SetPassword($new_password);


  $result=$user->UpdateUser();

  if($result)
  {
    $form_message="Password changed successfully";
    $current_password="";
    $new_password="";
    $new_password2="";

    $_POST['post_action']="";

    $auth->DeleteCurrentSession();

  }
  else
  {
    $form_message="There's an error saving the change";
    $_POST['post_action']="";

  }
}

function DisplayInput($current_password,$new_password,$new_password2)
{
  echo '
  <!-- Text input-->
<div class="control-group">
  <label class="control-label" for="current_password">Current Password</label>
  <div class="controls">
    <input id="current_password" name="current_password" type="password" value="' . $current_password . '" class="input-xlarge" maxlength="100">

  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="new_password">New Password</label>
  <div class="controls">
    <input id="new_password" name="new_password" type="password" value="' . $new_password . '" class="input-xlarge" maxlength="100">

  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="new_password2">New Password Confirmation</label>
  <div class="controls">
    <input id="new_password2" name="new_password2" type="password" value="' . $new_password2 . '" class="input-xlarge" maxlength="100">

  </div>
</div>


';

}


function DisplaySaveCancelButton()
{
  echo '
<table class="table">
        <thead>
          <tr>

<th>

    <button id="btn_save" name="btn_save" class="btn btn-primary" onclick="Btn_Save();">Save</button>

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


function Btn_Save()
   {
     DisplayWaitMessage();
     document.getElementById("admin_user_form").setAttribute("action","admin_user.php");
     document.getElementById("post_action").setAttribute("value","save");
   }

function Btn_Cancel()
   {
     document.getElementById("admin_user_form").setAttribute("action","admin_user.php");
     document.getElementById("post_action").setAttribute("value","cancel");
   }

</script>

';
}


?>
