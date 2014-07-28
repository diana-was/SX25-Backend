<?php
/**
 * Page class
 * Author: Archie Huang on 08/05/2009
 * 
 */

class Page extends Model
{
	private $_db; 
	private static $_Object;
	
	public function __construct(db_class $db)
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
			
	public function check_page_id($page_name) 
	{
		$page_id = false;
		$pageQ = "SELECT page_id FROM pages WHERE page_name LIKE '".$page_name."' LIMIT 1";
	
		$page_id = $this->_db->select_one($pageQ);
		return $page_id;
	}
	

	public function get_page_info($page_id) 
	{
		$pQuery = "SELECT * FROM pages WHERE page_id = '".$page_id."' LIMIT 1";
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

	public function get_pagename_info ($page_name) 
	{
		$pQuery = "SELECT * FROM pages WHERE page_name LIKE '".$page_name."' LIMIT 1";
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
	
	public function save_page($array, $id=0, &$err='')
	{
		if($id == '0')
		{
			$id = $this->_db->insert_array('pages', $array);
		}
		else
		{
			$this->_db->update_array('pages', $array, "page_id='".$id."'");
		}	
		return $id;
	}


	public function get_pages() 
	{
		$returnArray = array();
		$pQuery = "SELECT page_id, page_name, page_display_name FROM pages ORDER BY page_name";
		$pResults = $this->_db->select($pQuery);
		while($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$returnArray[] = $pRow;
		}
		return $returnArray;
	}
	
	private function replace_tag($string)
	{
		$string = str_replace('&lt;', '<', $string);
		$string = str_replace('&gt;', '>', $string);
		return $string;
	}

}
?>
