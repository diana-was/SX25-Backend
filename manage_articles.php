<?php

require_once('config.php');
require_once('header.php');

$Articles = Article::getInstance($db);
$Site = Site::getInstance($db); 

$articleList = array();
$pid = isset($_REQUEST['pid'])?$_REQUEST['pid']:0;

$startId = isset($_REQUEST['start'])?$_REQUEST['start']:0;
$endId = isset($_REQUEST['end'])?$_REQUEST['end']:0;
$sortBy = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'id';
$sortOrder = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'desc';
$recordPerPage = isset($_REQUEST['rpp']) ? $_REQUEST['rpp'] : 20;
$currentPage = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$server = isset($_REQUEST['server'])? $_REQUEST['server'] : 'sx25';
$keyword = isset($_REQUEST['keyword'])?trim($_REQUEST['keyword']):'';
$display = isset($_REQUEST['display']) ? $_REQUEST['display'] : '';

$fromRecord = ($currentPage-1) * $recordPerPage;

if($sortBy != '')
{
	$sortyQuery = " ORDER BY article_".$sortBy." ".$sortOrder;
}

// Display approved not approved or all
$all_articles_selected = $approved_articles_selected = $unapproved_articles_selected = '';
switch($display):
		case 'approved_articles': $appFlag = 1;
								  $approved_articles_selected = "selected='selected'";
								  break;
					
		case 'unapproved_articles': $appFlag = 0;
									$unapproved_articles_selected = "selected='selected'";
									break;
					
		default:    $appFlag = null;
					$all_articles_selected = "selected='selected'";
					break;
endswitch;


if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete')
{
	if ($server == 'library')
		$Articles->del_article($pid);
	else
		$Articles->unlink_articles($pid);
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'linkarticle' && ($pid > 0))
{
	$linkDomain = isset($_REQUEST['domain_url'])?trim($_REQUEST['domain_url']):'';
	$domain_id 	= $Site->check_domain_id($linkDomain); 
	$default   	= $Articles->check_default($linkDomain)?0:1;
	if ($domain_id)
		$Articles->save_article(array('article_domain_id' =>$domain_id,'article_domain' =>  $linkDomain, 'article_default' => $default),$pid);
	else
		$Articles->save_article(array('article_domain' =>  $linkDomain, 'article_default' => $default),$pid);
}

$articleList_array = array();
$domainname = isset($_REQUEST['domainname'])?trim($_REQUEST['domainname']):''; 
$domainname = ($domainname!='Please insert the domain name here')?$domainname:''; 

if(!empty($domainname)) {
	$domains = $Site->extractTextarea($domainname);
	
	foreach($domains as $key => $thisDomain){
		$al = $Articles->get_domain_articles($thisDomain,$sortyQuery);
		$articleList_array[] = $al;
	}
}
else
{
	switch ($server)
	{
		case 'sx25':	$articleList = $Articles->get_sx25_articles($fromRecord,$recordPerPage,$sortyQuery,$appFlag);
						break;
		case 'parked':	$articleList = $Articles->get_other_articles($fromRecord,$recordPerPage,$sortyQuery,$appFlag);
						break;
		default:		if (!empty($keyword))
							$articleList = $Articles->get_unused_articles($keyword, 100, 0, $appFlag);
						else
							$articleList = $Articles->get_library_articles($fromRecord,$recordPerPage,$sortyQuery,$appFlag);
						$server = 'library';
						break;
	}
}

function trim_value(&$value) 
{ 
    $value = trim($value);
	$value = strtolower($value);
}

$fullUrl = "manage_articles.php?server=$server&domainname=$domainname&keyword=$keyword&start=$startId&end=$endId&sort=$sortBy&order=$sortOrder&rpp=$recordPerPage&page=$currentPage&display=$display";


$totalRecords = $Articles->count_total_articles($server,$domainname,$appFlag);
$totalPage = ceil($totalRecords / $recordPerPage);

