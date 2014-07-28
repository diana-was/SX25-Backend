<?php
/**
 * Domains
 * Author: Archie Huang on 07/05/2009
**/
require_once ('config.php');

$Layout = new Layout ();
$config = Config::getInstance ();
$curlObj = new SingleCurl ( '', 10 );
$Site = Site::getInstance ( $db );

$succ = $fail = 0;
$sucMsg = $failMsg = $data = '';
$output = array ();

if (isset ( $_REQUEST ['action'] ) && $_REQUEST ['action'] == 'check_keyword_ads') {
	if ($_FILES ['csvfile'] ['error'] == UPLOAD_ERR_OK && 	// checks for errors
	is_uploaded_file ( $_FILES ['csvfile'] ['tmp_name'] )) { // checks that file
	                                                         // is
	                                                         // uploaded
		
		$handle = fopen ( $_FILES ['csvfile'] ['tmp_name'], "r" );
		$row = $count = 1;
		
		$feedType = isset ( $_REQUEST ['feedtype'] ) ? trim ( $_REQUEST ['feedtype'] ) : "";
		
		while ( ($data = fgetcsv ( $handle )) !== FALSE ) {
			if ($row == 1) {
				// skip for first line
			} else {
				$domain = isset ( $data [0] ) ? trim_value ( $data [0] ) : "";
				$keyword = isset ( $data [1] ) ? trim_value ( $data [1] ) : "";
				$numberOfAds = download_pretending ( $domain, $keyword, $feedType, $succ, $fail );
				$output [] = array (
						0 => $domain,
						1 => $keyword,
						2 => $numberOfAds 
				);
			}
			$row ++;
		}
		
		foreach ( $output as $row ) {
			if ($count == 1) {
				$data = "Domain, Keyword, number of Ads\n";
			}
			$data .= implode ( ",", $row );
			$data .= "\n";
			$count ++;
		}
		$outputfilename = $_REQUEST ['action'] . date ( "d-m-Y" ) . ".csv";
		
		header ( 'Content-Type: text/plain; charset=ISO-8859-1' );
		header ( "Content-type: application/octet-stream" );
		header ( "Content-Disposition: attachment; filename=\"$outputfilename\"" );
		
		echo $data;
		die ();
	}

}

function trim_value($value) {
	$value = trim ( $value );
	$value = strtolower ( $value );
	return $value;
}

function download_pretending($site, $keyword, $feedType, &$msg, $debug = false) {
	global $curlObj;
	$config = Config::getInstance ();
	$msg = '';
	// send sitemap to site
	$params = array (
			'action' => 'numads',
			'keyword' => $keyword,
			'feed_type' => $feedType,
			'type' => 'js' 
	);
	
	// Site autentication settings
	$curlObj->setName ( 'super' );
	$curlObj->setPass ( $config->users ['super'] );
	$curlObj->useAuth ( true );
	// connect
	$curlObj->createCurl ( 'get', "http://$site/API.php", $params );
	$err = $curlObj->getHttpErr ();
	$status = $curlObj->getHttpStatus ();
	if ($debug) {
		$curlObj->displayResponce ();
		echo '<br>';
	}
	$resp = json_decode ( $curlObj->__toString () );
	
	$code = isset ( $resp->result->code ) ? $resp->result->code : 0;
	$msg = isset ( $resp->msg ) ? $resp->msg : (isset ( $resp->result->message ) ? $resp->result->message : 'unknown error');
	$result = isset ( $resp->ads ) ? $resp->ads : 0;
	
	if (($err != 0) || ($status != 200) || ($code != 200))
		return $msg;
	else
		return $result;
}
?>
<?php require_once ('header.php'); ?>
<div id="content">
			<!-- *** START MAIN CONTENTS  *** -->

				<form action="/tool_check_keywordsAds.php" method="POST"
					enctype="multipart/form-data" name="ufrm">
					<input type="hidden" name="action" value="check_keyword_ads">
					<table border="0" width="100%" cellspacing="0" cellpadding="3"
						id="boxGray">
						<tr>
							<td colspan="2" class="greenHdr"><b>Check keyword Ads</b></td>
							<td class="greenHdr"></td>
						</tr>
						<tr>
							<td colspan="3"><br> <br> <br></td>
						</tr>
						<tr class="alter1">
							<td width="20%"></td>
							<td width="10%" valign="middle" align="right">Keywords file:</td>
							<td width="70%"><input name="csvfile" type="file" /></td>
						</tr>
						<tr class="alter1">
							<td></td>
							<td valign="middle" align="right">Feed type:</td>
							<td><select name="feedtype">
									<option value="TZ">TrafficZ</option>
									<option value="TC">Traffic Scoring</option>
									<option value="VC">Valid Click</option>
									<option value="OM">OB Media</option>
									<option value="IS">Infospace</option>
							</select></td>
						</tr>
						<tr class="alter1">
							<td></td>
							<td colspan="2" align="left"><input type="submit"
								value="Get number of Ads" style="margin-left: 130px" /></td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2" align="left"><br> <br> <font size="-2"
								style="padding-left: 50px">Uploaded file must be in CSV format.
									Header: domain,keyword.</font><br> <br></td>
						</tr>
					</table>
				</form> <!-- *** END MAIN CONTENTS  *** -->


			</td>
			<td class="brdrR">&nbsp;</td>
		</tr>
	</table>
</div>
<?php require_once('footer.php'); ?>
