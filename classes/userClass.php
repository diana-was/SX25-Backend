<?php
/**
 * User Class
 * Author: Archie Huang on 13/03/2009
**/

class User extends  Model
{
  protected  $displayErrors = true;
  /*Do not edit after this line*/
  protected $userID;
  protected $userLevel;
  protected $userPW;
  
  protected $userData=array();
  /**
   * Class Constructure
   * 
   * @param string $dbConn
   * @param array $settings
   * @return void
   */
  function __construct($settings = '')
  {
	  	global $db;
	    if ( is_array($settings) ){
		    foreach ( $settings as $k => $v ){
				    if ( !isset( $this->{$k} ) ) die('Property '.$k.' does not exists. Check your settings.');
				    $this->{$k} = $v;
			}
	    }

	    if(!isset($_SESSION)) session_start();
	    if (isset($_SESSION['princetonUser']) && !empty($_SESSION['princetonUser']))
	    {
		    $this->loadUser($_SESSION['princetonUser'] );
	    }
	    //Maybe there is a cookie?
	    if ( isset($_COOKIE['princetonCk']) && !$this->is_loaded()){
	      $u = unserialize(base64_decode($_COOKIE['princetonCk']));
	      $this->login($u['uname'], $u['password']);
	    }
  }
  
  /**
  	* Login function
  	* @param string $uname
  	* @param string $password
  	* @param bool $loadUser
  	* @return bool
  */
  function login($uname, $password, $remember = false, $loadUser = true)
  {
	  	global $db;
    	$uname    = $this->escape($uname);
    	$password = $originalPassword = $this->escape($password);
		$password = "MD5('$password')";

		$userQuery = "SELECT * FROM users WHERE user_name = '".$uname."' and user_password = ".$password." LIMIT 1";

		$pResults = $db->select($userQuery);
		if($this->userData = $db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$this->userID = $this->userData['user_id'];
			$this->userLevel = $this->userData['user_level'];
			$this->userPW = $this->userData['user_password'];
			$_SESSION['princetonUser'] = $this->userID;
			$_SESSION['princetonPW'] = $this->userPW;
			if ( $remember ){
			  $cookie = base64_encode(serialize(array('uname'=>$uname,'password'=>$originalPassword)));
			  $a = setcookie('princetonCk',$cookie,time()+2592000, '/', 'kwithost.com');
			}
			return true;
		}
		else
			return false;
  }
  
  /**
  	* Logout function
  	* param string $redirectTo
  	* @return bool
  */
  function logout($redirectTo = '')
  {
    setcookie('princetonCk', '', time()-3600);
    unset($_SESSION['princetonUser']);
    unset($_SESSION['princetonPW']);
    $this->userData = '';
    if ( $redirectTo != '' && !headers_sent()){
	   header('Location: '.$redirectTo );
	   exit;//To ensure security
	}
  }
  /**
  	* Function to determine if a property is true or false
  	* param string $prop
  	* @return bool
  */
  function is($prop){
  	return $this->get_property($prop)==1?true:false;
  }
  
    /**
  	* Get a property of a user. You should give here the name of the field that you seek from the user table
  	* @param string $property
  	* @return string
  */
  function get_property($property)
  {
    if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
    if (!isset($this->userData[$property])) $this->error('Unknown property <b>'.$property.'</b>', __LINE__);
    return $this->userData[$property];
  }
  /**
  	* Is the user an active user?
  	* @return bool
  */
  function is_active()
  {
    return $this->userData['user_active'];
  }
  
  /**
   * Is the user loaded?
   * @ return bool
   */
  function is_loaded()
  {
    return (empty($this->userID) || !isset($_SESSION['princetonPW']) || ($_SESSION['princetonPW'] != $this->userPW))? false : true;
  }
  /**
  	* Activates the user account
  	* @return bool
  */
  function activate()
  {
	global $db;
    if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
    if ( $this->is_active()) $this->error('Allready active account', __LINE__);

	$userQuery = "UPDATE users SET user_active = '1' WHERE user_id = '".$this->escape($this->userID)."' LIMIT 1";

	$pResults = $db->update_sql($userQuery);
	if($pResults)
	{
		$this->userData['user_active'] = true;
		return true;
	}
	return false;
  }
  /*
   * Creates a user account. The array should have the form 'database field' => 'value'
   * @param array $data
   * return int
   */  
  function insertUser($data){
	global $db;  
    if (!is_array($data)) $this->error('Data is not an array', __LINE__);

	$password = md5($data['user_password']);
    $data['user_password'] = $password;
    $id = $db->insert_array('users', $data);
    return $id;
  }
  /*
   * Creates a random password. You can use it to create a password or a hash for user activation
   * param int $length
   * param string $chrs
   * return string
   */
  function randomPass($length=10, $chrs = '1234567890qwertyuiopasdfghjklzxcvbnm'){
    for($i = 0; $i < $length; $i++) {
        $pwd .= $chrs{mt_rand(0, strlen($chrs)-1)};
    }
    return $pwd;
  }

  /**
  	* A function that is used to load one user's data
  	* @access private
  	* @param string $userID
  	* @return bool
  */
  function loadUser($userID)
  {
	global $db;

	$userQuery = "SELECT * FROM users WHERE user_id = '".$this->escape($userID)."' LIMIT 1";

	$pResults = $db->select($userQuery);
	if($this->userData = $db->get_row($pResults, 'MYSQL_ASSOC'))
	{
		$this->userID = $this->userData['user_id'];
		$this->userLevel = $this->userData['user_level'];
		$this->userPW = $this->userData['user_password'];
		return true;
	}
	else
		return false;
  }

  /**
  	* Produces the result of addslashes() with more safety
  	* @access private
  	* @param string $str
  	* @return string
  */  
  function escape($str) {
	global $db;
    $str = get_magic_quotes_gpc()?stripslashes($str):$str;
    $str = mysql_real_escape_string($str);
    return $str;
  }
  
  /**
  	* Error holder for the class
  	* @access private
  	* @param string $error
  	* @param int $line
  	* @param bool $die
  	* @return bool
  */  
  function error($error, $line = '', $die = false) {
    if ( $this->displayErrors )
    	echo '<b>Error: </b>'.$error.'<br /><b>Line: </b>'.($line==''?'Unknown':$line).'<br />';
    if ($die) exit;
    return false;
  }
}
?>