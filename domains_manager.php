<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once('config.php');
require_once('header.php');

$Profile= new Profile();
$Article= Article::getInstance($db);
$Themes	= new cssQueue($db);
$Layout	= new Layout();

$action	= isset($_REQUEST['action'])?$_REQUEST['action']:'';
$action_type = isset($_REQUEST['type'])?$_REQUEST['type']:'';
$sucMsg = '';
$failMsg= '';

if($action == 'edit_account')
{
	$account_id = $_REQUEST['account_id'];
	$accountInfo = $Profile->getAccountInfo($account_id);
	$account_name = $accountInfo['account_name'];
?>
	<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>
			<td valign="top" id="main">

			<!-- *** START MAIN CONTENTS  *** -->
			<table border="0" width="100%" cellspacing="0" cellpadding="3" id="boxGray">
			<tr>
				<td colspan="2" class="greenHdr"><b>Modify Account Name</b></td>
			</tr>
			<tr>
				<td align="center">
		
					<form action="domains_manager.php" method="POST">
					<input type="hidden" name="action" value="modify_account">
					<input type="hidden" name="account_id" value="<?php echo $account_id;?>">
					Account Name: <input type="text" name="account_name" value="<?php echo $account_name;?>" <?php if($account_id == '1') echo 'disabled';?>><br>
					<input type="submit" value="Modify">
					</form>
				</td>
			</tr>
			</table>		
			
			<!-- *** END MAIN CONTENTS  *** -->
					
			</td>
			<td rowspan="5" class="brdrR">&nbsp;</td>
		</tr>
	</table>
	</div>
<?php
}
elseif(($action == 'other' && $action_type == 'search') 
	  || ($action == 'list_domains') 
	  || ($action == 'other' && $action_type == 'delete_domains')
	  || ($action == 'other' && $action_type == 'clean_database')
	  || ($action == 'other' && $action_type == 'move_domains_account')
	  || ($action == 'other' && $action_type == 'move_domains_parked')
	  || ($action == 'other' && $action_type == 'create_domains_inserver')
	  || ($action == 'other' && $action_type == 'delete_domains_inserver')
	  || ($action == 'other' && $action_type == 'search_module_missing')
	  )
{
	$Site = Site::getInstance($db);
	$cloud = new Cloud();
	$domainList = array();
	$updateDomains = array();
	
	$domainArray = isset($_REQUEST['domains'])?$_REQUEST['domains']:array();
	if(count($domainArray) > 0 && ($action_type == 'delete_domains' ||$action_type == 'clean_database' || 'move_domains_parked' || $action_type == 'move_domains_account'))
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
							$profile_data = $Profile->getProfileInfo($domainInfo['domain_profile_id']);
							if ($cloud->terminateSite( $profile_data['profile_ip'], $domainInfo['domain_url'], 0))
							{
								if ($Site->del_domain($domain_id))
									$ok++;
								else
									$failMsg .= $domainInfo['domain_url'].' can NOT be deleted from database.<br>';
							} 
							else 
								$failMsg .= $domainInfo['domain_url'].' can NOT be deleted from the system.<br>';
							break;
					case 'clean_database':
							if ($Site->del_domain($domain_id))
								$ok++;
							else
								$failMsg .= $domainInfo['domain_url'].' can NOT be deleted from database.<br>';
							break;
					case 'move_domains_parked':
							if ($Site->del_domain($domain_id,true))
								$ok++;
							else
								$failMsg .= $domainInfo['domain_url'].' can NOT be deleted from database.<br>';
							break;
					case 'move_domains_account':
							if ($accountInfo)
							{
								if ($domainInfo['domain_profile_id'] == $accountInfo['account_profile'])
								{
									if ($Site->save_domain(array('domain_account_id' => $account_id),$domain_id))
										$ok++;
									else
										$failMsg .= 'Error updating '.$domainInfo['domain_url'].'.<br>';
								}
								else
								{
									// Remove the domain from the system in the old profile
									$profile_data = $Profile->getProfileInfo($domainInfo['domain_profile_id']);
									if ($cloud->terminateSite( $profile_data['profile_ip'], $domainInfo['domain_url'], 0))
									{
										// add the domain in the new profile
										if ($username = $cloud->createNewSite( $profile_move['profile_ip'], $domainInfo['domain_url'], $universalpass))
										{
											$domainUpdate['domain_profile_id'] 	= $accountInfo['account_profile'];
											$domainUpdate['domain_account_id'] 	= $account_id;
											$domainUpdate['domain_ftp_user']	= $username;
											$domainUpdate['domain_ftp_pass'] 	= $universalpass;
											if ($Site->save_domain($domainUpdate,$domain_id))
												$ok++;
											else
												$failMsg .= 'Error updating '.$domainInfo['domain_url'].'.<br>';
										}
										else
											$failMsg .= $domainInfo['domain_url'].' can NOT be added to the new profile.<br>';
									} 
									else
										$failMsg .= $domainInfo['domain_url'].' can NOT be removed from the old profile.<br>';
								}
							}
							else
								$failMsg .= $domainInfo['domain_url'].' NOT moved. The account does NOT exist.<br>';
							break;
					case 'create_domains_inserver':
							// Remove the domain from the system in the old profile
							$date = date('Y-m-d',strtotime($domainInfo['domain_createdate']));
							$dateUd = date('Y-m-d',strtotime($domainInfo['domain_updatedate']));
							// add the domain in the new profile
							$profile_data = $Profile->getProfileInfo($domainInfo['domain_profile_id']);
							if ($username = $cloud->createNewSite( $profile_data['profile_ip'], $domainInfo['domain_url'], $universalpass))
							{
								$ok++;
							}
							else
								$failMsg .= $domainInfo['domain_url'].' can NOT be created in the server.<br>';
							break;
					case 'delete_domains_inserver':
							$profile_data = $Profile->getProfileInfo($domainInfo['domain_profile_id']);
							if ($cloud->terminateSite( $profile_data['profile_ip'], $domainInfo['domain_url'], 0))
								$ok++;
							else 
								$failMsg .= $domainInfo['domain_url'].' can NOT be deleted from the system.<br>';
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
	elseif(!empty($_REQUEST['account_id']))
	{
		$domainList = $Site->get_domain_data_list('account',$_REQUEST['account_id']);
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
?>
<script type="text/javascript">

$(document).ready(function() {
	$("#article_dialog" ).dialog({
			autoOpen: false,
			show: "blind",
			hide: { effect: 'drop', direction: "down" },
			title: "Scrape Article",
			buttons: { "Close": function() { $(this).dialog("close"); }} 
	});
	$("#directory_dialog" ).dialog({
			autoOpen: false,
			show: "blind",
			hide: { effect: 'drop', direction: "down" },
			title: "Scrape Article",
			buttons: { "Close": function() { $(this).dialog("close"); }} 
	});
	$("#qa_dialog" ).dialog({
			autoOpen: false,
			show: "blind",
			hide: { effect: 'drop', direction: "down" },
			title: "Scrape Question/Answer",
			buttons: { "Close": function() { $(this).dialog("close"); }} 
	});
	
	$(".selectall").click(function() {
		var checked_status = this.checked;
		$('input[type="checkbox"]').each(function() {
			this.checked = checked_status;
		});
	});	
	
});

function confirm_operation(){
	 if ($('input[name=type]:checked').val() != 'search' && $('input[name=type]:checked').val() != 'search_module_missing')
		 return confirm("Are you sure you want to move/delete this domains?");
	 else
		 return true;
}

function scrape_article(){
	var domain_id = $('#article_domain_id').val();
	var limitnum = $('#article_limitnum').val();
	var keyword = $('#article_keyword').text();
	var article_source = $('#article_source').val();
	var domain_url = $('#url_'+domain_id).text();
	
	$('#article_dialog').dialog("close");
	$("#article_"+domain_id).html('<img src="images/loading.gif" >');
	
	$.get("ajax.php", {action:'scrape_article', domain_url:domain_url, domain_id:domain_id, limitnum:limitnum, keyword: keyword, article_source:article_source},function(data){
			if(data=='1')
				$("#article_"+domain_id).html('Minimum amount articles are ready now.');
			else
				$("#article_"+domain_id).html('System error, try it later.');						
	});		
}

function scrape_directory(){
	var limitnum = $('#directory_limitnum').val();
	var keyword = $('#directory_keyword').text();
	var domain_id = $('#directory_domain_id').val();
	
	$('#directory_dialog').dialog("close");
	$("#directory_"+domain_id).html('<img src="images/loading.gif" >');
	
	$.get("ajax.php", {action:'scrape_directory', limitnum:limitnum, keyword:keyword},function(data){
			if(null!=data)
				$("#directory_"+domain_id).html(data);
			else
				$("#directory_"+domain_id).html('System error, try it later.');						
	});	
}

function scrape_qa(){
	var limitnum = $('#qa_limitnum').val();
	var keyword = $('#qa_keyword').text();
	var domain_id = $('#qa_domain_id').val();
	
	$('#qa_dialog').dialog("close");
	$("#qa_"+domain_id).html('<img src="images/loading.gif" >');
	
	$.get("ajax.php", {action:'scrape_qa', limitnum:limitnum, keyword:keyword},function(data){
			if(null!=data)
				$("#qa_"+domain_id).html(data);
			else
				$("#qa_"+domain_id).html('System error, try it later.');						
	});	
}

function scrape_article_dialog(domain_id, domain_ur, domain_keyword){ 
	$('#article_keyword').text(domain_keyword);
	$('#article_domain_id').val(domain_id);
	$("#article_dialog" ).dialog("open");
}
function scrape_directory_dialog(domain_id, domain_keyword){
	$('#directory_keyword').text(domain_keyword);
	$('#directory_domain_id').val(domain_id);
	$("#directory_dialog" ).dialog("open");
}
function scrape_qa_dialog(domain_id, domain_keyword){
	$('#qa_keyword').text(domain_keyword);
	$('#qa_domain_id').val(domain_id);
	$("#qa_dialog" ).dialog("open");
}
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
				<form action="domains_manager.php" method="POST" name="data_table" id="data_table"  enctype="application/x-www-form-urlencoded">
				<table align="center" border="0" cellspacing="0" cellpadding="0" width="100%" class="tablePop">
					<tr>
						<td align="left" valign="top"><span class="txtHdr">Domains</span></td>
						<td align="right" valign="top"></td>
					</tr>
					<tr>
						<td colspan="2" valign="top">
						<div id="tableData">
						<table width="100%" border="0" cellspacing="1" cellpadding="0" id="dt1" class="dataTable">
							<tr>
								<td align="center" class="cellHdr"><input type="checkbox" class="selectall" ></td>
								<td align="center" class="cellHdr">Domain</td>
								<td align="center" class="cellHdr">Title</td>
								<td align="center" class="cellHdr">Default Keyword</td>
								<td align="center" class="cellHdr">Profile</td>
								<td align="center" class="cellHdr">Account</td>
								<td align="center" class="cellHdr">Layout</td>
								<td align="center" class="cellHdr">Feed</td>
								<td align="center" class="cellHdr">Feed ID</td>
								<td align="center" class="cellHdr">Status</td>
								<?php if($action_type == 'search_module_missing') : ?>
								<td align="center" class="cellHdr">Required Modules</a></td>
								<td align="center" class="cellHdr">Missing Elements</a></td>
								<?php else : ?>
								<td align="center" class="cellHdr">Articles</td>
								<td align="center" class="cellHdr">Image Missed</td>
								<?php endif; ?>
								<td align="center" class="cellHdr">Edit</td>
							</tr>
						    <?php
								$totalNum = 0;
								foreach ($domainList as $pRow)
								{
									$layoutNum  = $pRow['layout_name'];
									/************* work out other missed stuff ****************/
									if($action_type == 'search_module_missing')
									{
										$missed 	= get_missed_module_element($pRow['layout_id'], $pRow);
										$moduleList = $missed['module_list'];
										$advice 	= $missed['html'];
									}
									 /************* other missed stuff end ********************/
									else 
									{
										$articleNum = $Article->count_articles($pRow['domain_url']);
										/************* work out missed image ****************/
										$img = Image::getInstance($db);
										$existingImages = $img->getDomainImage($pRow['domain_id']);
										$requiredImages = Layout::get_image_amount($pRow['layout_id']);
										$dif = $requiredImages-$existingImages;
										$missedImage = $dif>0 ? $dif:0;
										if($missedImage>0)
											$red = ' href="/edit_domain.php?domain_id='.$pRow["domain_id"].'" target="_blank" style="color:red;" ';
										else
										    $red = '';
									}
									?>
								    
							<tr class="alter1">
							    <td  width="30" align="center" valign="middle" ><input type="checkbox" name="domains[]" value="<?php echo $pRow['domain_id'];?>"></td>
								<td align="left" valign="middle" ><a href="http://<?php echo $pRow['domain_url'];?>" target="_blank" id="url_<?php echo $pRow['domain_id'];?>"><?php echo $pRow['domain_url'];?></a></td>
							    <td align="left" valign="middle" ><?php echo $pRow['domain_title'];?></td>
							    <td align="left" valign="middle" ><?php echo $pRow['domain_keyword'];?></td>
							    <td align="left" valign="middle" ><?php echo $pRow['profile_name'];?></td>
							    <td align="left" valign="middle" ><?php echo $pRow['account_name'];?></td>
							    <td align="left" valign="middle" ><?php echo $layoutNum;?></td>
							    <td align="center" valign="middle" ><?php echo $pRow['domain_feedtype'];?></td>
							    <td align="center" valign="middle" ><?php echo $pRow['domain_feedid']; ?></td>
							    <td align="center" valign="middle" ><?php if ($pRow['status'] == 1) : echo 'OK'; else : echo 'Moved'; endif; ?></td>
							    <?php if($action_type == 'search_module_missing') : ?>
							    <td align="center" valign="middle" ><?php  echo $moduleList; ?></td>
							    <td align="center" valign="middle" ><?php  echo $advice; ?></td>
								<?php else :?>
							    <td align="center" valign="middle" ><?php echo $articleNum;?></a></td>
							    <td align="center" valign="middle" ><a <?php echo $red;?>><?php echo $missedImage;?></a></td>
								<?php endif; ?>
							    <td align="center" valign="middle" ><a href="/edit_domain.php?domain_id=<?php echo $pRow['domain_id'];?>" target="_blank">edit domain</a>&nbsp;&nbsp; <a href="/edit_template.php?domain_id=<?php echo $pRow['domain_id'];?>" target="_blank">Edit template</a></td>
							</tr>
							<?php }	?>
						</table>
						</div>

						<br>
				        <input type="hidden" name="account_id" value="<?php echo @$_REQUEST['account_id'];?>" />
				        <input type="hidden" name="search" value="<?php echo @$_REQUEST['search'];?>" />
						<table border="0" cellspacing="0" cellpadding="0" align="center">
				        <?php if ($user->userLevel == 6) { ?>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][0].checked = true;">
								<input type="radio" name="type" value="delete_domains"> Delete selected domains from System (database and slave)
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][1].checked = true;">
								<input type="radio" name="type" value="clean_database"> Remove selected domains from Database only
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][2].checked = true;">
								<input type="radio" name="type" value="move_domains_parked"> Move domains to Parked (delete from system but keep domain's articles link)
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][3].checked = true;">
								<input type="radio" name="type" value="move_domains_account"> Move domains to Account
		                    	<select name="move_account_id">
		                    	<option value="">Select</option>
								<?php
								$accountArray = $Profile->getAccounts(0);
								foreach($accountArray as $accountInfo)
								{
									echo '<option value="'.$accountInfo['account_id'].'">'.$accountInfo['account_name'].'</option>';
								}
								?>
								</select>
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][4].checked = true;">
								<input type="radio" name="type" value="create_domains_inserver"> Create domains in the server
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][5].checked = true;">
								<input type="radio" name="type" value="delete_domains_inserver"> Delete domains in the server
								</td>
							</tr>
							<tr>
								<td align="left">
								<div style="float:left"><input type="radio" name="type" value="search"> Search for domain: </div><textarea style="float:left" type="text" cols="30" rows="3" name="search" onclick="document.forms['data_table'].elements['type'][6].checked = true;"></textarea>
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][7].checked = true;">
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
								<td align="left" onclick="document.forms['data_table'].elements['type'][8].checked = true;">
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
								<td align="left" onclick="document.forms['data_table'].elements['type'][9].checked = true;">
								<input type="radio" name="type" value="search">Search for domains using keyword:
			                    <input type="text" name="keyword" size="40" >
								</td>
							</tr>
                            <tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][10].checked = true;">
								<input type="radio" name="type" value="search_module_missing">Search for <b>module missing elements</b> in Account:
		                    	<select name="account_id">
		                    	<option value="">Select</option>
								<?php
								$accountArray = $Profile->getAccounts(0);
								foreach($accountArray as $accountInfo)
								{
									echo '<option value="'.$accountInfo['account_id'].'">'.$accountInfo['account_name'].'</option>';
								}
								?>
								</select>
								</td>
							</tr>
							
						<?php } elseif ($user->userLevel >= 4) { ?>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][0].checked = true;">
								<input type="radio" name="type" value="delete_domains"> Delete selected domains from System (database and slave)
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][1].checked = true;">
								<input type="radio" name="type" value="move_domains_parked"> Move domains to Parked (delete from system but keep domain's articles link)
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][2].checked = true;">
								<input type="radio" name="type" value="move_domains_account"> Move domains to Account
		                    	<select name="move_account_id">
		                    	<option value="">Select</option>
								<?php
								$accountArray = $Profile->getAccounts(0);
								foreach($accountArray as $accountInfo)
								{
									echo '<option value="'.$accountInfo['account_id'].'">'.$accountInfo['account_name'].'</option>';
								}
								?>
								</select>
								</td>
							</tr>
							<tr>
								<td align="left">
								<div style="float:left"><input type="radio" name="type" value="search"> Search for domain: </div>
								<textarea style="float:left" type="text" cols="30" rows="3" name="search" onclick="document.forms['data_table'].elements['type'][3].checked = true;"></textarea>
								</td>
							</tr>
							<tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][4].checked = true;">
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
								<td align="left" onclick="document.forms['data_table'].elements['type'][5].checked = true;">
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
								<td align="left" onclick="document.forms['data_table'].elements['type'][6].checked = true;">
								<input type="radio" name="type" value="search">Search for domains using keyword:
			                    <input type="text" name="keyword" size="40" >
								</td>
							</tr>
                            <tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][7].checked = true;">
								<input type="radio" name="type" value="search_module_missing">Search for <b>module missing elements</b> in Account:
		                    	<select name="account_id">
		                    	<option value="">Select</option>
								<?php
								$accountArray = $Profile->getAccounts(0);
								foreach($accountArray as $accountInfo)
								{
									echo '<option value="'.$accountInfo['account_id'].'">'.$accountInfo['account_name'].'</option>';
								}
								?>
								</select>
								</td>
							</tr>
						<?php } else { ?>
							<tr>
								<td align="left">
								<div style="float:left"><input type="radio" name="type" value="search"> Search for domain: </div>
								<textarea style="float:left" type="text" cols="30" rows="3" name="search" onclick="document.forms['data_table'].elements['type'][0].checked = true;"></textarea>
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
                            <tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][4].checked = true;">
								<input type="radio" name="type" value="search_module_missing">Search for <b>module missing elements</b> in Account:
		                    	<select name="account_id">
		                    	<option value="">Select</option>
								<?php
								$accountArray = $Profile->getAccounts(0);
								foreach($accountArray as $accountInfo)
								{
									echo '<option value="'.$accountInfo['account_id'].'">'.$accountInfo['account_name'].'</option>';
								}
								?>
								</select>
								</td>
							</tr>
						<?php } ?>
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
}
else
{
	if($action == 'add_account')
	{
		$account['account_name'] = isset($_REQUEST['account'])?$_REQUEST['account']:'';
		$profileID = isset($_REQUEST['profile_id'])?$_REQUEST['profile_id']:'';
		if(!empty($profileID) && !empty($account['account_name']))
		{
			$account['account_profile'] = $profileID;
			$account_id = $Profile->save_account($account);
		}
	}
	elseif($action == 'other' && $action_type == 'delete')
	{
		$account_ids = $_REQUEST['accounts'];
		$del = 0;
		$err = 0;
		foreach($account_ids as $account_id)
		{
			if($Profile->del_account($account_id))
				$del++;
			else
				$err++;
		}
		if ($del > 0)
			$sucMsg = "$del accounts deleted";
		if ($err > 0)
			$failMsg = "$err accounts could NOT be deleted. Check that the accounts are empty.";
	}
	elseif($action == 'other' && $action_type == 'search_account')
	{
		$profileID = (isset($_REQUEST['profile_id'])&&!empty($_REQUEST['profile_id']))?$_REQUEST['profile_id']:0;		
	}
	elseif($action == 'modify_account')
	{
		$account_id = $_REQUEST['account_id'];
		$account_name['account_name'] = $_REQUEST['account_name'];
		$account_id = $Profile->save_account($account_name, $account_id);
	}
?>
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
			<table align="center" border="0" cellspacing="0" cellpadding="0" width="100%" class="tablePop">
			<tr style="background-color:#999999">
				<td align="left" valign="top"><span class="txtHdr">Accounts</span></td>
				<td align="right" valign="top">
				<form action="/domains_manager.php" method="POST">
					<input type="hidden" name="action" value="add_account">
					Create new account: 
					<?php $profiles = $Profile->getProfileList(); ?>
	        		<select name="profile_id">
	                	<option value=''>Please Select</option>
						<?php
						foreach ($profiles as $pRow) 
						{
							echo '<option value="'.$pRow['profile_id'].'">'.$pRow['profile_name'].'</option>';
						}
						?>
	            	</select>
	        		<input type="text" name="account">
	        		<input type="submit" value="Add">
	        	</form>
        		</td>
			</tr>
			<tr>
				<td colspan="2" valign="top">
				<br />
				<form action="domains_manager.php" method="POST" name="data_table" id="data_table"  enctype="application/x-www-form-urlencoded">
				
				<?php if(!empty($action)) : ?>
				<div id="tableData">
					<table width="100%" border="0" cellspacing="1" cellpadding="0" id="dt1" class="dataTable">
					<tr>
						<td align="center" class="cellHdr"></td>
						<td align="center" class="cellHdr"><a>Account</a></td>
						<td align="center" class="cellHdr"><a>Profile</a></td>
						<td align="center" class="cellHdr"><a>Number of Domains</a></td>
						<td align="center" class="cellHdr">Edit</td>
					</tr>
                	<?php
	                	$Account = Account::getInstance($db);
                		$totalNum = 0;
						$accounts = $Profile->getAccounts(isset($profileID)?$profileID:0);
						foreach ($accounts as $pRow) 
						{
							$domainNum = $Account->get_num_domains($pRow['account_id']);
							$totalNum = $totalNum+$domainNum;
							$profileName = $Profile->getProfileInfo($pRow['account_profile']);
							$profileName = $profileName['profile_name'];
							?>
						    
						    <tr class="alter1">
						    	<td  width="30" align="center" valign="middle" ><input type="checkbox" name="accounts[]" value="<?php echo $pRow['account_id'];?>"></td>
								<td align="left" valign="middle" ><a href="/domains_manager.php?action=list_domains&account_id=<?php echo $pRow['account_id'];?>" target=""><?php echo $pRow['account_name'];?></a></td>
						        <td align="center" valign="middle" ><?php echo $profileName;?></td>
						        <td align="center" valign="middle" ><?php echo $domainNum;?></td>
								<td align="center" valign="middle" ><a href="/domains_manager.php?action=edit_account&account_id=<?php echo $pRow['account_id'];?>" target="">edit name</a></td>
						   </tr>
					<?php
					}
					?>
					<tr >
						<td class="cellSubhdr"align="center" valign="middle" ></td>
						<td class="cellSubhdr"align="left" valign="middle" >TOTAL</td>
						<td class="cellSubhdr"align="center" valign="middle" ></td>
						<td class="cellSubhdr"align="center" valign="middle" ><?php echo $totalNum;?></td>
						<td class="cellSubhdr"align="center" valign="middle" ></td>
					</tr>
					</table>
				</div>
				<br>
				<?php endif; ?>		
						
				<table border="0" cellspacing="0" cellpadding="0" align="center">
				<tr>
					<td align="left" onclick="document.forms['data_table'].elements['type'][0].checked = true;">
					<input type="radio" name="type" value="search_account">Search for accounts in Profile:
                    <select name="profile_id">
                    <option value="">All</option>
					<?php
					$profiles = $Profile->getProfileList();
					foreach ($profiles as $pRow) 
					{
						echo '<option value="'.$pRow['profile_id'].'">'.$pRow['profile_name'].' ('.$pRow['profile_ip'].')</option>';
					}
					?>
					</select>
					</td>
				</tr>

				<tr>
					<td align="right">
					<div style="float:left">
					<input type="radio" name="type" value="search">Search for domain: 
					</div>
					<textarea style="float:left" type="text" cols="30" rows="3" name="search" onclick="document.forms['data_table'].elements['type'][1].checked = true;"></textarea>
					</td>
				</tr>

				<tr>
					<td align="left" onclick="document.forms['data_table'].elements['type'][2].checked = true;">
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
					<td align="left" onclick="document.forms['data_table'].elements['type'][3].checked = true;">
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
					<td align="left" onclick="document.forms['data_table'].elements['type'][4].checked = true;">
					<input type="radio" name="type" value="search">Search for domains using keyword:
                    <input type="text" name="keyword" size="40" >
					</td>
				</tr>
				
                <tr>
								<td align="left" onclick="document.forms['data_table'].elements['type'][5].checked = true;">
								<input type="radio" name="type" value="search_module_missing">Search for <b>module missing elements</b> in Account:
		                    	<select name="account_id">
		                    	<option value="">Select</option>
								<?php
								$accountArray = $Profile->getAccounts(0);
								foreach($accountArray as $accountInfo)
								{
									echo '<option value="'.$accountInfo['account_id'].'">'.$accountInfo['account_name'].'</option>';
								}
								?>
								</select>
								</td>
				</tr>

				<?php if (($user->userLevel >= 4) && !empty($action)) : ?>
				<tr>
					<td align="left" onclick="document.forms['data_table'].elements['type'][6].checked = true;">
					<input type="radio" name="type" value="delete"> Delete selected accounts from system <font size="-2">(account should be empty)</font>
					</td>
				</tr>
				<?php  endif; ?>
			
				<tr>
					<td align="center">
						<br>
			
						<input type="submit" value="Perform Selected Operation">
					</td>
				</tr>
				</table>
				
				<input type="hidden" value="other" name="action">
				</form>

				</td>
			</tr>
			</table>		
			
			<!-- *** END MAIN CONTENTS  *** -->
			</td>

			<td rowspan="5" class="brdrR">&nbsp;</td>
		</tr>
	</table>
</div>

<?php
}
?>