$newOrder = ($sortOrder == 'asc') ? 'desc' : 'asc';
?>
<script>
var url = '<?php echo $fullUrl; ?>';

$(function(){
	$('#dialog').hide();

	$('#display').change(function(){
			document.domain_article.submit();					
	});
	
	$("#ascrape").click(function (e) {		
	  $('#popup_a').show(600);
	  var offset = $(this).offset();
	  var lt = offset.left-400+10;
	  var tp = offset.top+30;
	  $('#popup_a').css('left',lt); 	  
	  $('#popup_a').css('top',tp);
	});
	
	$('#a_close').click(function(){
		$('#popup_a').hide(600); 
		return false;
	});		   				
	
	$('#scrape_a_form').on('submit', function(e) {
            e.preventDefault(); // <-- important
			var sk = $('#save_keyword').val();
			var limitnum = $('#limitnum').val();
			var time = limitnum*2000; 
			beforeSubmit: $('#a_message').html('<img src="images/loading.gif" />'+limitnum+', '+time);
            $(this).ajaxSubmit({
                target: '#a_message',
				success: $('#a_message_2').html('<a href="manage_articles.php?keyword='+sk+'"> To see the latest scraping result</a>').show(time)
            });
    });	
});

function delete_article(url){
	$cf = confirm('Are you sure you are going to remove this article?');
	if($cf){
	    window.location = url;
		return false; 
	}
	else{
	    return false;	
	}
}

function linkArticle(pid){
	$("#dialog").dialog({width:350, height:250, modal:true, shadow:true,buttons:{ "Ok": function() { $(this).dialog("close"); saveArticle(pid); }  },	beforeclose: 
		function(event, ui) { $("#dialog").dialog('destroy');}
	});
}
function saveArticle(pid){
	var domain_url = $('#domain_url').val();
	if (domain_url.length > 0)
    	window.location = url + '&action=linkarticle&domain_url=' + domain_url + '&pid=' + pid;
}

