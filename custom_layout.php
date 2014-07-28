<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once('config.php');

$Layout = new Layout();
$css = new cssMaker($db);
$queue  = new cssQueue($db);
$File = File::getInstance();
$Module = Module::getInstance($db);
$ModuleLayout = ModuleLayout::getInstance($db);
$allModules = $Module->get_modules(); 

$layoutInfo = array('layout_name' => ''
					, 'layout_folder' => ''
					, 'layout_id' => ''
					, 'layout_landing' => ''
					, 'layout_result' => ''
					, 'layout_sponsored' => ''
					, 'layout_sponsored_num' => ''
					, 'layout_default_module' => ''
					, 'layout_base' => ''
					, 'layout_comment' => ''
					, 'layout_id_mobile' => null
			);
			
$layoutSave = '';
$succ = 0;
$fail = 0;
$error = false;


// Upload files
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'upload')
{
	if (isset($_REQUEST['layout_id']) && !empty($_REQUEST['layout_id']))
	{
		$layoutInfo = $Layout->get_layout_info($_REQUEST['layout_id']);
		if ($layoutInfo)
		{
			$resp = $File->upload_files(array('image_files' => $sx25LayoutFolder.$layoutInfo['layout_folder'].'/images' , 'other_files' => $sx25LayoutFolder.$layoutInfo['layout_folder']));
			echo (empty($resp)?'Error Loading':$resp);
			exit;
		}
	}
	echo 'error';
	exit;
}



if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'cust_layout')
{
	if (isset($_REQUEST['new']))
	{
		unset($_REQUEST['new']);
	}
	
	$Site = Site::getInstance($db);
	
	$apply = isset($_REQUEST['apply_to']) ? $_REQUEST['apply_to'] : '';
	
	if($apply != '')
	{
		$layoutInfo = array('layout_name' => $_REQUEST['layout_name']
							, 'layout_folder' => $_REQUEST['layout_folder']
							, 'layout_landing' => replace_tag($_REQUEST['layout_landing'])
							, 'layout_result' => replace_tag($_REQUEST['layout_result'])
							, 'layout_sponsored' => replace_tag($_REQUEST['layout_sponsored'])
							, 'layout_sponsored_num' => $_REQUEST['layout_sponsored_num']
							, 'layout_default_module' => $_REQUEST['layout_default_module']
							, 'layout_base' => $_REQUEST['layout_base']
							, 'layout_comment' => replace_tag($_REQUEST['layout_comment'])
							, 'layout_modules'	=> empty($_REQUEST['layout_modules'])?array():$_REQUEST['layout_modules']
							, 'layout_id_mobile' => empty($_REQUEST['layout_id_mobile'])?null:$_REQUEST['layout_id_mobile']
							);
		
		// Validate errors
		$name = trim($_REQUEST['layout_name']);
		if (empty($name)) $error = true;
		$folderName = trim($_REQUEST['layout_folder']);
		if (empty($folderName)) $error = true;
		if ($error) {
				$layoutSave = '<font color="red">Layout NOT Saved, Name or File Name Missing!!</font>';
		}
		
		$checkID = $Layout->check_layout_id($name);
		if (empty($_REQUEST['layout_id']) && ($checkID > 0)) {
			$error = true;
			$layoutSave = '<font color="red">Layout name already exist!</font>';
		}
	
		$checkFolder = $Layout->check_layout_folder($folderName);
		if ((empty($_REQUEST['layout_id']) && ($checkFolder > 0)) 
		|| (!empty($_REQUEST['layout_id']) && ($checkFolder > 0) && ($checkFolder != $_REQUEST['layout_id']))) {
			$error = true;
			$layoutSave = '<font color="red">Folder name already exist!</font>';
		}
		
		if (!$error) 
		{
			$layout_id = $_REQUEST['layout_id'] != '' ? $_REQUEST['layout_id'] : 0;
			$new_layout_id = $Layout->save_layout($layoutInfo,$layout_id,$error);
			$layoutGenZ = substr($name, -4)=='base'; //(stripos($layoutInfo['layout_landing'], '{CSS_LIBRARY}{DOMAIN}.css') !== false) || (stripos($layoutInfo['layout_result'], '{CSS_LIBRARY}{DOMAIN}.css') !== false)
			
			if($new_layout_id)
			{
				$_REQUEST['layout_id'] = $new_layout_id;
				$layoutSave = '<font color="green">Layout Saved';
				
				if($apply == 'account')
				{
					$apply_acc_id = isset($_REQUEST['account_id'])?$_REQUEST['account_id']:0;
					if($apply_acc_id != '')
					{
						if($Site->update_layout_account($new_layout_id, $apply_acc_id))
						{
							$layoutSave .= '<br>Account Updated';
							$domainList = array();
							if($layoutGenZ){
								$accDomains = $Site->get_domain_data_list('account',$apply_acc_id);	
								if (is_array($accDomains))
								{
									foreach($accDomains as $domInfo)
									{
										$domainList[] = strtolower($domInfo['domain_url']);
									}
								}
							}
						}
					}
				}
				else if($apply == 'domain')
				{
					
					$domains = $Site->extractTextarea($_REQUEST['domains']);
					$domainList = array();
					
					foreach($domains as $key => $domName)
					{
						if($Site->update_domain(array('domain_layout_id' => $new_layout_id), $domName) !== false)
						{
							$domainList[] = $domName;
							$succ++;
						}
						else
							$fail++;
					}
					
					$layoutSave .= '<br>'.$succ.' domains Updated, <font color="red">'.$fail.' domains Failed</font>';
				}
				$layoutSave .= '</font>';
				
				if ($layoutGenZ && (($apply == 'domain') || ($apply == 'account')))
				{
					$succ=0;
					$fail=0;
					if(count($domainList) > 0)
					{
						// Get the random themes
						$themes = array();
						$domain_size = sizeof($domainList);
						$themesArray = $queue->getThemesArray(); // upgrade to account themes, $themesArray = $queue->getCategoryThemesArray($category_theme);				
						if(sizeof($themesArray)>$domain_size){
							$rand_keys = array_rand($themesArray, $domain_size);
							
							if($domain_size==1)
							{
								$themes[] = $themesArray[$rand_keys];
							}
							else
							{
								for($i=0; $i<sizeof($rand_keys); $i++)
									$themes[] = $themesArray[$rand_keys[$i]];
							}							
						}else{
							$themes = $themesArray;
						}
						$themecount = 0;

						foreach($domainList as $key => $domName)
						{
							//$css->setDefaultCss($domName);
							/* get theme */
							if($themecount==sizeof($themes))
								$themecount = 0;
							$theme = $themes[$themecount];
							$themecount++;
							
							if ($css->setColorTheme($domName, $theme))
								$succ++;
							else
								$fail++;
						}
						if ($fail == 0)
							$layoutSave .= '<br><font color="green">OK: '.$succ.' domains Gen Z generated</font>';
						else
							$layoutSave .= '<br><font color="red">ERROR: '.$fail.' OK: '.$succ.' domains Gen Z generated</font>';
						
					}
				}
				
				// Upload files
				$File->upload_files(array('image_files' => $sx25LayoutFolder.$folderName.'/images' , 'other_files' => $sx25LayoutFolder.$folderName));
			} 
			else
			{
				$layoutSave .= '<font color="red">Error Updating!!!</font>';
			}
		} 
		else 
		{
			$layoutInfo['layout_id'] = isset($_REQUEST['layout_id'])?$_REQUEST['layout_id']:'';
			foreach ($layoutInfo as $key => $val) {
				$layoutInfo[$key] = is_string($val)?stripslashes($val):$val;
			}
		}
	}
}

