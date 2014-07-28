<?php
/**
 * SX25 upload directories data
 * Author: Diana De Vargas
 * Date: 2012-02-29
**/
require_once('config.php');


$Directory = Dty::getInstance($db); 
$artNoKey = 0;
$succ = 0;
$err  = 0;
$fail = false;
$sucMsg = isset($_REQUEST['sucMsg'])?$_REQUEST['sucMsg']:'';
$failMsg = isset($_REQUEST['failMsg'])?$_REQUEST['failMsg']:'';
$log = isset($_REQUEST['log'])?$_REQUEST['log']:'';
// menues
$menExist = 0;
$menNoKey = 0;
$succMenu = 0;
$failMenu = 0;

if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'upload_directories')
{
	require_once('header.php');
	echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br>';
	$uploaddir = $config->uploadFolder;
	$uploadfile = $uploaddir . basename($_FILES['csvfile']['name']);

	if (move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploadfile)) 
	{
		$row = 1;
		$handle = fopen($uploadfile, "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			if($row == 1 || empty($data) || empty($data[0]) || !isset($data[1]))
			{
				$fail = ($data[0] != 'title') || ($data[1] != 'description') || ($data[2] != 'keyword') || ($data[3] != 'url')  || ($data[4] != 'own domain');
				if ($fail)
					break;
			}
			else
			{
				$title 	= !empty($data[0])?trim($data[0]):'';
				$des	= !empty($data[1])?trim($data[1]):'';
				$keyword= !empty($data[2])?trim($data[2]):'';
				$url	= !empty($data[3])?strtolower(trim($data[3])):'';
				$flag	= !empty($data[4])?strtolower(trim($data[4])):'';
				$flag   = ($flag == 'yes')?1:0;
				$urlParts = parse_url($url);

				if(!empty($title) && !empty($des) && !empty($keyword) && (!empty($urlParts['host']) || !empty($urlParts['path'])))
				{
					$artarray = array('directory_title' => $title, 'directory_description' => $des, 'directory_keyword' => $keyword, 'directory_url' => $url, 'directory_flag' => $flag, 'directory_update_date' => date('Y-m-d H:i:s'));
					$id = $Directory->check_directory($keyword,$url);
				    if ($Directory->save_directory($artarray, ($id == false?0:$id)))
					{
						echo "$url : inserted<br>";
						$succ++;
					}
					else
					{
						echo "$url : error inserting<br>";
						$err++;
					}
				}
				else
				{
					echo "Row $row : have empty fields<br>";
					$artNoKey++;
				}
			}
			$row++;
			ob_flush();
			flush();		
		}
	}
	echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
	
	
	if($succ > 0)
		$sucMsg = $succ." directories inserted successfully";
	if($fail)
		$failMsg .= "Error in Headers. Check the file.";
	if($err > 0)
		$failMsg .= "<br>".$err." directories NOT inserted";
	if($artNoKey > 0)
		$failMsg .= "<br>".$artNoKey." directories have not data in all columns";
	
}
else 
{
	require_once('header.php');
	$style = empty($log)?'style="display:none;"':'';
	echo '<div id="log" '.$style.'><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br><span id="logMsg">';
	echo $log;
	echo '</span><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
}
?>


<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<div id="Msgs" <?php if(empty($sucMsg) && empty($failMsg)) echo 'style="display:none;"'; ?>>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
						<div class="content" align="center">
				        <font color="Green" id="sucMsg"><?php echo $sucMsg;?></font><br />
						<font color="Red" id="failMsg"><?php echo $failMsg;?></font>
				
						</div>
						</td>
					</tr>
				</table>
				<br />
			</div>
			<!-- *** START MAIN CONTENTS  *** -->
			
			<span class="txtHdr">Upload Directories </span>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
			<tbody>
			<tr>
				<td valign="top" colspan="2">
					
					<div id="tableData">
					<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
						<tbody><tr>
						<td align="left" class="cellHdr"><strong>Populate Directories</strong></td>
                        </tr>

						<tr class="alter1">
                            
							<td valign="top" align="left">
		                       	<form enctype="multipart/form-data" action="add_directories.php" method="POST" id="data_table" name="import">
		                       	<input type="hidden" value="upload_directories" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">

									<tr>
										<td align="left" colspan="3">Select CSV file to upload:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="center" valign="top" width="300px"><br>
											<input name="csvfile" type="file" size="60"/>
											<br>
											<font size="-2">header must contain column names: title,description,keyword,url,own domain (yes/no)</font>
                                            <br /><br/>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"><input type="submit" value="Upload Directories" name="submit" ></td>
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