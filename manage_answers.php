<?php

require_once('config.php');
require_once('header.php');

$A = Answer::getInstance($db);
$QA = QuestionAnswer::getInstance($db);

$answerList = array();
$qID = isset($_REQUEST['qid'])?$_REQUEST['qid']:0;
$pid = isset($_REQUEST['pid'])?$_REQUEST['pid']:0;

$startId = isset($_REQUEST['start'])?$_REQUEST['start']:0;
$endId = isset($_REQUEST['end'])?$_REQUEST['end']:0;
$sortBy = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'id';
$sortOrder = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'asc';
$recordPerPage = isset($_REQUEST['rpp']) ? $_REQUEST['rpp'] : 20;
$currentPage = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$order = !empty($_REQUEST['answer_order'])?$_REQUEST['answer_order']:1;
$scrap_keyword = isset($_REQUEST['scrap_keyword'])?trim($_REQUEST['scrap_keyword']):'';

$fromRecord = ($currentPage-1) * $recordPerPage;

if($sortBy != '')
{
	$sortyQuery = " ORDER BY answer_".$sortBy." ".$sortOrder;
}
if (!empty($qID))
{
	$qaInfo 	= $QA->get_qa_info($qID);
}

if(!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'delete' : $A->del_answer($pid);
						break;
		case 'setorder' : 
						$A->save_answer(array('answer_order' => $order),$pid);
						break;
		case 'answers' : 
			            if (!empty($qaInfo))
			            {
							$answernum = $A->scrape_answer($qID, (empty($scrap_keyword)?$qaInfo['question_keyword']:$scrap_keyword), 5);
							if (empty($answernum))
								$Msg = "No answers found for: $scrap_keyword";
			            }
			            break;
	}
}

if (!empty($qID))
	$answerList = $A->get_answer($qID, $recordPerPage, $fromRecord, $sortyQuery);
else
	$answerList = $A->get_library_answer($fromRecord,$recordPerPage, $sortyQuery);

$fullUrl = "manage_answers.php?qid=$qID&start=$startId&end=$endId&sort=$sortBy&order=$sortOrder&rpp=$recordPerPage&page=$currentPage";


$totalRecords = $A->count_question_answers($qID);
$totalPage = ceil($totalRecords / $recordPerPage);

$newOrder = ($sortOrder == 'asc') ? 'desc' : 'asc';
?>

<script>
var url = '<?php echo $fullUrl; ?>';

$(function(){
	$('#dialog').hide();
	
});

function delete_answer(url)
{
	$cf = confirm('Are you sure you are going to remove this answer record?');
	if($cf){
	    window.location = url;
		return false; 
	}
	else{
	    return false;	
	}
}

function update_order(url,order)
{
    window.location = url + '&answer_order=' + order;
	return false; 
}

function update_qa(url, kw){
	$('#keyword').val(kw);
	$("#dialog_css").dialog({width:650, height:220, modal:true, shadow:true,buttons:{ "Ok": function() { $(this).dialog("close"); scrap_answers(url); }  },	 
		beforeclose: function(event, ui) { $("#dialog_css").dialog('destroy');}
	});
	return false; 
}

function scrap_answers(url){
	var keyword = $('#keyword').val();
    window.location = url + '&scrap_keyword=' + keyword;
}

</script>
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
    <?php if (!empty($qaInfo)) : ?>
    <br/>
    <br/>
	<table id="answer_table" width="95%">
		<thead>
			<tr>
                <th width="60px">Question ID</th>
                <th>Keyword</th>
                <th>Question Subject</th>
                <th>Question</th>
                <th width="60px">Get Answers</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="a-center"><?php echo $qaInfo['question_id']?></td>
				<td><?php echo $qaInfo['question_keyword']?></td>
				<td><?php echo $qaInfo['question_subject']?></td>
				<td><?php echo $qaInfo['question_content']?></td>
				<td class="a-center"><a href="#" onclick="javascript:update_qa('<?php echo $fullUrl;?>&action=answers','<?php echo $qaInfo['question_keyword']?>'); return false;"><img src="images/get.jpg" title="Get Answers" width="16" height="16" /></a></td>
			</tr>
		</tbody>
	</table>
	<?php endif; ?>
	<br/>
	<br/>
	<table id="answer_table" width="95%">
		<thead>
			<tr>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=id&order=<?php echo $newOrder;?>">ID<?php if($sortBy == 'id') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=type&order=<?php echo $newOrder;?>">Type<?php if($sortBy == 'type') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th><a href="<?php echo $fullUrl;?>&sort=keyword&order=<?php echo $newOrder;?>">Keyword<?php if($sortBy == 'keyword') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th><a href="<?php echo $fullUrl;?>&sort=subject&order=<?php echo $newOrder;?>">Subject<?php if($sortBy == 'subject') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="60%">Answer</th>
            	<th width="120px" valign="middle"><a href="edit_answer.php?pid=&qid=<?php echo $qaInfo['question_id'];?>" target="_blank" style="float: right;"><img src="images/new.jpg" title="New answer" width="16" height="16" /></a><p><a href="<?php echo $fullUrl;?>&sort=order&order=<?php echo $newOrder;?>">Actions/Order<?php if($sortBy == 'order') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></p></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($answerList as $pRow) 
{
	$kw = (trim($pRow['answer_keyword'])!='')?$pRow['answer_keyword']:$pRow['domain_keyword'];
	echo '<tr>';
   	echo '<td class="a-center">'.$pRow['answer_id'].'</td>';
	echo '<td class="a-center">'.$pRow['answer_type'].'</td>';
	echo '<td>'.$pRow['answer_keyword'].'</td>';
	echo '<td>'.$pRow['answer_subject'].'</td>';
	echo '<td>'.$A->replaceImageLibraryURL ($pRow['answer_content'],'display').'</td>'; //substr($pRow['answer_content'], 0, 600)
	echo '<td><a href="edit_answer.php?pid='.$pRow['answer_id'].'" target="_blank" style="float:left;"><img src="images/edit.jpg" title="Edit answer" width="16" height="16" /></a>';
	echo '<a href="#" style="float:left;" onclick="javascript:delete_answer(\''.$fullUrl.'&pid='.$pRow['answer_id'].'&action=delete\'); return false;"><img src="images/del.gif" title="Delete answer" width="16" height="16" /></a>';
	echo '<input type="text" value="'.$pRow['answer_order'].'" style="float:left;" onchange="javascript:update_order(\''.$fullUrl.'&order=0&pid='.$pRow['answer_id'].'&action=setorder\',this.value); return false;" maxlenght="2" size="2"/>';
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
