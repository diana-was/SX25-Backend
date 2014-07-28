<?php
/**
 * Article Display for Parked
 * Author: Archie Huang on 30/01/2009
 */

require_once('../config.php');

$keyword 	= isset($_REQUEST['keyword']) ?  $_REQUEST['keyword'] : exit();
$showtype 	= isset($_REQUEST['showtype']) ? $_REQUEST['showtype'] : 1;
$domain 	= isset($_REQUEST['domain']) ? trim($_REQUEST['domain']) : '';
$article_id = isset($_REQUEST['article_id']) ? $_REQUEST['article_id'] : '';
$numArticle = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : '';
$offset		= isset($_REQUEST['offset']) ? $_REQUEST['offset'] : '';
$callback 	= isset($_GET['callback'])?$_GET['callback']:'';

$kws = array("privacy page", "contactus page", "privacy_page", "contactus_page");
if(in_array($keyword, $kws))
{
	echo getpage($keyword,$domain,$db);
	exit;
}

$Article = Article::getInstance($db);

switch ($showtype)
{
	case '1':	echo "<div class='entry'>";
				print($Article->get_article_parked($keyword, $domain, $showtype, $article_id));
				echo "</div>";
				break;

	case '2':	echo $Article->get_article_parked($keyword, $domain, $showtype, $article_id); //$keyword.", ".$domain.", ".$showtype;//
				break;
				
	case '3':	echo $Article->get_article_parked($keyword, $domain, $showtype, $article_id);
				break;
				
	case '11':	// for parked ajax multiple articles query 
				echo $callback . '(' . $Article->get_article_parked($keyword, $domain, $showtype, $article_id). ')';
				break;
				
	case '12':	 // for parked ajax ONE articles query 
				$article_id = is_numeric($article_id)?$article_id:0; 
				echo $callback . '(' . $Article->get_article_parked($keyword, $domain, $showtype, $article_id). ')';
				break;
				
	case '13':	// for parked ajax one random articles query 
				echo $callback . '(' . $Article->get_article_parked($keyword, $domain, $showtype, $article_id). ')';
				break;
				
	case '20':	// for articles library
				echo $callback . '(' . $Article->get_article($keyword, $domain, $showtype, $numArticle, $offset). ')';
				break;
}

$os = strtolower(php_uname ("s"));
if (strpos($os, 'windows') === false) {
	@$db->close();
}

function getpage($keyword,$domain,$db){
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
	$str = html_entity_decode($str);
	$js = '<script>$(function(){  $("#comment, #comments, #feedBlock, #ad_block, #morebutton, #results").hide(); }); </script>';
	return $str.$js;
}

?>
