<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once('config.php');
require_once('header.php');
$Menu = Menu::getInstance($db);

$artExist = 0;
$artNoKey = 0;
$domainExist = 0;
$succ = 0;
$fail = 0;
$sucMsg = '';
$failMsg = '';
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_menu')
{
	$uploaddir = $config->uploadFolder;
	$uploadfile = $uploaddir . basename($_FILES['csvfile']['name']);

	if (move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploadfile)) 
	{
		$Site = Site::getInstance($db);
		$row = 1;
		$handle = fopen($uploadfile, "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			if($row == 1)
			{
				//skip first line
			}
			else
			{
				$keyword = '';
				$domain_url = '';
				$domain_url = $data[0];
				$menu_name = $data[1];

				if($domain_id = $Site->check_domain_id($domain_url))
				{
					if($menu_name != '' && $domain_id != '')
					{
						if( $Menu->checkMenu($menu_name,$domain_id) != '')
						{
							$artExist++;
						}
						else
						{
							$menues = array('menu_domain_id' => $domain_id
											,'menu_name' => $menu_name
											,'menu_name_display' => $menu_name
											,'menu_article' => 1
											);
							$id = $Menu->save_menu($menues);
							
							if($id > 0)
								$succ++;
							else
								$fail++;
						}
					}
					else
					{
						$artNoKey++;
					}
				}
				else
				{
					$domainExist++;
				}
			}
			$row++;
		}
	}
}
if($succ > 0)
	$sucMsg = $succ." menu items added successfully";
if($fail > 0)
	$failMsg = $fail." menu items cannot be saved";
if($artExist > 0)
	$failMsg .= "<br>".$artExist." menu items are already in the system";
if($artNoKey > 0)
	$failMsg .= "<br>".$artNoKey." domains don't have domain/menu item set yet";
if($domainExist > 0)
	$failMsg .= "<br>".$domainExist." domains don't exist";
?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<?php
if($sucMsg != '' || $failMsg != '')
{
?>
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
<?php
}
?>
			<!-- *** START MAIN CONTENTS  *** -->
			
			<span class="txtHdr">Add Menu Items</span><p>
</p><table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
		<tbody>


			<tr>
				<td valign="top" colspan="2">
					
					<div id="tableData">
					<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
						<tbody><tr>
						<td align="left" class="cellHdr"><strong>Add Custom Menus</strong></td>

                        </tr>

						<tr class="alter1">
							<td valign="top" align="left">
		                        <form enctype="multipart/form-data" action="/add_custom_menus.php" method="POST" id="data_table" name="import">
		                       	<input type="hidden" value="add_menu" name="action"/>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">

									<tr>
										<td align="left">Select CSV file to upload:</td>
									</tr>
									<tr>
										<td width="33%">&nbsp;</td>
										<td align="left" valign="top" width="300px"><br>
											<input name="csvfile" type="file" />
											<br>

											<font size="-2">header must contain column names: domain,menu name</font>
										</td>
										<td width="33%">&nbsp;</td>
									</tr>
                                    <tr>
										<td align="center" colspan="3"> <input type="submit" value="Add Menu Items" name="submit"></td>
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