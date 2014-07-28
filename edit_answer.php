<?php
/**
 * Dashboard home Script
 * Author: Diana Devargas 10/06/2011
**/

require_once('config.php');

$A = Answer::getInstance($db);

$answer_id 	= isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$action		= isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$question_id= isset($_REQUEST['qid']) ? $_REQUEST['qid'] : '';
$headerMsg 	= $answer_id != '' ? 'Edit Answer' : 'New Answer';
$Msg 		= '';

	
if($action != '')
{
	switch ($action)
	{
		case 'edit':
			foreach($_REQUEST as $key => $val)
			{
				if($key != 'action' && $key != 'pid' && $key != 'qid' && $key != 'PHPSESSID'  && $key != 'webfxtab_tabPane1' && $key != 'logintheme' && $key != 'cprelogin' && $key != 'cpsession' && $key != 'langedit' && $key != 'lang')
				{
					$editarray[$key] = (($key == 'answer_content') || ($key == 'answer_user_photo'))?$A->replaceImageLibraryURL ($val,'db'):$val;
				} 
			}

			$editarray['answer_update_date'] = date("Y-m-d H:i:s");
		
			if($A->save_answer($editarray,$answer_id))
				$Msg = 'Changes Saved for '.(isset($editarray['answer_subject'])?$editarray['answer_subject']:'');
			break;
	
		case 'new':
			foreach($_REQUEST as $key => $val)
			{
				if($key != 'action' && $key != 'pid' && $key != 'qid' && $key != 'PHPSESSID'  && $key != 'webfxtab_tabPane1' && $key != 'logintheme' && $key != 'cprelogin' && $key != 'cpsession' && $key != 'langedit' && $key != 'lang')
				{
					$editarray[$key] = (($key == 'answer_content') || ($key == 'answer_user_photo'))?$A->replaceImageLibraryURL ($val,'db'):$val;
				} 
			}
			$editarray['answer_update_date'] = date("Y-m-d H:i:s");
			if(!empty($editarray['question_id']) && ($answer_id=$A->save_answer($editarray)))
				$Msg = 'Answer Added for :'.(isset($editarray['answer_subject'])?$editarray['answer_subject']:'');
			else
			{
				$answer_id = '';
				$Msg = 'ERROR - Data no saved, information missing';
			}
			break;
		case 'upload':
			$ImageLibrary = ImageLibrary::getInstance($db);
			$File = File::getInstance();
			$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
			$keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
			$_FILES["image_files"] = $File->fixFilesArray($_FILES["image_files"]);
			if (!empty($type) && !empty($keyword) && isset($_FILES["image_files"]))
			{
				foreach($_FILES["image_files"] as $_file)
				{
					$resp = $ImageLibrary->saveImage($_file, $type.' - '.$keyword, true, -1);
					echo (empty($resp)?'Error Loading':$resp);
				}
				exit;
			}
			echo 'Error,';
			if (empty($type)) echo ' no type';
			if (empty($keyword)) echo ' no keyword';
			if (!isset($_FILES["image_files"])) echo ' no image';
			exit;
			break;
	}
}
require_once('header.php');
?>
<style>
#box{
width:100%;
text-align:center;
}

#answer_table
{
border-collapse:collapse;
background:#FFFFFF;
margin: auto;
text-align:left
}
#answer_table table, #answer_table td, #answer_table th
{
border:1px solid black;
}
#answer_table img{margin:10px;  border: 0 none;}
#pager img{margin:1px;  border: 0 none;}
#answer_table a, #pager a{text-decoration: none;}
.a-center { text-align: center;}
#main {  text-align: left;}
</style>
<script language="JavaScript">
	var imageLibrary = '<?php echo $config->imageLibrary; ?>';
	
	$(document).ready(function() {
		$("#image_gallery").trigger('ondblclick');
	});

	function imagelibrary(){
		var kw = $('#answer_type').val() + " - " +  $('#answer_keyword').val();
		$("#image_gallery").html('<img src="images/loading.gif" style="border:0px;" />');
	
		$.getJSON("editImage.php", {action:'getImageFromLibrary', keyword: kw, start:1, limit:0, aproved:-1},function(data){
			$("#image_gallery").html('');
			  if(null==data || data=='')
				  return;
			  $.each(data, function(key, value) { 					
	  				$("#image_gallery").append("<div style=\"display:block; float:left; margin:10px;\"><h3><a href='"+imageLibrary+value['image_library_name']+"' target='_blank' style='clear:both; margin-left: auto; margin-right: auto'>"+imageLibrary+value['image_library_name']+"</a></h3><img src='"+imageLibrary+value['image_library_name']+"' style='height:160px; background:#CBE3F5; padding:5px; display: block;   margin-left: auto;   margin-right: auto; ' /></div>");
			  });		 
	     });
	}
	
	 function changeVal() 
	 {
		 var type 	= $('#answer_type').val();
		 var kw 	= $('#answer_keyword').val();
		 $('#output').attr('request', "edit_answer.php?action=upload&type=" + type + "&keyword=" +  kw);
	 }
 
