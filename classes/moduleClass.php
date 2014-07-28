<?php
/**	APPLICATION:	SX25
*	FILE:			Module.php
*	DESCRIPTION:	display domain - Module base class
*	CREATED:		15 October 2010 by Diana De vargas
*	UPDATED:									
*/

class Module extends Model
{
	private 	$_db;
	private static $_Object; 
	
    /**
     * constructor : set up the variables
     *
     * @param dbobject $db db_class object

     * @return object
     */
	private function __construct(db_class $db)
	{
		$this->_db = $db;
		self::$_Object = $this;
		return self::$_Object;
	}

    /**
     * Get the module static object
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
     * Get Module Data from database
     *
     * @return array data 
     */
	public function check_module_id($module_name) 
	{
		$moduleQ = "SELECT module_id FROM modules WHERE module_name LIKE '".$module_name."' LIMIT 1";
	
		$module_id = $this->_db->select_one($moduleQ);
		return $module_id;
	}
	
	public function get_module_info($module_id) 
	{
		$pQuery = "SELECT * FROM modules WHERE module_id = '".$module_id."' LIMIT 1";
		$pResults = $this->_db->select($pQuery);
		if($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{ 
			$array=array();
			$set = json_decode($pRow['module_settings']);
			foreach($set as $member=>$data)
			{
				if (($member == 'sources') || ($member == 'perPage')) {
					$array[$member]=$data;
				}
			}
			$pRow['module_settings'] = $array;			
			return $pRow;
		}
		else
			return false;
	}
	
	public function save_module($array, $module_id=0)
	{
		if (isset($array['module_settings']) && is_array($array['module_settings']))
			$array['module_settings'] = json_encode($array['module_settings']);
		
		if($module_id == '0')
			$module_id = $this->_db->insert_array('modules', $array);
		else
			$this->_db->update_array('modules', $array, "module_id='".$module_id."'");
		return $module_id;
	}

	public function get_modules() 
	{
		$returnArray = array();
		$pQuery = "SELECT * FROM modules ORDER BY module_name";
		$pResults = $this->_db->select($pQuery);
		while($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$returnArray[] = $pRow;
		}
		return $returnArray;
	}
	
	public function get_modules_list()
	{
		$modules = $this->get_modules();
		foreach($modules as $k=>$v){
			$returnArray[$v['module_id']] = $v['module_name'];
		}
		return $returnArray;
	}

	public function get_module_toCheck() 
	{
		$pQuery = "SELECT module_id, module_name, module_settings FROM modules WHERE module_settings LIKE '%\"db\"%' ORDER BY module_id";
		$pResults = $this->_db->select($pQuery);
		while($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$array=array();
			$set = json_decode($pRow['module_settings']);
			foreach($set as $member=>$data)
			{
				if (is_object($data)) {
					$obj = array();
					foreach($data as $k=>$v)
						$obj[$k] = $v;
					$array[$member]=$obj;
				}
				$array[$member]=$data;
			}
			unset($pRow['modulelayout_settings']);
			$moduleTag 			= (isset($array['moduleTag']) && !empty($array['moduleTag']))?$array['moduleTag']:strtoupper($pRow['module_name']);
			$pRow['perPage'] 	= isset($array['perPage'])?$array['perPage']:0;
			$pRow['singleTags']	= isset($array['singleTags'])?$array['singleTags']:array();
			$pRow['idTags']		= isset($array['idTags'])?$array['idTags']:array();
			$returnArray[$moduleTag] = $pRow;
		}
		return $returnArray;
	}

	final public static function modulesFromTags($data,$search='_MODULE',$clean=array('{','_MODULE','}')){
		$tags = array();
		$modules = array();
		preg_match_all ('|({)[^}]+}|U', $data, $tags,  PREG_PATTERN_ORDER );
		if (is_array($tags) && isset($tags[0])) {
			foreach ($tags[0] as $tag) {
				if (stripos($tag, $search) !== false)	{
					$modules[] = str_replace($clean, '', $tag);
				}	
			}
		}
		return $modules;
	}

	final public static function modulesFromTagsIndividual($data,$tagsList){
			$tags = array();
			$modules = array();
			preg_match_all ('|({)[^}]+}|U', $data, $tags,  PREG_PATTERN_ORDER );
			if (is_array($tags) && isset($tags[0])) {
				foreach($tagsList as $m => $search) {
					foreach ($tags[0] as $tag) {
						foreach($search as $k=>$s){
							if (strpos($tag, strtoupper($s)) !== false)	{
								$req = str_replace(strtoupper($s),'',$tag);
								$req = str_replace(array('M_','_','{','}'),'',$req);
								$req = empty($req)?1:(int) $req;
								$modules[$m] = (!isset($modules[$m]) || $modules[$m] < $req)?$req:$modules[$m];
							}
						}
					}
				}
			}
			return $modules;
	}
}