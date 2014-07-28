<?php
/*
Login check
*/
$user = new User();

if ( !$user->is_loaded() )
{
	header("Location:login.php");
}
?>