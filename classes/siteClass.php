<?php
/*	APPLICATION:	PrincetonIT SX2
	FILE:			siteClass.php
	DESCRIPTION:	Class to extract domains data from database
	Author: 		Archie Huang on 03/04/2009
	CREATED:		19 Jan 2011 by Gordon Ye
	UPDATED:		Diana Devargas 08/02/2011		
*/

class Site extends  Model
{
	private $_db; 
	private static $_Object;
	
	private function __construct(db_class $db)
	{
		$this->_db = $db;
		self::$_Object = $this;
		return self::$_Object;
	}

    /**
     * Get the class static object
     *
     * @return self
     */
    public static function getInstance($dbObj=null) 
    {
    	/* old code compatibility */
		global $db;
		$dbObj = is_null($dbObj)?$db:$dbObj;
    	
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($dbObj);
    	}	
    	return self::$_Object;
    }
			
	public function extractTextarea($list){
		$domains = str_replace(" ", ",", $list);
		$domains = str_replace("\n", ",",$domains);
		$domains = str_replace("\t", ",",$domains);
		$domains = str_replace(",,", ",", $domains);
		$domains = explode(",", $domains);
		array_walk_recursive($domains, 'Site::trim_value');
		$domains = array_unique($domains);
		$domains = array_filter($domains);
		return $domains;
	}	

	public static function trim_value(&$value, $key){ 
		$value = trim($value);
		$value = strtolower($value);
	}
	
	public function checkDomain($url){
		$sql = "SELECT * FROM domains WHERE domain_url='".$url."'";
		if($this->_db->select_one($sql)){		
			return true;
		}else{
			return false;
		}
	}

	public function checkDomainStatus($url){
		$sql = "SELECT * FROM domains WHERE domain_url='".$url."' and status = 1 ";
		if($this->_db->select_one($sql)){		
			return true;
		}else{
			return false;
		}
	}
	
	public function markDelete($domain){
		$sql = "UPDATE domains SET status=0, domain_account_id = domain_profile_id WHERE domain_url='".$domain."' ";
		$result = $this->_db->update_sql($sql);
		return $result;
	}

	public function check_domain_id($domain) {
		$domain_id = false;
		$domainQ = "SELECT domain_id FROM domains WHERE LOWER(domain_url) = LOWER('".$domain."') LIMIT 1";
	
		$domain_id = $this->_db->select_one($domainQ);
		return $domain_id;
	
	}

	public function check_domain_user($domain) {
		$domainQ = "SELECT domain_ftp_user FROM domains WHERE LOWER(domain_url) = LOWER('".$domain."') LIMIT 1";
	
		$domain_user = $this->_db->select_one($domainQ);
		return $domain_user;
	}

	public function get_domain_info_name($domain) {

		$pQuery = "SELECT * FROM domains WHERE LOWER(domain_url) = LOWER('".$domain."') LIMIT 1";
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

	public function get_domain_info($domain_id) {
		$pQuery = "SELECT * FROM domains WHERE domain_id = '".$domain_id."' LIMIT 1";
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
	
	public function get_keyword_tracking_list(){
		$sql = "SELECT domain_url, keywords_feeds FROM domains where keywords_feeds is not null and trim(keywords_feeds) <> '' ORDER BY domain_url ASC";
		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$keywords_feeds = explode('||',$row['keywords_feeds']);
			foreach ($keywords_feeds as $kf)
			{
				if (!empty($kf)) 
				{
					$row['keywords_feeds'] = $kf;
					$output[] = $row;
				}
			}
		}
		return $output;
	}

	public function save_domain($array, $id=0)
	{
	
		if($id == '0')
		{
			$id = $this->_db->insert_array('domains', $array);
		}
		else
		{
			$res = $this->_db->update_array('domains', $array, "domain_id='".$id."'");
			if ($res == false)
				return $res;
		}
			
		return $id;
	}

	public function update_layout_account($new_layout_id, $account_id)
	{
		$updateQuery = "UPDATE domains SET domain_layout_id = '".$new_layout_id."' WHERE domain_account_id = '".$account_id."'";
		return $this->_db->update_sql($updateQuery);
	}
	
	public function update_domain($array, $url)
	{	
		if(empty($url))
			return false;
		else
			$resp = $this->_db->update_array('domains', $array, "domain_url='".$url."'");
			
		return $resp;
	}
	
	public function del_domain($id,$keep_articles=false,$mark_domain=false)
	{
		$article = Article::getInstance($this->_db);
		$image = new Image($this->_db);
		
		$info = $this->get_domain_info($id);
		if ($info)
		{
			if (!$keep_articles)
				$article->unlink_articles($info['domain_url']);
			else 
				$article->unlink_article_domainID($id);
			
			$image->unlink_images($id);
			
			$cQuery = "DELETE FROM messages WHERE domain_id = '".$id."'";
			$this->_db->delete($cQuery);
			
			$cQuery = "DELETE FROM comments WHERE comment_domain_id = '".$id."'";
			$this->_db->delete($cQuery);
		
			$cQuery = "DELETE FROM votes WHERE domain_id = '".$id."'";
			$this->_db->delete($cQuery);
			
			$mQuery = "DELETE FROM menus WHERE menu_domain_id = '".$id."'";
			$this->_db->delete($mQuery);
			 
			$mQuery = "DELETE FROM css_backup WHERE domain like '".$info['domain_url']."/%'";
			$this->_db->delete($mQuery);
			 
			$mQuery = "DELETE FROM mapping_keyword WHERE domain_id = '".$id."'";
			$this->_db->delete($mQuery);
			
			if ($mark_domain)
			{
				$sql = "UPDATE domains SET status=3, domain_account_id = domain_profile_id WHERE domain_id = '".$id."'";
				$this->_db->update_sql($sql);
				return true;
			}
			else 
			{
				$dQuery = "DELETE FROM domains WHERE domain_id = '".$id."'";
				if($this->_db->delete($dQuery))
					return true;
			}
				
		}
		
		return false;
	}

	public function save_relation($array, $id=0)
	{
		if($id == 0)
			$id = $this->_db->insert_array('relations', $array);
		else
			$this->_db->update_array('relations', $array, "relation_id='".$id."'");
			
		return $id;
	}

	public function line_to_format($options)
	{
			$options = str_replace("
	", "||", $options);
			$options = str_replace("\n", "||",$options);
			$options = str_replace("\t", "||",$options);
			$options = str_replace("||||", "||", $options);
			return $options;
	}

	public function get_domain_data_list($searchType='',$search='')
	{
		$output = array();
		$sql = "SELECT p.profile_id, p.profile_name, d.domain_id, d.domain_url, d.domain_title, d.domain_keyword, d.domain_feedtype, d.domain_feedid, d.domain_createdate, d.domain_product_category, d.status, a.account_name, l.layout_id, l.layout_name, c.theme_name, CONCAT(c.background,'_',c.header,'_',c.color) AS theme_code FROM domains d 
				LEFT JOIN accounts a ON (d.domain_account_id=a.account_id) 
				LEFT JOIN profiles AS p ON p.profile_id = d.domain_profile_id
				LEFT JOIN layouts AS l ON l.layout_id = d.domain_layout_id
				LEFT JOIN css_pending AS c ON c.id = d.domain_theme_id
				{SEARCH} ORDER BY a.account_name ASC, domain_url";

		if (is_array($search)) 
		{
			array_walk_recursive($search, 'Site::quote_value');
			$searchQ = ' in ('.implode(',',$search).') ';
		} 
		else 
		{
			$searchQ = (is_numeric($search)?' = ':' like ')." '".trim($search)."'";
		}
				
		switch($searchType)
		{
			case 'account'	:$sql = str_replace('{SEARCH}'," WHERE d.domain_account_id $searchQ ",$sql);
							break;
			case 'domain'	:$sql = str_replace('{SEARCH}'," WHERE d.domain_url $searchQ ",$sql);
							break;
			case 'status'	:$sql = str_replace('{SEARCH}'," WHERE d.status $searchQ ",$sql);
							break;
			case 'name'		:$sql = str_replace('{SEARCH}'," WHERE domain_url '%$searchQ%' or domain_title '%$searchQ%' ",$sql);
							break;
			case 'layout'	:$sql = str_replace('{SEARCH}'," WHERE d.domain_layout_id $searchQ ",$sql);
							break;
			case 'theme'	:$sql = str_replace('{SEARCH}'," WHERE d.domain_theme_id $searchQ ",$sql);
							break;
			case 'keyword'	:$sql = str_replace('{SEARCH}'," WHERE d.domain_keyword $searchQ ",$sql);
							break;
			default : 		$sql = str_replace('{SEARCH}','',$sql);
							break;
		}
		
		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}

	public function get_related_domains($search='',$limit=6){
		
		$limit = empty($limit)?'':" LIMIT $limit";
		$output = array();
		$sql = "SELECT * FROM domains WHERE `domain_keyword` LIKE '$search' ORDER BY domain_id DESC $limit";

		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}
	
	public function get_missed_module_element($modules, $domain_id, $domain_url, $domain_keyword)
	{
		$return = array();
		$domain_url = trim($domain_url);
		$domain_keyword = trim($domain_keyword);
		
		foreach($modules as $k=>$m){
			switch(strtoupper($k)):
				case('ARTICLE'):
					$Article = Article::getInstance($this->_db);
					$articleNum = $Article->count_articles($domain_url);
					if($articleNum < $m){
						$return['ARTICLE'] =  $m - $articleNum;
					}
					break;
				case('DIRECTORY'):
					$Directory = Dty::getInstance($this->_db);
					$directoryNum = $Directory->count_total_directories($domain_keyword); 
					if($directoryNum < $m){
						$return['DIRECTORY'] =  $m - $directoryNum;
					}
					break;
				case('QUESTION'):
					$QA = QuestionAnswer::getInstance($this->_db);
					$qaNum = $QA->count_total_qa($domain_keyword);
					if($qaNum < $m){
						$return['QUESTION'] = $m - $qaNum;
					}
					break;
				case('EVENT'):
					$Event = Event::getInstance($this->_db);
					$eNum = $Event->count_events($domain_keyword);
					if($eNum < $m){
						$return['EVENT'] =  $m - $eNum;
					}
					break;
				case('MENU_IMAGE'):
					$img = Image::getInstance($this->_db);
					$existingImages = $img->getDomainImage($domain_id);
					if($existingImages < $m){
						$return['MENU_IMAGE'] =  $m - $existingImages;
					}
					break;
			endswitch;
		}
		return $return;
	}
	
	public static function quote_value(&$value, $key){ 
		$value = "'$value'";
	}
	
}

?>