function trim_value(&$value) 
{ 
    $value = trim($value); 
}

if(!isset($_REQUEST['new']) && !$error)
{
	$layoutID = isset($_REQUEST['layout_id']) ? $_REQUEST['layout_id'] : 1;
	$layoutInfo = $Layout->get_layout_info($layoutID);
}



if(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'get_images' || $_REQUEST['action'] == 'get_files'))
{
	$data = array();
	if (!empty($layoutInfo))
	{
		if ($_REQUEST['action'] == 'get_images') 
		{
			$imageFiles = $File->getDirectoryList($sx25LayoutFolder.$layoutInfo['layout_folder'].'/images'); 
			foreach ($imageFiles as $f)
			{
				$a['src'] = $sx25LayoutLink.$layoutInfo['layout_folder'].'/images/'.$f;
				$a['name'] = $f;
				$data[] = $a;
			}
		}
		if ($_REQUEST['action'] == 'get_files') 
		{
			$otherFiles = $File->getDirectoryList($sx25LayoutFolder.$layoutInfo['layout_folder']); 
			foreach ($otherFiles as $f)
			{
				$a['href'] = $sx25LayoutLink.$layoutInfo['layout_folder'].'/'.$f;
				$a['name'] = $f;
				$data[] = $a;
			}
		}
	}
	echo json_encode($data);
	exit;
}


require_once('header.php');
?>
<script type="text/javascript" src="/js/tabpane.js"></script>
<link href="css/custom_layout.css" rel="stylesheet" type="text/css">

<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>
			<td valign="top" id="main">
				<?php if($layoutSave != '') : ?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
						<div class="content" align="center">
				        <?php echo $layoutSave;?>
						</div>
						</td>
					</tr>
				</table>
				<br />
				<?php endif; ?>
			
<script language="JavaScript">
    var changed = false;

    <?php if (isset($layoutInfo['layout_id']) && !empty($layoutInfo['layout_id'])): ?>
    $(document).ready(function() {
    	refreshImages();
    	refreshFiles();
	});    
    <?php endif; ?>
    
	function refreshImages() {
		$("#image_library").html('<img src="images/loading.gif" style="border:0px;" />');
	
		$.getJSON("custom_layout.php", {action:'get_images', layout_id: <?php echo $layoutID; ?>},function(data){
			$("#image_library").html('');
			  if(null==data || data=='')
				  return;
		    $.each(data, function(key, value) { 					
	  				$("#image_library").append('<div style="float:left"><img src="' + value['src'] + '" style="height:48px; width:48px; padding:2px;" title="' + value['title']  + '"></div>');
			});	 
	     });
	}

	function refreshFiles() {
		$("#file_library").html('<img src="images/loading.gif" style="border:0px;" />');
	
		$.getJSON("custom_layout.php", {action:'get_files', layout_id: <?php echo $layoutID; ?>},function(data){
			$("#file_library").html('');
			  if(null==data || data=='')
				  return;
		    $.each(data, function(key, value) { 					
	  				$("#file_library").append('<a href="' + value['href'] + '" target="_blank" style="font-size: 14px;">' + value['name'] + '</a></br>');
			});	 
	     });
	}
	
    function change() {
    	changed = true;
    }

    function save(form) {
    	form.target='_self';
    	changed = false;
    	this.form.submit();
    }

    window.onbeforeunload = confirmExit;
    function confirmExit()
    {
      if (changed)
        return "You have unsave changes.";
    }
    
    
