
<?php
require_once("initialize.php");

$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}

$username="";
$password="";
$password2="";
$form_message="";
$current_user_id="";

if(isset($_POST['ready_to_submit']) && $_POST['ready_to_submit']==1)
{
  if(isset($_POST['post_action']))
  {
    if(strcmp($_POST['post_action'],'add')==0)
    {
      AddUser();
    }
    elseif(strcmp($_POST['post_action'],'delete')==0)
    {
      DeleteUser();
    }
    elseif(strcmp($_POST['post_action'],'edit')==0)
    {
      EditUser();
    }
    elseif(strcmp($_POST['post_action'],'save')==0)
    {
      SaveEditedUser();
    }

  }
}

ShowHeader("Users");

DisplayJavaScript();

echo '
<div class="span10">
      <div class="hero-unit">
        <form class="form-horizontal" name="radius_users_form" id="radius_users_form" action="radius_users.php" method="post">
<fieldset>

<!-- Form Name -->
<legend><b>Users</b> <small id="form_message" style="color:red;">' . $form_message . '</small></legend>

';

DisplayInput($username,$password,$password2);

if(isset($_POST['post_action']) && strcmp($_POST['post_action'],'edit')==0)
{
  DisplaySaveCancelButton();
}
else
{
  DisplayAddEditDeleteButton();
  $mobile_detect = new Mobile_Detect;
  if ( $mobile_detect->isMobile() ) {
      DisplayUserListForMobile();
  }
  else
  {
    DisplayUserList();
  }
}




echo '

<input type="hidden" name="ready_to_submit" value="1">
<input type="hidden" name="post_action" id="post_action" value="">
<input type="hidden" name="current_user_id" id="current_user_id" value="' . $current_user_id . '">
</fieldset>
</form>

      </div>
    </div>
  </div>
</div>
';

ShowFooter();



function AddUser()
{

  global $username;
  global $password;
  global $password2;


  $username=trim($_POST['username']);
  $password=trim($_POST['password']);
  $password2=trim($_POST['password2']);


  global $form_message;



  if(ValidateInput()==false)
  {
    return;
  }

  global $CURRENT_DB;

  $username=base64_encode($username);
  $password=Encryption::Encrypt($password);



  $query="select * from radius_users where username='" . $username . "'";
  $result=$CURRENT_DB->DBSelectQuery($query);

  if($row=$result->fetchArray())
  {
    $form_message="Username already exist";
    $username=base64_decode($username);
    $password=Encryption::Decrypt($password);
    return;
  }

  $query="insert into radius_users (username,password) values ("
    . "'" . $username . "', "
    . "'" . $password . "') ";

  $result=$CURRENT_DB->DBUpdateQuery($query);

  if($result)
  {
    $form_message="User added successfully";
    $username="";
    $password="";
    $password2="";

    FreeRadius::CreateUserConfig();

  }
  else
  {
    $form_message="There's an error adding the user";

  }

}

function DeleteUser()
{
  $user_id=trim($_POST['user_id']);

  global $form_message;

  if( !isset($user_id) || $user_id=="" || !is_numeric($user_id) )
  {
    $form_message="Please select a user from the list to delete";
    return;
  }

  $query="delete from radius_users where user_id=" . $user_id;

  global $CURRENT_DB;

  $result=$CURRENT_DB->DBUpdateQuery($query);

/*
  if($CURRENT_DB->GetCount('radius_users')<=0)
  {
    $CURRENT_DB->ResetCount('radius_users');
  }
*/

  if($result)
  {
    $form_message="User deleted successfully";

    FreeRadius::CreateUserConfig();
  }
  else
  {
    $form_message="There's an error deleting the user";

  }


}

function EditUser()
{
  $user_id=trim($_POST['user_id']);

  global $form_message;

  if( !isset($user_id) || $user_id=="" || !is_numeric($user_id) )
  {
    $form_message="Please select a user from the list to edit";
    $_POST['post_action']="";
    return;
  }

  $query="select * from radius_users where user_id=" . $user_id;

  global $CURRENT_DB;

  $result=$CURRENT_DB->DBSelectQuery($query);

  if($row=$result->fetchArray())
  {
    global $username;
    global $password;
    global $password2;
    global $current_user_id;

    $username=base64_decode($row['username']);
    $password=Encryption::Decrypt($row['password']);
    $password2=Encryption::Decrypt($row['password']);
    $current_user_id=$row['user_id'];
  }
  else
  {
    $form_message="There's a problem editing the selected user";
    $_POST['post_action']="";
    return;
  }

}

