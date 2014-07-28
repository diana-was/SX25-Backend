<?php
/**
 * Login page
 * Author: Archie Huang on 13/03/2009
**/
$pageCat = 'Login';
require_once('config.php');
$user = new User();
if ( !$user->is_loaded() ) :

	if (isset($_POST['uname']) && isset($_POST['pwd']))
	{
	  $remember = isset($_POST['remember'])?$_POST['remember']:false;
	  if ( !$user->login($_POST['uname'],$_POST['pwd'],$remember )){
	    echo 'Wrong username and/or password';
	  }else{
	    header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	  }
	}
?>
	<h1>Login</h1>
	<p><form method="post" action="login.php" />
	 username: <input type="text" name="uname" /><br /><br />
	 password: <input type="password" name="pwd" /><br /><br />
	 Remember me? <input type="checkbox" name="remember" value="1" /><br /><br />
	 <input type="submit" value="login" />
	</form>
	</p>
<?php 
else :
	header("Location: index.php");

endif;
?>