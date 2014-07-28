<?php
/*	APPLICATION:	PrincetonIT SX2
	FILE:			menuClass.php
	DESCRIPTION:	Class to extract menus data from database
	Author: 		Archie Huang on 03/04/2009
	CREATED:		19 Jan 2011 by Gordon Ye
	UPDATED:		Diana Devargas 08/02/2011		
*/

class Menu extends Model
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
			
	public function get_menu_info($menu_id) {
		$pQuery = "SELECT * FROM menus WHERE menu_id = '".$menu_id."' LIMIT 1";
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

	public function save_menu($array, $id=0)
	{
	
		if($id == '0')
		{
			$id = $this->_db->insert_array('menus', $array);
		}
		else
		{
			$res = $this->_db->update_array('menus', $array, "menu_id='".$id."'");
			if ($res == false)
				return $res;
		}
			
		return $id;
	}

	public function del_menu($id)
	{
		$dQuery = "DELETE FROM menus WHERE menu_id = '".$id."'";
		if($this->_db->delete($dQuery))
			return true;
		
		return false;
	}

	public function checkMenu($name,$domain_id){
		$sql = "SELECT menu_id FROM menus WHERE menu_name_display='".$name."' and menu_domain_id = '".$domain_id."'";
		if($this->_db->select_one($sql)){		
			return true;
		}else{
			return false;
		}
	}	

	public function get_domain_menus($domain_id){
		$output = array();
		$sql = "SELECT * from menus where menu_domain_id = '".$domain_id."' ORDER BY menu_id ";

		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}

	public function get_domain_article_menus($domain_id){
		$output = array();
		$sql = "SELECT * from menus where menu_domain_id = '".$domain_id."' and menu_article = 1 ORDER BY menu_id ";

		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}
	
	public function get_menus_list(){
		$output = array();
		$sql = "SELECT domain_url, menu_name from menus join domains on menu_domain_id = domain_id ORDER BY domain_url, menu_name ";

		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}

}

?>
