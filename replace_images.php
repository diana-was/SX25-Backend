<?php
/**
 * Replace Images Tool
 * Author: Diana De vargas 23/06/2011
**/
require_once('config.php');
require_once('header.php');

$Site = Site::getInstance($db); 
$Img = image::getInstance($db);
$Lib = ImageLibrary::getInstance($db);

$domainExist = 0;
$row = 0;
$succ = 0;
$sucMsg = '';
$failMsg = '';

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'replace_images')
{
	echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br>';
	$pResults = $Lib->getImagesToReplace();
	$row = count($pResults);
	$keywords = array();
	$ids = array();
	foreach($pResults as $pRow) 
	{
		$keyword = strtolower(trim($pRow['image_library_keyword']));
		if ($key = array_search( $keyword, $keywords ))
		{
			$ids[$key][] = $pRow['image_library_id'];
		}
		else 
		{
			$key = count($keywords);
			$keywords[$key] = $keyword;
			$ids[$key] = array();
			$ids[$key][] = $pRow['image_library_id'];
		}
	}
	
	if (!empty($keywords))	
	{
		foreach($keywords as $key => $keyword)
		{
			$n = count ($ids[$key]);
			$images = array();
			$i = 0;
			$s = '';
			foreach($ids[$key] as $k => $image_library_id)
			{
				if (empty($images))
				{
					$i = $n - $k + 1;
					$images = $Lib->getGoogleImageSearch($keyword,($i>8?8:$i),$s);
					$s = empty($s)?8:$s+8; // next start
					if (empty($images))
						continue; // no images found
				}
				
				$image = array_pop ($images);
				if (!empty($image['content_photo_src']))
				{
					$Lib->replaceImage($image['content_photo_src'], $image_library_id);
					$succ++;
					echo '<p style="margin-left:30px;">Id: '.$image_library_id.', Keyword : '.$keyword.' <br /><img src="'.$image['content_photo_src'].'" width="60px" /></p>';
				}
			}
			ob_flush();
			flush();		
		} 
	}
	if($succ ==  $row)
		$sucMsg = "All $succ images have been replace.";
	else
		$failMsg = "$row images found for replacement. Only $succ images have been replace.";
	echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
}
else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'mark_images')
{
	echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br>';
	$uploaddir = $config->uploadFolder;
	$uploadfile = $uploaddir . basename($_FILES['csvfile']['name']);

	if (move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploadfile)) 
	{
		$r = 0;
		$handle = fopen($uploadfile, "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			$r++;
			if($r == 1 || empty($data) || empty($data[0]))
			{
				continue; //skip first line and empty lines
			}
			else
			{
				$domain_url = strtolower(trim($data[0]));
				echo "$domain_url";
				if($domain_id = $Site->check_domain_id($domain_url))
				{
					if($images = $Img->listDomainImages($domain_id))
					{
						foreach ($images as $img) 
						{
							$Lib->remove_image_byName($img['image_name']);
							$succ++;
						}
					}
					echo " : OK<br>";
					$row++;
				}
				else 
					echo " : NOT FOUND! <br>";
			}
		}
	}
	else 
		$failMsg .= "Error Reading file.<br>";
	if($succ > 0)
		$sucMsg .= "$row domains found. $succ images marked for replacement.";
	else
		$failMsg .= "$row domains found. NO images found for replacement.";
	echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
}

?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<?php if($sucMsg != '' || $failMsg != '') : ?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
						<div class="content" align="center">
				        <font color="Green"><?php echo $sucMsg;?></font><br />
						<font color="Red"><?php echo $failMsg;?></font>
				
						</div>
						</td>
					</tr>
				</table>
				<br />
			<?php endif; ?>
			<!-- *** START MAIN CONTENTS  *** -->
			
			<span class="txtHdr">Replace Images</span>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
			<tbody>
			<tr>
				<td valign="top" colspan="2">
					
					<div id="tableData">
					<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
						<tbody><tr>
						<td align="left" class="cellHdr"><strong>Replace Images</strong></td>
                        <td align="left" class="cellHdr"><strong>Mark Images to Replace </strong></td>
                        </tr>

						<tr class="alter1">
							<td valign="top" align="center" width="50%">	 
			                    <form action="/replace_images.php" method="POST" name="export">
								<input type="hidden" value="replace_images" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td align="left" colspan="3">Replace Images:</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"> <input type="submit" value="Replace Images" name="submit"></td>
									</tr>
                                   
								</table>
		                       </form>
							</td>
                            
							<td valign="top" align="left">
		                       	<form enctype="multipart/form-data" action="/replace_images.php" method="POST" id="data_table" name="import">
		                       	<input type="hidden" value="mark_images" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">

									<tr>
										<td align="left">Select CSV file to upload:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="left" valign="top" width="300px"><br>
											<input name="csvfile" type="file" />
											<br>
											<font size="-2">header must contain column name: domain</font>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"><input type="submit" value="Mark Images for Replacement" name="submit"></td>
									</tr>
								</table>
	                            </form>
							</td>
						</tr>
						</tbody></table>
					</div>
					</td>
				</tr>
			</tbody>
			</table>		
			
			<!-- *** END MAIN CONTENTS  *** -->
			
					
			</td>
			<td rowspan="5" class="brdrR">&nbsp;</td>
		</tr>
	</table>
</div>
<?php
require_once('footer.php');
?>