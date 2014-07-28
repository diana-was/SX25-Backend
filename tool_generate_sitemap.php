<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/

require_once ('config.php');
require_once ('header.php');
require_once ('api/lib/WSAclient.php');
require_once ('api/lib/WSAParser.php');

$Layout = new Layout ();
$config = Config::getInstance ();
$curlObj = new SingleCurl ( '', 10 );
$indexDomain = IndexDomain::getInstance ( $db );
$Site = Site::getInstance ( $db );

$succ = $fail = 0;
$sucMsg = $failMsg = $filename = '';
$toolParamArray = array ();

if (isset ( $_REQUEST ['action'] ) && $_REQUEST ['action'] == 'add') {
	if ($_FILES ['csvfile'] ['error'] == UPLOAD_ERR_OK && 	// checks for errors
	is_uploaded_file ( $_FILES ['csvfile'] ['tmp_name'] )) { // checks that file is
	                                                       // uploaded
		echo '<div id="log"><br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br>';
		$handle = fopen ( $_FILES ['csvfile'] ['tmp_name'], "r" );
		$row = 1;
		$countPage = 0;
		$temp = $strPage = $outXML = '';
		while ( ($data = fgetcsv ( $handle )) !== FALSE ) {
			if ($row == 1) {
				// skip for first line
			} else {
				$domain = isset ( $data [0] ) ? trim_value ( $data [0] ) : '';
				$pageURL = isset ( $data [1] ) ? trim_value ( $data [1] ) : '';
				$date_created = date ( "Y-m-d\TH:i:sP" );
				$lastmod = date ( "F d, Y H:i a" );
				
				if (! empty ( $domain ) && ! empty ( $pageURL )) {
					if ($domain != $temp) {
						// Close the file when domain changes
						if (! empty ( $temp )) {
							$outXML .= $strPage . '</urlset>';
							$pathfile = $config->uploadFolder . $filename . '.xml';
							
							// get indexing domain by domain_url
							$toolParamArray ['url'] = $temp;
							$toolParamArray ['checkOtherSubdomain'] = 1;
							
							save_sitemap ( $toolParamArray, $outXML, $countPage, $pathfile );
							// reset number of page in CSV file
							$countPage = 0;
						}
						// Initiate the xml and variable
						$outXML = '<?xml version="1.0" encoding="UTF-8"?>
									<?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>
									<!-- generator="sx25-ironhead" -->
									<!-- generated-on="' . $date_created . '" -->
									<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
									<url>
										<loc>http://' . $domain . '/index.php</loc>
										<lastmod>' . $lastmod . '</lastmod>
										<changefreq>weekly</changefreq>
										<priority>1.0</priority>
									</url>';
						echo "start: $domain<br>";
						$temp = $domain;
						$layout = $Layout->getLayoutDefaultModule ( $domain );
						$filename = str_replace ( '.', '_', $domain );
						$strPage = '';
					}
					
					$pageURL = str_replace ( '{module}', ($layout ['layout_default_module'] ? $layout ['layout_default_module'] : 'ARTICLE'), $pageURL );
					
					$strPage .= '<url>
										<loc>' . $pageURL . '</loc>
										<lastmod>' . $lastmod . '</lastmod>
										<changefreq>weekly</changefreq>
										<priority>0.5</priority>
								</url>';
					$countPage ++;
				}
			}
			$row ++;
			ob_flush ();
			flush ();
		}
		$outXML .= $strPage . '</urlset>';
		$pathfile = $config->uploadFolder . $filename . '.xml';
		$toolParamArray ['url'] = $temp;
		$toolParamArray ['checkOtherSubdomain'] = 1;
		save_sitemap ( $toolParamArray, $outXML, $countPage, $pathfile );
		echo '<br><input type="button" value="Hide" onclick="$(\'#log\').hide();"><br><br></div>';
	}

}

function trim_value($value) {
	$value = trim ( $value );
	$value = strtolower ( $value );
	return $value;
}

function write_and_send_file($domain, $pathfile, $outXML, &$succ, &$fail) {
	$file = fopen ( $pathfile, 'w+' );
	$wr = fwrite ( $file, $outXML );
	fclose ( $file );
	if ($wr) 	// send sitemap to site
	{
		if (download_pretending ( $domain, $pathfile, $resp )) {
			$succ ++;
			echo "sent: $domain -> $resp<br>";
		} else {
			$fail ++;
			echo "error: $domain -> $resp<br>";
		}
	} else {
		$fail ++;
		echo "error: $domain -> can NOT write file<br>";
	}
	return;
}

