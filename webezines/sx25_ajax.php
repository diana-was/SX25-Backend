<?php
include_once("../config.php");

$action 	= isset($_REQUEST['action'])?trim($_REQUEST['action']):'';
$callback 	= isset($_GET['callback'])?$_GET['callback']:'';

switch ($action)
{
    case 'setBan':	case 'setBanParked':
    				$Banned	= BannedDomain::getInstance($db);
					$domain	= isset($_REQUEST['domain'])?trim(urldecode($_REQUEST['domain'])):'';
					$keyword= isset($_REQUEST['keyword'])?trim(urldecode($_REQUEST['keyword'])):'';
					$reason	= isset($_REQUEST['banned_reason'])?trim(urldecode($_REQUEST['banned_reason'])):'';
					$data 	= array('domain'=>$domain, 'banned_reason'=>$reason, 'keyword'=>$keyword, 'source' => (($action=='setBan')?'SX25':'Parked'));
					$id 	= $Banned->save_banned($data);
					$resp	= $id?'ok':'fail';
					break;
    case 'cheapAdsList':	
    				$CheapAds	= cheapAds::getInstance($db);
					$resp 		= $CheapAds->get_cheapads();
					break;
    case 'cheapAdsSitehostList':
    				$CheapAds	= cheapAds::getInstance($db);
					$resp 		= $CheapAds->get_cheapads_list();
					break;
    default:		$resp		= 'fail';
    				break;	
}
header("Content-Type: application/json;charset=iso-8859-1");
echo $callback . '(' .json_encode($resp). ')';
exit;
?>