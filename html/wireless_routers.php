
<?php
require_once("initialize.php");

$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}




$router_name="";
$router_ip="";
$radius_secret="";
$radius_secret2="";
$form_message="";
$current_router_id="";
$additional_router_settings="";

if(isset($_POST['ready_to_submit']) && $_POST['ready_to_submit']==1)
{
  if(isset($_POST['post_action']))
  {
    if(strcmp($_POST['post_action'],'add')==0)
    {
      AddRouter();
    }
    elseif(strcmp($_POST['post_action'],'delete')==0)
    {
      DeleteRouter();
    }
    elseif(strcmp($_POST['post_action'],'edit')==0)
    {
      EditRouter();
    }
    elseif(strcmp($_POST['post_action'],'save')==0)
    {
      SaveEditedRouter();
    }

  }
}

ShowHeader("Wireless Routers");

DisplayJavaScript();

echo '
<div class="span10">
      <div class="hero-unit">
        <form class="form-horizontal" name="wireless_routers_form" id="wireless_routers_form" action="wireless_routers.php" method="post">
<fieldset>

<!-- Form Name -->
<legend><b>Wireless Routers</b> <small id="form_message" style="color:red;">' . $form_message . '</small></legend>';


DisplayInput($router_name,$router_ip,$radius_secret,$radius_secret2,$additional_router_settings);

if(isset($_POST['post_action']) && strcmp($_POST['post_action'],'edit')==0)
{
  DisplaySaveCancelButton();
}
else
{
  DisplayAddEditDeleteButton();
  $mobile_detect = new Mobile_Detect;
  if ( $mobile_detect->isMobile() ) {
      DisplayRouterListForMobile();
  }
  else
  {
    DisplayRouterList();
  }
  
}




echo '
</fieldset>
<input type="hidden" name="ready_to_submit" value="1">
<input type="hidden" name="post_action" id="post_action" value="">
<input type="hidden" name="current_router_id" id="current_router_id" value="' . $current_router_id . '">
</form>

      </div>
    </div>
  </div>
</div>
';

ShowFooter();



function AddRouter()
{

  global $router_name;
  global $router_ip;
  global $radius_secret;
  global $radius_secret2;
  global $additional_router_settings;

  $router_name=trim($_POST['router_name']);
  $router_ip=trim($_POST['router_ip']);
  $radius_secret=trim($_POST['radius_secret']);
  $radius_secret2=trim($_POST['radius_secret2']);
  $additional_router_settings=trim($_POST['additional_router_settings']);

  global $form_message;


  if(ValidateInput()==false)
  {
    return;
  }

  global $CURRENT_DB;

  $router_name=base64_encode($router_name);
  $router_ip=base64_encode($router_ip);
  $radius_secret=(new Encryption())->Encrypt($radius_secret);
  $additional_router_settings=base64_encode($additional_router_settings);


  $query="select * from wireless_routers where router_ip='" . $router_ip . "'";
  $result=$CURRENT_DB->DBSelectQuery($query);

  if($row=$result->fetchArray())
  {
    $form_message="Router with this ip already exist";
    $router_name=base64_decode($router_name);
    $router_ip=base64_decode($router_ip);
    $radius_secret=(new Encryption())->Decrypt($radius_secret);
    $additional_router_settings=base64_decode($additional_router_settings);
    return;
  }

  $query="insert into wireless_routers (router_name,router_ip,radius_secret,additional_settings) values ("
    . "'" . $router_name . "', "
    . "'" . $router_ip . "', "
    . "'" . $radius_secret . "', "
    . "'" . $additional_router_settings . "') ";

  $result=$CURRENT_DB->DBUpdateQuery($query);

  if($result)
  {
    $form_message="Router added successfully";
    $router_name="";
    $router_ip="";
    $radius_secret="";
    $radius_secret2="";
    $additional_router_settings="";

    (new FreeRadius())->CreateClientConfig();

  }
  else
  {
    $form_message="There's an error adding the router";

  }

}

