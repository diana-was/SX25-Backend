<?php

require_once('config.php');
require_once('header.php');

$Directory = Dty::getInstance($db);
$Site = Site::getInstance($db); 

$directoryList = array();
$pid = isset($_REQUEST['pid'])?$_REQUEST['pid']:0;

$startId = isset($_REQUEST['start'])?$_REQUEST['start']:0;
$endId = isset($_REQUEST['end'])?$_REQUEST['end']:0;
$sortBy = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'id';
$sortOrder = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'desc';
$recordPerPage = isset($_REQUEST['rpp']) ? $_REQUEST['rpp'] : 20;
$currentPage = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$server = isset($_REQUEST['server'])? $_REQUEST['server'] : 'sx25';
$keyword = isset($_REQUEST['keyword'])?trim($_REQUEST['keyword']):'';

$fromRecord = ($currentPage-1) * $recordPerPage;

if($sortBy != '')
{
	$sortyQuery = " ORDER BY directory_".$sortBy." ".$sortOrder;
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete')
{
	$Directory->del_directory($pid);
}

if (!empty($keyword))
	$directoryList = $Directory->get_directories($keyword, $recordPerPage, $fromRecord);
else
	$directoryList = $Directory->get_library_directories($fromRecord,$recordPerPage,$sortyQuery);

$fullUrl = "manage_directories.php?keyword=$keyword&start=$startId&end=$endId&sort=$sortBy&order=$sortOrder&rpp=$recordPerPage&page=$currentPage";


$totalRecords = $Directory->count_total_directories($keyword);
$totalPage = ceil($totalRecords / $recordPerPage);

$newOrder = ($sortOrder == 'asc') ? 'desc' : 'asc';
?>

<script>
var url = '<?php echo $fullUrl; ?>';

$(function(){
	$('#dialog').hide();
	
});

function delete_directory(url){
	$cf = confirm('Are you sure you are going to remove this directory?');
	if($cf){
	    window.location = url;
		return false; 
	}
	else{
	    return false;	
	}
}

</script>
<style>
#box{
width:100%;
text-align:center;
}

#directory_table
{
border-collapse:collapse;
background:#FFFFFF;
margin: auto;
text-align:left
}
#directory_table table, #directory_table td, #directory_table th
{
border:1px solid black;
}
#directory_table img{margin:10px;  border: 0 none;}
#pager img{margin:1px;  border: 0 none;}
#directory_table a, #pager a{text-decoration: none;}
.a-center { text-align: center;}
</style>
<div id="box">
	<div style="text-align:center;">
		<form action="manage_directories.php" method="post" name="directory" id="directory" style="float:right; margin-right: 80px;">
			<input type="text" onclick="this.value=''" name="keyword" style="width: 250px; color: rgb(102, 102, 102);" value="Please insert a keyword here">
			<button type="submit">Search Directories</button>
		</form>
		<h3>Directories</h3>
	</div>
	<table id="directory_table" width="95%">
		<thead>
			<tr>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=id&order=<?php echo $newOrder;?>">ID<?php if($sortBy == 'id') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th><a href="<?php echo $fullUrl;?>&sort=title&order=<?php echo $newOrder;?>">Title<?php if($sortBy == 'title') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th>Description</th>
                <th width="150px"><a href="<?php echo $fullUrl;?>&sort=keyword&order=<?php echo $newOrder;?>">Keyword<?php if($sortBy == 'keyword') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="250px"><a href="<?php echo $fullUrl;?>&sort=url&order=<?php echo $newOrder;?>">URL<?php if($sortBy == 'url') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
            	<th width="80px"><a>Action</a></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($directoryList as $pRow) 
{
	$kw = (trim($pRow['directory_keyword'])!='')?$pRow['directory_keyword']:$pRow['domain_keyword'];
	echo '<tr>';
   	echo '<td class="a-center">'.$pRow['directory_id'].'</td>';
	echo '<td><a href="view_directory.php?pid='.$pRow['directory_id'].'" target="_blank">'.$pRow['directory_title'].'</a></td>';
	echo '<td>'.$pRow['directory_description'].'</td>';
	echo '<td>'.$kw.'</td>';
	echo '<td><a href="'.$pRow['directory_url'].'" target="_blank">'.$pRow['directory_url'].'</a></td>';
	echo '<td><a href="edit_directory.php?pid='.$pRow['directory_id'].'" target="_blank"><img src="images/edit.jpg" title="Edit Directory" width="16" height="16" /></a>';
	echo '<a href="#" onclick="javascript:delete_directory(\''.$fullUrl.'&pid='.$pRow['directory_id'].'&action=delete\'); return false;"><img src="images/del.gif" title="Delete Directory" width="16" height="16" /></a>';
	echo '<a href="edit_directory.php?pid=" target="_blank"><img src="images/new.jpg" title="New Directory" width="16" height="16" /></a>';
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
				<a href="<?php echo $fullUrl; ?>&page=<?php echo ($currentPage-1); ?>&server=<?php echo $server; ?>">	
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

