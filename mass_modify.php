<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once('config.php');
require_once('header.php');

$profile = new Profile();
$Layout  = new Layout();
$messageSucc = '';
$messageError = '';

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'import')
{
	$uploaddir = $config->uploadFolder;
	$origname = basename($_FILES['import_upload']['name']);
	$uploadfile = $uploaddir . $origname;
	$Layout = new Layout();
	$Site = Site::getInstance($db);
	if (move_uploaded_file($_FILES['import_upload']['tmp_name'], $uploadfile)) 
	{
		$row = 1;
		$succ= 0;
		$handle = fopen($uploadfile, "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			unset($resultarray);
			
			if($row == 1)
			{
				$col = 0;
				while(isset($data[$col]) && $data[$col] != '')
				{
					$col_name[$col] = $data[$col];
					$col++;
				}
			}
			else
			{
				unset($domainData);
				
				foreach($col_name as $col_number => $col_field)
				{
					if($col_field == 'layout_name')
					{
						$layoutID = $Layout->check_layout_id($data[$col_number]);
						$layoutID = (!empty($layoutID) && ($layoutID > 0))?$layoutID:0;
						$data[$col_number] = $layoutID;
						$domainData['domain_layout_id'] = $layoutID;
						$domainData['status'] = 1;
					}
					else
						$domainData[$col_field] = $data[$col_number];
				}
				$domainData['domain_url'] = strtolower(trim($domainData['domain_url']));
				if ($domainData['domain_layout_id'] > 0)
				{
					$domainInfo = $Site->get_domain_info_name($domainData['domain_url']);
					if ($domainInfo)
					{
						$domainID = $domainInfo['domain_id']; 
						$original_keyword = trim($domainInfo['domain_keyword']);
						$keywordChanged = (strtolower($original_keyword)==strtolower($domainData['domain_keyword']))?'':strtolower($domainData['domain_keyword']);
						if ($Site->save_domain($domainData, $domainID))
						{
							$succ++;
							/****************** remove old articles and images ********************/
							if(!empty($keywordChanged))
								cleanUpDomain($domainID, $original_keyword);
						}
						else 
							$messageError .= $domainData['domain_url']." error saving data";
					}
					else
					{ 
						$messageError .= $domainData['domain_url']." doesn't exist,";
					}
					
				}
				else
					$messageError .= $domainData['domain_url']." template don't exist,";
			}
			$row++;
		}
		fclose($handle);
		$messageSucc = $succ.' domains updated';
		if (strlen($messageError) > 0)
			$messageError .= ' could not be updated';
	}
}

