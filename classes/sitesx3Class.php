<?php
/*	APPLICATION:	PrincetonIT SX2
	FILE:			sitesx3Class.php
	DESCRIPTION:	Class to extract domains data from sx3 database
	CREATED:		Diana Devargas 08/02/2011
	UPDATED:				
*/

class Sitesx3 extends  Model
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
    public static function getInstance(db_class $dbObj=null) 
    {
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
    }
			
	public static function extractTextarea($list){
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
		$sql = "UPDATE domains SET status=0 WHERE domain_url='".$domain."' ";
		$result = $this->_db->update_sql($sql);
		return $result;
	}

	public function check_domain_id($domain) {
		$domainQ = "SELECT domain_id FROM domains WHERE LOWER(domain_url) = LOWER('".$domain."') LIMIT 1";
		$domain_id = $this->_db->select_one($domainQ);
		return $domain_id;
	
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

	public function save_domain($array, $id=0)
	{
	
		if($id == '0')
		{
			$id = $this->_db->insert_array('domains', $array);
		}
		else
			$this->_db->update_array('domains', $array, "domain_id='".$id."'");
			
		return $id;
	}

	public function get_domains_by_lot($lot_id) {
		$pQuery = " SELECT d.*, p.profile_ip, p.profile_name 
					FROM domains AS d
					LEFT JOIN PROFILES AS p ON p.profile_id = d.domain_profile 
					WHERE domain_lot = '".$lot_id."'";
		$pResults = $this->_db->select($pQuery);

		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$result[] = $aRow;
		}
		return $result;
		
	}

	public function get_domain_options($domain_option_id) {
		$pQuery = "SELECT * FROM site_options WHERE option_id = ".$domain_option_id." LIMIT 1";
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
	
}

?>
