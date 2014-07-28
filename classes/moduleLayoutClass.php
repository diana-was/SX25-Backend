<?php
/**
 * Layout class
 * Author: Archie Huang on 08/05/2009
 * 
 */

class ModuleLayout extends  Model
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
	
	public function check_modulelayout_id($layout_name) 
	{
		$layoutQ = "SELECT modulelayouts_id FROM modulelayouts WHERE modulelayout_name LIKE '".$layout_name."' LIMIT 1";
	
		$layout_id = $this->_db->select_one($layoutQ);
		return $layout_id;
	}
	
	public function get_modulelayout_info($layout_id) 
	{
		$pQuery = "SELECT * FROM modulelayouts WHERE modulelayout_id = '".$layout_id."' LIMIT 1";
		$pResults = $this->_db->select($pQuery);
		if($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			$array=array();
			$set = json_decode($pRow['modulelayout_settings']);
			foreach($set as $member=>$data)
			{
				$array[$member]=$data;
			}
			$pRow['modulelayout_settings'] = $array;
			return $pRow;
		}
		else
		{
			return false;
		}
	
	}
	
	public function save_modulelayout($array, $layout_id=0, &$error='')
	{
		// encode the data
		if (isset($array['modulelayout']))
			$array['modulelayout'] 		= $this->__encodeTags($array['modulelayout']);
		if (isset($array['modulelayout_js']))
			$array['modulelayout_js'] 	= $this->__encodeTags($array['modulelayout_js']);
		if (isset($array['modulelayout_css']))
			$array['modulelayout_css'] 	= $this->__encodeTags($array['modulelayout_css']);
		if (isset($array['modulelayout_settings']) && is_array($array['modulelayout_settings']))
			$array['modulelayout_settings'] = json_encode($array['modulelayout_settings']);

		// save
		if($layout_id == '0')
		{
			$array['modulelayout_default'] = $this->check_default($array['modulelayout_module_id'])?0:1;
			$layout_id = $this->_db->insert_array('modulelayouts', $array);
			$error = ($layout_id === FALSE)?'module layout could NOT be inserted':'';
		}
		else
		{
			if (isset($array['modulelayout_default']))
			{
				$array['modulelayout_default'] = isset($array['modulelayout_module_id'])?($this->check_default($array['modulelayout_module_id'],$layout_id)?0:1)
																						:($this->check_default_byID($layout_id)?0:1);
			}
			$count = $this->_db->update_array('modulelayouts', $array, "modulelayout_id='".$layout_id."'");
			$error = ($count === FALSE)?'module layout NOT data changed':'';
		}	
		return $layout_id;
	}

	public function get_modulelayouts() 
	{
		$returnArray = array();
		$pQuery = "SELECT modulelayout_id, modulelayout_name, modulelayout_module_id FROM modulelayouts ORDER BY modulelayout_name";
		$pResults = $this->_db->select($pQuery);
		while($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$returnArray[] = $pRow;
		}
		return $returnArray;
	}

	public function get_module_layouts($module_id) 
	{
		$returnArray = array();
		$pQuery = "SELECT modulelayout_id, modulelayout_name, modulelayout_default FROM modulelayouts WHERE modulelayout_module_id = '$module_id' ORDER BY modulelayout_name";
		$pResults = $this->_db->select($pQuery);
		while($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$returnArray[] = $pRow;
		}
		return $returnArray;
	}

	function check_default($module_id,$id=0) 
	{
		$defaultQ = "SELECT modulelayout_id FROM modulelayouts WHERE modulelayout_module_id = '".$module_id."' and modulelayout_default = 1 LIMIT 1 ";
		$count = $this->_db->select_one($defaultQ);
		$default = (($count == false) || ($count == $id))?false:true;
		return $default;
	}
	
	public function get_modulelayouts_toCheck() 
	{
		$returnArray = array();
		$pQuery = "SELECT module_id, module_name, modulelayout_id, modulelayout_default, modulelayout_settings FROM modulelayouts JOIN modules ON module_id = modulelayout_module_id WHERE module_settings LIKE '%\"db\"%' ORDER BY modulelayout_id";
		$pResults = $this->_db->select($pQuery);
		while($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$array=array();
			$set = json_decode($pRow['modulelayout_settings']);
			foreach($set as $member=>$data)
			{
				$array[$member]=$data;
			}
			unset($pRow['modulelayout_settings']);
			$pRow['perPage'] = isset($array['perPage'])?$array['perPage']:0;
			$returnArray[$pRow['modulelayout_id']] = $pRow;
		}
		return $returnArray;
	}

	function check_default_byID ($id)
	{
		$info = $this->get_modulelayout_info($id);
		return $this->check_default($info['modulelayout_module_id'],$id);
	}
	
	protected function __decodeTags($resultBase)
	{
		$resultBase = str_replace('&lt;', '<', $resultBase);
		$resultBase = str_replace('&gt;', '>', $resultBase);
		return $resultBase;
	}

	protected function __encodeTags($resultBase)
	{
		$resultBase = str_replace('<', '&lt;', $resultBase);
		$resultBase = str_replace('>', '&gt;', $resultBase);
		return $resultBase;
	}

}
?>
