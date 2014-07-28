<?php
	date_default_timezone_set('UTC');
	ini_set('memory_limit', '200M');
	include_once("C:/PHPprojects/SX25/config.php");

	// Process the request
	if (RestRequest_Class::checkAuth($config->AuthRealm,$config->users)) 
	{
		$api = APIMethods_Class::getInstance($db);
		$api->processRequest();
	}
	else
	{
		$data = new RestRequest_Class();
		$data->sendResponse(401);
	}
	
?>
