<?php
/**
 * output various reports
 * Author: Gordon Ye on 20 Jan 2011
**/

require_once ('config.php');
require_once ('header.php');
$profileObj = new Profile ();
$profileList = $profileObj->getProfileList ();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
?>



<script>
$(function() {
	$('#profile_panel').hide();	 
	$( "#datefrom, #dateto" ).datepicker({dateFormat: 'yy-mm-dd'});
});

function goTo(report){
	if (report == 'missing_content_domains') {
		location.href= "reports.php?action=missing_content";
	} 
	else if(report == 'banned_sx25_domains'){
		location.href= "reports.php?action=banned_sx25_domains";
	}
	else if(report == 'banned_parked_domains') {
		location.href= "reports.php?action=banned_parked_domains";
	}
	else {
		location.href= "extract_report.php?action=report&report="+report;	
	}
}

function reportMissingContent(){
	location.href = "extract_report.php?action=report&report=missing_content";
}

function listProfiles(){
	$( "#profile_panel" ).dialog({ buttons: { "Go!": function() {  goToMapping();  $(this).dialog("close"); } } });
}

function goToMapping(){	
	var profile= $("#profile_list").serialize();
	location.href= "extract_report.php?action=report&report=sx25_mapping_keywords&"+profile;	
}

</script>

<style>
.p1,.p2,.p3,.p4,.p5 {
	float: left;
	width: 160px;
}

.page {
	clear: both;
	padding: 10px;
	margin-left: 20px
}

.save_page_button {
	margin-right: 15px;
}

.spacer {
	height: 20px;
	width: 90%;
	margin: 10px;
	border-bottom: 1px dashed #333;
	clear: both;
}

.fs {
	margin-left: 45px
}

.opacity {
	opacity: .5;
	filter: alpha(opacity=50); 	
}

.msg {
	color: red;
	padding: 15px;
}

table.whiteBlue td {
   padding: 15px;
}

.account_report{
	height:100px;
	overflow: hidden;
}
</style>

<div id="main_content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>
			<td valign="top" id="main" style="width:100%">
			
				<!-- *** START MAIN CONTENTS  *** -->
				<div>
	         		<span class="txtHdr" style="float:left;">Get Reports</span>
				</div> <br> <br>
				<?php if ($action == 'missing_content' || $action == 'banned_sx25_domains' || $action == 'banned_parked_domains'): ?>
				<form action="extract_report.php" method="POST">
			
				<table class="whiteBlue" align="center" border="0" cellpadding="3"	cellspacing="0" width="100%" id="boxGray">
					
					<?php if(($action == 'banned_sx25_domains') ||  ($action == 'banned_parked_domains')): ?>
					<tr>
						<td class="col1" width="20%">Date Range</td>
						<td align="left" width="80%">From: <input id="datefrom" name="datefrom" type="text">  To: <input id="dateto" name="dateto" type="text"></td>
					</tr>
					<?php else : ?>
					<tr>
						<td class="col1" width="20%">Choose account</td>
						<td align="left" width="80%">
						<?php
							$Account	= Account::getInstance($db);
							$accounts	= $Account->getAccountList();
						?>
							<select name="account_id[]" class="account_report" multiple="multiple" >
								<?php 
								foreach($accounts as $account)
								{
									echo '<option value="'.$account['account_id'].'" >'.$account['account_name'].'</option>';	
								}
								?>
							</select>
						</td>
					</tr>
					<?php endif; ?>
				</table>
				<input type="hidden" name="report" value="<?php echo $action; ?>" >
				<input type="hidden" name="action" value="report" >
				<button type="submit" class="button" >Get Report</button>
				</form>
				<?php else: ?>
				<table class="whiteBlue" align="center" border="0" cellpadding="3"
					<tr>
						<td class="col1" width="20%">SX25 Domains</td>
				        <td align="left" width="80%">
				        	<button class='button' onclick='goTo("remove_domains");'>Removed Domains</button>
				            <button class='button' onclick='goTo("active_domains");'>Active Domains</button>
				            <button class='button' onclick='goTo("sx25_articles");'>Articles</button>
				            <button class='button' onclick='goTo("sx25_images");'>Images</button>
				            <button class='button' onclick='goTo("sx25_both");'>Articles and Images</button>
				            <button class='button' onclick='goTo("sx25_menues");'>Domain Menus</button>
                            <button class='button' onclick='listProfiles(); return false;'>Domain Mapping Keywords</button>
							<button class='button' onclick='goTo("missing_content_domains");'>Missing Content Domains</button>
							<button class='button' onclick='goTo("banned_sx25_domains");'>Banned Domains</button>
				         </td>
					</tr>
					<tr>
						<td class="col1" width="20%">Parked Sites</td>
				        <td align="left" width="80%">
				            <button class='button' onclick='goTo("parked_domains");'>Parked Domains</button>
				            <button class='button' onclick='goTo("parked_articles");'>Parked Articles</button>
				            <button class='button' onclick='goTo("parked_genz");'>Parked GenZ Domains</button>
							<button class='button' onclick='goTo("banned_parked_domains");'>Banned Domains</button>
				         </td>
					</tr>
					<tr>
						<td class="col1" width="20%">Libraries</td>
				        <td align="left" width="80%">
				            <button class='button' onclick='goTo("directories");'>Directory Listings</button>
				            <button class='button' onclick='goTo("questions");'>Questions and Answers</button>
				            <button class='button' onclick='goTo("images");'>Image Library</button>
				            <button class='button' onclick='goTo("answers");'>Expert Answers</button>
				            <button class='button' onclick='goTo("related_keywords");'>Related Keywords</button>
				         </td>
					</tr>
  				</table>
				<?php endif; ?>
			</td>
			<td rowspan="5" class="brdrR">&nbsp;</td>
		</tr>
	</table>			
</div>

<?php if ($action != 'missing_content' && $action != 'banned_sx25_domains' && $action != 'banned_parked_domains'): ?>
<div id="profile_panel" title="Dialog Title">
	<h3>Please select profiles:</h3>
	<form id="profile_list">
		<ul>
			<li class='single_profile'><input type='checkbox' name='' value='' checked>All</li>
<?php
foreach ( $profileList as $k => $v ) {
	echo "<li class='single_profile'><input type='checkbox' name='profile_id[]' value='" . $v ['profile_id'] . "'>" . $v ['profile_name'] . "</li>";
}
?>
</ul>
	</form>
</div>
<?php endif; ?>
<?php
require_once('footer.php');
?>
