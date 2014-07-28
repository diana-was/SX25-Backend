<?php
/**
 * Parked Domains
 * Author: Diana De Vargas 
 * Created: 3/11/2011
**/
require_once('config.php');
require_once('header.php');

$Site 	= ParkedDomain::getInstance($db);
$Article= Article::getInstance($db);
$Layout	= new LayoutParked();
$Themes	= new cssQueue($db);

$action	= isset($_REQUEST['action'])?$_REQUEST['action']:'';
$action_type = isset($_REQUEST['type'])?$_REQUEST['type']:'';
$sucMsg = '';
$failMsg= '';
$domainList = array();

if(($action == 'other' && $action_type == 'search') 
	  || ($action == 'list_domains') 
	  || ($action == 'other' && $action_type == 'delete_domains')
	  || ($action == 'other' && $action_type == 'delete_keep_article')
	  )
{
	$updateDomains = array();
	
	$domainArray = isset($_REQUEST['domains'])?$_REQUEST['domains']:array();
	if(count($domainArray) > 0 && ($action_type == 'delete_domains' ||$action_type == 'delete_keep_article'))
	{
		$ok = 0;
		$account_id = isset($_REQUEST['move_account_id'])?$_REQUEST['move_account_id']:0;
		$accountInfo = $Profile->getAccountInfo($account_id);
		if ($accountInfo)
		{
			$profile_move = $Profile->getProfileInfo($accountInfo['account_profile']);
		}
		foreach($domainArray as $domain_id)
		{
			if($domainInfo=$Site->get_domain_info($domain_id))
			{
				$updateDomains[] =  $domainInfo['domain_url'];
				switch ($action_type) 
				{
					case 'delete_domains':
						    if (is_origin_twin_domain($domainInfo['domain_url']))
						    {
						    	$failMsg .= $domainInfo['domain_url'].' is original domain in the twin domain table and can NOT be deleted.<br>';
						    	break;
						    }
							if ($Site->del_domain($domainInfo['domain_url']))
								$ok++;
							else
								$failMsg .= $domainInfo['domain_url'].' have error when trying to delete from database.<br>';
							break;
					case 'delete_keep_article':
						    if (is_origin_twin_domain($domainInfo['domain_url']))
						    {
						    	$failMsg .= $domainInfo['domain_url'].' is original domain in the twin domain table and can NOT be deleted.<br>';
						    	break;
						    }
							if ($Site->del_domain($domainInfo['domain_url'],true))
								$ok++;
							else
								$failMsg .= $domainInfo['domain_url'].' have error when trying to delete from database.<br>';
							break;
				}
			}
		}
		if ($ok > 0)
			$sucMsg = "$ok domains processed successfully";
		if (!empty($updateDomains))
			$domainList = $Site->get_domain_data_list('domain',$updateDomains);
	}
	
	$whereClause = '';
	if(!empty($_REQUEST['search']))
	{
		$domainList = $Site->get_domain_data_list('domain',$Site->extractTextarea($_REQUEST['search']));
	}
	elseif(!empty($_REQUEST['layout_id']))
	{
		$domainList = $Site->get_domain_data_list('layout',$_REQUEST['layout_id']);
	}
	elseif(!empty($_REQUEST['theme_id']))
	{
		$domainList = $Site->get_domain_data_list('theme',$_REQUEST['theme_id']);
	}
	elseif(!empty($_REQUEST['keyword']))
	{
		$domainList = $Site->get_domain_data_list('keyword',$_REQUEST['keyword']);
	}
}
?>
<script type="text/javascript">
function confirm_operation(){
 if ($('input[name=type]:checked').val() != 'search')
	 return confirm("Are you sure you want to delete this domains?");
 else
	 return true;
}

