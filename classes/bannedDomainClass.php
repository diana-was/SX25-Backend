<?php
/*	APPLICATION:	PrincetonIT SX25
	FILE:			bannedDomainClass.php
	DESCRIPTION:	Class to extract banned domains data from database
	Author:		    17 Aug 2012 by Gordon Ye
*/

class BannedDomain extends  Model
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
			
	public function get_banned_domain_list($searchType='',$search='', $source = 'SX25')
	{
		$output = array();
		
		if (strtolower($source) != 'parked')
		{
			$sql = "SELECT p.profile_name, d.domain_url, d.domain_title, bd.banned_keyword, bd.banned_reason, bd.banned_date_added as banned, count(*) as ocurrence, d.domain_feedtype, d.domain_feedid, a.account_name, l.layout_id, l.layout_name 
					FROM banned_domains bd 
					LEFT JOIN domains d ON (bd.banned_domain=d.domain_url) 
					LEFT JOIN accounts a ON (d.domain_account_id=a.account_id) 
					LEFT JOIN profiles AS p ON p.profile_id = d.domain_profile_id
					LEFT JOIN layouts AS l ON l.layout_id = d.domain_layout_id
					WHERE banned_source = 'SX25' {SEARCH}  group by p.profile_name, d.domain_url, d.domain_title, bd.banned_keyword, bd.banned_reason, bd.banned_date_added, d.domain_feedtype, d.domain_feedid, a.account_name, l.layout_id, l.layout_name
					ORDER BY a.account_name ASC, domain_url";
		}
		else 
		{
			$sql = "SELECT d.domain_url, bd.banned_keyword, bd.banned_reason, bd.banned_date_added as banned, count(*) as ocurrence, l.layout_id, l.layout_name 
					FROM banned_domains bd 
					LEFT JOIN domains_parked d ON (bd.banned_domain=d.domain_url) 
					LEFT JOIN layouts_parked AS l ON l.layout_id = d.domain_layout_id
					WHERE banned_source = 'Parked' {SEARCH} group by d.domain_url, bd.banned_keyword, bd.banned_reason, bd.banned_date_added, l.layout_id, l.layout_name
					ORDER BY domain_url";
		}
		if (is_array($search)) 
		{
			if ($searchType == 'date')
			{
				$from   = isset($search['datefrom'])?trim($search['datefrom']):'';
				$to   	= isset($search['dateto'])?trim($search['dateto']):'';
				//---- work out date range ----
				if(empty($from) && empty($to))
					$searchQ = '';
				elseif (empty($from))
					$searchQ = " <= '".$to."'";
				elseif (empty($to))
					$searchQ = "  >= '".$from."' ";
				else
					$searchQ = " between '".$from."' AND '".$to."'";
			}
			else 
			{
				array_walk_recursive($search, 'Site::quote_value');
				$searchQ = ' in ('.implode(',',$search).') ';
			}
		} 
		else 
		{
			$searchQ = (is_numeric($search)?' = ':' like ')." '".trim($search)."'";
		}
		
		switch($searchType)
		{
			case 'account'	:$sql = str_replace('{SEARCH}'," AND d.domain_account_id $searchQ ",$sql);
							break;
			case 'domain'	:$sql = str_replace('{SEARCH}'," AND d.domain_url $searchQ ",$sql);
							break;
			case 'status'	:$sql = str_replace('{SEARCH}'," AND d.status $searchQ ",$sql);
							break;
			case 'name'		:$sql = str_replace('{SEARCH}'," AND domain_url '%$searchQ%' or domain_title '%$searchQ%' ",$sql);
							break;
			case 'layout'	:$sql = str_replace('{SEARCH}'," AND d.domain_layout_id $searchQ ",$sql);
							break;
			case 'theme'	:$sql = str_replace('{SEARCH}'," AND d.domain_theme_id $searchQ ",$sql);
							break;
			case 'keyword'	:$sql = str_replace('{SEARCH}'," AND d.domain_keyword $searchQ ",$sql);
							break;
			case 'date'		:$sql = str_replace('{SEARCH}',empty($searchQ)?'':" AND bd.banned_date_added $searchQ ",$sql);
							break;
			default : 		$sql = str_replace('{SEARCH}','',$sql);
							break;
		}
				//echo $sql;
		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}

	public function save_banned($array)
	{	 
		$domain 	= isset($array['domain'])?str_ireplace('www.','',trim($array['domain'])):'';
		$keyword 	= isset($array['keyword'])?trim($array['keyword']):'';
		$reason 	= isset($array['banned_reason'])?trim($array['banned_reason']):'';
		$source		= isset($array['source'])?strtolower(trim($array['source'])):'';
		
		if (!empty($domain))
		{
			// date set in GMT
			$data 		= array('banned_domain'=>$domain, 'banned_reason'=>$reason, 'banned_keyword'=>$keyword, 'banned_date_added'=>gmdate('Y-m-d'), 'banned_source' => (($source=='parked')?'Parked':'SX25'));
			return $this->_db->insert_array('banned_domains', $data);
		}
		else
			return false;
	}

	
	public static function quote_value(&$value, $key)
	{ 
		$value = "'$value'";
	}

	
}

?>
