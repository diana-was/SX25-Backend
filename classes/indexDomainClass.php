<?php
/**
 * Indexing Domain Class
 * @author: Khoa Nguyen on 03/09/2012
 */

class IndexDomain extends Model{
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
	
	public function getRowIndexDomain($domain){
		$pQuery = "SELECT di.*, d.domain_id FROM domains_indexing AS di
							INNER JOIN domains AS d ON d.domain_id = di.indexing_domain_id
							WHERE LOWER(d.domain_url) = LOWER('$domain') LIMIT 1";
		
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
	
	public function saveIndexDomain($array, $id = 0){
		if($id == 0){
			$id = $this->_db->insert_array('domains_indexing', $array);
		}else{
			$updateSQL = "UPDATE domains_indexing SET indexing_results = '".$array['indexing_results']."' WHERE indexing_id = '".$id."'";
			$this->_db->update_sql($updateSQL);
		}
		return $id;
	}
}