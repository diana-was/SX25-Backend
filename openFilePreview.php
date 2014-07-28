<?php
/**
 * File Open Preview
 * 
 */
require_once 'config.php';
error_reporting(0);
$curl = new SingleCurl();
$link = isset($_REQUEST['link'])?$_REQUEST['link']:'';
if (!empty($link)) {
	$curl->createCurl('post',$link, array());
	echo $curl->__toString();
}
//$curl->displayResponce();
//$curl->printMe();
?>