function DeleteRouter()
{
  $router_id=trim($_POST['router_id']);

  global $form_message;

  if(!isset($router_id) || $router_id=="" || !is_numeric($router_id))
  {
    $form_message="Please select a router from the list to delete";
    return;
  }

  $query="delete from wireless_routers where router_id=" . $router_id;

  global $CURRENT_DB;

  $result=$CURRENT_DB->DBUpdateQuery($query);

/*
  if($CURRENT_DB->GetCount('wireless_routers')<=0)
  {
    $CURRENT_DB->ResetCount('wireless_routers');
  }
*/

  if($result)
  {
    $form_message="Router deleted successfully";

    (new FreeRadius())->CreateClientConfig();

  }
  else
  {
    $form_message="There's an error deleting the router";

  }


}

function EditRouter()
{
  $router_id=trim($_POST['router_id']);

  global $form_message;

  if(!isset($router_id) || $router_id=="" || !is_numeric($router_id))
  {
    $form_message="Please select a router from the list to edit";
    $_POST['post_action']="";
    return;
  }

  $query="select * from wireless_routers where router_id=" . $router_id;

  global $CURRENT_DB;

  $result=$CURRENT_DB->DBSelectQuery($query);

  if($row=$result->fetchArray())
  {
    global $router_name;
    global $router_ip;
    global $radius_secret;
    global $radius_secret2;
    global $current_router_id;
    global $additional_router_settings;

    $router_name=base64_decode($row['router_name']);
    $router_ip=base64_decode($row['router_ip']);
    $radius_secret=(new Encryption())->Decrypt($row['radius_secret']);
    $radius_secret2=(new Encryption())->Decrypt($row['radius_secret']);
    $current_router_id=$row['router_id'];
    $additional_router_settings=base64_decode($row['additional_settings']);
  }
  else
  {
    $form_message="There's a problem editing the selected router";
    $_POST['post_action']="";
    return;
  }

}

function SaveEditedRouter()
{


  global $router_name;
  global $router_ip;
  global $radius_secret;
  global $radius_secret2;
  global $current_router_id;
  global $additional_router_settings;

  $current_router_id=trim($_POST['current_router_id']);
  $router_name=trim($_POST['router_name']);
  $router_ip=trim($_POST['router_ip']);
  $radius_secret=trim($_POST['radius_secret']);
  $radius_secret2=trim($_POST['radius_secret2']);
  $additional_router_settings=trim($_POST['additional_router_settings']);

  global $form_message;

  if(!isset($current_router_id) || $current_router_id=="" || !is_numeric($current_router_id))
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

  $router_name=base64_encode($router_name);
  $router_ip=base64_encode($router_ip);
  $radius_secret=(new Encryption())->Encrypt($radius_secret);
  $additional_router_settings=base64_encode($additional_router_settings);

  $query="select * from wireless_routers where router_ip='" . $router_ip . "' and router_id!=" . $current_router_id;
  $result=$CURRENT_DB->DBSelectQuery($query);

  if($row=$result->fetchArray())
  {
    $form_message="Router with this ip already exist";
    $_POST['post_action']="edit";
    $router_name=base64_decode($router_name);
    $router_ip=base64_decode($router_ip);
    $radius_secret=(new Encryption())->Decrypt($radius_secret);
    $additional_router_settings=base64_decode($additional_router_settings);
    return;
  }

  $query="update wireless_routers set router_name='"
  . $router_name . "', router_ip='"
  . $router_ip . "', radius_secret='"
  . $radius_secret . "', additional_settings='"
  . $additional_router_settings . "' where router_id=" . $current_router_id;



  $result=$CURRENT_DB->DBUpdateQuery($query);

  if($result)
  {
    $form_message="Router change saved successfully";
    $router_name="";
    $router_ip="";
    $radius_secret="";
    $radius_secret2="";
    $current_router_id="";
    $additional_router_settings="";

    $_POST['post_action']="";

    (new FreeRadius())->CreateClientConfig();

  }
  else
  {
    $form_message="There's an error saving the change";
    $_POST['post_action']="edit";

  }
}

