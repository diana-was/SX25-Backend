<?php
/**
 * Open File Display for Parked
 * Author: Diana De vargas 
 * Created: 26/10/2011
 */
require_once('../config.php');

// Required variables
$action 	= !empty($_REQUEST['action'])?trim($_REQUEST['action']):'';
$domain 	= !empty($_REQUEST['domain']) ?trim(str_ireplace('www.','',$_REQUEST['domain'])) : '';
$keyword 	= !empty($_REQUEST['keyword'])?$_REQUEST['keyword'] : (isset($_REQUEST['Keyword'])?$_REQUEST['Keyword'] : '');
$quantity 	= !empty($_REQUEST['quantity'])?trim($_REQUEST['quantity']):5;
// Optional
$showtype 	= !empty($_REQUEST['showtype'])?$_REQUEST['showtype'] : 0;	// layout to use if html output
$id 		= !empty($_REQUEST['id'])?$_REQUEST['id'] : '';				// to get a specifit item info
$start 		= isset($_REQUEST['start'])?trim($_REQUEST['start']):''; 	// used if requesting next lot of items  
$callback 	= !empty($_GET['callback'])?$_GET['callback']:'';			// used when required json output
// for directory and qa
$alterkw	= !empty($_REQUEST['alt_keyword'])?$_REQUEST['alt_keyword'] : '';
// for ecommerce
$source 	= !empty($_REQUEST['source']) ? $_REQUEST['source']:array('amazon','shopzilla');
$category 	= !empty($_REQUEST['category']) ? $_REQUEST['category']:'All';

$kws = array("privacy page", "contactus page", "privacy_page", "contactus_page", "list_directory", "list directory","list_directory_parked");
if(in_array($keyword, $kws))
{
	echo getpage($keyword,$domain,$db);
	exit;
}

$Site = ParkedDomain::getInstance($db);
$siteInfo = $Site->get_domain_info_name($domain);

$alterkw = ($siteInfo && (empty($alterkw) || ($alterkw == 'other')))?$siteInfo['domain_keyword']:$alterkw;
$keyword = empty($keyword)?$alterkw:$keyword;
switch ($action)
{
	case 'question_answer' :
						$lyModuleId = 11;
						$avatar = 'http://webezines.kwithost.com/theme1/images/avatar.jpg';
						$moduleCode = '';
						$modObj = QuestionAnswer::getInstance($db);
						if(empty($id))
							$QAs = $modObj->getQuestionsByKeyword($keyword, $start, $quantity, $alterkw, $avatar);
						else
							$QAs = $modObj->getQuestionById($id,$avatar);
						
						if (!empty($callback))
						{
							echo $callback.'('.json_encode($QAs).')';
						}
						elseif ($showtype == 1)  
						{
							echo json_encode($QAs);
						}
						else  
						{	// Display HTML code
							echo getHtmlCode($showtype,$lyModuleId,empty($id)?$QAs:array($QAs),$domain);
						}
						break;
						
	case 'expert_answers' :
						$lyModuleId = 11;
						$avatar = 'http://webezines.kwithost.com/theme1/images/avatar.jpg';
						$moduleCode = '';
						$modObj = QuestionAnswer::getInstance($db);	
						$QAs = array();					
						if(empty($id))
						{
							$Qs = $modObj->getQuestionsByKeyword($keyword, $start, 1, $alterkw, $avatar,true);
							$id = empty ($Qs[0])?'':$Qs[0]['question_id'];
						}
						else
							$Qs = $modObj->getQuestionById($id,$avatar);

						if (!empty ($id))
						{
							$As = $modObj->getQuestionAnswers($id, $quantity, $start);
							foreach ($As as $row => $Data)
							{
								foreach ($Data as $k => $val)
									$As[$row][$k] = str_replace("{IMAGE_LIBRARY}","http://webezines.kwithost.com/",$val);
							}
							$QAs = array('question'=>$Qs, 'answers'=>$As);
						}
							
						if (!empty($callback))
						{
							echo $callback.'('.json_encode($QAs).')';
						}
						elseif ($showtype == 1)
						{
							echo json_encode($QAs);
						}
						else
						{
							echo getHtmlCode($showtype,$lyModuleId,$QAs,$domain,$id);
						}					
						break;
						
	case 'directory' :
						$lyModuleId = 10;
						$moduleCode = '';
						$modObj = Dty::getInstance($db);
						if(empty($id))
							$dtys = $modObj->getDirectoriesByKeyword($keyword, $quantity, $start, $alterkw);
						else
							$dtys = $modObj->get_directory_info($id);
						
						if (!empty($callback))
						{
							echo $callback.'('.json_encode($dtys).')';
						}
						else 
						{	// Display HTML code
							echo getHtmlCode($showtype,$lyModuleId,empty($id)?$dtys:array($dtys),$domain);
						}
						break;
	case 'ecommerce' :
						$lyModuleId = 12;
						$modObj = Shopping::getInstance($db);
						if(empty($id))
							$data = $modObj->getData($keyword, $source, $quantity, array('category' => $category));
						else
							$data = $modObj->getData($keyword, $source, 1, array('product_id' => $id,'category' => $category));
						
						if (!empty($callback))
						{
							echo $callback.'('.json_encode($data).')';
						}
						else 
						{	// Display HTML code
							echo getHtmlCode($showtype,$lyModuleId,$data,$domain,$id);
						}
						break;
	case 'event' :
						$lyModuleId = 13;
						$modObj = Event::getInstance($db);
						if(empty($id))
							$data = $modObj->getData($keyword, 'eventful', $quantity, array('orign_keyword' => $alterkw));
						else
							$data = $modObj->getData($keyword, 'eventful', 1, array('orign_keyword' => $alterkw,'event_id' => $id));

						if (!empty($callback))
						{
							echo $callback.'('.json_encode($data).')';
						}
						else 
						{	// Display HTML code
							echo getHtmlCode($showtype,$lyModuleId,$data,$domain,$id);
						}
						break;
}

