<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once('config.php');
require_once('header.php');
$Layout = new LayoutParked();
$Profile = new Profile();
$Account = new ParkedAccount();
$Layouts = $Layout->get_layouts();
$Profiles = $Profile->getParkedProfiles();
$Accounts = $Account->get_parked_accounts();

$layoutSave = '';
$layoutIds 	= array();
$profileIds = array();
$accountIds = array();
$result = array();

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'copy_layout')
{
	$layoutIds 	= isset($_REQUEST['layout_id']) && is_array($_REQUEST['layout_id'])?$_REQUEST['layout_id']:array();
	$profileIds = isset($_REQUEST['profile_id']) && is_array($_REQUEST['profile_id'])?$_REQUEST['profile_id']:array();
	$accountIds = isset($_REQUEST['account_id']) && is_array($_REQUEST['account_id'])?$_REQUEST['account_id']:array();
	
	$layoutSave = '<font color="green">Layouts copied: Check results</font>';

	$profiles = isset($_REQUEST['profile_id'])?$_REQUEST['profile_id']:'';
	if (!empty($layoutIds)) {
		$result = $Layout->publish_layout_forParked($layoutIds,$profileIds,$accountIds,$windowError);
	}
}

function trim_value(&$value) 
{ 
    $value = trim($value); 
}

?>
<style>
	.label, .inputSelect {
		min-width: 200px;
		float: left;
		margin: 5px 5px 5px 5px;
	}
	.box {
		width: 100%;
		vertical-align: top;	
		clear:both;
	}
</style>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>

			<td class="brdrL">&nbsp;</td>
			<td valign="top" id="main">
			<?php	if($layoutSave != '') : ?>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
				<tr>
					<td valign="top"><div class="blueHdr">System Message</div>
					<div class="content" align="center">
			        <?php echo $layoutSave;?>
					</div>
					</td>
				</tr>
			</table>
			<br />
			<?php endif; ?>
	
			<!-- *** START MAIN CONTENTS  *** -->

			<form action="/copy_layoutToParked.php" method="post" enctype="multipart/form-data" name="copy_layout_form" target='_self' >
				<input type="hidden" name="action" id='action' value="copy_layout">
				<span class="txtHdr">Copy Layouts to Parked</span>
				<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
				<tbody>
				<tr>
					<td valign="top" >
						<table width="100%" cellspacing="5" cellpadding="5" border="0" class="data_table" id="dt1">
						<tbody>
							<tr>
								<td align="left" width="20%" class="cellHdr"><strong>Layouts</strong></td>
				                <td align="left" class="cellHdr"><strong>Copy to Parked</strong></td>
				            </tr>
							<tr class="alter1">
								<td valign="top">
			                            <select MULTIPLE SIZE="20" style="width:90%"  name ="layout_id[]" id="parked_layouts" class="parked_layouts">
										<?php foreach ($Layouts as $pRow) : ?>
											<option value="<?php echo ($pRow['layout_id']); ?>" <?php if (isset($result[$pRow['layout_id']])) echo 'selected="selected"'?>><?php  echo ($pRow['layout_name']); ?></option>	
										<?php endforeach; ?>
							            </select>					
							    </td>
				    			<td valign="top">
										<div id="layout_0" >
											<div class="label">Layout</div>	
											<div class="label">Profile</div>	
											<div class="label">Accounts</div>	
											<div class="label">Results</div>	
										</div>
										<?php foreach ($Layouts as $pRow) : ?>
												<div id="layout_<?php echo ($pRow['layout_id']); ?>" class="box" <?php if (!empty($result[$pRow['layout_id']])) { echo ('style="display:block;"'); } else { echo ('style="display:none"');} ?>>
													<div class="label"><?php  echo ($pRow['layout_name']); ?></div>
							                   		<select class="inputSelect" name="profile_id[<?php echo ($pRow['layout_id']); ?>]" id="profile_<?php echo ($pRow['layout_id']); ?>">
							                    	<option value="" >Select One</option>
													<?php foreach ($Profiles as $row) : ?>
														<option value="<?php echo ($row['profile_id']); ?>"><?php echo $row['profile_name']; ?></option>
													<?php endforeach; ?>
							                		</select>
							                   		<select MULTIPLE SIZE="5" class="inputSelect" name="account_id[<?php echo ($pRow['layout_id']); ?>][]" id="account_<?php echo ($pRow['layout_id']); ?>">
													<?php foreach ($Accounts as $row) : ?>
														<option value="<?php echo ($row['parked_account_id']); ?>"><?php echo $row['parked_account_name']; ?></option>
													<?php endforeach; ?>
							                		</select>
													<div class="label" id="result_<?php echo ($pRow['layout_id']); ?>"><?php if (isset($result[$pRow['layout_id']])) echo $result[$pRow['layout_id']]; ?></div>	
												</div>
										<?php endforeach; ?>
				    			</td>
							</tr>
							<tr>
								<td>
			                        <p  class="alignCenter">
										<input name="apply" type="button" class="label"  value=" Clear All " onclick="clearAll ();">
									</p>
								</td>
								<td>
			                        <p  class="alignCenter">
										<input name="apply" type="submit" class="label"  value=" Copy to Parked " onclick="setAction('copy_layout')">
									</p>
								</td>
							</tr>
						</tbody>
						</table>
					</td>
				</tr>
				</tbody>
				</table>		
			</form>
			</td>
		</tr>
	</table>
</div>

<script language="JavaScript">
/*$("#parked_layouts").change( function() {
	alert('Handler for .change() called.');
	
	for (x in $("#layouts_list").options) {
	}
		
}); */

$("#parked_layouts").change(function () {
    $("#parked_layouts option:selected").each(function () {
	    $("#layout_" + $(this).val() ).css("display","block"); 
    });
    $("#parked_layouts option").each(function () {
		id = $(this).val();
		if ($(this).attr("selected") == false ) {
			$("#layout_"+id).css("display","none");
			$("#profile_"+id).attr("selectedIndex",0);
			$("#account_"+id+" option:selected").each(function () {
				$(this).attr("selected",false);
			});
			$("#result_"+id).html("");
		}
    });
  })
  .change();


function clearAll () {
    $("#parked_layouts option:selected").each(function () {
		$(this).attr("selected",false);
    });
    $("#parked_layouts").change();
}

function setAction (value) {
	$("#action").val(value);
}

</script>

<?php
require_once('footer.php');
?>
