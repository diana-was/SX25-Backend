<?php

require_once('config.php');
require_once('header.php');

$Event = Event::getInstance($db);

$eventList = array();
$pid = isset($_REQUEST['pid'])?$_REQUEST['pid']:0;

$startId = isset($_REQUEST['start'])?$_REQUEST['start']:0;
$endId = isset($_REQUEST['end'])?$_REQUEST['end']:0;
$sortBy = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'id';
$sortOrder = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'desc';
$recordPerPage = isset($_REQUEST['rpp']) ? $_REQUEST['rpp'] : 20;
$currentPage = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$keyword = isset($_REQUEST['keyword'])?trim($_REQUEST['keyword']):'';

$fromRecord = ($currentPage-1) * $recordPerPage;

if($sortBy != '')
{
	$sortyQuery = " ORDER BY event_".$sortBy." ".$sortOrder;
}

if(!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'delete' : $Event->del_event($pid);
						break;
	}
}

if (!empty($keyword))
	$eventList = $Event->get_event($keyword, $recordPerPage, $fromRecord, $sortyQuery);
else
	$eventList = $Event->get_library_event($fromRecord,$recordPerPage,$sortyQuery);

$fullUrl = "manage_event.php?keyword=$keyword&start=$startId&end=$endId&sort=$sortBy&order=$sortOrder&rpp=$recordPerPage&page=$currentPage";


$totalRecords = $Event->count_events($keyword);
$totalPage = ceil($totalRecords / $recordPerPage);

$newOrder = ($sortOrder == 'asc') ? 'desc' : 'asc';
?>

<script>
var url = '<?php echo $fullUrl; ?>';

$(function(){
	$('#dialog').hide();
	
});

function delete_event(url)
{
	$cf = confirm('Are you sure you are going to remove this event record?');
	if($cf){
	    window.location = url;
		return false; 
	}
	else{
	    return false;	
	}
}

function update_event(url)
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
		<form action="manage_event.php" method="post" name="event" id="event" style="float:right; margin-right: 80px;">
			<input type="text" onclick="this.value=''" name="keyword" style="width: 250px; color: rgb(102, 102, 102);" value="Please insert a keyword here">
			<button type="submit">Search Event</button>
		</form>
		<h3>Events</h3>
	</div>
	<table id="event_table" width="95%">
		<thead>
			<tr>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=id&order=<?php echo $newOrder;?>">ID<?php if($sortBy == 'id') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th><a href="<?php echo $fullUrl;?>&sort=title&order=<?php echo $newOrder;?>">Title<?php if($sortBy == 'title') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th>Description</th>
                <th width="150px"><a href="<?php echo $fullUrl;?>&sort=keyword&order=<?php echo $newOrder;?>">Keyword<?php if($sortBy == 'keyword') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=start_time&order=<?php echo $newOrder;?>">Start<?php if($sortBy == 'start_time') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=country_name&order=<?php echo $newOrder;?>">Country<?php if($sortBy == 'country_name') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=region_name&order=<?php echo $newOrder;?>">Region<?php if($sortBy == 'region_name') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=city_name&order=<?php echo $newOrder;?>">City<?php if($sortBy == 'city_name') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
            	<th width="60px" valign="middle"><a href="edit_event.php?pid=" target="_blank" style="float: right;"><img src="images/new.jpg" title="New event" width="16" height="16" /></a><p>Action</p></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($eventList as $pRow) 
{
	$kw = (trim($pRow['event_keyword'])!='')?$pRow['event_keyword']:$pRow['domain_keyword'];
	echo '<tr>';
   	echo '<td class="a-center">'.$pRow['event_id'].'</td>';
	echo '<td>'.$pRow['event_title'].'</td>';
	echo '<td>'.substr($pRow['event_description'], 0, 200).' ... </td>';
	echo '<td>'.$kw.'</td>';
	echo '<td>'.date('Y-m-d',strtotime($pRow['event_start_time'])).'</td>';
	echo '<td>'.$pRow['event_country_name'].'</td>';
	echo '<td>'.$pRow['event_region_name'].'</td>';
	echo '<td>'.$pRow['event_city_name'].'</td>';
	echo '<td><a href="edit_event.php?pid='.$pRow['event_id'].'" target="_blank"><img src="images/edit.jpg" title="Edit event" width="16" height="16" /></a>';
	echo '<a href="#" onclick="javascript:delete_event(\''.$fullUrl.'&pid='.$pRow['event_id'].'&action=delete\'); return false;"><img src="images/del.gif" title="Delete event" width="16" height="16" /></a>';
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

