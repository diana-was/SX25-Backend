<?php
/**
 * Business class
 * Author: Khoa Nguyen on 07/08/2012
 */
class Business extends Model
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
	public static function getInstance(db_class $db)
	{
		$class = __CLASS__;
		if (!isset(self::$_Object)) {
			return new $class($db);
		}
		return self::$_Object;
	}
	
	/**
	 * @author	Khoa Nguyen
	 * @desc	insert new business
	 * @return	int business_id
	 */
	
	public function save_business($array, $id=0)
	{
		if($id == '0')
			$id = $this->_db->insert_array('businesses', $array);
		else
			$this->_db->update_array('businesses', $array, "business_id='".$id."'");
			
		return $id;
	}
	/**
	 * @author	Khoa Nguyen
	 * @desc	get business list
	 * @return	business array
	 */
	public function getBusinessList()
	{
		$result = array();
		$pQuery = "SELECT * FROM businesses ORDER BY business_title ";
		$pResults = $this->_db->select($pQuery);
		
		while ($aRow = $this->_db->get_row($pResults, 'MYSQL_ASSOC')) {
			$result[] = $aRow;
		}
		return $result;
	}
	/**
	 * @author	Khoa Nguyen
	 * @desc	check business if keyword exist
	 * @param unknown_type $keyword
	 */
	public function checkBusiness($siteurl, $title, $keyword){
		$sql = "SELECT business_id FROM businesses WHERE business_keyword = '".$keyword."' 
														AND business_siteurl = '".$siteurl."' 
														AND business_title = '".$title."'";
		if($this->_db->select_one($sql)){
			return true;
		}else{
			return false;
		}
	}
}