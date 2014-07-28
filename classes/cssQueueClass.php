<?php 
/**	APPLICATION:	SX2/cssQueueClass.php
*	FILE:			cssQueueClass.php
*	DESCRIPTION:	generate factorial and save in table pending for working out properly css combination
*	CREATED:		21 Mar 2011 by Gordon Ye
*	UPDATED:	
*   NOTE:			status in pending table, default 0 represents untest, 1 represents tested, 2 represents approval
*/



class cssQueue extends  Model
{
	protected	$_domain;
	protected	$_session;
	protected	$_action;
	protected	$_layout_id;
	private 	$_db;
	private		$_cssStr;
	private 	$codes = array();
	private 	$pos = 0;
	private     $_factorial = array();
	
    public function __construct(db_class $db)
	{
		$this->_db = $db;
	}
	
	public function generateCombination($css_structure_id='', $type=''){		
		$this->createCombination($css_structure_id, $type);
		$count = count($this->_factorial);
		for($i = 0; $i < $count; $i++){
			$element = explode(',', $this->_factorial[$i]);
			/* check if a combination is existing */
			$sql = "SELECT count(*) FROM `css_pending` WHERE background='".$element[0]."' AND header='".$element[1]."' AND color='".$element[2]."' ";
			$duplicate = $this->_db->select_one($sql);
			if($duplicate)
				return false;
			/* save combination */	
			$sql = "INSERT INTO css_pending (background, header, color) VALUES ('".$element[0]."', '".$element[1]."', '".$element[2]."')";
			$this->_db->insert_sql($sql);
		}		
	}
		
	public function createCombination($css_structure_id='', $type='') {    
		if(!empty($css_structure_id)){
				if($type=='background'){
					$sql = "SELECT c.css_structure_id AS color, h.css_structure_id AS header, b.css_structure_id AS background
					FROM css_structure AS c
					JOIN css_structure AS b ON b.css_structure_type = 'background'
					AND b.css_structure_id =".$css_structure_id."
					JOIN css_structure AS h ON h.css_structure_type = 'header'
					AND h.css_structure_active =1
					WHERE c.css_structure_type = 'color'
					AND c.css_structure_active =1";	
				}
				else if($type=='header')
				{
					$sql = "SELECT c.css_structure_id AS color, h.css_structure_id AS header, b.css_structure_id AS background
					FROM css_structure AS c
					JOIN css_structure AS b ON b.css_structure_type = 'background'
					AND b.css_structure_active =1
					JOIN css_structure AS h ON h.css_structure_type = 'header'
					AND h.css_structure_id =".$css_structure_id."
					WHERE c.css_structure_type = 'color'
					AND c.css_structure_active =1";
				}
				else
				{
					$sql = "SELECT c.css_structure_id AS color, h.css_structure_id AS header, b.css_structure_id AS background
					FROM css_structure AS c
					JOIN css_structure AS b ON b.css_structure_type = 'background'
					AND b.css_structure_active =1
					JOIN css_structure AS h ON h.css_structure_type = 'header'
					AND h.css_structure_active =1
					WHERE c.css_structure_type = 'color'
					AND c.css_structure_id =".$css_structure_id;
				}
		}
		else
		{
			$sql = "SELECT c.css_structure_id AS color, h.css_structure_id AS header, b.css_structure_id AS background
			FROM css_structure AS c
			JOIN css_structure AS b ON b.css_structure_type = 'background'
			AND b.css_structure_active =1
			JOIN css_structure AS h ON h.css_structure_type = 'header'
			AND h.css_structure_active =1
			WHERE c.css_structure_type = 'color'
			AND c.css_structure_active =1";
		}
		
		$csscontent = $this->_db->select($sql);
		while($row = $this->_db->get_row($csscontent)){
			$this->_factorial[] = $row['background'].','.$row['header'].','.$row['color'];
		}
		//echo '-----sql ----'. $sql;
	}
		
	public function getCombinationAmount(){
		$sql = "SELECT count(*) FROM `css_pending` WHERE status=0";
		return $this->_db->select_one($sql);
	}
	