function download_pretending($site, $pathfile, &$msg, $debug = false) {
	global $curlObj;
	$config = Config::getInstance ();
	$msg = '';
	// send sitemap to site
	$params = array (
			'type' => 'js',
			'action' => 'sitemap',
			'sitemap' => '@' . $pathfile 
	);
	
	// Site autentication settings
	$curlObj->setName ( 'super' );
	$curlObj->setPass ( $config->users ['super'] );
	$curlObj->useAuth ( true );
	// connect
	$curlObj->createCurl ( 'post', "http://$site/API.php", $params );
	$err = $curlObj->getHttpErr ();
	$status = $curlObj->getHttpStatus ();
	if ($debug) {
		$curlObj->displayResponce ();
		echo '<br>';
	}
	$resp = json_decode ( $curlObj->__toString () );
	$code = isset ( $resp->result->code ) ? $resp->result->code : 0;
	$msg = isset ( $resp->msg ) ? $resp->msg : (isset ( $resp->result->message ) ? $resp->result->message : 'unknown error');
	
	if (($err != 0) || ($status != 200) || ($code != 200))
		return false;
	else
		return true;
}
/*
 * @author	khoa.nguyen 
 * @desc	get outXML from SEO Tool 
 * @param	array(domain, checkOtherSubdomain)
 */
function get_indexed_pages($toolParamArray) {
	$config = Config::getInstance ();
	$WSAclient = new WSAclient ( $config->WSA_USER_ID, $config->WSA_API_KEY );
	$result = $WSAclient->newReport ( $config->TOOL_ID, $toolParamArray, $config->WSA_SUBSCRIPTION_ID, 'xml', 'EN' );
	unset ( $WSAclient );
	
	ob_start ();
	$xml = simplexml_load_string ( $result );
	
	$date_created = date ( "Y-m-d\TH:i:sP" );
	$lastmod = date ( "F d, Y H:i a" );
	$i = 0;
	$outXML = '<?xml version="1.0" encoding="UTF-8"?>
				<?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>
				<!-- generator="sx25-ironhead" -->
				<!-- generated-on="' . $date_created . '" -->
				<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
	foreach ( $xml->output->list->entry as $entry ) {
		if ($i == 0) {
			$outXML .= '<url>
						<loc>http://' . $toolParamArray ['url'] . '/index.php</loc>
						<lastmod>' . $lastmod . '</lastmod>
						<changefreq>weekly</changefreq>
						<priority>1.0</priority>
						</url>';
		} else {
			$outXML .= '<url>
						<loc>' . $entry->URL . '</loc>
						<lastmod>' . $lastmod . '</lastmod>
						<changefreq>weekly</changefreq>
						<priority>0.5</priority>
						</url>';
		}
		$i ++;
	}
	$outXML .= '</urlset>';
	$status = $xml->status;
	unset ( $xml );
	ob_end_clean ();
	
	return array (
			'status' => $status,
			'outXML' => $outXML,
			'pages'	 => $i,
	);
}
/*
 * @author	khoa.nguyen 
 * @desc	save pages that get from SEO Tool into database
 * @param	$toolParamArray - array(domain, checkOtherSubdomain) 
 * 			$outXML - XML string to save file 
 * 			$countPage - the number of CSV file 
 * 			$pathfile - the path to save file
 */
