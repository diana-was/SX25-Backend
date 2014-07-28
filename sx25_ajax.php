<?php
require_once('config.php');
require_once('check_user.php');
header("Content-Type: text/html;charset=iso-8859-1");

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$resp = 0; // default responce 0. False

switch ($action)
{
	case 'approve_article' :	
		$aid 		=  isset($_REQUEST['aid'])?$_REQUEST['aid']:'';
		$approved 	=  isset($_REQUEST['approved'])?$_REQUEST['approved']:0;
		
		if(!empty($aid)){
			$Article = Article::getInstance($db);
			$return = $Article->approveArticle($aid,$approved);
			if($return)
				$resp = 1;
		}
		echo $resp;
		break;
	
	case 'approve_question':
		$qid =  isset($_REQUEST['qid'])?$_REQUEST['qid']:'';
		$approved 	=  isset($_REQUEST['approved'])?$_REQUEST['approved']:0;
				
		if(!empty($qid)){
			$QA = QuestionAnswer::getInstance($db);
			$return = $QA->approveQuestion($qid,$approved);
			if($return)
				$resp = 1;
		}
		echo $resp;
		break;
		
	case 'approve_image':
		$mid =  isset($_REQUEST['mid'])?$_REQUEST['mid']:'';
		$approved 	=  isset($_REQUEST['approved'])?$_REQUEST['approved']:1;
				
		if(!empty($mid)){
			$ImageLibrary = ImageLibrary::getInstance($db);
			$return = $ImageLibrary->approveImage($mid,$approved);
			if($return)
				$resp = 1;
		}
		echo $resp;
		break;
		
	case 'scrape_article':
		$keyword 		= !empty($_REQUEST['keyword'])?trim($_REQUEST['keyword']):'';
		$save_keyword = !empty($_REQUEST['save_keyword'])?trim($_REQUEST['save_keyword']):'';
		$domain_id 		= !empty($_REQUEST['domain_id'])?trim($_REQUEST['domain_id']):'';
		$domain_url 	= !empty($_REQUEST['domain_url'])?trim($_REQUEST['domain_url']):'';
		$limitnum 		= !empty($_REQUEST['limitnum'])?trim($_REQUEST['limitnum']):1;
		$article_source = !empty($_REQUEST['article_source'])?trim($_REQUEST['article_source']):'ehow';
		
		//if (!empty($keyword) && (!empty($domain_id) || !empty($domain_url)))
		if (!empty($keyword))
		{
			$Article = Article::getInstance($db);
			$article_id = $Article->scrape_article($keyword, $domain_id, $domain_url, 1, $limitnum, 0, 0, $article_source, $save_keyword);
			if(!empty($article_id))
				$resp = 1;
		}
		echo $resp;
		break;
		
	case 'scrape_directory':
		$keyword 		= !empty($_REQUEST['keyword'])?trim($_REQUEST['keyword']):'';
		$scrape_amount 	= !empty($_REQUEST['limitnum'])?trim($_REQUEST['limitnum']):1;
		$Directory = Dty::getInstance($db);
		
		if (!empty($keyword))
		{
			if(!($set = $Directory->check_directory_set($keyword,$scrape_amount)))
			{
				$dty_amount = $Directory->scrape_directory($keyword,$scrape_amount,0,$directory_id);
				if(!empty($dty_amount))
				{
					$returnStr = "$keyword : scrape directory $dty_amount";
				}
				else
				{
					$returnStr =  "$keyword : No directory found";
				}
			}
			else
			{
				$returnStr =  "$keyword : exist $set.<br />";
			}
		}
		else 
			$returnStr = 'Missing parameters';
		echo $returnStr;
		break;
		
	case 'scrape_qa':
		$keyword 		= !empty($_REQUEST['keyword'])?trim($_REQUEST['keyword']):'';
		$scrape_amount 	= !empty($_REQUEST['limitnum'])?trim($_REQUEST['limitnum']):1;
		$save_keyword = !empty($_REQUEST['save_keyword'])?trim($_REQUEST['save_keyword']):'';
		
		if (!empty($keyword))
		{
			$QA = QuestionAnswer::getInstance($db);
			if(!($set = $QA->check_qa_set($keyword,$scrape_amount)))
			{
				$qa_amount = $QA->scrape_qa($keyword,$scrape_amount,0,$save_keyword);
				if(!empty($qa_amount))
				{
					$returnStr = "$keyword : scrape question/answer $qa_amount";
				}
				else
				{
					$returnStr =  "$keyword : No question/answer found";
				}
			}
			else
			{
				$returnStr =  "$keyword : exist $set.<br />";
			}
		}
		else 
			$returnStr = 'Missing parameters';
		echo $returnStr;
		break;
		
	case 'default_content': 
		$account_id = isset($_REQUEST['account_id'])?$_REQUEST['account_id']:'';
		$content = isset($_REQUEST['content'])?strtoupper(trim($_REQUEST['content'])):'';
		set_bulk_content($account_id, $content);
	break;
}






