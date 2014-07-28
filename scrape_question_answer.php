<?php
/**
 * http://www.dmoz.org/search?q=insurance
 * Author: Gordon Ye on 16/05/2011
**/
require_once('config.php');


$Profile = new Profile();
$Site = Site::getInstance($db); 
$QA = QuestionAnswer::getInstance($db); 
$artExist = 0;
$artNoKey = 0;
$scrape_amount = (isset($_REQUEST['scrape_amount']) && !empty($_REQUEST['scrape_amount']))?$_REQUEST['scrape_amount']:0;
$succ = 0;
$fail = 0;
$sucMsg = isset($_REQUEST['sucMsg'])?$_REQUEST['sucMsg']:'';
$failMsg = isset($_REQUEST['failMsg'])?$_REQUEST['failMsg']:'';
$log = isset($_REQUEST['log'])?$_REQUEST['log']:'';
// menues
$menExist = 0;
$menNoKey = 0;
$succMenu = 0;
$failMenu = 0;

if(isset($_REQUEST['action']))
{
	if($_REQUEST['action'] == 'default_question_answer')
	{
		$Menu = Menu::getInstance($db); 
		
		$account_id = isset($_REQUEST['account_id'])?$_REQUEST['account_id']:'';
	
		if($account_id != '')
		{
			$pResults = $Site->get_domain_data_list('account',$account_id);
			foreach($pResults as $pRow) 
			{
				$keyword = trim($pRow['domain_keyword']);
	
				if(!empty($keyword))
				{
					if(!($set = $QA->check_qa_set($keyword,$scrape_amount)))
					{
							$qa_amount = $QA->scrape_qa($keyword,$scrape_amount);
							if(!empty($qa_amount))
							{
								$log .= "<br />".$pRow['domain_url']." - $keyword : found $qa_amount<br />";
								$succ++;
							}
							else
							{
								$log .= "<br />".$pRow['domain_url']." - $keyword : Not found<br />";
								$fail++;
							}
					}
					else
					{
						$log .= "<br />".$pRow['domain_url']." - $keyword : exist $set.<br />";
						$artExist++;
					}
					
				}
				else
				{
					$log .= "<br />*** ".$pRow['domain_url']." has no domain keyword ***<br />";
					$artNoKey++;
				}
				
			}
		}
			
	}
	
	else if($_REQUEST['action'] == 'extra_question_answer')
	{
		require_once('header.php');
		echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br>';
		$uploaddir = $config->uploadFolder;
		$uploadfile = $uploaddir . basename($_FILES['csvfile']['name']);
	
		if (move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploadfile)) 
		{
			$row = 1;
			$handle = fopen($uploadfile, "r");
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
			{
				if($row == 1 || empty($data) || empty($data[0]) || !isset($data[1]))
				{
					//skip first line
				}
				else
				{
					$keyword = isset($data[1])?trim($data[1]):'';
	
					if(!empty($keyword))
					{
						if(!($set = $QA->check_qa_set($keyword,$scrape_amount)))
						{
								$qanum = $QA->scrape_qa($keyword,$scrape_amount);
								if($qanum != 0)
								{
									echo $data[0]." - $keyword : found $qanum<br>";
									$succ++;
								}
								else
								{
									echo $data[0]." - $keyword : Not found<br>";
									$fail++;
								}
						}
						else
						{
							echo $data[0]." - $keyword : $set records exist<br>";
							$artExist++;
						}
					}
					else
					{
						$artNoKey++;
					}
				}
				$row++;
				ob_flush();
				flush();		
			}
		}
		echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
	}
	
	
	if($succ > 0)
		$sucMsg = $succ." domains populated successfully";
	if($fail > 0)
		$failMsg = $fail." domains not found questiones and answers ";
	if($artExist > 0)
		$failMsg .= "<br>".$artExist." domains already have default questiones and answers";
	if($artNoKey > 0)
		$failMsg .= "<br>".$artNoKey." domains don't have keyword set yet";
	
	// Ajax call
	if($_REQUEST['action'] == 'default_question_answer')
	{
		echo json_encode(array($sucMsg,$failMsg,$log));
		exit;
	}
}
else 
{
	require_once('header.php');
	$style = empty($log)?'style="display:none;"':'';
	echo '<div id="log" '.$style.'><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br><span id="logMsg">';
	echo $log;
	echo '</span><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
}
?>

<script>

function populate_default_question_answer(){
	var account_id = $('#account_id').val();
	var profile_id = $('#profile_id').val();
	var scrape_amount = $('#scrape_amount_qa').val();
	var action = 'default_question_answer';
	$.blockUI({ message: '<h1><img src="images/loading.gif" /> Just a moment...</h1>' }); 	
	 $.getJSON("scrape_question_answer.php", {action:action, account_id:account_id, profile_id:profile_id, scrape_amount:scrape_amount},function(data){			
			$.unblockUI();
			$('#logMsg').html(data[2]);
			$('#sucMsg').html(data[0]);
			$('#failMsg').html(data[1]);
			$('#log').show();
			$('#Msgs').show();
    });
		
}

</script>


<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<div id="Msgs" <?php if(empty($sucMsg) && empty($failMsg)) echo 'style="display:none;"'; ?>>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
						<div class="content" align="center">
				        <font color="Green" id="sucMsg"><?php echo $sucMsg;?></font><br />
						<font color="Red" id="failMsg"><?php echo $failMsg;?></font>
				
						</div>
						</td>
					</tr>
				</table>
				<br />
			</div>
			<!-- *** START MAIN CONTENTS  *** -->
			
			<span class="txtHdr">Populate Questiones and Answers </span>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
			<tbody>
			<tr>
				<td valign="top" colspan="2">
					
					<div id="tableData">
					<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
						<tbody><tr>
						<td align="left" class="cellHdr"><strong>Populate Default Questiones and Answers</strong></td>
                        <td align="left" class="cellHdr"><strong>Populate Extra Questiones and Answers</strong></td>
                        </tr>

						<tr class="alter1">
							<td valign="top" align="center" width="50%">	 
			                   
                               
                               <div id="export">
								<input type="hidden" value="default_question_answer" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td align="left" colspan="3">Select the Account:</td>
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
							
                                            <select name="account_id" id="account_id"></select>
											<br><font size="-2">Make sure the default keywords are set before populating</font>
                                            
                                            <br /><br/>Select scraping amount per domain
                                            <select id="scrape_amount_qa" name="scrape_amount" style="width:60px">
                                            <option value="5">5</option>
                                            <option value="10" selected="selected">10</option>
                                            <option value="20">20</option>                                           
											</select>
											<br>

										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"> <input type="submit" value="Populate Default" name="submit" onclick="populate_default_question_answer(); return false;"></td>
									</tr>
                                   
								</table>
		                       </div> <!-- scrape_directory form end -->
							</td>
                            
							<td valign="top" align="left">
		                       	<form enctype="multipart/form-data" action="scrape_question_answer.php" method="POST" id="data_table" name="import">
		                       	<input type="hidden" value="extra_question_answer" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">

									<tr>
										<td align="left">Select CSV file to upload:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="left" valign="top" width="300px"><br>
											<input name="csvfile" type="file" />
											<br>
											<font size="-2">header must contain column names: domain,keyword</font>

                                            <br /><br/>Select scraping amount per domain
                                            <select id="scrape_amount" name="scrape_amount" style="width:60px">
                                            <option value="5">5</option>
                                            <option value="10" selected="selected">10</option>
                                            <option value="20">20</option>                                           
											</select>
											<br>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"> <input type="submit" value="Populate Extra" name="submit" ></td>
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