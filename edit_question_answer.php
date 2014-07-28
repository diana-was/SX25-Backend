<?php
/**
 * Dashboard home Script
 * Author: Diana Devargas 10/06/2011
**/

require_once('config.php');
require_once('header.php');

$QA = QuestionAnswer::getInstance($db);

$question_id = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$action		= isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$headerMsg 	= $question_id != '' ? 'Edit QA' : 'New QA';
$Msg 		= '';

	
if($action != '')
{
	switch ($action)
	{
		case 'edit':
			foreach($_REQUEST as $key => $val)
			{
				if($key != 'action' && $key != 'pid' && $key != 'PHPSESSID'  && $key != 'webfxtab_tabPane1' && $key != 'logintheme' && $key != 'cprelogin' && $key != 'cpsession' && $key != 'langedit' && $key != 'lang')
					$editarray[$key] = $val; 
			}

			$editarray['question_update_date'] = date("Y-m-d H:i:s");
		
			if($QA->save_qa($editarray,$question_id))
				$Msg = 'Changes Saved for '.(isset($editarray['question_subject'])?$editarray['question_subject']:'');
			break;
	
		case 'new':
			foreach($_REQUEST as $key => $val)
			{
				if($key != 'action' && $key != 'pid' && $key != 'PHPSESSID'  && $key != 'webfxtab_tabPane1' && $key != 'logintheme' && $key != 'cprelogin' && $key != 'cpsession' && $key != 'langedit' && $key != 'lang')
					$editarray[$key] = $val;
			}
			
			$editarray['question_update_date'] = date("Y-m-d H:i:s");
					
			if($question_id=$QA->save_qa($editarray))
				$Msg = 'QA Added for '.(isset($editarray['question_subject'])?$editarray['question_subject']:'');
			else
				$question_id = '';
			break;
	}
}
?>
<style>
#box{
width:100%;
text-align:center;
}

#question_table
{
border-collapse:collapse;
background:#FFFFFF;
margin: auto;
text-align:left
}
#question_table table, #question_table td, #question_table th
{
border:1px solid black;
}
#question_table img{margin:10px;  border: 0 none;}
#pager img{margin:1px;  border: 0 none;}
#question_table a, #pager a{text-decoration: none;}
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
<span class="txtHdr" style="float:left;"><?php echo $headerMsg;?></span>
</div>
<br>
<br>
<?php
if($question_id != '')
{
	if($questionRow = $QA->get_qa_info($question_id)) :
			$orig_keyword = $questionRow['question_keyword']; ?>

		   <form id="form" method="post" action="edit_question_answer.php">
			<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
							<tr>
								<td width="10%" valign="top">Subject:</td>
								<td>
							 		<input type="text" size="162" name="question_subject" tabindex="1" value="<?php echo $questionRow['question_subject']; ?>" /> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Keyword:</td>
								<td>
							 		<input type="text" size="162" name="question_keyword" tabindex="3" value="<?php echo $questionRow['question_keyword']; ?>"/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Username:</td>
								<td>
							 		<input type="text" size="162" name="question_username" tabindex="3" value="<?php echo $questionRow['question_username']; ?>"/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">User Photo URL:</td>
								<td>
							 		<input type="text" size="162" name="question_user_photo" tabindex="3" value="<?php echo $questionRow['question_user_photo']; ?>"/> 
								</td>
							</tr>
							<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
							<script type="text/javascript">
							tinyMCE.init({
								// General options
								mode : "exact",
								elements : "question_content",
								theme : "advanced",
								plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
								editor_selector : "mceQuestion",
									
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
								media_external_list_url : "js/media_list.js"

							});

							</script>
							<tr>
								<td width="30" valign="top">Question:</td>
								<td>
									<textarea name="question_content" class="mceQuestion"  tabindex="4" style="width:1000px;height:400px;"><?php echo $questionRow['question_content']; ?></textarea>
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Answerer Username:</td>
								<td>
							 		<input type="text" size="162" name="question_answerer" tabindex="3" value="<?php echo $questionRow['question_answerer']; ?>"/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Answer:</td>
								<td>
									<textarea name="question_answer" class="mceAnswer"  tabindex="4" style="width:1000px;height:400px;"><?php echo $questionRow['question_answer']; ?></textarea>
								</td>
							</tr>
						 	<tr>
						 		<td colspan="2" align="center">
						  			<input type="hidden" name="action" id="action" value="edit" />
									<input type="hidden" name="pid" value="<?php echo $questionRow['question_id']; ?>" />
						  			<input id="button1" type="submit" value="Save" onclick="document.forms['form'].elements['action'].value = 'edit'; submit();"/> 
						  		</td>
							</tr>
				</table>
				</form>
<?php 
	endif;
} 
else
{ 
?>
	    <form id="form" method="post" action="edit_question_answer.php">
		<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
							<tr>
								<td width="30" valign="top">Subject:</td>
								<td>
							 		<input type="text" size="162" name="question_subject" tabindex="1" value="" /> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Keyword:</td>
								<td>
							 		<input type="text" size="162" name="question_keyword" tabindex="3" value=""/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Username:</td>
								<td>
							 		<input type="text" size="162" name="question_username" tabindex="3" value=""/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">User Photo URL:</td>
								<td>
							 		<input type="text" size="162" name="question_user_photo" tabindex="3" value=""/> 
								</td>
							</tr>
							<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
							<script type="text/javascript">
							tinyMCE.init({
								// General options
								mode : "exact",
								elements : "question_content",
								theme : "advanced",
								plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
								editor_selector : "mceQuestion",
									
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
								media_external_list_url : "js/media_list.js"

							});

							</script>
							<tr>
								<td width="30" valign="top">Question:</td>
								<td>
									<textarea name="question_content" class="mceQuestion"  tabindex="4" style="width:1000px;height:400px;"></textarea>
							 	</td>
							</tr>
							<tr>
								<td width="30" valign="top">Answerer Username:</td>
								<td>
							 		<input type="text" size="162" name="question_answerer" tabindex="3" value=""/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Answer:</td>
								<td>
									<textarea name="question_answer" class="mceAnswer"  tabindex="4" style="width:1000px;height:400px;"></textarea>
							 	</td>
							</tr>
						 	<tr>
						 		<td colspan="2" align="center">
					  				<input type="hidden" name="action" value="new" />
					  				<input id="button1" type="submit" value="Add" /> 
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