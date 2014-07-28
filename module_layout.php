<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once('config.php');
require_once('header.php');
$Layout = ModuleLayout::getInstance($db);
$Module = Module::getInstance($db);

$layoutInfo = array ('modulelayout_module_id'  => '' 
					,'modulelayout_name'  => '' 
					,'modulelayout'  => '' 
					,'modulelayout_js'  => '' 
					,'modulelayout_css'  => '' 
					,'modulelayout_settings'  => '' 
					,'modulelayout_default'  => '' );
$layoutSave = '';
$succ = 0;
$fail = 0;
$error = false;

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'save_layout')
{
	if (isset($_REQUEST['new']))
	{
		unset($_REQUEST['new']);
	}
	
	//print_r($_REQUEST);
	$layoutInfo = array('modulelayout_module_id' 	=> $_REQUEST['modulelayout_module_id']
						, 'modulelayout_name' 		=> $_REQUEST['modulelayout_name']
						, 'modulelayout' 			=> $_REQUEST['modulelayout']
						, 'modulelayout_js' 		=> $_REQUEST['modulelayout_js']
						, 'modulelayout_css'		=> $_REQUEST['modulelayout_css']
						, 'modulelayout_settings'	=> empty($_REQUEST['modulelayout_settings'])?array():$_REQUEST['modulelayout_settings']);
	
	// rebuild the sources list to an array
    if (isset($layoutInfo['modulelayout_settings']['sources']))
    	$layoutInfo['modulelayout_settings']['sources'] = explode(',',$layoutInfo['modulelayout_settings']['sources']);
    	
	// Validate errors
	$name = trim($_REQUEST['modulelayout_name']);
	if (empty($name)) 
		$error = 'true';
	$moduleID = trim($_REQUEST['modulelayout_module_id']);
	if (empty($moduleID)) 
		$error = 'true';

	if ($error) 
	{
		$layoutSave = '<font color="red">Layout NOT Saved, Module or Name Missing!!</font>';
	}
	
	$checkID = $Layout->check_modulelayout_id($name);
	if (empty($_REQUEST['modulelayout_id']) && ($checkID > 0)) 
	{
		$error = 'true';
		$layoutSave = '<font color="red">Module layout name already exist!</font>';
	}

	if (!$error) {
		$modulelayout_id = empty($_REQUEST['modulelayout_id']) ? 0 : $_REQUEST['modulelayout_id'];
		$new_modulelayout_id = $Layout->save_modulelayout($layoutInfo,$modulelayout_id,$error);
		
		if($new_modulelayout_id && empty($error))
		{
			$layoutSave = '<font color="green">Layout Saved</font>';
		} 
		else 
		{
			$layoutSave = "<font color=\"red\">$error</font>";
		}
		$_REQUEST['modulelayout_id'] = $new_modulelayout_id;
		
	} else {
		$layoutInfo['modulelayout_id'] = isset($_REQUEST['modulelayout_id'])?$_REQUEST['modulelayout_id']:'';
		foreach ($layoutInfo as $key => $val) {
			$layoutInfo[$key] = is_string($val)?stripslashes($val):$val;
		}
	}
}

if(!isset($_REQUEST['new']) && !$error)
{
	$layoutID = isset($_REQUEST['modulelayout_id']) ? $_REQUEST['modulelayout_id'] : 1;
	$layoutInfo = $Layout->get_modulelayout_info($layoutID);
}
if(!isset($_REQUEST['new']))
{
	$moduleInfo = $Module->get_module_info($layoutInfo['modulelayout_module_id']);
}
?>
<script type="text/javascript" src="/js/tabpane.js"></script>
<link href="css/module_layout.css" rel="stylesheet" type="text/css">

<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>

			<td class="brdrL">&nbsp;</td>
			<td valign="top" id="main">
			<?php
if($layoutSave != '')
{
?>
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
<?php
}
?>
			

