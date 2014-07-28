<?php
set_time_limit(0);
date_default_timezone_set('America/Los_Angeles');

Global $appPath;
$appPath = pathinfo(__FILE__,PATHINFO_DIRNAME).'/';

// Magic class load : load the classes -- try to start the clean up of the code
function __autoload($class_name) {
	Global $appPath;
	//TODO for portability this has been move form the config.ini to here
	// Global $classes;
	$classes['db_class']	= "dbClass.php";
	$classes['ftp_base'] 	= "ftpClass.php";
	$classes['simple_html_dom']	= "simple_html_dom.php";
	
	if ($class_name == 'ftp') {
		$mod_sockets=TRUE;
		if (!extension_loaded('sockets')) {
			$prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
			
			$os = strtolower(php_uname ("s"));
			if (strpos($os, 'windows') === false) {
				if(!@dl($prefix . 'sockets.' . PHP_SHLIB_SUFFIX)) 
					$mod_sockets=FALSE;
			} else {
				$mod_sockets=FALSE;
			}
		}
		$class_file = "ftpClass".($mod_sockets?"Sockets":"Pure").".php";
	} elseif (isset($classes[$class_name])) {
			$class_file = $classes[$class_name];
	} else {
		$class_file = str_replace($class_name[0], strtolower($class_name[0]), $class_name).'Class.php';
	}
	
	$class_file_name = $appPath.'classes/'.$class_file;
	if (is_file($class_file_name)) {
		require_once ($class_file_name);
	} else {
		echo 'No file found by outoload to load the class '.$class_name;
	}
}

// Get the data from the URL 
$controller = Controller::getInstance($appPath);
//$controller->printMe();

// Testing Localhost
if ((($controller->address == '127.0.0.1') || stripos($controller->server_name, 'localhost') !== false) && ($controller->system == 'WINDOWS')) {
	define('APPLICATION_ENVIRONMENT', 'TESTLOCAL');
	define('TESTING_DOMAIN', 'kidsinsurances.net');
	error_reporting(E_ALL);
} elseif (stripos($controller->server_name, 'tb.princetonit.com') !== false) { // testbed server1
	define('APPLICATION_ENVIRONMENT', 'TESTING');
	define('TESTING_DOMAIN', 'kidsinsurances.net');
	error_reporting(E_ALL);
} else {	// Production
	define('APPLICATION_ENVIRONMENT', 'PRODUCTION');	
	if (isset($_REQUEST['debug']))
		error_reporting(E_ALL);
	else
		error_reporting(0);
}

// load configuration file
$config = Config::getInstance($appPath.'config/config.ini',APPLICATION_ENVIRONMENT);
$config->globalizeProperties();
//$config->printMe();

$db = new db_class;
if (!$db->connect($dbserver, $dbuser, $dbpass, $dbname, true)) 
$db->print_last_error(false);

?>