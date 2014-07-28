<?php
/**
 * called by reports.php, export various reports
 * Author: Gordon Ye, 20 Jan 2011
 */
 
require_once('config.php');

$action = isset($_REQUEST['report'])?$_REQUEST['report']:'active_domains';
$report = Report::getInstance($db);
$extra['profile_id'] = empty($_REQUEST['profile_id'])?'':$_REQUEST['profile_id'];
$extra['account_id'] = empty($_POST['account_id'])?'':$_POST['account_id'];
$extra['datefrom'] = empty($_POST['datefrom'])?'':$_POST['datefrom'];
$extra['dateto'] = empty($_POST['dateto'])?'':$_POST['dateto'];
$output = $report->getReport($action, $extra);
$outputfilename = $action."_".date("d-m-Y").".csv";

header('Content-Type: text/plain; charset=ISO-8859-1');
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$outputfilename\"");
	
echo $output;

?>