<?php
/*	APPLICATION:	PrincetonIT SX2
	FILE:			ParkedDomainClass.php
	DESCRIPTION:	Class to extract parked domains data from database
	Author: 		Diana De vargas
	CREATED:		21 April 2011 
	UPDATED:				
*/

class ParkedDomain extends Model
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
		$sql = "SELECT domain_id FROM domains_parked WHERE domain_url='".$url."'";
		if($this->_db->select_one($sql)){		
			return true;
		}else{
			return false;
		}
	}

	public function check_domain_status($url){
		$sql = "SELECT status FROM domains_parked WHERE domain_url='".$url."' and status = 1 ";
		if($this->_db->select_one($sql)){		
			return true;
		}else{
			return false;
		}
	}
	
	public function check_domain_id($domain) {
		$domain_id = false;
		$domainQ = "SELECT domain_id FROM domains_parked WHERE LOWER(domain_url) = LOWER('".$domain."') LIMIT 1";
	
		$domain_id = $this->_db->select_one($domainQ);
		return $domain_id;
	
	}

	public function get_domain_info_name($domain) {

		$pQuery = "SELECT * FROM domains_parked WHERE LOWER(domain_url) = LOWER('".$domain."') LIMIT 1";
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
		$pQuery = "SELECT * FROM domains_parked WHERE domain_id = '".$domain_id."' LIMIT 1";
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

	public function save_domain($array, $id=0)
	{
	
		if($id == '0')
		{
			$id = $this->_db->insert_array('domains_parked', $array);
		}
		else
		{
			$res = $this->_db->update_array('domains_parked', $array, "domain_id='".$id."'");
			if ($res === false)
				return $res;
		}
			
		return $id;
	}

	public function update_domain($array, $url)
	{
	
		if(empty($url))
			return false;
		else
			$resp = $this->_db->update_array('domains_parked', $array, "domain_url='".$url."'");
			
		return $resp;
	}
	
	public function del_domain($domain,$keep_articles=false)
	{
		$domain = trim(strtolower($domain));
		$dQuery = "DELETE FROM domains_parked WHERE LOWER(domain_url) = '$domain' ";
			
		if($this->_db->delete($dQuery))
		{
			$article = Article::getInstance($this->_db);
			if (!$keep_articles)
				$article->unlink_articles($domain);
			// delete the domain from twin table
			$this->delete_twin_domain_copy($domain);
			return true;
		}
		else
			return false;
	}

	public function get_domain_data_list($searchType='',$search=''){
		$output = array();
		$sql = "SELECT d.domain_url, d.domain_createdate AS genz_css_createdate, l.layout_name, p.theme_name, CONCAT(p.background,'_',p.header,'_',p.color) AS theme_code
		        ,d.domain_keyword as keyword 
				FROM domains_parked d 
				LEFT JOIN layouts_parked AS l ON l.layout_id = d.domain_layout_id
				LEFT JOIN css_pending AS p ON p.id = d.domain_theme_id
				{SEARCH} ORDER BY d.domain_url ";

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
			case 'domain'	:$sql = str_replace('{SEARCH}'," WHERE d.domain_url $searchQ ",$sql);
							break;
			case 'layout'	:$sql = str_replace('{SEARCH}'," WHERE d.domain_layout_id $searchQ ",$sql);
							break;
			case 'theme'	:$sql = str_replace('{SEARCH}'," WHERE d.domain_theme_id $searchQ ",$sql);
							break;
			case 'keyword'	:$sql = str_replace('{SEARCH}'," WHERE d.domain_keyword $searchQ ",$sql);
							break;
			case 'status'	:$sql = str_replace('{SEARCH}'," WHERE d.domain_status $searchQ ",$sql);
			default : 		$sql = str_replace('{SEARCH}','',$sql);
							break;
		}
		
		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}
	
	public function setRelatedKeywords($domain,$values)
	{
		$values = array_map('trim',$values);
		if (empty($domain) || !isset($values['keyword']) || empty($values['keyword']))
			return;
		
		$values_array['keyword'] = $values['keyword'];
		$relQ = "SELECT keyword_id FROM related_keywords WHERE LOWER(keyword) = LOWER('".$values_array['keyword']."') ";
		for($i=1; $i<=6; $i++){
			if (isset($values["keyword_related$i"]) && !empty($values["keyword_related$i"]))
			{
				$values_array["keyword_related$i"] = $values["keyword_related$i"];
				$relQ .= " and '".strtolower($values_array["keyword_related$i"])."' in (keyword_related1,keyword_related2,keyword_related3,keyword_related4,keyword_related5,keyword_related6) ";
			}
		}
		$relQ .=  ' LIMIT 1 ';

		$keyword_id = $this->_db->select_one($relQ);
		if (!$keyword_id || $keyword_id == 0)
			$keyword_id = $this->_db->insert_array('related_keywords', $values_array);
		
			
		$domain_id = $this->check_domain_id($domain);
		if ($domain_id)
			$this->save_domain(array('domain_keyword' => $values_array['keyword'],'domain_related_keyword' => $keyword_id),$domain_id);
		else 
			$this->save_domain(array('domain_url' => $domain,'domain_keyword' => $values_array['keyword'],'domain_related_keyword' => $keyword_id));
	}
	
	public function getRelatedKeywords($id) 
	{
		$pQuery = "SELECT * FROM related_keywords WHERE keyword_id = '".$id."' LIMIT 1";
		$pResults = $this->_db->select($pQuery);
		if($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			return $this->getRelated($pRow);
		}
		else
		{
			return false;
		}
	}

	public function getRelatedKeywordsFor($keyword){
		$output = array();
		$sql = "SELECT * FROM related_keywords WHERE LOWER(keyword) = LOWER('".$keyword."')";

		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output = array_merge($output,$this->getRelated($row));
		}
		return array_unique ($output);
	}
	
	private function getRelated($row)
	{
		$output = array();
		for($i=1; $i<=6; $i++){
			if (!empty($row["keyword_related$i"]))
				$output[] = strtolower(trim($row["keyword_related$i"]));
		}
		return array_unique($output);
	}

	
	public function getDomainRelatedKeywords($domain,$setDomain=false) 
	{
		$pQuery = "SELECT domains_parked.*, related_keywords.* FROM domains_parked 
					LEFT JOIN related_keywords on keyword_id = domain_related_keyword and (keyword_related1 IS NOT NULL OR keyword_related2 IS NOT NULL OR  keyword_related3 IS NOT NULL OR  keyword_related4 IS NOT NULL OR  keyword_related5 IS NOT NULL OR  keyword_related6 IS NOT NULL) 
					WHERE LOWER(domain_url) = LOWER('".$domain."') LIMIT 1";
		$pResults = $this->_db->select($pQuery);
		if($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			if (!empty($pRow['keyword_id']))
				return $this->getRelated($pRow);
			else
			{
				if ($setDomain)
				{
					$this->setDomainRelatedKeywords($domain);
					return $this->getDomainRelatedKeywords($domain,false);
				}
				else
					return $this->getRelatedKeywordsFor($pRow['domain_keyword']);
			}
		}
		return false;
	}
	
	public function setDomainRelatedKeywords($domain,$keyword='')
	{
		$curlObj = new SingleCurl();
		$curlObj->createCurl('get',"http://$domain");
		$resultpage = $curlObj->__toString();	// if the domain has amz_default_related template get domain and keyword
		
		$begin = strpos($resultpage, '<!-- do not replace or delete this code - BEGIN -->');
		$end = strpos($resultpage, '<!-- END - do not replace or delete this code -->');
		if ($begin == false || $end == false)
		{
			if (!empty($keyword))
				$this->setRelatedKeywords($domain,array('keyword' => $keyword));
			return;
		}
			
		$matches = substr( $resultpage,$begin + 54, $end - ($begin + 54));
		$matches = trim(strip_tags($matches));
		preg_match ( "/call='([^']*)'/", $matches, $match,PREG_OFFSET_CAPTURE);
		
		if (isset($match[1][0])) 
		{
			// Get the keyword from the domain
			$domainInfo = $this->get_domain_info_name($domain);
			if ($domainInfo)
			{
				$domain_id = $domainInfo['domain_id'];
				$keyword = empty($domainInfo['domain_keyword'])?$keyword:$domainInfo['domain_keyword'];

				$parse = @parse_url ( $match[1][0] );
	            $query = isset($parse['query'])?$parse['query']:'';
	            parse_str($query,$values_array);
				$values_array = array_map('trim',$values_array);

				$values = array();
				$values['keyword'] = (!isset($values_array['keyword']) || empty($values_array['keyword']))?strtolower($keyword):strtolower($values_array['keyword']);
				if (empty($values['keyword']))
					return;
				
				$relQ = "SELECT keyword_id FROM related_keywords WHERE LOWER(keyword) = '".$values['keyword']."' ";
				$k=0;
				for($i=1; $i<=6; $i++)
				{
					$values["keyword_related$i"] = isset($values_array["related$i"])?strtolower($values_array["related$i"]):'';
					if (!empty($values["keyword_related$i"]))
					{
						$relQ .= ($k == 0)?" and (":" and ";
						$relQ .= "'".$values["keyword_related$i"]."' in (keyword_related1,keyword_related2,keyword_related3,keyword_related4,keyword_related5,keyword_related6) ";
						$k++;
					}
				}
				if ($k > 0)
					$relQ .= ' LIMIT 1 ';
				else
				{
					if (empty($domainInfo['domain_keyword']))
						$this->setRelatedKeywords($domain,array('keyword' => $values['keyword']));
					return;
				}
				
				$keyword_id = $this->_db->select_one($relQ);
				if (!$keyword_id || $keyword_id == 0)
				{
					$relQ = "SELECT keyword_id FROM related_keywords WHERE LOWER(keyword) = '".$values['keyword']."'  
							and keyword_related1 is null and keyword_related2 is null and keyword_related3 is null and keyword_related4 is null and keyword_related5 is null and keyword_related6 is null LIMIT 1 ";
					$keyword_id = $this->_db->select_one($relQ);
					if (!$keyword_id || $keyword_id == 0)
						$keyword_id = $this->_db->insert_array('related_keywords', $values);
					else
						$this->_db->update_array('related_keywords', $values, "keyword_id='".$keyword_id."'");
				}	
				
				$this->save_domain(array('domain_keyword' => $values['keyword'],'domain_related_keyword' => $keyword_id),$domain_id);
			
			}
		}
	}

	public function save_twin_domain($origin_domain, $copy_domain)
	{
		$origin_domain	= strtolower(trim($origin_domain)); 
		$copy_domain	= strtolower(trim($copy_domain));
		$domainQ = "SELECT domain_parked_twin_id FROM domains_parked_twin WHERE LOWER(domain_parked_twin_copy) = '".$copy_domain."' LIMIT 1";
		$domain_id = $this->_db->select_one($domainQ);
		
		if($domain_id)
		{
			$aQuery = "UPDATE domains_parked_twin set domain_parked_twin_origin = '$origin_domain', domain_parked_twin_updatedate = CURRENT_TIMESTAMP WHERE domain_parked_twin_id = '$domain_id'";			
			$this->_db->update_sql($aQuery);
		}
		else
		{
			$sql = "INSERT INTO domains_parked_twin (domain_parked_twin_origin, domain_parked_twin_copy) VALUES ('$origin_domain', '$copy_domain')";
			$domain_id = $this->_db->insert_sql($sql);
		}
			
		return $domain_id;
	}
	
	public function delete_twin_domain($origin_domain, $copy_domain)
	{
		$origin_domain	= strtolower(trim($origin_domain)); 
		$copy_domain	= strtolower(trim($copy_domain));
		$dQuery = "DELETE FROM domains_parked_twin WHERE LOWER(domain_parked_twin_origin) = '".$origin_domain."' and LOWER(domain_parked_twin_copy) = '".$copy_domain."' ";
		if($this->_db->delete($dQuery))
			return true;
		else
			return false;
	}

	public function delete_twin_domain_copy($copy_domain)
	{
		$copy_domain	= strtolower(trim($copy_domain));
		$dQuery = "DELETE FROM domains_parked_twin WHERE LOWER(domain_parked_twin_copy) = '".$copy_domain."' ";
		if($this->_db->delete($dQuery))
			return true;
		else
			return false;
	}
	
	public static function quote_value(&$value, $key)
	{ 
		$value = "'$value'";
	}

	public function is_origin_twin_domain($origin_domain)
	{
		$sql = "SELECT domain_parked_twin_copy FROM domains_parked_twin WHERE LOWER(domain_parked_twin_origin) = '".$origin_domain."' LIMIT 1 ";
		if($this->_db->select_one($sql)){		
			return true;
		}else{
			return false;
		}
	}
	
}

?>
