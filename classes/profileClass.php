<?php
/**
 * Profile class
 * Author: Archie Huang on 03/04/2009
 * 
 */

class Profile extends Model
{
	function __construct() {
	} 
	
	function check_profile_id($profile) {
		global $db;
		
		$profile_id = false;
		
		$profile_id = $db->select_one("SELECT profile_id FROM profiles WHERE profile_name LIKE '".$profile."' LIMIT 1");
		return $profile_id;
	
	}

	function get_profile_id($profile_ip) {
		global $db;
		
		if($profile_id = $db->select_one("SELECT profile_id FROM profiles WHERE profile_ip LIKE '".$profile_ip."' LIMIT 1")){		
			return $profile_id;
		}else{
			return false;
		}		
	}
	
	function save_profile($array, $id=0)
	{
		global $db;
	
		if($id == 0)	
			$id = $db->insert_array('profiles', $array);
		else
			$db->update_array('profiles', $array, "profile_id='".$id."'");
			
		return $id;
	}
	
	function save_account($accname, $id=0)
	{
		global $db;
	
		if($id == 0)	
			$id = $db->insert_sql("insert into accounts (account_name,account_profile) VALUES ('".$accname['account_name']."','".$accname['account_profile']."')");
		else
			$db->update_sql("update accounts set account_name = '".$accname['account_name']."' where account_id='".$id."'");
			
		return $id;
	}
	
	function del_account($id)
	{
		global $db;
		$upQuery = "SELECT count(*) FROM domains WHERE domain_account_id = '".$id."'";
		$count = $db->select_one($upQuery);
		
		if ($count == 0)
		{
			$dQuery = "DELETE FROM accounts WHERE account_id = '".$id."'";
			if($db->delete($dQuery))
				return true;
			else
				return false;
		}
		else 
			return false;
	}
	
	function getProfileInfo($profile)
	{
		global $db;
	
		$pQuery = "SELECT * FROM profiles WHERE profile_id = '".$profile."' LIMIT 1";
		$pResults = $db->select($pQuery);
		if($pRow=$db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			return $pRow;
		}
		else
		{
			return false;
		}
		
	}

	function getProfileList()
	{
		global $db;
		
		$result = array();
		$pQuery = "SELECT * FROM profiles ORDER BY profile_name ";
		$pResults = $db->select($pQuery);

		while ($aRow = $db->get_row($pResults, 'MYSQL_ASSOC')) {
			$result[] = $aRow;
		}
		return $result;
	}
	
	public	function getParkedProfiles()
	{
		global $db;
		$returnArray = array();
		
		$pQuery = "SELECT * FROM profiles where profile_parked_domain is not null ORDER BY profile_name";
		$pResults = $db->select($pQuery);
		while($pRow=$db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$returnArray[] = $pRow;
		}
		return $returnArray;
	}
	
	
	
	function getAccountInfo($account_id)
	{
		global $db;
	
		$pQuery = "SELECT * FROM accounts WHERE account_id = '".$account_id."' LIMIT 1";
		$pResults = $db->select($pQuery);
		if($pRow=$db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			return $pRow;
		}
		else
		{
			return false;
		}
		
	}
	function getAccounts($profile_id)
	{
		global $db;
		
		$returnArray = array();
		
		if($profile_id == '0')
			$pQuery = "SELECT * FROM accounts ORDER BY account_id";
		else
			$pQuery = "SELECT * FROM accounts WHERE account_profile = '".$profile_id."' OR  account_profile = '0' ORDER BY account_id";
		$pResults = $db->select($pQuery);
		while($pRow=$db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$returnArray[] = $pRow;
		}
		return $returnArray;
		
	}
}
?>
