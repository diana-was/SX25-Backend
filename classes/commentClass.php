<?php
/**	APPLICATION:	SX25
*	FILE:			Comment.php
*	DESCRIPTION:	display comment data from database
*	CREATED:		20 October 2010 by Diana De vargas
*	UPDATED:									
*/

class Comment extends Model
{
	private 	$_db;
	private static $_Object; 
	private 	$_WordPressAPIKey = '92763e4423cb';
	
    /**
     * constructor : set up the variables
     *
     * @param dbobject $db db_class object

     * @return object
     */
	public function __construct(db_class $db)
	{
		$this->_db = $db; 
		self::$_Object = $this;
		return self::$_Object;
	}


    /**
     * Get the controller static object
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

    
	public function get_comments($table,$id,$domain_id,$status='',$maxComments=0, $fromRecord='', $sortyQuery='')
	{
		$output = array();

		if (empty($table) && empty($domain_id))
			return $output;

		$wStatus 	= empty($status)?'':"AND comment_status = '$status'";
		$limit 		= (empty($maxComments) || !is_numeric($maxComments))?'':"LIMIT $maxComments";
		$limit 		= (empty($fromRecord) || !is_numeric($fromRecord))?$limit:(empty($limit)?"OFFSET $fromRecord":"LIMIT $fromRecord,$maxComments");
		$sortyQuery = empty($sortyQuery)?'ORDER BY comment_date DESC':$sortyQuery;
			
		/* Get the domain comments */
		if (empty($table))
		{
			$cQuery = "SELECT * FROM comments WHERE comment_domain_id = '$domain_id' $wStatus $sortyQuery $limit ";
		}
		else
		{
			if (!empty($domain_id))
			{
				$question = " comment_table = '$table' and comment_table_id = '$id' and comment_domain_id = '$domain_id' ";
				$cQuery = "SELECT * FROM comments WHERE $question $wStatus $sortyQuery $limit ";
			}			
			else 
			{
				$question = " comment_table = '$table' and comment_table_id = '$id' ";
				$cQuery = "SELECT * FROM comments WHERE $question $wStatus $sortyQuery $limit ";
			}	
		}	

