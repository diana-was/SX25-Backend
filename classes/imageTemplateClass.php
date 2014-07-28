<?php

/*	APPLICATION:	PrincetonIT SX2
	FILE:			imageTemplateClass.php
	DESCRIPTION:	Class to extract image online
	CREATED:		12 Jan 2011 by Gordon Ye
	UPDATED:									
*/

class imageTemplate extends Image 
{
	protected  $cssImageFolder;
	protected  $cssImageLink;
	private $_curlObj;
	private $_db; 
	private static $_Object;
	
	public function __construct(db_class $db)
	{
		$config = Config::getInstance();
		$this->cssImageFolder	= $config->sx25cssImageFolder;
		$this->cssImageLink		= $config->sx25cssImageLink;
		$this->_curlObj 		= new SingleCurl();
		$this->_db 				= $db;
		self::$_Object 			= $this;
		return self::$_Object;
	}
	
	public static function getInstance(db_class $db){
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
	}
		
	public function getBingImageSearch($keyword = '', $start = '1')
	{
		$BingData = array();	
		$start = isset($_REQUEST['start']) ? $_REQUEST['start']:1;
		
		$validclickurl = "http://www.bing.com/images";

		$this->_curlObj->createCurl('get', "http://www.bing.com/images", array( 'q' => ' '.$keyword.' filterui:imagesize-desktop','FORM' => 'I4IR','format' => 'htmlraw','first' => $start,));
		$result = $this->_curlObj->__toString();
		//$this->_curlObj->displayResponce();var_dump($result);
		$urlpart = explode('imgurl:&quot;', $result);
		$c=1;
		$xc=1; //prevent infinite loop
			
				for($i=$_REQUEST['start']; $i<($_REQUEST['start']+10); $i++){
					$urlonly = explode('&quot;', $urlpart[$i]);
					$image_url = $urlonly[0];
					$BingData[$c]= $image_url;
					$c++;
					$xc++;
				}		
		return $BingData;
	}
	
	public function loadDatabasePics($start = '0', $type='background')
	{
		$data = array();
		//$sql = "SELECT * FROM css WHERE css_part='".$type."' AND type='image' ORDER BY css_id ASC LIMIT 10 OFFSET ".$start;
		$sql = "SELECT c.*, s.css_structure_active FROM css as c join css_structure as s on (c.`css_structure_id`=s.`css_structure_id`) WHERE c.css_part='".$type."' AND c.type='image' ORDER BY s.css_structure_active DESC LIMIT 10 OFFSET ".$start;
		$result = $this->_db->select($sql);
		while ($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			/*$css = $row['css'];
			$csschop = explode('url("', $css);
			$csschop1 = explode('")', $csschop[1]);
			$image = $csschop1[0];*/
			$image = $row['value'];
			$data[$row['css_id']] = array('id' => $row['css_id'], 'image' => $image);
		}
		return $data;
	}	
	
	public function setImage($domain, $path, $location, $keyword)
	{	
		$imagesize = $this->getRemoteFileSize($path); 
		$existing = $this->verifySameImage($keyword, $imagesize);
		
		if(!$existing){
			$format = strtolower(substr($path, -3, 3));
			if($format == 'jpg'){
				$Filename = $keyword.'_'.time().'.jpg';
			}
			else if($format == 'png'){
				$Filename = $keyword.'_'.time().'.png';
			}
			else if($format == 'gif'){
				$Filename = $keyword.'_'.time().'.gif';
			}else{
				return "image system has error";
			}				
			$content = file_get_contents($path);
			$result = file_put_contents($this->cssImageFolder.$Filename, $content);
		}else{
			$Filename = $existing;	
		}
		
		$css = new cssMaker($this->_db);
		$css->appendCss($domain, $location, $keyword, $Filename, $existing);
		return $this->cssImageLink.$Filename;	
	}
	
	public function extract_image_link($source){
		$img = explode('&imgrefurl=', $source);
			
		$file_headers = @get_headers($img[0]);
		if($file_headers[0] == 'HTTP/1.1 404 Not Found'){ 
			$path = $this->cssImageLink.'default'.mt_rand(0, 5).'.jpg';		
		}else{
			$path = $img[0];	
		}
		return $path;
	}
	
	public function getRemoteFileSize($filename)
	{
		$chGetSize = curl_init();		
		//curl_setopt($chDownloadFilePrep, CURLOPT_CUSTOMREQUEST, 'GET');	 
		// Set the url we're requesting
		curl_setopt($chGetSize, CURLOPT_URL, $filename);		 
		// Set a valid user agent
		curl_setopt($chGetSize, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11");		 
		// Don't output any response directly to the browser
		curl_setopt($chGetSize, CURLOPT_RETURNTRANSFER, true);		 
		// Don't return the header (we'll use curl_getinfo();
		curl_setopt($chGetSize, CURLOPT_HEADER, false);		 
		// Don't download the body content
		curl_setopt($chGetSize, CURLOPT_NOBODY, true);	 
		// Run the curl functions to process the request
		$chGetSizeStore = curl_exec($chGetSize);
		$chGetSizeError = curl_error($chGetSize);
		$chGetSizeInfo = curl_getinfo($chGetSize);
		// Close the connection
		curl_close($chGetSize);// Print the file size in bytes	 
		return $chGetSizeInfo['download_content_length'];	
	}
	
	private function verifySameImage($keyword, $imagesize)
	{
		$max = (int)$imagesize+10;
		$min = (int)$imagesize-10;
		$sql = 'SELECT css_id,value from css WHERE size>'.$min.' AND size<'.$max.' AND description="'.$keyword.'" AND type="image" and css_structure_id is null ';
		$pResults = $this->_db->select($sql);
		if($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			$filename = $pRow['value'];
			$exist = file_exists( $this->cssImageFolder.$filename);
			if (!$exist)
			{
				$cQuery = "DELETE FROM css WHERE css_id = '".$pRow['css_id']."' ";
				$this->_db->delete($cQuery);
			}
		}
		else
			$filename = false;
		
		if($filename && $exist)
			return $filename;
		else
			return 0;
	}
	
}

?>
