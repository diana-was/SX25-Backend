<?php

require_once('config.php');

error_reporting(-1);
$Images = new ImageLibrary($db);
$Site = Site::getInstance($db); 

$iid 		= !empty($_REQUEST['iid'])?$_REQUEST['iid']:0;
$keyword 	= !empty($_REQUEST['keyword'])?$_REQUEST['keyword']:'';
$search 	= !empty($_REQUEST['search'])?$_REQUEST['search']:'';
$search_type= !empty($_REQUEST['search_type'])?$_REQUEST['search_type']:'';
if ($search_type == 'domain') 
{
	$search = $Site->check_domain_id($search);
}
$startId 		= !empty($_REQUEST['start'])?$_REQUEST['start']:0;
$endId 			= !empty($_REQUEST['end'])?$_REQUEST['end']:0;
$sortBy 		= !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : 'keyword';
$sortOrder 		= !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'asc';
$recordPerPage 	= !empty($_REQUEST['rpp']) ? $_REQUEST['rpp'] : 20;
$currentPage 	= !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;

$fromRecord = ($currentPage-1) * $recordPerPage;

if (!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'delete': 	$Images->remove_image($iid);
						break;
		case 'updateKeyword' : 	
						$Images->updateKeyword($iid, $keyword);
						break;
		case 'replaceImage':
						if (!empty($iid) && !empty($_REQUEST['url']))
						{
							if ($Images->replaceImage($_REQUEST['url'], $iid))
								echo 'OK|<font color="Green">Image Updated!!</font>';
							else 
								echo 'ERROR|<font color="Red">Error Updating Image</font>';
						} 
						else 
							echo 'ERROR|<font color="Red">Invalid URL</font>';
						break;
		case 'uploadImage': 
						if (!empty($iid) && !empty($_FILES["upimage"]))
						{
							if ($Images->replaceImage($_FILES["upimage"], $iid))
								echo 'OK|<font color="Green">Image Updated!!</font>';
							else 
								echo 'ERROR|<font color="Red">Error Updating Image</font>';
						} 
						else 
							echo 'ERROR|<font color="Red">Invalid URL</font>';
						break;
	}
	exit();
}
if($sortBy != '')
{
	$sortyQuery = " ORDER BY image_library_".$sortBy." ".$sortOrder;
}

$fullUrl = 'manage_images.php?start='.$startId.'&end='.$endId.'&sort='.$sortBy.'&order='.$sortOrder."&rpp=".$recordPerPage."&page=".$currentPage;
$fullUrl .= !empty($search)?'&search='.$search.'&search_type='.$search_type:'';

$imageRecords = $Images->getSXImage($fromRecord,$recordPerPage,$sortyQuery,$search,$search_type);
$totalRecords = $Images->count_total_images($search,$search_type);
$totalPage = ceil($totalRecords/$recordPerPage);
$newOrder = ($sortOrder == 'asc') ? 'desc' : 'asc';

require_once('header.php');

?>

<script>
var iid;
var kw;
var options = { 
	    target:     '#upload_area', 
	    success:    showResponse
	}; 
$(document).ready(function(){
	$('#dialog_css').hide(); 
	$('#dialog_bar').hide(); 	
	$('#upload_file').hide(); 	
 	$('#upload_form').submit(function(){
 	 	$(this).ajaxSubmit(options);
 		return false; 
    }); 
});

function delete_image(image_library_id){
	$cf = confirm('Are you sure you are going to remove this image?');
	if($cf){
	    $.get("manage_images.php", {action:'delete', iid:image_library_id}, function(data){
			$('#image_library_'+image_library_id).hide(1000);																 
		});
		return false; 
	}
	else{
	    return false;	
	}
}

function change_keyword(image_library_id, kw){
	iid = image_library_id;
	$('#keyword').val(kw);
	$("#dialog_css").dialog({width:650, height:220, modal:true, shadow:true,buttons:{ "Ok": function() { $(this).dialog("close"); updateKeyword(); }  },	 
		beforeclose: function(event, ui) { $("#dialog_css").dialog('destroy');}
	});
}

function updateKeyword(){
	var keyword = $('#keyword').val();
	var image_library_id = iid;
	$.get("manage_images.php", {action:'updateKeyword', keyword:keyword, iid:image_library_id}, function(data){		 
		$('#kw_'+iid).html(keyword);		
	});
}

function change_image(image_library_id,keyword){
	iid = image_library_id;
	kw = keyword;
	from_google('');
	$("#dialog_bar").dialog({width:1500, height:300, modal:true, shadow:true,	 
		beforeclose: function(event, ui) { $("#dialog_bar").dialog('destroy');}
	});
	
}

