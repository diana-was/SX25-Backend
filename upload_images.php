<?php
/**
 * Dashboard home Script
 * Author: Diana Devargas 10/06/2011
**/

require_once('config.php');

$action		= !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$keyword    = !empty($_REQUEST['keyword'])? strtolower(trim($_REQUEST['keyword'])) : '';
$headerMsg 	= 'Upload Images';
$Msg 		= '';

	
if(!empty($action))
{
	switch ($action)
	{
		case 'upload':
			$ImageLibrary = ImageLibrary::getInstance($db);
			$File = File::getInstance();
			$_FILES["image_files"] = $File->fixFilesArray($_FILES["image_files"]);
			if (!empty($keyword) && isset($_FILES["image_files"]))
			{
				foreach($_FILES["image_files"] as $_file)
				{
					$resp = $ImageLibrary->saveImage($_file, $keyword, true, 3);
					echo (empty($resp)?'Error Loading':$resp);
				}
				exit;
			}
			echo 'Error,';
			if (empty($keyword)) echo ' no keyword';
			if (!isset($_FILES["image_files"])) echo ' no image';
			exit;
			break;
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
	var imageLibrary = '<?php echo $config->imageLibrary; ?>';
	
	$(document).ready(function() {
		$("#image_gallery").trigger('ondblclick');
	});

	$(window).unload(function() {
	    if (opener)
		opener.location.reload();
	});
		
	function imagelibrary(){
		var kw = $('#keyword').val();
		$("#image_gallery").html('<img src="images/loading.gif" style="border:0px;" />');
	
		$.getJSON("editImage.php", {action:'getImageFromLibrary', keyword: kw, start:1, limit:0, aproved:1},function(data){
			$("#image_gallery").html('');
			  if(data==null || data=='')
			  {
				  $('#image_gallery').html('No images found for ' +  kw);				  
				  return;
			  }
			  $.each(data, function(key, value) { 					
	  				$("#image_gallery").append("<div style=\"display:block; float:left; margin:10px;\"><h3><a href='"+imageLibrary+value['image_library_name']+"' target='_blank' style='clear:both; margin-left: auto; margin-right: auto'>"+imageLibrary+value['image_library_name']+"</a></h3><img src='"+imageLibrary+value['image_library_name']+"' style='height:160px; background:#CBE3F5; padding:5px; display: block;   margin-left: auto;   margin-right: auto; ' /></div>");
			  });		 
	     });
	}
	
	 function changeVal(kw) 
	 {
		 $('#output').attr('request', "upload_images.php?action=upload&keyword=" +  kw);
		 imagelibrary();
	 }
 
</script>
<div id="box">
	<table style="margin:auto;" width="95%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<td >&nbsp;</td>
			<td valign="top" id="main">
				<?php if($Msg != '') :	?>
					<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
						<tr>
							<td valign="top"><div class="blueHdr">System Message</div>
							<div class="content" align="center">
					        <font color="Green"><?php echo $Msg;?></font>
					
							</div>
							</td>
						</tr>
					</table>
				<?php endif; ?>		
			
<!-- *** START MAIN CONTENTS  *** -->
		
					<div><span class="txtHdr" style="float:left;"><?php echo $headerMsg;?></span></div>
					<br>
					<br>
				    <form id="form" method="post" action="upload_images.php">
					<table align="center"  border="0" cellpadding="3" cellspacing="0" width="100%" id="boxGray">
								<tr>
									<td width="30" valign="top">Keyword:</td>
									<td>
								 		<input type="text" size="162" id="keyword"  name="keyword" tabindex="2" value="<?php echo $keyword; ?>" onchange="changeVal(this.value);"/> 
									</td>
								</tr>
					</table>
					</form>
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
			<td valign="center" id="image_gallery" style="background-color:#eeeeee;" ondblclick="imagelibrary();">No images for <?php echo (!empty($keyword)?$keyword:'');?></td>
			<td rowspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td >&nbsp;</td>
			<td valign="top" id="main" id="boxGray" style="background-color:#eeeeee;">
				<div id="output" class="dropzone" type="image_files" accept="gif|jpg|png|bmp" request="upload_images.php?action=upload&keyword=<?php echo (!empty($keyword)?$keyword:'');?>"  afterUpload="image_gallery">
					<ul id="output-listing"></ul>
				</div>		
			</td>
			<td rowspan="5">&nbsp;</td>
		</tr>
	</table>

</div>
<?php
require_once('footer.php');
?>