<?php
require_once('config.php');
require_once('header.php');

$Article = Article::getInstance($db);
$Profile = new Profile();
$Site = Site::getInstance($db); 
$Parked = ParkedDomain::getInstance($db); 

?>

<script language="javascript">
function default_content(){
	var account_id = $('#account_id').val();
	var content_choice = $('#content_choice').val();
	$("#loading_zone").html('<img src="images/loading.gif" style="margin:40px 120px">');
	$('#feedback_panel').html('');
	
	$.get("sx25_ajax.php", {action:'default_content', account_id: account_id, content:content_choice},function(data){
		$('#feedback_panel').html(data).fadeIn(200);
	});
	
	$("#loading_zone").ajaxComplete(function() {
	  	$(this).fadeOut();
	});
}
</script>

<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<?php if(!empty($sucMsg) && !empty($failMsg)) : ?>
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
			
			<span class="txtHdr">Populate Default Content </span>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
			<tbody>
			<tr>
				<td valign="top" colspan="2">
					
					<div id="tableData">
					<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
						<tbody><tr>
						<td align="left" class="cellHdr"><strong>Populate Default Content</strong></td>
                        <td align="left" class="cellHdr"><strong>Populate Extra Content</strong></td>
                        </tr>

						<tr class="alter1">
							<td valign="top" align="center" width="50%">	 

									<input type="hidden" value="default_content" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td align="left" colspan="3">Select Account:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="left" valign="top" width="200px"><br>
											<?php $Profiles = $Profile->getProfileList(); ?>
											<select name="profile_id" id="profile_id" onChange="initCs();">
                                            <option value="">Please Select</option>
											<?php foreach ($Profiles as $row) : ?>
												<option value="<?php echo ($row['profile_id']); ?>"><?php echo $row['profile_name']; ?></option>
											<?php endforeach; ?>
											</select>
											&nbsp;&nbsp;<select name="account_id" id="account_id"></select>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="left" valign="top" width="200px"><br>
                                            Fill Content <select name="content_choice" id="content_choice">
                                            	 <option value="" selected="selected">Select here</option>
                                            	 <option value="all">ALL</option>
                                                 <option value="menu_image">images</option>
                                                 <option value="article">articles</option>
                                                 <option value="question">Q&A</option>
                                                 <option value="directory">directories</option>
                                                 <option value="event">events</option>
                                            </select>
											<br><br />
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3">  <button onclick="default_content(); return false;">Populate Content</button></td>
									</tr>
                                   
								</table>

							</td>
                            
							<td valign="top" align="left">
                                <div id="loading_zone"></div>
                            	<div id="feedback_panel"></div>
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