<script language="JavaScript">
    var changed = false;

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
			infValue = replaceTags(infValue);

			// exist document ready?
			var docReady = (infValue.search(/\$\s*\(document\)\s*.\s*ready/) != -1) || (form.modulelayout_js.value.search(/\$\s*\(document\)\s*.\s*ready/) != -1);
			// replace document ready();
			infValue = infValue.replace(/\$\s*\(document\)\s*.\s*ready/, ' ready = ');   

			/* Upload the jquery script later to work */
			var jqueryExist = infValue.search(/jquery.min.js/i) != -1;

			//Call the JS;
			var js = '<' + 'link rel="stylesheet" type="text/css" href="js/jcarousellite.css"' + '/>' + "\n";
			if (!jqueryExist) 
				js += '<' + 'script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.1/jquery.js" type="text/javascript"><' + '/script>' + "\n";
			js += '<' + 'script src="js/jcarousellite.js" type="text/javascript"><' + '/script>' + "\n";
			js += '<' + 'script src="js/jquery.easing.js" type="text/javascript"><' + '/script>' + "\n";
			js += '<' + 'script src="js/jquery.mousewheel.js" type="text/javascript"><' + '/script>' + "\n";

			head =  '<' + '!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' + "\n";
			head += '<' + 'html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">' + "\n";
			head += '<' + 'head>' + "\n";
			head += js + "\n";
			head += '<' + 'style type="text/css">' + "\n"; 
			head += replaceTags(form.modulelayout_css.value);  + "\n";
			head += '<' + '/style>' + "\n"; 
			head +='<' + '/head>' + "\n";
			head +='<' + 'body>' + "\n";
			infValue = head + infValue;
			infValue += '<' + 'script type="text/javascript">' + "\n"; 
			infValue += replaceTags(form.modulelayout_js.value.replace(/\$\s*\(document\)\s*.\s*ready/, ' ready = ')) + "\n";   
			infValue += '<' + '/script>' + "\n"; 
			infValue += '<' + '/body>' + "\n"; 
			infValue += '<' + '/html>' + "\n"; 

			var callReady = (docReady)?' ready(); ':'';

			js = '<' + 'script type="text/javascript"> ' + callReady + '<'  + '/script><' + '/body>';
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
		link = 'https://sx2.kwithost.com';
		title = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
		summary = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'; 
		content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.<br>"At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.';
		// Articles module
		infValue = infValue.replace(/\{ARTICLE_TITLE\}/g, title); 
		infValue = infValue.replace(/\{ARTICLE_SUMMARY\}/g, summary); 
		infValue = infValue.replace(/\{ARTICLE_CONTENT\}/g, content); 
		// ArticleFeed Module
		data = '<li><div class="articleFeed"><img alt="10 Top Smart Web Design Tips" src="http://cavatars.articlesbase.com/public/120_67217_kk3Bc.jpg"><h2>10 Top Smart Web Design Tips</h2>When in the thoughts of producing a website, think hard on the some important key points you must deploy. Here you have 10 top tips. [<a class="readmore" target="_blank" href="http://www.articlesbase.com/web-design-articles/10-top-smart-web-design-tips-1541932.html">read more</a>]</div></li>';
		data +='<li><div class="articleFeed"><h2>IVES &amp; Shaughnessy Web Information Services</h2>318 WALL STREET SUITE 2A, KINGSTON, NY 12401955-1999We can take your existing information resources and convert them into easy to use tools, helping manage your information to meet your business objectives [<a class="readmore" target="_blank" href="http://www.hotfrog.com/Companies/IVES-Shaughnessy-Web-Information-Services">read more</a>]</div></li>';
		infValue = infValue.replace(/\{ARTICLE_SOURCELINK\}/g, link); 
		infValue = infValue.replace(/\{ARTICLE_SOURCETITLE\}/g, 'Articles from About'); 
		infValue = infValue.replace(/\{ARTICLE_SOURCE\}/g, 'About'); 
		infValue = infValue.replace(/\{ARTICLE_LIST\}/g, data); 
		// Directory
		infValue = infValue.replace(/\{DIRECTORY_TITLE\}/g, 'Transportation Management'); 
		infValue = infValue.replace(/\{DIRECTORY_DESCRIPTION\}/g, summary); 
		infValue = infValue.replace(/\{DIRECTORY_URL\}/g, 'https://sx2.kwithost.com'); 
		// Forum Module 
		data = '<li><div class="Forum"><h2><b>Web</b>.med good fms <b>information</b>. - Fibromyalgia Message Board <b>...</b></h2>Join Date: Feb 2001. Posts: 75. Hugs: 0. Hugged 0 Times in 0 Posts. Thanks: 0.   Thanked 0 Times in 0 Posts. Dale HB User. Smile <b>Web</b>.med good fms <b>information</b>.   <b>...</b> [ <a href="http://www.healthboards.com/boards/showthread.php%3Ft%3D52552" class="readmore" target="_blank">read more</a> ]</div></li>';
		data +='<li><div class="Forum"><h2>persistent mild elevation of ALT - Liver &amp; Pancreas Disorders <b>...</b></h2>Jul 19, 2011 <b>...</b> There is a lot of good information on this website in the form of <b>...</b> I feel   that the best part of the <b>web information</b> here is see other <b>...</b> [ <a href="http://www.healthboards.com/boards/showthread.php%3Ft%3D859949" class="readmore" target="_blank">read more</a> ]</div></li>';
		data +='<li><div class="Forum"><h2>Poly-cystic overies - Infertility Message Board - HealthBoards</h2>I have serached the <b>web</b>, which I am not very good at and do not seemed to find   any indepth <b>information</b> like the info I had read a year ago. <b>...</b> [ <a href="http://www.healthboards.com/boards/showthread.php%3Ft%3D783262" class="readmore" target="_blank">read more</a> ]</div></li>';
		infValue = infValue.replace(/\{FORUM_SOURCELINK\}/g, link); 
		infValue = infValue.replace(/\{FORUM_SOURCETITLE\}/g, 'People from Askville Amazon'); 
		infValue = infValue.replace(/\{FORUM_SOURCE\}/g, 'AskvilleAmazon'); 
		infValue = infValue.replace(/\{FORUM_LIST\}/g, data); 
		// Image Module
		data = '<li><img src="http://images.google.com/images?q=tbn:ANd9GcQX7ZsTxpW3scrHgwJsW7A9t6ENvjSo6HujwphfP-HXQfM5WhZcchPxKA:www.mediainfluencer.net/wp/wp-content/uploads/2008/05/information-web-spider_sml.jpg" alt="information-web-spider_sml.jpg « Media Influencer" /><a href="http://www.mediainfluencer.net/2008/05/disintermediation-of-minds/information-web-spider_smljpg/" target="_blank" class="link"></a><span  class="description"><b>information</b>-<b>web</b>-spider_sml.jpg « Media Influencer</span></li>';
		data +='<li><img src="http://images.google.com/images?q=tbn:ANd9GcTPZROt3fR3gy5Bk7C9SGHWygHttCKBYcXIU86YKQFqtJeqq0wT8rjFwz4:idke.ruc.edu.cn/projects/Post/web/deepweblogo.jpg" alt="Web Data Integration, Deep Web data Integration, Dataspaces, WAMDM ..." /><a href="http://idke.ruc.edu.cn/projects/web.htm" target="_blank" class="link"></a><span  class="description"><b>Web</b> Data Integration, Deep <b>Web</b> data Integration, Dataspaces, WAMDM <b>...</b></span></li>';
		data +='<li><img src="http://images.google.com/images?q=tbn:ANd9GcSwQVVV9mLuDWUfoMWX-jsBwukRlckpIHevUEYoOB5Z6sklNd-EcMiF1n4:code.google.com/apis/socialgraph/images/the-web.png" alt="Social Graph API - Google Code" /><a href="http://code.google.com/apis/socialgraph/" target="_blank" class="link"></a><span  class="description">Social Graph API - Google Code</span></li>';
		infValue = infValue.replace(/\{IMAGE_SOURCELINK_1\}/g, link); 
		infValue = infValue.replace(/\{IMAGE_SOURCETITLE_1\}/g, 'Images from Google'); 
		infValue = infValue.replace(/\{IMAGE_SOURCE_1\}/g, 'Yahoo'); 
		infValue = infValue.replace(/\{IMAGE_LIST_1\}/g, data); 
		infValue = infValue.replace(/\{IMAGE_SOURCELINK_2\}/g, link); 
		infValue = infValue.replace(/\{IMAGE_SOURCETITLE_2\}/g, 'Images from Bing'); 
		infValue = infValue.replace(/\{IMAGE_SOURCE_2\}/g, 'Yahoo'); 
		infValue = infValue.replace(/\{IMAGE_LIST_2\}/g, data); 
		infValue = infValue.replace(/\{IMAGE_SOURCELINK_3\}/g, link); 
		infValue = infValue.replace(/\{IMAGE_SOURCETITLE_3\}/g, 'Images from People'); 
		infValue = infValue.replace(/\{IMAGE_SOURCE_3\}/g, 'Yahoo'); 
		infValue = infValue.replace(/\{IMAGE_LIST_3\}/g, data); 
		infValue = infValue.replace(/\{IMAGE_SOURCELINK_4\}/g, link); 
		infValue = infValue.replace(/\{IMAGE_SOURCETITLE_4\}/g, 'Images from Flickr'); 
		infValue = infValue.replace(/\{IMAGE_SOURCE_4\}/g, 'Yahoo'); 
		infValue = infValue.replace(/\{IMAGE_LIST_4\}/g, data); 
		// News Module
		data = '<li><div class="News"><h2>Vermillion Pancake Fly-In Set For Sunday</h2>VERMILLION — Pancake Fly-In Breakfast will be held at the Vermillion Airport on Sunday, Aug. 7. The event runs from 8 a.m.-noon. [ <a href="http://yankton.net/articles/2011/08/03/community/doc4e3a0517b836d134106266.txt" class="readmore" target="_blank">read more</a> ]</div></li>';
		data +='<li><div class="News"><h2>White House Picks New Information Chief</h2>Steven VanRoekel, who has been a leader at Microsoft and the F.C.C., will take over as chief information officer for the federal government. [ <a href="http://www.nytimes.com/2011/08/04/technology/white-house-picks-new-information-chief.html?partner=rss&emc=rss" class="readmore" target="_blank">read more</a> ]</div></li>';
		data +='<li><div class="News"><h2>A Climate Scientist\'s View of a Famine\'s Roots</h2>A climate scientist lays out the argument for ocean warming underpinning Somali hunger. [ <a href="http://dotearth.blogs.nytimes.com/2011/08/03/a-climate-scientists-view-of-a-famines-roots/" class="readmore" target="_blank">read more</a> ]</div></li>';
		infValue = infValue.replace(/\{NEWS_SOURCELINK\}/g, link); 
		infValue = infValue.replace(/\{NEWS_SOURCETITLE\}/g, 'News from Yahoo'); 
		infValue = infValue.replace(/\{NEWS_SOURCE\}/g, 'Yahoo'); 
		infValue = infValue.replace(/\{NEWS_LIST\}/g, data); 
		// News Subscribe
		infValue = infValue.replace(/\{SUBSCRIBE_DOMAIN\}/g, 'mydomain.com'); 
		// POLL Module
		infValue = infValue.replace(/\{POLL_TITLE\}/g, 'New Poll Vote Now'); 
		infValue = infValue.replace(/\{POLL_SUMMARY\}/g, summary); 
		infValue = infValue.replace(/\{POLL_CONTENT\}/g, link); 
		// Question Module
		infValue = infValue.replace(/\{QUESTION_KEYWORD\}/g, 'keyword'); 
		infValue = infValue.replace(/\{QUESTION_USER_PHOTO\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion_one.jpg'); 
		infValue = infValue.replace(/\{QUESTION_SUBJECT\}/g, title + '?'); 
		infValue = infValue.replace(/\{QUESTION_CONTENT\}/g, content); 
		infValue = infValue.replace(/\{QUESTION_ANSWER_SUMMARY\}/g, content); 
		infValue = infValue.replace(/\{QUESTION_ID\}/g, 1); 
		infValue = infValue.replace(/\{QUESTION_ANSWERER\}/g, 'Black Smith'); 
		infValue = infValue.replace(/\{QUESTION_DATE\}/g, '<?php echo date('Y-m-d H:i:m'); ?>'); 
		// RSS Module
		infValue = infValue.replace(/\{BBC_RSSTITLE\}/g, title); 
		infValue = infValue.replace(/\{BBC_RSSNEWS\}/g, content); 
		infValue = infValue.replace(/\{CNN_RSSTITLE\}/g, title); 
		infValue = infValue.replace(/\{CNN_RSSNEWS\}/g, content); 
		// Video Module
		data = '<li><a href="/display/video/result.php?hplink=http%3A%2F%2Fwww.youtube.com%2Fv%2FTpYIKF1wuyE%26feature%3Dyoutube_gdata" target="_self"><img src="http://i.ytimg.com/vi/TpYIKF1wuyE/default.jpg" alt="Auto-Tune the News #11: Pure Poppycock. (ft. Joel Madden)" /></a><span  class="description"><b>Youtube Video:</b>Auto-Tune the News #11: Pure Poppycock. (ft. Joel Madden)</span></li>';
		data +='<li><a href="/display/video/result.php?hplink=http%3A%2F%2Fwww.youtube.com%2Fv%2FrEG-6-SDbys%26feature%3Dyoutube_gdata" target="_self"><img src="http://i.ytimg.com/vi/rEG-6-SDbys/default.jpg" alt="Anoj\'s Top 10 Halo Reach Beta Submission Video" /></a><span  class="description"><b>Youtube Video:</b>Anoj\'s Top 10 Halo Reach Beta Submission Video</span></li>';
		data +='<li><a href="/display/video/result.php?hplink=http%3A%2F%2Fwww.youtube.com%2Fv%2FU-M6SiLkBms%26feature%3Dyoutube_gdata" target="_self"><img src="http://i.ytimg.com/vi/U-M6SiLkBms/default.jpg" alt="Us Now" /></a><span  class="description"><b>Youtube Video:</b>Us Now</span></li>';
		data +='<li><a href="/display/video/result.php?hplink=http%3A%2F%2Fwww.youtube.com%2Fv%2F3TOK3g4_HIA%26feature%3Dyoutube_gdata" target="_self"><img src="http://i.ytimg.com/vi/3TOK3g4_HIA/default.jpg" alt="Brewery Show - Sixpoint Craft Ales" /></a><span  class="description"><b>Youtube Video:</b>Brewery Show - Sixpoint Craft Ales</span></li>';
		infValue = infValue.replace(/\{VIDEO_SOURCES_TITLES\}/g, 'Videos from YouTube'); 
		infValue = infValue.replace(/\{VIDEO_LIST\}/g, data); 
		
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
		return  infValue;
	}
</script>


<!-- *** START MAIN CONTENTS  *** -->
	
<table border="0" cellpadding="0" cellspacing="3" width="100%">
<tr>
	<td colspan="2">
		<div id="boxGray">
			<div class="greenHdr">Module Layout</div>

				<form action="/module_layout.php" method="post" enctype="multipart/form-data" name="module_layout">
		    	<input type="hidden" name="action" value="save_layout">
                <input type="hidden" name="modulelayout_id" value="<?php if (isset($layoutInfo['modulelayout_id'])) echo $layoutInfo['modulelayout_id'];?>" />
		        <div id="custLayout">
		        	<div class="custLabelL">Module Layout Name: 
		        		<input name="modulelayout_name" type="text" class="frmCLText" value="<?php echo $layoutInfo['modulelayout_name'];?>" size="40" maxlength="30" onchange="change();" <?php if (!isset($_REQUEST['new']) && ($user->userLevel < 6)) echo 'readonly="readonly"';?>>
		        	</div>
		        	<div class="custLabelR">Existing Layouts: 
                   		<select class="inputSelect" name="modulelayout_go" onchange="location.href=this.options[this.selectedIndex].value;">
                    	<option value="/module_layout.php?new=1" >Create new blank layout</option>
						<?php
						$layouts = $Layout->get_modulelayouts();
						foreach ($layouts as $pRow) 
						{
							echo '<option value="/module_layout.php?modulelayout_id='.$pRow['modulelayout_id'].'&mod=1"';
							if(!isset($_REQUEST['new']) && $pRow['modulelayout_id'] == $layoutInfo['modulelayout_id'])
								echo ' selected';
							echo '>'.$pRow['modulelayout_name'].'</option>';
						}
						?>
                		</select>
		        		<br>
		        		<span class="error"></span>
		        	</div>	
		           	<br class="clearboth">

					<div class="extLabelR">Module:
					    <?php if (isset($_REQUEST['new']) || empty($layoutInfo['modulelayout_id'])) { ?>
                   		<select class="inputSelect" name="modulelayout_module_id" >
                    	<option value="" >Select...</option>
						<?php
						$modules = $Module->get_modules();
						foreach ($modules as $pRow) 
						{
							echo '<option value="'.$pRow['module_id'].'"';
							if($pRow['module_id'] == $layoutInfo['modulelayout_module_id'])
								echo ' selected';
							echo '>'.$pRow['module_name'].'</option>';
						}
						?>
                		</select>
                		<?php } else {?>
		        		<input name="modulelayout_module_id" type="hidden" class="frmCLText" value="<?php echo $layoutInfo['modulelayout_module_id'];?>" size="40" maxlength="30" readonly="readonly">
		        		<input name="module_name" type="text" class="frmCLText" value="<?php echo $moduleInfo['module_name'];?>" size="40" maxlength="30" readonly="readonly">
		        		
                		<?php }?>
		        		<br>
		        	</div>	
		           	<br class="clearboth">

					<div class="tab-pane" id="tabPane1">
						<script type="text/javascript">
							tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );
							//tp1.setClassNameTag( "dynamic-tab-pane-control-luna" );
							//alert( 0 )
						</script>
						<div class="tab-page" id="tabPage1">
							<h2 class="tab">HTML</h2>
							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage1" ) );</script>
							<div class="custTextL">Landing Source Code:<br />
								<textarea cols="120" rows="25" name="modulelayout" id="modulelayout" class="frmCLArea" onchange="change();"><?php echo $layoutInfo['modulelayout'];?></textarea>
								<br>
				    		</div>
				    		<br class="clearboth" />
				
							<p class="alignRight"><input name="preview" type="Button" class="submitbutton" value=" Preview " onclick="pView(this.form, 'modulelayout');"></p>
						</div>

						<div class="tab-page" id="tabPage2">
							<h2 class="tab">JS</h2>

							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage2" ) );</script>		
							<div class="custTextR">JS Source Code:<br />
								<textarea cols="120" rows="25" name="modulelayout_js" id="modulelayout_js" class="frmCLArea" onchange="change();"><?php echo $layoutInfo['modulelayout_js'];?></textarea>
								<br>
				    		</div>
						</div>

						<div class="tab-page" id="tabPage3">
							<h2 class="tab">CSS</h2>

							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage3" ) );</script>		
							<div class="custTextR">CSS Source Code:<br />
								<textarea cols="120" rows="25" name="modulelayout_css" id="modulelayout_css" class="frmCLArea" onchange="change();"><?php echo $layoutInfo['modulelayout_css'];?></textarea>
								<br>
				    		</div>
						</div>

						<div class="tab-page" id="tabPage4">
							<h2 class="tab">Settings</h2>

							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage4" ) );</script>
							<div class="frmCLArea" id="settings_div">
							<?php
								if (!isset($_REQUEST['new']) && !empty($layoutInfo['modulelayout_id']))
								{
									$settings = array_merge($moduleInfo['module_settings'],$layoutInfo['modulelayout_settings']);
									if (!array_key_exists('perPage',$settings))
										$settings['perPage'] = 3;
									foreach($settings as $key => $val)
									{
										$val = is_array($val)?implode(',',$val):$val;
										?> 
										<div class="custLabel"><?php echo ucwords($key);?>:
							        		<input name="modulelayout_settings[<?php echo $key;?>]" type="text" class="frmCLUrl" value="<?php echo $val;?>" size="40" onchange="change();">
							        	</div>
										<?php if ($key == 'sources') : ?>
										<div class="custLabel"><?php echo 'Available sources';?>:
							        		<input name="module_settings" type="text" class="frmCLUrl" value="<?php echo implode(',',$moduleInfo['module_settings']['sources']);?>" size="40" disabled="disabled">
							        	</div>
							        	<?php endif;
									}
								} 
								else 
								{
									echo '<input type="hidden" name="modulelayout_settings" value="" >';
								}
							?>	
							</div>
						</div>

					  	<br class="clearboth">
					  	<?php if ($user->userLevel >= 5) : ?>
                        <p  class="alignCenter">
							<input name="apply" type="submit" class="submitbutton"  value=" Save " onclick="save(this.form)">
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
			<td rowspan="5" class="brdrR">&nbsp;</td>
		</tr>
	</table>
</div>


<?php
require_once('footer.php');
?>