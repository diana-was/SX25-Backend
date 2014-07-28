<?php
/**
 * Directory class
 * Author: Gordon Ye 16/05/2011
 * 
 */

class Answer extends  Model
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
	
	public function replaceImageLibraryURL ($data,$for)
	{
		$controller = Controller::getInstance();
		$config = Config::getInstance();
		if ($for == 'display')
		{
			$data = str_ireplace('{IMAGE_LIBRARY}',$config->imageLibrary,$data);
		}
		elseif ($for == 'db')
		{
			$imageLibrary = ($controller->appURL == '/') && ($config->imageLibrary[0] == '/')?substr($config->imageLibrary,1):$config->imageLibrary;
			$server = ($_SERVER["SERVER_PORT"] != "80")? str_ireplace(':'.$_SERVER["SERVER_PORT"],'',$controller->server):$controller->server;
			$data = str_ireplace($controller->server,'',$data);
			$data = str_ireplace($server,'',$data);
			$data = str_ireplace($config->imageLibrary,'{IMAGE_LIBRARY}',$data);
			$data = str_ireplace($imageLibrary,'{IMAGE_LIBRARY}',$data);
		}
		return $data;
	}
	
	public function check_answer_set($question_id,$amount=1)
	{		
		if ( $answer = $this->_db->select_one("SELECT count(*) FROM answers WHERE question_id = '$question_id' "))
			return ($answer >= $amount)?$answer:false;
		else
			return false;
	}

	public function get_answer_info($answer_id) 
	{
		$pQuery = "SELECT * FROM answers WHERE answer_id = '".$answer_id."' LIMIT 1";
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

	function get_library_answer($fromRecord,$recordPerPage,$sortyQuery) {
		
		$output = array();
		$answerQuery = "SELECT * FROM answers ".$sortyQuery." LIMIT ".$fromRecord.",".$recordPerPage;
		$pResults = $this->_db->select($answerQuery);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		
		return $output;
	}

	function get_answer($question_id, $limit=1, $startnum=0, $sortyQuery='') {
	
		$answer = array();
		$sortyQuery = empty($sortyQuery)?'ORDER BY answer_order ':$sortyQuery;
		$limit = (empty($limit) || !is_numeric($limit))?1:$limit;
		
		$answerQuery = "SELECT * FROM answers WHERE question_id = '$question_id' $sortyQuery LIMIT $startnum,$limit ";
		$pResults = $this->_db->select($answerQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$answer[] = $pRow;		
			}
		}
	
		return $answer;
	}
	
	public function count_question_answers($question_id) 
	{
		$query = empty($question_id)?'':" WHERE question_id = '$question_id' ";
		$answerQuery = "SELECT count(*) FROM answers $query ";
		$count = $this->_db->select_one($answerQuery);
		return $count;
	}

	function count_total_answers() 
	{
		$directory = array();
		
		$directoryQuery = "SELECT question_id, count(*) as `answers`  FROM answers GROUP BY question_id ORDER BY question_id";
		$pResults = $this->_db->select($directoryQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$directory[] = $pRow;		
			}
		}
	
		return $directory;
	}
	
	function count_keywords_answers() {
	
		$directory = array();
		
		$directoryQuery = "SELECT question_keyword AS keyword
				, COUNT(*) AS `Q&A`
				, CASE SUM(`question_default`) WHEN 0 THEN 'NO' ELSE 'Yes' END AS defaults
				, SUM(Book) AS Books
				, SUM(School) AS Schools
				, SUM(Article) AS Articles
				, SUM(Comparison) AS Comparisons
				, SUM(Blog) AS Blogs
				, SUM(Other) AS Others
				FROM question_answer 
				JOIN (
					SELECT question_id
					,SUM(CASE answer_type WHEN 'Book' THEN 1 ELSE 0 END) Book
					,SUM(CASE answer_type WHEN 'School' THEN 1 ELSE 0 END) School
					,SUM(CASE answer_type WHEN 'Article' THEN 1 ELSE 0 END) Article
					,SUM(CASE answer_type WHEN 'Comparison' THEN 1 ELSE 0 END) Comparison
					,SUM(CASE answer_type WHEN 'Blog' THEN 1 ELSE 0 END) Blog
					,SUM(CASE answer_type WHEN 'Other' THEN 1 ELSE 0 END) Other
					FROM answers GROUP BY question_id ORDER BY question_id
				) AS a ON a.question_id = question_answer.question_id
				GROUP BY question_keyword ORDER BY question_keyword";
		$pResults = $this->_db->select($directoryQuery);
		if($pResults){
			while($pRow = $this->_db->get_row($pResults,'MYSQL_ASSOC')){
				$directory[] = $pRow;		
			}
		}
	
		return $directory;
	}
	
	
	
	public function del_answer($id)
	{	
		$a = $this->get_answer_info($id);
		if (!empty($a) && $a['answer_order'] > 0)
		{
				// update old answers order
				$uQuery = "UPDATE answers SET answer_order = answer_order - 1 WHERE question_id = '".$a['question_id']."' and answer_order > '".$a['answer_order']."' ";
				$this->_db->update_sql($uQuery);
		}	
		$dQuery = "DELETE FROM answers WHERE answer_id = '".$id."'";
		if($this->_db->delete($dQuery))
			return true;
		else
			return false;
	}
	
	public function scrape_answer($question_id, $keyword, $limitnum=0, $this_id=0) 
	{			
		$contentLayout = 29;
		$Html = new Html();
		$modObj = Shopping::getInstance();
		$ModuleLayout = ModuleLayout::getInstance($this->_db);
		$_answer = $modObj->getData($keyword, 'amazon', $limitnum, array('category' => 'books'));

		$answernum = 0;
		if(!empty($_answer)):
	
			foreach($_answer as $row)
			{
				if (($limitnum > 0) && ($answernum >= $limitnum))
					break;

				$answer_content     = '';
				$answer_short_answer= '';
				$answer_user_name 	= '';
				$answer_keyword     = $keyword;
				$answer_subject 	= $row['product_name'];
				$answer_user_photo  = $row['product_image'];
				$answer_link        = $row['product_url'];
				
				if ($this->check_answer($question_id,$answer_link,$answer_subject))
					continue;
				
				// Get the detail
				$_detail = $modObj->getData($keyword, 'amazon', 1, array('product_id' => $row['product_id']));
				if (!empty($_detail))
				{
					// content	
					$layout = $ModuleLayout->get_modulelayout_info($contentLayout);
					// replace the data in the layout
					if (!empty($layout)) 
					{
						$answer_content = $Html->replace_tag($Html->parseShoppingDetail($layout['modulelayout'],$_detail));
					}			
					// short answer		
					if (isset($_detail[0]['product_description'])) 
					{
						$answer_short_answer = $_detail[0]['product_description'];
					}					
					// author		
					if (isset($_detail[0]['product_features']))
					{
						$answer_user_name = $this->get_author($_detail[0]['product_features']);
					}
				}
				
				$artarray = array(	'question_id'		=> $question_id,
									'answer_subject' 	=> $answer_subject, 
									'answer_content'	=> $answer_content,
									'answer_short_answer'=>$answer_short_answer, 
									'answer_user_name'	=> $answer_user_name,
									'answer_keyword' 	=> $keyword,
									'answer_user_photo'	=> $answer_user_photo,
									'answer_link' 		=> $answer_link,
									'answer_type'		=> 'Book',
									'answer_created_date' => date('Y-m-d')
								);
				if($id = $this->save_answer($artarray))
				{
					$answernum++;					
				}			
			}
		endif;
		
		return $answernum;
	}
	
	function get_author($features)
	{
		$answer_user_name = '';
		$features = trim($features);
		if (!empty($features))
		{
			$parts = explode('<li>',$features);
			foreach($parts as $p) 
			{
				if (stripos ( $p, 'author') !== false)
				{
					$answer_user_name = trim(str_ireplace(array('author',':'), '', strip_tags($p)));
				}
			}
		}
		return $answer_user_name;
	}
	
	function check_answer($question_id,$answer_link,$answer_subject) 
	{
		if ( $id = $this->_db->select_one("SELECT answer_id FROM answers WHERE question_id = '$question_id' and answer_link = '$answer_link' and answer_subject = '$answer_subject' LIMIT 1"))
			return true;
		else
			return false;
	}
	
	public function save_answer($array, $id=0)
	{	
		$n = -1;
		// get aswer info
		if (!empty($id)) 
		{
			$a = $this->get_answer_info($id);
			$nOld = !empty($a['question_id'])?$this->count_question_answers($a['question_id']):-1;
		}
		if (isset($array['question_id']))
		{
			$n = $this->count_question_answers($array['question_id']);
		}	
		// Insert the new data
		if($id == 0)
		{
			$array['answer_order'] = $n + 1; 
			$id = $this->_db->insert_array('answers', $array);
		}
		else
		{
			// The order change
			if (isset($array['answer_order']) && ($array['answer_order'] != $a['answer_order']) && (!isset($array['question_id']) || $array['question_id'] == $a['question_id']))
			{
				$array['answer_order'] = $array['answer_order'] > $nOld?$nOld:$array['answer_order'];
				if ($array['answer_order'] > $a['answer_order'])
					$uQuery = "UPDATE answers SET answer_order = answer_order - 1 WHERE question_id = '".$a['question_id']."' and answer_id <> '$id' and answer_order > '".$a['answer_order']."' and answer_order <= '".$array['answer_order']."' ";
				else 
					$uQuery = "UPDATE answers SET answer_order = answer_order + 1 WHERE question_id = '".$a['question_id']."' and answer_id <> '$id' and answer_order < '".$a['answer_order']."' and answer_order >= '".$array['answer_order']."' ";
				$this->_db->update_sql($uQuery);
			}
			// The question_id change
			elseif ((isset($array['question_id']) && $array['question_id'] != $a['question_id']))
			{
				// update old answers order
				$uQuery = "UPDATE answers SET answer_order = answer_order - 1 WHERE question_id = '".$a['question_id']."' and answer_order > '".$a['answer_order']."' ";
				$this->_db->update_sql($uQuery);
				// update new answers order
				$array['answer_order'] = $array['answer_order'] > $n?$n+1:$array['answer_order'];
				$uQuery = "UPDATE answers SET answer_order = answer_order + 1 WHERE question_id = '".$array['question_id']."' answer_order < '".$array['answer_order']."' ";
				$this->_db->update_sql($uQuery);
			}
			// update the answer
			$this->_db->update_array('answers', $array, "answer_id='".$id."'");
		}

		//echo '<pre>'; print_r($array);	echo '</pre><br>';							
		//echo '<br>last_error:'.$this->_db->last_error;
		return $id;
	}

	/*
	 *  this fuction is a copy of the one in sx25standard QuestionAnswerModule_Class
	 *   
	 */
	public function getAnswerByQuestionID($question_id, $start='', $numQuestiones=5, $avatar='')
	{
		$limit = ($numQuestiones > 0)?" LIMIT $numQuestiones ":'';
		$offset = (!empty($start) && !empty($limit))?" OFFSET $start ":'';
		$aQuery = "SELECT * FROM answers WHERE question_id='".$question_id."' order by answer_order $limit $offset ";
		$aResults = $this->_db->select($aQuery);
		$result = array();

		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$aRow['answer_user_photo'] = empty($aRow['answer_user_photo'])?$avatar:$aRow['answer_user_photo'];
			$result[] = $aRow;
		}
	
		return $result;
	}
	
	public function getAnswerById($answer_id,$avatar='')
	{
		$aQuery = "SELECT * FROM answers WHERE answer_id='".$answer_id."' ";
		$aResults = $this->_db->select($aQuery);
		$pRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
		// check the default for the photo
		$pRow['answer_user_photo'] = empty($pRow['answer_user_photo'])?$avatar:$pRow['answer_user_photo'];
		
		return $pRow;
	}
}
?>
