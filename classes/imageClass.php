<?php

/*	APPLICATION:	PrincetonIT SX2
	FILE:			imageClass.php
	DESCRIPTION:	Class to extract image online
	CREATED:		12 Jan 2011 by Gordon Ye
	UPDATED:									
*/

class Image extends  Model
{
	private $_curlObj;
	private $_db; 
	private static $_Object;
	
	public function __construct(db_class $db)
	{
		$this->_curlObj = new SingleCurl();
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
	
	public function checkImage($domain_id, $image_location){
		$sql = "SELECT count(*) FROM images WHERE image_domain_id = '$domain_id' and image_location = '".$image_location."' ";
		if($this->_db->select_one($sql) > 0){
			return true;
		}else{
			return false;
		}
	}
	
	//copy image into imagelibrary, save information into images, image name save as (domain_id)+(_)+(location).jpg, add one field 'keyword' into images table
	public function setImage($path, $domain_id, $location, $keyword, $uploadFile=false)
	{
		$Lib = ImageLibrary::getInstance($this->_db);
		$id = $Lib->saveImage($path, $keyword, $uploadFile);

		if ($id)
		{
			$Filename = $Lib->get_image_filename($id);
			
			$sql = "SELECT * FROM images WHERE image_domain_id='$domain_id' AND image_location='$location'";
			if($this->_db->select_one($sql)){
				$sql = "UPDATE images SET image_keyword='$keyword', image_name='$Filename' WHERE image_domain_id='$domain_id' AND image_location='$location'";
				$this->_db->update_sql($sql);
			}else{
				$sql = "INSERT INTO images (image_domain_id, image_location, image_keyword, image_name) VALUES ('$domain_id', '$location', '$keyword', '$Filename')";
				$this->_db->insert_sql($sql);
			}
			return $Lib->imageLibrary.$Filename;
		}else{
			return ''; //return $uploadFile?$path["name"]:$path;
		}
	}
	
	public function setImageLibrary($image_library_id, $domain_id, $location)
	{
		$Lib = ImageLibrary::getInstance($this->_db);

		if ($lib = $Lib->get_images_library($image_library_id) )
		{
			$sql = "SELECT * FROM images WHERE image_domain_id='$domain_id' AND image_location='$location'";
			if($this->_db->select_one($sql)){
				$sql = "UPDATE images SET image_keyword='".$lib['image_library_keyword']."', image_name='".$lib['image_library_name']."' WHERE image_domain_id='$domain_id' AND image_location='$location'";
				$this->_db->update_sql($sql);
			}else{
				$sql = "INSERT INTO images (image_domain_id, image_location, image_keyword, image_name) VALUES ('$domain_id', '$location', '".$lib['image_library_keyword']."', '".$lib['image_library_name']."')";
				$this->_db->insert_sql($sql);
			}
			return $Lib->imageLibrary.$lib['image_library_name'];
		}else{
			return false;
		}
	}
	
	public function getDomainImage($domain_id)
	{
		$sql = "SELECT count(*) FROM `images` where `image_domain_id`=".$domain_id." ";
		$amount = $this->_db->select_one($sql);
		return $amount;
	}

	public function listDomainImages($domain_id)
	{
		$output = array();
		
		$sql = "SELECT * FROM images WHERE image_domain_id = '$domain_id' ORDER  BY image_name";
		
		$pResults = $this->_db->select($sql);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}
	
	// gordon
	public function getImageLocationArray($domain_id)
	{
		$output = array();
		
		$sql = "SELECT image_location FROM images WHERE image_domain_id = '$domain_id' ORDER  BY image_name";
		
		$pResults = $this->_db->select($sql);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row['image_location'];
		}
		return $output;
	}

	public function link_image($id,$domain_id)
	{
		if(!empty($id) && !empty($domain_id))	
		{
			$sql = "UPDATE images set image_domain_id = '$domain_id' WHERE image_id = '".$id."'";
			return $this->_db->update_sql($sql);
		}	
		return false;
	}
	
	public function unlink_images($domain_id)
	{
		if(!empty($domain_id))	
		{
			$sql = "DELETE FROM images WHERE image_domain_id = '".$domain_id."'";
			return $this->_db->delete($sql);
		}	
		return false;
	}
	
	public function unlink_images_keyword($domain_id, $keyword)
	{
		if(!empty($domain_id))	
		{
			$sql = "DELETE FROM images WHERE image_domain_id = '".$domain_id."' AND image_keyword = '".$keyword."'";
			return $this->_db->delete($sql);
		}	
		return false;
	}

	public function count_images() 
	{
		$output = array();
		
		$sql = "SELECT domain_id, domain_url, count(*) as images FROM images join domains on domain_id = image_domain_id GROUP BY domain_id, domain_url ORDER  BY domain_url";
		
		$pResults = $this->_db->select($sql);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}
	// gordon
	public static function imgNameSwitch($imgArray)
	{
		$returnA = array();
		
		foreach($imgArray as $k=>$v){
			switch(strtoupper(trim($v))):
				case 'LANDING_IMG': 
					$returnA[] = 'landing_pic';
					break;
				case 'RESULTS_IMG': 
					$returnA[] = 'result_pic';
					break;
				case 'M_IMG_1': 
					$returnA[] = 'menu_pic_1';
					break;
				case 'M_IMG_2': 
					$returnA[] = 'menu_pic_2';
					break;
				case 'M_IMG_3': 
					$returnA[] = 'menu_pic_3';
					break;
				case 'M_IMG_4': 
					$returnA[] = 'menu_pic_4';
					break;
				case 'M_IMG_5': 
					$returnA[] = 'menu_pic_5';
					break;
				case 'M_IMG_6': 
					$returnA[] = 'menu_pic_6';
					break;
				case 'M_IMG_7': 
					$returnA[] = 'menu_pic_7';
					break;
				case 'M_IMG_8': 
					$returnA[] = 'menu_pic_8';
					break;
				case 'M_IMG_9': 
					$returnA[] = 'menu_pic_9';
					break;
				case 'M_IMG_10': 
					$returnA[] = 'menu_pic_10';
					break;
				case 'IMG_1': 
					$returnA[] = 'page_pic_1';
					break;
				case 'IMG_2': 
					$returnA[] = 'page_pic_2';
					break;
				case 'IMG_3': 
					$returnA[] = 'page_pic_3';
					break;
				case 'IMG_4': 
					$returnA[] = 'page_pic_4';
					break;
				case 'IMG_5': 
					$returnA[] = 'page_pic_5';
					break;
				case 'IMG_6': 
					$returnA[] = 'page_pic_6';
					break;
				case 'IMG_7': 
					$returnA[] = 'page_pic_7';
					break;
				case 'IMG_8': 
					$returnA[] = 'page_pic_8';
					break;
				case 'IMG_9': 
					$returnA[] = 'page_pic_9';
					break;
				case 'IMG_10': 
					$returnA[] = 'page_pic_10';
					break;
				default:
					break;
			endswitch;					
		}
		return $returnA;
	}
	
}

?>
