<?php
/**
 * Article class
 * Author: Archie Huang on 30/01/2009
 * 
 */

class Article extends  Model
{
	protected $maxScrapArticles = 5;
	protected $MaxArticles = 20;
	protected $curlErrors = 0;
	private $_db; 
	private $_curlObj;
	private static $_Object;
	
	public function __construct($dbObj=null)
	{
    	/* old code compatibility */
		global $db;
		$this->_db = is_null($dbObj)?$db:$dbObj;
		$this->_curlObj = new SingleCurl();
		
		self::$_Object = $this;
		return self::$_Object;
	}

    /**
     * Get the class static object
     *
     * @return self
     */
    public static function getInstance(db_class $db) 
    {
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
    }

	/*
	 * 
	 * Functions Used by sx25 API to send data to external programs 
	 * 
	 */
	
	function get_article($keyword, $domain, $outputtype=1, $numArticles=1, $offset='') 
	{
	    /* 
	     * Get the articles from database
	     */
		$pResults = array();

		switch ($outputtype)
		{
			case '1':	$pResults = $this->get_domainSX25_article($domain,$keyword);
						break;
			default  :	$pResults = $this->get_articles_by_keyword($keyword,$numArticles,$offset);
						break;
		}

		/*
		 * Format the responce according with outputtype
		 */
		$article 	= '';
		$json 		= false;
		
		if(!empty($pResults) && $pResults)
		{
			$article = array();
			switch ($outputtype)
			{
				case '1':	
							$content	= $pResults['article_content'];
							$title 		= $pResults['article_title'];
							$article	= "<h3>".$title."</h3>".$content;
							$json 		= false;
							break;
				case '20':	$article 	= $pResults;
							$json 		= true;
							break;
				default  :	$article 	= isset($pResults['article_summary'])?$pResults['article_summary']:$pResults;
							$json 		= true;
							break;
			}
		}
		
		/*
		 *  Send responce
		 */
		if ($json)
		{
			return (!empty($article)?json_encode($article):'99');
		}
		else
		{
			return (!empty($article)?$article:'');
		}
	}
	
	function get_article_parked($keyword, $domain, $outputtype=1, $article_id=0, $offset='') 
	{
	    /* 
	     * Get the articles from database
	     */
		$pResults = array();
	    $numArticles = 0;

	    if (!empty($article_id) && is_numeric($article_id))
		{
			$pResults[] = $this->get_article_info($article_id);
		}
		else
		{
			switch ($outputtype)
			{
				case '11':	$numArticles = 6;
							$kw = '';
							break;
				case '12':	$numArticles = 1;
							$kw = $keyword;
							break;
				default  :	$kw = '';
							break;
			}
			
			$pResults = $this->get_parkedDomain_articles($domain,$kw,$numArticles,$offset);
		}
		
		/*
		 *  This outputs type need only one article 
		 */
		if (($outputtype == '1') || ($outputtype == '2') || ($outputtype == '3') || ($outputtype == '13'))
		{
			$pRow = array();
			foreach($pResults as $rRow)
			{
				if (empty($pRow))
					$pRow = $rRow;
				if (strcasecmp($rRow['article_keyword'],$keyword) == 0) 
					$pRow = $rRow;
			}
			$pResults = $pRow;
		}	
		
		/*
		 * Format the responce according with outputtype
		 */
		$article 	= array();
		$json 		= false;
		
		if(!empty($pResults) && $pResults)
		{
			switch ($outputtype)
			{
				case '11' :	
							foreach($pResults as $pRow)
							{
								$summary	= $pRow['article_summary'];
								$title 		= $pRow['article_title'];
								$id 		= $pRow['article_id'];
								$keyword 	= $pRow['article_keyword'];
								$article[$id]	= array('id' => $id, 'keyword' =>$keyword, 'title' => $title, 'summary'=>$summary);		
							}
							$json = true;
							break;
				case '12' :
							foreach($pResults as $pRow)
							{
								$content 	= $pRow['article_content'];
								$title 		= $pRow['article_title'];
								$id 		= $pRow['article_id'];
								$article[$id] = array('id' => $id,'title' => $title, 'content'=>$content);		
							}
							$json = true;
							break;		
				case  '1' :
							$content 	= $pResults['article_content'];
							$title 		= $pResults['article_title'];
							$article 	= '<h4 class="the_title">'.$title.'</h4>'.$content;
							$json 		= false;
							break;
				case '2' :
							$content	= $pResults['article_content'];
							$title 		= $pResults['article_title'];
							$full 		= "<p>".$title."</p>".$content;
							$part 		= substr(strip_tags($content), 0, 1000).' ... '; 					
							$article	= "<div id='part'>".$part."</div><div id='full'>".$full."</div>";
							$json 		= false;
							break;
				case '3' :
							$content 	= $pResults['article_content'];
							$title 		= $pResults['article_title'];
							$full 		= '<h4 class="the_title">'.$title.'</h4>'.$content;
							$part 		= substr(strip_tags($content), 0, 400).' ... '; 	
							$article 	= "<div id='part'>".$part."</div><div id='full'>".$full."</div>";
							$json 		= false;
							break;
				case '13' :
							$content 	= $pResults['article_content'];
							$title 		= $pResults['article_title'];
							$article 	= '<h4 class="the_title">'.$title.'</h4>'.$content;
							$json 		= true;
							break;
				default :	$article 	= isset($pResults['article_summary'])?$pResults['article_summary']:$pResults;
							$json 		= false;
							break;
			}
		}

		/*
		 *  Send responce
		 */
		if ($json)
		{
			return (!empty($article)?json_encode($article):'99');
		}
		else
		{
			return (!empty($article)?$article:'');
		}
	}
	