function SaveEditedUser()
{


  global $username;
  global $password;
  global $password2;
  global $current_user_id;

  $current_user_id=trim($_POST['current_user_id']);
  $username=trim($_POST['username']);
  $password=trim($_POST['password']);
  $password2=trim($_POST['password2']);

  global $form_message;

  if( !isset($current_user_id) || $current_user_id=="" || !is_numeric($current_user_id) )
  {
    $form_message="There's a problem saving the change";
    $_POST['post_action']="";
    return;
  }


  if(ValidateInput()==false)
  {
    $_POST['post_action']="edit";
    return;
  }


  global $CURRENT_DB;

  $username=base64_encode($username);
  $password=Encryption::Encrypt($password);

  $query="select * from radius_users where username='" . $username . "' and user_id!=" .$current_user_id;
  $result=$CURRENT_DB->DBSelectQuery($query);

  if($row=$result->fetchArray())
  {
    $form_message="Username already exist";
    $_POST['post_action']="edit";
    $username=base64_decode($username);
    $password=Encryption::Decrypt($password);
    return;
  }


  $query="update radius_users set username='"
  . $username . "', password='"
  . $password . "' where user_id=" . $current_user_id;



  $result=$CURRENT_DB->DBUpdateQuery($query);

  if($result)
  {
    $form_message="User change saved successfully";
    $username="";
    $password="";
    $password2="";
    $current_user_id="";

    $_POST['post_action']="";

    FreeRadius::CreateUserConfig();

  }
  else
  {
    $form_message="There's an error saving the change";
    $_POST['post_action']="edit";

  }
}

function ValidateInput()
{
  global $username;
  global $password;
  global $password2;

  global $form_message;

  if(!isset($username) || $username == "" )
  {

    $form_message="Please enter the username";
    return false;
  }

  //if(!ctype_alnum($username))
  if(!preg_match("/^[a-zA-Z0-9_\-]+$/", $username))
  {
    $form_message="Username must be alphanumeric";
    return false;
  }

  if(!isset($password) || $password == "" )
  {

    $form_message="Please enter the password";
    return false;
  }

  if(!isset($password2) || $password2 == "" )
  {

    $form_message="Please enter the password confirmation";
    return false;
  }

  if(strcmp($password,$password2)!=0)
  {

    $form_message="Password do not match";
    return false;
  }

  return true;
}

function DisplayInput($username,$password,$password2)
{
  echo '
  <!-- Text input-->
<div class="control-group">
  <label class="control-label" for="username" data-toggle="tooltip" title="Username used to authenticate with the radius server. Can be alphanumeric and/or underscore (_) and/or dash (-)">Username</label>
  <div class="controls">
    <input id="username" name="username" type="text" value="' . $username . '" class="input-xlarge" maxlength="30">

  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="User_ip" data-toggle="tooltip" title="The password for the user">Password</label>
  <div class="controls">
    <input id="password" name="password" type="password" value="' . $password . '" class="input-xlarge" maxlength="30">

  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="password2" data-toggle="tooltip" title="The password for the user">Password Confirmation</label>
  <div class="controls">
    <input id="password2" name="password2" type="password" value="' . $password2 . '" class="input-xlarge" maxlength="30">

  </div>
</div>';
}

function DisplayAddEditDeleteButton()
{
  echo '
<table class="table">
        <thead>
          <tr>

<th>

    <button id="btn_add" name="btn_add" class="btn btn-primary" onclick="Btn_Add();">Add</button>

</th>

<th>

    <button id="btn_edit" name="btn_edit" class="btn btn-primary" onclick="Btn_Edit();">Edit</button>

</th>

<th>

    <button id="btn_delete" name="btn_delete" class="btn btn-danger" onclick="return Btn_Delete();">Delete</button>

</th>

</tr>
  </thead>
      </table>';

}



function DisplaySaveCancelButton()
{
  echo '
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
      </table>';

}

