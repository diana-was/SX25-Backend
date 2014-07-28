<?php
/*	APPLICATION:	PrincetonIT SX2
	FILE:			matchingKeywordClass.php
	DESCRIPTION:	Class to extract menus data from database
	Author: 		Archie Huang on 03/04/2009
	CREATED:		19 Jan 2011 by Gordon Ye
	UPDATED:		Diana Devargas 08/02/2011		
*/

class MappingKeyword extends Model
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
			
		
	public function getMappingKeyword($domain_id, $keyword) 
	{
		$mk = $this->_db->select_one("select mapping_keyword_mapping from mapping_keyword where domain_id='$domain_id' AND mapping_keyword_original='$keyword' order by mapping_keyword_id DESC limit 1");		
		return $mk;
	}


	public function saveMappingKeyword($array, $id=0)
	{
	
		if($id == '0')
		{
			$id = $this->_db->insert_array('mapping_keyword', $array); 
		}
		else
		{
			$res = $this->_db->update_array('mapping_keyword', $array, "mapping_keyword_id='".$id."'");
		}
		return $id;
	}

	public function delMappingKeyword($id)
	{
		$dQuery = "DELETE FROM mapping_keyword WHERE mapping_keyword_id = '".$id."'";
		if($this->_db->delete($dQuery))
			return true;
		
		return false;
	}

	public function check_mapping_id($domain_id, $keyword) 
	{
		$mapping_id = $this->_db->select_one("select mapping_keyword_id from mapping_keyword where domain_id='$domain_id' AND mapping_keyword_original='$keyword' order by mapping_keyword_id DESC limit 1");
		return $mapping_id;
	}
	
	public function get_domain_mapping_keywords($profilesArray)
	{
		$where = empty($profilesArray)?'':' and domain_profile_id in ('.implode(',',$profilesArray).') ';
		$sql = "SELECT p.profile_name `profile`, d.domain_url, m.mapping_keyword_original `original keyword`, m.mapping_keyword_mapping `mapping keyword` 
				from mapping_keyword m 
				JOIN domains as d on m.domain_id = d.domain_id ".$where." 
				left JOIN profiles as p ON d.domain_profile_id = p.profile_id
				ORDER BY p.profile_name ASC, d.domain_url, m.mapping_keyword_original ";
		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}

}

?>
