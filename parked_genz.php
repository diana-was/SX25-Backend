<?php
/**
 * Domains
 * Author: Diana DeVargas 
 * Create Date: 2011-04-19
 * Update Date:
**/
require_once('config.php');
$Layout = new LayoutParked();
$Site = ParkedDomain::getInstance($db);
$css = new cssMaker($db);
$queue  = new cssQueue($db);
			
$layoutSave = '';
$succ = 0;
$fail = 0;
$error = false;

$layoutName = isset($_REQUEST['layout'])?trim($_REQUEST['layout']):'';
$domains = isset($_REQUEST['domains'])?$_REQUEST['domains']:'';
$api = isset($_REQUEST['api'])?true:false;

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'parked_genz')
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
			// Get the random themes
			$themes = array();
			$domain_size = sizeof($domainList);
			$themesArray = $queue->getThemesArray();				
			if(sizeof($themesArray)>$domain_size){
				$rand_keys = array_rand($themesArray, $domain_size);
				
				if($domain_size==1)
				{
					$themes[] = $themesArray[$rand_keys];
				}
				else
				{
					for($i=0; $i<sizeof($rand_keys); $i++)
						$themes[] = $themesArray[$rand_keys[$i]];
				}							
			}else{
				$themes = $themesArray;
			}
			$themecount = 0;

			foreach($domainList as $key => $domName)
			{
				$domain_id = $Site->check_domain_id($domName);
				if ($domain_id)
					$Site->save_domain(array('domain_layout_id' => $layout_id),$domain_id);
				else 
					$Site->save_domain(array('domain_url' => $domName,'domain_layout_id' => $layout_id ));
				
				/* get theme */
				if($themecount==sizeof($themes))
					$themecount = 0;
				$theme = $themes[$themecount];
				$themecount++;
				
				if($css->setColorTheme($domName, $theme, $layout_id, 'parked'))
					$succ++;
				else 
					$fail++;
			}
			if ($fail == 0)
				$layoutSave = '<font color="green">OK: '.$succ.' domains generated</font>';
			else
				$layoutSave = '<font color="green">ERROR: '.$fail.' OK: '.$succ.' domains generated</font>';
		}
		elseif (count($domainList) > 0)
		{
			foreach($domainList as $key => $domName)
			{
				$domain_id = $Site->check_domain_id($domName);
				if ($domain_id)
					$Site->save_domain(array('domain_layout_id' => $layout_id),$domain_id);
				else 
					$Site->save_domain(array('domain_url' => $domName,'domain_layout_id' => $layout_id ));
			}
			$layoutSave = '<font color="green">OK: no css to generate</font>';
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
		<td valign="top" align="left"><span class="txtHdr">Parked GenZ</span></td>	
	</tr>
	<tr>
	<td valign="top" colspan="2">

		<form enctype="multipart/form-data" action="/parked_genz.php" method="POST" id="data_table" name="generate">
		<input type="hidden" value="parked_genz" name="action"/>
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

		<font size="-2">Use this feature to generate the GenZ css for parked domain</font>
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
