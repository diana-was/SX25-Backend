<?php
/**
 * Directory class
 * Author: Gordon Ye 16/05/2011
 * 
 */

class QuestionAnswer extends  Model
{
	private $_curlObj;
	private $_db;
	private $_aid = array('kdzL7q_V34H6yHJL_EoJkgpgnBuQr4mSfnzHCYVKPQBRZiho0H20JBTJgWEEJRKBMGPZpw--','TtRrWCbV34FtJ.RuiRhx9s.tvUI6jfCcIMBS9v1nQHt2RWXW3gfKRnAU54a_rMiI');
	private $_yahooimg = array('http://l.yimg.com/a/i/identity/nopic_48.gif','http://l.yimg.com/a/i/identity2/profile_48a.png','http://l.yimg.com/a/i/identity2/profile_48b.png','http://l.yimg.com/a/i/identity2/profile_48c.png','http://l.yimg.com/a/i/identity2/profile_48d.png','http://l.yimg.com/a/i/identity2/profile_48e.png');
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
	
	public function check_qa_set($keyword,$amount=1){		
		if ( $qa = $this->_db->select_one("SELECT count(*) FROM question_answer WHERE LOWER(question_keyword) like LOWER('".$keyword."') "))
			return ($qa >= $amount)?$qa:false;
		else
			return false;
	}

	public function get_qa_info($qa_id) 
	{
		$pQuery = "SELECT * , (select count(*) from answers where answers.question_id = ".$qa_id.") as num_answers FROM question_answer WHERE question_id = '".$qa_id."' LIMIT 1";
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

	function get_library_qa($fromRecord,$recordPerPage,$sortyQuery,$appFlag=null) 
	{
		$output = array();
		$approved = is_null($appFlag)?'':" where question_approved = '$appFlag' ";
		$qaQuery = "SELECT *, (select count(*) from answers where answers.question_id = question_answer.question_id) as num_answers FROM question_answer $approved $sortyQuery LIMIT ".$fromRecord.",".$recordPerPage;
		$pResults = $this->_db->select($qaQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		
		return $output;
	}

	function get_qa($keyword, $limit=1, $startnum=0,$sortyQuery='',$appFlag=null) 
	{
		$qa = array();
		$approved = is_null($appFlag)?'':" and question_approved = '$appFlag' ";
		$sortyQuery = empty($sortyQuery)?'ORDER BY question_id':$sortyQuery;
	    $limit = (empty($limit) || !is_numeric($limit))?1:$limit;
		$keywords = (strpos($keyword, '%') === FALSE)?" = '$keyword' ":" like '$keyword%' ";
		$qaQuery = "SELECT *, (select count(*) from answers where answers.question_id = question_answer.question_id) as num_answers FROM question_answer WHERE question_keyword $keywords $approved $sortyQuery LIMIT $startnum,$limit ";
		$pResults = $this->_db->select($qaQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$qa[] = $pRow;		
			}
		}
	
		return $qa;
	}
	
	public function count_total_qa($keyword='') 
	{
		$query = empty($keyword)?'':" WHERE question_keyword like '$keyword' ";
		$qaQuery = "SELECT count(*) FROM question_answer $query ";
		$count = $this->_db->select_one($qaQuery);
		return $count;
	}

	function count_keywords_qa() {
	
		$directory = array();
		
		$directoryQuery = "SELECT question_keyword as keyword, count(*) as `Q&A`  FROM question_answer GROUP BY question_keyword ORDER BY question_keyword";
		$pResults = $this->_db->select($directoryQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$directory[] = $pRow;		
			}
		}
	
		return $directory;
	}
	
	public function del_qa($id)
	{	
		$dQuery = "DELETE FROM question_answer WHERE question_id = '".$id."'";
		if($this->_db->delete($dQuery))
			return true;
		else
			return false;
	}
	
	public function scrape_qa($keyword, $limitnum=0, $this_id=0, $save_keyword=null) 
	{
		$itg = $this->_aid[rand()%2];
		$this->_curlObj->createCurl('get','http://answers.yahooapis.com/AnswersService/V1/questionSearch',array("query" => $keyword, 
																			 "appid" => $itg,
																			 "region" => "us"
																		));
		$resultpage = $this->_curlObj->__toString();
							
		$rss = new xml2Array();	
		$results = $rss -> parse($resultpage);
		
		$keyword = empty($save_keyword)?$keyword:$save_keyword;
		$qanum = 0;
		$_qa = @$results[0]['children'];
		if($_qa):
	
			foreach($_qa as $row)
			{
				if (($limitnum > 0) && ($qanum >= $limitnum))
					break;
				
				$subject = @$row['children'][0]['tagData'];
				$content = @$row['children'][1]['tagData'];				
				$date = @$row['children'][2]['tagData'];
				$username = @$row['children'][7]['tagData'];
				$user_photo = @$row['children'][8]['tagData'];
				$answer = @$row['children'][11]['tagData'];
				$answerer = @$row['children'][13]['tagData'];
				
				if(in_array($user_photo, $this->_yahooimg))
					$user_photo = '';
				
				if ($this->check_qa($keyword,$subject,$username))
					continue;
				
				$artarray = array('question_subject' => $subject, 'question_content' => $content, 'question_answer' => $answer, 'question_keyword' => $keyword, 'question_date' => $date, 'question_username'=>$username,'question_user_photo'=>$user_photo,'question_answerer'=>$answerer, 'question_created_date' => date('Y-m-d')); 
								
				if($directory_id = $this->save_qa($artarray))
				{
					$qanum++;					
				}			
			}
		endif;
		
		return $qanum;
	}
	
