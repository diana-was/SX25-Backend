<?php
/**
 * Directory class
 * Author: Gordon Ye 16/05/2011
 * 
 */

class cheapAds extends Model {
	private $_curlObj;
	private $_db;
	private static $_Object;
	
	private function __construct(db_class $db) {
		$this->_curlObj = new SingleCurl ();
		$this->_db = $db;
		self::$_Object = $this;
		return self::$_Object;
	}
	
	public static function getInstance(db_class $db) {
		$class = __CLASS__;
		if (! isset ( self::$_Object )) {
			return new $class ( $db );
		}
		return self::$_Object;
	}
	
	public function get_cheapad_info($cheapad_id) {
		
		$pQuery = "SELECT * FROM cheapads WHERE cheapad_id = '" . $cheapad_id . "' LIMIT 1";
		$pResults = $this->_db->select ( $pQuery );
		if ($pRow = $this->_db->get_row ( $pResults, 'MYSQL_ASSOC' )) {
			return $pRow;
		} else {
			return false;
		}
	
	}
	
	function get_library_cheapads($fromRecord, $recordPerPage, $sortyQuery) {
		
		$output = array ();
		$cheapadQuery = "SELECT * FROM cheapads " . $sortyQuery . " LIMIT " . $fromRecord . "," . $recordPerPage;
		$pResults = $this->_db->select ( $cheapadQuery );
		while ( $row = $this->_db->get_row ( $pResults, 'MYSQL_ASSOC' ) ) {
			$output [] = $row;
		}
		
		return $output;
	}
	
	function get_cheapads($title='', $limit = 0, $startnum = 0) 
	{
		$cheapad = array ();
		$limit = (empty ( $limit ) || ! is_numeric ( $limit )) ? '' : " LIMIT $limit ";
		$offset = (empty ( $startnum ) || ! is_numeric ( $startnum ) || empty ( $limit )) ? '' : " OFFSET $startnum ";
		$where 	= empty($title)?'':"WHERE cheapad_sitehost like '$title'";
		
		$cheapadQuery = "SELECT * FROM cheapads $where ORDER BY cheapad_id  $limit $offset";
		$pResults = $this->_db->select ( $cheapadQuery );
		if ($pResults) {
			while ( $pRow = $this->_db->get_row ( $pResults, 'MYSQL_ASSOC' ) ) {
				$cheapad [] = $pRow;
			}
		}
		
		return $cheapad;
	}

	function get_cheapads_list() 
	{
		$cheapad= $this->get_cheapads();
		$list	= array();
		
		foreach($cheapad as $key => $row)
		{
			$list[] = $row['cheapad_sitehost'];
		} 
		
		return $list;
	}
	
	function count_total_cheapads($title = '') {
		$query = empty ( $title ) ? '' : " WHERE cheapad_sitehost like '$title' ";
		$cheapadQuery = "SELECT count(*) FROM cheapads $query ";
		$count = $this->_db->select_one ( $cheapadQuery );
		return $count;
	}
	
	public function del_cheapad($id) {
		$dQuery = "DELETE FROM cheapads WHERE cheapad_id = '" . $id . "'";
		if ($this->_db->delete ( $dQuery ))
			return true;
		else
			return false;
	}
	
	public function save_cheapad($array, $id = 0) {
		global $user;
		if (!isset($user)) return false;  // don't save if no user is logged in
		
		$array ['cheapad_createdby_user'] = $user->userID;		
		
		if ($id == 0)
			$id = $this->_db->insert_array ( 'cheapads', $array );
		else
			$this->_db->update_array ( 'cheapads', $array, "cheapad_id='" . $id . "'" );
		
		return $id;
	}
	
}
?>
