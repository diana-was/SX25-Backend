<?php

require_once ('config.php');
require_once ('header.php');

$Cheapad = cheapAds::getInstance ( $db );

$cheapadList = array ();
$pid = isset ( $_REQUEST ['pid'] ) ? $_REQUEST ['pid'] : 0;

$startId = isset ( $_REQUEST ['start'] ) ? $_REQUEST ['start'] : 0;
$endId = isset ( $_REQUEST ['end'] ) ? $_REQUEST ['end'] : 0;
$sortBy = isset ( $_REQUEST ['sort'] ) ? $_REQUEST ['sort'] : 'id';
$sortOrder = isset ( $_REQUEST ['order'] ) ? $_REQUEST ['order'] : 'desc';
$recordPerPage = isset ( $_REQUEST ['rpp'] ) ? $_REQUEST ['rpp'] : 20;
$currentPage = isset ( $_REQUEST ['page'] ) ? $_REQUEST ['page'] : 1;
$title = isset ( $_REQUEST ['title'] ) ? trim ( $_REQUEST ['title'] ) : '';

$fromRecord = ($currentPage - 1) * $recordPerPage;

if ($sortBy != '') {
	$sortyQuery = " ORDER BY cheapad_" . $sortBy . " " . $sortOrder;
}

if (isset ( $_REQUEST ['action'] ) && $_REQUEST ['action'] == 'delete') {
	$Cheapad->del_cheapad ( $pid );
}

if (! empty ( $title ))
	$cheapadList = $Cheapad->get_cheapads ( $title, $recordPerPage, $fromRecord );
else
	$cheapadList = $Cheapad->get_library_cheapads ( $fromRecord, $recordPerPage, $sortyQuery );

$fullUrl = "manage_cheapads.php?title=$title&start=$startId&end=$endId&sort=$sortBy&order=$sortOrder&rpp=$recordPerPage&page=$currentPage";

$totalRecords = $Cheapad->count_total_cheapads ( $title );
$totalPage = ceil ( $totalRecords / $recordPerPage );

$newOrder = ($sortOrder == 'asc') ? 'desc' : 'asc';
?>

<script>
var url = '<?php echo $fullUrl; ?>';

$(function(){
	$('#dialog').hide();
	
});

function delete_cheapad(url){
	$cf = confirm('Are you sure you are going to remove this cheapad?');
	if($cf){
	    window.location = url;
		return false; 
	}else{
	    return false;	
	}
}

</script>
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
</style>
<div id="box">
	<div style="text-align: center;">
		<form action="manage_cheapads.php" method="post" name="cheapad"
			id="cheapad" style="float: right; margin-right: 80px;">
			<input type="text" onclick="this.value=''" name="title"
				style="width: 250px; color: rgb(102, 102, 102);"
				value="Please insert a sitehost here">
			<button type="submit">Search Cheapads</button>
		</form>
		<h3>Cheapads</h3>
	</div>
	<table id="cheapad_table" width="95%" cellpadding="4">
		<thead>
			<tr>
				<th width="60px"><a
					href="<?php echo $fullUrl;?>&sort=id&order=<?php echo $newOrder;?>">ID<?php if($sortBy == 'id') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
				<th><a
					href="<?php echo $fullUrl;?>&sort=title&order=<?php echo $newOrder;?>">Title<?php if($sortBy == 'title') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
				<th>Description</th>
				<th width="250px"><a
					href="<?php echo $fullUrl;?>&sort=sitehost&order=<?php echo $newOrder;?>">SiteHost<?php if($sortBy == 'sitehost') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
				<th width="80px"><a
					href="<?php echo $fullUrl;?>&sort=feedtype&order=<?php echo $newOrder;?>">FeedType<?php if($sortBy == 'feedtype') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
				<th width="80px"><a>Action</a></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ( $cheapadList as $pRow ) {
	$kw = (trim ( $pRow ['cheapad_feedtype'] ) != '') ? $pRow ['cheapad_feedtype'] : '';
	echo '<tr>';
	echo '<td class="a-center">' . $pRow ['cheapad_id'] . '</td>';
	echo '<td><a href="view_cheapad.php?pid=' . $pRow ['cheapad_id'] . '" target="_blank">' . $pRow ['cheapad_title'] . '</a></td>';
	echo '<td>' . $pRow ['cheapad_description'] . '</td>';
	echo '<td><a href="' . $pRow ['cheapad_sitehost'] . '" target="_blank">' . $pRow ['cheapad_sitehost'] . '</a></td>';
	echo '<td>' . $kw . '</td>';
	echo '<td><a href="edit_cheapad.php?pid=' . $pRow ['cheapad_id'] . '" target="_blank"><img src="images/edit.jpg" title="Edit Cheapad" width="16" height="16" /></a>';
	echo '<a href="#" onclick="javascript:delete_cheapad(\'' . $fullUrl . '&pid=' . $pRow ['cheapad_id'] . '&action=delete\'); return false;"><img src="images/del.gif" title="Delete Cheapad" width="16" height="16" /></a>';
	echo '<a href="edit_cheapad.php?pid=" target="_blank"><img src="images/new.jpg" title="New Cheapad" width="16" height="16" /></a>';
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
			if ($currentPage > 1) :
				?>
				<a
				href="<?php echo $fullUrl; ?>&page=<?php echo ($currentPage-1); ?>">
				<img src="images/arrow_left.gif" width="16" height="16" />
			</a>
			<?php endif;
			
			$leftBound = ($currentPage - 5) < 1 ? 1 : ($currentPage - 5);
			$rightBound = ($currentPage + 5) > $totalPage ? $totalPage : ($currentPage + 5);
			
			for($i = $leftBound; $i <= $rightBound; $i ++) {
				if ($i != $currentPage)
					echo '<a href="' . $fullUrl . '&page=' . $i . '">';
				echo $i;
				if ($i != $currentPage)
					echo '</a>';
				if ($i < $rightBound)
					echo ' . ';
				else
					echo ' ';
			}
			if ($currentPage < $totalPage) :
				?>
				<a
				href="<?php echo $fullUrl; ?>&page=<?php echo ($currentPage+1); ?>">
				<img src="images/arrow_right.gif" width="16" height="16" />
			</a>
			<?php endif; ?>	
			 | View  
			<select name="perpage" id="perpage"
				onchange="parent.document.location='<?php echo $fullUrl; ?>&rpp=' + this.value;">
				<option value="10"
					<?php if($recordPerPage == 10)	echo ' selected'; ?>>10</option>
				<option value="20"
					<?php if($recordPerPage == 20)	echo ' selected'; ?>>20</option>
				<option value="50"
					<?php if($recordPerPage == 50)	echo ' selected'; ?>>50</option>
				<option value="100"
					<?php if($recordPerPage == 100)	echo ' selected'; ?>>100</option>
			</select> per page | Total <strong><?php echo $totalRecords; ?></strong>
			records found
		</form>
	</div>
</div>
<?php
require_once ('footer.php');
?>

