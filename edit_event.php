<?php
/**
 * Dashboard home Script
 * Author: Diana Devargas 30/12/2011
**/

require_once('config.php');
require_once('header.php');

$Event = Event::getInstance($db);

$event_id = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$action		= isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$headerMsg 	= $event_id != '' ? 'Edit Event' : 'New Event';
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

			$editarray['event_update_date'] = date("Y-m-d H:i:s");
			if (empty($editarray['event_stop_time']))
				unset($editarray['event_stop_time']);
		
			if($Event->save_event($editarray,$event_id))
				$Msg = 'Changes Saved for '.(isset($editarray['event_title'])?$editarray['event_title']:'');
			break;
	
		case 'new':
			foreach($_REQUEST as $key => $val)
			{
				if($key != 'action' && $key != 'pid' && $key != 'PHPSESSID'  && $key != 'webfxtab_tabPane1' && $key != 'logintheme' && $key != 'cprelogin' && $key != 'cpsession' && $key != 'langedit' && $key != 'lang')
					$editarray[$key] = $val;
			}
			
			$editarray['event_update_date'] = date("Y-m-d H:i:s");
			if (empty($editarray['event_stop_time']))
				unset($editarray['event_stop_time']);
			
			if($event_id=$Event->save_event($editarray))
				$Msg = 'Event Added for '.(isset($editarray['event_title'])?$editarray['event_title']:'');
			else
				$event_id = '';
			break;
	}
}
?>
<style>
#box{
width:100%;
text-align:center;
}

#event_table
{
border-collapse:collapse;
background:#FFFFFF;
margin: auto;
text-align:left
}
#event_table table, #event_table td, #event_table th
{
border:1px solid black;
}
#event_table img{margin:10px;  border: 0 none;}
#pager img{margin:1px;  border: 0 none;}
#event_table a, #pager a{text-decoration: none;}
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
if($event_id != '')
{
	if($eventRow = $Event->get_event_info($event_id)) :
			$orig_keyword = $eventRow['event_keyword']; ?>

		   <form id="form" method="post" action="edit_event.php">
			<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td width="10%" valign="top">Subject:</td>
						<td>
					 		<input type="text" size="162" name="event_title" tabindex="1" value="<?php echo $eventRow['event_title']; ?>" /> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Keyword:</td>
						<td>
					 		<input type="text" size="162" name="event_keyword" tabindex="2" value="<?php echo $eventRow['event_keyword']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Start (YY-MM-DD hh:mm:ss):</td>
						<td>
					 		<input type="text" size="162" name="event_start_time" tabindex="3" value="<?php echo date('Y-m-d H:i:s',strtotime($eventRow['event_start_time'])); ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">End (YY-MM-DD hh:mm:ss):</td>
						<td>
					 		<input type="text" size="162" name="event_stop_time" tabindex="4" value="<?php if (!empty($eventRow['event_stop_time'])) echo date('Y-m-d H:i:s',strtotime($eventRow['event_stop_time'])); ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Event URL:</td>
						<td>
					 		<input type="text" size="162" name="event_url" tabindex="5" value="<?php echo $eventRow['event_url']; ?>"/> 
						</td>
					</tr>
					<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
					<script type="text/javascript">
					tinyMCE.init({
						// General options
						mode : "exact",
						elements : "event_description",
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
						<td width="30" valign="top">Description:</td>
						<td>
							<textarea name="event_description" class="mceQuestion"  tabindex="6" style="width:1000px;height:400px;"><?php echo $eventRow['event_description']; ?></textarea>
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Image:</td>
						<td>
					 		<input type="text" size="162" name="event_image_url" tabindex="7" value="<?php echo $eventRow['event_image_url']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Country:</td>
						<td>
					 		<input type="text" size="162" name="event_country_name" tabindex="8" value="<?php echo $eventRow['event_country_name']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Region:</td>
						<td>
					 		<input type="text" size="162" name="event_region_name" tabindex="9" value="<?php echo $eventRow['event_region_name']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">City:</td>
						<td>
					 		<input type="text" size="162" name="event_city_name" tabindex="10" value="<?php echo $eventRow['event_city_name']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Postal Code:</td>
						<td>
					 		<input type="text" size="162" name="event_postal_code" tabindex="11" value="<?php echo $eventRow['event_postal_code']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Venue Name:</td>
						<td>
					 		<input type="text" size="162" name="event_venue_name" tabindex="12" value="<?php echo $eventRow['event_venue_name']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Venue Adress:</td>
						<td>
					 		<input type="text" size="162" name="event_venue_address" tabindex="13" value="<?php echo $eventRow['event_venue_address']; ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Venue URL:</td>
						<td>
					 		<input type="text" size="162" name="event_venue_url" tabindex="14" value="<?php echo $eventRow['event_venue_url']; ?>"/> 
						</td>
					</tr>
				 	<tr>
				 		<td colspan="2" align="center">
				  			<input type="hidden" name="action" id="action" value="edit" />
							<input type="hidden" name="pid" value="<?php echo $eventRow['event_id']; ?>" />
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
	    <form id="form" method="post" action="edit_event.php">
		<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td width="10%" valign="top">Subject:</td>
						<td>
					 		<input type="text" size="162" name="event_title" tabindex="1" value="" /> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Keyword:</td>
						<td>
					 		<input type="text" size="162" name="event_keyword" tabindex="2" value=""/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Start (YY-MM-DD hh:mm:ss):</td>
						<td>
					 		<input type="text" size="162" name="event_start_time" tabindex="3" value="<?php echo date('Y-m-d H:i:s',strtotime('now')); ?>"/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">End (YY-MM-DD hh:mm:ss):</td>
						<td>
					 		<input type="text" size="162" name="event_stop_time" tabindex="4" value=""/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Event URL:</td>
						<td>
					 		<input type="text" size="162" name="event_url" tabindex="5" value=""/> 
						</td>
					</tr>
					<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
					<script type="text/javascript">
					tinyMCE.init({
						// General options
						mode : "exact",
						elements : "event_description",
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
						<td width="30" valign="top">Description:</td>
						<td>
							<textarea name="event_description" class="mceQuestion"  tabindex="6" style="width:1000px;height:400px;"></textarea>
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Image:</td>
						<td>
					 		<input type="text" size="162" name="event_image_url" tabindex="7" value=""/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Country:</td>
						<td>
					 		<input type="text" size="162" name="event_country_name" tabindex="8" value=""/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Region:</td>
						<td>
					 		<input type="text" size="162" name="event_region_name" tabindex="9" value=""/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">City:</td>
						<td>
					 		<input type="text" size="162" name="event_city_name" tabindex="10" value=""/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Postal Code:</td>
						<td>
					 		<input type="text" size="162" name="event_postal_code" tabindex="11" value=""/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Venue Name:</td>
						<td>
					 		<input type="text" size="162" name="event_venue_name" tabindex="12" value=""/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Venue Adress:</td>
						<td>
					 		<input type="text" size="162" name="event_venue_address" tabindex="13" value=""/> 
						</td>
					</tr>
					<tr>
						<td width="30" valign="top">Venue URL:</td>
						<td>
					 		<input type="text" size="162" name="event_venue_url" tabindex="14" value=""/> 
						</td>
					</tr>
				 	<tr>
				 		<td colspan="2" align="center">
				  			<input type="hidden" name="action" id="action" value="new" />
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