	public function get_domainSX25_article($domain,$keyword) 
	{
		$articleQuery = "SELECT * FROM articles WHERE article_keyword = '".$keyword."' and  article_domain = '".$domain."' and article_default = '0' LIMIT 1";
		$pResults = $this->_db->select($articleQuery);

		if(!$pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$articleQuery = "SELECT * FROM articles WHERE article_domain = '".$domain."' and article_default = '1' LIMIT 1";
			$pResults = $this->_db->select($articleQuery);
			if(!$pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
			{
				$pRow = '';
			}
		}
		return $pRow;
	}

	public function get_domain_articles($domain,$sortyQuery='') 
	{
		$output = array();
		$articleQuery = "SELECT * FROM articles WHERE article_domain = '".$domain."' ".$sortyQuery;
		$pResults = $this->_db->select($articleQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		
		return $output;
	}

	private function get_parkedDomain_articles($domain,$keyword='',$numArticles=0,$offset='')
	{
		$output = array();
		$limit = ($numArticles > 0)?" LIMIT $numArticles ":' LIMIT  '.$this->MaxArticles.' ';
		$limit .= (!empty($limit) && !empty($offset))?" OFFSET $offset ":'';
		$andKeyword = !empty($keyword)?" AND LOWER(article_keyword) = LOWER('$keyword')":''; 
					 

		/* Search full text */
		$articleQuery = " SELECT articles.* 
						FROM articles 
						LEFT JOIN domains_parked_twin ON domain_parked_twin_origin = article_domain AND domain_parked_twin_copy = '$domain'
						WHERE (LOWER(article_domain) = LOWER('$domain') OR domain_parked_twin_id IS NOT NULL) $andKeyword $limit ";
		$pResults = $this->_db->select($articleQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
	
		return $output;
	}
	
	public function get_articles_by_keyword($keyword,$numArticles=1,$offset='')
	{
		$limit = ($numArticles > 0)?" LIMIT $numArticles ":' LIMIT  '.$this->MaxArticles.' ';
		$limit .= (!empty($limit) && !empty($offset))?" OFFSET $offset ":'';

		/* Search full text */
		$aQuery = "SELECT * FROM articles WHERE MATCH (article_keyword,article_title,article_content) AGAINST ('$keyword') $limit ";
		$aResults = $this->_db->select($aQuery);

		if((!$aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) || ($numArticles > 0 && $this->_db->row_count < $numArticles)) 
		{
			/* Search keyword */
			$aQuery = "SELECT * FROM articles WHERE article_keyword like '$keyword' $limit ";
			$aResults = $this->_db->select($aQuery);
			
			/* Search ALL */
			if((!$aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) || ($numArticles > 0 && $this->_db->row_count < $numArticles)) 
			{
				$aQuery = "SELECT * FROM articles WHERE MATCH (article_keyword,article_title,article_content) AGAINST ('$keyword'  WITH QUERY EXPANSION) $limit ";
				$aResults = $this->_db->select($aQuery);
				$aRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
			}
		}
	
		$result = array();
		if (isset($aRow) && !empty($aRow)) {
			$result[] = $aRow;
		}
		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$result[] = $aRow;
		}
		return $result;
	}
	
	
	
	public function get_article_info($article_id) {
		
		$pQuery = "SELECT * FROM articles WHERE article_id = '".$article_id."' LIMIT 1";
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
	
	function check_article_set($keyword,$domain) 
	{
		if ( $article_id = $this->_db->select_one("SELECT article_id FROM articles WHERE LOWER(article_keyword) = LOWER('".$keyword."') and article_domain_id = '".$domain."' LIMIT 1"))
			return $article_id;
		else
			return false;
	}
	
	function check_article_set2($keyword, $domain_url) {
		
		if ( $article_id = $this->_db->select_one("SELECT article_id FROM articles WHERE LOWER(article_keyword) = LOWER('".$keyword."') and article_domain = '".$domain_url."' LIMIT 1"))
			return $article_id;
		else
			return false;
	}
	
	/*
	 * 
	 * Functions Used by sx25 backend 
	 * 
	 */
	
	function get_sx25_articles($fromRecord,$recordPerPage,$sortyQuery,$appFlag=null) 
	{
		$output = array();
		$approved = is_null($appFlag)?'':" and article_approved = '$appFlag' ";
		$articleQuery = "SELECT * FROM articles JOIN domains ON (articles.article_domain_id = domains.domain_id)  WHERE articles.article_domain is NOT NULL $approved $sortyQuery LIMIT ".$fromRecord.",".$recordPerPage;
		$pResults = $this->_db->select($articleQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}
	
	function get_other_articles($fromRecord,$recordPerPage,$sortyQuery,$appFlag=null) 
	{
		$output = array();
		$approved = is_null($appFlag)?'':" and article_approved = '$appFlag' ";
		$articleQuery = "SELECT * FROM articles LEFT JOIN domains ON (articles.article_domain_id = domains.domain_id) WHERE articles.article_domain is NOT NULL and domains.domain_id is NULL $approved $sortyQuery LIMIT ".$fromRecord.",".$recordPerPage;
		$pResults = $this->_db->select($articleQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		
		return $output;
	}

	function get_library_articles($fromRecord,$recordPerPage,$sortyQuery,$appFlag=null) 
	{
		$output = array();
		$approved = is_null($appFlag)?'':" and article_approved = '$appFlag' ";
		$articleQuery = "SELECT *, article_keyword as domain_keyword FROM articles WHERE article_domain is NULL and article_domain_id is NULL $approved $sortyQuery LIMIT ".$fromRecord.",".$recordPerPage;
		$pResults = $this->_db->select($articleQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		
		return $output;
	}
	
	function count_total_articles($server='',$domainname='',$appFlag=null) {
	
		$server = strtolower($server);
		$approved = is_null($appFlag)?'':" and article_approved = '$appFlag' ";
		$query = empty($domainname)?'':" and articles.article_domain = '$domainname' ";
		switch ($server) 
		{
			case 'sx25'  : 	$articleQuery = "SELECT count(*) FROM articles JOIN domains ON (articles.article_domain_id = domains.domain_id)  WHERE articles.article_domain is NOT NULL $approved $query ";
							break;
			case 'parked': $articleQuery = "SELECT count(*) FROM articles LEFT JOIN domains ON (articles.article_domain_id = domains.domain_id) WHERE articles.article_domain is NOT NULL and domains.domain_id is NULL $approved $query ";
							break;
			default 	 : $articleQuery = "SELECT count(*) FROM articles WHERE article_domain is NULL and article_domain_id is NULL $approved ";
							break;
		}
		$count = $this->_db->select_one($articleQuery);
		return $count;
	}
	
	
	public function del_article($id)
	{
	
		$rowArray = $this->get_article_info($id);
		
		if ($rowArray)
		{
			if (empty($rowArray['article_domain_id']))
				unset($rowArray['article_domain_id']);
			if (empty($rowArray['article_domain']))
				unset($rowArray['article_domain']);
			$uid = isset($_SESSION['princetonUsername'])?$_SESSION['princetonUsername']:null ;
			if($uid==null || $uid=='')
				$uid =$_SESSION['princetonUser'];
				
			$rowArray['article_removed_by'] = $uid;
			if ($bkId = $this->_db->insert_array('articles_backup', $rowArray))
			{
				$dQuery = "DELETE FROM articles WHERE article_id = '".$id."'";
				if($this->_db->delete($dQuery))
					return true;
			}
		}
		return false;
	}

	public function approveArticle($id,$approved=1){
		global $user;
		if (!isset($user)) return false;
		$approved = ($approved == 1)?1:0;
		$aQuery = "UPDATE articles SET article_approved='$approved', article_approved_user = '".$user->userID."',article_update_date = '".date('Y-m-d')."'  WHERE article_id = '$id'";
		if ($return = $this->_db->update_sql($aQuery))
			return $return;
		else
			return false;
	}
	
	
	function get_unused_articles($keyword, $limit=1, $startnum=0, $appFlag=null) {
	
		$article = array();
	    $limit = (empty($limit) || !is_numeric($limit))?1:$limit;
		$approved = is_null($appFlag)?'':" and article_approved = '$appFlag' ";
		$articleQuery = "SELECT * FROM articles WHERE article_keyword like '$keyword' and article_domain is null and article_domain_id is null $approved ORDER BY article_id LIMIT $startnum,$limit ";
		$pResults = $this->_db->select($articleQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$article[] = $pRow;		
			}
		}
	
		return $article;
	}

	function check_default($domain_url,$id=0) 
	{
		// verify if domain has default article
		if (empty($domain_url))
			return true;
		$defaultQ = "SELECT article_id FROM articles WHERE article_domain = '".$domain_url."' and article_default = 1 LIMIT 1 ";
		$count = $this->_db->select_one($defaultQ);
		$default = (($count == false) || ($count == $id))?false:true;
		return $default;
	}
	
	function check_default_byID ($id)
	{
		$info = $this->get_article_info($id);
		return $this->check_default($info['article_domain'],$id);
	}
	
	function scrape_article($keyword, $domain_id, $domain_url, $default, $limitnum=1, $startnum=0, $this_id=0, $article_source= 'ehow', $save_keyword=null) 
	{
		$keyword = trim($keyword);
		$default = $default?1:0;
		// verify it is not other default article
		if ($default==1)
		{
			$default = $this->check_default($domain_url,$this_id)?0:1;
		}
		// Get library articles if it is for a domain 
		$articles = (!empty($domain_id) || !empty($domain_url))?$this->get_unused_articles($keyword, $limitnum, $startnum):array();
		if (empty($articles))
		{
			echo "Scraping from $article_source -> Domain: $domain_url, keyword: $keyword <br>";
			//$limit = ($limitnum < $this->maxScrapArticles)?$this->maxScrapArticles:$limitnum;
			//$articles = $this->scrape_EzineArticles($keyword, $limitnum, 1);
			if($article_source== 'ehow')
				$articles = $this->scrape_ehow($keyword, $limitnum, 1);
			else if($article_source== 'articleBase')
				$articles = $this->scrape_articlebase($keyword, $limitnum, 1);
			else if($article_source== 'EzineArticles')
				$articles = $this->scrape_EzineArticles($keyword, $limitnum, 1);
			else if($article_source== 'hubpages')
				$articles = $this->scrape_hubpages($keyword, $limitnum, 1);
				
			echo "Scraped from $article_source -> Domain: $domain_url, keyword: $keyword, found:".count($articles).'<br>';
		}
		else 
		{
			echo "Library articles -> Domain: $domain_url, keyword: $keyword <br>";
		}
			
		$usethisarticle = '';
		$artnum = 0;
		foreach($articles as $key => $artarray)
		{
			$artarray['article_update_date'] = date('Y-m-d');
			if ($artnum < $limitnum)
			{		
				$artarray['article_domain_id'] 	= $domain_id;
				if (empty($artarray['article_domain_id']))
					unset($artarray['article_domain_id']);
					
				$artarray['article_domain']    	= $domain_url;
				if (empty($artarray['article_domain']))
					unset($artarray['article_domain']);
				$artarray['article_default']   		= $default;
				
				$default 							= 0; // only one default article
				
				// Unlink the article
				if ($this_id > 0) 
				{
					$this->unlink_this_article($this_id);
				}
				// If from database replacing the article with existing articles
				if (isset($artarray['article_id']))
				{
					$upArray = array();
					if (isset($artarray['article_domain_id']))
						$upArray['article_domain_id']	= $artarray['article_domain_id'];
					if (isset($artarray['article_domain']))
						$upArray['article_domain'] 		= $artarray['article_domain'];
					$upArray['article_default']  		= $artarray['article_default'];
					$upArray['article_update_date']		= $artarray['article_update_date'];
					$article_id 						= $this->save_article($upArray, $artarray['article_id']);
				}
				else // scraped from Ezine and Save the article
				{
					$artarray['article_keyword'] = empty($save_keyword)?$keyword:$save_keyword;
					$article_id	= $this->save_article($artarray,0);
				}
				if($article_id)
				{
					$artnum++;
					if(empty($usethisarticle))
						$usethisarticle = $article_id;
				}
					//$this->del_article($artarray['article_id']);
			}	
			else 	
			{
				$artarray['article_default']= 0;
				$article_id	= isset($artarray['article_id'])?$artarray['article_id']:0;
				$article_id = $this->save_article($artarray, $article_id);
				echo "Insert in library -> keyword: $keyword <br>";				
			}
		}
		ob_flush();
		flush();		
		return $usethisarticle;
	}
	
	private function scrape_articlebase($keyword, $limitnum=1, $startnum=1)
	{
		$i = 1;
		$article_id = 0;
		while($article_id == 0 && $i < 21)
		{
			$querykeyword = urlencode("'".$keyword."' site:articlesbase.com");
			$livesearch = "http://www.bing.com/search?q=".$querykeyword."&go=&form=QBLH&first=".$i;
			$resultpage = $this->download_pretending($livesearch);
			$doc = new simple_html_dom();
			$doc->load($resultpage);
			$found = $doc->find('ul.sb_results',0);
			
			$eachresult = $found->find('div.sb_tlst');
			
			$artnum = 0;
			$valnum = 1;
			$artarray = array();
			
			foreach($eachresult as $val)
			{
				$title = '';
				$content = '';
				//title
				$title 	= $val->find('a',0)->plaintext;
				$url 	= $val->find('a',0)->href;
				
				if(stripos($url, 'articlesbase.com') !== false) 
				{
					if ($this->check_article($title,$keyword))
						continue;
						
					if($valnum >= $startnum)
					{
						$cachelink = html_entity_decode($url);
						$contentpage = $this->download_pretending($cachelink);
						$article = new simple_html_dom();
						$article->load($contentpage);
						
						$articleText = $article->find('div.article_cnt',0);
						
						//content
						$content = $this->clean_html($articleText);
						$summary = $this->getSummary($content);
						
						$author = 'ArticlesBase.com';
	
						if(!empty($title) && !empty($articleText->plaintext))
						{	
							$content .= '<br>Article Source: www.ArticlesBase.com';
							$artarray[] = array('article_title' => $title, 'article_summary' => $summary, 'article_content' => $content, 'article_author' => $author, 'article_keyword' => $keyword, 'article_update_date' => date('Y-m-d'));
						}
						
						// clean memory leak
						$article->clear(); 
						unset($article);
						
						if(count($artarray) == $limitnum)
								break;
					}
					$valnum++;
				}
			}
			
			$i = $i+10;
			// clean memory leak
			$doc->clear(); 
			unset($doc);
		}
		return $artarray;
	}
	
	private function scrape_ehow($keyword, $limitnum=1, $startnum=1)
	{

			$livesearch = "http://www.ehow.com/search.html?q=".urlencode($keyword)."&skin=corporate&t=article";
			$resultpage = $this->download_pretending($livesearch);
			$doc = new simple_html_dom();
			$doc->load($resultpage);
			
			$artnum = 0;
			$count = 0;
			$valnum = 1;
			$artarray = array();

			$found = $doc->find('ul.Results',0);
			
			if (!empty($found))
			{
				foreach($found->find('li.item') as $articles)
				{
					//echo 'List: <br>'; echo($articles->innertext); echo '<br>';
					if($count>0)
					{
						$content = '';
						//title
						$title = $articles->find('a',0)->plaintext;				
						if(!empty($title) && !($this->check_article($title,$keyword))) 
						{	
								$url 		= $articles->find('a',0)->href; 
								$cachelink 	= html_entity_decode($url);
								$contentpage= $this->download_pretending($cachelink);
								// insert missing tag
								$contentpage = str_replace('<div id="relatedContentUpper"','</p><div id="relatedContentUpper"',$contentpage);
								
								$article = new simple_html_dom();
								$article->load($contentpage);
								
								$articleText = $article->find('article',0);
								//echo 'Article: <br>'; echo($articleText->innertext); echo '<br><br>';
								if (! empty ( $articleText->innertext )) {
									// exclude all ads from google and other links
									$delCode = $articleText->find ( 'div[id=DMINSTR]', 0 );
									if (!empty($delCode) && !empty($delCode->plaintext)) $delCode->outertext  = '';
									$delCode = $articleText->find ('div[id=GoogleAdsense336x280]');
									if (!empty($delCode) && !empty($delCode->plaintext)) $delCode->outertext  = '';
									$delCode = $articleText->find ( 'div.GoogleTextAd', 0 );
									if (!empty($delCode) && !empty($delCode->plaintext)) $delCode->outertext  = '';
									$delCode = $articleText->find ( 'div[id=relatedContentUpper]', 0 );
									if (!empty($delCode) && !empty($delCode->plaintext)) $delCode->outertext  = '';
									$delCode = $articleText->find ( 'div[id=relatedContentLower]', 0 );
									if (!empty($delCode) && !empty($delCode->plaintext)) $delCode->outertext  = '';
									$delCode = $articleText->find ( 'div[id=RelatedSearches]', 0 );
									if (!empty($delCode) && !empty($delCode->plaintext)) $delCode->outertext  = '';
									
									// get content for article
									$topArticle = $articleText->find('p[id=intelliTxt]',0);
									$allSteps 	= $articleText->find('ol[id=intelliTxt]',0);
									$section 	= !empty($allSteps)?$allSteps->find('li.section',0):null;
									$steps 		= !empty($section)?$section:$allSteps;
									$tips		= $articleText->find('figure.tipsWarnings',0);
									$tips		= !empty($tips)?$tips->find('ul',0):$tips;
									
									if(!empty($topArticle->plaintext) || !empty($steps))
									{
										$tipsSection=!empty($tips->plaintext)?PHP_EOL.'<br><div class="tips">Tips &amp; Warnings</div>'.$this->clean_html($tips):'';
										$content 	= empty($topArticle->plaintext)?$this->clean_html($steps):$this->clean_html($topArticle).PHP_EOL.'<br><div class="instructions">Instructions</div>'.$this->clean_html($steps).$tipsSection;
										$summary 	= empty($topArticle->plaintext)?$this->getSummary($steps->plaintext):$this->getSummary($topArticle->plaintext);
										
										$author 	= 'ehow.com';
					
										if (! empty ( $title ) && (! empty ( $topArticle->plaintext ) || ! empty ( $steps->plaintext ))) {
											$content .= '<br>Article Source: www.ehow.com';
											$artarray [] = array (
													'article_title' => $title,
													'article_summary' => $summary,
													'article_content' => $content,
													'article_author' => $author,
													'article_keyword' => $keyword,
													'article_update_date' => date ( 'Y-m-d' ) 
											);
										}
									}
									if(count($artarray) == $limitnum)
											break;
								}
								// clean memory leak
								$article->clear(); 
								unset($article);
						}
					}
					$count++;
				}
			}
			// clean memory leak
			$doc->clear(); 
			unset($doc);
			return $artarray;
	}
	
	public function scrape_hubpages($keyword, $limitnum=1, $startnum=1)
	{
		$keyword	= strtolower(preg_replace('#[^\w](?R)*#', '+', trim($keyword)));
		$livesearch = "http://hubpages.com/search/".urlencode($keyword);
		$resultpage = $this->download_pretending($livesearch);
		$doc = new simple_html_dom();
		$doc->load($resultpage);
			
		$artnum = 0;
		$count = 0;
		$valnum = 1;
		$artarray = array();
	
		$found = $doc->find('ol.searchTop',0);
			
		if (!empty($found))
		{
			foreach($found->find('li.article_result') as $articles)
			{

				if($count>0)
				{
					$content = '';
					//title
					$title = $articles->find('a',0)->plaintext;
					if(!empty($title) && !($this->check_article($title,$keyword)))
					{
						$url 		= $articles->find('a',0)->href;
						$cachelink 	= html_entity_decode($url);
						$contentpage= $this->download_pretending($cachelink);
	
						$article = new simple_html_dom();
						$article->load($contentpage);
	
						$articleContainer = $article->find('div#hub_main',0);
						
						if (!empty($articleContainer->innertext))
						{
							$articleText = $articleContainer->find('div.moduleText', 0);

							if(!empty($articleText->plaintext))
							{
								//content
								$content = $this->clean_html($articleText);
								$summary = $this->getSummary($content);
								
								$author = 'hubpages.com';
			
								if(!empty($title) && (!empty($articleText->plaintext)))
								{
									$content .= '<br>Article Source: http://hubpages.com';
									$artarray[] = array('article_title' => $title, 'article_summary' => $summary, 'article_content' => $content, 'article_author' => $author, 'article_keyword' => $keyword, 'article_update_date' => date('Y-m-d'));
								}
							}
							if(count($artarray) == $limitnum)
								break;
						}
						// clean memory leak
						$article->clear();
						unset($article);
					}
				}
				$count++;
			}
		}
		// clean memory leak
		$doc->clear();
		unset($doc);
		return $artarray;
	}
	
	/* 
	 * Clean the img and scripts from html 
	 * $html  simple_html_dom object
	 * $what  integer :  1 clean img tag, 2 clean script, 0 clean both 
	 * retunt string
	 * */
	private function clean_html ($htlm,$what=0)
	{
		if (empty($htlm))
			return '';

		$cleanText = $htlm->innertext;
		// delete image
		if ($what == 0 || $what == 1)
		{
			foreach ($htlm->find('img') as $img)
			{
				$cleanText = str_replace($img->outertext,'',$cleanText);
			}
		}
		// delete script
		if ($what == 0 || $what == 2)
		{
			foreach ($htlm->find('script') as $script)
			{
				$cleanText = str_replace($script->outertext,'',$cleanText);
			}
		}		
		return $cleanText;
	}

	
	function clean_articles() 
	{
		$articleQuery = "SELECT * FROM articles WHERE article_content like '%<script%' or (article_content like '%<img%' and article_author = 'ehow.com') limit 20 ";
		$pResults = $this->_db->select($articleQuery);
		$updated = 0;
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$contentPage = $row['article_content'];
			$article = new simple_html_dom();
			$article->load($contentPage);
			$delete = ($row['article_author'] == 'ehow.com')?0:2;
			$cleanContent = $this->clean_html($article,$delete);
			echo 'Article: '.(($delete==0)?' both':' script').' '.$row['article_id'].' '.$row['article_title'].'<BR>'.PHP_EOL;
			$this->save_article(array('article_content' => $cleanContent), $row['article_id']);
			// clean memory leak
			$article->clear(); 
			unset($article);
			// flush
			ob_flush();
			flush();
			$updated++;		
		}
		return $updated;
	}
	
	
	private function scrape_EzineArticles($keyword, $limitnum=1, $startnum=1) 
	{
		//echo "scrape_EzineArticles($keyword, $limitnum, $startnum) ";
		$querykeyword = urlencode($keyword." site:ezinearticles.com");
		$livesearch = "http://www.bing.com/search?q=".$querykeyword."&go=&form=QBLH";
		$resultpage = $this->download_pretending($livesearch);
		if ($resultpage == false)
			return array();
		$resultpage = explode('<ul id="wg0" class="sb_results">', $resultpage);
		$resultlist = isset($resultpage[1])?$resultpage[1]:'';
		
		$eachresult = explode('<li class="sa_wr"><div class="sa_cc"><div class="sb_tlst"><h3>', $resultlist);
	
		$artnum = 0;
		$valnum = 1;
		$artarray = array();
		
		foreach($eachresult as $key => $val)
		{		
		  if($key!=0){   
			$titlerest = explode('</a></h3></div>', $val);
			$titleonly = explode('">', $titlerest[0]);
			
			// title
			$title = str_replace('<strong>', '', str_replace('</strong>', '', str_replace('<h3></h3>', '', $titleonly[1])));
	
			if(!is_numeric(strpos($title, 'Ezine Articles'))&&!is_numeric(strpos($title, 'EzineArticles'))&&!is_numeric(strpos($title, 'Expert Author'))&&!($this->check_article($title,$keyword)))
			{
										
				if($valnum >= $startnum)
				{
					$restpart = $titlerest[0];
	
					$cacherest = explode('<a href="', $restpart);
					//echo "<br>cacherest:$cacherest<br>";
					$cacheonly = isset($cacherest[1])?explode('" onmousedown=', $cacherest[1]):'';
					$cachelink = isset($cacheonly[0])?trim($cacheonly[0]):'';
					
					if (!empty($cachelink))
					{
						$cachelink = html_entity_decode($cachelink);
						sleep(rand(1,4)); // for blockbuster 
						$contentpage = $this->download_pretending($cachelink, true);
						if ($contentpage == false)
							break;
						//content
						$contentpiece = explode('<div id="article-body">', $contentpage);
						$contentpiece = isset($contentpiece[1])?explode('<div id="article-resource">', $contentpiece[1]):array();
						$content = isset($contentpiece[0])?$contentpiece[0]:'';

						//autor
						$contentpiece = explode('<div class="author-profile">', $contentpage);
						$contentpiece = isset($contentpiece[1])?explode('<div id="article-body">', $contentpiece[1]):array();
						$content .= isset($contentpiece[0])?'<div class="author-profile">'.$contentpiece[0]:'';

						//author link
						$contentpiece = explode('<div id="article-resource">', $contentpage);
						$contentpiece = isset($contentpiece[1])?explode('</div>', $contentpiece[1]):array();
						$content .= isset($contentpiece[0])?'<div id="article-resource">'.$contentpiece[0].'</div>':'';
						
						// Clean content
						$content = $this->deco($content);
						
						if (!empty($content))
						{
							//echo "from ezine content: $content<BR>";
							//new summary
							$summary = trim($content);
							$summary = strip_tags($summary,'<b>');
							$summary = str_ireplace(array('<b>','</b>'),array('|b|','|/b|'),$summary);
							$summary = preg_replace('/<(\/?[^>]+)>/','',$summary);
							$summary = str_replace(array('|b|','|/b|'),array('<b>','</b>'),$summary);
							$summary = substr($summary, 0, 330)."...";
	
							$substr = 330;
							if (strlen($summary) > $substr)
							{	
								$testchar = substr($summary, $substr, 1);
								while ($testchar != " ") {
									$substr = $substr - 1;
									$testchar = substr($summary, $substr, 1);
								}
								$summary = substr($summary, 0, $substr);
							}
							
							$author = 'EzineArticles.com';
							
							$artarray[] = array('article_title' => $title, 'article_summary' => $summary, 'article_content' => $content, 'article_author' => $author, 'article_keyword' => $keyword, 'article_update_date' => date('Y-m-d'));
	
							if(count($artarray) == $limitnum)
								break;
						}
						else 
							echo "keyword: $keyword.  Ezine link: $cachelink<BR>";
					}
				}
				
			}
			$valnum++;
		  }
		}
		return $artarray;
	}
	
	
	function save_article($array, $id=0)
	{
		if($id == 0)	
		{
			$array['article_default'] = (isset($array['article_domain']) && !empty($array['article_domain']))?($this->check_default($array['article_domain'])?0:1):0;
			$id = $this->_db->insert_array('articles', $array);
		}
		else
		{
			if (isset($array['article_default']))
			{
				$array['article_default'] = isset($array['article_domain'])?($this->check_default($array['article_domain'],$id)?0:1)
																			:($this->check_default_byID($id)?0:1);
			}
			$this->_db->update_array('articles', $array, "article_id='".$id."'");
		}
		// If link an article to a twin copy domain, delete the domain from twin table.  	
		if ($id && isset($array['article_domain'])) 
		{
			$Parked = ParkedDomain::getInstance($this->_db);
			$Parked->delete_twin_domain_copy($array['article_domain']); 
		}
		return $id;
	}
	
	function link_article($id,$domain_url,$domain_id='')
	{
		$return = false;
		if(!empty($id) && !empty($domain_url))	
		{
			$default = $this->check_default($domain_url)?0:1;
			if (empty($domain_id))
				$aQuery = "UPDATE articles set article_domain_id = NULL, article_domain = '$domain_url', article_default = $default WHERE article_id = '".$id."'";
			else 
				$aQuery = "UPDATE articles set article_domain_id = '$domain_id', article_domain = '$domain_url', article_default = $default WHERE article_id = '".$id."'";
			// If link an article to a twin copy domain, delete the domain from twin table.  	
			if ($return = $this->_db->update_sql($aQuery)) 
			{
				$Parked = ParkedDomain::getInstance($this->_db);
				$Parked->delete_twin_domain_copy($domain_url); 
			}
		}	
		return $return;
	}
	
	function unlink_articles($data)
	{
		if(!empty($data))	
		{
			if (is_numeric($data))
				$aQuery = "UPDATE articles set article_domain_id = null, article_domain = null, article_default = 0 WHERE article_id = '".$data."'";
			else
				$aQuery = "UPDATE articles set article_domain_id = null, article_domain = null, article_default = 0 WHERE article_domain = '".$data."'";
			return $this->_db->update_sql($aQuery);
		}	
		return false;
	}
	
	function unlink_this_article($id)
	{
	
		if(!empty($id))	
		{
			$aQuery = "UPDATE articles set article_domain_id = null, article_domain = null, article_default = 0 WHERE article_id = '$id'";
			return $this->_db->update_sql($aQuery);
		}	
		return false;
	}
	
	function save_comment($array, $id=0)
	{
		$comment = Comment::getInstance($this->_db);
		return $comment->save_comment($array,$id);
	}
	
	function check_article($title,$keyword) {
		if ( $article_id = $this->_db->select_one("SELECT article_id FROM articles WHERE LOWER(article_keyword) = LOWER('".$keyword."') and LOWER(article_title) = LOWER('".$title."') LIMIT 1"))
			return true;
		else
			return false;
	}
	
	function download_pretending($url,$useProxy=false,$debug=false) 
	{
		$this->_curlObj->createCurl('get',$url,null,$useProxy);
		$err = $this->_curlObj->getHttpErr();
		$status = $this->_curlObj->getHttpStatus();
		if ($debug || (($err != 0) && $useProxy))
		{
			$this->_curlObj->displayResponce();
			echo '<br>';
		}
		if (($status != 200) && ($err == 0))
		{
			if ($this->curlErrors < 5)
				echo '<script type="text/javascript">window.open("'.$url.'","");</script>'.PHP_EOL;
			else
				echo '<a href="'.$url.'" target="_blank">Ezine link : '.$url.'</a><br>'.PHP_EOL;
			$this->curlErrors++;
		}
		
		if (($err != 0) || ($status != 200)) 
			return false;
		else
			return $this->_curlObj->__toString();
	}
	
	function download_pretending_articlebase($url)
	{
		$user_agent = 'MSIE';
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec ($ch);
		curl_close ($ch);
		return $result;
	}
	
	
	function deco($content) 
	{   
	   $content = str_replace('<a', '<a style="color:#000;text-decoration:none;"', $content);
	   $content = str_replace('display:inline;', 'display:none;',$content);
	   $content = str_replace(' style="color:#000;background:#ffff66"', '', $content);
	   $content = str_replace(' style="color:#000;background:#66ffff"', '', $content);
	   $content = str_replace(' style="color:#000;background:#ffcc99"', '', $content);
	   $content = str_replace(' style="color:#000;background:#66ff99"', '', $content);
	   return $content;
	}
	
	function count_articles($domain) {
		
		$articleQuery = "SELECT count(*) FROM articles WHERE article_domain = '".$domain."' ";
		
		$count = $this->_db->select_one($articleQuery);
		return $count;
	}
	
	function count_parked_articles() {
		$output = array();
		
		$articleQuery = "SELECT article_domain AS domain_url, COUNT(*) AS articles, '' AS original_domain 
		                 FROM articles 
		                 WHERE article_domain IS NOT NULL AND article_domain_id IS NULL 
		                 GROUP BY article_domain 
		                 UNION ALL
		                 SELECT domain_parked_twin_copy AS domain_url, COUNT(*) AS articles,  domain_parked_twin_origin AS original_domain
		                 FROM domains_parked_twin 
		                 JOIN articles ON article_domain = domain_parked_twin_origin AND article_domain IS NOT NULL 
		                 GROUP BY domain_parked_twin_copy, domain_parked_twin_origin
		                 ORDER  BY 1 ";
		
		$pResults = $this->_db->select($articleQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}

	function count_sx25_articles() {
		$output = array();
		
		$articleQuery = "SELECT article_domain_id as domain_id, article_domain as domain_url, count(*) as articles FROM articles WHERE article_domain is not null and article_domain_id IS NOT NULL GROUP BY article_domain_id, article_domain ORDER  BY article_domain";
		
		$pResults = $this->_db->select($articleQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}

	function link_article_domainID($domain_url,$domain_id)
	{
	
		if(!empty($domain_id) && !empty($domain_url))	
		{
			$aQuery = "UPDATE articles set article_domain_id = '$domain_id' WHERE article_domain = '$domain_url' and article_domain = (select domain_url from domains where domain_id = '$domain_id') ";
			return $this->_db->update_sql($aQuery);
		}	
		return false;
	}

	public function del_article_domain_keyword($domain, $keyword, $equal=true)
	{
		if(!empty($domain) && !empty($keyword))	
		{
			$whenKeyword = $equal?"article_keyword = '$keyword'":"article_keyword <> '$keyword'";
			if (is_numeric($domain))
				$aQuery = "UPDATE articles set article_domain_id = null, article_domain = null, article_default = 0 WHERE article_id = '".$domain."' AND $whenKeyword";
			else
				$aQuery = "UPDATE articles set article_domain_id = null, article_domain = null, article_default = 0 WHERE article_domain = '".$domain."' AND $whenKeyword";
			return $this->_db->update_sql($aQuery);
		}	
		return false;
	}
	
	function unlink_article_domainID($domain_id)
	{
	
		if(!empty($domain_id))	
		{
			$aQuery = "UPDATE articles set article_domain_id = NULL WHERE article_domain_id = '$domain_id' ";
			return $this->_db->update_sql($aQuery);
		}	
		return false;
	}
	
	
	private function getSummary($content){
		$summary = trim($content);
		$summary = strip_tags($summary,'<b>');
		$summary = str_ireplace(array('<b>','</b>'),array('|b|','|/b|'),$summary);
		$summary = preg_replace('/<(\/?[^>]+)>/','',$summary);
		$summary = str_replace(array('|b|','|/b|'),array('<b>','</b>'),$summary);
		$summary = substr($summary, 0, 330)."...";
	
		$substr = 330;
		if (strlen($summary) > $substr)
		{	
			$testchar = substr($summary, $substr, 1);
			while ($testchar != " ") {
				$substr = $substr - 1;
				$testchar = substr($summary, $substr, 1);
			}
			$summary = substr($summary, 0, $substr);
		}	
		return $summary;
	}
	
	
}
?>