function replaceImage(url){
	$.get("manage_images.php", {action:'replaceImage', url:url, iid:iid}, function(data){		 
		var message = data.split("|");
		$("#image_gallery").html(message[1]);	
		if (message[0] == "OK")			
			$('#img_'+iid).attr("src", url);
	});
}

function upload_image(image_library_id,keyword){
	iid = image_library_id;
	kw = keyword;
	$("#upload_form #image_library_id").val(image_library_id);
	$("#upload_file").dialog({width:650, height:220, modal:true,  
		beforeclose: function(event, ui) { $("#upload_file").dialog('destroy');}
	});
}

function showResponse(responseText, statusText, xhr, $form)  { 
	var message = responseText.split("|");
	$("#image_gallery").html(message[1]);	
	if (message[0] == "OK")	
	{		
		var SRC = $('#img_'+iid).attr("src");
		$('#img_'+iid).attr("src", SRC + '?sec=' + Math.floor(Math.random()*11));
	}
} 

function from_google(s){
	if(s=='+')
		start+=8;
	else if(s=='-' && start>8){
		start-=8;
	}else if(s=='-' && start<8){
		$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look forward.</span>');
		return false;
	}else{
		start=1;	
	}
	$("#image_gallery").html('<img src="images/loading.gif" style="border:0px;" />');


	$.getJSON("editImage.php", {action:'loadGooglePics', keyword: kw, start:start},function(data){
		  if(null==data || data==''){
				$("#image_gallery").html('<span style="font-size:12px;">No more images. Please look backward.</span>');
				return false;  
		  }
		  $("#image_gallery").html('');
		  $("#bwd").html('<img onclick="from_google(\'-\')" src="images/ar6.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
		  $("#fwd").html('<img onclick="from_google(\'+\')" src="images/ar5.gif" style="cursor:pointer; border:0px; padding:6px 12px;" />');
		  $.each(data, function(key, value) { 					
  				$("#image_gallery").append("<img src='"+value+"' style='width:120px; height:160px; cursor:pointer;background:#CBE3F5; margin-left:3px; padding:5px;' onclick='replaceImage(this.src)' />");
			});		 
     });
}

function upload_images()
{
	var link = "upload_images.php?keyword=" + $('#image_keyword').val();
	newWindow = window.open(link, 'upload_images');
	newWindow.focus();
}

function approve_image(mid){
	var approved_flag = ($('#approved_flag_'+mid).val() == 1)?2:1;
	var question = ($('#approved_flag_'+mid).val() == 1)?"Do you want to approve this image?":"Do you want to unapprove this image?";
	var r = confirm(question);
	if (r == true){
	  	$.get("sx25_ajax.php", {action:'approve_image', mid: mid, approved:approved_flag},function(data){
			if(!data){
				alert('System error, please do it later.');
				return false;  
			}
			if (approved_flag != 1)
			{
				$("#image_approve_"+mid+" img").attr("src","images/approved.jpg");
				$("#image_approve_"+mid+" img").attr("title","image approved");
			    $('#approved_flag_'+mid).val(approved_flag);
			}
			else
			{
				$("#image_approve_"+mid+" img").attr("src","images/no-approved.jpg");
				$("#image_approve_"+mid+" img").attr("title","image not approved yet");
			    $('#approved_flag_'+mid).val(1);			  
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
</style>
<div id="box">
	<h3 style="margin:10px auto;">Images</h3>
	<div style="text-align:center; heigth:60px;">
		<a href="#" onclick="upload_images(); return false;" style="float:right; display:inline; margin: auto 60px auto 0;" target="_blank"><img src="images/upload.png" title="Upload Images"  /></a>
		<form action="manage_images.php" method="get" name="manage_images" id="manage_images" style="float:right; heigth:20px; margin: 20px 10px; 20px 0;">
			<input type="text" onclick="this.value=''" id="image_keyword" name="search" style="width: 250px; color: rgb(102, 102, 102);" value="<?php echo ($search_type == 'keyword' && !empty($search)?$search:'Please type keyword here');?>">
			<input type="hidden" name="search_type" value="keyword">
			<button type="submit">Search Images</button>
		</form>
		<form action="manage_images.php" method="get" name="manage_images" id="manage_images" style="float:right; heigth:20px; margin: 20px 80px 20px 0;">
			<input type="text" onclick="this.value=''" name="search" style="width: 250px; color: rgb(102, 102, 102);" value="<?php echo ($search_type == 'domain' && !empty($search)?$search:'Please type domain here');?>">
			<input type="hidden" name="search_type" value="domain">
			<button type="submit">Search Images</button>
		</form>
	</div>
	<table id="article_table" width="95%">
		<thead>
			<tr>
                <th width="100px"><a href="<?php echo $fullUrl;?>&sort=id&order=<?php echo $newOrder;?>&server=<?php echo $server;?>">ID<?php if($sortBy == 'id') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th><a href="<?php echo $fullUrl;?>&sort=name&order=<?php echo $newOrder;?>">Image Name<?php if($sortBy == 'name') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th><a href="<?php echo $fullUrl;?>&sort=keyword&order=<?php echo $newOrder;?>">Keyword<?php if($sortBy == 'keyword') {  echo '<img src="images/arrow_down_mini.gif" width="16" height="16" align="absmiddle" />'; }?></a></th>
                <th width="250px">Image</th>
                <th width="100px">Domains</th>
            	<th width="150px">Action</th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($imageRecords as $pRow) 
{
	$kw = trim($pRow['image_library_keyword']);
	echo '<tr id="image_library_'.$pRow['image_library_id'].'">';
   	echo '<td class="a-center">'.$pRow['image_library_id'].'<br /></td>';
	echo '<td class="a-center">'.$pRow['image_library_name'].'</td>';
	echo '<td class="a-center" id="kw_'.$pRow['image_library_id'].'">'.$kw.'</td>';
	echo '<td class="a-center"><img src="'.$config->imageLibrary.$pRow['image_library_name'].'?'.strtotime('now').'" id="img_'.$pRow['image_library_id'].'" width="200"></td>';
	echo '<td class="a-center">'.$pRow['domains'].'</td>';
	echo '<td><a href="#" onclick="javascript:change_keyword('.$pRow['image_library_id'].',\''.$kw.'\'); return false;"><img src="images/editIcon.jpg" title="Edit Keyword" /></a>';
	echo '<a href="edit_image.php?image_name='.$pRow['image_library_name'].'" target="blank"><img src="images/crop.jpg" title="Crop Image" /></a>';
	echo '<a href="#" onclick="javascript:change_image('.$pRow['image_library_id'].',\''.$kw.'\'); return false;"><img src="images/swap.bmp" title="Replace Image" /></a>';
	echo '<a href="#" onclick="javascript:upload_image('.$pRow['image_library_id'].',\''.$kw.'\'); return false;"><img src="images/upload_photo.png" title="Upload Image" /></a>';
	echo '<a href="#" onclick="javascript:delete_image(\''.$pRow['image_library_id'].'\'); return false;"><img src="images/deleteIcon.jpg" title="Remove Image"  /></a>';
	
	switch($pRow['image_library_approved']):
		case '1':
			echo "<a href='#' onclick='approve_image(".$pRow['image_library_id']."); return false;' id='image_approve_".$pRow['image_library_id']."'><input type='hidden' id='approved_flag_".$pRow['image_library_id']."' value='".$pRow['image_library_approved']."' /><img src='images/no-approved.jpg' title='image not approved' height='48' /></a></td>";
				break;
		default:
        	echo "<a href='#' onclick='approve_image(".$pRow['image_library_id']."); return false;' id='image_approve_".$pRow['image_library_id']."'><input type='hidden' id='approved_flag_".$pRow['image_library_id']."' value='".$pRow['image_library_approved']."' /><img src='images/approved.jpg' title='image approved' height='48' /></a></td>";
	endswitch;			
						
	echo '</tr>';
}
?>
		</tbody>
	</table>

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

<div id="dialog_css"  title="Change Keyword" style="padding: 40px;">
                        <div class="etf"><span>Keyword: <span style="color: rgb(248, 25, 2); float: right;">* </span></span>
                        <input type="text" style="width: 440px;" id="keyword" name="keyword"></div>
                        <input type="hidden" id="image_library_id" name="image_library_id">
</div> 

<div id="dialog_bar" style="margin:auto; text-align:center;  margin-top:10px">
	<div >
		<table style="margin:auto; margin-top:12px">
			<tr>
				<td id="bwd" style="border-bottom:0px;border-left:0px;"></td>
				<td id="image_gallery" style="border-bottom:0px;border-left:0px;"></td>
				<td id="fwd" style="border-bottom:0px;border-left:0px;"></td>
			</tr>
		</table>
	</div>
</div>                   
<div id="upload_file"  title="Upload Picture"  style="padding: 40px;">
      <div class="etf">
      </div>
	  <form id="upload_form" name="upload_form" action="manage_images.php" enctype="multipart/form-data" method="POST" style="color:red;">
	  <input type="file" SIZE="70;" name="upimage" />
      <input type="hidden" id="image_library_id" name="iid">
	  <input type="hidden" name="action" value="uploadImage" >
	  <button >Go</button>
	  </form>
	  <div id="upload_area" style="color: rgb(248, 25, 2); "></div>
</div>                   
<?php 
require_once('footer.php');

?>