function DisplayUserList()
{
  echo '
<table class="table">
        <thead>

          <tr>

            <th>

            </th>
            <th>
              #
            </th>
            <th>
              Username
            </th>
            <th>
              Password
            </th>
            <th>

            </th>

          </tr>

        </thead>
        <tbody> ';

     global $CURRENT_DB;

  $query="select * from radius_users";

  $result=$CURRENT_DB->DBSelectQuery($query);
  $count=1;
    while($row = $result->fetchArray())
     {
      if($count%2==0)
      {
        $css_style_class='class="info"';
        $css_style='style="border: none;background-color: #d9edf7;"';
      }
      else
      {
        $css_style_class="";
        $css_style='style="border: none;"';
      }

       echo '<tr ' . $css_style_class . ' >';

       echo '
            <td>
              <input type="radio" name="user_id" value="' . $row['user_id'] . '">
            </td>
            <td>
              ' . $count . '
            </td>
            <td>
              ' . base64_decode($row['username']) . '
            </td>
            <td>

              <input type="password" readOnly="true" id="user_password_id_' . $count . '" value="'
              . Encryption::Decrypt($row['password']) . '" ' .$css_style .'/>
            </td>
             <td>
            <button class="btn btn-info" id="btn_show_user_password_' . $count . '" onclick="Show_User_Password(\'user_password_id_' . $count . '\',\'btn_show_user_password_' . $count . '\');return false;" >Show User Password</button>
            </td>
          </tr>';

       $count++;
     }

          echo '
        </tbody>
      </table>';
}

function DisplayUserListForMobile()
{
  
   global $CURRENT_DB;

  $query="select * from radius_users";

  $result=$CURRENT_DB->DBSelectQuery($query);
  $count=1;
    while($row = $result->fetchArray())
     {
      
      if($count%2==1)
      {
        $div_css_style='style="padding: 15px;background-color: #d9edf7;"';
        $password_css_style='style="border: none;background-color: #d9edf7;"';
      }
      else
      {
        $div_css_style='style="padding: 15px;background-color: #e6fff2;"';
        $password_css_style='style="border: none;background-color: #e6fff2;"';
      }
       

       echo '
        
        <div ' . $div_css_style . '>
       
        <table>
          
          <tr>         
            <td>
              <input type="radio" name="user_id" value="' . $row['user_id'] . '">      
             <b> # ' . $count . ' </b>
            </td>
          </tr>
          
            <tr>
              <td><b>Username:</b></td>
            </tr>
            <tr>
              <td>
              ' . base64_decode($row['username']) . '
              </td>
            </tr>
   
            <tr>
              <td><b>Password:</b></td>
            </tr>
            <tr><td>
              <input type="password" readOnly="true" id="user_password_id_' . $count . '" value="'
              . Encryption::Decrypt($row['password']) . '"  ' . $password_css_style . '/>
            </td></tr>
          
          
          <tr>
            <td>
              <button class="btn btn-info" id="btn_show_user_password_' . $count . '" onclick="Show_User_Password(\'user_password_id_' . $count . '\',\'btn_show_user_password_' . $count . '\');return false;" >Show User Password</button>
            </td> 
           </tr>
           </table>
           </div>
         ';

       $count++;
     }

}

function DisplayJavaScript()
{
echo '
<script language="javascript">

function Btn_Add()
   {
     DisplayWaitMessage();
     document.getElementById("radius_users_form").setAttribute("action","radius_users.php");
     document.getElementById("post_action").setAttribute("value","add");
   }

function Btn_Edit()
   {
     document.getElementById("radius_users_form").setAttribute("action","radius_users.php");
     document.getElementById("post_action").setAttribute("value","edit");
   }

function Btn_Delete()
   {
     
     swal({
          title: "Please Confirm!", 
          text: "Are you sure you want to delete?", 
          type: "warning",
          showCancelButton: true,
          confirmButtonText: "Delete",
          confirmButtonColor: "#ec6c62"
        }, function (answer) {                      
              if (answer==true)
                {
                  DisplayWaitMessage();
                  document.getElementById("radius_users_form").setAttribute("action","radius_users.php");
                  document.getElementById("post_action").setAttribute("value","delete");
                  document.getElementById("radius_users_form").submit();
                }
                          
         });
                
        //this is needed to ensure dialog does not auto close
        return false;  
   }

function Btn_Save()
   {
     DisplayWaitMessage();
     document.getElementById("radius_users_form").setAttribute("action","radius_users.php");
     document.getElementById("post_action").setAttribute("value","save");
   }

function Btn_Cancel()
   {
     document.getElementById("radius_users_form").setAttribute("action","radius_users.php");
     document.getElementById("post_action").setAttribute("value","cancel");
   }

function Show_User_Password(user_password_id,btn_show_user_password)
{
  var password_type=document.getElementById(user_password_id).type;
  if(password_type=="password")
  {
    document.getElementById(btn_show_user_password).innerHTML="Hide User Password";
    document.getElementById(user_password_id).setAttribute("type","text");
  }
  else
  {
    document.getElementById(btn_show_user_password).innerHTML="Show User Password";
    document.getElementById(user_password_id).setAttribute("type","password");
  }
}

</script>

';
}

?>
