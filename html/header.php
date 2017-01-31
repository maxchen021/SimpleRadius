<?php
require_once("initialize.php");

function ShowHeader($current_menu_name)
{



global $SYSTEM_SETTING;

$basic_setting=$SYSTEM_SETTING["basic_setting_menu"];
$advanced_setting=$SYSTEM_SETTING["advanced_setting_menu"];
$other_setting=$SYSTEM_SETTING["other_setting_menu"];

$mobile_detect = new Mobile_Detect;

echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>'. $SYSTEM_SETTING["title"] .'</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">

  <!--link rel="stylesheet/less" href="less/bootstrap.less" type="text/css" /-->
  <!--link rel="stylesheet/less" href="less/responsive.less" type="text/css" /-->
  <!--script src="js/less-1.3.3.min.js"></script-->
  <!--append ‘#!watch’ to the browser URL, then refresh the page. -->

  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/bootstrap-responsive.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">

  <link href="css/overlay.css" rel="stylesheet">

  <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
  <![endif]-->

  <!-- Fav and touch icons -->
  <link rel="apple-touch-icon-precomposed" sizes="57x57" href="img/favicon/apple-touch-icon-57x57.png" />
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/favicon/apple-touch-icon-114x114.png" />
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/favicon/apple-touch-icon-72x72.png" />
  <link rel="apple-touch-icon-precomposed" sizes="144x144" href="img/favicon/apple-touch-icon-144x144.png" />
  <link rel="apple-touch-icon-precomposed" sizes="60x60" href="img/favicon/apple-touch-icon-60x60.png" />
  <link rel="apple-touch-icon-precomposed" sizes="120x120" href="img/favicon/apple-touch-icon-120x120.png" />
  <link rel="apple-touch-icon-precomposed" sizes="76x76" href="img/favicon/apple-touch-icon-76x76.png" />
  <link rel="apple-touch-icon-precomposed" sizes="152x152" href="img/favicon/apple-touch-icon-152x152.png" />
  <link rel="icon" type="image/png" href="img/favicon/favicon-196x196.png" sizes="196x196" />
  <link rel="icon" type="image/png" href="img/favicon/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/png" href="img/favicon/favicon-32x32.png" sizes="32x32" />
  <link rel="icon" type="image/png" href="img/favicon/favicon-16x16.png" sizes="16x16" />
  <link rel="icon" type="image/png" href="img/favicon/favicon-128.png" sizes="128x128" />
  <meta name="application-name" content="&nbsp;"/>
  <meta name="msapplication-TileColor" content="#FFFFFF" />
  <meta name="msapplication-TileImage" content="img/favicon/mstile-144x144.png" />
  <meta name="msapplication-square70x70logo" content="img/favicon/mstile-70x70.png" />
  <meta name="msapplication-square150x150logo" content="img/favicon/mstile-150x150.png" />
  <meta name="msapplication-wide310x150logo" content="img/favicon/mstile-310x150.png" />
  <meta name="msapplication-square310x310logo" content="img/favicon/mstile-310x310.png" />

  <script type="text/javascript" src="js/jquery.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
  <script type="text/javascript" src="js/scripts.js"></script>

  <script type="text/javascript" src="js/simple_radius.js"></script>

  <!-- SlickNav -->
  <link rel="stylesheet" href="SlickNav/slicknav.css" />
  <script src="SlickNav/jquery.slicknav.min.js"></script>
  
  <!-- sweetalert -->
  <script src="sweetalert/sweetalert.min.js"></script>
  <link rel="stylesheet" type="text/css" href="sweetalert/sweetalert.css"> 

  <script>
  $(document).ready(function(){
      $(\'[data-toggle="tooltip"]\').tooltip();
  });
  </script>';

  // Any mobile device (phones or tablets).
  if ( $mobile_detect->isMobile() ) {
    echo '
    <script>
    	$(function(){
    		$(\'#menu\').slicknav();
    	});
    </script>
    <style>
      .slicknav_menu {
      	display:block;
      }
      #menu {
        display:none;
      }
    </style>
    ';
  }

echo '
</head>
';

echo '
<body>


<div class="overlay" style="display:none;"></div>
<div class="popup" style="display:none;"><br/><br/><br/><b>Processing, please wait....</b></div>
';

// Any mobile device (phones or tablets).
if ( $mobile_detect->isMobile() ) {
  ShowMobileHeader($current_menu_name);
}
else {
  ShowDesktopHeader($current_menu_name);
}

} //end ShowHeader function

function ShowDesktopHeader($current_menu_name)
{
  global $SYSTEM_SETTING;

  $basic_setting=$SYSTEM_SETTING["basic_setting_menu"];
  $advanced_setting=$SYSTEM_SETTING["advanced_setting_menu"];
  $other_setting=$SYSTEM_SETTING["other_setting_menu"];

echo '
  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12">
        <div class="page-header">
          <h1>
            '. $SYSTEM_SETTING["title"] .'
          </h1>
        </div>
      </div>
    </div>
    <div class="row-fluid">
      <div class="span2">
        <ul class="nav nav-list well">
          <li class="nav-header">
            Basic Setting
          </li>
          ';


  //print the basic menu
  foreach($basic_setting as $url=>$menu_name)
  {
    if(strcmp($menu_name, $current_menu_name)==0)
    {
      echo '<li class="active">';
    }
    else
    {
      echo '<li>';
    }
    echo '<a href="' . $url .'">' . $menu_name . '</a>';
    echo '</li>';
  }


          echo '
          <li class="nav-header">
            Advanced Setting
          </li>
          ';

          //print the advanced menu
  foreach($advanced_setting as $url=>$menu_name)
  {
    if(strcmp($menu_name, $current_menu_name)==0)
    {
      echo '<li class="active">';
    }
    else
    {
      echo '<li>';
    }
    echo '<a href="' . $url .'">' . $menu_name . '</a>';
    echo '</li>';
  }

          echo '
          <li class="divider">
          </li>
          ';

          //print the other menu
  foreach($other_setting as $url=>$menu_name)
  {
    if(strcmp($menu_name, $current_menu_name)==0)
    {
      echo '<li class="active">';
    }
    else
    {
      echo '<li>';
    }
    echo '<a href="' . $url .'">' . $menu_name . '</a>';
    echo '</li>';
  }

          echo '
        </ul>
      </div>
      ';

} // end ShowDesktopHeader function

function ShowMobileHeader($current_menu_name)
{
  global $SYSTEM_SETTING;

  $basic_setting=$SYSTEM_SETTING["basic_setting_menu"];
  $advanced_setting=$SYSTEM_SETTING["advanced_setting_menu"];
  $other_setting=$SYSTEM_SETTING["other_setting_menu"];

  echo '<ul id="menu">';

  echo '<li><u><strong>Basic Setting</strong></u></li>';
  //print the basic menu
  foreach($basic_setting as $url=>$menu_name)
  {
    echo '<li>';
    echo '<a href="' . $url .'">' . $menu_name . '</a>';
    echo '</li>';
  }

  echo '<li><u><strong>Advanced Setting</strong></u></li>';
  //print the advanced menu
  foreach($advanced_setting as $url=>$menu_name)
  {
    echo '<li>';
    echo '<a href="' . $url .'">' . $menu_name . '</a>';
    echo '</li>';
  }

  echo '<li><u><strong>Other Setting</strong></u></li>';
  //print the other menu
  foreach($other_setting as $url=>$menu_name)
  {
    echo '<li>';
    echo '<a href="' . $url .'">' . $menu_name . '</a>';
    echo '</li>';
  }


  echo '</ul>';
} //end ShowMobileHeader function

?>