		$cResults = $this->_db->select($cQuery);
		while(($cRow = $this->_db->get_row($cResults, 'MYSQL_ASSOC'))) 
		{
			$output[]	= $cRow;
		}
		return $output;
	}

	public function count_comments($table,$id,$domain_id,$status='')
	{
		if (empty($table) && empty($domain_id))
			return 0;

		$wStatus 	= empty($status)?'':"AND comment_status = '$status'";
			
		/* Get the domain comments */
		if (empty($table))
		{
			$cQuery = "SELECT count(*) FROM comments WHERE comment_domain_id = '$domain_id' $wStatus";
		}
		else
		{
			if (!empty($domain_id))
			{
				$question = " comment_table = '$table' and comment_table_id = '$id' and comment_domain_id = '$domain_id' ";
				$cQuery = "SELECT count(*) FROM comments WHERE $question $wStatus";
			}			
			else 
			{
				$question = " comment_table = '$table' and comment_table_id = '$id' ";
				$cQuery = "SELECT count(*) FROM comments WHERE $question $wStatus";
			}	
		}	

		return $this->_db->select_one($cQuery);;
	}
	
	public function get_commentInfo($id) 
	{
		$output = array();
		$sql = "select * From comments where comment_id = '$id' Limit 1 ";

		$result = $this->_db->select($sql);
		if($output = $this->_db->get_row($result, 'MYSQL_ASSOC'))
		{
			return $output;		
		}
		else
			return false;
	}
	
	/*
	 *  Save the commentd data in the comments table
	 *  
	 *  array = an array with the field => value. Special field interest is an array stored in json format.
	 *  id  = id to update, 0 if new comment
	 *  
	 *  return id or false if fail
	 */
	public function insert_comment($table,$id,$domain_id,$domain,$array,$CheckDomain=true)
	{
		$MyBlogURL 			= $CheckDomain?"http://$domain":"http://kwithost.com";
		$comment_author 	= !empty($array['author'])?mysql_real_escape_string(strip_tags($array['author'])):'';
		$comment_title 		= !empty($array['title'])?mysql_real_escape_string(strip_tags($array['title'])):'';
		$comment_content 	= !empty($array['content'])?mysql_real_escape_string(strip_tags($array['content'])):'';

		if(!empty($comment_content) && (!$CheckDomain || !empty($domain)))
		{
			$akismet = new Akismet($MyBlogURL ,$this->WordPressAPIKey);
			$akismet->setCommentAuthor($comment_author);
			$akismet->setCommentContent($comment_content);
			$akismet->setPermalink($MyBlogURL);
			if(!$akismet->isCommentSpam())
			{
				$data = array();
				$data['comment_author'] 	= $comment_author;
				$data['comment_title'] 		= $comment_title;
				$data['comment_content'] 	= $comment_content;
				$data['comment_domain'] 	= $domain;
				$data['comment_date'] 		= date("Y-m-d");
				$data['comment_status'] 	= (!empty($array['status']) && $array['status'] == 1)?1:0;
				if (!empty($domain_id) && is_numeric($domain_id))
					$data['comment_domain_id'] 	= $domain_id;
				
				if (!empty($table) && !empty($id) && is_numeric($id))
				{
					$data['comment_table']	= $table;
					$data['comment_table_id']= $id;
				}
				
				$id = $this->_db->insert_array('comments', $data);
				return $id;
			}
		}

		return false;
	}

	function save_comment($array, $comment_id=0,$CheckDomain=true)
	{
		if (empty($comment_id) || !is_numeric($comment_id))
		{
			$table		= !empty($array['table'])?mysql_real_escape_string(strip_tags($array['table'])):'';
			$id			= !empty($array['table_id'])?mysql_real_escape_string(strip_tags($array['table_id'])):'';
			$domain_id	= !empty($array['domain_id'])?mysql_real_escape_string(strip_tags($array['domain_id'])):'';
			$domain		=  !empty($array['domain'])?mysql_real_escape_string(strip_tags($array['domain'])):'';
			return $this->insert_comment($table,$id,$domain_id,$domain,$array,$CheckDomain);
		}
		else 
		{	
			$data = array();
			
			if(isset($array['content']) && !empty($array['content']))
			{
				$data['comment_content'] 	= mysql_real_escape_string(strip_tags($array['content']));
			}
	
			if (isset($array['author']))
				$data['comment_author'] = mysql_real_escape_string(strip_tags($array['author']));
	
			if (isset($array['title']))
				$data['comment_title'] 	= mysql_real_escape_string(strip_tags($array['title']));
	
			if (isset($array['domain']))
				$data['comment_domain'] = mysql_real_escape_string(strip_tags($array['domain']));
				
			if (isset($array['domain_id']) && is_numeric($array['domain_id']))
				$data['comment_domain_id'] 	= mysql_real_escape_string(strip_tags($array['domain_id']));
			
			if (isset($array['table']) && !empty($array['table_id']) && is_numeric($array['table_id']))
			{
				$data['comment_table']	= mysql_real_escape_string(strip_tags($array['table']));
				$data['comment_table_id']= mysql_real_escape_string(strip_tags($array['table_id']));
			}
			
			$this->_db->update_array('comments', $data, "comment_id='$comment_id'");
			return $comment_id;
		}
	}
	
	public function change_comment_status($comment_id,$status)
	{
		if (is_numeric($comment_id) && is_numeric($status))
		{
			$dQuery = "UPDATE comments SET comment_status = $status WHERE comment_id = $comment_id";
			return $this->_db->update_sql($dQuery);
		}
		return false;
	}
    
	public function del_comment($id)
	{
		$dQuery = "DELETE FROM comments WHERE comment_id = '$id'";
		if($this->_db->delete($dQuery))
			return true;
		
		return false;
	}
	
	public function list_comments($status=null,$table=null,$startDate=null,$endDate=null)
	{
		$output = array();
		$where 	= empty($status)?'':"WHERE comment_status = '$status'";
		$where  = empty($table)?$where:"$where AND comment_table = '$table'";
		$date   = empty($startDate)?'':" comment_date >= '$startDate' ";
		$date  .= empty($endDate)?'':(empty($date)?'':' AND ')." comment_date <= '$endDate' ";
		$where  = empty($date)?$where:"$where AND $date";
		$sql 	= "SELECT *, CASE comment_table 
					WHEN 'articles' THEN (SELECT article_title FROM articles WHERE article_id = comment_table_id)
					WHEN 'question_answer' THEN (SELECT question_subject FROM question_answer WHERE question_id = comment_table_id)
					WHEN 'goals' THEN (SELECT goal_subject FROM goals WHERE goal_id = comment_table_id)
					ELSE NULL END AS comment_title
					FROM comments $where ORDER BY comment_id DESC Limit 100 ";
		
		$pResults = $this->_db->select($sql);
		while($row = $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		return $output;
	}
	
    
}