function set_bulk_content($account_id, $content)
{
	Global $db;
	$count = 1;
	$Menu = Menu::getInstance($db); 
	$Site = Site::getInstance($db); 
		
	if($account_id != '')
	{
		$pResults = $Site->get_domain_data_list('account',$account_id);
		echo "<h3>There are ".sizeof($pResults)." domains in this account.</h3> ";
		
		foreach($pResults as $pRow) 
		{
			$keyword 	= $pRow['domain_keyword'];
			$domain_id 	= $pRow['domain_id'];
			$domain_url = strtolower(trim($pRow['domain_url']));
			
			$MM = get_missed_module_element($pRow);
			$moduleList = $MM['module_list'];
			$missingModules = $MM['missingModules'];
			if(sizeof($missingModules)==0)
			{
				echo "<br /><br /><b>".$count++.". $domain_url - $keyword </b><br /> "; 
				echo "no missing module element.<br /> ";
			}
			else
			{
				echo "<br /><br /><b>".$count++.". $domain_url - $keyword  </b><br /> ";   
				foreach($missingModules as $k=>$m)
				{
					echo "Required ".$m.' '.strtoupper($k)." <br />";
					switch(strtoupper($k)):
						case('ARTICLE'):
								if($content=='ALL' || $content=='ARTICLE')
								{
									$a = populateArticle($pRow,$m);
									if(empty($a) || $a<$m)
										echo ' ... <span style="color:red;">Required '.$m.' Articles, but only populate '.$a.'. </span><br />';
									else
										echo " ... Sucessfully populating $m Articles. <br />";
								}
							break;
						case('DIRECTORY'):
								if($content=='ALL' || $content=='DIRECTORY')
								{ 								
									$d = populateDirectory($pRow,$m);
									if(empty($d) || $d<$m)
										echo ' ... <span style="color:red;">Required '.$m.' Directories, but only populate '.$d.'. </span><br />';
									else
										echo " ... Sucessfully populating $m Directories. <br />";
								}
							break;
						case('QUESTION'):
								if($content=='ALL' || $content=='QUESTION')
								{ 								
									$q = populateQuestion($pRow,$m);
									if(empty($q) || $q<$m)
										echo ' ... <span style="color:red;">Required '.$m.' Questions, but only populate '.$q.'. </span><br />';
									else
										echo " ... Sucessfully populating $m Questions. <br />";
								}
							break;
						case('MENU_IMAGE'):
								if($content=='ALL' || $content=='MENU_IMAGE') 
								{
									$missing = populateMenuImage($pRow,$m);
									if (count($missing) > 0){
										foreach ($missing as $v)
											echo " ... <span style='color:red;'>fail to populate Image in ".$v.". </span> <br />";
									}
									else
										echo " ... Sucessfully populating $m Images. <br />";
										
								}
							break;
						case('EVENT'):
								if($content=='ALL' || $content=='EVENT') 
								{
									$p = populateEvent($pRow,$m);
									if(empty($p) || $p<$m)
										echo ' ... <span style="color:red;">Required '.$m.' Events, but only populate '.$p.'. </span><br />';
									else
										echo " ... Sucessfully populating $m Events. <br />";
								}
							break;
							
						default:
								$returnStr .= '<li id=\''.$k.'_'.$domain_id.'\'>'.$m.' '.$k.' required.</a></li>';
								break;
					endswitch;
				} 
			}
		} 
	}		
}



