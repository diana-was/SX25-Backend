<?php
/**
 * Dashboard home Script
 * Author: Diana Devargas 10/06/2011
**/

require_once('config.php');
require_once('header.php');

$Goal = Goal::getInstance($db);

$goal_id    = isset($_REQUEST['gid']) ? $_REQUEST['gid'] : '';
$action		= isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$headerMsg 	= $goal_id != '' ? 'Edit Goal' : 'New Goal';
$Msg 		= '';

	
if($action != '')
{
	switch ($action)
	{
		case 'edit':
			foreach($_REQUEST as $key => $val)
			{
				if(stripos($key,'goal_') !== false) 
					$editarray[$key] = $val; 
			}

			$editarray['goal_update_date'] = date("Y-m-d H:i:s");
			$editarray['goal_completion_date'] = empty($editarray['goal_completion_date'])?'':$editarray['goal_completion_date'];
			
			if($Goal->save_goal($editarray,$goal_id))
				$Msg = 'Changes Saved for '.(isset($editarray['goal_subject'])?$editarray['goal_subject']:'');
			break;
	
		case 'new':
			foreach($_REQUEST as $key => $val)
			{
				if(stripos($key,'goal_') !== false) 
					$editarray[$key] = $val;
			}
			
			$editarray['goal_update_date'] 		= date("Y-m-d H:i:s");
			$editarray['goal_approved'] 		= 1;
			$editarray['goal_completion_date'] 	= (empty($editarray['goal_completion_date']) || strtotime($editarray['goal_completion_date']) < 1325404800)?'':$editarray['goal_completion_date'];
			$editarray['goal_completion'] 		= !empty($editarray['goal_completion_date'])?100:(is_numeric($editarray['goal_completion'])?$editarray['goal_completion']:0);
			
			if($goal_id=$Goal->save_goal($editarray))
				$Msg = 'Goal Added for '.(isset($editarray['goal_subject'])?$editarray['goal_subject']:'');
			else
				$goal_id = '';
			break;
	}
}
?>
<style>
#box{
width:100%;
text-align:center;
}

#goal_table
{
border-collapse:collapse;
background:#FFFFFF;
margin: auto;
text-align:left
}
#goal_table table, #goal_table td, #goal_table th
{
border:1px solid black;
}
#goal_table img{margin:10px;  border: 0 none;}
#pager img{margin:1px;  border: 0 none;}
#goal_table a, #pager a{text-decoration: none;}
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
if($goal_id != '')
{
	if($goalRow = $Goal->get_goal_info($goal_id)) :
			$orig_keyword = $goalRow['goal_keyword']; ?>

			<form id="form" method="post" action="edit_goal.php">
				<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td width="9%" valign="top" align="right">Subject:</td>
						<td>
					 		<input type="text" size="162" name="goal_subject" tabindex="1" value="<?php echo $goalRow['goal_subject']; ?>" /> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Keyword:</td>
						<td>
					 		<input type="text" size="162" name="goal_keyword" tabindex="2" value="<?php echo $goalRow['goal_keyword']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Username:</td>
						<td>
					 		<input type="text" size="162" name="goal_visitor" tabindex="3" value="<?php echo $goalRow['goal_visitor']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">User Photo URL:</td>
						<td>
					 		<input type="text" size="162" name="goal_user_photo" tabindex="4" value="<?php echo $goalRow['goal_user_photo']; ?>"/> 
						</td>
					</tr>
					<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
					<script type="text/javascript">
					tinyMCE.init({
						// General options
						mode : "exact",
						elements : "goal_content",
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
						<td valign="top" align="right">Content:</td>
						<td>
							<textarea name="goal_content" class="mceQuestion"  tabindex="5" style="width:1000px;height:400px;"><?php echo $goalRow['goal_content']; ?></textarea>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Start Date (YYYY-MM-DD):</td>
						<td>
					 		<input type="text" size="10" name="goal_start_date" tabindex="6" value="<?php echo $goalRow['goal_start_date']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Start Status:</td>
						<td>
					 		<input type="text" size="162" name="goal_start_status" tabindex="7" value="<?php echo $goalRow['goal_start_status']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Target Date (YYYY-MM-DD):</td>
						<td>
					 		<input type="text" size="10" name="goal_target_date" tabindex="8" value="<?php echo $goalRow['goal_target_date']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Target Status:</td>
						<td>
					 		<input type="text" size="162" name="goal_target_status" tabindex="9" value="<?php echo $goalRow['goal_target_status']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">% of Completion:</td>
						<td>
					 		<input type="text" size="10" name="goal_completion" tabindex="10" value="<?php echo $goalRow['goal_completion']; ?>"/>%
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Completion Date (YYYY-MM-DD):</td>
						<td>
					 		<input type="text" size="10" name="goal_completion_date" tabindex="11" value="<?php echo $goalRow['goal_completion_date']; ?>"/>
						</td>
					</tr>
				 	<tr>
				 		<td colspan="2" align="center">
				  			<input type="hidden" name="action" id="action" value="edit" />
							<input type="hidden" name="gid" value="<?php echo $goalRow['goal_id']; ?>" />
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
			<form id="form" method="post" action="edit_goal.php">
				<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td width="9%" valign="top" align="right">Subject:</td>
						<td>
					 		<input type="text" size="162" name="goal_subject" tabindex="1" value="" /> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Keyword:</td>
						<td>
					 		<input type="text" size="162" name="goal_keyword" tabindex="2" value=""/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Username:</td>
						<td>
					 		<input type="text" size="162" name="goal_visitor" tabindex="3" value=""/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">User Photo URL:</td>
						<td>
					 		<input type="text" size="162" name="goal_user_photo" tabindex="4" value=""/> 
						</td>
					</tr>
					<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
					<script type="text/javascript">
					tinyMCE.init({
						// General options
						mode : "exact",
						elements : "goal_content",
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
						<td valign="top" align="right">Content:</td>
						<td>
							<textarea name="goal_content" class="mceQuestion"  tabindex="5" style="width:1000px;height:400px;"></textarea>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Start Date (YYYY-MM-DD):</td>
						<td>
					 		<input type="text" size="10" name="goal_start_date" tabindex="6" value=""/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Start Status:</td>
						<td>
					 		<input type="text" size="162" name="goal_start_status" tabindex="7" value=""/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Target Date (YYYY-MM-DD):</td>
						<td>
					 		<input type="text" size="10" name="goal_target_date" tabindex="8" value=""/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Target Status:</td>
						<td>
					 		<input type="text" size="162" name="goal_target_status" tabindex="9" value=""/> 
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">% of Completion:</td>
						<td>
					 		<input type="text" size="10" name="goal_completion" tabindex="10" value=""/>%
						</td>
					</tr>
					<tr>
						<td valign="top" align="right">Completion Date (YYYY-MM-DD):</td>
						<td>
					 		<input type="text" size="10" name="goal_completion_date" tabindex="11" value=""/>
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