$os = strtolower(php_uname ("s"));
if (strpos($os, 'windows') === false) {
	@$db->close();
}

/*  Get the Html code
 *   $showtype = the id of the module_layout to use 
 *   $lyModuleId = the module id to use (verify that the $showtype is correct)
 *   
 * 
 */
function getHtmlCode($showtype,$lyModuleId,$data,$domain,$id=0)
{
	global $db;
	$Html = new Html();
	$ModuleLayout = ModuleLayout::getInstance($db);
	$moduleCode = '';
	
	// get the layout_id according with $showtype
	$layout = !empty($showtype)?$ModuleLayout->get_modulelayout_info($showtype):false;
	if (!$layout || $layout['modulelayout_module_id'] != $lyModuleId) 
	{
		$layouts = $ModuleLayout->get_module_layouts($lyModuleId);
		foreach ($layouts as $info) {
			if ($info['modulelayout_default'])
				$layout = $ModuleLayout->get_modulelayout_info($info['modulelayout_id']);
		}
	}
	
	// replace the data in the layout
	if ($layout && $layout['modulelayout_module_id'] == $lyModuleId) 
	{
		switch ($lyModuleId)
		{
			case 10 : 	$moduleCode = $Html->parseDirectoryModule($layout['modulelayout'],$data);
						break;
			case 11 : 	if (!isset($data['question']))
						{
							$moduleCode = $Html->parseQuestionModule($layout['modulelayout'],$data);
						}
						elseif(!empty($id))
						{
							$moduleCode = $Html->parseQuestionModule($layout['modulelayout'],$data['question']);
							$answers = stripos($layout['modulelayout'], '{ANSWER_') !== false;
							if (!empty($data['answers']) && $answers)
							{
								if (stripos($layout['modulelayout'], '{ANSWER_TYPE_') === false)
								{
									$moduleCode = $Html->parseQuestionAnswers($moduleCode,$data['answers']);
								}
								else 
								{
									$typeArray = Layout::imagesFromTags($layout['modulelayout'],array('ANSWER_TYPE'),array('{','ANSWER_TYPE_','_END}','}'));
									$moduleCode = $Html->parseTypeAnswers($moduleCode,$data['answers'], $typeArray);
								}
							}
						}
						break;
			case 12 : 	if (empty($id))
							$moduleCode = $Html->parseShoppingModule($layout['modulelayout'],$data);
						else 
							$moduleCode = $Html->parseShoppingDetail($layout['modulelayout'],$data);
						break;
			case 13 : 	$moduleCode = $Html->parseEventModule($layout['modulelayout'],$data);
						break;
		}
		
		if (!empty($layout['modulelayout_css'])) 
		{
			$moduleCode = $Html->insertHtmlCode($moduleCode,'css',$layout['modulelayout_css'] );
		}
		if (!empty($layout['modulelayout_js'])) 
		{
			$moduleCode = $Html->insertHtmlCode($moduleCode,'jsLoad',$layout['modulelayout_js']);
		}
		$moduleCode = str_replace("{DOMAIN}",$domain,$moduleCode);
		$moduleCode = str_replace("{IMAGE_LIBRARY}","http://webezines.kwithost.com/",$moduleCode);
		$moduleCode = $Html->replace_tag($moduleCode);
	}
	return $moduleCode;
}

function getpage($keyword,$domain,$db)
{
	$str 	= '';
	$js		= '';
	$Page 	= Page::getInstance($db);
	
	if($keyword=='privacy page' || $keyword=='privacy_page'){
		$page 	= $Page->get_pagename_info ('privacy');
		$str 	= isset($page['page'])?str_replace(array('{DOMAIN}','{TITLE}','{TITLE_LINK}'),$domain,$page['page']):''; 
	}
	else if($keyword=='contactus page' || $keyword=='contactus_page'){
		$page 	= $Page->get_pagename_info ('contactus_page');
		$str 	= isset($page['page'])?str_replace(array('{DOMAIN}','{TITLE}','{TITLE_LINK}'),$domain,$page['page']):''; 
		$str 	= str_replace(array('{DOMAIN_KEYWORD}','{KEYWORDS}'),$keyword,$str); 
	}
	else if($keyword=='list_directory' || $keyword=='list_directory_parked' || $keyword=='list directory'){
		$page 	= $Page->get_pagename_info ('list_directory_parked');
		$str 	= isset($page['page'])?str_replace(array('{DOMAIN}','{TITLE}','{TITLE_LINK}'),$domain,$page['page']):''; 
		$str 	= str_replace(array('{DOMAIN_KEYWORD}','{KEYWORDS}'),$keyword,$str); 
	}	
	
	$str = html_entity_decode($str);
	$js = '<script>$(function(){  $("#comment, #comments, #feedBlock, #ad_block, #morebutton, #results").hide(); }); </script>';
	return $str.$js;
}

?>
