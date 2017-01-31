<?php
require_once("initialize.php");

if(!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['ready_to_submit']))
{
  DisplayLoginForm("");
  exit;
}

$username=trim($_POST['username']);
$password=trim($_POST['password']);

$ready_to_submit=trim($_POST['ready_to_submit']);



if(isset($_POST['ready_to_submit']) && $_POST['ready_to_submit']==1)
{
  if(!isset($username) || !isset($password) || $username == "" || $password == "")
  {
    DisplayLoginForm("Please enter the username and password");
    exit;
  }

  $user=new User();
  $user->SetUserID($username);
  $user->SetPassword($password);

  $auth=new Authentication();
  if($auth->AuthenticateUser($user->GetUserID(), $user->GetPassword()))
  {
  	$auth->CreateNewSession($username);
  	header("Location: wireless_routers.php");
  }
  else 
  {
  	DisplayLoginForm("Incorrect username/password");

  }
}
else
{
  DisplayLoginForm("");
}

function DisplayLoginForm($message)
{
  global $SYSTEM_SETTING;

	echo '
	<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
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

    <title>'. $SYSTEM_SETTING["title"] .'</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap/dist/css/bootstrap.css" rel="stylesheet">



    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="bootstrap/assets/js/html5shiv.js"></script>
      <script src="bootstrap/assets/js/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>';

  

echo '
    <div class="container">

      <form class="form-signin" action="login.php" method="post">
        <h2 class="form-signin-heading">' .$SYSTEM_SETTING["title"]. " " .$SYSTEM_SETTING["version"]. '</h2>
        <input type="text" class="form-control" placeholder="Username" autofocus name="username" >
        <input type="password" class="form-control" placeholder="Password" name="password" >
        
        <button class="btn btn-lg btn-primary btn-block" type="submit">Log in</button>

        <input type="hidden" name="ready_to_submit" value="1"> '
         . '<br/><label style="color:red;">' .$message . '</label>' .

     ' </form>

      
    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
  </body>
</html>
';
}


?>