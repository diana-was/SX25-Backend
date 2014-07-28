<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
set_time_limit(0);
require_once('config.php');
require_once('header.php');
$messageSave = '';
$messageAcc = '';
$messageExist = '';
$messageSucc = '';
$failMsg = '';
$Profile = new Profile();
$Article = Article::getInstance($db);
$Parked = ParkedDomain::getInstance($db);

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add')
{
	$domains = str_replace("", ",", $_REQUEST['domains']);
	$domains = str_replace("\n", ",",$domains);
	$domains = str_replace("\t", ",",$domains);
	$domains = str_replace(",,", ",", $domains);
	$domains = explode(",", $domains);
	$domains = array_unique($domains);
	array_walk($domains, 'trim_value');
	
	$profile_id = isset($_REQUEST['profile_id'])?$_REQUEST['profile_id']:'';
	$account_id = isset($_REQUEST['account_id'])?$_REQUEST['account_id']:'';
	$account = isset($_REQUEST['account'])?$_REQUEST['account']:'';
	$feed_type = isset($_REQUEST['feed_type'])?$_REQUEST['feed_type']:'';
	
	$succ = 0;
	$ok = 0;
	if(!empty($profile_id) && (!empty($account_id) || !empty($account)))
	{
		echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br>';
		$Site = Site::getInstance($db);
		$Profile = new Profile();
		
		if(empty($account_id) || $account != '')
		{
			$accSave['account_name'] = $account;
			$accSave['account_profile'] = $profile_id;
			$account_id = $Profile->save_account($accSave);
			$error = ($account_id > 0);
		}
		else 
		{
			$accountInfo = $Profile->getAccountInfo($account_id);
			$error = ($profile_id != $accountInfo['account_profile']);
		}
	
		if (!$error)
		{
			$profile_data = $Profile->getProfileInfo($profile_id);
			$cloud = new Cloud();
			
			foreach($domains as $key => $val)
			{
				$val = strtolower(trim($val));
				if(!empty($val))
				{
					$domain_id = $Site->check_domain_id($val);
					if(empty($domain_id))
					{
						$username = '';
						MODEL::printTime("$val : started");
						if ($username = $cloud->createNewSite( $profile_data['profile_ip'], $val, $universalpass))
						{
							$domainArray['domain_url'] = $val;
							$domainArray['domain_profile_id'] = $profile_id;
							$domainArray['domain_account_id'] = $account_id;
							$domainArray['domain_ftp_user'] = $username;
							$domainArray['domain_ftp_pass'] = $universalpass;
							$domainArray['domain_layout_id'] = $defaultlayout;
							$domainArray['domain_feedtype'] = $feed_type;
							$domainArray['domain_createdate'] = date('Y-m-d');
							$domainArray['domain_updatedate'] = date('Y-m-d');
		
							$thisDomainID = $Site->save_domain($domainArray);
							
							if($thisDomainID && !empty($thisDomainID))
							{
								MODEL::printTime("$val : created ");								
								$Article->link_article_domainID($val,$thisDomainID);
								$parkedID = $Parked->check_domain_id($val);
								if ($parkedID) 
								{
									$Parked->del_domain($val,true);
									MODEL::printTime("$val : deleted form parked table");
								}
								$succ++;
							}
							else
							{
								MODEL::printTime("$val : unable to save");								
								$messageSave .= $val.'<br />';
							}
						}
						else
						{
							MODEL::printTime("$val : unable to save");								
							$messageAcc .= $val.'<br />';
						}
					}
					else
					{
						// move domains to account
						$domainInfo=$Site->get_domain_info($domain_id);
						if ($domainInfo['domain_profile_id'] == $profile_id)
						{
							if ($Site->save_domain(array('domain_account_id' => $account_id, 'status' => 1),$domain_id))
							{
								$ok++;
								$messageExist .= $val.'<br />';
								MODEL::printTime("$val : updated ");
							}
							else
							{
								$failMsg .= 'Error updating '.$domainInfo['domain_url'].'.<br>';
								MODEL::printTime("$val : FAIL updating ");
							}
						}
						else
						{
							// Remove the domain from the system in the old profile
							$old_profile_data = $Profile->getProfileInfo($domainInfo['domain_profile_id']);
							if ($cloud->terminateSite( $old_profile_data['profile_ip'], $domainInfo['domain_url'], 0))
							{
								MODEL::printTime("$val : deleted from slave");								
								// add the domain in the new profile
								if ($username = $cloud->createNewSite( $profile_data['profile_ip'], $domainInfo['domain_url'], $universalpass))
								{
									MODEL::printTime("$val : created in slave");								
									$domainUpdate['domain_profile_id'] 	= $profile_id;
									$domainUpdate['domain_account_id'] 	= $account_id;
									$domainUpdate['domain_ftp_user']	= $username;
									$domainUpdate['domain_ftp_pass'] 	= $universalpass;
									$domainUpdate['status'] 			= 1;
									if ($Site->save_domain($domainUpdate,$domain_id))
									{
										$ok++;
										$messageExist .= $val.'<br />';
										MODEL::printTime("$val : updated ");
									}
									else
									{
										$failMsg .= 'Error updating '.$domainInfo['domain_url'].'.<br>';
										MODEL::printTime("$val : FAIL updating ");
									}
								}
								else
								{
									$failMsg .= $domainInfo['domain_url'].' can NOT be added to the new profile.<br>';
									MODEL::printTime("$val : FAIL creating in slave");
								}
							} 
							else
							{
								$failMsg .= $domainInfo['domain_url'].' can NOT be removed from the old profile.<br>';
								MODEL::printTime("$val : FAIL deleting from slave");
							}
						}
					}
				
				}
				ob_flush();
				flush();		
			}
			if($messageSave != '')
				$messageSave .= ' unable saved into database as database error.<br />';
			if($messageAcc != '')
				$messageAcc .= ' unable to created as existing in server already.<br /> ';
			$messageExist = $ok.' domains moved.<br />'.$messageExist;
		}
		else 
			$messageAcc .= ' The profile from the selected Account is different than the selected profile.<br /> ';
		echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
	}
	else 
		$messageAcc .= ' Profile and Account data are required.<br /> ';
		
	$messageSucc = $succ.' domains added';
}

