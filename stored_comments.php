<?php
/**
 * Author: Archie Huang on 09/07/2009
**/
require_once('config.php');
require_once('header.php');
$comment = Comment::getInstance($db);

if(isset($_REQUEST['action'])) 
{
	switch ($_REQUEST['action']) 
	{
		case 'approve_comments':
			$commentArray = @$_REQUEST['comments'];
		
			if(is_array($commentArray) && count($commentArray) > 0)
			{
				foreach($commentArray as $comment_id)
				{
					$comment->change_comment_status($comment_id,1);
				}
			}
			break;
	
		case 'delete_comments':
			$commentArray = @$_REQUEST['comments'];
			if(is_array($commentArray) && count($commentArray) > 0)
			{
				foreach($commentArray as $comment_id)
				{
					$comment->del_comment($comment_id);
				}
			}
			break;
	}
}
?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			
			<!-- *** START MAIN CONTENTS  *** -->

<table align="center" border="0" cellspacing="0" cellpadding="0" width="100%" class="tablePop">
	<tr>
		<td align="left" valign="top"><span class="txtHdr">Stored Comments</span></td>
		<td align="right" valign="top"></td>
	</tr>

	<tr>
		<td colspan="2" valign="top">

<form action="/stored_comments.php" method="POST" name="data_table" id="data_table"  enctype="application/x-www-form-urlencoded">
<div id="tableData">
<table width="100%" border="0" cellspacing="1" cellpadding="0" id="dt1" class="dataTable">
	<tr>
	<td align="center" class="cellHdr"></td>
	<td align="center" class="cellHdr"><a>Edit</a></td>
	<td align="center" class="cellHdr"><a>Title</a></td>
	<td align="center" class="cellHdr"><a>Content</a></td>
	<td align="center" class="cellHdr"><a>Author</a></td>
	<td align="center" class="cellHdr"><a>Domain</a></td>
	<td align="center" class="cellHdr"><a>Article</a></td>
	<td align="center" class="cellHdr"><a>Date</a></td>
	</tr>
    
                <?php
$totalNum = 0;
$pResults = $comment->list_comments(-1);
foreach ($pResults as $pRow) 
{
	$domainUrl = $db->select_one("SELECT domain_url FROM domains WHERE domain_id='".$pRow['comment_domain_id']."'");
	$articleTitle = $db->select_one("SELECT article_title FROM articles WHERE article_id='".$pRow['comment_article_id']."'");
	?>
    
    <tr class="alter1">
    	<td  width="30" align="center" valign="middle" ><input type="checkbox" name="comments[]" value="<?php echo $pRow['comment_id'];?>"></td>
        <td align="center" valign="middle" ><a href="edit_comment.php?cid=<?php echo $pRow['comment_id'];?>" target="_blank"><img src="images/edit.jpg" title="Edit Article" width="16" height="16" /></a></td>
        <td align="left" valign="middle" ><a><?php echo $pRow['comment_title'];?></a></td>
        <td align="left" valign="middle" ><?php echo $pRow['comment_content'];?></td>
        <td align="left" valign="middle" ><a><?php echo $pRow['comment_author'];?></a></td>
        <td align="center" valign="middle" ><a><?php echo $domainUrl;?></a></td>
        <td align="center" valign="middle" ><a><?php echo $articleTitle;?></a></td>
         <td align="center" valign="middle" ><a><?php echo $pRow['comment_date'];?></a></td>
		
   </tr>
<?php
}
?>

	</table>
</div>

<br>
<table border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td align="left" onclick="document.forms['data_table'].elements['type'][0].checked = true;">
		<input type="radio" name="action" value="approve_comments"> Approve selected comments from system
		</td>
	</tr>

	<tr>
		<td align="left">
		<input type="radio" name="action" value="delete_comments"> Delete selected comments from system
		</td>
	</tr>
	<tr>
		<td align="center">
			<br>

			<input type="submit" value="Perform Selected Operation">
		</td>
	</tr>
</table>
</form>

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