<?php
/**
 * Edit Comment New
 * Author: George Huang on 29/01/2010
**/
require_once('config.php');
require_once('header.php');
$comment = Comment::getInstance($db);

$comment_id = isset($_REQUEST['cid']) ? $_REQUEST['cid'] : '';
$action 	= isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$startnum 	= 1;
$headerMsg 	= $comment_id != '' ? 'Edit Comment' : 'New Comment';
$Msg 		= '';
$table_id 	= !empty($_REQUEST['aid'])?$_REQUEST['aid']:'';		
$table		= !empty($_REQUEST['table'])?$_REQUEST['table']:(empty($table_id)?'':'articles');

if(!empty($action))
{
    $editarray = array();
	foreach($_REQUEST as $key => $val)
	{
		if($key != 'action' && $key != 'cid' && $key != 'PHPSESSID')
		{
			$key = ($key == 'comment')?'content':$key;
			$editarray[$key] = $val;
		}
	}

	if($action == 'edit')
	{
		if($comment->save_comment($editarray,$comment_id))
			$Msg = 'Changes Saved';
		
	}
	else if($action == 'new')
	{
		if($comment_id = $comment->save_comment($editarray,0,false))
			$Msg = 'Comment Added';
	}
}
?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>

			<td class="brdrL">&nbsp;</td>
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
if($comment_id != '') 
{
	if($commentRow = $comment->get_commentInfo($comment_id)) 
	{
	?>
	<div style="z-index:1;">
		 <form id="form" method="post" action="edit_comment.php">
			
		 <table width="99%" border="0" align="center"  cellpadding="3" cellspacing="1" class="table_style">
		 
			<tr><td width="30" valign="top">Title:</td>
				<td>
				 <input type="text" name="title" tabindex="1" value="<?php echo $commentRow['comment_title']; ?>" style="width:600px;"/> 
				</td>
			</tr>
			<tr><td width="30" valign="top">Author:</td>
				<td>
				 <input type="text" name="author" tabindex="3" value="<?php echo $commentRow['comment_author']; ?>"/> 
				</td>
			</tr>
			<tr><td width="30" valign="top">Table:</td>
				<td>
				 <input type="text" name="table" tabindex="3" value="<?php echo $commentRow['comment_table']; ?>"/> 
				</td>
			</tr>
			<tr><td width="30" valign="top">TableID:</td>
				<td>
				 <input type="text" name="table_id" tabindex="3" value="<?php echo $commentRow['comment_table_id']; ?>"/> 
				</td>
			</tr>
			<tr>
       		  <td class="left_title_2"> 
			</td>
			  <td> 
			  <script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
				<script type="text/javascript">
		tinyMCE.init({
			// General options
			mode : "exact",
			elements : "comment",
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
			media_external_list_url : "js/media_list.js"

		});
		</script>
			  <tr><td width="30" valign="top">Content:</td>
				<td>
				 <textarea name="comment" tabindex="4" style="width:600px;height:400px;"><?php echo $commentRow['comment_content']; ?></textarea>
				 </td>
			  </tr>
			  			
			<tr><td colspan="2" align="center">
			  <input type="hidden" name="action" value="edit" />
				<input type="hidden" name="cid" value="<?php echo $commentRow['comment_id']; ?>" />
			  <input id="button1" type="submit" value="Save" /> 
			  <input id="button2" type="reset" value="Reset"  />
			  </td>
			</tr>
		  </table>
		</form>
		</div>
<?php    
	}
} 
else 
{
?>

<div style="z-index:1;">
 <form id="form" method="post" action="edit_comment.php">

 <table width="99%" border="0" align="center"  cellpadding="3" cellspacing="1" class="table_style">

    <tr><td width="30" valign="top">Title:</td>
		<td>
		 <input type="text" name="title" tabindex="1" value="" style="width:600px;"/> 
		</td>
		</tr>
		<tr><td width="30" valign="top">Author:</td>
		<td>
		 <input type="text" name="author" tabindex="3" value=""/> 
		</td>
		</tr>
		<tr><td width="30" valign="top">Table:</td>
			<td>
			 <input type="text" name="table" tabindex="3" value="<?php echo $table;?>"/> 
			</td>
		</tr>
		<tr><td width="30" valign="top">TableID:</td>
			<td>
			 <input type="text" name="table_id" tabindex="3" value="<?php echo $table_id; ?>"/> 
			</td>
		</tr>
		
	<tr><td width="30" valign="top">Content:</td>
      <td> 
      <script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
		<script type="text/javascript">
tinyMCE.init({
	// General options
	mode : "exact",
	elements : "comment",
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
	media_external_list_url : "js/media_list.js"

});
</script>
      <textarea name="comment" tabindex="4" style="width:600px;height:400px;"></textarea>
	  
      </td>
    </tr>
	
    <tr bgcolor="#FFFFFF">

      <td colspan="2">
	 <tr><td colspan="2" align="center">
	  <input type="hidden" name="action" value="new" />
	  <input type="hidden" name="domain_id" value="<?php echo (!empty($_REQUEST['did'])?$_REQUEST['did']:''); ?>" />
	  <input id="button1" type="submit" value="Add" /> 
	  <input id="button2" type="reset" value="Reset"  />
	  </td>
	 </tr></td>
    </tr>
  </table>
</form>
</div>
<?php 
} 
?>
			</td>
			<td rowspan="5" class="brdrR">&nbsp;</td>
		</tr>
	</table>

</div>
<?php
require_once('footer.php');
?>