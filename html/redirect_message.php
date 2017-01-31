
<?php
require_once("initialize.php");

/*
$auth=new Authentication();
if($auth->CheckUserSession()==false){
  header("Location: login.php");
}
*/

global $SYSTEM_SETTING;

if(!isset($_GET['type']) || $_GET['type'] == "" )
{
  $current_redirect_type = "default";
}
else {
  $current_redirect_type = $_GET['type'];
}

if (isset($_GET['ip']) && $_GET['ip']!="" )
{
  $SYSTEM_SETTING["redirect_message"][$current_redirect_type]["redirect_page"] = "https://" . $_GET['ip'] . "/" . $SYSTEM_SETTING["redirect_message"][$current_redirect_type]["redirect_page"];
}

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
</head>';

echo '
<script language="JavaScript" type="text/javascript">
var count =' . $SYSTEM_SETTING["redirect_message"][$current_redirect_type]["countdown_time"] . '
var redirect="' . $SYSTEM_SETTING["redirect_message"][$current_redirect_type]["redirect_page"] . '"

function countDown(){

   if (count <=0){';
if ( isset($SYSTEM_SETTING["redirect_message"][$current_redirect_type]["final_redirect_message"]) && $SYSTEM_SETTING["redirect_message"][$current_redirect_type]["final_redirect_message"]!="" ) {
  echo 'document.getElementById("timer").innerHTML = "' . $SYSTEM_SETTING["redirect_message"][$current_redirect_type]["final_redirect_message"] . '"';
}
else {
    echo 'window.location = redirect;';
  }

echo '
   }else{
    count--;
    document.getElementById("timer").innerHTML = "' . $SYSTEM_SETTING["redirect_message"][$current_redirect_type]["redirect_message"] . '"
    setTimeout("countDown()", 1000)
   }
   ';

echo '
}
</script>';

echo '
<body>
<div class="container-fluid">
  <div class="row-fluid">
    <div class="span12">
      <div class="page-header">
        <h1>
          ' . $SYSTEM_SETTING["title"] . '
        </h1>
      </div>
    </div>
  </div>';

echo '
<div class="container-fluid">
  <div class="row-fluid">
<div class="span12">
      <div class="hero-unit">

      <h3>' . $SYSTEM_SETTING["redirect_message"][$current_redirect_type]["redirect_title"] . '</h3> <br/>
      <span id="timer">
<script>
 countDown();
</script>
</span>

      </div>
    </div>
  </div>
</div>

';


echo '
</body>
</html>';

//=========================================================================================
?>
