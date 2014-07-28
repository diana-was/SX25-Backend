<?php

require_once('config.php');
require_once('header.php');

$Goal = Goal::getInstance($db);

$qaList = array();
$gid = isset($_REQUEST['gid'])?$_REQUEST['gid']:0;

$startId = isset($_REQUEST['start'])?$_REQUEST['start']:0;
$endId = isset($_REQUEST['end'])?$_REQUEST['end']:0;
$sortBy = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'id';
$sortOrder = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'desc';
$recordPerPage = isset($_REQUEST['rpp']) ? $_REQUEST['rpp'] : 20;
$currentPage = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$keyword = isset($_REQUEST['keyword'])?trim($_REQUEST['keyword']):'';
$approved = isset($_REQUEST['approved']) && ($_REQUEST['approved']==1)?1:0;
$scrap_keyword = isset($_REQUEST['scrap_keyword'])?trim($_REQUEST['scrap_keyword']):'';

$fromRecord = ($currentPage-1) * $recordPerPage;

$sortyQuery = !empty($sortBy)?" ORDER BY goal_"."$sortBy $sortOrder":'';

if(!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'delete' : $Goal->del_goal($gid);
						break;
		case 'setapproved' : 
						$Goal->save_goal(array('goal_approved' => $approved),$gid);
						break;
	}
}

if (!empty($keyword))
	$goalList = $Goal->get_goal($keyword, $recordPerPage, $fromRecord, $sortyQuery);
else
	$goalList = $Goal->get_library_goal($fromRecord,$recordPerPage,$sortyQuery);

$fullUrl = "manage_goal.php?keyword=$keyword&start=$startId&end=$endId&sort=$sortBy&order=$sortOrder&rpp=$recordPerPage&page=$currentPage";


$totalRecords = $Goal->count_total_goal($keyword);
$totalPage = ceil($totalRecords / $recordPerPage);

$newOrder = ($sortOrder == 'asc') ? 'desc' : 'asc';
?>
<script>
var url = '<?php echo $fullUrl; ?>';

$(function(){
	$('#dialog').hide();
	
});

function delete_goal(url)
{
	$cf = confirm('Are you sure you are going to remove this goal record?');
	if($cf){
	    window.location = url;
		return false; 
	}
	else{
	    return false;	
	}
}

function update_goal(url)
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
	<div style="text-align:center;">
		<form action="manage_goal.php" method="post" name="goal" id="goal" style="float:right; margin-right: 80px;">
			<input type="text" onclick="this.value=''" name="keyword" style="width: 250px; color: rgb(102, 102, 102);" value="Please insert a keyword here">
			<button type="submit">Search Goals</button>
		</form>
		<span class="txtHdr">Manage Goals</span>
	</div>
	<table id="goal_table" width="95%">
		<thead>
			<tr>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=id&order=<?php echo $newOrder;?>">ID<?php if($sortBy == 'id') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th><a href="<?php echo $fullUrl;?>&sort=subject&order=<?php echo $newOrder;?>">Subject<?php if($sortBy == 'subject') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th>Description</th>
                <th width="150px"><a href="<?php echo $fullUrl;?>&sort=keyword&order=<?php echo $newOrder;?>">Keyword<?php if($sortBy == 'keyword') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="200px"><a href="<?php echo $fullUrl;?>&sort=start_status&order=<?php echo $newOrder;?>">Start Status<?php if($sortBy == 'start_status') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="200px"><a href="<?php echo $fullUrl;?>&sort=target_status&order=<?php echo $newOrder;?>">Target Status<?php if($sortBy == 'target_status') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
            	<th width="80px" valign="middle"><a href="edit_goal.php?gid=" target="_blank" style="float: right;"><img src="images/new.jpg" title="New goal" width="16" height="16" /></a><p>Action</p></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($goalList as $pRow) 
{
	$kw = (trim($pRow['goal_keyword'])!='')?$pRow['goal_keyword']:$pRow['domain_keyword'];
	echo '<tr>';
   	echo '<td class="a-center">'.$pRow['goal_id'].'</td>';
	echo '<td>'.$pRow['goal_subject'].'</td>';
	echo '<td>'.substr($pRow['goal_content'], 0, 200).' ... </td>';
	echo '<td>'.$kw.'</td>';
	echo '<td>'.$pRow['goal_start_status'].'</td>';
	echo '<td>'.$pRow['goal_target_status'].'</td>';
	echo '<td><a href="edit_goal.php?gid='.$pRow['goal_id'].'" target="_blank"><img src="images/edit.jpg" title="Edit goal" width="16" height="16" /></a>';
	echo '<a href="#" onclick="javascript:delete_goal(\''.$fullUrl.'&gid='.$pRow['goal_id'].'&action=delete\'); return false;"><img src="images/del.gif" title="Delete goal" width="16" height="16" /></a>';
	if ($pRow['goal_approved']==0)
		echo '<a href="#" onclick="javascript:update_goal(\''.$fullUrl.'&approved=1&gid='.$pRow['goal_id'].'&action=setapproved\'); return false;"><img src="images/a-.png" title="Set Approved" width="16" height="16" /></a>';
	else
		echo '<a href="#" onclick="javascript:update_goal(\''.$fullUrl.'&approved=0&gid='.$pRow['goal_id'].'&action=setapproved\'); return false;"><img src="images/a.png" title="Set Not Approved" width="16" height="16" /></a>';
	if ($pRow['num_comments'] > 0)
		echo '<a href="manage_comments.php?gid='.$pRow['goal_id'].'" target="_blank"><img src="images/c.png" title="See '.$pRow['num_comments'].' comments" width="20" height="20"/></a>';
	else
		echo '<a href="manage_comments.php?gid='.$pRow['goal_id'].'" target="_blank"><img src="images/c-.png" title="Create comments" width="20" height="20"/></a>';
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

<div id="dialog_css"  title="Change Keyword" style="display:none">
	<br />
	<br />                      
	<div class="etf"><span>Keyword: <span style="color: rgb(248, 25, 2); float: right;">* </span></span>
	<input type="text" style="width: 440px;" id="keyword" name="keyword"></div>
	<br />
	<br />	
	<br /> 
</div>

<?php 
require_once('footer.php');
?>

