<?php
/**
 * CleanUp
 * Author: Diana Devargas 
 * created: 2011-11-25
**/
set_time_limit(0);
require_once('config.php');
require_once('header.php');
$messageFail = '';
$messageExist = '';
$messageSucc = '';
$messageError = '';
$messageDelete = '';

$Sx25 = Site::getInstance($db);
$parkDomain = ParkedDomain::getInstance($db);
if(!empty($_REQUEST['action']))
{
	echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br>';
	switch ($_REQUEST['action']) 
	{
		case 'deleteCSV': 	
							$file = File::getInstance();
							if ($file->deleteDirectory($config->uploadFolder,true,true))
								$messageSucc = 'Files deleted from "upload/" directory<br />';
							else
								$messageDelete .= 'Error deleting files in "upload/" directory <br />';
							break;
		case 'deleteDomains': 
							$cloud = new Cloud();
							$Profile = new Profile();
							$toDelete = $Sx25->get_domain_data_list('status',3);
							
							// Clean Up sx25 domains to delete
							echo "<BR> Domains delete started .......................<br>";
							echo '<br>';
							$succ= 0;
							foreach ($toDelete as $domainInfo)
							{
								$sx25ID = $domainInfo['domain_id'];
								$domain = $domainInfo['domain_url'];
								$profile_data = $Profile->getProfileInfo($domainInfo['profile_id']);
								if ($cloud->terminateSite( $profile_data['profile_ip'], $domain, 0))
								{
									MODEL::printTime("$domain : deleted form slave");
									if ($Sx25->del_domain($sx25ID,false,false))
									{
										MODEL::printTime("$domain : deleted form database");
										$messageDelete .= $domain.'<br />';
										$succ++;
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
							echo "<BR> Domains delete END<br>";
							$messageSucc = $succ.' domains deleted<br />';
							break;
		case 'releaseParkedDomains': 
							$toDelete = $parkDomain->get_domain_data_list('status',2);
							
							// Clean Up parked domains to delete
							echo "<BR> Parked domains delete started .......................<br>";
							echo '<br>';
							$succ= 0;
							foreach ($toDelete as $parkDomainInfo)
							{
								$parkDomainURL = $parkDomainInfo['domain_url'];
								if ($parkDomain->del_domain($parkDomainURL,false))
								{
									MODEL::printTime("$parkDomainURL : deleted form database");
									$messageDelete .= $parkDomainURL.'<br />';
									$succ++;
								}
								else
								{
									MODEL::printTime("$parkDomainURL : ERROR deleting form database");
									$messageFail .= "$parkDomainURL can NOT be deleted from SX25.<br>";
								}
								ob_flush();
								flush();		
							}
							echo "<BR> Parked domains delete from database END<br>";
							$messageSucc = $succ.' parked domains deleted<br />';
							break;
		case 'releaseDomains':
							$toDelete = $Sx25->get_domain_data_list('status',2);
								
							// Clean Up sx25 domains to delete
							echo "<BR> Domains delete started .......................<br>";
							echo '<br>';
							$succ= 0;
							foreach ($toDelete as $domainInfo)
							{
								$sx25ID = $domainInfo['domain_id'];
								$domain = $domainInfo['domain_url'];
								if ($Sx25->del_domain($sx25ID,false,true))
								{
									MODEL::printTime("$domain : deleted form database");
									$messageDelete .= $domain.'<br />';
									$succ++;
								}
								else
								{
									MODEL::printTime("$domain : ERROR deleting form database");
									$messageFail .= "$domain can NOT be deleted from SX25.<br>";
								}
								ob_flush();
								flush();		
							}
							echo "<BR> Domains delete from database END<br>";
							$messageSucc = $succ.' domains deleted<br />';
							break;
		case 'cleanArticles':
							$obj = Article::getInstance($db);
							while(($u = $obj->clean_articles()) > 0)
							{
								echo "Updated : $u";
							}
							break;
			
	}
		
	if(!empty($messageExist))
		$messageExist .= ' updated as existing in database.<br />';
	if(!empty($messageDelete))
		$messageDelete .= ' deleted from sx25 database.<br />';
	
	echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
}

function trim_value($value) 
{ 
    $value = trim($value);
	$value = strtolower($value);
	return $value;
}

?>
<script>

function goTo(report){
	location.href= "extract_report.php?action=report&report="+report;	
}

</script>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			<!-- *** START MAIN CONTENTS  *** -->
				<?php
				if($messageSucc != '' || $messageFail != '' || $messageExist != '' || $messageError != '' || $messageDelete != '')
				{
				?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
						<div class="content" align="center">
				        <font color="Green"><?php echo "$messageSucc <br> $messageExist";?></font><br />
						<font color="Red"><?php echo "$messageError $messageFail $messageDelete";?></font>
				
						</div>
						</td>
					</tr>
				</table>
				<br />
				<?php
				}
				?>

				<form action="/cleanup.php" method="POST" enctype="multipart/form-data" name="ufrm">
				<input type="hidden" name="action" id="action" value="">
				<table border="0" width="100%" cellspacing="0" cellpadding="3" id="boxGray">
					<tr>
						<th class="greenHdr"><b>Clean Up</b></td>
						<th colspan="4" class="greenHdr"></td>
					</tr>
					<tr>
						<td colspan="5"><br><br><br></td>
					</tr>
					<tr>
						<td colspan="5"><br><br><br></td>
					</tr>
				    <tr class="alter1">
						<td align="center" valign="middle">
							<input class="button" type="submit" value="Delete Working CSVs" onclick="$('#action').val('deleteCSV')">
						</td>
						<td align="center" valign="middle">
							<input class="button" type='button'  value="Get Domains to Delete from DB"  onclick='goTo("release_domains");'>
						</td>
						<td align="center" valign="middle">
							<input class="button" type="submit" value="Delete Marked Domains from DB" onclick="$('#action').val('releaseDomains')">
						</td>
						<td align="center" valign="middle">
							<input class="button" type='button'  value="Get Domains to Delete from DB and Server"  onclick='goTo("delete_domains");'>
						</td>
						<td align="center" valign="middle">
							<input class="button" type="submit" value="Remove Marked Domains DB and Server" onclick="$('#action').val('deleteDomains')">
						</td>
					</tr>
					<tr class="center">
						<td align="center" valign="middle">
							<input class="button" type="submit" value="Clean articles scripts" onclick="$('#action').val('cleanArticles')">
						</td>
						<td align="center" valign="middle">
							<input class="button" type='button'  value="Get Parked Domains to Delete"  onclick='goTo("remove_parked_domains");'>
						</td>
						<td align="center" valign="middle">
							<input class="button" type="submit" value="Delete Marked Parked Domains from DB" onclick="$('#action').val('releaseParkedDomains')">
						</td>
						<td align="center" valign="middle" colspan="2">
						</td>
					</tr>
					<tr>
						<td colspan="5"><br><br><br></td>
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
