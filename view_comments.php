<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once('config.php');
require_once('header.php');

$startDate 	= isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : date("Y-m-d",strtotime('-1 month'));
$endDate 	= isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : date("Y-m-d");
$table		= isset($_REQUEST['table']) ? $_REQUEST['table'] : '';
$comment = Comment::getInstance($db);

if(isset($_REQUEST['action'])) 
{
	switch ($_REQUEST['action']) 
	{
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
<h2>Approved Comments</h2>
<table align="center" border="0" cellspacing="0" cellpadding="0" width="100%" class="tablePop">
<form action="/view_comments.php" method="POST" name="data_table" id="data_table"  enctype="application/x-www-form-urlencoded">
	<tr>
		<td align="left" valign="top">
		    <span class="txtHdr">From: </span><input name="start_date" type="text" value="<?php echo $startDate;?>" size="8"/> <span class="txtHdr">to</span> <input name="end_date" type="text" value="<?php echo $endDate;?>" size="8"/> 
			<span class="txtHdr">Table: </span>
			<select name="table" id="table">
			<option value="" <?php if ($table == '') echo 'selected="selected"'?>>All</option>
			<option value="articles" <?php if ($table == 'articles') echo 'selected="selected"'?>>Articles</option>
			<option value="question_answers" <?php if ($table == 'question_answers') echo 'selected="selected"'?>>Question/Answers</option>
			<option value="goals" <?php if ($table == 'goals') echo 'selected="selected"'?>>Goals</option>
			</select>
		    <input type="submit" value="Search">
		</td>
		<td align="left" valign="top"></td>
		<td align="right" valign="top"></td>
	</tr>

	<tr>
		<td colspan="2" valign="top">


<div id="tableData">
<table width="100%" border="0" cellspacing="1" cellpadding="0" id="dt1" class="dataTable">
	<tr>
	<td align="center" class="cellHdr"></td>
	<td align="center" class="cellHdr"><a>Edit</a></td>
	<td align="center" class="cellHdr"><a>Title</a></td>
	<td align="center" class="cellHdr"><a>Content</a></td>
	<td align="center" class="cellHdr"><a>Author</a></td>
	<td align="center" class="cellHdr"><a>Domain</a></td>
	<td align="center" class="cellHdr"><a>Table</a></td>
	<td align="center" class="cellHdr"><a>Title</a></td>
	<td align="center" class="cellHdr"><a>Date</a></td>
	</tr>
<?php
$totalNum = 0;
$pResults = $comment->list_comments(1,$table,$startDate,$endDate);
foreach ($pResults as $pRow) :
?>
    <tr class="alter1">
    	<td width="30" align="center" valign="middle" ><input type="checkbox" name="comments[]" value="<?php echo $pRow['comment_id'];?>"></td>
        <td align="center" valign="middle" ><a href="edit_comment.php?cid=<?php echo $pRow['comment_id'];?>" target="_blank"><img src="images/edit.jpg" title="Edit Article" width="16" height="16" /></a></td>
        <td align="left" valign="middle" ><a><?php echo $pRow['comment_title'];?></a></td>
        <td align="left" valign="middle"  width="50%"><?php echo $pRow['comment_content'];?></td>
        <td align="left" valign="middle" ><a><?php echo $pRow['comment_author'];?></a></td>
        <td align="center" valign="middle" ><a><?php echo $pRow['comment_domain'];?></a></td>
        <td align="center" valign="middle" ><a><?php echo $pRow['comment_table'];?></a></td>
        <td align="center" valign="middle" ><a><?php echo $pRow['comment_title'];?></a></td>
        <td align="center" valign="middle" ><a><?php echo $pRow['comment_date'];?></a></td>
		
   </tr>
<?php
endforeach;
?>

	</table>
</div>

<br>
<table border="0" cellspacing="0" cellpadding="0" align="center">
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