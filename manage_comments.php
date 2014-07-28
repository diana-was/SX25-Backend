<?php
require_once('config.php');
require_once('header.php');

$Cmt = Comment::getInstance($db);
$Goal = Goal::getInstance($db);

$commentList = array();
$gID = isset($_REQUEST['gid'])?$_REQUEST['gid']:0;
$cid = isset($_REQUEST['cid'])?$_REQUEST['cid']:0;
if (empty($gID))
	header('Location: view_comments.php');

$startId = isset($_REQUEST['start'])?$_REQUEST['start']:0;
$endId = isset($_REQUEST['end'])?$_REQUEST['end']:0;
$sortBy = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'id';
$sortOrder = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'asc';
$recordPerPage = isset($_REQUEST['rpp']) ? $_REQUEST['rpp'] : 20;
$currentPage = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$order = !empty($_REQUEST['comment_order'])?$_REQUEST['comment_order']:1;

$fromRecord = ($currentPage-1) * $recordPerPage;

if($sortBy != '')
{
	$sortyQuery = " ORDER BY comment_".$sortBy." ".$sortOrder;
}
if (!empty($gID))
{
	$goalInfo 	= $Goal->get_goal_info($gID);
}

if(!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'delete' : $Cmt->del_comment($cid);
						break;
		case 'setorder' : 
						$Cmt->save_comment(array('comment_order' => $order),$cid);
						break;
		case 'approve':
						$status = (empty($_REQUEST['status']) || !is_numeric($_REQUEST['status']))?0:$_REQUEST['status'];
						$Cmt->change_comment_status($cid,$status);
						break;
	
	}
}

$commentList = $Cmt->get_comments('goals',$gID,'','',$recordPerPage, $fromRecord, $sortyQuery);
$fullUrl = "manage_comments.php?gid=$gID&start=$startId&end=$endId&sort=$sortBy&order=$sortOrder&rpp=$recordPerPage&page=$currentPage";

$totalRecords = $Cmt->count_comments('goals',$gID,'','');
$totalPage = ceil($totalRecords / $recordPerPage);

$newOrder = ($sortOrder == 'asc') ? 'desc' : 'asc';
?>

<script>
var url = '<?php echo $fullUrl; ?>';

$(function(){
	$('#dialog').hide();
	
});

function delete_comment(url)
{
	$cf = confirm('Are you sure you are going to remove this comment record?');
	if($cf){
	    window.location = url;
		return false; 
	}
	else{
	    return false;	
	}
}

function aprove_comment(url)
{
    window.location = url;
	return false; 
}

</script>
<style>
#box{
width:100%;
text-align:center;
}

#comment_table
{
border-collapse:collapse;
background:#FFFFFF;
margin: auto;
text-align:left
}
#comment_table table, #comment_table td, #comment_table th
{
border:1px solid black;
}
#comment_table img{margin:10px;  border: 0 none;}
#pager img{margin:1px;  border: 0 none;}
#comment_table a, #pager a{text-decoration: none;}
.a-center { text-align: center;}
</style>

<?php if(!empty($Msg)): ?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
	<tr>
		<td valign="top"><div class="blueHdr">System Message</div>
		<div class="content" align="center">
        <font color="Red"><?php echo $Msg;?></font>

		</div>
		</td>
	</tr>
</table>
<?php endif; ?>		

