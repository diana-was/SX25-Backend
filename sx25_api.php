<?php
/**
 * Get data from sx25
 * Author: Diana DeVargas 
 * Create Date: 2012-01-06
 * Update Date:
**/
include_once("config.php");


$outputXML = '';
$outputCSV = '';

$domains = isset($_REQUEST['domains'])?$_REQUEST['domains']:'';
$api = isset($_REQUEST['api'])?true:false;

if(!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'get_sx25':
			$Site = Site::getInstance($db);
			$domainList = $Site->extractTextarea($domains);
			$Site->get_domain_data_list();
			$searchType = empty($domainList)?'':'domain';
			$search = empty($domainList)?'':$domainList;
			$domainData = $Site->get_domain_data_list($searchType,$search);
			$count = 1;
			foreach ($domainData as $row)
			{
				if ($count == 1)
				{
					$keys = array_keys($row);
					$outputCSV .= 'line,'.implode(',',$keys).PHP_EOL;
				}
				$outputCSV .= $count.",".implode(',',$row).PHP_EOL;
				$outputXML .= "<domain line=\"$count\">".PHP_EOL;
				foreach ($row as $key => $val)
					$outputXML .= "<$key>".htmlspecialchars($val)."</$key>".PHP_EOL;
				$outputXML .= '</domain>'.PHP_EOL;
				$count++;
			}
			break;
		case 'get_parked':
			$Site = ParkedDomain::getInstance($db);
			$domainList = $Site->extractTextarea($domains);
			$Site->get_domain_data_list();
			$searchType = empty($domainList)?'':'domain';
			$search = empty($domainList)?'':$domainList;
			$domainData = $Site->get_domain_data_list($searchType,$search);
			$count = 1;
			foreach ($domainData as $row)
			{
				if ($count == 1)
				{
					$keys = array_keys($row);
					$outputCSV .= 'line,'.implode(',',$keys).PHP_EOL;
				}
				$outputCSV .= $count.",".implode(',',$row).PHP_EOL;
				$outputXML .= "<domain line=\"$count\">".PHP_EOL;
				foreach ($row as $key => $val)
					$outputXML .= "<$key>".htmlspecialchars($val)."</$key>".PHP_EOL;
				$outputXML .= '</domain>'.PHP_EOL;
				$count++;
			}
			break;
		case 'remove_directory':
			$directory = Dty::getInstance($db);
			$domainList = Site::extractTextarea($domains);
			$keyword = isset($_REQUEST['keyword'])?$_REQUEST['keyword']:'';
			$delete = isset($_REQUEST['delete'])?true:false;
			foreach ($domainList as $domain)
			{
				if ($directory->remove_directory($domain, $keyword, $delete))
					$outputXML = "<domain removed=\"ok\" name=\"$domain\" />";
				else
					$outputXML = "<domain removed=\"error\" name=\"$domain\" />";
			}
			break;
		case 'add_business' :
			$Business = Business::getInstance ( $db );
			$site_url	= isset ( $_REQUEST ['siteURL'] ) ? trim_value ( $_REQUEST ['siteURL'] ) : '';
			$title		= isset ( $_REQUEST ['title'] ) ? trim_value ( $_REQUEST ['title'] ) : '';
			$keyword	= isset ( $_REQUEST ['keyword'] ) ? trim_value ( $_REQUEST ['keyword'] ) : '';
			$position	= isset ( $_REQUEST ['position'] ) ? trim_value ( $_REQUEST ['position'] ) : '';
			$desc		= isset ( $_REQUEST ['description'] ) ? trim_value ( $_REQUEST ['description'] ) : '';

			if (! empty ( $site_url ) && ! empty ( $title ) && ! empty ( $keyword ) && ! empty ( $desc )) {
				if (! $Business->checkBusiness ( $site_url, $title, $keyword )) {
					$business = array (
							'business_siteurl' => $site_url,
							'business_title' => $title,
							'business_keyword' => $keyword,
							'business_position' => $position,
							'business_description' => $desc
					);
					$id = $Business->save_business ( $business );
					if ($id)
						$outputXML = '<result value="ok" detail="Inserted" />';
					else
						$outputXML = '<result value="error" detail="Insert fail" />';
				}
				else
					$outputXML = '<result value="ok" detail="Exist" />';
			}
			else
					$outputXML = '<result value="error" detail="Missing data" />'."s.$site_url t.$title k.$keyword d.$desc";
			break;
	}
}
if ($api)
{ 
	header("Content-type: text/xml");
	echo "<?xml version='1.0' encoding='ISO-8859-1'?>".PHP_EOL;
	echo '<domains>'.PHP_EOL;
	echo $outputXML;
	echo '</domains>'.PHP_EOL;
	exit ();
}
function trim_value($value) {
	$value = trim ( $value );
	$value = strtolower ( $value );
	return $value;
}

require_once('header.php');
?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
			
			<!-- *** START MAIN CONTENTS  *** -->
			<?php if(!empty($outputCSV)) : ?>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" id="boxGray">
				<tr>
					<td valign="top"><div class="blueHdr">System Message</div>
					<div class="content" align="left">
				        <?php echo "<pre>$outputCSV</pre>";?>
					</div>
					</td>
				</tr>
			</table>
			<?php endif; ?>			
			
<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="tablePop">
<tbody>
	<tr>
		<td valign="top" align="left"><span class="txtHdr">SX25 API</span></td>	
	</tr>
	<tr>
	<td valign="top" colspan="2">

		<form enctype="multipart/form-data" action="/sx25_api.php" method="POST" id="data_table" name="generate">
		<div id="tableData">
		<table width="100%" cellspacing="1" cellpadding="0" border="0" class="data_table" id="dt1">
			<tbody>
			<tr>
				<td align="left" class="cellHdr"><strong>Domain List</strong></td>
			</tr>
			<tr class="alter1">
				<td valign="middle" align="center">	 
	        	Action :  
                   	<select class="inputSelect" name="action" >
                   	<option value="" selected="selected">Select one ...</option>
                   	<option value="get_sx25" >SX25 Domain</option>
                   	<option value="get_parked" >Parked Domains</option>
                	</select>
	        		<br>
	        	</td>
			</tr>
			<tr class="alter1">
				<td valign="middle" align="center">	 
					<br>Domain List:<br>
					<textarea cols="30" rows="9" name="domains" style="width: 250px;"></textarea>
					<br><font size="-2">Empty if required all domains</font><br>
				</td>
			</tr>
			<tr class="alter2"><td align="center"><input type="submit" value="Generate" name="submit"/></td></tr>
			</tbody>
		</table>
		</div>
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
?>