<div id="article_dialog" style="display:none">
								<table width="100%" cellspacing="0" cellpadding="0" border="0">
									<tbody><tr>
										<td align="left" colspan="3">Select article source:</td>
									</tr>
									
									<tr>
										<td width="13%">&nbsp;</td>
										<td width="250px" valign="top" align="left"><br>
                                            From <select name="article_source" id="article_source">
                                            	 <option selected="selected" value="ehow">ehow</option>
                                                 <option value="articleBase">articleBase</option>
                                                 <option value="EzineArticles">EzineArticles</option>
                                                 <option value="hubpages">hubpages</option>
                                            </select>
											<br><br>
											
										</td>
										<td width="13%">&nbsp;</td>
									</tr>
                                    
                                    <tr>
										<td width="13%">&nbsp;</td>
										<td width="250px" valign="top" align="left"><br>
                                            Amount <select id="article_limitnum" name="article_limitnum">
                                            	 <option selected="selected" value="1">1</option>
                                                 <option value="2">2</option>
                                                 <option value="3">3</option>
                                                 <option value="4">4</option>
                                                 <option value="5">5</option>
                                                 <option value="6">6</option>
                                            </select>
											<br><br>
											<font size="-2">Domain keyword: <b id="article_keyword"></b></font>
                                            <input type="hidden" name="article_domain_id" id="article_domain_id" value=""  />
										</td>
										<td width="13%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"><br /> <input type="submit" name="submit" value="Populate Article" onclick="scrape_article();"></td>
									</tr>
                                   
								</tbody></table>