function save_sitemap($toolParamArray, $outXML, $countPage, $pathfile) {
	global $db;
	global $succ;
	global $fail;
	$config = Config::getInstance ();
	$indexDomain = IndexDomain::getInstance ( $db );
	$Site = Site::getInstance ( $db );
	
	$domain = $toolParamArray ['url'];
	$totalPage = google_indexed_number ( $domain );
	$indexingDomain = $indexDomain->getRowIndexDomain ( $domain );
	$indexingID = isset($indexingDomain ['indexing_id'])?$indexingDomain ['indexing_id']:0;
	$dataSave = array ();
	if (empty ( $indexingDomain )){
		$rowDomain = $Site->get_domain_info_name ( $domain );
		$dataSave ['indexing_domain_id'] = $rowDomain ['domain_id'];
	}
	if (empty ( $indexingDomain ) || ($indexingDomain['indexing_results'] < $totalPage) || ($indexingDomain['indexing_results'] < $countPage)) 
	{
		if ($totalPage > $countPage) 
		{
			echo 'Domain have been indexed by google<br/>';
			// generate sitemap with pages that get from SEO Tool and update number of page into database
			$dataAPI = get_indexed_pages ( $toolParamArray );
			if ($dataAPI ['status'] == 1)
			{
				$dataSave ['indexing_results'] = $dataAPI ['pages'];
				$indexDomain->saveIndexDomain ( $dataSave, $indexingID );
				write_and_send_file ( $domain, $pathfile, $dataAPI ['outXML'], $succ, $fail );
			} else {
				echo '$domain : can\'t get  indexed pages from google. Maybe the API reached the daily limit.<br/>';
			}
		}
		else //get the sitemap from the csv
		{ 
			$dataSave ['indexing_results'] = $countPage;
			$indexDomain->saveIndexDomain ( $dataSave, $indexingID );
			write_and_send_file ( $domain, $pathfile, $outXML, $succ, $fail );
		}
	} 
	else {
		echo "$domain: existing sitemap ".$indexingDomain['indexing_results']; 
	}
	
}
/*
 * @author	khoa.nguyen 
 * @desc		get the number of google indexed 
 * @param	string domain
 */
function google_indexed_number($domain) {
	global $curlObj;
	
	$domain = trim_value ( $domain );
	$url = 'http://www.google.com/search?hl=en&lr=&ie=UTF-8&q=site:' . $domain . '&filter=0';
	$curlObj->createCurl ( 'get', $url );
	
	$resultpage = $curlObj->__toString ();
	$doc = new simple_html_dom ();
	$doc->load ( $resultpage );
	
	$content = $doc->find ( '#resultStats', 0 )->plaintext;
	$content = explode ( ' ', $content );
	if ($content [0] == 'About') {
		return ( int ) $content [1]; // the number of google indexed is greater 10
	} else {
		return ( int ) $content [0];
	}

}

if ($succ > 0)
	$sucMsg = $succ . " sitemap created successfully";
if ($fail > 0)
	$failMsg = $fail . " sitemap cannot be created";
?>
<div id="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="brdrL">&nbsp;</td>

			<td valign="top" id="main">
						<?php
						if ($sucMsg != '' || $failMsg != '') {
							?>
<table border="0" cellpadding="0" cellspacing="0" width="100%"
					id="boxGray">
					<tr>
						<td valign="top"><div class="blueHdr">System Message</div>
							<div class="content" align="center">
								<font color="Green"><?php echo $sucMsg;?></font><br /> <font
									color="Red"><?php echo $failMsg;?></font>

							</div></td>
					</tr>
				</table> <br />
<?php
						}
						?>
			<!-- *** START MAIN CONTENTS  *** -->

				<form action="/tool_generate_sitemap.php" method="POST"
					enctype="multipart/form-data" name="ufrm">
					<input type="hidden" name="action" value="add">
					<table border="0" width="100%" cellspacing="0" cellpadding="3"
						id="boxGray">
						<tr>
							<td colspan="2" class="greenHdr"><b>Generate Sitemap</b></td>
							<td class="greenHdr"></td>
						</tr>
						<tr>
							<td colspan="3"><br> <br> <br></td>
						</tr>
						<tr class="alter1">
							<td></td>
							<td valign="middle" align="center">Select local CSV file to
								upload:</td>
							<td></td>
						</tr>
						<tr class="alter1">
							<td></td>
							<td valign="middle" align="center"><input name="csvfile"
								type="file" /><br> <br></td>
						</tr>
						<tr class="alter1">
							<td></td>
							<td align="center" valign="middle"><input type="submit"
								value="Generate sitemap"></td>
							<td></td>
						</tr>
						<tr>
							<td colspan="3" align="center"><br> <br> <font size="-2">Uploaded
									file must be in CSV format. Header: domain,page url.</font><br>
								<br></td>
						</tr>
					</table>
				</form> <!-- *** END MAIN CONTENTS  *** -->


			</td>
			<td class="brdrR">&nbsp;</td>
		</tr>
	</table>
</div>
<?php require_once('footer.php'); ?>