?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<span class="txtHdr">Mass Modify</span>
			<p>Use the mass modify feature to export or import domain details for your domains to perform mass modifications or for archival purposes.</p>
			
			<!-- *** START MAIN CONTENTS  *** -->
			<?php if($messageSucc != '' || $messageError != '') : ?>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
				<tr>
					<td valign="top"><div class="blueHdr">System Message</div>
					<div class="content" align="center">
				        <font color="Green"><?php echo $messageSucc;?></font><br />
						<font color="Red"><?php echo $messageError;?></font>
					</div>
					</td>
				</tr>
			</table>
			<?php endif; ?>			
			
			<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
		<tbody>
			<tr>
				<td valign="top" align="left"><span class="txtHdr">Export Domain Settings</span></td>	
			</tr>

			<tr>
				<td valign="top" colspan="2">
				<script src="/js/mass.js" language="javascript"></script>

					<form action="/mass_modify_action.php" method="POST" name="export">
					<input type="hidden" value="export" name="action"/>
					<div id="tableData">
					<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
						<tbody><tr>
						<td align="left" colspan="2" class="cellHdr"><strong>Export Options</strong></td></tr>

						<tr class="alter1">
							<td valign="top" align="center" width="50%">	 
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td align="left" colspan="3">Select the data fields to be exported for each domain:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="left" valign="top" width="200px"><br>

											<select multiple="multiple" size="9" name="fields[]" style="width: 200px;" id='fields' onchange='fieldSelected()'>
												<option selected="selected" value="domain_url">Domain</option>
												<option selected="selected" value="domain_title">Domain Title</option>
												<option selected="selected" value="domain_keyword">Keyword</option>
												<option selected="selected" value="layout_name">Custom Layout</option>
												<option selected="selected" value="domain_feedtype">Feed Type</option>
												<option selected="selected" value="domain_feedid">Feed ID</option>
												<option selected="selected" value="domain_product_category">Product Category</option>
											</select><br>

											<font size="-2">Minimum of one selection required, not including Domain field.</font>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
								</table>
							</td>
							<td valign="top" align="left">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">

									<tr>
										<td align="left">Select the domains to be exported:</td>
									</tr>
									<tr>
									<td valign="top" align="center">

									<table border="0" cellpadding="0" cellspacing="0" width="50%">	
										<tr>
											<td>

												<input type="radio" name="group_type" value="all" CHECKED>
											</td>
											<td align="left" nowrap width="99%">
												<div onclick="document.forms['export'].elements['group_type'][0].checked = true;">
											All Domains
												</div>
											</td>

										</tr>
										<tr>
											<td>
												<input type="radio" name="group_type" value="profile" >
											</td>
											<td align="left" nowrap>
												<div onclick="document.forms['export'].elements['group_type'][1].checked = true;">Profile 
											    <select name="profile_id" id="profile_id" onChange="initCs();">
	                                            	<option value="">Please Select</option>
		                                            <?php $list = $profile->getProfileList();
														foreach ($list as $pRow) 
														{
															echo '<option value="'.$pRow['profile_id'].'">'.$pRow['profile_name'].'</option>';
														}
													?>
												</select>
												<select name="account_id" id="account_id"></select>
												</div>
											</td>
										</tr>
										<tr>
											<td>
												<input type="radio" name="group_type" value="layout" >
											</td>
											<td>
												<div onclick="document.forms['export'].elements['group_type'][2].checked = true;">Layout
											    <select name="layout_id" id="layout_id" >
								                    <option value="">Select</option>
													<?php
													$layouts = $Layout->get_layouts();
													foreach ($layouts as $pRow) 
													{
														echo '<option value="'.$pRow['layout_id'].'">'.$pRow['layout_name'].'</option>';
													}
													?>
												</select>
												</div>
											</td>										
										</tr>
										<tr>
											<td>
												<input type="radio" name="group_type" value="domains" >
											</td>
											<td align="left">
												<div onclick="document.forms['export'].elements['group_type'][3].checked = true;">

											Domain List
												</div>
											</td>
										</tr>
										<tr>
											<td>&nbsp;
												
											</td>
											<td align="left">

					<textarea cols="30" rows="9" name="domains" onclick="document.forms['export'].elements['group_type'][2].checked = true;" style="width: 250px;"></textarea>
											</td>
										</tr>
									</table>
									</td></tr>
								</table>
							</td>
						</tr>

						<tr align="center" class="alter2">
							<td colspan="2"><input type="submit" value="Export Settings" name="submit"></td>
						</tr>
						</tbody></table>
					</div></form>

					<br/><br/>

					<form enctype="multipart/form-data" action="/mass_modify.php" method="POST" id="data_table" name="import">
		<span class="txtHdr">Import Domain Settings</span>

		<div id="tableData">
		<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
			<tbody><tr>
			<td align="left" class="cellHdr"><strong>Import File</strong></td></tr><tr class="alter1"><td valign="middle" align="center">	 
				<input type="hidden" value="import" name="action"/><br>
				     Select local CSV file to upload: <input name="import_upload" type="file" />
				    <br><br>

				</td></tr>
				<tr class="alter2"><td align="center"><input type="submit" value="Import Settings" name="submit"/></td></tr>
			</tbody></table>
		</div>

		<font size="-2">Uploaded file must be in CSV file format and include a header containing column (field) names.</font>
		</form>

		<br><br><br><br>

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

function cleanUpDomain($domain_id, $keyword)
{
	global $db;
	$Image = Image::getInstance($db);
	$Article = Article::getInstance($db);
	$Image->unlink_images_keyword($domain_id, $keyword);
	$Article->del_article_domain_keyword($domain_id, $keyword);
}
?>