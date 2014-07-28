<?php
/**
 * Layout class
 * Author: Archie Huang on 08/05/2009
 * 
 */

class ParkedAccount extends Model
{
	function __construct() {
	} 
	
	function check_parked_account_id($parked_account_name) {
		global $db;
		
		$parked_account_id = false;
		$parked_accountQ = "SELECT parked_account_id FROM parked_accounts WHERE parked_account_name LIKE '".$parked_account_name."' LIMIT 1";
	
		$parked_account_id = $db->select_one($parked_accountQ);
		return $parked_account_id;
	
	}
	
	function get_parked_account_info($parked_account_id) {
		global $db;
		
		$pQuery = "SELECT * FROM parked_accounts WHERE parked_account_id = '".$parked_account_id."' LIMIT 1";
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
	
	function save_parked_account($array, $id=0)
	{
		global $db;
	
		if($id == '0')
		{
			$id = $db->insert_array('parked_accounts', $array);
		}
		else
			$db->update_array('parked_accounts', $array, "parked_account_id='".$id."'");
			
		return $id;
	}
	
	public function get_parked_accounts() {
		global $db;
		$returnArray = array();
		
		$pQuery = "SELECT * FROM parked_accounts ORDER BY CAST(TRIM(REPLACE(parked_account_name,'Parked','')) AS SIGNED)";
		$pResults = $db->select($pQuery);
		while($pRow=$db->get_row($pResults, 'MYSQL_ASSOC'))
		{
			$returnArray[] = $pRow;
		}
		return $returnArray;
	}
}
?>
