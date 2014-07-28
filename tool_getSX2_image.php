<?php
require_once('config.php');
require_once('header.php');

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';

if($action=='sx2'){
	$limit 	= isset($_REQUEST['limit'])?$_REQUEST['limit']:10;
    
	$Image = ImageLibrary::getInstance($db);
	$images = getSX2Image($db,$limit);
	//$sx2ImageFolder = "C:\\PHPprojects\\SX25backend\\sx25themes\\";	
	$sx2ImageFolder = "/data/tmp/imagelibrary/";	
	foreach ($images as  $sx2Image)
	{
		$image_url = $sx2ImageFolder.$sx2Image['image_name'];
		$keyword = $sx2Image['image_keyword'];
		if ($Image->saveImage($image_url, $keyword))
		{
			echo "COPIED: $image_url,  keyword:$keyword <br>";
			remove_image($db,$sx2Image['image_id'],$image_url);
		}
		else 
		{
			echo "Error saving image............................,".$sx2Image['image_name']."<br>";
		}
		 ob_flush();
		 flush();
	}
	echo "DONE!";
}

function getSX2Image($db,$limit=10){
	$limit = is_numeric($limit)?$limit:10;
	$limit = ($limit == 0)?'':" LIMIT $limit ";
	$output = array();
	$imagesQuery = "SELECT * FROM images_sx2 WHERE image_copied = 0 $limit ";
	$pResults =  $db->select($imagesQuery);
	while($row =  $db->get_row($pResults, 'MYSQL_ASSOC')){
		$output[] = $row;
	}
	return $output;
}

function remove_image($db,$id,$dumpurl){
	$Query = "UPDATE images_sx2 SET image_copied=1 WHERE image_id = $id";
	$db->update_sql($Query);
	unlink($dumpurl);
}
	
	
?>
