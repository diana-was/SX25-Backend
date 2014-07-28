<?php
/**
 * Create Twin domains in Park for Articles
 * Author: Diana De vargas 23/06/2011
**/
require_once('config.php');
require_once('header.php');

$Article = Article::getInstance($db);
$Site = Site::getInstance($db); 
$Parked = ParkedDomain::getInstance($db); 

$domainExist = 0;
$row = 0;
$succ = 0;
$sucMsg = '';
$failMsg = '';

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'create_twin')
{
	echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br><h2>Creating twin domains</h2><br>';
	$uploaddir = $config->uploadFolder;
	$uploadfile = $uploaddir . basename($_FILES['csvfile']['name']);

	if (move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploadfile)) 
	{
		$r = 0;
		$handle = fopen($uploadfile, "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			$r++;
			if($r == 1 || empty($data) || empty($data[0]))
			{
				continue; //skip first line and empty lines
			}
			else
			{
				$domain_origin 	= strtolower(trim($data[0]));
				$domain_copy 	= strtolower(trim($data[1]));
				echo "Origin: $domain_origin, Copy: $domain_copy";
				if(!empty($domain_origin) && !empty($domain_copy) && ($domain_origin != $domain_copy))
				{
					if($id = $Parked->save_twin_domain($domain_origin, $domain_copy))
					{
						echo " : OK<br>";
						$succ++;
					}
					else
						echo " : Error saving twin domains! <br>";
					$row++;
				}
				else 
					echo " : Name Error! <br>";
			}
		}
	}
	else 
		$failMsg .= "Error Reading file.<br>";
	if($succ > 0)
		$sucMsg .= "$row domains found. $succ inserted.";
	else
		$failMsg .= "$row domains found. NO twin domains created.";
	echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
}

?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<?php if($sucMsg != '' || $failMsg != '') : ?>
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
			
			<span class="txtHdr">Parked Twin Domains</span>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
			<tbody>
			<tr>
				<td valign="top" colspan="2">
					
					<div id="tableData">
					<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
						<tbody><tr>
						<td align="left" class="cellHdr"><strong>Create Twin Domains</strong></td>
                        </tr>

						<tr class="alter1">
							<td valign="top" align="center" width="50%">	 
			                    <form enctype="multipart/form-data" action="/parked_twin_domains.php" method="POST" name="import" id="import">
								<input type="hidden" value="create_twin" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td align="left" colspan="3">Select CSV file to upload:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="center" valign="top" width="300px"><br>
											<input name="csvfile" type="file" />
											<br>
											<font size="-2">header must contain column name: origin, copy</font>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"><input type="submit" value="Create" name="submit"></td>
									</tr>
									<tr>
										<td align="left" colspan="3"><br>A twin "copy" domain is a domain without any articles that displays all the articles from the origin domain as their own. If an article is link to the twin "copy" domain it lost the twin capability and only displays its own articles.</td>
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