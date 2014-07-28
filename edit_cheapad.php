<?php
/**
 * Dashboard home Script
 * Author: Diana Devargas 10/06/2011
**/

require_once ('config.php');
require_once ('header.php');

$Cheapad = cheapAds::getInstance ( $db );
global $user;

$cheapad_id = isset ( $_REQUEST ['pid'] ) ? $_REQUEST ['pid'] : '';
$action = isset ( $_REQUEST ['action'] ) ? $_REQUEST ['action'] : '';
$headerMsg = $cheapad_id != '' ? 'Edit Cheapad' : 'New Cheapad';
$Msg = '';

if (! isset ( $user ))
	header ( 'Location: manage_cheapads.php' );

if ($action != '') {
	switch ($action) {
		case 'edit' :
			foreach ( $_REQUEST as $key => $val ) {
				if ($key != 'action' && $key != 'pid' && $key != 'PHPSESSID' && $key != 'webfxtab_tabPane1' && $key != 'logintheme' && $key != 'cprelogin' && $key != 'cpsession' && $key != 'langedit' && $key != 'lang')
					$editarray [$key] = $val;
			}
			
			if ($cheapad_id = $Cheapad->save_cheapad ( $editarray, $cheapad_id ))
				$Msg = 'Changes Saved for ' . (isset ( $editarray ['cheapad_sitehost'] ) ? $editarray ['cheapad_sitehost'] : '');
			break;
		
		case 'new' :
			foreach ( $_REQUEST as $key => $val ) {
				if ($key != 'action' && $key != 'pid' && $key != 'PHPSESSID' && $key != 'webfxtab_tabPane1' && $key != 'logintheme' && $key != 'cprelogin' && $key != 'cpsession' && $key != 'langedit' && $key != 'lang')
					$editarray [$key] = $val;
			}
			
			if ($cheapad_id = $Cheapad->save_cheapad ( $editarray ))
				$Msg = 'Cheapad Added for ' . (isset ( $editarray ['cheapad_sitehost'] ) ? $editarray ['cheapad_sitehost'] : '');
			break;
	}
}
?>
<style>
#box {
	width: 100%;
	text-align: center;
}

#cheapad_table {
	border-collapse: collapse;
	background: #FFFFFF;
	margin: auto;
	text-align: left
}

#cheapad_table table,#cheapad_table td,#cheapad_table th {
	border: 1px solid black;
}

#cheapad_table img {
	margin: 10px;
	border: 0 none;
}

#pager img {
	margin: 1px;
	border: 0 none;
}

#cheapad_table a,#pager a {
	text-decoration: none;
}

.a-center {
	text-align: center;
}

#main {
	text-align: left;
}
</style>
<div id="box">
	<table style="margin: auto;" width="95%" border="0" cellspacing="0"
		cellpadding="0">
		<tr>

			<td>&nbsp;</td>
			<td valign="top" id="main">
				<?php
				if ($Msg != '') {
					?>
<table border="0" cellpadding="0" cellspacing="0" width="100%"
					id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
							<div class="content" align="center">
								<font color="Green"><?php echo $Msg;?></font>

							</div></td>
					</tr>
				</table>

<?php
				}
				?>		
			
			<!-- *** START MAIN CONTENTS  *** -->

				<div>
					<span class="txtHdr" style="float: left;"><?php echo $headerMsg;?></span>
				</div> <br> <br>
