<?php
/**
 * VC Tracking system (the system will accept a csv that contains Domain-mappingkeyword-tracking, then it will put these info into the domain corespondingly)
 * Author: Gordon Ye 10/01/2013
**/
require_once('config.php');

$Site = Site::getInstance($db); 

$domainExist = 0;
$row = 0;
$succ = 0;
$sucMsg = '';
$failMsg = '';

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'keyword_tracking_import')
{
	//echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br><h2>Creating twin domains</h2><br>';
	$uploaddir = $config->uploadFolder;
	$uploadfile = $uploaddir . basename($_FILES['csvfile']['name']);

	if (move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploadfile)) 
	{
		$r = 0;
		$handle = fopen($uploadfile, "r");
		$kfArray = array();
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			$r++;
			if($r == 1 || empty($data) || empty($data[0]))
			{
				continue; //skip first line and empty lines
			}
			else
			{
				$domain	= strtolower(trim($data[0]));
				$domain_id = $Site->check_domain_id($domain);				
				if($domain_id)
				{
					unset($data[0]);
					if(!array_key_exists($domain_id, $kfArray)){
						$kfArray[$domain_id] = $data[1].', '.$data[2];					
					}
					else
						$kfArray[$domain_id] .= '||'. $data[1].', '.$data[2];
				}
				else 
					echo " : Domain is invalid! <br>";
			}
		}
		if(!empty($kfArray))
		{
			foreach($kfArray as $k=>$v)
				$Site->save_domain(array('keywords_feeds' => $Site->line_to_format($v)), $k);   //print_r(array('keywords_feeds', $v));
		}
	}
	else 
		$failMsg .= "Error Reading file.<br>";
}

else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'keyword_tracking_export')
{
	$report = Report::getInstance($db);
	$output = $report->getReport('sx25_keyword_tracking');
	$outputfilename = $action."_".date("d-m-Y").".csv";
	
	header('Content-Type: text/plain; charset=ISO-8859-1');
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"$outputfilename\"");
	
	echo $output;
	exit;
}
require_once('header.php');
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
			
			<span class="txtHdr">Keywords Tracking</span>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop" id="dt1">
			<tbody>
			<tr>
				<td valign="top" width="50%">
					<div id="tableData">
					<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
						<tbody>
						<tr>
							<td width="50%" align="left" class="cellHdr"><strong>Import tracking list</strong></td>
							<td width="50%" align="left" class="cellHdr"><strong>Export tracking list</strong></td>
                        </tr>

						<tr class="alter1">
							<td valign="top" align="center" width="50%">	 
			                    <form enctype="multipart/form-data" action="keyword_tracking.php" method="POST" name="import" id="import">
								<input type="hidden" value="keyword_tracking_import" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td align="left" colspan="3">Select CSV file to upload:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="center" valign="top" width="300px"><br>
											<input name="csvfile" type="file" />
											<br>
											<font size="-2">header must contain column name: Domain, Keyword, Tracking</font>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"><br><input type="submit" value="Upload Tracking" name="submit"></td>
									</tr>
									<tr>
										<td align="left" colspan="3"><br><font size="-2">Upload TAGs for feed tracking (the system will accept a csv that contains Domain, Keyword, Tracking, and save this info into the domain).</font></td>
									</tr>
                                   
								</table>
		                       </form>
							</td>
							<td valign="top" align="center" width="50%"  height="120px">	 
			                   <a href="keyword_tracking.php?action=keyword_tracking_export"><button  style="margin-top:35px;">Export Tracking List</button></a>
							</td>
						</tr>
						</tbody>
					</table>
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