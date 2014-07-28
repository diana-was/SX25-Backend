<?php

require_once('config.php');
require_once('header.php');

$QA = QuestionAnswer::getInstance($db);
$A = Answer::getInstance($db);

$qaList = array();
$pid = isset($_REQUEST['pid'])?$_REQUEST['pid']:0;

$startId = isset($_REQUEST['start'])?$_REQUEST['start']:0;
$endId = isset($_REQUEST['end'])?$_REQUEST['end']:0;
$sortBy = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'id';
$sortOrder = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'desc';
$recordPerPage = isset($_REQUEST['rpp']) ? $_REQUEST['rpp'] : 20;
$currentPage = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$default = isset($_REQUEST['default']) && ($_REQUEST['default']==1)?1:0;
$scrap_keyword = isset($_REQUEST['scrap_keyword'])?trim($_REQUEST['scrap_keyword']):'';
$keyword = isset($_REQUEST['keyword'])?trim($_REQUEST['keyword']):''; 
$keyword = ($keyword!='Please insert a keyword here')?$keyword:'';  
$display = isset($_REQUEST['display']) ? $_REQUEST['display'] : '';

$fromRecord = ($currentPage-1) * $recordPerPage;

if($sortBy != '')
{
	$sortyQuery = " ORDER BY question_".$sortBy." ".$sortOrder;
}

// Display approved not approved or all
$all_questions_selected = $approved_questions_selected = $unapproved_questions_selected = '';
switch($display):
		case 'approved_questions': $appFlag = 1;
								  $approved_questions_selected = "selected='selected'";
								  break;
					
		case 'unapproved_questions': $appFlag = 0;
									$unapproved_questions_selected = "selected='selected'";
									break;
					
		default:    $appFlag = null;
					$all_questions_selected = "selected='selected'";
					break;
endswitch;

if(!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'delete' : $QA->del_qa($pid);
						break;
		case 'setdefault' : 
						$QA->save_qa(array('question_default' => $default),$pid);
						break;
		case 'answers' : 
			            $Q = $QA->get_qa_info($pid);
			            if (!empty($Q))
			            {
							$answernum = $A->scrape_answer($pid, (empty($scrap_keyword)?$Q['question_keyword']:$scrap_keyword), 5);
							if (empty($answernum))
								$Msg = "No answers found for: $scrap_keyword";
			            }
			            break;
	}
}

if (!empty($keyword))
	$qaList = $QA->get_qa($keyword, $recordPerPage, $fromRecord, $sortyQuery,$appFlag);
else
	$qaList = $QA->get_library_qa($fromRecord,$recordPerPage,$sortyQuery,$appFlag);

$fullUrl = "manage_question_answer.php?keyword=$keyword&start=$startId&end=$endId&sort=$sortBy&order=$sortOrder&rpp=$recordPerPage&page=$currentPage";


$totalRecords = $QA->count_total_qa($keyword);
$totalPage = ceil($totalRecords / $recordPerPage);

$newOrder = ($sortOrder == 'asc') ? 'desc' : 'asc';
?>

<script>
var url = '<?php echo $fullUrl; ?>';

$(function(){
	$('#dialog').hide();
	// reload when display change
	$('#display').change(function(){
			document.question.submit();					
	});
	
	$("#qascrape").click(function (e) {		
	  $('#popup_qa').show(600);
	  var offset = $(this).offset();
	  var lt = offset.left-400+10;
	  var tp = offset.top+30;
	  $('#popup_qa').css('left',lt); 	  
	  $('#popup_qa').css('top',tp);
	});
	
	$('#qa_close').click(function(){
		$('#popup_qa').hide(600); 
		return false;
	});		   				
	
	$('#scrape_qa_form').on('submit', function(e) {
            e.preventDefault(); // <-- important
			var sk = $('#save_keyword').val();
			var limitnum = $('#limitnum').val();
			var time = limitnum*2000; 
			beforeSubmit: $('#qa_message').html('<img src="images/loading.gif" />');
            $(this).ajaxSubmit({
                target: '#qa_message',
				success: $('#qa_message_2').html('<a href="manage_question_answer.php?keyword='+sk+'"> To see the latest scraping result</a>').show(time)
            });
        });	
});


