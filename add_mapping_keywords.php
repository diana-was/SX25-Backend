<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once('config.php');
require_once('header.php');

$artExist = 0;
$artNoKey = 0;
$domainExist = 0;
$succ = 0;
$fail = 0;
$sucMsg = '';
$failMsg = '';
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_mapping_keyword')
{
	$uploaddir = $config->uploadFolder;
	$uploadfile = $uploaddir . basename($_FILES['csvfile']['name']);

	if (move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploadfile)) 
	{
		$MK = MappingKeyword::getInstance($db);
		$Site = Site::getInstance($db);
		$row = 1;
		$count = 0;
		$handle = fopen($uploadfile, "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			$domain_url = strtolower(trim($data['0']));
			$original_keyword = strtolower(trim(@$data['1']));
			$mapping_keyword = strtolower(trim(@$data['2']));
			if($row == 1)
			{
				if($domain_url!='domain_url' || $original_keyword!='original_keyword' || $mapping_keyword!='mapping_keyword')
				{
					$failMsg = "<p style=\"color:red;\">CSV file has wrong field format, please be sure it has following fields: domain_url, original_keyword and mapping_keyword.</p>";
					break;
				}
			}
			else
			{				
				$domainInfo = $Site->get_domain_info_name($domain_url);	
				if ($domainInfo)
				{
					$domain_id = $domainInfo['domain_id'];
					$array = array('mapping_keyword_original'=>$original_keyword, 'mapping_keyword_mapping'=>$mapping_keyword, 'domain_id'=>$domain_id);
					if ($id = $MK->check_mapping_id($domain_id, $original_keyword))
						$saving = $MK->saveMappingKeyword($array,$id);
					else
						$saving = $MK->saveMappingKeyword($array);
					if($saving)
						$count++;
					else 
						$failMsg .= "Error mapping $original_keyword to $mapping_keyword in $domain_url<br>";
				}
				else 
					$failMsg .= "Domain does NOT exist $domain_url<br>";
			}
			$row++;
		}
		$sucMsg = "<p>".$count." mapping_keywords have been updated. </p>";	
	}
}
?>

<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<?php
if($sucMsg != '' || $failMsg != '')
{
?>
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
<?php
}
?>
			<!-- *** START MAIN CONTENTS  *** -->
			
			<span class="txtHdr">Add Mapping Keywords</span><p>
</p><table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
		<tbody>


			<tr>
				<td valign="top" colspan="2">
					
					<div id="tableData">
					<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
						<tbody><tr>
						<td align="left" class="cellHdr"><strong>Add Mapping Keywords</strong></td>

                        </tr>

						<tr class="alter1">
							<td valign="top" align="left">
		                        <form enctype="multipart/form-data" action="" method="POST" id="data_table" name="import">
		                       	<input type="hidden" value="add_mapping_keyword" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">

									<tr>
										<td align="left" colspan="3">Select CSV file to upload:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="center" width="300px">
											<input name="csvfile" type="file" />
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="center" valign="top" width="300px"><br>
											<font size="-2">CSV file must has following fields: domain_url, original_keyword and mapping_keyword.</font>
											<br>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"> <input type="submit" value="Add Mapping Keywords" name="submit"></td>
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