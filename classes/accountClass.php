<?php
/*	APPLICATION:	PrincetonIT SX2
	FILE:			accountClass.php
	DESCRIPTION:	Class to extract accounts data from database
	Author: 		Archie Huang on 03/04/2009
	CREATED:		19 Jan 2011 by Gordon Ye
	UPDATED:		Diana Devargas 08/02/2011		
*/

class Account extends  Model
{
 
	private $_db; 
	private static $_Object;
	
	private function __construct(db_class $db)
	{
		$this->_db = $db;
		self::$_Object = $this;
		return self::$_Object;
	}

    /**
     * Get the class static object
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
			
	public function checkAccount($account){
		$sql = "SELECT * FROM accounts WHERE account_name='".$account."'";
		if($this->_db->select_one($sql)){		
			return true;
		}else{
			return false;
		}
	}

	public function check_account_id($account,$profile_id) {
		
		$accountQ = "SELECT account_id FROM accounts WHERE LOWER(account_name) = LOWER('".$account."') and account_profile = '".$profile_id."' LIMIT 1";
		if ($account_id = $this->_db->select_one($accountQ))
			return $account_id;
		else 
			return false;
	}

	public function get_account_info_name($account,$profile_id) {
		$pQuery = "SELECT * FROM accounts WHERE LOWER(account_name) = LOWER('".$account."') and account_profile = '".$profile_id."' LIMIT 1";
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

	public function get_account_info($account_id) {
		$pQuery = "SELECT * FROM accounts WHERE account_id = '".$account_id."' LIMIT 1";
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

	public function save_account($array, $id=0)
	{
		if($id == '0')
		{
			$id = $this->_db->insert_array('accounts', $array);
		}
		else
			$this->_db->update_array('accounts', $array, "account_id='".$id."'");
			
		return $id;
	}

	function getAccountList()
	{
		$result = array();
		$pQuery = "SELECT * FROM accounts ORDER BY account_name ";
		$pResults = $this->_db->select($pQuery);

		while ($aRow = $this->_db->get_row($pResults, 'MYSQL_ASSOC')) {
			$result[] = $aRow;
		}
		return $result;
	}

	public function get_num_domains($account_id) {
		
		$accountQ = "SELECT count(*) FROM domains WHERE domain_account_id='$account_id'";
		if ($num = $this->_db->select_one($accountQ))
			return $num;
		else 
			return 0;
	}
	
}

?>
