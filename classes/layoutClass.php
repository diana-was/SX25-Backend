<?php
/**
 * Layout class
 * Author: Archie Huang on 08/05/2009
 * 
 */

class Layout extends Model
{
	private 	$_db;
	protected	$sx25LayoutFolder;
	protected	$cssFolder;
	protected	$cssImageFolder;
	
	public function __construct() {
		global $db;
		
		$this->_db = $db;
		$config = Config::getInstance();
		$this->sx25LayoutFolder = $config->sx25LayoutFolder;
		$this->cssFolder		= $config->sx25cssFolder;
		$this->cssImageFolder	= $config->sx25cssImageFolder;
	} 
	
	public function check_layout_id($layout_name) {
		$layout_id = false;
		$layoutQ = "SELECT layout_id FROM layouts WHERE layout_name LIKE '".$layout_name."' LIMIT 1";
	
		$layout_id = $this->_db->select_one($layoutQ);
		return $layout_id;
	}
	
	public function check_layout_folder($layout_folder) {
		$layout_id = false;
		$layoutQ = "SELECT layout_id FROM layouts WHERE layout_folder LIKE '".$layout_folder."' LIMIT 1";
	
		$layout_id = $this->_db->select_one($layoutQ);
		return $layout_id;
	}
	
	public function get_layout_info($layout_id) {
		global $db;
		
		$pQuery = "SELECT * FROM layouts WHERE layout_id = '".$layout_id."' LIMIT 1";
		$pResults = $db->select($pQuery);
		if($pRow=$db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			$array=array();
			if (!empty($pRow['layout_modules']))
			{
				$set = json_decode($pRow['layout_modules']);
				foreach($set as $member=>$data)
				{
					$array[$member]=$data;
				}
			}
			$pRow['layout_modules'] = $array;
			return $pRow;
		}
		else
		{
			return false;
		}
	
	}
	
	public function get_base_folders($layout_id) {
		$layout_folder = array();
	
		$layoutInfo = $this->get_layout_info($layout_id);
		if ($layoutInfo)
		{
			$layout_folder['base'] = $this->sx25LayoutFolder.$layoutInfo['layout_folder'].'/'.$layoutInfo['layout_base'];
			$layout_folder['folderPath'] = $this->sx25LayoutFolder.$layoutInfo['layout_folder'];
			$layout_folder['folder'] = $layoutInfo['layout_folder'];
		}	
		return $layout_folder;
	}
	
	public function save_layout($array, $id=0, &$err='')
	{
		if (isset($array['layout_modules']) && is_array($array['layout_modules']))
			$array['layout_modules'] = json_encode($array['layout_modules']);
		
		if($id == '0')
		{
			$oldFolderName = $array['layout_folder'];
			$id = $this->_db->insert_array('layouts', $array);
		}
		else
		{
			$layoutQ = "SELECT layout_folder FROM layouts WHERE layout_id = '".$id."' LIMIT 1";
			$oldFolderName = $this->_db->select_one($layoutQ);
			$this->_db->update_array('layouts', $array, "layout_id='".$id."'");
		}
		$err = $this->save_layout_files($id,$oldFolderName);
			
		return $id;
	}


	public function save_layout_files($id,$oldFolderName='')
	{
		$config = Config::getInstance();	
		$pf = new Profile();			
		$results = '';
		
		$data = $this->get_layout_info($id);
		
		if ($data === false){
			$results = 'Layout not found';
			break;
		}

		// create 'csslibrary' folder if doesn't exist
		if (!is_dir($this->cssFolder)) {
			mkdir($this->cssFolder,0777);
		}
		
		// create 'csslibrary/layout_image' folder if doesn't exist
		if (!is_dir($this->cssImageFolder)) {
			mkdir($this->cssImageFolder,0777);
		}
		
		if (!empty($oldFolderName) && is_dir($this->sx25LayoutFolder.$oldFolderName) && ($oldFolderName != $data['layout_folder']))
		{
			rename ( $this->sx25LayoutFolder.$oldFolderName, $this->sx25LayoutFolder.$data['layout_folder'] );
		}
		
		// create layout folder if doesn't exist
		if (!is_dir($this->sx25LayoutFolder.$data['layout_folder'])) {
			mkdir($this->sx25LayoutFolder.$data['layout_folder'],0777);
		}

		// create image folder if doesn't exist
		if (!is_dir($this->sx25LayoutFolder.$data['layout_folder'].'/images')) {
			mkdir($this->sx25LayoutFolder.$data['layout_folder'].'/images',0777);
		}
		
		return $results;
	}

	public function get_layouts() {
		$returnArray = array();
		
		$pQuery = "SELECT layout_id, layout_name FROM layouts ORDER BY layout_name";
		$pResults = $this->_db->select($pQuery);
		while($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$returnArray[] = $pRow;
		}
		return $returnArray;
	}
	
	public static function get_image_required($layout_id){
		$images = array();
		$layout_info = self::get_layout_info($layout_id);

		$layout = $layout_info['layout_landing'];
		$images = self::imagesFromTags($layout,array('LANDING_IMG','RESULTS_IMG','M_IMG_','IMG_'));
		
		$layout = $layout_info['layout_result'];
		$images = array_merge($images,self::imagesFromTags($layout,array('LANDING_IMG','RESULTS_IMG','M_IMG_','IMG_')));

		$layout = $layout_info['layout_comment'];
		$images = array_merge($images,self::imagesFromTags($layout,array('LANDING_IMG','RESULTS_IMG','M_IMG_','IMG_')));
		
		$layout = $layout_info['layout_sponsored'];
		$images = array_merge($images,self::imagesFromTags($layout,array('LANDING_IMG','RESULTS_IMG','M_IMG_','IMG_')));

		$images = array_unique($images);
		
		return $images;
	}
	