	function check_qa($keyword,$subject,$username) 
	{
		if ( $article_id = $this->_db->select_one("SELECT question_id FROM question_answer WHERE LOWER(question_keyword) = LOWER('".$keyword."') and LOWER(question_subject) = LOWER('".$subject."') and LOWER(question_username) = LOWER('".$username."') LIMIT 1"))
			return true;
		else
			return false;
	}
	
	public function save_qa($array, $id=0)
	{	
		// mark other q&a as not default
		if (!empty($array['question_default']))
		{
			if (!empty($array['question_keyword']))
				$kw = $array['question_keyword'];
			elseif ($id == 0)
				$kw = '';
			else
			{
				$info = $this->get_qa_info($id);
				$kw = !empty($info['question_keyword'])?$info['question_keyword']:'';
			}
			
			$sql = "UPDATE question_answer SET question_default=0  where question_keyword = '$kw' and question_id <> '$id'";
			$this->_db->update_sql($sql);
		}
		
		// Insert the new data
		if($id == 0)	
			$id = $this->_db->insert_array('question_answer', $array);
		else
			$this->_db->update_array('question_answer', $array, "question_id='".$id."'");
			
		return $id;
	}

	/*
	 *  this fuction is a copy of the one in sx25standard QuestionAnswerModule_Class
	 *   
	 */	

	public function getQuestionsByKeyword($keyword, $start='', $numQuestiones=5, $alterkw='', $avatar='', $expert = false)
	{
		$keyword = strtolower(trim($keyword));
		$alterkw = strtolower(trim($alterkw));
		$limit = ($numQuestiones > 0)?" LIMIT $numQuestiones ":'';
		$offset = (!empty($start) && !empty($limit))?" OFFSET $start ":'';
		$expertA = $expert?'and (select count(*) from answers where answers.question_id = question_answer.question_id) > 0':'';
		$aQuery = "SELECT * FROM question_answer WHERE question_keyword = '$keyword' $expertA  order by question_default DESC, RAND() $limit $offset ";
		$aResults = $this->_db->select($aQuery);
		$result = array();

		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$aRow['content_source'] = 'db';
			$aRow['question_user_photo'] = empty($aRow['question_user_photo'])?$avatar:$aRow['question_user_photo'];
			$result[] = $aRow;
		}
	
		if(sizeof($result)==0) 
		{
			// Search for Q&A by related keyword in related_keyword_qa table or with the $alterkw
			$where 	= " question_keyword in (ifnull((select related_keyword_original From related_keyword_qa where related_keyword = '$keyword'),'$alterkw')) and ";
			$aQuery = "SELECT * FROM question_answer WHERE $where question_answer IS NOT NULL $expertA order by question_default DESC, RAND() $limit $offset ";
			$aResults = $this->_db->select($aQuery);
			$result = array();

			while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) 
			{
				$aRow['content_source'] = 'db';
				$aRow['question_user_photo'] = empty($aRow['question_user_photo'])?$avatar:$aRow['question_user_photo'];
				$result[] = $aRow;
			}
			if(sizeof($result)==0) 
			{
				// Search any Q&A
				$aQuery = "SELECT * FROM question_answer WHERE question_answer IS NOT NULL $expertA order by question_default DESC, RAND() $limit $offset ";
				$aResults = $this->_db->select($aQuery);
				$result = array();
		
				while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
					$aRow['content_source'] = 'db';
					$aRow['question_user_photo'] = empty($aRow['question_user_photo'])?$avatar:$aRow['question_user_photo'];
					$result[] = $aRow;
				}
			}
		}
		return $result;
	}
	
	public function getQuestionById($question_id,$avatar='')
	{
		$aQuery = "SELECT * FROM question_answer WHERE question_id='".$question_id."' ";
		$aResults = $this->_db->select($aQuery);
		$pRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
		// check the default for the photo
		$pRow['question_user_photo'] = empty($pRow['question_user_photo'])?$avatar:$pRow['question_user_photo'];
		
		return $pRow;
	}
	
	/*
	 *  this fuction is a copy of the one in sx25standard QuestionAnswerModule_Class
	 *   
	 */	
	public function getQuestionAnswers($question_id, $maxAnswers=0, $offset='')
	{
		$question 	= !empty($question_id)?" WHERE question_id = '$question_id' ":'';
		$limit		= !empty($maxAnswers)?" LIMIT $maxAnswers ":'';
		$offset 	= (!empty($offset) && !empty($limit))? " OFFSET  $offset ":'';
		$cQuery = "SELECT * FROM answers $question ORDER BY question_id, answer_order $limit $offset ";
		$cResults = $this->_db->select($cQuery);
		$answersArray = array();
		while($cRow = $this->_db->get_row($cResults, 'MYSQL_ASSOC')) 
		{
			$answersArray[] = $cRow;
		}
		return $answersArray;
	}
	
	public function approveQuestion($id,$approved=1){
		global $user;
		if (!isset($user)) return false;
		
		$approved = ($approved == 0)?0:1;
		$aQuery = "UPDATE question_answer SET question_approved='$approved', question_approved_user = '".$user->userID."',question_update_date = now()  WHERE question_id = '".$id."'";
		if ($return = $this->_db->update_sql($aQuery))
			return $return;
		else
			return false;
	}
	
}
?>
