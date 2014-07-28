<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
set_time_limit(0);
require_once('config.php');
require_once('header.php');
$messageSave = '';
$messageFail = '';
$messageExist = '';
$messageSucc = '';
$messageError = '';
$messageDelete = '';

$Site = ParkedDomain::getInstance($db);
$Sx25 = Site::getInstance($db);
$layoutParked = new LayoutParked();
$cloud = new Cloud();
$Profile = new Profile();
$queue  = new cssQueue($db);
$css = new cssMaker($db);

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add')
{
	$uploaddir = $config->uploadFolder;
	$origname = basename($_FILES['import_upload']['name']);
	$uploadfile = $uploaddir . $origname;
	if (move_uploaded_file($_FILES['import_upload']['tmp_name'], $uploadfile)) 
	{
		echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br>';
		$row = 1;
		$succ= 0;
		$handle = fopen($uploadfile, "r");
		$toDelete = array();
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			unset($resultarray);
			
			if($row == 1)
			{
				$row++;
				if (empty($data[0]) || (trim_value($data[0]) != 'domain')) 
				{
					$messageError .= 'Header <b>domain</b> does not exist in the file.<b>'.$data[0].'</b> found<br>';
					break;
				}
				if (empty($data[1]) || (trim_value($data[1]) != 'keyword')) 
				{
					$messageError .= 'Header <b>keyword</b> does not exist in the file.<b>'.$data[1].'</b> found<br>';
					break;
				}
				continue;
			}
			else
			{
				$domain		= isset($data[0])?trim_value($data[0]):'';
				$keyword	= isset($data[1])?trim_value($data[1]):'';
				$layoutName = isset($data[2])?trim_value($data[2]):'';
				$layout_id	= $layoutParked->check_layout_id($layoutName);
				if(!empty($domain))
				{
					MODEL::printTime("$domain : started");
					unset($domainArray);
					
					$domainArray['domain_url']			= $domain;
					$domainArray['domain_keyword']		= $keyword;
					$domainArray['domain_layout_id']	= $layout_id;
					$domainArray['domain_updatedate']	= date('Y-m-d');
					$id = $Site->check_domain_id($domain);
					if(!$id)
					{
						$thisDomainID = $Site->save_domain($domainArray);
						MODEL::printTime("$domain : inserted");
					}
					elseif (!empty($domainArray['domain_keyword']))
					{
						$thisDomainID = $Site->save_domain($domainArray,$id);
						$messageExist .= $domain.'<br />';
						MODEL::printTime("$domain : updated");
					}
					
					/* Generate the GenZ CSS */
					$layoutGenZ = substr($layoutName, -4)=='base';
					if(!empty($thisDomainID) && !empty($layoutGenZ) && !empty($layout_id)){
						//get theme for domain
						$themesArray = $queue->getThemesArray();
						$theme_id = array_rand($themesArray, 1);
						if($css->setColorTheme($domain, $theme_id, $layout_id, 'parked')){
							MODEL::printTime("$domain : created css");
						}else{
							MODEL::printTime("$domain : do not created css");
						}
					}
					
					if(!empty($thisDomainID))
					{
						$succ++;
						$sx25ID = $Sx25->check_domain_id($domain);
						if ($sx25ID)
						{
							$toDelete[$sx25ID] = $domain;
							$Sx25->markDelete($domain);
						}
						/* If domain exist in database delete old articles and images */
						if ($id || $sx25ID)
						{
							/****************** unlink articles when set up new domains in parked ************************/
							$Article = Article::getInstance($db);
							$Article->del_article_domain_keyword($domain,$domainArray['domain_keyword'],false);
							/****************** end unlink *******************************/
							if ($sx25ID)
							{
								$Image = Image::getInstance($db);
								$Image->unlink_images($sx25ID);
							}
						}
						MODEL::printTime("$domain : check sx25");
					}
					else
					{
						$messageSave .= $domain.'<br />';
					}
				}
			}
			ob_flush();
			flush();		
		}
		
		// Clean Up sx25 domains to delete
		echo "<BR> Domains created for Parked and ready to use .......................<br>";
		MODEL::printTime("<br>Dont forget to Clean Up and deleted domains. ");
		echo '<br>';
		/* Not delete it now - task to delete later
		 * 
		 * 
		foreach ($toDelete as $sx25ID => $domain)
		{
			$domainInfo = $Sx25->get_domain_info($sx25ID);
			$profile_data = $Profile->getProfileInfo($domainInfo['domain_profile_id']);
			if ($cloud->terminateSite( $profile_data['profile_ip'], $domain, 0))
			{
				MODEL::printTime("$domain : deleted form slave");
				if ($Sx25->del_domain($sx25ID,true))
				{
					MODEL::printTime("$domain : deleted form database");
					$messageDelete .= $domain.'<br />';
				}
				else
				{
					MODEL::printTime("$domain : ERROR deleting form database");
					$messageFail .= "$domain can NOT be deleted from SX25.<br>";
				}
			} 
			else 
			{
				MODEL::printTime("$domain : ERROR deleting form slave");
				$messageFail .= "$domain can NOT be deleted from the server.<br>";
			}
			ob_flush();
			flush();		
		}
		*/
		echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
		if(!empty($messageSave))
			$messageSave .= ' unable to save into database as database error.<br />';
		if(!empty($messageExist))
			$messageExist .= ' updated as existing in database.<br />';
		if(!empty($messageDelete))
			$messageDelete .= ' deleted from sx25 database.<br />';
		
		$messageSucc = $succ.' domains updated<br />';
	}
	else 
		$messageError .= 'Can NOT open the file<br />';
}

function trim_value($value) 
{ 
    $value = trim($value);
	$value = strtolower($value);
	return $value;
}

?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			<!-- *** START MAIN CONTENTS  *** -->
				<?php
				if($messageSucc != '' || $messageSave != '' || $messageFail != '' || $messageExist != '' || $messageError != '' || $messageDelete != '')
				{
				?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
						<div class="content" align="center">
				        <font color="Green"><?php echo "$messageSucc <br> $messageExist";?></font><br />
						<font color="Red"><?php echo "$messageError $messageSave $messageFail $messageDelete";?></font>
				
						</div>
						</td>
					</tr>
				</table>
				<br />
				<?php
				}
				?>

				<form action="/add_parked_domains.php" method="POST" enctype="multipart/form-data" name="ufrm">
				<input type="hidden" name="action" value="add">
				<table border="0" width="100%" cellspacing="0" cellpadding="3" id="boxGray">
					<tr>
						<td colspan="2" class="greenHdr"><b>Add Domains</b></td>
						<td class="greenHdr"></td>
					</tr>
					<tr>
						<td colspan="3"><br><br><br></td>
					</tr>
					<tr class="alter1">
						<td></td>
						<td valign="middle" align="center">	 
							Select local CSV file to upload:
						</td>
						<td></td>
					</tr>
					<tr class="alter1">
						<td></td>
						<td valign="middle" align="center">	 
							<input name="import_upload" type="file" /><br><br>
						</td>
					</tr>
				    <tr class="alter1">
						<td></td>
						<td align="center" valign="middle">
							<input type="submit" value="Add Domains">
						</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3" align="center"><br><br><font size="-2">Uploaded file must be in CSV format. Header: domain,keyword,layout name.</font><br><br></td>
					</tr>
				</table>		
				</form>		
				<!-- *** END MAIN CONTENTS  *** -->
			
					
			</td>
			<td class="brdrR">&nbsp;</td>
		</tr>
	</table>
</div>
<?php require_once('footer.php'); ?>