<?php
if ($cheapad_id != '') {
	if ($cheapadRow = $Cheapad->get_cheapad_info ( $cheapad_id )) :
		$orig_title = $cheapadRow ['cheapad_title'];
		?>

		   <form id="form" method="post" action="edit_cheapad.php">
					<table align="center" border="0" cellpadding="3" cellspacing="0"
						width="100%" id="boxGray">
						<tr>
							<td width="10%" valign="top">Title:</td>
							<td><input type="text" size="162" name="cheapad_title"
								tabindex="1" value="<?php echo $cheapadRow['cheapad_title']; ?>" />
							</td>
						</tr>
						<tr>
							<td width="30" valign="top">Feedtype:</td>
							<td>
								<select name="cheapad_feedtype">
								<option value=''<?php echo $cheapadRow['cheapad_feedtype'] == '' ? ' selected' : '';?>>Select ...</option>
				                <option value='TZ'<?php echo $cheapadRow['cheapad_feedtype'] == 'TZ' ? ' selected' : '';?>>TrafficZ</option>
				                <option value='TZ-2'<?php echo $cheapadRow['cheapad_feedtype'] == 'TZ-2' ? ' selected' : '';?>>TrafficZ 2 - Clean Referer</option>
			                    <option value='TS'<?php echo $cheapadRow['cheapad_feedtype'] == 'TS' ? ' selected' : '';?>>TrafficScoring</option>
			                    <option value='TS-2'<?php echo $cheapadRow['cheapad_feedtype'] == 'TS-2' ? ' selected' : '';?>>TrafficScoring 2 - Clean Referer</option>
				                <option value='VC'<?php echo $cheapadRow['cheapad_feedtype'] == 'VC' ? ' selected' : '';?>>ValidClick</option>
				                <option value='OB'<?php echo $cheapadRow['cheapad_feedtype'] == 'OB' ? ' selected' : '';?>>OB Media</option>
				                <option value='IS'<?php echo $cheapadRow['cheapad_feedtype'] == 'IS' ? ' selected' : '';?>>InfoSpace</option>
				                <option value='TS'<?php echo $cheapadRow['cheapad_feedtype'] == 'TS' ? ' selected' : '';?>>Traffic Scoring</option>
				                <option value='Parked'<?php echo $cheapadRow['cheapad_feedtype'] == 'Parked' ? ' selected' : '';?>>Parked</option>
				                </select>
							</td>
						</tr>
						<tr>
							<td width="30" valign="top">Sitehost:</td>
							<td><input type="text" size="162" name="cheapad_sitehost"
								tabindex="3"
								value="<?php echo $cheapadRow['cheapad_sitehost']; ?>" /></td>
						</tr>
						<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
						<script type="text/javascript">
								tinyMCE.init({
								// General options
								mode : "exact",
								elements : "cheapad_description",
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
							<td width="30" valign="top">Description:</td>
							<td><textarea name="cheapad_description" tabindex="4"
									style="width: 1000px; height: 400px;"><?php echo $cheapadRow['cheapad_description']; ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="2" align="center"><input type="hidden" name="action"
								id="action" value="edit" /> <input type="hidden" name="pid"
								value="<?php echo $cheapadRow['cheapad_id']; ?>" /> <input
								id="button1" type="submit" value="Save"
								onclick="document.forms['form'].elements['action'].value = 'edit'; submit();" />
							</td>
						</tr>
					</table>
				</form>

	<?php 
	endif;
} else {
	?>
	    <form id="form" method="post" action="edit_cheapad.php">
					<table align="center" border="0" cellpadding="3" cellspacing="0"
						width="100%" id="boxGray">
						<tr>
							<td width="30" valign="top">Title:</td>
							<td><input type="text" size="162" name="cheapad_title"
								tabindex="1" value="" /></td>
						</tr>
						<tr>
							<td width="30" valign="top">Feedtype:</td>
							<td>
								<select name="cheapad_feedtype">
								<option value='' selected>Select ...</option>
				                <option value='TZ'>TrafficZ</option>
				                <option value='TZ-2'>TrafficZ 2 - Clean Referer</option>
			                    <option value='TS'>TrafficScoring</option>
			                    <option value='TS-2'>TrafficScoring 2 - Clean Referer</option>
				                <option value='VC'>ValidClick</option>
				                <option value='OB'>OB Media</option>
				                <option value='IS'>InfoSpace</option>
				                <option value='TS'>Traffic Scoring</option>
				                <option value='Parked'>Parked</option>
				                </select>
							</td>
						</tr>
						<tr>
							<td width="30" valign="top">Sitehost:</td>
							<td><input type="text" size="162" name="cheapad_sitehost"
								tabindex="3" value="" /></td>
						</tr>
						<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
						<script type="text/javascript">
							tinyMCE.init({
								// General options
								mode : "exact",
								elements : "cheapad_description",
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
							<td width="30" valign="top">Description:</td>
							<td><textarea name="cheapad_description" tabindex="4"
									style="width: 1000px; height: 400px;"></textarea></td>
						</tr>
						<tr>
							<td colspan="2" align="center"><input type="hidden" name="action"
								value="new" /> <input id="button1" type="submit" value="Add" />
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
require_once ('footer.php');
?>