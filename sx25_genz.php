<?php
/**
 * Re generate css for SX25 domains using base genz themes
 * Author: Diana DeVargas 
 * Create Date: 2011-06-06
 * Update Date:
**/
require_once('config.php');
$Layout = new Layout();
$Site = Site::getInstance($db);
$css = new cssMaker($db);
$queue  = new cssQueue($db);

$layoutSave = '';
$succ = 0;
$fail = 0;
$failDomains = '';
$error = false;

$layoutName = isset($_REQUEST['layout'])?trim($_REQUEST['layout']):'';
$domains = isset($_REQUEST['domains'])?$_REQUEST['domains']:'';
$api = isset($_REQUEST['api'])?true:false;

if(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'genz' || $_REQUEST['action'] == 'fix-path'))
{
	// Validate errors
	if (empty($Layout))
	{ 
		$error = true;
		$layoutSave = '<font color="red">Error: Layout Name is Missing!!</font>';
	}
	
	$layout_id = $Layout->check_layout_id($layoutName);
	if (empty($layout_id) || ($layout_id == 0)) 
	{
		$error = true;
		$layoutSave = '<font color="red">Error: Layout name does not exist!</font>';
	}

	if (!$error) 
	{
		$layoutGenZ = substr($layoutName, -4)=='base'; //(stripos($layoutInfo['layout_landing'], '{CSS_LIBRARY}{DOMAIN}.css') !== false) || (stripos($layoutInfo['layout_result'], '{CSS_LIBRARY}{DOMAIN}.css') !== false)
		
		$domainList = $Site->extractTextarea($domains);
		
		if ($layoutGenZ && (count($domainList) > 0))
		{
			$layoutInfo = $Layout->get_layout_info($layout_id);
			
			// Get the random themes in case is needed
			$themes = $queue->getThemesArray();				
			$themecount = 0;
			
			foreach($domainList as $key => $domName)
			{
				$domain = $Site->get_domain_info_name($domName);
				if ($domain) 
				{
					if ($domain['domain_layout_id'] == $layout_id)
					{
						/* get theme */
						$theme = $domain['domain_theme_id'];
						if (empty($theme))
						{
							if($themecount==sizeof($themes))
								$themecount = 0;
							$theme = $themes[$themecount];
							$themecount++;
						}
						
						switch ($_REQUEST['action'])
						{
							case 'fix-path':
								$filename = $config->sx25cssFolder.$domain['domain_url'] .".css";
								if (file_exists($filename))
								{
									$cssStr = file_get_contents($filename);
									$folderDir = 'sx25themes/'.$layoutInfo['layout_folder'];
									$cssStr = preg_replace ( '/\((\s*)\/'.$layoutInfo['layout_folder'].'\//i' , '(/'.$folderDir.'/', $cssStr);
									$cssStr = preg_replace ( '/\'(\s*)\/'.$layoutInfo['layout_folder'].'\//i' , "'/".$folderDir.'/', $cssStr);
									$cssStr = preg_replace ( '/"(\s*)\/'.$layoutInfo['layout_folder'].'\//i' , '"/'.$folderDir.'/', $cssStr);
									$handle = fopen($filename, "w+");
									$numbytes = fwrite($handle, $cssStr);
									fclose($handle);
									$succ++;
									break;
								}						
							default :
								if($css->setColorTheme($domName, $theme, $layout_id, 'sx25'))
									$succ++;
								else
								{ 
									$failDomains .= ", $domName css error";
									$fail++;
								}
								break;
						}
					}
					else
					{
						$failDomains .= ", $domName different layout";
						$fail++;
					}
				}
				else 
					{
						$failDomains .= ", $domName not exit in database";
						$fail++;
					}
			}
			if ($fail == 0)
				$layoutSave = '<font color="green">OK: '.$succ.' domains css generated</font>';
			else
				$layoutSave = '<font color="green">ERROR: '.$fail.' OK: '.$succ.' domains css generated</font><br>'.$failDomains.'<br>';
		}
	} 
} 
	
if ($api)
{ ?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
	<title><?php echo $systemName;?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	<body>
	<?php echo $layoutSave; ?>
	</body>
	</html>
<?php 
}
else
{
	require_once('header.php');
?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<!-- *** START MAIN CONTENTS  *** -->
			<?php if(!empty($layoutSave)) : ?>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
				<tr>
					<td valign="top"><div class="blueHdr">System Message</div>
					<div class="content" align="center">
				        <?php echo $layoutSave;?>
					</div>
					</td>
				</tr>
			</table>
			<?php endif; ?>			
			
<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
<tbody>
	<tr>
		<td valign="top" align="left"><span class="txtHdr">Regenerate CSS for GenZ Domains</span></td>	
	</tr>
	<tr>
	<td valign="top" colspan="2">

		<form enctype="multipart/form-data" action="/sx25_genz.php" method="POST" id="data_table" name="generate">
		<input type="hidden" value="genz" name="action"/>
		<div id="tableData">
		<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
			<tbody>
			<tr>
				<td align="left" class="cellHdr"><strong>Domain List</strong></td>
			</tr>
			<tr class="alter1">
				<td valign="middle" align="center">	 
	        	Layout :  
                   	<select class="inputSelect" name="layout" >
                   	<option value="" selected="selected">Select one ...</option>
					<?php
					$layouts = $Layout->get_layouts();
					foreach ($layouts as $pRow) : 
						if (substr($pRow['layout_name'], -4)=='base') : ?> 
						<option value="<?php echo $pRow['layout_name'];?>"><?php echo $pRow['layout_name'];?></option>
					<?php endif; 
					endforeach;?>
                	</select>
	        		<br>
	        	</td>
			</tr>
			<tr class="alter1">
				<td valign="middle" align="center">	 
					<textarea cols="30" rows="9" name="domains" style="width: 250px;"></textarea>
				</td>
			</tr>
			<tr class="alter2"><td align="center"><input type="submit" value="Generate" name="submit"/></td></tr>
			</tbody>
		</table>
		</div>

		<font size="-2">Use this feature to re-generate the GenZ css for sx25 domains using a selected theme</font>
		</form>

		<br><br><br><br>

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
} ?>