function pView(form, tab) {
	var inf = document.getElementById(tab);
	infValue = inf.value;
	if (infValue != "")
	{	
		var spl;
		spl = (tab == "layout_result") ? form.layout_sponsored.value : "";	
		var splNum = form.layout_sponsored_num.value;
		
		if (spl != "")
		{
			spl = spl.replace(/\{TITLE\}/g, 'Find Movie Listings'); 
			spl = spl.replace(/\{DESCRIPTION\}/g, 'Your Source for Movie. Find and Compare Movie Listings Here'); 
			spl = spl.replace(/\{SITEHOST\}/g, 'http://www.AreaConnect.com'); 
			var sponsor = '';
			for (var i=1; i <= splNum; i++) {
				sponsor += spl;
			}
			infValue = infValue.replace(/\{SPONSOR_LISTINGS_[0-9]\}/g, spl);	
			infValue = infValue.replace(/\{SPONSOR_LISTINGS\}/g, sponsor);		
		}
		
		infValue = replaceTags(infValue);

		// exist document ready?
		var docReady = (infValue.search(/\$\s*\(document\)\s*.\s*ready/) != -1);
		// replace document ready();
		infValue = infValue.replace(/\$\s*\(document\)\s*.\s*ready/, ' ready = ');   

		/* Upload the jquery script later to work */
		var jqueryExist = infValue.search(/jquery.min.js/i) != -1;

		//Call the JS;
		var js = '';
		if (!jqueryExist) {
			js = '<' + 'script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.1/jquery.min.js" type="text/javascript"><' + '/script><' + '/head>';
			infValue = infValue.replace(/\<\/head\>/i, js);
		}

		var callReady = (docReady)?' ready(); ':'';

		js  = '<' + 'script type="text/javascript"> ';
		js += callReady;
		js += '<'  + '/script><' + '/body>';
		infValue = infValue.replace(/\<\/body\>/i, js);

		win = window.open("","Preview","scrollbars=yes,resizable=yes,status=0,width=1024,height=768");
		win.document.write("" + infValue + "");

		win.document.close();
		
	}
	else
	{
		alert("Please enter source code content in order to preview.");
	}
}