</div> <!-- article_dialog end-->

<div id="directory_dialog" style="display:none">
								<table width="100%" cellspacing="0" cellpadding="0" border="0">
									<tbody><tr>
										<td align="left" colspan="3">Select scraping amount:</td>
									</tr>
																		                                    
                                    <tr>
										<td width="13%">&nbsp;</td>
										<td width="250px" valign="top" align="left"><br>
                                            <select id="directory_limitnum" name="directory_limitnum">
                                            	 <option selected="selected" value="1">1</option>
                                                 <option value="2">2</option>
                                                 <option value="3">3</option>
                                                 <option value="4">4</option>
                                                 <option value="5">5</option>
                                                 <option value="6">6</option>
                                            </select>
											<br><br>
											<font size="-2">Domain keyword: <b id="directory_keyword"></b></font>
                                            <input type="hidden" name="directory_domain_id" id="directory_domain_id" value=""  />
										</td>
										<td width="13%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"><br /> <input type="submit" name="submit" value="Populate directory" onclick="scrape_directory();"></td>
									</tr>
                                   
								</tbody></table>
</div> <!-- directory_dialog end-->

<div id="qa_dialog" style="display:none">
								<table width="100%" cellspacing="0" cellpadding="0" border="0">
									<tbody><tr>
										<td align="left" colspan="3">Select scraping amount:</td>
									</tr>
																		                                    
                                    <tr>
										<td width="13%">&nbsp;</td>
										<td width="250px" valign="top" align="left"><br>
                                            <select id="qa_limitnum" name="qa_limitnum">
                                            	 <option selected="selected" value="1">1</option>
                                                 <option value="2">2</option>
                                                 <option value="3">3</option>
                                                 <option value="4">4</option>
                                                 <option value="5">5</option>
                                                 <option value="6">6</option>
                                            </select>
											<br><br>
											<font size="-2">Domain keyword: <b id="qa_keyword"></b></font>
                                            <input type="hidden" name="qa_domain_id" id="qa_domain_id" value=""  />
										</td>
										<td width="13%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"><br /> <input type="submit" name="submit" value="Populate Q&A" onclick="scrape_qa();"></td>
									</tr>
                                   
								</tbody></table>