function ValidateInput()
{
  global $router_name;
  global $router_ip;
  global $radius_secret;
  global $radius_secret2;
  global $additional_router_settings;

  global $form_message;

  if(!isset($router_name) || $router_name == "" )
  {

    $form_message="Please enter the router name";
    return false;
  }

  if(!isset($router_ip) || $router_ip == "" )
  {

    $form_message="Please enter the router ip";
    return false;
  }

  if(!filter_var($router_ip, FILTER_VALIDATE_IP))
  {
    $form_message="Please enter a valid router ip";
    return false;
  }

  if(!isset($radius_secret) || $radius_secret == "" )
  {

    $form_message="Please enter the radius secret";
    return false;
  }

  if(!isset($radius_secret2) || $radius_secret2 == "" )
  {

    $form_message="Please enter the radius secret confirmation";
    return false;
  }

  if(strcmp($radius_secret,$radius_secret2)!=0)
  {

    $form_message="Radius secret do not match";
    return false;
  }

  return true;
}

function DisplayInput($router_name,$router_ip,$radius_secret,$radius_secret2,$additional_router_settings)
{
  $mobile_detect = new Mobile_Detect;
  if ( $mobile_detect->isMobile() ) {
      $textarea_width_style="";
  }
  else
  {     
      $textarea_width_style='style="width:270px;"';
  }

  echo '


  <!-- Text input-->
<div class="control-group">
  <label class="control-label" for="router_name" data-toggle="tooltip" title="Name used to identify the router such as Router1" >Router Name</label>
  <div class="controls">
    <input id="router_name" name="router_name" type="text" value="' . $router_name . '" class="input-xlarge" maxlength="30">

  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="router_ip" data-toggle="tooltip" title="The IP address of the router">Router IP</label>
  <div class="controls">

        <input id="router_ip" name="router_ip" type="text" value="' . $router_ip . '" class="input-xlarge">

  </div>
</div>



<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="radius_secret" data-toggle="tooltip" title="Radius secret (passphrase) to be shared between radius server and router">Radius Secret</label>
  <div class="controls">
    <input id="radius_secret" name="radius_secret" type="password" value="' . $radius_secret . '" class="input-xlarge" maxlength="30">

  </div>
</div>

<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="radius_secret2" data-toggle="tooltip" title="Radius secret (passphrase) to be shared between radius server and router">Radius Secret Confirmation</label>
  <div class="controls">
    <input id="radius_secret2" name="radius_secret2" type="password" value="' . $radius_secret2 . '" class="input-xlarge" maxlength="30">

  </div>
</div>


<!-- Textarea -->
<div class="control-group">
  <label class="control-label" for="additional_router_settings">Additional Settings</label>
  <div class="controls" >                     
    <textarea id="additional_router_settings" name="additional_router_settings" rows="2" ' .$textarea_width_style. '>' . $additional_router_settings . '</textarea>
  </div>
</div>



';
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

function DisplayRouterList()
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
              Name
            </th>
            <th>
              IP Address
            </th>
            <th>
              Radius Secret
            </th>
            <th>
              Additional Settings
            </th>
            <th>

            </th>
          </tr>
        </thead>
        <tbody> ';

     global $CURRENT_DB;

  $query="select * from wireless_routers";

  $result=$CURRENT_DB->DBSelectQuery($query);
  $count=1;
  $css_style_class="";
  $css_style="";
    while($row = $result->fetchArray())
     {
      if($count%2==0)
      {
        $css_style_class='class="info"';
        $css_style='style="border: none;background-color: #d9edf7;"';
        $textarea_css_style='style="border: none;background-color: #d9edf7;overflow-y: scroll;"';
      }
      else
      {
        $css_style_class="";
        $css_style='style="border: none;"';
        $textarea_css_style='style="border: none;overflow-y: scroll;"';
      }

       echo '<tr ' . $css_style_class . ' >';

       echo '
            <td>
              <input type="radio" name="router_id" value="' . $row['router_id'] . '">
            </td>
            <td>
              ' . $count . '
            </td>
            <td>
              ' . base64_decode($row['router_name']) . '
            </td>
            <td>
              ' . base64_decode($row['router_ip']) . '
            </td>
            <td>

              <input type="password" readOnly="true" id="radius_secret_id_' . $count . '" value="'
              . (new Encryption())->Decrypt($row['radius_secret']) . '" ' . $css_style .'/>
            </td>

            <td>

              <textarea readonly id="additional_router_settings" rows="2" ' .$textarea_css_style .'>'
              . base64_decode($row['additional_settings']) . '</textarea>
            </td>

             <td>
            <button class="btn btn-info" id="btn_show_radius_secret_' . $count . '" onclick="Show_Radius_Secret(\'radius_secret_id_' . $count . '\',\'btn_show_radius_secret_' . $count . '\');return false;" >Show Radius Secret</button>
            </td>

          </tr>';

       $count++;
     }

          echo '
        </tbody>
      </table>';
}