function stripslashes(str) {
str=str.replace(/\\'/g,'\'');
str=str.replace(/\\"/g,'"');
str=str.replace(/\\\\/g,'\\');
str=str.replace(/\\0/g,'\0');
return str;
}

	function replaceTags(infValue) {
		infValue = infValue.replace(/\{THEME\}/g, '<?php echo $sx25LayoutLink; ?>' + $('#layout_folder').val() + '/'); 
		infValue = infValue.replace(/\{CSS_LIBRARY\}(\s*)\{DOMAIN\}.css/g, '<?php echo $sx25LayoutLink; ?>' + $('#layout_folder').val() + '/' + $('#layout_base').val()); 
		infValue = infValue.replace(/\{MENU1\}/g, 'Dvd Movies'); 
		infValue = infValue.replace(/\{MENU2\}/g, 'Movies Playing'); 
		infValue = infValue.replace(/\{MENU3\}/g, 'Dvd Videos'); 
		infValue = infValue.replace(/\{MENU4\}/g, 'Italian Movies'); 
		infValue = infValue.replace(/\{MENU5\}/g, 'Career Education'); 
		infValue = infValue.replace(/\{MENU6\}/g, 'Human Resources Jobs'); 
		infValue = infValue.replace(/\{MENU7\}/g, 'Part Time Jobs'); 
		infValue = infValue.replace(/\{MENU8\}/g, 'Accounting'); 
		infValue = infValue.replace(/\{MENU9\}/g, 'Freight'); 
		infValue = infValue.replace(/\{MENU10\}/g, 'Transportation Management'); 
		infValue = infValue.replace(/\{MENU11\}/g, 'Air'); 
		infValue = infValue.replace(/\{MENU12\}/g, 'Freight Transportation'); 

		infValue = infValue.replace(/\{MENU_LINK1\}/g, 'Menu-link1'); 
		infValue = infValue.replace(/\{MENU_LINK2\}/g, 'Menu-link2'); 
		infValue = infValue.replace(/\{MENU_LINK3\}/g, 'Menu-link3'); 
		infValue = infValue.replace(/\{MENU_LINK4\}/g, 'Menu-link4'); 
		infValue = infValue.replace(/\{MENU_LINK5\}/g, 'Menu-link5'); 
		infValue = infValue.replace(/\{MENU_LINK6\}/g, 'Menu-link6'); 
		infValue = infValue.replace(/\{MENU_LINK7\}/g, 'Menu-link7'); 
		infValue = infValue.replace(/\{MENU_LINK8\}/g, 'Menu-link8'); 
		infValue = infValue.replace(/\{MENU_LINK9\}/g, 'Menu-link9'); 
		infValue = infValue.replace(/\{MENU_LINK10\}/g, 'Menu-link10'); 
		infValue = infValue.replace(/\{MENU_LINK11\}/g, 'Menu-link11'); 
		infValue = infValue.replace(/\{MENU_LINK12\}/g, 'Menu-link12'); 
		
		title = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
		summary = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.';
		content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.<br>"At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.';
		infValue = infValue.replace(/\{ARTICLE_TITLE\}/g, title); 
		infValue = infValue.replace(/\{ARTICLE_SUMMARY\}/g, summary); 
		infValue = infValue.replace(/\{ARTICLE_CONTENT\}/g, content); 

		infValue = infValue.replace(/\{M_ARTICLE_TITLE_1\}/g, title + ' 1'); 
		infValue = infValue.replace(/\{M_ARTICLE_SUMMARY_1\}/g, summary); 
		infValue = infValue.replace(/\{M_ARTICLE_CONTENT_1\}/g, content); 
		infValue = infValue.replace(/\{M_ARTICLE_TITLE_2\}/g, title + ' 2'); 
		infValue = infValue.replace(/\{M_ARTICLE_SUMMARY_2\}/g, summary); 
		infValue = infValue.replace(/\{M_ARTICLE_CONTENT_2\}/g, content); 
		infValue = infValue.replace(/\{M_ARTICLE_TITLE_3\}/g, title + ' 3'); 
		infValue = infValue.replace(/\{M_ARTICLE_SUMMARY_3\}/g, summary); 
		infValue = infValue.replace(/\{M_ARTICLE_CONTENT_3\}/g, content); 
		infValue = infValue.replace(/\{M_ARTICLE_TITLE_4\}/g, title + ' 4'); 
		infValue = infValue.replace(/\{M_ARTICLE_SUMMARY_4\}/g, summary); 
		infValue = infValue.replace(/\{M_ARTICLE_CONTENT_4\}/g, content); 
		infValue = infValue.replace(/\{M_ARTICLE_TITLE_5\}/g, title + ' 5'); 
		infValue = infValue.replace(/\{M_ARTICLE_SUMMARY_5\}/g, summary); 
		infValue = infValue.replace(/\{M_ARTICLE_CONTENT_5\}/g, content); 

		infValue = infValue.replace(/\{M_IMG_1\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion1.jpg'); 
		infValue = infValue.replace(/\{M_IMG_2\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion2.jpg'); 
		infValue = infValue.replace(/\{M_IMG_3\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion3.jpg'); 
		infValue = infValue.replace(/\{M_IMG_4\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion4.jpg'); 
		infValue = infValue.replace(/\{M_IMG_5\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion5.jpg'); 
		infValue = infValue.replace(/\{M_IMG_6\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion11.jpg'); 
		infValue = infValue.replace(/\{M_IMG_7\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion12.jpg'); 
		infValue = infValue.replace(/\{M_IMG_8\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion13.jpg'); 
		infValue = infValue.replace(/\{M_IMG_9\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion14.jpg'); 
		infValue = infValue.replace(/\{M_IMG_10\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion15.jpg'); 
		infValue = infValue.replace(/\{IMG_1\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion6.jpg'); 
		infValue = infValue.replace(/\{IMG_2\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion7.jpg'); 
		infValue = infValue.replace(/\{IMG_3\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion8.jpg'); 
		infValue = infValue.replace(/\{IMG_4\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion9.jpg'); 
		infValue = infValue.replace(/\{IMG_5\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion10.jpg'); 
		infValue = infValue.replace(/\{IMG_6\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion15.jpg'); 
		infValue = infValue.replace(/\{IMG_7\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion17.jpg'); 
		infValue = infValue.replace(/\{IMG_8\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion18.jpg'); 
		infValue = infValue.replace(/\{IMG_9\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion19.jpg'); 
		infValue = infValue.replace(/\{IMG_10\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion20.jpg'); 

		infValue = infValue.replace(/\{CLICK_LINK\}/g, 'CLICK_LINK'); 
		infValue = infValue.replace(/\{COPYRIGHT\}/g, '©2008 mydomain.com All rights reserved.'); 
		infValue = infValue.replace(/\{DOMAIN\}/g, 'edu4indian.com'); 
		infValue = infValue.replace(/\{ROOT_DOMAIN\}/g, 'edu4indian.com'); 
		infValue = infValue.replace(/\{KEYWORDS\}/g, 'Savings Account'); 
		infValue = infValue.replace(/\{LANDING_IMG\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion_one.jpg'); 
		infValue = infValue.replace(/\{META_KEYWORDS\}/g, 'META_KEYWORDS'); 
		infValue = infValue.replace(/\{META_TAG\}/g, ''); 
		infValue = infValue.replace(/\{META_TITLE\}/g, 'META_TITLE'); 
		infValue = infValue.replace(/\{PAGETITLE\}/g, 'PAGETITLE'); 
		infValue = infValue.replace(/\{RESULTS_IMG\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion_two.jpg'); 
		infValue = infValue.replace(/\{SEARCH_TERM\}/g, 'Savings Account'); 
		infValue = infValue.replace(/\{USER_CONTENT\}/g, '');
		
		infValue = infValue.replace(/\{RELATED1\}/g, 'Movies'); 
		infValue = infValue.replace(/\{RELATED2\}/g, 'Dvd Movies'); 
		infValue = infValue.replace(/\{RELATED3\}/g, 'Movies Playing'); 
		infValue = infValue.replace(/\{RELATED4\}/g, 'Dvd Videos'); 
		infValue = infValue.replace(/\{RELATED5\}/g, 'Italian Movies'); 
		infValue = infValue.replace(/\{RELATED6\}/g, 'Career'); 
		infValue = infValue.replace(/\{RELATED7\}/g, 'Career Education'); 
		infValue = infValue.replace(/\{RELATED8\}/g, 'Human Resources Jobs'); 
		infValue = infValue.replace(/\{RELATED9\}/g, 'Part Time Jobs'); 
		infValue = infValue.replace(/\{RELATED10\}/g, 'Accounting'); 
		infValue = infValue.replace(/\{RELATED11\}/g, 'Transportation'); 
		infValue = infValue.replace(/\{RELATED12\}/g, 'Freight'); 
		infValue = infValue.replace(/\{RELATED13\}/g, 'Transportation Management'); 
		infValue = infValue.replace(/\{RELATED14\}/g, 'Air'); 
		infValue = infValue.replace(/\{RELATED15\}/g, 'Freight Transportation'); 

		infValue = infValue.replace(/\{SPONSOR_LISTINGS_1\}/g, 'SPONSOR_LISTINGS_1'); 
		infValue = infValue.replace(/\{SPONSOR_LISTINGS_2\}/g, 'SPONSOR_LISTINGS_2'); 
		infValue = infValue.replace(/\{SPONSOR_LISTINGS_3\}/g, 'SPONSOR_LISTINGS_3'); 
		infValue = infValue.replace(/\{SPONSOR_LISTINGS_4\}/g, 'SPONSOR_LISTINGS_4'); 
		infValue = infValue.replace(/\{SPONSOR_LISTINGS_5\}/g, 'SPONSOR_LISTINGS_5'); 
		infValue = infValue.replace(/\{SPONSOR_LISTINGS_6\}/g, 'SPONSOR_LISTINGS_6'); 
		infValue = infValue.replace(/\{SPONSOR_LISTINGS_7\}/g, 'SPONSOR_LISTINGS_7'); 
		infValue = infValue.replace(/\{SPONSOR_LISTINGS_8\}/g, 'SPONSOR_LISTINGS_8'); 
		infValue = infValue.replace(/\{SPONSOR_LISTINGS_9\}/g, 'SPONSOR_LISTINGS_9'); 
		infValue = infValue.replace(/\{SPONSOR_LISTINGS_10\}/g, 'SPONSOR_LISTINGS_10'); 
		
		infValue = infValue.replace(/\{TITLE_LINK\}/g, 'TITLE_LINK'); 
		infValue = infValue.replace(/\{TITLE\}/g, 'Top Online Bank Account'); 
		infValue = infValue.replace(/\{SITEHOST\}/g, 'SITEHOST'); 
		infValue = infValue.replace(/\{DESCRIPTION\}/g, 'DESCRIPTION'); 
		return  infValue;
	}
</script>



<!-- *** START MAIN CONTENTS  *** -->
<table border="0" cellpadding="0" cellspacing="3" width="100%">
<tr>
	<td colspan="2">
		<div id="boxGray">
			<div class="greenHdr">Custom Layout</div>
				<form action="custom_layout.php" method="post" enctype="multipart/form-data" name="custom_layout">
		    	<input type="hidden" name="action" value="cust_layout">
                <input type="hidden" name="layout_id" value="<?php echo $layoutInfo['layout_id'];?>" />
		        <div id="custLayout">
		        	<div class="custLabelL">Layout Name: 
		        		<input name="layout_name" type="text" class="frmCLText" value="<?php echo $layoutInfo['layout_name'];?>" size="40" maxlength="250" <?php if (!isset($_REQUEST['new']) && ($user->userLevel < 6)) echo 'readonly="readonly"';?>>
		        	</div>
		        	<div class="custLabelR">Existing Layouts: 
	                    <select class="inputSelect" name="layout_go" onchange="location.href=this.options[this.selectedIndex].value; change();">
	                    	<option value="/custom_layout.php?new=1" >Create new blank layout</option>
							<?php
							$layouts = $Layout->get_layouts();
							foreach ($layouts as $pRow) 
							{
								echo '<option value="/custom_layout.php?layout_id='.$pRow['layout_id'].'&mod=1"';
								if(!isset($_REQUEST['new']) && $pRow['layout_id'] == $layoutInfo['layout_id'])
									echo ' selected';
								echo '>'.$pRow['layout_name'].'</option>';
							}
							?>
	                	</select>
		        		<br>
		        		<span class="error"></span>
		        	</div>	
		           	<br class="clearboth">
					<div class="custLabelL">Folder name:
		        		<input name="layout_folder" id="layout_folder" type="text" class="frmCLUrl" value="<?php echo $layoutInfo['layout_folder'];?>" size="40"  maxlength="250" onChange="$('#foldername').html(this.value); change();" <?php if (!isset($_REQUEST['new']) && ($user->userLevel < 6)) echo 'readonly="readonly"';?>>
		        		<span class="error"></span>
		        	</div>	
					<div class="custLabelR">
		        		<span style="color:blue"><br>HELP : Use {THEME} to be replace by the "domain" and folder name: http://image.Domain/folderName/</span>
		        	</div>	
		           	<br class="clearboth">
		           	<br class="clearboth">
		           	<br class="clearboth">

					<div class="tab-pane" id="tabPane1">
						<script type="text/javascript">
							tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );
							//tp1.setClassNameTag( "dynamic-tab-pane-control-luna" );
							//alert( 0 )
						</script>
						
						<div class="tab-page" id="tabPage1">
							<h2 class="tab">Landing Page</h2>

							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage1" ) );</script>
							<div class="custTextL">Landing Source Code:<br /><textarea cols="120" rows="40" name="layout_landing" id="layout_landing" class="frmCLArea"><?php echo $layoutInfo['layout_landing'];?></textarea><br><span class="error"></span>
    						<br><br>
    						Comment Source Code:<br />
				    		<textarea cols="120" rows="5" name="layout_comment"  id="layout_comment" class="frmCLArea" onChange="change();"><?php echo $layoutInfo['layout_comment'];?></textarea>

				    		</div><br class="clearboth" />
				
							<br class="clearboth"><br>
							<p class="alignRight">
								<input name="preview" type="Button" class="submitbutton" value=" Preview " id="layout_landing" onclick="pView(this.form, 'layout_landing');"  onChange="change();">
							</p>
						</div>
						
						<div class="tab-page" id="tabPage2">
							<h2 class="tab">Results Page</h2>
							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage2" ) );</script>		
							<div class="custTextL">Results Source Code:
								<br />
								<textarea cols="120" rows="40" name="layout_result" id="layout_result" class="frmCLArea"  onChange="change();"><?php echo $layoutInfo['layout_result'];?></textarea>
                            	<br>
                            	<span class="error"></span>
                            	<br>Number of Sponsored Listings:
								<select class="inputSelect" name="layout_sponsored_num"  onChange="change();">
	                            <option value="2"<?php echo $layoutInfo['layout_sponsored_num'] == '2' ? ' selected' : '';?>>2</option>
	                            <option value="3"<?php echo $layoutInfo['layout_sponsored_num'] == '3' ? ' selected' : '';?>>3</option>
	                            <option value="4"<?php echo $layoutInfo['layout_sponsored_num'] == '4' ? ' selected' : '';?>>4</option>
								<option value="5"<?php echo $layoutInfo['layout_sponsored_num'] == '5' ? ' selected' : '';?>>5</option>
								<option value="6"<?php echo $layoutInfo['layout_sponsored_num'] == '6' ? ' selected' : '';?>>6</option>
								<option value="7"<?php echo $layoutInfo['layout_sponsored_num'] == '7' ? ' selected' : '';?>>7</option>
								<option value="8"<?php echo $layoutInfo['layout_sponsored_num'] == '8' ? ' selected' : '';?>>8</option>
								<option value="9"<?php echo $layoutInfo['layout_sponsored_num'] == '9' ? ' selected' : '';?>>9</option>
								<option value="10"<?php echo $layoutInfo['layout_sponsored_num'] == '10' ? ' selected' : '';?>>10</option>
								</select>
								<br><br>
								Sponsored Listings Source Code:
								<br />
					    			<textarea cols="120" rows="5" name="layout_sponsored"  id="layout_sponsored" class="frmCLArea"  onChange="change();"><?php echo $layoutInfo['layout_sponsored'];?></textarea>
					    		<br>
					    		<span class="error"></span>
				    		</div>
				    		<br class="clearboth" />
			
							<p  class="alignRight">
								<input name="preview" type="Button" class="submitbutton" value=" Preview " id="layout_result" onclick="pView(this.form, 'layout_result');" >
							</p>
						</div>

						<div class="tab-page" id="tabPage3">
							<h2 class="tab">Theme Files</h2>

							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage3" ) );</script>		
							<div class="custTextL" style="height: 35px; padding-top:15px;">
					        	<label style="font-weight:bold; padding-right:10px;">Base CSS(use in GenZ):</lable>
					        	<input name="layout_base" id="layout_base" type="text" class="frmCLText" value="<?php echo $layoutInfo['layout_base'];?>" size="40" maxlength="250" />
					        </div>
							<br class="clearboth" />
							
							<div class="custTextL" style="height: 35px; padding-top:15px;">
								<label style="font-weight:bold; padding-right:10px;">Mobile Layout:</label>
			                    <select class="inputSelect" name="layout_id_mobile"">
			                    	<option value="" >Select</option>
									<?php
									foreach ($layouts as $pRow) 
									{
										echo '<option value="'.$pRow['layout_id'].'"';
										if($pRow['layout_id'] == $layoutInfo['layout_id_mobile'])
											echo ' selected';
										echo '>'.$pRow['layout_name'].'</option>';
									}
									?>
			                	</select>
					        </div>
					        
				        	<br class="clearboth" />
							<?php if (isset($layoutInfo['layout_id']) && !empty($layoutInfo['layout_id'])):?>
							<div class="custTextL">
					        	<div class="shortLabelL"><h3>Image Files:</h3></div>
								<br class="clearboth" />
								<div id="image_library" ondblclick="refreshImages();"></div>
								<br class="clearboth" />
								<div id="output" class="dropzone" type="image_files" accept="gif|jpg|png|bmp" request="custom_layout.php?action=upload&layout_id=<?php echo $layoutInfo['layout_id'];?>"   afterUpload="image_library">
									<ul id="output-listing"></ul>
								</div>		
							</div>
							<br class="clearboth" />
							<div class="custTextL">
								<div class="shortLabelL"><h3>Other Files:</h3></div>
								<br class="clearboth" />
								<div id="file_library" ondblclick="refreshFiles();"></div>
								<br class="clearboth" />
								<div id="output" class="dropzone" type="other_files" accept="js|css" request="custom_layout.php?action=upload&layout_id=<?php echo $layoutInfo['layout_id'];?>"   afterUpload="file_library">
									<ul id="output-listing"></ul>
								</div>
							</div>
							<?php else : ?>
							<div class="custTextL">
					        	<h3>Save template to start uploading images, js, and css files</h3>
							</div>
							<?php endif; ?>		
							<br class="clearboth" />						
						</div>

						<div class="tab-page" id="tabPage4">
							<h2 class="tab">Modules</h2>
							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage4" ) );</script>
							<?php  
								$lModules 	= isset($layoutInfo['layout_modules'])?$layoutInfo['layout_modules']:array();
								$mLanding 	= isset($lModules['landing'])?$lModules['landing']:array();
								$mLandingId= isset($lModules['landing-id'])?$lModules['landing-id']:array();
								$mResult 	= isset($lModules['result'])?$lModules['result']:array();
								$mResultId = isset($lModules['result-id'])?$lModules['result-id']:array();
							?>
							<div class="shortLabelL">Default Module When using {MODULE} tag:</div>
								<select class="inputSelect" name="layout_default_module" >
										<option value="ARTICLE" 	<?php if ($layoutInfo['layout_default_module'] == 'ARTICLE') echo 'selected'; ?>>ARTICLE</option>
										<option value="VIDEO" 		<?php if ($layoutInfo['layout_default_module'] == 'VIDEO') echo 'selected'; ?>>VIDEO</option>
										<option value="POLL" 		<?php if ($layoutInfo['layout_default_module'] == 'POLL') echo 'selected'; ?>>POLL</option>
										<option value="ARTICLEFEED" <?php if ($layoutInfo['layout_default_module'] == 'ARTICLEFEED') echo 'selected'; ?>>ARTICLE FEED</option>
										<option value="NEWS" 		<?php if ($layoutInfo['layout_default_module'] == 'NEWS') echo 'selected'; ?>>NEWS</option>
										<option value="FORUM" 		<?php if ($layoutInfo['layout_default_module'] == 'FORUM') echo 'selected'; ?>>FORUM</option>
										<option value="RSS" 		<?php if ($layoutInfo['layout_default_module'] == 'RSS') echo 'selected'; ?>>RSS</option>
										<option value="DIRECTORY" 	<?php if ($layoutInfo['layout_default_module'] == 'DIRECTORY') echo 'selected'; ?>>DIRECTORY</option>
										<option value="EVENT" 		<?php if ($layoutInfo['layout_default_module'] == 'EVENT') echo 'selected'; ?>>EVENT</option>
										<option value="SHOPPING" 	<?php if ($layoutInfo['layout_default_module'] == 'SHOPPING') echo 'selected'; ?>>SHOPPING</option>
										<option value="QUESTION" 	<?php if ($layoutInfo['layout_default_module'] == 'QUESTION') echo 'selected'; ?>>QUESTION</option>
										<option value="GOAL" 		<?php if ($layoutInfo['layout_default_module'] == 'GOAL') echo 'selected'; ?>>GOAL</option>
								</select>
				        	<br class="clearboth" />
				        	
							<h3>Landing Page Modules Layouts:</h3>
							<div class="custColL">
								<h3>List</h3>
								<?php   foreach($allModules as $module) :
									$moduleID = $module['module_id'];
									$mLayouts = $ModuleLayout->get_module_layouts($moduleID);
									?>
									<div class="shortLabelL"><?php echo ucwords($module['module_name']);?></div>
									<select class="inputSelect" name="layout_modules[landing][<?php echo $moduleID;?>]" >
									<?php foreach($mLayouts as $val)
									{	 
											$thisID = $val['modulelayout_id'];
											$thisIsDefault = $val['modulelayout_default'] == 1;
											echo '<option value="'.($thisIsDefault?0:$thisID).'"';
											if((isset($mLanding->$moduleID) && ($mLanding->$moduleID == $thisID || (empty($mLanding->$moduleID) && $thisIsDefault)))
											  || (!isset($mLanding->$moduleID) && $thisIsDefault))
												echo ' selected';
											echo '>'.$val['modulelayout_name'].'</option>';
									}
									?>
									</select>
									<br class="clearboth" />
								<?php	endforeach; ?>	
							</div>
							<div class="custColR">
								<h3>Single ID</h3>
								<?php   foreach($allModules as $module) :
									$moduleID = $module['module_id'];
									$mLayouts = $ModuleLayout->get_module_layouts($moduleID);
									?>
									<div class="shortLabelL"><?php echo ucwords($module['module_name']);?></div>
									<select class="inputSelect" name="layout_modules[landing-id][<?php echo $moduleID;?>]" >
									<?php foreach($mLayouts as $val)
									{	 
											$thisID = $val['modulelayout_id'];
											$thisIsDefault = $val['modulelayout_default'] == 1;
											echo '<option value="'.($thisIsDefault?0:$thisID).'"';
											if((isset($mLandingId->$moduleID) && ($mLandingId->$moduleID == $thisID || (empty($mLandingId->$moduleID) && $thisIsDefault)))
											  || (!isset($mLandingId->$moduleID) && $thisIsDefault))
												echo ' selected';
											echo '>'.$val['modulelayout_name'].'</option>';
									}
									?>
									</select>
									<br class="clearboth" />
								<?php	endforeach; ?>	
							</div>
							<br class="clearboth" />
							<h3>Result Page Modules Layouts:</h3>
							<div class="custColL">
								<h3>List</h3>
								<?php   foreach($allModules as $module) :
									$moduleID = $module['module_id'];
									$mLayouts = $ModuleLayout->get_module_layouts($moduleID);
									?>
									<div class="shortLabelL"><?php echo ucwords($module['module_name']);?></div>
									<select class="inputSelect" name="layout_modules[result][<?php echo $moduleID;?>]" >
									<?php foreach($mLayouts as $val)
									{	 
											$thisID = $val['modulelayout_id'];
											$thisIsDefault = $val['modulelayout_default'] == 1;
											echo '<option value="'.($thisIsDefault?0:$thisID).'"';
											if((isset($mResult->$moduleID) && ($mResult->$moduleID == $thisID || (empty($mResult->$moduleID) && $thisIsDefault)))
											  || (!isset($mResult->$moduleID) && $thisIsDefault))
												echo ' selected';
											echo '>'.$val['modulelayout_name'].'</option>';
									}
									?>
									</select>
									<br class="clearboth" />
								<?php	endforeach; ?>	
							</div>
							<div class="custColR">
								<h3>Single ID</h3>
								<?php   foreach($allModules as $module) :
									$moduleID = $module['module_id'];
									$mLayouts = $ModuleLayout->get_module_layouts($moduleID);
									?>
									<div class="shortLabelL"><?php echo ucwords($module['module_name']);?></div>
									<select class="inputSelect" name="layout_modules[result-id][<?php echo $moduleID;?>]" >
									<?php foreach($mLayouts as $val)
									{	 
											$thisID = $val['modulelayout_id'];
											$thisIsDefault = $val['modulelayout_default'] == 1;
											echo '<option value="'.($thisIsDefault?0:$thisID).'"';
											if((isset($mResultId->$moduleID) && ($mResultId->$moduleID == $thisID || (empty($mResultId->$moduleID) && $thisIsDefault)))
											  || (!isset($mResultId->$moduleID) && $thisIsDefault))
												echo ' selected';
											echo '>'.$val['modulelayout_name'].'</option>';
									}
									?>
									</select>
									<br class="clearboth" />
								<?php	endforeach; ?>	
							</div>
							<br class="clearboth" />
						</div>

						
						<div class="tab-page" id="tabPage5">
							<h2 class="tab">Documentation</h2>
							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage5" ) );</script>		
							<?php include_once('sx25_doc.html'); ?>
						</div>
						
						<?php if ($user->userLevel >= 5) : ?>
						<div class="frmCLInput">
							<p><input type="radio" name="apply_to" value="layout" >Save

                            <br><input type="radio" name="apply_to" value="account" >Save and apply layout to account: 
                            <select name="account_id" id="account_id" onChange="document.forms['custom_layout'].elements['apply_to'][1].checked = true; change();">
                			<option value=''>Please Select</option>
							<?php
							$productQuery = "SELECT * FROM accounts ORDER BY account_id";
							$pResults = $db->select($productQuery);
							while ($pRow=$db->get_row($pResults, 'MYSQL_ASSOC')) 
							{
								echo '<option value="'.$pRow['account_id'].'">'.$pRow['account_name'].'</option>';
							}
							?>
            				</select>
                            <br><input type="radio" name="apply_to" value="domain" >Save and apply layout to certain domains: 
                            <br><textarea cols="8" rows="5" name="domains" class="frmCLTextbox" onFocus="document.forms['custom_layout'].elements['apply_to'][2].checked = true;"></textarea>

						    </p>
						</div>
					  	<br class="clearboth">
                        <p  class="alignCenter">
							<input name="apply" type="submit" class="submitbutton"  value=" Perform Selected Operation " onclick="save(this.form)">
						</p>
						<?php endif; ?>
					</div>
					<script type="text/javascript">
					//<![CDATA[
			
					setupAllTabs();
			
					//}>
					</script>
		    	</div>
    		</form>
			</div>
	</td>
</tr>
</table>
		
			
			<!-- *** END MAIN CONTENTS  *** -->
			
					
			</td>
			<td class="brdrR">&nbsp;</td>
		</tr>
	</table>

</div>


<?php
function replace_tag($string)
{
	$string = str_replace('<', '&lt;', $string);
	$string = str_replace('>', '&gt;', $string);
	return $string;
}
require_once('footer.php');
?>