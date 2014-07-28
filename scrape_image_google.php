<?php
require_once('config.php');

$action 	= $_REQUEST['action'];
$keyword	= isset($_REQUEST['keyword']) ?  $_REQUEST['keyword'] : exit();
$limit		= isset($_REQUEST['quantity'])?$_REQUEST['quantity']:1; 
$callback	= isset($_GET['callback'])?$_GET['callback']:'';
$debug		= isset($_GET['debug'])?true:false;
$user_agent	= "MSIE";
if($action == 'loadGooglePic'){	
		$start = isset($_REQUEST['start']) ? $_REQUEST['start']:1;
			
		$param = array ( 'q' 	=> urlencode($keyword),
						 'hl' 	=> 'en',
						 'gbv' 	=> '2',
						 'biw' 	=> '1920',
						 'bih' 	=> '887');
		
		if($limit==1)
		{
			$param['tbs']	= 'isch:1,imgo:1,islt:qsvga,isz:i';
			$param['sa']  	= '1';
			$param['aq']  	= 'f';
			$param['aqi']  	= 'g10';
			$param['aql']  	= '';
			$param['oq']  	= '';
			$param['gs_rfai']= '';
		}
		elseif($limit>1)
		{
			$param['tbs']	= 'isch:1,imgo:1,islt:qsvga,isz:m';
			$param['source']= 'lnt';
			$param['sa']  	= 'X';
			$param['ei'] 	= 'd-tYTJfkJ4X9cLeY8J0J';
		    $param['ved'] 	= '0CAgQpwU';
		}
		
		$curlObj = new SingleCurl();
		$curlObj->useragent = $user_agent;
		$curlObj->createCurl('get','http://www.google.com/images',$param);
		$htmldata = $curlObj->__toString();
			
		$pos = strpos($htmldata, 'href="/imgres?imgurl=');
		if($pos!==false)
			$urlpart = explode('href="/imgres?imgurl=', $htmldata);
		else
			$urlpart = explode('/imgres?imgurl=', $htmldata);
		
		for($i=$start; $i<($start+$limit); $i++){
			$path[$i-1] = array('id'=>$i, 'image'=>extract_image_link($urlpart[$i]));
		}
		if ($debug)
		{
			echo '<pre>';
			print_r($path);
			echo '</pre>';
		}
		echo $callback . '(' .json_encode($path). ')';
		exit;
}

function extract_image_link($source){
		$pos = strpos($source, '&amp;imgrefurl=');
		if($pos!==false)
			$img = explode('&amp;imgrefurl=', $source);
		else
			$img = explode('&imgrefurl=', $source);
			
		$file_headers = @get_headers($img[0]);
		if($file_headers[0] == 'HTTP/1.1 404 Not Found'){ 
			$path = 'http://webezines.kwithost.com/default'.mt_rand(0, 5).'.jpg';		
		}else{
			$path = $img[0];	
		}
		return $path;
}

?>
