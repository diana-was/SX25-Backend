<?php

/*	APPLICATION:	PrincetonIT SX2
	FILE:			imageClass.php
	DESCRIPTION:	Class to extract image online
	CREATED:		12 Jan 2011 by Gordon Ye
	UPDATED:									
*/

class ImageLibrary extends  Model
{
	private $_curlObj;
	private $_db; 
	private	  $defaultImage = 'Default_0.jpg';
	private	  $defaultAvatar = 'Default_1.jpg';
	protected $maxUses = 3;
	protected $imageLibraryPath;
	protected $imageLibrary;
	private static $_Object;
	
	public function __construct(db_class $db)
	{
		$config = Config::getInstance();
		$this->imageLibraryPath = $config->imageLibraryPath;
		$this->imageLibrary = $config->imageLibrary;
		$this->_curlObj = new SingleCurl('',15);
		$this->_db = $db;
		self::$_Object = $this;
		return self::$_Object;
	}
	
	public static function getInstance(db_class $db)
	{
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
	}
	
	public function checkImage($image_name)
	{
		$sql = "SELECT * FROM images_library WHERE image_library_name = '".$image_name."' ";
		if($this->_db->select_one($sql)){
			return true;
		}else{
			return false;
		}
	}


	public function get_images_library($image_library_id) 
	{
		$pQuery = "SELECT * FROM images_library WHERE image_library_id = '".$image_library_id."' LIMIT 1";
		$pResults = $this->_db->select($pQuery);
		if($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			return $pRow;
		}
		else
		{
			return false;
		}
	}	

	public function get_image_filename($image_library_id) 
	{
		$Query = "SELECT image_library_name FROM images_library WHERE image_library_id = '$image_library_id' ";

		return $this->_db->select_one($Query);
	}
	
	public function getGoogleImageSearch($keyword = '', $per_page = 4, $start='')
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();

		$googleData = array();	
		$keyword = str_replace(" ", "+", str_replace("-", "+", $keyword));
		
		$params = array("v" => '1.0', 
				 "rsz" => $per_page,
				 "hl" => 'en',
				 "q" => $keyword,
				 "imgsz" => 'medium',
				 "key" => 'ABQIAAAA-AcshcNcyntuwOULxcNwkRTmkHv8EVFAxCToSfP2qbF2JkGCshT8Dzl3JPFuQ4AVijNqBpj31BRGDg',
			);
		$start = !empty($start) ? array('start' => $start):array();
		$params = array_merge($params,$start);				
			
		$this->_curlObj->createCurl('get','http://ajax.googleapis.com/ajax/services/search/images',$params);
		//$this->_curlObj->displayResponce();
		$resultJosn=$this->_curlObj->__toString();
		$result = json_decode( $resultJosn);	
		if ( !empty( $result->responseData->results ) ) {			
			foreach ( $result->responseData->results as $res ) {
				$googleData[] = array(
					'content_title' => $res->title,
					'content_link' =>  $res->originalContextUrl,
					'content_photo_src' =>  $res->unescapedUrl,					
					'content_source' =>  'Google'		
				);
			}	
		}
		