<div id="box">
<br>
<br>
<div><span class="txtHdr">Manage Goals' Comments</span></div>
    <?php if (!empty($goalInfo)) : ?>
    <br/>
    <br/>
	<table id="comment_table" width="95%">
		<thead>
			<tr>
                <th width="60px">Goal ID</th>
                <th>Keyword</th>
                <th>Goal Subject</th>
                <th>Goal Content</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="a-center"><?php echo $goalInfo['goal_id']?></td>
				<td><?php echo $goalInfo['goal_keyword']?></td>
				<td><?php echo $goalInfo['goal_subject']?></td>
				<td><?php echo $goalInfo['goal_content']?></td>
			</tr>
		</tbody>
	</table>
	<?php endif; ?>
	<br/>
	<br/>
	<table id="comment_table" width="95%">
		<thead>
			<tr>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=id&order=<?php echo $newOrder;?>">ID<?php if($sortBy == 'id') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="100px"><a href="<?php echo $fullUrl;?>&sort=author&order=<?php echo $newOrder;?>">Author<?php if($sortBy == 'author') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th><a href="<?php echo $fullUrl;?>&sort=title&order=<?php echo $newOrder;?>">Keyword<?php if($sortBy == 'title') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="60%">Content</th>
            	<th width="120px" valign="middle"><a href="edit_comment.php?cid=&table=goals&aid=<?php echo $goalInfo['goal_id'];?>" target="_blank" style="float: right;"><img src="images/new.jpg" title="New comment" width="16" height="16" /></a><p>Actions</p></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($commentList as $pRow) 
{
	echo '<tr>';
   	echo '<td class="a-center">'.$pRow['comment_id'].'</td>';
	echo '<td class="a-center">'.$pRow['comment_author'].'</td>';
	echo '<td>'.$pRow['comment_title'].'</td>';
	echo '<td>'.substr($pRow['comment_content'], 0, 400).'</td>'; 
	echo '<td><a href="edit_comment.php?cid='.$pRow['comment_id'].'&table=goals&aid='.$goalInfo['goal_id'].'" target="_blank" style="float:left;"><img src="images/edit.jpg" title="Edit comment" width="16" height="16" /></a>';
	echo '<a href="#" style="float:left;" onclick="javascript:delete_comment(\''.$fullUrl.'&cid='.$pRow['comment_id'].'&action=delete\'); return false;"><img src="images/del.gif" title="Delete comment" width="16" height="16" /></a>';
	if ($pRow['comment_status']==0)
		echo '<a href="#" onclick="javascript:aprove_comment(\''.$fullUrl.'&status=1&cid='.$pRow['comment_id'].'&action=approve\'); return false;"><img src="images/a-.png" title="Set Approved" width="16" height="16" /></a>';
	else
		echo '<a href="#" onclick="javascript:aprove_comment(\''.$fullUrl.'&status=0&cid='.$pRow['comment_id'].'&action=approve\'); return false;"><img src="images/a.png" title="Set Not Approved" width="16" height="16" /></a>';
	echo '</td>';
	echo '</tr>';
}
?>
		</tbody>
	</table>

	<div id="pager">
		<form method="post" name="form1" id="form1">
			Page <?php echo "$currentPage "; ?> of <?php echo $totalPage; ?> pages | Go to page :
			<?php
			if($currentPage > 1) : ?>
				<a href="<?php echo $fullUrl; ?>&page=<?php echo ($currentPage-1); ?>">	
				<img src="images/arrow_left.gif" width="16" height="16" />
				</a>
			<?php endif;
			
			$leftBound = ($currentPage - 5) < 1 ? 1 : ($currentPage - 5);
			$rightBound = ($currentPage + 5) > $totalPage ? $totalPage : ($currentPage + 5);
			
			for($i = $leftBound; $i <= $rightBound; $i++)
			{
				if($i != $currentPage)
					echo '<a href="'.$fullUrl.'&page='.$i.'">';
				echo $i;
				if($i != $currentPage)
					echo '</a>';
				if ($i < $rightBound)
					echo ' . ';
				else 
					echo ' ';
			}
			if($currentPage < $totalPage) : ?>
				<a href="<?php echo $fullUrl; ?>&page=<?php echo ($currentPage+1); ?>">	
				<img src="images/arrow_right.gif" width="16" height="16" />
				</a>
			<?php endif; ?>	
			 | View  
			<select name="perpage" id="perpage" onchange="parent.document.location='<?php echo $fullUrl; ?>&rpp=' + this.value;">
				<option value="10" <?php if($recordPerPage == 10)	echo ' selected'; ?> >10</option>
				<option value="20" <?php if($recordPerPage == 20)	echo ' selected'; ?> >20</option>
				<option value="50" <?php if($recordPerPage == 50)	echo ' selected'; ?> >50</option>
				<option value="100" <?php if($recordPerPage == 100)	echo ' selected'; ?> >100</option>
			</select>
			per page | Total <strong><?php echo $totalRecords; ?></strong> records found
		</form>
	</div> 
</div>


<?php 
require_once('footer.php');
?>
