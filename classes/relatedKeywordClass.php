<?php
/*	APPLICATION:	PrincetonIT SX2
	FILE:			RelatedKeywordClass.php
	DESCRIPTION:	Class to manage the related_keywords table in database
	Author: 		Diana Devargas 
	CREATED:		18/04/2012
	UPDATED:				
*/

class RelatedKeyword extends Model
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
			
	public function get_relatedKeyword($keyword) 
	{
		if($kw = $this->_db->select_one("select related_keyword From related_keyword_qa where related_keyword_original = '$keyword' LIMIT 1")) 
		{
			return $kw;
		}
		else
		{
			return false;
		}
	}

	public function get_relatedKeywords($keyword)
	{
		$output = array();
		$sql = "select related_keyword From related_keyword_qa where related_keyword_original = '$keyword' ORDER BY related_keyword ";

		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row['related_keyword'];
		}
		return $output;
	}
	
	public function get_originalKeyword($keyword) 
	{
		if($kw = $this->_db->select_one("select related_keyword_original From related_keyword_qa where related_keyword = '$keyword' LIMIT 1")) 
		{
			return $kw;
		}
		else
		{
			return false;
		}
	}

	public function get_originalKeywords($keyword)
	{
		$output = array();
		$sql = "select related_keyword_original From related_keyword_qa where related_keyword = '$keyword' ORDER BY related_keyword ";

		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row['related_keyword_original'];
		}
		return $output;
	}
	
	public function get_relatedId($original,$keyword) 
	{
		if($id = $this->_db->select_one("select related_keyword_id From related_keyword_qa where related_keyword_original = '$original' and related_keyword = '$keyword'")) 
		{
			return $id;
		}
		else
		{
			return false;
		}
	}

	
	public function save_relatedKeyword($array, $id=0)
	{
		if (empty($array['related_keyword_original']) || empty($array['related_keyword']))
			return false;
			
		$idR = $this->get_relatedId($array['related_keyword_original'], $array['related_keyword']);
		$id = empty($id)?$idR:$id;
				
		if(empty($id))
		{
			$id = $this->_db->insert_array('related_keyword_qa', $array);
		}
		elseif ($idR != $id)
		{
			$this->del_relatedKeyword($id);
			$id = $idR;
		}
		return $id;
	}

	public function del_relatedKeyword($id)
	{
		$dQuery = "DELETE FROM related_keyword_qa WHERE related_keyword_id = '$id'";
		if($this->_db->delete($dQuery))
			return true;
		
		return false;
	}

	public function get_relatedKeyword_list(){
		$output = array();
		$sql = "SELECT related_keyword_original, related_keyword, related_keyword_createdate from related_keyword_qa ORDER BY related_keyword_original, related_keyword ";

		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}

}

?>
