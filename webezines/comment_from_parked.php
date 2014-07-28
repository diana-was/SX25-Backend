<?php
include_once("../config.php");
$insert_id = 0;
$msg = 'Sorry missing parameters';

if (!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'contact_us':
			$name 	= !empty($_REQUEST['name'])?mysql_real_escape_string(strip_tags($_REQUEST['name'])):'';
			$email 	= !empty($_REQUEST['email'])?mysql_real_escape_string(strip_tags($_REQUEST['email'])):'';
			$subject= !empty($_REQUEST['subject'])?mysql_real_escape_string(strip_tags($_REQUEST['subject'])):'';
			$message= !empty($_REQUEST['message'])?mysql_real_escape_string(strip_tags($_REQUEST['message'])):'';
			$domain = !empty($_REQUEST['domain'])?mysql_real_escape_string(strip_tags($_REQUEST['domain'])):'';
			
			if($message != '')
			{
				$Msg = Message::getInstance($db);
				$insert_id = $Msg->save_message($domain,0,array('name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message));
			}
		    $msg = 'Thanks for your message!';
		break;
		default:
			$comment_author 	= !empty($_REQUEST['author'])?mysql_real_escape_string(strip_tags($_REQUEST['author'])):'';
			$comment_title 		= !empty($_REQUEST['title'])?mysql_real_escape_string(strip_tags($_REQUEST['title'])):'';
			$comment_content 	= !empty($_REQUEST['comment_content'])?mysql_real_escape_string(strip_tags($_REQUEST['comment_content'])):'';
			$comment_article_id = !empty($_REQUEST['article_id'])?mysql_real_escape_string(strip_tags($_REQUEST['article_id'])):'';
			$comment_keyword 	= !empty($_REQUEST['keyword'])?mysql_real_escape_string(strip_tags($_REQUEST['keyword'])):'';
			$comment_question_id= !empty($_REQUEST['question_id'])?mysql_real_escape_string(strip_tags($_REQUEST['question_id'])):'';
			$comment_domain 	= !empty($_REQUEST['domain'])?mysql_real_escape_string(strip_tags($_REQUEST['domain'])):'';
		
			if (!empty($comment_article_id) && is_numeric($comment_article_id))
			{
				$table			= 'articles';
				$id				= $comment_article_id;
			}
			elseif (!empty($comment_question_id) && is_numeric($comment_question_id))
			{
				$table			= 'question_answer';
				$id				= $comment_question_id;
			}
			else
			{
				$table			= !empty($_REQUEST['about'])?@mysql_real_escape_string(strip_tags($_REQUEST['about'])):'';
				$id				= !empty($_REQUEST['id'])?@mysql_real_escape_string(strip_tags($_REQUEST['id'])):'';
			}
			
			$extra = ''; 
			if(!empty($comment_keyword))
				$extra = 'keyword: '.$comment_keyword.'; ';		
				
			$comment_content = $extra.$comment_content;
			
			if($comment_content != '')
			{
				$comment = Comment::getInstance($db);
				$insert_id = $comment->insert_comment($table,$id,0,$comment_domain,array('author' => $comment_author, 'title' => $comment_title, 'content' => $comment_content));
			}
		    $msg = 'Thanks for your comment!';
		break;
	}
}
if (isset($_GET['callback']))
	echo $_GET['callback'] . "(\"$msg\")";
else
	echo json_encode(array('result' => !empty($insert_id), 'msg' => $msg));
?>