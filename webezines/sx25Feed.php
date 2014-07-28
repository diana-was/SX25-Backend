<?php
include_once("../config.php");
header("Content-Type: application/json;charset=iso-8859-1");

$callback 	= isset($_GET['callback'])?$_GET['callback']:'';
if(!isset($_REQUEST['keyword'])){
	echo $callback.'(' .json_encode('Invalid Query'). ')';
	exit;
}

$action 	= isset($_REQUEST['action'])?trim($_REQUEST['action']):'';
$from 		= isset($_REQUEST['from'])?trim($_REQUEST['from']):'';
$keyword 	= trim($_REQUEST['keyword']);
$start 		= isset($_REQUEST['start'])?trim($_REQUEST['start']):''; 
$quantity 	= isset($_REQUEST['quantity'])?trim($_REQUEST['quantity']):5;
$perpage 	= isset($_REQUEST['perpage'])?$_REQUEST['perpage']:5;
$debug 		= isset($_REQUEST['debug'])?true:false;

$feed = AjaxFeed::getInstance($db,$callback);

switch ($action)
{
	case 'twitter_news':	echo $feed->twitterNews($keyword,$debug);
							break;
							
	case 'yahoo_news' :
	case 'meehive_news':	echo $feed->yahooNews($keyword,$debug);
							break;
							
	case 'loadYahooAnswer':echo $feed->yahooAnswer($keyword,$debug);
							break;
							
	case 'loadGooglePic':	//echo $feed->loadGooglePic($keyword, $start, $quantity);    Google doesn't show URL	
							echo $feed->loadBingPics($keyword, $start, $quantity);
							break;
							
	case 'getHealthBoards':	echo $feed->getHealthBoards($keyword, $perpage);
    						break;
    						
	case 'getAllbusinessArticles':	echo $feed->getAllbusinessArticles($keyword, $perpage);
    						break;
    						
	case 'getAmazonAskville':echo $feed->getAmazonAskville($keyword, $perpage);
    						break;
    						
	case 'getGoogleWebSearch':echo $feed->getGoogleWebSearch($keyword, $perpage, true);
    						break;
    						
	case 'getBingWebSearch':echo $feed->getBingWebSearch($keyword, $perpage, true);
    						break;
    						
	case 'getArticlebaseArticles':echo $feed->getArticlebaseArticles($keyword, $perpage);
    						break;
    						
	case 'getForbesNews':	echo $feed->getForbesNews($keyword, $perpage);
    						break;
    						
    case 'local_relate_sites':
							$Site = Site::getInstance($db);
							$sites = array();
							$keywords = explode(' ',trim($_REQUEST['keyword']));
							$keyword = implode('%',$keywords);
							$siteList = $Site->get_related_domains("%$keyword%",6);
							setSitesList($siteList);
							
							foreach($keywords as $k)
							{
								$siteList = $Site->get_related_domains("%$k%",6);
								setSitesList($siteList);
								
								if (empty($siteList) || !is_array($siteList))
								{
									$siteList = $Site->get_related_domains("%".substr($k,0,strlen($k)-1)."%",6);
									setSitesList($siteList);
								}
							}
							if(isset($_REQUEST['from']) && $_REQUEST['from']=='local')
							echo json_encode($sites);
							  elseif (isset($_GET['callback']))
							echo $_GET['callback'] . '(' .json_encode($sites). ')';
							  else
							foreach($sites as $id => $site)  
								echo "Site: $id ".$site['url'].' keyword: '.$site['k'].'<br>';
							break;
							
    default:				echo $feed->getAboutArticles($keyword, $perpage);
    						break;	
}

function setSitesList($list)
{
	global $sites;
	if(is_array($list)){
		foreach($list as $m)
		{
			$sites[$m['domain_id']] = array('i' => $m['domain_id'],'k' => $m['domain_keyword'],'url' => $m['domain_url']);
		}
	}
	array_unique($sites);
}

exit;
?>