	public function getCssPartArray($part, $amount){
		$sql = "SELECT * FROM css WHERE css_part='".$part."' ORDER BY css_id ASC limit ".$amount;
		$csscontent = $this->_db->select($sql);
		while($row = $this->_db->get_row($csscontent)){
			$result[] = $row['css_id'];
		}
		return $result;
	}
	
	public function approveCombination($id, $theme='', $category='', $note=''){
		if(empty($theme))
			$theme = $id;
		$note = trim($note);
		$this->_db->update_sql("UPDATE css_pending SET status=2, theme_name='".mysql_real_escape_string($theme)."',category_id='".mysql_real_escape_string($category)."', note='".mysql_real_escape_string($note)."' WHERE id=".$id);	
	}
	
	public function getApproveThemes(){
		//$sql = "SELECT * FROM css_pending WHERE status=2 ORDER BY theme_name ASC ";
		$sql = "SELECT * FROM css_pending cs LEFT JOIN categorys ca ON (cs.category_id=ca.category_id) WHERE cs.status=2 ORDER BY cs.category_id ASC ";
		return $csscontent = $this->_db->select($sql);
	}
	
	public function getThemesArray($amount=0)
	{
		$limit = empty($amount)?'':" LIMIT $amount ";
		$sql = "SELECT * FROM css_pending WHERE status=2 ORDER BY id ASC $limit ";
		$themes = $this->_db->select($sql);
		while ($theme =  $this->_db->get_row($themes, 'MYSQL_ASSOC'))
		{
			$result[] = $theme['id'];
		}
		return $result;
	}
	
	public function getApproveThemesArray($amount=0)
	{
		$limit = empty($amount)?'':" LIMIT $amount ";
		$sql = "SELECT * FROM css_pending WHERE status=2 ORDER BY id ASC $limit ";
		$themes = $this->_db->select($sql);
		while ($theme =  $this->_db->get_row($themes, 'MYSQL_ASSOC'))
		{
			$result[] = $theme;
		}
		return $result;
	}
	
	public function getCategoryThemesArray($account_theme){
		$sql = "SELECT * FROM css_pending WHERE status=2 AND category_id=$account_theme ORDER BY id ASC ";
		$themes = $this->_db->select($sql);
		while ($theme =  $this->_db->get_row($themes, 'MYSQL_ASSOC')){
				$result[] = $theme['id'];
				$count++;
		}
		return $result;
	}
	
	public function themeSearch($seekstr){
		if(trim($seekstr)!=''){
			$sql = "SELECT * FROM css_pending WHERE theme_name = '".trim($seekstr)."'  AND status=2  ORDER BY theme_name ASC";
			$themes = $this->_db->select($sql);
			if($this->_db->row_count==0){
				$sql = "SELECT * FROM css_pending WHERE theme_name like '".trim($seekstr)."%' ";
				$themes = $this->_db->select($sql);
				if($this->_db->row_count==0){
					$sql = "SELECT * FROM css_pending WHERE theme_name like '%".trim($seekstr)."%' ";
					$themes = $this->_db->select($sql);
					if($this->_db->row_count==0){
						$sql = "SELECT * FROM css_pending WHERE theme_name like '%".trim($seekstr)."%' ";
						$themes = $this->_db->select($sql);
						if($this->_db->row_count==0){
							$parts = explode('_', trim($seekstr));						
							$sql = "SELECT * FROM css_pending WHERE background='".$parts[0]."' AND header='".$parts[1]."' AND color='".$parts[2]."' AND status=2";						
							$themes = $this->_db->select($sql);
						}
					}
				}		
			}
		}else{
			//$sql = "SELECT * FROM css_pending WHERE status=2  ORDER BY theme_name ASC";
			$sql = "SELECT * FROM css_pending cs LEFT JOIN categorys ca ON (cs.category_id=ca.category_id) WHERE cs.status=2 ORDER BY cs.category_id ASC ";
			$themes = $this->_db->select($sql);
		}
		
		while ($theme =  $this->_db->get_row($themes, 'MYSQL_ASSOC')){
				$result[] = $theme;
		}
		return $result;
	}
	
	public function getCategorys(){
		$sql = "SELECT * FROM categorys ORDER BY category_name ASC ";
		return $csscategory = $this->_db->select($sql);
	}
	
}

?>