function trim_value(&$value) 
{ 
    $value = trim($value);
	$value = strtolower($value);
}

?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			<!-- *** START MAIN CONTENTS  *** -->
				<?php
				if($messageSucc != '' || $messageSave != '' || $messageAcc != '' || $messageExist != '' || $failMsg != '')
				{
				?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
						<div class="content" align="center">
				        <font color="Green"><?php echo $messageSucc;?></font><br />
				        <font color="Green"><?php echo $messageExist;?></font><br />
						<font color="Red"><?php echo $messageSave.'<br>'.$messageAcc.'<br>'.$failMsg;?></font>
						</div>
						</td>
					</tr>
				</table>
				<br />
				<?php
				}
				?>

				<form action="/add_domains.php" method="POST" enctype="multipart/form-data" name="ufrm">
				<input type="hidden" name="action" value="add">
				<table border="0" width="100%" cellspacing="0" cellpadding="3" id="boxGray">
					<tr>
						<td colspan="2" class="greenHdr"><b>Add Domains</b></td>
					</tr>
					<tr>
						<td width="50%" rowspan="2" align="center" valign="top">
							<b>1) Paste a list of one or more domains in the text box below</b><br /><br />			
							<textarea cols="40" rows="10" name="domains"></textarea>
						</td>
				
					    <td align="center" valign="top"><b>2) Select a profile the domains belong to</b><br />
						 	Belong to profile: 
						 	<select name="profile_id" id="profile_id" onChange="initCs();">
							 	<?php $pResults = $Profile->getProfileList(); ?>
						        <option value="">Please Select</option>
								<?php 
								foreach ($pResults as $pRow) 
								{
									echo '<option value="'.$pRow['profile_id'].'">'.$pRow['profile_name'].'</option>';
								} ?>
				            </select>
							<br /><br />
				            <b>2) Select an account to add the domains to </b><br>
						 	Add to account: <select name="account_id" id="account_id" onChange="document.forms['ufrm'].elements['account'].value = '';">
				              
				            </select>
				            <br>
				            <b>- or -</b><br>
						 	Create new account: 
						 	<input type="text" name="account" id="account" onkeyup="document.forms['ufrm'].elements['account_id'].value = '';">
							<br /><br />
				            <b>3) Select a feed type for the domains </b>
				            <br>
				            Use Feed: 
				            <select name="feed_type">
				                <option value='TZ'>TrafficZ</option>
				                <option value='TZ-2'>TrafficZ 2 - Clean Referer</option>
                                <option value='TS'>TrafficScoring</option>
                                <option value='TS-2'>TrafficScoring 2 - Clean Referer</option> 
				                <option value='VC'>ValidClick</option>
				                <option value='OB'>OB Media</option>
				                <option value='IS'>InfoSpace</option>
				            </select>
				            <Br />(you can assign feed ids later for VC or OB)
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center" valign="middle">
							<b>4) And... </b>
							<input type="submit" value="Add Domains">
						</td>
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
