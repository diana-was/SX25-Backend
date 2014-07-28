<?php
require_once('config.php');

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$keyword = isset($_REQUEST['keyword'])?$_REQUEST['keyword']:'';
$user_agent = "MSIE";


if($action == 'scrapGooglePics'){	
		$start = isset($_REQUEST['start']) ? $_REQUEST['start']:1;
		
		$param = array ( 'q' 	=> urlencode($keyword),
						 'hl' 	=> 'en',
						 'gbv' 	=> '2',
						 'biw' 	=> '1920',
						 'bih' 	=> '887',
						 'tbs' 	=> 'isch:1,imgo:1,islt:qsvga,isz:m',
						 'source'=> 'lnt',
						 'sa' 	=> 'X',
						 'ei'	=> 'd-tYTJfkJ4X9cLeY8J0J',
					     'ved' 	=> '0CAgQpwU');
		
		$curlObj = new SingleCurl();
		$curlObj->useragent = $user_agent;
		$curlObj->createCurl('get','http://www.google.com/images',$param);
		$htmldata = $curlObj->__toString();
		
		$urlpart = explode('/imgres?imgurl=', $htmldata);
		
		$path = array();
		for($i=$_REQUEST['start']; $i<($_REQUEST['start']+10); $i++){
			$url = extract_image_link($urlpart[$i]);
			if (!empty($url))
				$path[] = $url;
		}
				
		if(isset($_REQUEST['cross_domain']) && $_REQUEST['cross_domain']=='true') 
			echo $_GET['callback'].'('.json_encode($path).')';
		else 
			echo json_encode($path);
		
		exit;
}	


if($action == 'loadGooglePics')
{
		$path = array();
		$start = isset($_REQUEST['start']) ? $_REQUEST['start']:'';
		$Library = ImageLibrary::getInstance($db);
		$imgs = $Library->getGoogleImageSearch($keyword,8,$start);
		foreach($imgs as $i){
			if (!empty($i['content_photo_src']))
				$path[] = $i['content_photo_src'];
		}
				
		if(isset($_REQUEST['cross_domain']) && $_REQUEST['cross_domain']=='true') 
			echo $_GET['callback'].'('.json_encode($path).')';
		else 
			echo json_encode($path);
		
		exit;
}

if($action == 'loadBingPics'){	
		$start = isset($_REQUEST['start']) ? $_REQUEST['start']:1;
		
		$param = array ( 'q' 		=> urlencode($keyword).' filterui:imagesize-small',
						 'FORM'		=> 'I4IR',
						 'format'	=> 'htmlraw',
						 'first' 	=> $start);
		
		$curlObj = new SingleCurl('',15);
		$curlObj->useragent = $user_agent;
		$curlObj->createCurl('get','http://www.bing.com/images',$param);
		$htmldata = $curlObj->__toString();
				
		$urlpart = explode('imgurl:&quot;', $htmldata);
			
		$data = array();
		$c=1;
		$xc=1; //prevent infinite loop
		
		$start = isset($_REQUEST['start'])?$_REQUEST['start']:1;
		for($i=$start; $i<($start+10); $i++){
			$urlonly = isset($urlpart[$i])?explode('&quot;', $urlpart[$i]):array();
			$image_url = isset($urlonly[0])?$urlonly[0]:'';
			if (!empty($image_url))
			{
				$data[$c]= $image_url;
				$c++;
			}
		}
		if(isset($_REQUEST['cross_domain']) && $_REQUEST['cross_domain']=='true') 
			echo $_GET['callback'].'('.json_encode($data).')';
		else 
			echo json_encode($data);
		
		exit;
}

if($action == 'getImageFromLibrary')
{	
		$start 	= isset($_REQUEST['start']) ? $_REQUEST['start']:1;
		$limit 	= isset($_REQUEST['limit']) ? $_REQUEST['limit']:0;
		$aproved= isset($_REQUEST['aproved']) ? $_REQUEST['aproved']:1;
		
		$data = array();
		$ImageLibrary = ImageLibrary::getInstance($db);
		$result =  $ImageLibrary->getKeywordImages($keyword,$limit,false,'',$start,$aproved);
		
		foreach($result as $k){			
			$data[] = $k;
		}
		echo json_encode($data);	
		exit;
}

// set image from Image Library
if($action=='setImageFromLibrary'){
	if (!isset($_REQUEST['domain_id']) || !isset($_REQUEST['location'])  || !isset($_REQUEST['image_library_id']))
	{
	    echo 'Parameter missing';
		exit;
	}
	$domain_id 	= $_REQUEST['domain_id'];
	$Site = Site::getInstance($db);
	$data = $Site->get_domain_info($domain_id);
	
	if ($data) 
	{
	    $image_library_id	= $_REQUEST['image_library_id'];
		$location 			= $_REQUEST['location'];
	    
		$Image = Image::getInstance($db);
		echo $Image->setImageLibrary($image_library_id, $domain_id, $location);
	} 
	else
		echo 'Domain Not found.';
	exit;
}

