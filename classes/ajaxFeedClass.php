<?php

class AjaxFeed extends  Model
{
	private $_db;
	private $_curlObj;
	private $_cache;
	protected $_callback;
	private $_aid = array('kdzL7q_V34H6yHJL_EoJkgpgnBuQr4mSfnzHCYVKPQBRZiho0H20JBTJgWEEJRKBMGPZpw--','TtRrWCbV34FtJ.RuiRhx9s.tvUI6jfCcIMBS9v1nQHt2RWXW3gfKRnAU54a_rMiI');
	private static $_Object;	// Created a stacic object if the object can be shared or called in multiple places and can be unic 
	
    /**
     * constructor : set up the static object
     *
     * @return static object
     */
	public function __construct(db_class $db,$callback='')
	{
		$this->_curlObj = new SingleCurl();
		$this->_cache   = Cache::getInstance($db);
		$this->_db = $db;
		$this->_callback = $callback;
		self::$_Object = $this;
		return self::$_Object;
	}
	
	
    /**
     * Get the class static object
     *
     * @return self
     */
    public static function getInstance($dbObj=null,$callback='') 
    {
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($dbObj,$callback);
    	}	
    	return self::$_Object;
    }
    
	public function yahooNews($keyword,$debug=false)
	{
		if($data = $this->_cache->getcache($keyword, 48)) {	
		   return $this->_callback.'(' .$data. ')';
		}
		
		$rt = array();
		$itg = $this->_aid[rand()%2];			
		//$validclickurl = "http://search.yahooapis.com/NewsSearchService/V1/newsSearch?appid=".$aid."&query=".urlencode($keyword)."&results=4&language=en";
		$this->_curlObj->createCurl('get','http://search.yahooapis.com/NewsSearchService/V1/newsSearch',array("appid" =>$itg, "query" => urlencode($keyword), 'results'=>4, 'language'=> 'en'));
		if ($debug)
			$this->_curlObj->displayResponce();
		
		$data = $this->_curlObj->__toString();
		$rss 		= new xml2Array();
		$result  	= $rss -> parse($data);
		$result2 	= isset($result[0]['children'])?$result[0]['children']:array();
		
		foreach($result2 as $row){
			if (isset($row['children']) && is_array($row['children']))
			{ 
				$date = date('F j, Y, g:i a',$row['children'][7]['tagData']);
				$img = isset($row['children'][9]['children'][0]['tagData'])?$row['children'][9]['children'][0]['tagData']:'';
				if($img=='')
					$img = 'http://webezines.kwithost.com/default'.mt_rand(0, 5).'.jpg';
				 $rt[] = array('title'=>$row['children'][0]['tagData'], 'content'=>$row['children'][1]['tagData'], 'url'=>$row['children'][3]['tagData'], 'date'=>$date, 'image'=>$img);	
			}
		}
		$data = json_encode($rt);
		$this->_cache->writecache($keyword, $data);
		return $this->_callback.'(' .$data. ')';		
	}
	
	public function yahooAnswer($keyword,$debug=false)
	{
		$this->_curlObj->createCurl('get','http://answers.yahoo.com/search/search_result;_ylt=AqVmR6_Xxr33WPw1P5Wf9t0jzKIX;_ylv=3',array('p' => urlencode($keyword), 'keywords_search' => 'Search+Y!+Answers'));
		if ($debug)
			$this->_curlObj->displayResponce();
		
		$htmldata = $this->_curlObj->__toString();
		
		$pos = explode('<ul class="questions">', $htmldata);		
		$q1 = explode('<h3><a href="/', $pos[1]);
		$path = explode('"><strong class="highlight">', $q1[1]);
		$link = 'http://answers.yahoo.com/'.$path[0];
		
		$path2 = explode('"><strong class="highlight">', $q1[2]);
		$link2 = 'http://answers.yahoo.com/'.$path2[0];
		
		$result = array();
		$qa1 = $this->extractYahooAnswer($link);
		if($qa1!='empty')
			$result[0] = array_merge(array('id'=>'1'), $qa1);
			
		$qa2 = $this->extractYahooAnswer($link2);		
		if($qa2!='empty')	
			$result[1] = array_merge(array('id'=>'2'), $qa2);
		
		return $this->_callback.'('.json_encode($result).')';
	}
	
	public function twitterNews($keyword,$debug=false)
	{
		/*$this->_curlObj->createCurl('get','http://search.twitter.com/search.json',array("q" => urlencode($keyword),"result_type"=>"recent","count"=>"5", "format"=>"json"));
		if ($debug)
			$this->_curlObj->displayResponce();
		*/
		
		$htmldata = file_get_contents("http://search.twitter.com/search.json?q=".urlencode($keyword).'&result_type=recent&count=5&format=json', true);
		
		$ma = json_decode($htmldata,true);
		$all = $ma['results'];

		for($i=0; $i<5; $i++){
			$onenews = $all[$i];
			$result[$i] = array('time'=>$onenews['created_at'], 'content'=>$onenews['text'], 'image'=>$onenews['profile_image_url']);
		}	
		return $this->_callback.'(' .json_encode($result). ')';
	}
	
	public function loadGooglePic($keyword, $start=1, $quantity=1)
	{
		if($quantity==1){
			//$validclickurl = "http://www.google.com/images?hl=en&gbv=2&biw=1920&bih=887&tbs=isch%3A1%2Cimgo%3A1%2Cislt%3Aqsvga%2Cisz%3Ai&sa=1&q=".urlencode($keyword)."&aq=f&aqi=g10&aql=&oq=&gs_rfai=";
			$this->_curlObj->createCurl('get','http://search.twitter.com/search',array('hl' => 'en', 'gbv' => '2', 'biw' => '1920', 'bih' => '887', 'tbs' => 'isch%3A1%2Cimgo%3A1%2Cislt%3Aqsvga%2Cisz%3Ai', 'sa' => '1', "q" => urlencode($keyword), 'aq' => 'f','aqi' => 'g10','aql' => '','oq' => '','gs_rfai' => ''));
		}
		else if($quantity>1){
			//$validclickurl = "http://www.google.com/images?q=".urlencode($keyword)."&hl=en&gbv=2&biw=1920&bih=887&tbs=isch:1,imgo:1,islt:qsvga,isz:m&source=lnt&sa=X&ei=d-tYTJfkJ4X9cLeY8J0J&ved=0CAgQpwU";
			$this->_curlObj->createCurl('get','http://www.google.com/images',array('hl' => 'en', 'gbv' => '2', 'biw' => '1920', 'bih' => '887', 'tbs' => 'isch:1,imgo:1,islt:qsvga,isz:m', 'sa' => 'X', "q" => urlencode($keyword), 'source' => 'lnt','ei' => 'd-tYTJfkJ4X9cLeY8J0J','ved' => '0CAgQpwU'));
		}
			
		$htmldata = $this->_curlObj->__toString();
		$pos = strpos($htmldata, 'href="/imgres?imgurl=');
		if($pos!==false)
			$urlpart = explode('href="/imgres?imgurl=', $htmldata);
		else
			$urlpart = explode('/imgres?imgurl=', $htmldata);
		
		for($i=$start; $i<($start+$quantity); $i++){
			$path[$i-1] = array('id'=>$i, 'image'=>($this->extract_image_link($urlpart[$i])));
		}
		
		return $this->_callback.'(' .json_encode($path). ')';
	}

	public function loadBingPics ($keyword, $start=1, $quantity=1)
	{	
			$start=($start>0)?$start:1;
			$param = array ( 'q' 		=> urlencode($keyword).' filterui:imagesize-small',
							 'FORM'		=> 'I4IR',
							 'format'	=> 'htmlraw',
							 'first' 	=> $start);
			
			$this->_curlObj->createCurl('get','http://www.bing.com/images',$param);
			$htmldata = $this->_curlObj->__toString();
					
			$urlpart = explode('imgurl:&quot;', $htmldata);
				
			$data = array();
			$c=$start;
			
			for($i=$start; $i<($start+$quantity); $i++){
				$urlonly = isset($urlpart[$i])?explode('&quot;', $urlpart[$i]):array();
				$image_url = isset($urlonly[0])?$urlonly[0]:'';
				if (!empty($image_url))
				{
					$data[$c-1]= array('id'=>$c, 'image'=>$image_url);
					$c++;
				}
			}
			return $this->_callback.'(' .json_encode($data). ')';
	}	

	private function extractNewsDetail($ns){	
		$image = explode('src="', $ns);
		$image_link = explode('"', $image[1]); 

		$msg = explode('<div class="msg">', $ns);
		
		$content = explode("</div>", $msg[1]);
		$ctt = $content;   //$this->cleanup($content[0]);
		
		$ti = explode('<div class="info">', $ns);
		$time = explode('<span class="source">', $ti[1]);
		return array('image'=>$image_link[0], 'content'=>$ctt, 'time'=>$time[0]);
	}
	
	private function extractYahooAnswer($link){
		//$this->_curlObj->_useragent = "MSIE";
		$this->_curlObj->createCurl('get',$link);
		
		$htmldata = $this->_curlObj->__toString();
		$content = explode('<div class="content">', $htmldata);
		$ques = explode('</div>', $content[1]);
		if($ques[0]==null ||$ques[0]=='')
			return 'empty';
		else
			$question = $ques[0];		
		$ans = explode('</div>', $content[2]);
		$answer = $ans[0];		
		return array('question'=>$question, 'answer'=>$answer);
	}
	
	private function extract_image_link($source)
	{
		$controller = Controller::getInstance();
		$pos = strpos($source, '&amp;imgrefurl=');
		if($pos!==false)
			$img = explode('&amp;imgrefurl=', $source);
		else
			$img = explode('&imgrefurl=', $source);
			
		$file_headers = @get_headers($img[0]);
		if($file_headers[0] == 'HTTP/1.1 404 Not Found'){ 
			$path = $controller->server.'/default'.mt_rand(0, 5).'.jpg';		
		}else{
			$path = $img[0];	
		}
		return $path;
	}

	/**
	 *  Articles from about.com by getBingWebSearch...
	 */
	function getAboutArticles($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:about.com'; 
		
		$result = $this->getBingWebSearch($keyword,$per_page);	
		return $this->_callback.'('.json_encode($result).')';
	}
	
	/**
	 *  Articles from healthboards.com by getGoogleWebSearch...
	 */
	function getHealthBoards($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:healthboards.com/boards/showthread'; 
		
		$result = $this->getBingWebSearch($keyword,$per_page);	
		return $this->_callback.'('.json_encode($result).')';
	}
	
	/**
	 *  Articles from allbusiness.com by getBingWebSearch...
	 */
	function getAllbusinessArticles($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:allbusiness.com'; 
		
		$result = $this->getBingWebSearch($keyword,$per_page);	
		return $this->_callback.'('.json_encode($result).')';
	}
	
	/**
	 *  Articles from askville.amazon.com by getBingWebSearch...
	 */
	function getAmazonAskville($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:askville.amazon.com'; 
		
		$result = $this->getBingWebSearch($keyword,$per_page);		
		return $this->_callback.'('.json_encode($result).')';
	}	

	/**
	 * Web search from google ..
	 */
	function getGoogleWebSearch($keyword='', $per_page = 4, $callbak = false)
	{	
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$googleData = array();	
		$kewordUrl = 'http://ajax.googleapis.com/ajax/services/search/web?v=1.0&rsz='.intval($per_page).'&hl=en&q='.urlencode($keyword).'&key=ABQIAAAA-AcshcNcyntuwOULxcNwkRTmkHv8EVFAxCToSfP2qbF2JkGCshT8Dzl3JPFuQ4AVijNqBpj31BRGDg';
		
		$this->_curlObj->createCurl('get',$kewordUrl,array());
		$resultJosn = $this->_curlObj->__toString();
		$result = json_decode( $resultJosn);
		
		if ( !empty( $result->responseData->results ) ) {			
			foreach ( $result->responseData->results as $res ) {	
				$googleData[] = array(
					'content_title' =>$res->title,
					'content_main_content' => $res->content,
					'content_link' => $res->url,
				);
			}				
		}
		
		if ($callbak)
			return $this->_callback.'('.json_encode($googleData).')';
		else
			return $googleData;
	}
	
	/**
	 * web serach from bing...
	 *
	 */
	function getBingWebSearch($keyword, $per_page = 5, $callbak = false, $api_key ='21A22DC008050C0B514E6119548DC4C54FCEA0F5')
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$bingData = array();
		$num = 0;
		$bingUrl = 'http://api.bing.net/xml.aspx?AppId='.$api_key.'&Query='.urlencode($keyword).'&Sources=Web&Version=2.2&Market=en-us';
		
		$this->_curlObj->createCurl('get',$bingUrl,array());
		$bingXml = $this->_curlObj->__toString();
		$xml2Array = new xml2Array();
		$bingArray = $xml2Array->parse($bingXml);
		$result = isset($bingArray[0]['children'][1]['children'][2]['children']) ? $bingArray[0]['children'][1]['children'][2]['children'] : array();
		
		if(!empty($result))
		{
			foreach ($result as $item)
			{
				if ($num == $per_page) break;
			
				if (isset($item['children'][0]['tagData']))
					$data['content_title'] = $item['children'][0]['tagData'];
				if (isset($item['children'][1]['tagData']))
					$data['content_main_content'] = $item['children'][1]['tagData'];
				if (isset($item['children'][2]['tagData']))
					$data['content_link'] = $item['children'][2]['tagData'];					
				if (isset($item['children'][5]['tagData']))
					$data['content_time_start'] = strtotime($item['children'][5]['tagData']);				
				if (isset($item['children'][6]['children'][5]['children'][1]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][1]['children'][1]['tagData'];	
				if (isset($item['children'][6]['children'][1]['children'][2]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][2]['children'][1]['tagData'];
				if (isset($item['children'][6]['children'][2]['children'][3]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][3]['children'][1]['tagData'];
				if (isset($item['children'][6]['children'][3]['children'][4]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][4]['children'][1]['tagData'];																					
				if (isset($item['children'][6]['children'][4]['children'][5]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][5]['children'][1]['tagData'];		
				if (isset($item['children'][6]['children'][4]['children'][6]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][6]['children'][1]['tagData'];
				if (isset($item['children'][6]['children'][4]['children'][7]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][7]['children'][1]['tagData'];
				if (isset($item['children'][6]['children'][4]['children'][8]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][8]['children'][1]['tagData'];
																																		
				if(!empty($data)) {										
					$bingData[] = $data;
					unset($data);
					$num++;
				}				
			}
		}

		if ($callbak)
			return $this->_callback.'('.json_encode($bingData).')';
		else
			return $bingData;
	}

	function getArticlebaseArticles($keywords='',$per_page=5) 
	{
		if (empty($keywords) || !is_string($keywords)) return array();
		/** http://www.articlesbase.com/find-articles.php?q=new+york */
		$artiles_getdata = array();
		$artiles_url = 'http://www.articlesbase.com/find-articles.php?q='.urlencode($keywords);
		
		$this->_curlObj->createCurl('get',$artiles_url,array());
		$artiles_data = $this->_curlObj->__toString();
		$content = @preg_match_all('|<div\sclass="article_row">\s*<div class="title">\s*<h3><a\stitle=".*?" href="(.*?)">(.*?)</a></h3>\s*</div>\s*<img\ssrc="(.*?)"\swidth=".*?"\sheight=".*?"\salt=".*?"\stitle=".*?"\sclass=".*?"\s/>\s*<p>(.*?)</p>\s*<div\sclass=".*?">|',$artiles_data,$artiles_array);
        if (isset($artiles_array['1'])) $artile['content_link'] = $artiles_array['1'];
        if (isset($artiles_array['2'])) $artile['content_title'] = $artiles_array['2'];
        if (isset($artiles_array['3'])) $artile['content_photo_src'] = $artiles_array['3'];
        if (isset($artiles_array['4'])) $artile['content_main_content'] = $artiles_array['4'];

        $count = count($artiles_array['1']);
        if ($count <= $per_page) {
	         for ($i = 0;$i< $count;$i++) {
				 $artiles_getdata[] = array(
	                    'content_link'	=> $artile['content_link'][$i],
						'content_title' => $artile['content_title'][$i],
						'content_main_content' => $artile['content_main_content'][$i],
						'content_photo_src'	=> $artile['content_photo_src'][$i]	             
				  ); 
			 }
        } else {
	        for ($i = 0;$i<$per_page;$i++) {
				 $artiles_getdata[] = array(
	                  	'content_link'	=> $artile['content_link'][$i],
						'content_title' => $artile['content_title'][$i],
						'content_main_content' => $artile['content_main_content'][$i],
						'content_photo_src'	=> $artile['content_photo_src'][$i]	       
						    ); 
					 }
        }				
        return $this->_callback.'('.json_encode($artiles_getdata).')';
	}
	
	/**
	 * get news from forbes search
	 */
	function getForbesNews($keyword , $per_page = 5)
	{
		$keyword = urlencode(trim($keyword));
		$appurl = "http://search.forbes.com/search/find?tab=searchtabgeneraldark&MT=".$keyword;
		
		$this->_curlObj->createCurl('get',$appurl,array());
		$contents = $this->_curlObj->__toString();
		if( empty($contents)) return false;
		preg_match_all( "/<div class=\"head\">\s+<a href=\"(.*?)\" >(.*)<\/a>\s+<span class=\"type\">\s+\<\!-- Include video, slide or mp3 icon here ForbesResults\.jsp--\>\s+(.*?)<\/span><\/div>\s+<div class=\"date\">(.*?)<\/div>\s+<div class=\"dek\">([\w\W]*?)<\/div>/" , $contents, $matchs );
		if (!$matchs) return false;
		$data = array();
		$count = (count($matchs[1]) < $per_page) ? count($matchs[1]) : $per_page;
		for($i=0; $i < $count; $i++)
		{
			$data[] = array(
						'content_link' => $matchs[1][$i],
						'content_title' => preg_replace('/<span class=\"bold\">(.*?)<\/span>/', '<b>\\1</b>', $matchs[2][$i]).$matchs[3][$i],
						'content_main_content' => $matchs[5][$i],
						'content_time_start' => strtotime($matchs[4][$i])
					);
		}
        return $this->_callback.'('.json_encode($data).')';
	}
	
	private function cleanup($st){
		$t1 = explode('<span class="expand">', $st);
		$t2 = isset($t1[1])?explode('</span>', $t1[1]):array();
		$t1 = isset($t1[0])?$t1[0]:'';
		$t2 = isset($t2[1])?$t2[1]:'';
		return $t1.$t2;
	}

}

?>