function approve_article(aid){
	var r = confirm("Do you want to approve this article?");
	if (r == true){
		approved_flag = ($('#approved_flag_'+aid).val() == 1)?0:1;
	  	$.get("sx25_ajax.php", {action:'approve_article', aid: aid, approved:approved_flag},function(data){
			if(!data){
				alert('System error, please do it later.');
				return false;  
			}
			if (approved_flag == 1)
			{
				$("#article_approve_"+aid+" img").attr("src","images/approved.jpg");
				$("#article_approve_"+aid+" img").attr("title","Article approved");
			    $('#approved_flag_'+aid).val(1);
			}
			else
			{
				$("#article_approve_"+aid+" img").attr("src","images/no-approved.jpg");
				$("#article_approve_"+aid+" img").attr("title","Article not approved yet");
			    $('#approved_flag_'+aid).val(0);			  
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

#article_table
{
border-collapse:collapse;
background:#FFFFFF;
margin: auto;
text-align:left
}
#article_table table, #article_table td, #article_table th
{
border:1px solid black;
}
#article_table img{margin:10px;  border: 0 none;}
#pager img{margin:1px;  border: 0 none;}
#article_table a, #pager a{text-decoration: none;}
.a-center { text-align: center;}

#popup_a
{
 position:absolute; 
 background:#fff;
 border: 8px solid #ccc;
 width:400px;
 display:none;
 z-index:999;
}

#popup_a b{
    clear: left;
    float: left;
    width: 120px;
}

#popup_a .input{
    height:30px;
	margin-left: 20px;
}

#ascrape{
 cursor:pointer; 
 float:right; 
 width:23px;
}

#a_close{
 float:right;
 cursor:pointer; 
}
#a_message, #a_message_2{margin-left: 20px;  margin-bottom: 20px;}
#a_message_2{display:none}
</style>
<div id="box">
	<div style="text-align:center;">
    	
            
		<form action="manage_articles.php" method="post" name="domain_article" id="domain_article" style="float:right; margin-right: 60px;">
			<input type="hidden" name="server" value="<?php echo $server;?>">
			<?php if ($server == 'library') : ?>
			<input type="text" onclick="this.value=''" name="keyword" style="width: 250px; color: rgb(102, 102, 102);" value="Please insert a keyword here">
			<button type="submit">Search Articles</button>
			<?php else : ?>
			<input type="text" onclick="this.value=''" name="domainname" style="width: 250px; color: rgb(102, 102, 102);" value="Please insert the domain name here">
			<button type="submit">Search Articles</button>
			<?php endif; ?>
            
            <select name="display" id="display" style="float:right; margin-left:30px;">
                <option value="all_articles" <?php echo $all_articles_selected;?> >Display all articles</option>
                <option value="approved_articles" <?php echo $approved_articles_selected;?> >approved article only</option>
                <option value="unapproved_articles" <?php echo $unapproved_articles_selected;?> >unapproved article only</option>         
            </select>
            
		</form><h3>Articles</h3>
	</div>
	<table id="article_table" width="95%">
		<thead>
			<tr>
                <th width="60px"><a href="<?php echo $fullUrl;?>&sort=id&order=<?php echo $newOrder;?>&server=<?php echo $server;?>">ID<?php if($sortBy == 'id') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th><a href="<?php echo $fullUrl;?>&sort=title&order=<?php echo $newOrder;?>&server=<?php echo $server;?>">Title<?php if($sortBy == 'title') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
				<?php if ($server == 'library') : ?>
                <th>Summary</th>
				<?php else : ?>
                <th width="170px"><a href="<?php echo $fullUrl;?>&sort=website&order=<?php echo $newOrder;?>&server=<?php echo $server;?>">Domain<?php if($sortBy == 'website') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
				<?php endif; ?>
                <th width="150px"><a href="<?php echo $fullUrl;?>&sort=keyword&order=<?php echo $newOrder;?>&server=<?php echo $server;?>">Keyword<?php if($sortBy == 'keyword') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="80px"><a href="<?php echo $fullUrl;?>&sort=default&order=<?php echo $newOrder;?>&server=<?php echo $server;?>">Server<?php if($sortBy == 'default') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
            	<th width="80px"><img src="images/scrape.png" id="ascrape" title="Scrape Article" /><a>Action</a></th>
			</tr>
		</thead>
		<tbody>
<?php
if(!empty($articleList_array)){
	foreach($articleList_array as $key=> $articleList){
		display_articles($articleList,$server,$fullUrl);
	}	
}else{
	display_articles($articleList,$server,$fullUrl);
}

function display_articles($articleList,$server,$fullUrl){
	foreach ($articleList as $pRow) 
	{
		$kw = (trim($pRow['article_keyword'])!='')?$pRow['article_keyword']:$pRow['domain_keyword'];
		$domain = $pRow['article_domain'];
		echo '<tr>';
		echo '<td class="a-center">'.$pRow['article_id'].'<br /></td>';
		echo '<td><a href="view_article.php?pid='.$pRow['article_id'].'" target="_blank">'.$pRow['article_title'].'</a></td>';
		if ($server == 'library')
			echo '<td>'.$pRow['article_summary'].'</td>';
		else
			echo '<td><a href="http://'.$domain.'" target="_blank">'.$domain.'</a></td>';
		echo '<td>'.$kw.'</td>';
		echo '<td class="a-center">'.$server.'</td>';
		echo '<td><a href="edit_article.php?pid='.$pRow['article_id'].'&domain='.$domain.'" ><img src="images/edit.jpg" title="Edit Article" width="16" height="16" /></a>';
		echo '<a href="#" onclick="javascript:delete_article(\''.$fullUrl.'&pid='.$pRow['article_id'].'&action=delete\'); return false;"><img src="images/del.gif" title="Delete Article" width="16" height="16" /></a>';

		switch($pRow['article_approved']):
			case '1':
				echo "<a href='#' onclick='approve_article(".$pRow['article_id']."); return false;' id='article_approve_".$pRow['article_id']."'><input type='hidden' id='approved_flag_".$pRow['article_id']."' value='".$pRow['article_approved']."' /><img src='images/approved.jpg' title='Article approved' height='20' /></a>";
				break;
			case '0':
			default:
        		echo "<a href='#' onclick='approve_article(".$pRow['article_id']."); return false;' id='article_approve_".$pRow['article_id']."'><input type='hidden' id='approved_flag_".$pRow['article_id']."' value='".$pRow['article_approved']."' /><img src='images/no-approved.jpg' title='Article not approved yet' height='20' /></a>";
		endswitch;
		
		if ($server == 'library')
			echo '<a href="#" onclick="javascript:linkArticle('.$pRow['article_id'].'); return false;"><img src="images/link.jpg" title="Link Article" width="16" height="16" /></a>';
		else
			echo '<a href="edit_article.php?pid=&domain='.$domain.'" target="_blank"><img src="images/new.jpg" title="New Article" width="16" height="16" /></a>';
		echo '</td>';
		echo '</tr>';
	}
}	
?>
		</tbody>
	</table>
	<div id="dialog" title="Link Domain" >
		<br /><br />
        Domain URL: <br /><br /><input name="domain_url" id="domain_url" value="" type="text" size="40" />
        <br /><br />
    </div>

	<div id="pager">
		<form method="post" name="form1" id="form1">
		<input type="hidden" name="server" value="<?php echo $server;?>">
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
					echo '<a href="'.$fullUrl.'&page='.$i.'&server='.$server.'">';
				echo $i;
				if($i != $currentPage)
					echo '</a>';
				if ($i < $rightBound)
					echo ' . ';
				else 
					echo ' ';
			}
			if($currentPage < $totalPage) : ?>
				<a href="<?php echo $fullUrl; ?>&page=<?php echo ($currentPage+1); ?>&server=<?php echo $server; ?>">	
				<img src="images/arrow_right.gif" width="16" height="16" />
				</a>
			<?php endif; ?>	
			 | View  
			<select name="perpage" id="perpage" onchange="parent.document.location='<?php echo $fullUrl; ?>&server=<?php echo $server;?>&rpp=' + this.value;">
				<option value="10" <?php if($recordPerPage == 10)	echo ' selected'; ?> >10</option>
				<option value="20" <?php if($recordPerPage == 20)	echo ' selected'; ?> >20</option>
				<option value="50" <?php if($recordPerPage == 50)	echo ' selected'; ?> >50</option>
				<option value="100" <?php if($recordPerPage == 100)	echo ' selected'; ?> >100</option>
			</select>
			per page | Total <strong><?php echo $totalRecords; ?></strong> records found
		</form>
	</div> 
</div>

<div id="popup_a"  title="Scrape Article">
    <a hre="#" id="a_close">Close</a>
	<form id="scrape_a_form" method="post" action="sx25_ajax.php" enctype="multipart/form-data">
	<h3 style="margin:20px 0 20px 120px">Scrape Article</h3>
	<div class="input"><b>Search Keyword:</b><input type="text" name="keyword" /></div>
	<div class="input"><b>Save Keyword:</b><input type="text" name="save_keyword" id="save_keyword" /></div>
	<div class="input"><b>Article Source:</b>
		<select name="article_source" id="article_source">
			<option value="ehow" selected>ehow</option>
			<option value="EzineArticles" >EzineArticles</option>
			<option value="hubpages" >hubpages</option>
		</select>			
	</div>
	<div class="input"><b>Amount:</b>
		<select name="limitnum" id="limitnum">
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
	<input type="hidden" value="scrape_article" name="action" />	
	<br />
	<div class="input"><input type="submit" name="submit" value="Submit" />	</div>
	<br /> 
	<br />
	</form>
	<div id="a_message"></div>
	<div id="a_message_2"></div>
</div> 
<?php 
require_once('footer.php');
?>