	public static function get_image_amount($layout_id){
		$images = self::get_image_required($layout_id);
		return sizeof($images);
	}
	
	public static function imagesFromTags($data,array $search,$clean=array('{','}'))
	{
		$tags = array();
		$images = array();
		preg_match_all ('/({)[^({|})]+}/U', $data, $tags,  PREG_PATTERN_ORDER );
		if (is_array($tags) && isset($tags[0])) 
		{
			foreach ($tags[0] as $tag) 
			{
				foreach ($search as $s)
				{
					$tag = strtoupper($tag);
					if (stripos($tag, $s) !== false)	
					{
						$images[] = str_replace($clean, '', $tag);
						break;
					}
				}	
			}
		}
		return array_unique($images);
	}

	public function getLayoutModules ($layout_id)
	{
		$modules = array();
		
		$layoutRow = $this->get_layout_info($layout_id);
		$moduleObj 			= Module::getInstance($this->_db);
		$moduleList 		= $moduleObj->get_module_toCheck();
		$moduleLayoutObj 	= ModuleLayout::getInstance($this->_db);
		$moduleLayoutList	= $moduleLayoutObj->get_modulelayouts_toCheck();

		// serach modules tags in the layouts
		$modules1= Module::modulesFromTags($layoutRow['layout_landing']);
		$modules2= Module::modulesFromTags($layoutRow['layout_result']);
		$required1 = $this->get_required_content($modules1,$layoutRow['layout_modules']['landing'],$moduleList,$moduleLayoutList);
		$required2 = $this->get_required_content($modules2,$layoutRow['layout_modules']['result'],$moduleList,$moduleLayoutList);
		$M1 = array_merge($modules1, $modules2);						
		$M1 = array_unique($M1);
		foreach($M1 as $m)
		{
			if (isset($required1[$m]) && isset($required2[$m]))
				$req	= ($required1[$m] > $required2[$m])?$required1[$m]:$required2[$m];
			elseif (isset($required1[$m]))
				$req	= $required1[$m];
			else
				$req	= $required2[$m];
			$modules[$m]= $req;
		}
		
		// serach modules single tags in the layouts
		$moduleNames = array();
		foreach($moduleList as $m => $obj){
			$moduleNames[$m] = $obj['singleTags'];
			if ($m == 'QUESTION')
				$moduleNames['QUESTION_BYID'] = $obj['idTags'];
		}
		$required3 = Module::modulesFromTagsIndividual($layoutRow['layout_landing'],$moduleNames);
		$required4 = Module::modulesFromTagsIndividual($layoutRow['layout_result'],$moduleNames);

		$M2 = array_merge(array_keys($required3), array_keys($required4));
		$M2 = array_unique($M2);
		foreach($M2 as $m)
		{
			if (isset($required3[$m]) && isset($required4[$m]))
				$req	= ($required3[$m] > $required4[$m])?$required3[$m]:$required4[$m];
			elseif (isset($required3[$m]))
				$req	= $required3[$m];
			else
				$req	= $required4[$m];
			if (!isset($modules[$m]))
				$modules[$m]= $req;
			else
				$modules[$m]= ($modules[$m] > $req)?$modules[$m]:$req;
		}
		$M3 = array_merge($M1, $M2);						
		$M3 = array_unique($M3);
		
		// Be sure all modules are in
		foreach($M3 as $m)
		{
			if (!isset($modules[$m]))
				$modules[$m]= 0;
		}
		$requiredImages = self::get_image_amount($layout_id);
		if ($requiredImages>0) {
			$modules['MENU_IMAGE'] = $requiredImages;
		}
		return $modules;
	}
	
	public function getLayoutDefaultModule($domain) {
		$pQuery = 	"SELECT layouts.layout_default_module FROM layouts
					INNER JOIN domains ON domains.domain_layout_id = layouts.layout_id
					WHERE LOWER(domains.domain_url) = LOWER('" . $domain . "') LIMIT 1";
	
		return $this->_db->select_one($pQuery);
	}
	
	private function get_required_content($modules,$layoutModules,$moduleList,$moduleLayoutList)
	{
		$content = array();
		foreach($modules as $m)
		{
			$req	= 0;
			$id 	= isset($moduleList[$m])?$moduleList[$m]['module_id']:0; 	// module id
			// Get the modulelayout_id
			if (!empty($id))
			{
				$layoutID  = $layoutModules->$id;
				if ($layoutID == 0) { // find the default if exist
					foreach ($moduleLayoutList as $k => $v){
						if (($v['module_id'] == $id) && ($v['modulelayout_default'] == 1)){
							$layoutID = $k;
						}
					} 
				}
				$req		= isset($moduleLayoutList[$layoutID])?$moduleLayoutList[$layoutID]['perPage']:$moduleList[$m]['perPage'];
			}
			$content[$m]= $req;
		}
		return $content;
	}

	private function replace_tag($string)
	{
		$string = str_replace('&lt;', '<', $string);
		$string = str_replace('&gt;', '>', $string);
		return $string;
	}
	
}
?>
