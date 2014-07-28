<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once('config.php');
$Layout = new LayoutParked();
$File = File::getInstance();

$layoutInfo = array('layout_name' => ''
						, 'layout_folder' => ''
						, 'layout_landing' => ''
						, 'layout_result' => ''
						, 'layout_css' => ''
						, 'layout_js' => ''
						, 'layout_sponsored_num' => 0
						, 'layout_sponsored' => ''
						, 'layout_fileopen_1' => ''
						, 'layout_news_read_1' => ''
						, 'layout_fileopen_2' => ''
						, 'layout_news_read_2' => '');
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
			$resp = $File->upload_files(array('image_files' => $layoutFolder.$layoutInfo['layout_folder'].'/images' , 'other_files' => $layoutFolder.$layoutInfo['layout_folder']));
			echo (empty($resp)?'Error Loading':$resp);
			exit;
		}
	}
	echo 'error';
	exit;
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'parked_layout')
{
	$layoutInfo = array('layout_name' => $_REQUEST['layout_name']
						, 'layout_folder' => $_REQUEST['layout_folder']
						, 'layout_landing' => replace_tag($_REQUEST['layout_landing'])
						, 'layout_result' => replace_tag($_REQUEST['layout_result'])
						, 'layout_css' => $_REQUEST['layout_css']
						, 'layout_js' => replace_tag($_REQUEST['layout_js'])
						, 'layout_sponsored_num' => $_REQUEST['layout_sponsored_num']
						, 'layout_sponsored' => replace_tag($_REQUEST['layout_sponsored'])
						, 'layout_fileopen_1' => $_REQUEST['layout_fileopen_1']
						, 'layout_news_read_1' => $_REQUEST['layout_news_read_1']
						, 'layout_fileopen_2' => $_REQUEST['layout_fileopen_2']
						, 'layout_news_read_2' => $_REQUEST['layout_news_read_2']);
	
	// Validate errors
	$name = trim($_REQUEST['layout_name']);
	if (empty($name)) $error = 'true';
	$folderName = trim($_REQUEST['layout_folder']);
	if (empty($folderName)) $error = 'true';
	if ($error) {
			$layoutSave = '<font color="red">Layout NOT Saved, Name or File Name Missing!!</font>';
	}
	
	$checkID = $Layout->check_layout_id($name);
	if (empty($_REQUEST['layout_id']) && ($checkID > 0)) {
		$error = 'true';
		$layoutSave = '<font color="red">Layout name already exist!</font>';
	}

	$checkFolder = $Layout->check_layout_folder($folderName);
	if ((empty($_REQUEST['layout_id']) && ($checkFolder > 0)) 
	|| (!empty($_REQUEST['layout_id']) && ($checkFolder > 0) && ($checkFolder != $_REQUEST['layout_id']))) {
		$error = 'true';
		$layoutSave = '<font color="red">Folder name already exist!</font>';
	}
	
	if (!$error) {
		$layout_id = empty($_REQUEST['layout_id']) ? 0 : $_REQUEST['layout_id'];
		$new_layout_id = $Layout->save_layout($layoutInfo,$layout_id,$error);
		
		if($new_layout_id && empty($error))
		{
			$layoutSave = '<font color="green">Layout Saved</font>';
		} 
		else 
		{
			$layoutSave = '<font color="red">$error</font>';
		}
		$_REQUEST['layout_id'] = $new_layout_id;
		
		// Upload files
		//$File->upload_files(array('image_files' => $layoutFolder.$folderName.'/images' , 'other_files' => $layoutFolder.$folderName));
		
	} else {
		$layoutInfo['layout_id'] = isset($_REQUEST['layout_id'])?$_REQUEST['layout_id']:'';
		foreach ($layoutInfo as $key => $val) {
			$layoutInfo[$key] = stripslashes($val);
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
			$imageFiles = $File->getDirectoryList($layoutFolder.$layoutInfo['layout_folder'].'/images'); 
			foreach ($imageFiles as $f)
			{
				$a['src'] = $layoutLink.$layoutInfo['layout_folder'].'/images/'.$f;
				$a['name'] = $f;
				$data[] = $a;
			}
		}
		if ($_REQUEST['action'] == 'get_files') 
		{
			$otherFiles = $File->getDirectoryList($layoutFolder.$layoutInfo['layout_folder']); 
			foreach ($otherFiles as $f)
			{
				$a['href'] = $layoutLink.$layoutInfo['layout_folder'].'/'.$f;
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
<link href="css/parked_layout.css" rel="stylesheet" type="text/css">

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

    <?php if (isset($layoutInfo['layout_id']) && !empty($layoutInfo['layout_id'])): ?>
    $(document).ready(function() {
    	refreshImages();
    	refreshFiles();
	});    
    <?php endif; ?>
    
	function refreshImages() {
		$("#image_library").html('<img src="images/loading.gif" style="border:0px;" />');
	
		$.getJSON("parked_layout.php", {action:'get_images', layout_id: <?php echo $layoutID; ?>},function(data){
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
	
		$.getJSON("parked_layout.php", {action:'get_files', layout_id: <?php echo $layoutID; ?>},function(data){
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

			fileOpen = (tab == "layout_result")?form.layout_fileopen_2.value:form.layout_fileopen_1.value;
			fileOpen = replaceTags(fileOpen);
			
			newsRead = (tab == "layout_result")?form.layout_news_read_2.value:form.layout_news_read_1.value;
			newsRead = replaceTags(newsRead);
							
			infValue = infValue.replace(/\{FILE_OPEN\}/g, '<div id="FILE_OPEN"></div>' ); 
			infValue = infValue.replace(/\{NEWS_READ\}/g, '<div id="NEWS_READ"></div>') ; 
			// exist document ready?
			var docReady = (infValue.search(/\$\s*\(document\)\s*.\s*ready/) != -1) || (form.layout_js.value.search(/\$\s*\(document\)\s*.\s*ready/) != -1);
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

			js = '<' + 'script type="text/javascript"> ';
			if (fileOpen != '')	
				js += " $.get('openFilePreview.php', { link: '" + fileOpen + "'}, function(data) {  $('#FILE_OPEN').html(data); "+ callReady +" }); ";
			if (newsRead != '')	
				js += " $('#NEWS_READ').load('openFilePreview.php', { link: '" + newsRead + "'}); ";
			if (fileOpen == '')	{
				js += callReady;
			}
			js += '<'  + '/script><' + '/body>';
			infValue = infValue.replace(/\<\/body\>/i, js);

			win = window.open("","Preview","scrollbars=yes,resizable=yes,status=0,width=1024,height=768");
			win.document.write("" + infValue + "");

			var css 	= win.document.createElement('style');
			css.type 	= 'text/css';
			css.id 		= 'cssID'; 
			var cssValue  = form.layout_css.value;
			cssValue	= cssValue.replace(/url\(image/g, 'url(<?php echo $layoutLink ?>' + $('#layout_folder').val() + '/image'); 
			css.innerHTML = replaceTags(cssValue); 

			//Create the Script Object
			var script	 = win.document.createElement('script');
			script.type  = 'text/javascript';
			script.defer = false;
			script.id    = 'scriptJS';
			script.innerHTML = replaceTags(form.layout_js.value.replace(/\$\s*\(document\)\s*.\s*ready/, ' ready = '));   
			
			var head = win.document.getElementsByTagName('head').item(0);
			head.appendChild(css);
			head.appendChild(script);

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
		infValue = infValue.replace(/\{PROFILE_LINK\}/g, '<?php echo $layoutLink ?>' + $('#layout_folder').val() + '/'); 
		infValue = infValue.replace(/\{CAT1_LINK3\}/g, 'CAT1_LINK3'); 
		infValue = infValue.replace(/\{CAT1_LINK4\}/g, 'CAT1_LINK4'); 
		infValue = infValue.replace(/\{CAT1_RELATED1\}/g, 'Dvd Movies'); 
		infValue = infValue.replace(/\{CAT1_RELATED2\}/g, 'Movies Playing'); 
		infValue = infValue.replace(/\{CAT1_RELATED3\}/g, 'Dvd Videos'); 
		infValue = infValue.replace(/\{CAT1_RELATED4\}/g, 'Italian Movies'); 
		infValue = infValue.replace(/\{CAT2_LINK1\}/g, 'CAT2_LINK1'); 
		infValue = infValue.replace(/\{CAT2_LINK2\}/g, 'CAT2_LINK2'); 
		infValue = infValue.replace(/\{CAT2_LINK3\}/g, 'CAT2_LINK3'); 
		infValue = infValue.replace(/\{CAT2_LINK4\}/g, 'CAT2_LINK4'); 
		infValue = infValue.replace(/\{CAT2_RELATED1\}/g, 'Career Education'); 
		infValue = infValue.replace(/\{CAT2_RELATED2\}/g, 'Human Resources Jobs'); 
		infValue = infValue.replace(/\{CAT2_RELATED3\}/g, 'Part Time Jobs'); 
		infValue = infValue.replace(/\{CAT2_RELATED4\}/g, 'Accounting'); 
		infValue = infValue.replace(/\{CAT3_LINK1\}/g, 'CAT3_LINK1'); 
		infValue = infValue.replace(/\{CAT3_LINK2\}/g, 'CAT3_LINK2'); 
		infValue = infValue.replace(/\{CAT3_LINK3\}/g, 'CAT3_LINK3'); 
		infValue = infValue.replace(/\{CAT3_LINK4\}/g, 'CAT3_LINK4'); 
		infValue = infValue.replace(/\{CAT3_RELATED1\}/g, 'Freight'); 
		infValue = infValue.replace(/\{CAT3_RELATED2\}/g, 'Transportation Management'); 
		infValue = infValue.replace(/\{CAT3_RELATED3\}/g, 'Air'); 
		infValue = infValue.replace(/\{CAT3_RELATED4\}/g, 'Freight Transportation'); 
		infValue = infValue.replace(/\{CATEGORY1_LINK\}/g, 'CATEGORY1_LINK'); 
		infValue = infValue.replace(/\{CATEGORY1\}/g, 'Movies'); 
		infValue = infValue.replace(/\{CATEGORY2_LINK\}/g, 'CATEGORY2_LINK'); 
		infValue = infValue.replace(/\{CATEGORY2\}/g, 'Career'); 
		infValue = infValue.replace(/\{CATEGORY3_LINK\}/g, 'CATEGORY3_LINK'); 
		infValue = infValue.replace(/\{CATEGORY3\}/g, 'Transportation'); 
		infValue = infValue.replace(/\{CATEGORY4_LINK\}/g, 'CATEGORY4_LINK'); 
		infValue = infValue.replace(/\{CATEGORY4\}/g, 'Movies'); 
		infValue = infValue.replace(/\{CLICK_LINK\}/g, 'CLICK_LINK'); 
		infValue = infValue.replace(/\{COPYRIGHT\}/g, '©2008 mydomain.com All rights reserved.'); 
		infValue = infValue.replace(/\{DESCRIPTION\}/g, 'DESCRIPTION'); 
		infValue = infValue.replace(/\{DOMAIN\}/g, 'edu4indian.com'); 
		infValue = infValue.replace(/\{FORSALE\}/g, ''); 
		infValue = infValue.replace(/\{KEYWORDS\}/g, ''); 
		infValue = infValue.replace(/\{LANDING_IMG\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion_one.jpg'); 
		infValue = infValue.replace(/\{LINK1\}/g, 'LINK1'); 
		infValue = infValue.replace(/\{LINK2\}/g, 'LINK2'); 
		infValue = infValue.replace(/\{LINK3\}/g, 'LINK3'); 
		infValue = infValue.replace(/\{LINK4\}/g, 'LINK4'); 
		infValue = infValue.replace(/\{LINK5\}/g, 'LINK5'); 
		infValue = infValue.replace(/\{LINK6\}/g, 'LINK6'); 
		infValue = infValue.replace(/\{LINK7\}/g, 'LINK7'); 
		infValue = infValue.replace(/\{LINK8\}/g, 'LINK8'); 
		infValue = infValue.replace(/\{LINK9\}/g, 'LINK9'); 
		infValue = infValue.replace(/\{LINK10\}/g, 'LINK10'); 
		infValue = infValue.replace(/\{LINK11\}/g, 'LINK11'); 
		infValue = infValue.replace(/\{LINK12\}/g, 'LINK12'); 
		infValue = infValue.replace(/\{LINK13\}/g, 'LINK13'); 
		infValue = infValue.replace(/\{LINK14\}/g, 'LINK14'); 
		infValue = infValue.replace(/\{LINK15\}/g, 'LINK15'); 
		infValue = infValue.replace(/\{META_KEYWORDS\}/g, 'META_KEYWORDS'); 
		infValue = infValue.replace(/\{META_TAG\}/g, ''); 
		infValue = infValue.replace(/\{META_TITLE\}/g, 'META_TITLE'); 
		infValue = infValue.replace(/\{PAGETITLE\}/g, 'PAGETITLE'); 
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
		infValue = infValue.replace(/\{RESULTS_IMG\}/g, '<?php echo $controller->server.$controller->baseURL; ?>images/fashion_two.jpg'); 
		infValue = infValue.replace(/\{R\}/g, 'R'); 
		infValue = infValue.replace(/\{SEARCH_TERM\}/g, 'Movies'); 
		infValue = infValue.replace(/\{SITEHOST\}/g, 'SITEHOST'); 
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
		infValue = infValue.replace(/\{STYLESHEET\}/g, 'style1_8.css'); 
		infValue = infValue.replace(/\{TITLE_LINK\}/g, 'TITLE_LINK'); 
		infValue = infValue.replace(/\{TITLE\}/g, 'Edu 4 Indian'); 
		infValue = infValue.replace(/\{USER_CONTENT\}/g, '');
		return  infValue;
	}
</script>


<!-- *** START MAIN CONTENTS  *** -->
	
<table border="0" cellpadding="0" cellspacing="3" width="100%">
<tr>
	<td colspan="2">
		<div id="boxGray">
			<div class="greenHdr">Parked Layout</div>

				<form action="/parked_layout.php" method="post" enctype="multipart/form-data" name="parked_layout">
		    	<input type="hidden" name="action" value="parked_layout">
		        <div id="custLayout">
		        	<div class="custLabelL">Layout Name: 
		        		<input name="layout_name" type="text" class="frmCLText" value="<?php echo $layoutInfo['layout_name'];?>" size="40" maxlength="30" onchange="change();" <?php if (!isset($_REQUEST['new']) && ($user->userLevel < 6)) echo 'readonly="readonly"';?>>
		        	</div>
		        	<div class="custLabelR">Existing Layouts: 
                   		<select class="inputSelect" name="layout_go" onchange="location.href=this.options[this.selectedIndex].value;">
                    	<option value="/parked_layout.php?new=1" >Create new blank layout</option>
						<?php
						$layouts = $Layout->get_layouts();
						foreach ($layouts as $pRow) 
						{
							echo '<option value="/parked_layout.php?layout_id='.$pRow['layout_id'].'&mod=1"';
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
		        		<input name="layout_folder" id="layout_folder" type="text" class="frmCLUrl" value="<?php echo $layoutInfo['layout_folder'];?>" size="40" maxlength="30" onChange="$('#foldername').html(this.value); change();" <?php if (!isset($_REQUEST['new']) && ($user->userLevel < 6)) echo 'readonly="readonly"';?>>
		        		<span class="error"></span>
		        	</div>	
					<div class="custLabelR">
		        		<span style="color:blue"><br>HELP : Use {PROFILE_LINK} to be replace by the "profile domain" and folder name: http://profileDomain/folderName</span>
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
							<div class="custTextL">Landing Source Code:<br />
								<textarea cols="120" rows="40" name="layout_landing" id="layout_landing" class="frmCLArea" onchange="change();"><?php echo $layoutInfo['layout_landing'];?></textarea>
								<br>
								<span class="error"></span>
				    		</div>
				    		<br class="clearboth" />
				
							<div class="extLabelL">Landing URL File Open:
				        		<input name="layout_fileopen_1" type="text" class="frmCLUrl" value="<?php echo $layoutInfo['layout_fileopen_1'];?>" size="40" onchange="change();">
				        	</div>
				        	<div class="extLabelR">Landing URL News read:
                                <input name="layout_news_read_1" type="text" class="frmCLUrl" value="<?php echo $layoutInfo['layout_news_read_1'];?>" size="40" onchange="change();">
				        	</div>	
			        		<br>
			        		<span class="error"></span>
							<br class="clearboth"><br>
							<p class="alignRight"><input name="preview" type="Button" class="submitbutton" value=" Preview " id="layout_landing" onclick="pView(this.form, 'layout_landing');"></p>
						</div>

						<div class="tab-page" id="tabPage2">
							<h2 class="tab">Results Page</h2>

							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage2" ) );</script>		
							<div class="custTextR">Results Source Code:<br />
								<textarea cols="120" rows="40" name="layout_result" id="layout_result" class="frmCLArea" onchange="change();"><?php echo $layoutInfo['layout_result'];?></textarea>
								<br>
			    				<span class="error"></span>
				    		</div>
							<br class="clearboth">

							<div class="extLabelL">
	                            Number of Sponsored Listings:
								<select class="inputSelect" name="layout_sponsored_num" onchange="change();">
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
							</div>
							<div  class="extLabelR">
								<input name="preview" type="Button" class="submitbutton" value=" Preview " id="layout_result" onclick="pView(this.form, 'layout_result');">
							</div>
							<br><br>
							Sponsored Listings Source Code:<br />
				    		<textarea cols="120" rows="5" name="layout_sponsored"  id="layout_sponsored" class="frmCLArea" onchange="change();"><?php echo $layoutInfo['layout_sponsored'];?></textarea>
			    			<br>
				    		<br class="clearboth" />
							<div>
								<div class="extLabelL">Landing URL File Open:
					        		<input name="layout_fileopen_2" type="text" class="frmCLUrl" value="<?php echo $layoutInfo['layout_fileopen_2'];?>" size="40" onchange="change();">
					        	</div>
					        	<div class="extLabelR">Landing URL News read:
	                                <input name="layout_news_read_2" type="text" class="frmCLUrl" value="<?php echo $layoutInfo['layout_news_read_2'];?>" size="40" onchange="change();">
					        	</div>	
				        	</div>
			        		<br>
			        		<span class="error"></span>
				    		<br class="clearboth" />
						</div>

						<div class="tab-page" id="tabPage3">
							<h2 class="tab">CSS</h2>

							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage3" ) );</script>		
							<div class="custTextR">CSS Source Code:<br /><textarea cols="120" rows="40" name="layout_css" id="layout_css" class="frmCLArea" onchange="change();"><?php echo $layoutInfo['layout_css'];?></textarea>
			    			<span class="error"></span>
				    		</div>
						</div>

						<div class="tab-page" id="tabPage4">
							<h2 class="tab">JS</h2>

							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage4" ) );</script>		
							<div class="custTextR">JS Source Code:<br /><textarea cols="120" rows="40" name="layout_js" id="layout_js" class="frmCLArea" onchange="change();"><?php echo $layoutInfo['layout_js'];?></textarea>
			    			<span class="error"></span>
				    		</div>
						</div>

						<div class="tab-page" id="tabPage5">
							<h2 class="tab">Theme Files</h2>

							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage5" ) );</script>		
				        	<br class="clearboth" />
							<?php if (!empty($layoutInfo['layout_id'])):?>
							<div class="custTextL">
					        	<div class="shortLabelL"><h3>Image Files:</h3></div>
								<br class="clearboth" />
								<div id="image_library" ondblclick="refreshImages();"></div>
								<br class="clearboth" />
								<div id="output" class="dropzone" type="image_files" accept="gif|jpg|png|bmp" request="parked_layout.php?action=upload&layout_id=<?php echo $layoutInfo['layout_id'];?>"  afterUpload="image_library">
									<ul id="output-listing"></ul>
								</div>		
							</div>
							<br class="clearboth" />
							<div class="custTextL">
								<div class="shortLabelL"><h3>Other Files:</h3></div>
								<br class="clearboth" />
								<div id="file_library" ondblclick="refreshFiles();"></div>
								<br class="clearboth" />
								<div id="output" class="dropzone" type="other_files" accept="js|css" request="parked_layout.php?action=upload&layout_id=<?php echo $layoutInfo['layout_id'];?>"  afterUpload="file_library">
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

						<div class="tab-page" id="tabPage6">
							<h2 class="tab">Documentation</h2>
							<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage6" ) );</script>		
							<?php include_once('parked_doc.html'); ?>
						</div>

					  	<br class="clearboth">
						<?php if ($user->userLevel >= 5) : ?>
                        <p  class="alignCenter">
							<input name="apply" type="submit" class="submitbutton"  value=" Save " onclick="save(this.form)">
						</p>
						<?php  endif; ?>
					</div>
					<script type="text/javascript">
					//<![CDATA[
			
					setupAllTabs();
			
					//}>
					</script>
                    <input type="hidden" name="layout_id" value="<?php if (isset($layoutInfo['layout_id'])) echo $layoutInfo['layout_id'];?>" />
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
function replace_tag($string)
{
	$string = str_replace('<', '&lt;', $string);
	$string = str_replace('>', '&gt;', $string);
	return $string;
}
require_once('footer.php');
?>