function get_missed_module_element($domainInfo=array())
{
	global $db;
	static $layoutMod = array();
	$Layout	= new Layout();
	$layout_id = empty($domainInfo['layout_id'])?'0':$domainInfo['layout_id'];
	
	if (!array_key_exists ($layout_id, $layoutMod))
	{
		$layoutMod[$layout_id] = $Layout->getLayoutModules($layout_id);
		ksort($layoutMod[$layout_id]);
	}
	$modules 		= $layoutMod[$layout_id];
	$domain_id 		= trim($domainInfo['domain_id']);
	$domain_url 	= trim($domainInfo['domain_url']);
	$domain_keyword = trim($domainInfo['domain_keyword']);
	
	$Site = Site::getInstance($db);
	$missingModules = $Site->get_missed_module_element($modules, $domain_id, $domain_url, $domain_keyword);
	
	$moduleList= '';
	foreach($modules as $k=>$m){
		$moduleList.= $k.($m > 0?" = $m":'').'<BR />';
	}
		
	return array('module_list' => $moduleList, 'missingModules' => $missingModules);
}


function populateMenuImage($domainInfo=array(),$scrape_amount)
{	       			
	global $db;
	$keyword = $domainInfo['domain_keyword'];
	$layout_id = empty($domainInfo['layout_id'])?'0':$domainInfo['layout_id'];
	$domain_id = empty($domainInfo['domain_id'])?'0':$domainInfo['domain_id'];
	$Layout	= new Layout();
	$Library = ImageLibrary::getInstance($db);
	$Image = Image::getInstance($db);
	/**************** get required images in layout **********************/
	$requireImg = $Layout->get_image_required($layout_id);
	$requireImg = Image::imgNameSwitch($requireImg);
	/**************** get existing images in database **********************/
	$existImg = $Image->getImageLocationArray($domain_id); 
	
	$scrapeImg = array();
	foreach($requireImg as $k=>$v){
		if(!in_array($v, $existImg))
			$scrapeImg[] = $v;
	}
	
	// Get image from the library
	$images = $Library->getKeywordImages($keyword, $scrape_amount, true, $domain_id);
	if (is_array($images) and count($images) > 0)
	{
		foreach($images as $k=>$v)
		{
			$location = array_pop($scrapeImg);
			$Image->setImageLibrary($v['image_library_id'], $domain_id, $location);
		}
	}
	// Get extra images from Google if need
	$existing = is_array($images)?count($images):0;
	$missing  = $scrape_amount - $existing;
	if ($missing > 0)
	{
		$images = $Library->getGoogleImageSearch($keyword, $missing+2, $existing);
		foreach($images as $k=>$v)
		{
			if (count($scrapeImg) == 0)
				break;
			if (!empty($v['content_photo_src']))
			{
				$location = array_pop($scrapeImg);
				$Image->setImage($v['content_photo_src'], $domain_id, $location, $keyword);
			}
		}
	}

	return $scrapeImg;
}


function populateArticle($domainInfo=array(),$m)
{	       			
	global $db;
	global $moduleMinArticle;
	
	$keyword = $domainInfo['domain_keyword'];
	$domain_id = $domainInfo['domain_id'];
	$domain_url = $domainInfo['domain_url'];
	$source = array('ehow','articleBase','EzineArticles');
	$Article = Article::getInstance($db);
	$initialNum = $Article->count_articles($domain_url);
	$articleNum = $initialNum;
	
	for($i=0; $i<sizeof($source); $i++)
	{
		$article_id = $Article->scrape_article($keyword, $domain_id, $domain_url, 1, $m, 0, 0, $source[$i]);
		$articleNum = empty($article_id)?$articleNum:$Article->count_articles($domain_url);
		$amount = $articleNum - $initialNum;							
		
		if($amount >= $m)
			break;
	}
	
	return $amount;	    	
}


function populateDirectory($domainInfo=array(),$scrape_amount)
{	
	global $db;
	
	$keyword = $domainInfo['domain_keyword'];
	$Directory = Dty::getInstance($db);
	$dty_amount = $Directory->scrape_directory($keyword,$scrape_amount,0,$directory_id);
	$dty_amount = empty($dty_amount)?0:$dty_amount;
	return $dty_amount;
}

function populateQuestion($domainInfo=array(),$scrape_amount)
{	       			
	global $db;
	
	$keyword = $domainInfo['domain_keyword'];
	$QA = QuestionAnswer::getInstance($db); 
	$q_amount = $QA->scrape_qa($keyword,$scrape_amount);
	$q_amount = empty($q_amount)?0:$q_amount;
	return $q_amount;
}


function populateEvent($domainInfo=array(),$scrape_amount)
{
	global $db;
	
	$keyword = $domainInfo['domain_keyword'];
	$Event = Event::getInstance($db); 
	$event_amount = $Event->scrape_event($keyword,$scrape_amount);
	$event_amount = empty($event_amount)?0:$event_amount;
	return $event_amount;
}


?>
