<?php
/**
 * Directory class
 * Author: Gordon Ye 16/05/2011
 * 
 */
 
class Dty extends  Model
{
	private $_curlObj;
	private $_db; 
	private static $_Object;
	
	private function __construct(db_class $db)
	{
		$this->_curlObj = new SingleCurl();
		$this->_db = $db;
		self::$_Object = $this;
		return self::$_Object;
	}
	
	public static function getInstance(db_class $db){
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
	}
	
	public function check_directory_set($keyword,$amount=1){		
		if ( $directories = $this->_db->select_one("SELECT count(*) FROM directories WHERE LOWER(directory_keyword) like LOWER('".$keyword."') "))
			return ($directories >= $amount)?$directories:false;
		else
			return false;
	}

	public function get_directory_info($directory_id) {
		
		$pQuery = "SELECT * FROM directories WHERE directory_id = '".$directory_id."' LIMIT 1";
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

	function get_library_directories($fromRecord,$recordPerPage,$sortyQuery) {
		
		$output = array();
		$directoryQuery = "SELECT * FROM directories ".$sortyQuery." LIMIT ".$fromRecord.",".$recordPerPage;
		$pResults = $this->_db->select($directoryQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		
		return $output;
	}

	function get_directories($keyword, $limit=1, $startnum=0) {
	
		$directory = array();
		$limit = (empty($limit) || !is_numeric($limit))?'':" LIMIT $limit ";
		$offset = (empty($startnum) || !is_numeric($startnum)  || empty($limit))?'':" OFFSET $startnum ";
		
		
		$directoryQuery = "SELECT * FROM directories WHERE directory_keyword like '$keyword' ORDER BY directory_id  $limit $offset";
		$pResults = $this->_db->select($directoryQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$directory[] = $pRow;		
			}
		}
	
		return $directory;
	}
	
	function count_total_directories($keyword='') {		
		$query = empty($keyword)?'':" WHERE directory_keyword like '$keyword' ";
		$directoryQuery = "SELECT count(*) FROM directories $query ";
		$count = $this->_db->select_one($directoryQuery);
		return $count;
	}

	function count_keywords_directories() {
	
		$directory = array();
		
		$directoryQuery = "SELECT directory_keyword as keyword, count(*) as listings  FROM directories GROUP BY directory_keyword ORDER BY directory_keyword";
		$pResults = $this->_db->select($directoryQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$directory[] = $pRow;		
			}
		}
	
		return $directory;
	}
	
	public function del_directory($id)
	{		
		$dQuery = "DELETE FROM directories WHERE directory_id = '".$id."'";
		if($this->_db->delete($dQuery))
			return true;
		else
			return false;
	}
	
	public function scrape_directory($keyword, $limitnum=0, $this_id=0, &$usethisdirectory='', $search='') 
	{		
		$search = empty($search)?$keyword:$search;
		$this->_curlObj->createCurl('get','http://www.bing.com/search',array("q" => $search." site:dmoz.org", 
																			 "go" => "",
																			 "form" => "QBRE",
																			 "filt" => "all"
																		));
		$resultpage = $this->_curlObj->__toString();
			
		$resultpage = explode('<li class="sa_wr"><div class="sa_cc"', $resultpage);
		$resultlist = isset($resultpage[1])?$resultpage[1]:'';
		
		$linkString = explode('href="', $resultlist);
		$links = isset($linkString[1])?explode('"', $linkString[1]):array();
		$link = isset($links[0])?$links[0]:'';
		
		$this->_curlObj->createCurl('get',$link);
		$resultpage = $this->_curlObj->__toString();
		$resultlist = explode('<ul class="directory-url" style="margin-left:0;">',$resultpage);
		$eachresult = isset($resultlist[1])?explode('</li>', $resultlist[1]):array();
	
		$usethisdirectory = '';
		$artnum = 0;
		foreach($eachresult as $key => $val)
		{		
			if (($limitnum > 0) && ($artnum >= $limitnum))
				break;
				
			$urlrest = explode('<a href="', $val);
			
			$urlstr = explode('">', $urlrest[1]);			
			$url = $urlstr[0];
			
			$titlestr = explode('</a>', $urlstr[1]);
			$title = $titlestr[0];
			
			$des = $titlestr[1];
			
			if ($this->check_directory($keyword,$url))
				continue;
				
			$artarray = array('directory_title' => $title, 'directory_description' => $des, 'directory_keyword' => $keyword, 'directory_url' => $url, 'directory_update_date' => date('Y-m-d H:i:s')); 
							
			if($directory_id = $this->save_directory($artarray, $this_id))
			{
				$artnum++;
				if(empty($usethisdirectory))
					$usethisdirectory = $directory_id;
			}																
		}

		return $artnum;
	}
	
