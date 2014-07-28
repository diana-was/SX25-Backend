<?php
/**
 * Dashboard home Script
 * Author: Archie Huang on 28/01/2009
 * Updated by : Diana Devargas 27/05/2011
**/

$pageCat = 'Articles';
require_once('config.php');
require_once('header.php');

$Article = Article::getInstance($db);
$Site = Site::getInstance($db); 

$article_id = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$domain_id 	= isset($_REQUEST['domain_id']) ? $_REQUEST['domain_id'] : '';
$domain_url = isset($_REQUEST['domain']) ? $_REQUEST['domain'] : '';
$action		= isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$orig_id 	= isset($_REQUEST['orig_id']) ? $_REQUEST['orig_id'] : '';
$startnum 	= -1;
$headerMsg 	= $article_id != '' ? 'Edit Article' : 'New Article';
$Msg 		= '';

if (empty($orig_id))
	$orig_id = $article_id;
	
if($action != '')
{
	switch ($action)
	{
		case 'edit':
		case 'reset':
			foreach($_REQUEST as $key => $val)
			{
				if($key != 'action' && $key != 'pid' && $key != 'PHPSESSID'  && $key != 'webfxtab_tabPane1' && $key != 'logintheme' && $key != 'cprelogin' && $key != 'cpsession' && $key != 'langedit' && $key != 'lang')
					$editarray[$key] = $val; 
			}
			
			// if reset get the original article
			if($action == 'reset')
			{
				if (($article_id != $orig_id) & !empty($orig_id))
				{
					$artarray = $Article->get_article_info($orig_id);
					
					if ($artarray)
					{
						if (empty($artarray['article_domain_id']) && empty($artarray['article_domain']))
						{
							$artarray['article_domain_id'] 	= $editarray['article_domain_id'];
							if (empty($artarray['article_domain_id']))
								unset($artarray['article_domain_id']);
								
							$artarray['article_domain']    	= $editarray['article_domain'];
							if (empty($artarray['article_domain']))
								unset($artarray['article_domain']);
							$artarray['article_default']   	= $editarray['article_default'];
							if ($article_id	= $Article->save_article($artarray, $orig_id))
							{
								$Article->unlink_this_article($article_id);
								$article_id = $orig_id;
								$Msg = 'Changes Saved for '.$editarray['article_domain'];
							}
						}				
					}
				}
			}
			else 
			{
				if (empty($editarray['article_domain_id']))
					unset($editarray['article_domain_id']);
					
				if (empty($editarray['article_domain']))
					unset($editarray['article_domain']);

				$editarray['article_update_date'] = date("Y-m-d");
			
				if($Article->save_article($editarray,$article_id))
					$Msg = 'Changes Saved for '.(isset($editarray['article_domain'])?$editarray['article_domain']:'');
			}
			break;
	
		case 'new':
			foreach($_REQUEST as $key => $val)
			{
				if($key != 'action' && $key != 'pid' && $key != 'PHPSESSID'  && $key != 'webfxtab_tabPane1' && $key != 'logintheme' && $key != 'cprelogin' && $key != 'cpsession' && $key != 'langedit' && $key != 'lang')
					$editarray[$key] = $val;
			}
			if (empty($editarray['article_domain_id']))
				unset($editarray['article_domain_id']);
				
			if (empty($editarray['article_domain']))
				unset($editarray['article_domain']);
			$editarray['article_update_date'] = date("Y-m-d");
		
			if($Article->save_article($editarray))
				$Msg = 'Article Added for '.(isset($editarray['article_domain'])?$editarray['article_domain']:'');
			
			break;
			
		case 'next':
			$keyword = $_REQUEST['keyword'];
			$startnum = isset($_REQUEST['startnum'])?$_REQUEST['startnum'] + 1:0;
			$skeyword = $_REQUEST['skeyword'];
			$default = $_REQUEST['default'];
			$article_id = $Article->scrape_article($keyword, $domain_id, $domain_url, $default, 1, $startnum, $article_id);
			if (!empty($article_id))
				$Msg = "Changes Saved for $domain_url";
			break;
			
		case 'previous':
			$keyword = $_REQUEST['keyword'];
			$startnum = (isset($_REQUEST['startnum']) && ($_REQUEST['startnum'] > 0))?$_REQUEST['startnum'] - 1:0;
			$skeyword = $_REQUEST['skeyword'];
			$default = $_REQUEST['default'];
			$article_id = $Article->scrape_article($keyword, $domain_id, $domain_url, $default, 1, $startnum, $article_id);
			if (!empty($article_id))
				$Msg = "Changes Saved for $domain_url";
			break;
	}
}
?>
<style>
#box{
width:100%;
text-align:center;
}