function delete_qa(url)
{
	$cf = confirm('Are you sure you are going to remove this question/answer record?');
	if($cf){
	    window.location = url;
		return false; 
	}
	else{
	    return false;	
	}
}

function update_qa(url)
{
    window.location = url;
	return false; 
}

function get_answers(url, kw){
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

function approve_question(qid){
	var r = confirm("Do you want to approve this question?");
	if (r == true){
	  	approved_flag = ($('#approved_flag_'+qid).val() == 1)?0:1;
	  	$.get("sx25_ajax.php", {action:'approve_question', qid: qid, approved:approved_flag},function(data){
			if(!data){
				alert('System error, please do it later.');
				return false;  
			}
			if (approved_flag == 1)
			{
				$("#question_approve_"+qid+" img").attr("src","images/approved.jpg");
				$("#question_approve_"+qid+" img").attr("title","Question approved");
			    $('#approved_flag_'+qid).val(1);
			}
			else
			{
				$("#question_approve_"+qid+" img").attr("src","images/no-approved.jpg");
				$("#question_approve_"+qid+" img").attr("title","Question not approved yet");
			    $('#approved_flag_'+qid).val(0);			  
			}
		});
	}
}

</script>
<style>
#box{
width:100%;
text-align:center;
}

#question_table
{
border-collapse:collapse;
background:#FFFFFF;
margin: auto;
text-align:left
}
#question_table table, #question_table td, #question_table th
{
border:1px solid black;
}
#question_table img{margin:10px;  border: 0 none;}
#pager img{margin:1px;  border: 0 none;}
#question_table a, #pager a{text-decoration: none;}
.a-center { text-align: center;}

#popup_qa
{
 position:absolute; 
 background:#fff;
 border: 8px solid #ccc;
 width:400px;
 display:none;
 z-index:999;
}

#popup_qa b{
    clear: left;
    float: left;
    width: 120px;
}

#popup_qa .input{
    height:30px;
	margin-left: 20px;
}

#qascrape{
 cursor:pointer; 
 float:right; 
 width:23px;
}