$(document).ready(function() {
	$(".selectall").click(function() {
		var checked_status = this.checked;
		$('input[type="checkbox"]').each(function() {
			this.checked = checked_status;
		});
	});
});
</script>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>
			<td valign="top" id="main">

				<?php if(!empty($sucMsg) || !empty($failMsg)) : ?>
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
				<form action="/parked_domains_manager.php" method="POST" name="data_table" id="data_table"  enctype="application/x-www-form-urlencoded">
				<table align="center" border="0" cellspacing="0" cellpadding="0" width="100%" class="tablePop">
					<tr>
						<td align="left" valign="top"><span class="txtHdr">Parked Domains</span></td>
						<td align="right" valign="top"></td>
					</tr>
					<tr>
						<td colspan="2" valign="top">
						<div id="tableData">
						<table width="100%" border="0" cellspacing="1" cellpadding="0" id="dt1" class="dataTable">
							<tr>
								<td align="center" class="cellHdr"><input type="checkbox" class="selectall" ></td>
								<td align="center" class="cellHdr"><a>Domain</a></td>
								<td align="center" class="cellHdr"><a>Default Keyword</a></td>
								<td align="center" class="cellHdr"><a>Layout</a></td>
								<td align="center" class="cellHdr"><a>Theme</a></td>
								<td align="center" class="cellHdr"><a>Theme Code</a></td>
								<td align="center" class="cellHdr"><a>Articles</a></td>
								<td align="center" class="cellHdr"><a>Related Keywords</a></td>
								<td align="center" class="cellHdr">Edit</td>
							</tr>
						    <?php
								$totalNum = 0;
								foreach ($domainList as $pRow) 
								{
									$articleNum = $Article->count_articles($pRow['domain_url']);
									$related = array(); 
									for ($i = 1; $i <= 6; $i++)
									{
										if (!empty($pRow["keyword_related$i"]))
											$related[] = trim($pRow["keyword_related$i"]);
									}
									$related = implode('<br>',$related);
							?>
								    
							<tr class="alter1">
							    <td  width="30" align="center" valign="middle" ><input type="checkbox" name="domains[]" value="<?php echo $pRow['domain_id'];?>"></td>
								<td align="left" valign="middle" ><a href="http://<?php echo $pRow['domain_url'];?>" target="_blank"><?php echo $pRow['domain_url'];?></a></td>
							    <td align="left" valign="middle" ><a><?php echo $pRow['keyword'];?></a></td>
							    <td align="left" valign="middle" ><a><?php echo $pRow['layout_name'];?></a></td>
							    <td align="left" valign="middle" ><a><?php echo $pRow['theme_name'];?></a></td>
							    <td align="left" valign="middle" ><a><?php echo $pRow['theme_code'];?></a></td>
							    <td align="center" valign="middle" ><a><?php echo $articleNum;?></a></td>
							    <td align="left" valign="middle" ><a><?php echo $related;?></a></td>
							    <td align="center" valign="middle" ><a href="/edit_parked_template.php?domain=<?php echo $pRow['domain_url'];?>" target="_blank">Edit template</a></td>
							</tr>
							<?php }	?>
						</table>
						</div>

						<br>
				        <input type="hidden" name="account_id" value="<?php echo @$_REQUEST['account_id'];?>" />
				        <input type="hidden" name="search" value="<?php echo @$_REQUEST['search'];?>" />
						<table border="0" cellspacing="0" cellpadding="0" align="center">
							<tr>
								<td align="left">
								<div style="float:left"><input type="radio" name="type" value="search"> Search for domain: </div><textarea style="float:left" type="text" cols="30" rows="3" name="search" onclick="document.forms['data_table'].elements['type'][0].checked = true;"></textarea>
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][1].checked = true;">
								<input type="radio" name="type" value="search">Search for domains using Layout:
			                    <select name="layout_id">
			                    <option value="">Select</option>
								<?php
								$layouts = $Layout->get_layouts();
								foreach ($layouts as $pRow) 
								{
									echo '<option value="'.$pRow['layout_id'].'">'.$pRow['layout_name'].'</option>';
								}
								?>
								</select>
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][2].checked = true;">
								<input type="radio" name="type" value="search">Search for domains using theme:
			                    <select name="theme_id">
			                    <option value="">Select</option>
								<?php
								$themes = $Themes->getApproveThemesArray();
								foreach ($themes as $pRow) 
								{
									echo '<option value="'.$pRow['id'].'">'.$pRow['theme_name'].'</option>';
								}
								?>
								</select>
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][3].checked = true;">
								<input type="radio" name="type" value="search">Search for domains using keyword:
			                    <input type="text" name="keyword" size="40" >
								</td>
							</tr>
						    <?php if ($user->userLevel >= 4) : ?>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][4].checked = true;">
								<input type="radio" name="type" value="delete_domains"> Delete selected domains
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][5].checked = true;">
								<input type="radio" name="type" value="delete_keep_article"> Delete domains but keep domain's articles link
								</td>
							</tr>
							<?php endif; ?>
							<tr>
								<td align="center">
									<br>
						
									<input type="submit" value="Perform Selected Operation" onclick="return confirm_operation();">
								</td>
							</tr>
						</table>
						<input type="hidden" value="other" name="action">
						</td>
					</tr>
				</table>		
				</form>
				<!-- *** END MAIN CONTENTS  *** -->
			
			</td>
			<td rowspan="5" class="brdrR">&nbsp;</td>
		</tr>
	</table>
</div>
<?php	
require_once('footer.php');
?>