</div> <!-- qa_dialog end-->


<?php 
require_once('footer.php');

function get_missed_module_element($layout_id, $domainInfo=array())
{
	global $db;
	global $Layout;
	static $layoutMod = array();
	
	if (!array_key_exists ($layout_id, $layoutMod))
	{
		$layoutMod[$layout_id] = $Layout->getLayoutModules($layout_id);
		ksort($layoutMod[$layout_id]);
	}
	$modules 		= $layoutMod[$layout_id];
	$domain_id 		= trim($domainInfo['domain_id']);
	$domain_url 	= trim($domainInfo['domain_url']);
	$domain_keyword = trim($domainInfo['domain_keyword']);
	
	$Site = Site::getInstance($db);
	$missingModules = $Site->get_missed_module_element($modules, $domain_id, $domain_url, $domain_keyword);
	$returnStr = '<ul id="missList">';
	$moduleList= '';

	foreach($modules as $k=>$m){
		$moduleList.= $k.($m > 0?" = $m":'').'<BR />';
	}
	
	foreach($missingModules as $k=>$m){
		switch(strtoupper($k)):
			case('ARTICLE'):
					$returnStr .= '<li id=\'article_'.$domain_id.'\'><a href=\'#\' class=\'article_'.$domain_id.'\' onclick="scrape_article_dialog('.$domain_id.',\''.$domain_url.'\',\''.$domain_keyword.'\'); return false;">'.$m.' ARTICLE required.</a></li>';
				break;
			case('DIRECTORY'):
					$returnStr .= '<li id=\'directory_'.$domain_id.'\'><a href=\'#\' class=\'directory_'.$domain_id.'\' onclick="scrape_directory_dialog('.$domain_id.',\''.$domain_keyword.'\'); return false;">'.$m.' DIRECTORY required.</a></li>';
				break;
			case('QUESTION'):
					$returnStr .= '<li id=\'qa_'.$domain_id.'\'><a href=\'#\' class=\'qa_'.$domain_id.'\' onclick="scrape_qa_dialog('.$domain_id.',\''.$domain_keyword.'\'); return false;">'.$m.' QUESTION required.</a></li>';
				break;
			case('MENU_IMAGE'):
					$returnStr .= '<li id=\'image_menu_'.$domain_id.'\'><a href=\'href="/edit_domain.php?domain_id='.$domain_id.'" target="_blank" style="color:red;"\' >'.$m.' IMAGE required.</a></li>';
				break;
			default:
					$returnStr .= '<li id=\''.$k.'_'.$domain_id.'\'>'.$m.' '.$k.' required.</a></li>';
		endswitch;
	}
	$returnStr."</ul>";
	
	return array('module_list' => $moduleList, 'html' => $returnStr);
}

?>