#qa_close{
 float:right;
 cursor:pointer; 
}
#qa_message, #qa_message_2{margin-left: 20px; margin-bottom: 20px;}
#qa_message_2{display:none}
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
		<form action="manage_question_answer.php" method="post" name="question" id="question" style="float:right; margin-right: 80px;">
			<input type="text" onclick="this.value=''" name="keyword" style="width: 250px; color: rgb(102, 102, 102);" value="Please insert a keyword here">
			<button type="submit">Search Question/Answer</button>
            <select name="display" id="display" style="float:right; margin-left:30px;">
                <option value="all_questions" <?php echo $all_questions_selected;?> >Display all questions</option>
                <option value="approved_questions" <?php echo $approved_questions_selected;?> >approved question only</option>
                <option value="unapproved_questions" <?php echo $unapproved_questions_selected;?> >unapproved question only</option>         
            </select>
		</form>
		<h3>Question/Answer</h3>
	</div>
	<table id="question_table" width="95%">
		<thead>
			<tr>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=id&order=<?php echo $newOrder;?>">ID<?php if($sortBy == 'id') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th><a href="<?php echo $fullUrl;?>&sort=subject&order=<?php echo $newOrder;?>">Subject<?php if($sortBy == 'subject') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th>Description</th>
                <th width="150px"><a href="<?php echo $fullUrl;?>&sort=keyword&order=<?php echo $newOrder;?>">Keyword<?php if($sortBy == 'keyword') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="450px"><a href="<?php echo $fullUrl;?>&sort=answer&order=<?php echo $newOrder;?>">Answer<?php if($sortBy == 'answer') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
            	<th width="80px" valign="middle"><a href="edit_question_answer.php?pid=" target="_blank" style="float: right;"><img src="images/new.jpg" title="New question" width="16" height="16" /></a><img src="images/scrape.png" id="qascrape" title="Scrape Q&A" /><p>Action</p></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($qaList as $pRow) 
{
	$kw = (trim($pRow['question_keyword'])!='')?$pRow['question_keyword']:$pRow['domain_keyword'];
	echo '<tr>';
   	echo '<td class="a-center">'.$pRow['question_id'].'</td>';
	echo '<td>'.$pRow['question_subject'].'</td>';
	echo '<td>'.substr($pRow['question_content'], 0, 200).' ... </td>';
	echo '<td>'.$kw.'</td>';
	echo '<td>'.substr($pRow['question_answer'], 0, 200).' ... </td>';
	echo '<td><a href="edit_question_answer.php?pid='.$pRow['question_id'].'" target="_blank"><img src="images/edit.jpg" title="Edit question" width="16" height="16" /></a>';
	echo '<a href="#" onclick="javascript:delete_qa(\''.$fullUrl.'&pid='.$pRow['question_id'].'&action=delete\'); return false;"><img src="images/del.gif" title="Delete question" width="16" height="16" /></a>';
	if ($pRow['question_default']==0)
		echo '<a href="#" onclick="javascript:update_qa(\''.$fullUrl.'&default=1&pid='.$pRow['question_id'].'&action=setdefault\'); return false;"><img src="images/normal.png" title="Set Default" width="16" height="16" /></a>';
	else
		echo '<a href="#" onclick="javascript:update_qa(\''.$fullUrl.'&default=0&pid='.$pRow['question_id'].'&action=setdefault\'); return false;"><img src="images/default.png" title="Set Normal" width="16" height="16" /></a>';
		
		
	switch($pRow['question_approved']):
			case '1':
        		echo "<a href='#' onclick='approve_question(".$pRow['question_id']."); return false;' id='question_approve_".$pRow['question_id']."'><input type='hidden' id='approved_flag_".$pRow['question_id']."' value='".$pRow['question_approved']."' /><img src='images/approved.jpg' title='Question approved'height='20' /></a>";
				break;
			case '0':
			default:
        		echo "<a href='#' onclick='approve_question(".$pRow['question_id']."); return false;' id='question_approve_".$pRow['question_id']."'><input type='hidden' id='approved_flag_".$pRow['question_id']."' value='".$pRow['question_approved']."' /><img src='images/no-approved.jpg' title='Question not approved yet' height='20' /></a>";
	endswitch;
		
		
	echo '<a href="#" onclick="javascript:get_answers(\''.$fullUrl.'&pid='.$pRow['question_id'].'&action=answers\',\''.$kw.'\'); return false;"><img src="images/get.jpg" title="Get Answers" width="16" height="16" /></a>';
	if ($pRow['num_answers'] > 0)
		echo '<a href="manage_answers.php?qid='.$pRow['question_id'].'" target="_blank"><img src="images/a.png" title="See '.$pRow['num_answers'].' answers" width="20" height="20"/></a>';
	else
		echo '<a href="manage_answers.php?qid='.$pRow['question_id'].'" target="_blank"><img src="images/a-.png" title="Create answers" width="20" height="20"/></a>';
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

<div id="popup_qa"  title="Scrape Q&A">
    <a hre="#" id="qa_close">Close</a>
	<form id="scrape_qa_form" method="post" action="sx25_ajax.php" enctype="multipart/form-data">
	<h3 style="margin:20px 0 20px 120px">Scrape Q&A</h3>
	<div class="input"><b>Search Keyword:</b><input type="text" name="keyword" /></div>
	<div class="input"><b>Save Keyword:</b><input type="text" name="save_keyword" id="save_keyword" /></div>
	<div class="input"><b>Amount:</b><select name="limitnum" id="limitnum">
			<option value="1" selected>1</option>
			<option value="2" >2</option>
			<option value="3" >3</option>
			<option value="4" >4</option>
			<option value="5" >5</option>
			<option value="6" >6</option>
			<option value="7" >7</option>
			<option value="8" >8</option>
			<option value="9" >9</option>
			<option value="10" >10</option>
		</select>
	</div>
	<input type="hidden" value="scrape_qa" name="action" />	
	<br />
	<div class="input"><input type="submit" name="submit" value="Submit" />	</div>
	<br /> 
	<br />
	</form>
	<div id="qa_message"></div>
	<div id="qa_message_2"></div>
</div> 
	

<?php 
require_once('footer.php');
?>