function DisplayRouterListForMobile()
{

     global $CURRENT_DB;

  $query="select * from wireless_routers";

  $result=$CURRENT_DB->DBSelectQuery($query);
  $count=1;
  $css_style_class="";
  $css_style="";
    while($row = $result->fetchArray())
     {
      if($count%2==1)
      {
        $div_css_style='style="padding: 15px;background-color: #d9edf7;"';
        $password_css_style='style="border: none;background-color: #d9edf7;"';
        $textarea__css_style='style="border: none;background-color: #d9edf7;overflow-y: scroll;"';
      }
      else
      {
        $div_css_style='style="padding: 15px;background-color: #e6fff2;"';
        $password_css_style='style="border: none;background-color: #e6fff2;"';
        $textarea__css_style='style="border: none;background-color: #e6fff2;overflow-y: scroll;"';
      }
   
       echo '
        
        <div ' . $div_css_style . '>
       
        <table>
          
          <tr>         
            <td>
              <input type="radio" name="router_id" value="' . $row['router_id'] . '">
             <b> # ' . $count . ' </b>
            </td>
          </tr>
          
            <tr>
              <td><b>Name:</b></td>
            </tr>
            <tr>
              <td>
              ' . base64_decode($row['router_name']) . '
              </td>
            </tr>
            
            <tr>
              <td><b>IP Address:</b></td>
            </tr>
            <tr>
              <td>
              ' . base64_decode($row['router_ip']) . '
              </td>
            </tr>
   
            <tr>
              <td><b>Radius Secret:</b></td></tr>
            <tr>
            <td>
              <input type="password" readOnly="true" id="radius_secret_id_' . $count . '" value="'
              . (new Encryption())->Decrypt($row['radius_secret']) . '"  ' . $password_css_style . '/>
            </td>
            </tr>

            <tr>
              <td><b>Additional Settings:</b></td>
            </tr>
            <tr><td>
            <textarea readonly id="additional_router_settings" rows="2" ' .$textarea__css_style .'>'
              . base64_decode($row['additional_settings']) . '</textarea>
            </td></tr>
          
          
          <tr>
            <td>
              <button class="btn btn-info" id="btn_show_radius_secret_' . $count . '" onclick="Show_Radius_Secret(\'radius_secret_id_' . $count . '\',\'btn_show_radius_secret_' . $count . '\');return false;" >Show Radius Secret</button>
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
     document.getElementById("wireless_routers_form").setAttribute("action","wireless_routers.php");
     document.getElementById("post_action").setAttribute("value","add");
   }

function Btn_Edit()
   {
     document.getElementById("wireless_routers_form").setAttribute("action","wireless_routers.php");
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
                  document.getElementById("wireless_routers_form").setAttribute("action","wireless_routers.php");
                  document.getElementById("post_action").setAttribute("value","delete");
                  document.getElementById("wireless_routers_form").submit();
                }
                          
         });
                
        //this is needed to ensure dialog does not auto close
        return false;  
   }

function Btn_Save()
   {
     DisplayWaitMessage();
     document.getElementById("wireless_routers_form").setAttribute("action","wireless_routers.php");
     document.getElementById("post_action").setAttribute("value","save");
   }

function Btn_Cancel()
   {
     document.getElementById("wireless_routers_form").setAttribute("action","wireless_routers.php");
     document.getElementById("post_action").setAttribute("value","cancel");
   }

function Show_Radius_Secret(router_secret_id,btn_show_radius_secret)
{
  var password_type=document.getElementById(router_secret_id).type;
  if(password_type=="password")
  {
    document.getElementById(btn_show_radius_secret).innerHTML="Hide Radius Secret";
    document.getElementById(router_secret_id).setAttribute("type","text");
  }
  else
  {
    document.getElementById(btn_show_radius_secret).innerHTML="Show Radius Secret";
    document.getElementById(router_secret_id).setAttribute("type","password");
  }
}


</script>

';
}

?>
