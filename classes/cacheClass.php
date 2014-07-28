<?php 
/**	APPLICATION:	SX25/News.php
*	FILE:			Cache.php
*	DESCRIPTION:	cache news content for 24 hours
*	CREATED:		15 October 2010 by Gordon Ye
*	UPDATED:									
*/



class Cache extends  Model
{

	private $_db; 
	private static $_Object;
	protected $duration = 3; 
	
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
	
	 public function getcache($keyword, $hours = 24) {
		$keyword = strtolower($keyword);
		$pQuery = "SELECT * FROM news WHERE keyword='".$keyword."' and created_date >= DATE_SUB(NOW(),INTERVAL $hours HOUR) LIMIT 1";
		$pResults = $this->_db->select($pQuery);
		if(!$news=$this->_db->get_row($pResults)){			
				return false;
		}      
        
        //Get contents
        return stripslashes($news['content']);
    }
    
    
    
    /**
    * Remove specific cache file
    * Wildcard * supported to match any ENDING
    * @return void
    */
    public function removecache($id) {
		$pQuery = "DELETE FROM news WHERE id='".$id."' ";
		$pResults = $this->_db->delete($pQuery);
    }
	
	public function cleanCache(){
		$pQuery = 'DELETE FROM news WHERE created_date < DATE_SUB(NOW(),INTERVAL '.$this->duration.' DAY) ';
		$pResults = $this->_db->delete($pQuery);
	}
    
    
    /**
    * Write cache file
    * @return bool
    */
    public function writecache($keyword, $content) {
		if($content!='' && $content!='[]'){
			$keyword = strtolower($keyword);
			$content = addslashes($content);
			$pQuery = "INSERT INTO news (keyword, content) VALUES ('$keyword','$content')";
			$pResults = $this->_db->insert_sql($pQuery);
		}
    }
	
	 public function update_sql($keyword, $content) {
		$content = addslashes($content);
		$current = time();
		$pQuery = "UPDATE news SET content='$content', created_date='$current' WHERE keyword = '$keyword'";
		$pResults = $this->_db->insert_sql($pQuery);
    }
    
    
    /**
    * Safen up file name. All other methods use this, ensures consistent access
    * @return string
    */
    private function filename($file) {
        $file = strtolower($file);
        $file = preg_replace('~[^a-z0-9/\\_\-]~', '', $file);
        return $file;    
    }
}

?>