</script>
<div id="box">
	<table style="margin:auto;" width="95%" border="0" cellspacing="0" cellpadding="5">
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
			
<div><span class="txtHdr" style="float:left;"><?php echo $headerMsg;?></span></div>
<br>
<br>
<?php
if($answer_id != '')
{
	if($answerRow = $A->get_answer_info($answer_id)) : ?>

		   <form id="form" method="post" action="edit_answer.php">
			<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
							<tr>
								<td width="10%" valign="top">Subject:</td>
								<td>
							 		<input type="text" size="162" name="answer_subject" tabindex="1" value="<?php echo $answerRow['answer_subject']; ?>" /> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Keyword:</td>
								<td>
							 		<input type="text" size="162" id="answer_keyword"  name="answer_keyword" tabindex="2" value="<?php echo $answerRow['answer_keyword']; ?>" onchange="changeVal();"/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Type:</td>
								<td>
							 		<select name=answer_type id=answer_type  onchange="changeVal();">
							 		<option value="Book"<?php echo $answerRow['answer_type'] == 'Book' ? ' selected' : '';?>>Book</option> 
							 		<option value="Blog"<?php echo $answerRow['answer_type'] == 'Blog' ? ' selected' : '';?>>Blog</option> 
							 		<option value="Article"<?php echo $answerRow['answer_type'] == 'Article' ? ' selected' : '';?>>Article</option> 
							 		<option value="Comparison"<?php echo $answerRow['answer_type'] == 'Comparison' ? ' selected' : '';?>>Comparison</option>
							 		<option value="School"<?php echo $answerRow['answer_type'] == 'School' ? ' selected' : '';?>>School</option>
							 		<option value="Other"<?php echo $answerRow['answer_type'] == 'Other' ? ' selected' : '';?>>Other</option>
							 		</select>
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Author Name:</td>
								<td>
							 		<input type="text" size="162" name="answer_user_name" tabindex="3" value="<?php echo $answerRow['answer_user_name']; ?>"/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Photo:</td>
								<td>
							 		<input type="text" size="162" name="answer_user_photo" tabindex="4" value="<?php echo $A->replaceImageLibraryURL ($answerRow['answer_user_photo'],'display'); ?>"/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Link:</td>
								<td>
							 		<input type="text" size="162" name="answer_link" tabindex="5" value="<?php echo $answerRow['answer_link']; ?>"/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Short Answer:</td>
								<td>
									<textarea name="answer_short_answer" tabindex="6" style="width:1000px;height:100px;"><?php echo $answerRow['answer_short_answer']; ?></textarea>
								</td>
							</tr>
							<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
							<script type="text/javascript">
							tinyMCE.init({
								// General options
								mode : "exact",
								elements : "answer_content",
								theme : "advanced",
								plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
								editor_selector : "mceAnswer",
									
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
								<td width="30" valign="top">Content:</td>
								<td>
									<textarea name="answer_content" class="mceAnswer"  tabindex="7" style="width:1000px;height:400px;">
									<?php echo $A->replaceImageLibraryURL ($answerRow['answer_content'],'display'); ?>
									</textarea>
								</td>
							</tr>
						 	<tr>
						 		<td colspan="2" align="center">
						  			<input type="hidden" name="action" id="action" value="edit" />
									<input type="hidden" name="pid" value="<?php echo $answerRow['answer_id']; ?>" />
									<input type="hidden" name="question_id" value="<?php echo $answerRow['question_id']; ?>" />
						  			<input id="button1" type="submit" value="Save" onclick="document.forms['form'].elements['action'].value = 'edit'; submit();"/> 
						  		</td>
							</tr>
				</table>
				</form>
<?php 
	endif;
} 
elseif (!empty($question_id))
{ 
?>
	    <form id="form" method="post" action="edit_answer.php">
		<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
							<tr>
								<td width="10%" valign="top">Subject:</td>
								<td>
							 		<input type="text" size="162" name="answer_subject" tabindex="1" value="" /> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Keyword:</td>
								<td>
							 		<input type="text" size="162" name="answer_keyword" id="answer_keyword" tabindex="2" value="" onchange="changeVal();"/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Type:</td>
								<td>
							 		<select name=answer_type id=answer_type  onchange="changeVal();">
							 		<option value="Book">Book</option> 
							 		<option value="Blog">Blog</option> 
							 		<option value="Article">Article</option> 
							 		<option value="Comparison">Comparison</option>
							 		<option value="School">School</option>
							 		<option value="Other">Other</option>
							 		</select>
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Author Name:</td>
								<td>
							 		<input type="text" size="162" name="answer_user_name" tabindex="3" value=""/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Photo:</td>
								<td>
							 		<input type="text" size="162" name="answer_user_photo" tabindex="4" value=""/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Link:</td>
								<td>
							 		<input type="text" size="162" name="answer_link" tabindex="5" value=""/> 
								</td>
							</tr>
							<tr>
								<td width="30" valign="top">Short Answer:</td>
								<td>
									<textarea name="answer_short_answer" tabindex="6" style="width:1000px;height:100px;"></textarea>
								</td>
							</tr>
							<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
							<script type="text/javascript">
							tinyMCE.init({
								// General options
								mode : "exact",
								elements : "answer_content",
								theme : "advanced",
								plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
								editor_selector : "mceNewAnswer",
									
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
								<td width="30" valign="top">answer:</td>
								<td>
									<textarea name="answer_content" class="mceNewAnswer"  tabindex="7" style="width:1000px;height:400px;"></textarea>
							 	</td>
							</tr>
						 	<tr>
						 		<td colspan="2" align="center">
					  				<input type="hidden" name="action" value="new" />
									<input type="hidden" name="qid" value="<?php echo $question_id; ?>" />
									<input type="hidden" name="question_id" value="<?php echo $question_id; ?>" />
					  				<input id="button1" type="submit" value="Add" /> 
						  		</td>
							</tr>
		</table>
		</form>
<?php } ?>
			</td>
			<td rowspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td >&nbsp;</td>
			<td valign="top" id="main" id="boxGray" style="background-color:#eeeeee;">
			<div><span class="txtHdr" style="float:left;">Images</span></div>
			<br>
			<br>
			</td>
			<td rowspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td >&nbsp;</td>
			<td valign="top" id="main" id="boxGray" style="background-color:#eeeeee;">
				<div id="output" class="dropzone" type="image_files" accept="gif|jpg|png|bmp" request="edit_answer.php?action=upload&type=<?php echo (!empty($answerRow['answer_type'])?$answerRow['answer_type']:'Book');?>&keyword=<?php echo (!empty($answerRow['answer_keyword'])?$answerRow['answer_keyword']:'');?>"   afterUpload="image_gallery">
					<ul id="output-listing"></ul>
				</div>		
			</td>
			<td rowspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td >&nbsp;</td>
			<td valign="center" id="image_gallery" style="background-color:#eeeeee;" ondblclick="imagelibrary();">No images for <?php echo (!empty($answerRow['answer_type'])?$answerRow['answer_type']:'Book');?> <?php echo (!empty($answerRow['answer_keyword'])?$answerRow['answer_keyword']:'');?>
			</td>
			<td rowspan="5">&nbsp;</td>
		</tr>
	</table>

</div>
<?php
require_once('footer.php');
?>