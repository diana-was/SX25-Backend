<?php
// force to be api
if (!isset($_REQUEST['api']))
{
	$_REQUEST['api']= 1;
}
// call gen Z	
include_once("../sx25_api.php");
?>