if($action=='setImage'){

	if (!isset($_REQUEST['domain_id']) || !isset($_REQUEST['location'])  || !isset($_REQUEST['path']))
	{
	    echo 'Parameter missing';
		exit;
	}
	$domain_id 	= $_REQUEST['domain_id'];
	$Site = Site::getInstance($db);
	$data = $Site->get_domain_info($domain_id);
	
	if ($data) 
	{
	    $image_url 	= $_REQUEST['path'];
		$location 	= $_REQUEST['location'];
		$keyword 	= (isset($_REQUEST['keyword']) && !empty($_REQUEST['keyword']))?$_REQUEST['keyword']:$data['domain_keyword'];
	    
		$Image = Image::getInstance($db);
		echo $Image->setImage($image_url, $domain_id, $location, $keyword);
	} 
	else
		echo 'Domain Not found.';
	exit;
}

if($action=='upload_image'){
     
	if (!isset($_REQUEST['domain_id']) || !isset($_REQUEST['image_location'])  || !isset($_FILES["upimage"]["name"]))
	{
	    echo 'Parameter missing';
		exit;
	}
		
	$domain_id 	= $_REQUEST['domain_id'];
	$Site = Site::getInstance($db);
	$data = $Site->get_domain_info($domain_id);
	
	if ($data) 
	{
		$location 	= $_REQUEST['image_location'];
		$keyword 	= (isset($_REQUEST['kw']) && !empty($_REQUEST['kw']))?$_REQUEST['kw']:$data['domain_keyword'];
	
		$Image = Image::getInstance($db);
		$Filename = $Image->setImage($_FILES["upimage"], $domain_id, $location, $keyword, true);
				
		if($Filename != $_FILES["upimage"]["name"]){
			echo '<b style="color:green;">'.$Filename.' is already uploaded. Please refresh page now.</b>';
		}else{
			echo 'fail copying file.';
		}
	}
	else
		echo 'Domain Not found.';
	exit;
}

if($action=='upload_background_image'){
	
	$keyword = (isset($_REQUEST['kw']) && !empty($_REQUEST['kw']))?$_REQUEST['kw']:'background';
	$kwName = preg_replace('/\s+/', '', ucwords($keyword));
	$kwName = substr ( $kwName, 0, 30);
	
	$format = substr($_FILES["upimage"]["name"], -3, 3);
	$name = $kwName.'_'.date("Ymd_His");
	
	if($format == 'jpg'||$format == 'JPG'){
		$Filename = $name.'.jpg';
	}
	else if($format == 'png'){
		$Filename = $name.'.png';
	}
	else if($format == 'gif'){
		$Filename = $name.'.gif';
	}else{
		echo "Upload image system is invalid file.";
		exit;
	}
	
	$result = move_uploaded_file($_FILES["upimage"]["tmp_name"], $config->sx25cssImageFolder.$Filename);
	if($result){
		$sql = "INSERT INTO css_structure (css_structure_type, css_structure_description, css_structure_active) VALUES ('background', '$keyword', '0')";
		$structure_id = $db->insert_sql($sql);
		if($structure_id){
			$size = $_FILES['upimage']['size'];
			$css = "body{ background: url(layout_images/$Filename)  repeat;  background-attachment: fixed;}";
			$sql = "INSERT INTO css (css_part, type, size, description, css, value, css_structure_id) VALUES ('background', 'image', '$size', '$keyword', '$css', '$Filename', $structure_id)";
			$db->insert_sql($sql);			
		}else{
			echo "Insert in css_structure fail";
			exit;
		}		
	}else{
			echo "Error copying the file to ".$config->sx25cssImageFolder.$Filename;
			exit;
	}
	exit;
}


function extract_image_link($source){		
		//$img = explode('&imgrefurl=', $source);	
		$img = explode('&amp;imgrefurl=', $source);	
		$file_headers = @get_headers($img[0]);
		if($file_headers[0] == 'HTTP/1.1 404 Not Found'){ 
			$img = explode('&imgrefurl=', $source);
			$file_headers2 = @get_headers($img[0]);
			if($file_headers2[0] == 'HTTP/1.1 404 Not Found')
				$path = 'http://webezines.kwithost.com/default'.mt_rand(0, 5).'.jpg';		
		}else{
			$path = $img[0];	
		}
		return $path;
}

function loadPics($keyword)
{
	$s_keyword	= str_replace(" ", "+", str_replace("-", "+", $keyword));
	$Filename	= $config->imageLibraryPath.$s_keyword.".jpg";
	$Imagename	= $config->imageLibrary.$s_keyword.".jpg";

	if(!file_exists($Filename))
	{
		$param = array ( 'q' 		=> urlencode($keyword).' filterui:imagesize-small',
						 'FORM'		=> 'I4IR');
		
		$curlObj = new SingleCurl();
		$curlObj->timeout = 5;
		$curlObj->createCurl('get','http://www.bing.com/images',$param);
		$data = $curlObj->__toString();
		
		
		$startresult = explode('<span class="batch">', $data);
		$urlpart = explode('furl=', $startresult[1]);
		$urlonly = explode('"', $urlpart[1]);
		$image_url = $urlonly[0];


		if(copy($image_url, $Filename))
		{
			return $Imagename;
		}
		else
		{
			unlink($Filename);
			return $image_url;
		}
	}
	else
		return $Imagename;

}



?>