		return $googleData;
	}
	
	
	//copy image into imagelibrary, save information into images_library, image name save as keyword+(_)+#.jpg
	public function setImage($path, $keyword)
	{
		$id = $this->saveImage($path, $keyword);
		if ($id)
		{
			$fileName = $this->get_image_filename($id);
			return $this->imageLibrary.$fileName;
		}else{
			return $path;
		}
	}

	public function saveImage($path, $keyword, $uploadFile=false, $approved=1)
	{
		$origName = $uploadFile?$path["name"]:$path;
		$format = strtolower(substr($origName, -3, 3));
		$fName = str_replace(' ','',ucwords($keyword));
		$fName = substr ( $fName, 0, 30);
		$dumpurl = $this->imageLibraryPath.$fName;
		$approved = is_int($approved)?$approved:1;
		
		$i = 0;
		if($format == 'jpg'){
			while(file_exists($dumpurl."_$i.jpg") || $this->checkImage($fName."_$i.jpg")){
				$i++;
			}
			$Filename =  $fName."_$i.jpg";
		}
		else if($format == 'png'){
			while(file_exists($dumpurl."_$i.png") || $this->checkImage($fName."_$i.png")){
				$i++;
			}
			$Filename = $fName."_$i.png";
		}
		else if($format == 'gif'){
			while(file_exists($dumpurl."_$i.gif") || $this->checkImage($fName."_$i.gif")){
				$i++;
			}
			$Filename = $fName."_$i.gif";
		}else{
			return false;
		}
		
		if ($uploadFile)
		{
			$result = move_uploaded_file($path["tmp_name"], $this->imageLibraryPath.$Filename);
		}
		else
		{
			if ($content = file_get_contents($path))
			{
				$result  = file_put_contents($this->imageLibraryPath.$Filename, $content);
			}
			else 
			{
				return false;
			}
		}
		
		if($result){
			$sql = "INSERT INTO images_library (image_library_keyword, image_library_name, image_library_approved) VALUES ('$keyword', '$Filename', $approved)";
			$id = $this->_db->insert_sql($sql);
			return $id;
		}else{
			return false;
		}
	}

	
	public function replaceImage($path, $image_library_id)
	{
		global $user;
		if (!isset($user)) return false;
		
		$origName = $this->get_image_filename($image_library_id);
		
		if ($origName)
		{
			$dumpurl = $this->imageLibraryPath.$origName;

			if (isset($path["tmp_name"]))
			{
				if(file_exists($dumpurl))
					unlink($dumpurl);
				$result = move_uploaded_file($path["tmp_name"], $dumpurl);
				$sql = "UPDATE images_library set image_library_approved = 3, image_library_approved_user = '".$user->userID."', image_library_update_date = now() WHERE image_library_id = '$image_library_id'";
				$this->_db->update_sql($sql);
				return true;
			}
			else
			{
				if ($content = file_get_contents($path))
				{
					if(file_exists($dumpurl))
						unlink($dumpurl);
					$result  = file_put_contents($dumpurl, $content);
					$sql = "UPDATE images_library set image_library_approved = 2, image_library_approved_user = '".$user->userID."', image_library_update_date = now() W WHERE image_library_id = '$image_library_id'";
					$this->_db->update_sql($sql);
					return true;
				}
				else echo "path no existe $path";
			}
		}	
		else echo "no in library $image_library_id";
		return false;
	}
	
	public function getSXImage($fromRecord,$recordPerPage,$sortyQuery,$search='',$search_type='')
	{
		switch ($search_type)
		{
			case 'domain' : $searchSQL = " and image_library_name in (select image_name from images where image_domain_id = '$search') ";
							break; 
			case 'keyword': $searchSQL = " and image_library_keyword like '$search%' ";
							break; 
			default   	  : $searchSQL = '';
							break; 
		}
		
		$output = array();
		$imagesQuery = "SELECT image_library_id, image_library_keyword, image_library_name, image_library_approved, SUM(CASE IFNULL(image_id,0) WHEN 0 THEN 0 ELSE 1 END) AS domains
						FROM images_library  
						LEFT JOIN images  ON image_name = image_library_name
						WHERE image_library_approved >= 1 AND image_library_name IS NOT NULL $searchSQL
						GROUP BY image_library_id, image_library_keyword, image_library_name ".$sortyQuery." LIMIT ".$fromRecord.",".$recordPerPage;
		$pResults =  $this->_db->select($imagesQuery);
		while($row =  $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}
	
	public function count_total_images($search='',$search_type='') 
	{
		switch ($search_type)
		{
			case 'domain' : $Query = "SELECT count(*) FROM images_library JOIN images  ON image_name = image_library_name and image_domain_id = '$search' WHERE image_library_approved >= 1 and image_library_name is NOT NULL";
							break; 
			case 'keyword': $Query = "SELECT count(*) FROM images_library WHERE image_library_keyword like '$search%' and image_library_approved >= 1 and image_library_name is NOT NULL";
							break; 
			default   	  : $Query = "SELECT count(*) FROM images_library WHERE image_library_approved >= 1 and image_library_name is NOT NULL";
							break; 
		}

		$count = $this->_db->select_one($Query);
		return $count;
	}
	
	public function count_using_image($image_name) 
	{
		$Query = "SELECT count(*) FROM images WHERE image_name = '$image_name' ";
		return $this->_db->select_one($Query);
	}
	
	function count_keywords_images() 
	{
		$directory = array();
		
		$directoryQuery = "SELECT image_library_keyword as keyword, count(*) as images, SUM(CASE IFNULL(image_id,0) WHEN 0 THEN 0 ELSE 1 END) as `domains using it`  FROM images_library LEFT JOIN images  ON image_name = image_library_name WHERE image_library_approved >= 1 GROUP BY image_library_keyword ORDER BY image_library_keyword ";
		$pResults = $this->_db->select($directoryQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$directory[] = $pRow;		
			}
		}
	
		return $directory;
	}
	
	public function remove_image($id)
	{
		$imageName = $this->get_image_filename($id);
		$using = $this->count_using_image($imageName);
		if ($using > 0)
		{
			$Query = "UPDATE images_library SET image_library_approved = 0 WHERE image_library_id = $id";
			$this->_db->update_sql($Query);
		}
		else						
		{
			$Query = "DELETE FROM images_library WHERE image_library_id = $id";
			$this->_db->delete($Query);
			$dumpurl = $this->imageLibraryPath.$imageName;
			unlink($dumpurl);
		}	
	}
	
	public function remove_image_byName($image_name)
	{
		$using = $this->count_using_image($image_name);
		if ($using > 0)
		{
			$Query = "UPDATE images_library SET image_library_approved = 0  WHERE image_library_name = '".$image_name."'";
			$this->_db->update_sql($Query);
		}
		else						
		{
			$Query = "DELETE FROM images_library WHERE image_library_name = '".$image_name."'";
			$this->_db->delete($Query);
			$dumpurl = $this->imageLibraryPath.$imageName;
			unlink($dumpurl);
		}	
	}
	
	public function updateKeyword($id, $keyword)
	{
		$Query = "UPDATE images_library SET image_library_keyword='$keyword' WHERE image_library_id=$id";							
		return $this->_db->update_sql($Query);
	}

	public function getKeywordImages ($keyword,$limit=0,$random=true,$exclude_domains='',$start='',$approved=1)
	{
		$keyword	= trim($keyword);
		$sortyQuery = $random?' Order by image_library_approved DESC, RAND() ':' Order by image_library_approved DESC, image_library_id ';
		$maxRecords = ($limit > 0)?" LIMIT $limit ":'';
		$exclude	= empty($exclude_domains)? '':" and image_domain_id not in ($exclude_domains) ";
		$output 	= array();
		$offset 	= (!empty($start) && !empty($maxRecords))?" OFFSET $start ":'';
		$approved 	= is_numeric($approved)?$approved:1;
        $whereApproved = ($approved==1)?"image_library_approved >= 1":"image_library_approved = $approved";
		
		$imagesQuery = "SELECT image_library_id, image_library_name
						FROM images_library  
						LEFT JOIN images ON image_name = image_library_name 
						WHERE image_library_keyword = '$keyword' AND $whereApproved AND image_library_name IS NOT NULL $exclude
						GROUP BY image_library_id, image_library_name
						HAVING SUM(CASE IFNULL(image_id,0) WHEN 0 THEN 0 ELSE 1 END) < ".$this->maxUses." $sortyQuery $maxRecords $offset ";
		$pResults =  $this->_db->select($imagesQuery);
		while($row =  $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}

		$kws = explode(' ',$keyword);
		if (empty($output) && (count($kws) > 1) && empty($offset)) {
			if (count($kws) > 2)
			{
				array_pop($kws);
				$keyword1 = implode ( ' ', $kws );
				$keyword2 = $keyword1.'%';
			} 
			else
			{
				$keyword1 = $kws[0];
				$keyword2 = $kws[1];
			}
			$imagesQuery = "SELECT image_library_id, image_library_name
							FROM images_library  
							LEFT JOIN images ON image_name = image_library_name 
							WHERE (image_library_keyword like '$keyword1' or image_library_keyword like '$keyword2') AND $whereApproved AND image_library_name IS NOT NULL $exclude
							GROUP BY image_library_id, image_library_name
							HAVING SUM(CASE IFNULL(image_id,0) WHEN 0 THEN 0 ELSE 1 END) < ".$this->maxUses." $sortyQuery $maxRecords $offset ";
			
			$pResults =  $this->_db->select($imagesQuery);
			while($row =  $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
				$output[] = $row;
			}
		}
		return $output;
	}
	
	public function getImagesToReplace()
	{
		$output = array();
		$imagesQuery = "SELECT * FROM images_library WHERE image_library_approved = 0 AND image_library_name IS NOT NULL ORDER BY image_library_keyword ";
		$pResults =  $this->_db->select($imagesQuery);
		while($row =  $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}
	
	public function approveImage($id,$approved){
		global $user;
		if (!isset($user)) return false;
		
		$approved = empty($approved)?1:$approved;
		$aQuery = "UPDATE images_library SET image_library_approved = '$approved', image_library_approved_user = '".$user->userID."',image_library_update_date = now()  WHERE image_library_id = '".$id."'";
		if ($return = $this->_db->update_sql($aQuery))
			return $return;
		else
			return false;
	}

	protected  function __getValDefaultImage()
	{
		return $this->imageLibrary.$this->defaultImage;
	}

	protected function __getValDefaultAvatar()
	{
		return $this->imageLibrary.$this->defaultAvatar;
	}
	
	
}
?>