#article_table
{
border-collapse:collapse;
background:#FFFFFF;
margin: auto;
text-align:left
}
#article_table table, #article_table td, #article_table th
{
border:1px solid black;
}
#article_table img{margin:10px;  border: 0 none;}
#pager img{margin:1px;  border: 0 none;}
#article_table a, #pager a{text-decoration: none;}
.a-center { text-align: center;}
#main {  text-align: left;}
</style>
<div id="box">
	<table style="margin:auto;" width="95%" border="0" cellspacing="0" cellpadding="0">
		<tr>

			<td >&nbsp;</td>
			<td valign="top" id="main">
				<?php
if($Msg != '')
{
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
	<tr>
		<td valign="top"><div class="blueHdr">System Message</div>
		<div class="content" align="center">
        <font color="Green"><?php echo $Msg;?></font>

		</div>
		</td>
	</tr>
</table>

<?php
}
?>		
			
			<!-- *** START MAIN CONTENTS  *** -->
			
			<div>
<span class="txtHdr" style="float:left;"><?php echo "$headerMsg: $domain_url";?></span>
</div>
<br>
<br>
<?php
if($article_id != '')
{
	if($articleRow = $Article->get_article_info($article_id)) :
		if($articleRow['article_keyword'] == '')
		{
			$info = $Site->get_domain_info($articleRow['article_domain_id']);
			$orig_keyword = $info['domain_keyword'];
		}
		else
			$orig_keyword = $articleRow['article_keyword']; ?>

		   <form id="form" method="post" action="edit_article.php">
			<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
							<tr>
								<td width="10%" valign="top">Title:</td>
								<td>
							 		<input type="text" size="162" name="article_title" tabindex="1" value="<?php echo $articleRow['article_title']; ?>" /> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Keyword:</td>
								<td>
							 		<input type="text" size="162" name="article_keyword" tabindex="3" value="<?php echo $articleRow['article_keyword']; ?>"/> 
								</td>
							</tr>
							<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
							<script type="text/javascript">
								tinyMCE.init({
								// General options
								mode : "exact",
								elements : "article_content",
								theme : "advanced",
								plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

								// Theme options
								theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
								theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
								theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
								theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
								theme_advanced_toolbar_location : "top",
								theme_advanced_toolbar_align : "left",
								theme_advanced_statusbar_location : "bottom",
								theme_advanced_resizing : true,

								// Example content CSS (should be your site CSS)
								content_css : "css/common.css",

								// Drop lists for link/image/media/template dialogs
								template_external_list_url : "js/template_list.js",
								external_link_list_url : "js/link_list.js",
								external_image_list_url : "js/image_list.js",
								media_external_list_url : "js/media_list.js",

							});
							</script>
							<tr>
								<td width="30" valign="top">Content:</td>
								<td>
									<textarea name="article_content" tabindex="4" style="width:1000px;height:400px;"><?php echo $articleRow['article_content']; ?></textarea>
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Summary:</td>
								<td>
									<textarea name="article_summary" tabindex="4" style="width:1000px;height:100px;"><?php echo $articleRow['article_summary']; ?></textarea>
						  		</td>
							</tr>
						 	<tr>
						 		<td colspan="2" align="center">
						  			<input type="hidden" name="action" id="action" value="edit" />
									<input type="hidden" name="pid" value="<?php echo $articleRow['article_id']; ?>" />
								  	<input type="hidden" name="article_domain_id" value="<?php echo $articleRow['article_domain_id']; ?>" />
								  	<input type="hidden" name="article_domain" value="<?php echo $articleRow['article_domain']; ?>" />
						  			<input id="button1" type="submit" value="Save" onclick="document.forms['form'].elements['action'].value = 'edit'; submit();"/> 
						  			<input id="button2" type="reset" value="Reset" onclick="document.forms['form'].elements['action'].value = 'reset'; submit();"/>
						  		</td>
							</tr>
				</table>
				</form>
				<fieldset id="options">
				<legend>Get a New Article</legend>
				<br>
				<div align="center">
					<form id="formButton1" method="post" action="edit_article.php">
					  	<input type="hidden" name="action" value="next" />
					  	<input type="hidden" name="pid" value="<?php echo $articleRow['article_id']; ?>" />
					  	<input type="hidden" name="keyword" value="<?php echo $orig_keyword; ?>" />
					  	<input type="hidden" name="skeyword" value="<?php echo $articleRow['article_keyword']; ?>" />
					  	<input type="hidden" name="default" value="<?php echo $articleRow['article_default']; ?>" />
					  	<input type="hidden" name="domain_id" value="<?php echo $articleRow['article_domain_id']; ?>" />
					  	<input type="hidden" name="domain" value="<?php echo $articleRow['article_domain']; ?>" />
					    <input type="hidden" name="startnum" value="<?php echo $startnum; ?>" />
					    <input id="button1" type="submit" value="Get Next" /> 
				    </form>
				  
				  	<form id="formButton2" method="post" action="edit_article.php">
				  		<input type="hidden" name="action" value="previous" />
				  		<input type="hidden" name="pid" value="<?php echo $articleRow['article_id']; ?>" />
				  		<input type="hidden" name="keyword" value="<?php echo $orig_keyword; ?>" />
				   		<input type="hidden" name="skeyword" value="<?php echo $articleRow['article_keyword']; ?>" />
					  	<input type="hidden" name="default" value="<?php echo $articleRow['article_default']; ?>" />
					  	<input type="hidden" name="domain_id" value="<?php echo $articleRow['article_domain_id']; ?>" />
					  	<input type="hidden" name="domain" value="<?php echo $articleRow['article_domain']; ?>" />
				   		<input type="hidden" name="startnum" value="<?php echo $startnum; ?>" />
				  		<input id="button1" type="submit" value="Get Previous" /> 
				  	</form>
				</div>
				</fieldset>
<?php 
	endif;
} 
else
{ 
	$domainInfo = $Site->get_domain_info_name($domain_url);
	if ($domainInfo)
	{
		$domain_url = $domainInfo['domain_url'];
		$domain_id = $domainInfo['domain_id'];
	} 
		
?>
	    <form id="form" method="post" action="edit_article.php">
		<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
							<tr>
								<td width="30" valign="top">Title:</td>
								<td>
							 		<input type="text" size="162" name="article_title" tabindex="1" value="" /> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Keyword:</td>
								<td>
							 		<input type="text" size="162" name="article_keyword" tabindex="3" value=""/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Author:</td>
								<td>
							 		<input type="text" name="article_author" tabindex="3" value=""/> 
								</td>
							</tr>
							<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
							<script type="text/javascript">
							tinyMCE.init({
								// General options
								mode : "exact",
								elements : "article_content",
								theme : "advanced",
								plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

								// Theme options
								theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
								theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
								theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
								theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
								theme_advanced_toolbar_location : "top",
								theme_advanced_toolbar_align : "left",
								theme_advanced_statusbar_location : "bottom",
								theme_advanced_resizing : true,

								// Example content CSS (should be your site CSS)
								content_css : "css/common.css",

								// Drop lists for link/image/media/template dialogs
								template_external_list_url : "js/template_list.js",
								external_link_list_url : "js/link_list.js",
								external_image_list_url : "js/image_list.js",
								media_external_list_url : "js/media_list.js",

							});
							</script>
							<tr>
								<td width="30" valign="top">Content:</td>
								<td>
									<textarea name="article_content" tabindex="4" style="width:1000px;height:400px;"></textarea>
							 	</td>
							</tr>
							<tr>
								<td width="30" valign="top">Summary:</td>
								<td>
							 		<textarea name="article_summary" tabindex="4" style="width:1000px;height:100px;"></textarea>
						  		</td>
							</tr>
						 	<tr>
						 		<td colspan="2" align="center">
						  			<input type="hidden" name="action" value="new" />
									<input type="hidden" name="article_domain_id" value="<?php echo $domain_id; ?>" />
									<input type="hidden" name="article_domain" value="<?php echo $domain_url; ?>" />
						  			<input id="button1" type="submit" value="Add" /> 
						  			<input id="button2" type="reset" value="Reset"  />
						  		</td>
							</tr>
		</table>
		</form>
<?php } ?>
			</td>
			<td rowspan="5">&nbsp;</td>
		</tr>
	</table>

</div>
<?php
require_once('footer.php');
?>