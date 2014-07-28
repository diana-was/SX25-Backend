<?php
/**
 * Goal class
 * Author: Diana DeVargas 26/05/2012
 * 
 */

class Goal extends  Model
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
	
	public static function getInstance(db_class $db)
	{
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
	}
	
	public function check_goal_set($keyword,$amount=1)
	{		
		if ( $goal = $this->_db->select_one("SELECT count(*) FROM goals WHERE LOWER(goal_keyword) like '".$keyword."' "))
			return ($goal >= $amount)?$goal:false;
		else
			return false;
	}

	public function get_goal_info($goal_id) 
	{
		$pQuery = "SELECT *, (select count(*) from comments where comments.comment_table = 'goals' and comments.comment_table_id = ".$goal_id.") as num_comments FROM goals WHERE goal_id = '".$goal_id."' LIMIT 1";
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

	function get_library_goal($fromRecord,$recordPerPage,$sortyQuery) 
	{
		$output = array();
		$goalQuery = "SELECT *, (select count(*) from comments where comments.comment_table = 'goals' and comments.comment_table_id = goals.goal_id) as num_comments FROM goals $sortyQuery LIMIT $fromRecord,$recordPerPage";
		$pResults = $this->_db->select($goalQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		
		return $output;
	}

	function get_goal($keyword, $limit=1, $startnum=0,$sortyQuery='') 
	{
		$goal = array();
		$sortyQuery = empty($sortyQuery)?'ORDER BY goal_id':$sortyQuery;
	    $limit = (empty($limit) || !is_numeric($limit))?1:$limit;
		
		$goalQuery = "SELECT *, (select count(*) from comments where comments.comment_table = 'goals' and comments.comment_table_id = goals.goal_id) as num_comments FROM goals WHERE goal_keyword like '$keyword' $sortyQuery LIMIT $startnum,$limit ";
		$pResults = $this->_db->select($goalQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$goal[] = $pRow;		
			}
		}
	
		return $goal;
	}
	
	public function count_total_goal($keyword='') 
	{
		$query = empty($keyword)?'':" WHERE goal_keyword like '$keyword' ";
		$goalQuery = "SELECT count(*) FROM goals $query ";
		$count = $this->_db->select_one($goalQuery);
		return $count;
	}

	function count_keywords_goal() 
	{
		$directory = array();
		
		$directoryQuery = "SELECT goal_keyword as keyword, count(*) as `Num Goals`  FROM goals GROUP BY goal_keyword ORDER BY goal_keyword";
		$pResults = $this->_db->select($directoryQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$directory[] = $pRow;		
			}
		}
	
		return $directory;
	}
	
	public function del_goal($id)
	{	
		$dQuery = "DELETE FROM goals WHERE goal_id = '".$id."'";
		if($this->_db->delete($dQuery))
			return true;
		else
			return false;
	}
	
	function check_goal($keyword,$subject,$visitor,$startdate) 
	{
		if ( $article_id = $this->_db->select_one("SELECT goal_id FROM goals WHERE goal_keyword = '".$keyword."' and goal_subject = '".$subject."' and goal_visitor =  '".$visitor."' and goal_start_date = '$startdate'LIMIT 1"))
			return true;
		else
			return false;
	}
	
	public function save_goal($array, $id=0)
	{	 
		$minDate = 1325404800; // 2012-01-01
		// Check data
		if (isset($array['goal_start_date']))
			$array['goal_start_date'] 	= (empty($array['goal_start_date']) || strtotime($array['goal_start_date']) < $minDate)?null:date('Y-m-d',strtotime($array['goal_start_date']));
		if (isset($array['goal_target_date']))
			$array['goal_target_date'] 	= (empty($array['goal_target_date']) || strtotime($array['goal_target_date']) < $minDate)?null:date('Y-m-d',strtotime($array['goal_target_date']));
		if (isset($array['goal_completion_date']))
			$array['goal_completion_date'] 	= (empty($array['goal_completion_date']) || strtotime($array['goal_completion_date']) < $minDate)?'':date('Y-m-d',strtotime($array['goal_completion_date']));
		if (isset($array['goal_completion']))
		{
			$array['goal_completion'] 	= is_numeric($array['goal_completion'])?(($array['goal_completion'] > 100)?100:$array['goal_completion']):0;
			$completion = $array['goal_completion'] * 1;
			if (isset($array['goal_completion_date']) && empty($array['goal_completion_date']) && $completion == 100)
				$array['goal_completion_date'] = date('Y-m-d');
			elseif (!empty($array['goal_completion_date']) && $completion < 100)
				$array['goal_completion_date'] = null;
		}
		if (!empty($array['goal_completion_date']))
			$array['goal_completion'] = 100;
		elseif(isset($array['goal_completion_date']) && $array['goal_completion_date'] == '')
			$array['goal_completion_date'] = null;
		if (isset($array['goal_status']))
			$array['goal_status'] 	= (empty($array['goal_status']) || $array['goal_status'] != 1)?0:1;
		if($id == 0)
			$id = $this->_db->insert_array('goals', $array);
		else
			$this->_db->update_array('goals', $array, "goal_id='".$id."'");
			
		return $id;
	}

	/*
	 *  this fuction is a copy of the one in sx25standard GoalModule_Class
	 *   
	 */	
	public function getGoalsByKeyword($keyword, $start='', $numGoals=5, $alterkw='',$avatar='')
	{
		$keyword = strtolower(trim($keyword));
		$alterkw = strtolower(trim($alterkw));
		$limit = ($numGoals > 0)?" LIMIT $numGoals ":'';
		$offset = (!empty($start))?" OFFSET $start ":'';
		$order  = ' goal_created_date DESC ';
		$aQuery = "SELECT goals.* FROM goals WHERE goal_keyword = '$keyword' and goal_approved = 1 order by $order $limit $offset ";
		$aResults = $this->_db->select($aQuery);
		$result = array();

		if($this->_db->row_count == 0) 
		{
			/* serach for the keyword in the content */
			$aQuery = "SELECT goals.*, (MATCH(goal_keyword,goal_subject,goal_content) AGAINST ('\"$keyword\"'  IN BOOLEAN MODE)) AS score FROM goals WHERE goal_approved = 1 HAVING score >= 1 order by $order $limit $offset ";
			$aResults = $this->_db->select($aQuery);
	
			if($this->_db->row_count == 0) 
			{
				// Search for Q&A by related keyword in related_keyword_qa table or with the $alterkw
				$order  = ' goal_created_date DESC ';
				$aQuery = "SELECT goals.* FROM goals WHERE goal_keyword = '$alterkw' and goal_approved = 1 order by $order $limit $offset ";
				$aResults = $this->_db->select($aQuery);

				if($this->_db->row_count == 0) 
				{
					// Search any Q&A
					$order  = ' goal_created_date DESC ';
					$aQuery = "SELECT goals.* FROM goals WHERE goal_approved = 1 order by $order $limit $offset ";
					$aResults = $this->_db->select($aQuery);
				}
			}
		}
		
		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$aRow['content_source'] = 'db';
			$aRow['goal_user_photo'] = empty($aRow['goal_user_photo'])?$avatar:$aRow['goal_user_photo'];
			$result[] = $aRow;
		}
		
		return $result;
	}
	
	public function getGoalById($goal_id,$avatar='')
	{
		$aQuery = "SELECT * FROM goals WHERE goal_id='".$goal_id."' ";
		$aResults = $this->_db->select($aQuery);
		$pRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
		// check the default for the photo
		$pRow['goal_user_photo'] = empty($pRow['goal_user_photo'])?$avatar:$pRow['goal_user_photo'];
		
		return $pRow;
	}
	
	public function getGoalComments($domain_id,$goal_id,$maxComments=10)
	{
		$comment = Comment::getInstance($this->_db);
		return $comment->get_comments('goals',$goal_id,$domain_id,$maxComments);
	}
		
}
?>
