<?php
/**
 * Manage theme images for base themplates
 * Author: Diana Devargas 7/03/2012
**/

require_once('config.php');

$A = Answer::getInstance($db);

$answer_id 	= isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$action		= isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$question_id= isset($_REQUEST['qid']) ? $_REQUEST['qid'] : '';
$headerMsg 	= $answer_id != '' ? 'Edit Answer' : 'New Answer';
$Msg 		= '';

	
if($action != '')
{
	switch ($action)
	{
		case 'getImageFromLibrary':
			$File = File::getInstance();
			$imageFiles = $File->getDirectoryList($config->sx25cssFolder.'layout_images/');
			$data = array();
			foreach ($imageFiles as $f)
			{
				$a['src'] = $config->sx25cssLink.'layout_images/'.$f;
				$a['name'] = $f;
				$data[] = $a;
			}
			echo json_encode($data);	
			exit;
		case 'upload':
			$File = File::getInstance();
			$File->upload_files(array('image_files' => $config->sx25cssFolder.'layout_images/'));
			exit;
	}
}
require_once('header.php');
?>
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
#main {  text-align: left;}
</style>
<script language="JavaScript">
	
	$(document).ready(function() {
		$("#image_gallery").trigger('ondblclick');
	});

	function cssLibrary(){
		var kw = $('#answer_type').val() + " - " +  $('#answer_keyword').val();
		$("#image_gallery").html('<img src="images/loading.gif" style="border:0px;" />');
	
		$.getJSON("manage_themeimages.php", {action:'getImageFromLibrary', start:1, limit:0},function(data){
			$("#image_gallery").html('');
			  if(null==data || data=='')
				  return;
			  $.each(data, function(key, value) { 					
	  				$("#image_gallery").append("<div style=\"display:block; float:left; margin:10px;\"><h3><a href='"+value['src']+"' target='_blank' style='clear:both; margin-left: auto; margin-right: auto'>"+value['name']+"</a></h3><img src='"+value['src']+"' style='height:100px; max-width:160px; background:#CBE3F5; padding:5px; display: block;   margin-left: auto;   margin-right: auto; ' /></div>");
			  });		 
	     });
	}
	
</script>
<div id="box">
	<table style="margin:auto;" width="95%" border="0" cellspacing="0" cellpadding="5">
		<tr>

			<td >&nbsp;</td>
			<td valign="top" id="main">
			
			<!-- *** START MAIN CONTENTS  *** -->
			
			</td>
			<td rowspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td >&nbsp;</td>
			<td valign="top" id="main" id="boxGray" style="background-color:#eeeeee;">
			<div><span class="txtHdr" style="float:left;">Images</span></div>
			<br>
			<br>
			</td>
			<td rowspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td >&nbsp;</td>
			<td valign="top" id="main" id="boxGray" style="background-color:#eeeeee;">
				<div id="output" class="dropzone" type="image_files" accept="gif|jpg|png|bmp" request="manage_themeimages.php?action=upload"  afterUpload="image_gallery">
					<ul id="output-listing"></ul>
				</div>		
			</td>
			<td rowspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td >&nbsp;</td>
			<td valign="center" id="image_gallery" style="background-color:#eeeeee;" ondblclick="cssLibrary();">No images
			</td>
			<td rowspan="5">&nbsp;</td>
		</tr>
	</table>

</div>
<?php
require_once('footer.php');
?>