	function check_directory($keyword,$url) 
	{
		$url = $this->clean_url ($url);
		if ( $article_id = $this->_db->select_one("SELECT directory_id FROM directories WHERE LOWER(directory_keyword) = LOWER('$keyword') and LOWER(directory_url) = LOWER('$url') LIMIT 1"))
			return $article_id;
		else
			return false;
	}
	
	
	public function save_directory($array, $id=0)
	{	
		// url cleanup
		if (!empty($array['directory_url']))
		{
			$array['directory_url'] = $this->clean_url ($array['directory_url']);
		}
		
		if($id == 0)	
			$id = $this->_db->insert_array('directories', $array);
		else
			$this->_db->update_array('directories', $array, "directory_id='".$id."'");
			
		return $id;
	}

	public function clean_url ($data)
	{
		$url = '';
		if (!empty($data))
		{
			$urlParts = parse_url($data);
			$url 	= empty($urlParts['scheme'])?'http://':$urlParts['scheme'].'://';
			$url   .= empty($urlParts['host'])?'':$urlParts['host'];
			$url   .= empty($urlParts['path'])?'':$urlParts['path'];
			$url   .= empty($urlParts['query'])?'':'?'.$urlParts['query'];
			$url   .= empty($urlParts['fragment'])?'':'#'.$urlParts['fragment'];
			$array['directory_url'] = $url;
		}
		return $url;
	}

	function remove_directory($url, $keyword='', $delete = true)
	{
		$directory_url = $this->clean_url ($url);
		$keyword = trim($keyword);
		$where = empty($keyword)?'':" directory_keyword = '$keyword' and ";
		
		if ($delete)
		{ 
			$dQuery = "DELETE FROM directories WHERE $where directory_url = '$directory_url' ";
			if($this->_db->delete($dQuery))
				return true;
			else
				return false;
		}
		else 
		{
			$dQuery = "UPDATE directories set directory_flag = 0 WHERE $where directory_url = '$directory_url' ";
			if($this->_db->update_sql($dQuery))
				return true;
			else
				return false;
		}
	}
	
	
	
	
	/*
	 *  this fuction is a copy of the one in sx25standard DirectoryModule_Class
	 *   
	 */
	public function getDirectoriesByKeyword($keyword, $numDirectories=0, $start=0, $alterkw='')
	{
		$keyword = strtolower(trim($keyword));
		$alterkw = strtolower(trim($alterkw));
		$limit = ($numDirectories > 0)?" LIMIT $numDirectories ":'';
		$offset = (!empty($start) && !empty($limit))?" OFFSET $start ":'';
		$aQuery = "SELECT * FROM directories WHERE directory_keyword like '$keyword' ORDER BY directory_flag DESC, directory_title ASC $limit $offset";
		$aResults = $this->_db->select($aQuery);
		$result = array();

		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$aRow['content_source'] = 'db';
			$result[] = $aRow;
		}
		// try to getdirectories with alternative keyword 
		if(sizeof($result)==0) 
		{
		    $where = (empty($alterkw) || ($keyword == $alterkw))?' ORDER BY directory_flag DESC, RAND() ':" WHERE directory_keyword like '$alterkw' ORDER BY directory_flag DESC, directory_title ASC ";
			$aQuery = "SELECT * FROM directories $where $limit $offset";
			$aResults = $this->_db->select($aQuery);
	
			while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
				$aRow['content_source'] = 'db';
				$result[] = $aRow;
			}
			// try yo get random directories if not found for alternative keyword 
			if(sizeof($result)==0 && !empty($alterkw) && ($keyword != $alterkw)) 
			{
				$aQuery = "SELECT * FROM directories ORDER BY directory_flag DESC, RAND() $limit $offset";
				$aResults = $this->_db->select($aQuery);
		
				while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
					$aRow['content_source'] = 'db';
					$result[] = $aRow;
				}
			}
		}
		return $result;
	}
	
}
?>
