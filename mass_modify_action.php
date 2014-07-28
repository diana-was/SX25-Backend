<?php
require_once('config.php');
unset($domainsArray);

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'export')
{
	$domain = Site::getInstance($db);
	
	$group_type = isset($_REQUEST['group_type'])?$_REQUEST['group_type']:'all';
	$fields = isset($_REQUEST['fields'])?$_REQUEST['fields']:'';
	$domainsArray = array();
	$i=0;

	switch($group_type)
	{
		case 'all':
			$pResults = $domain->get_domain_data_list();
			foreach($pResults as $pRow)
			{
				foreach($fields as $fieldname)
				{
					$domainsArray[$i][$fieldname] = $pRow[$fieldname];
				}
				$i++;
			}
		break;
		
		case 'profile':
			$profile_id =  isset($_REQUEST['profile_id'])?$_REQUEST['profile_id']:'';
			$account_id =  isset($_REQUEST['account_id'])?$_REQUEST['account_id']:'';
			if($account_id != '')
			{
				$pResults = $domain->get_domain_data_list('account',$account_id);
				foreach($pResults as $pRow)
				{
					foreach($fields as $fieldname)
					{
						$domainsArray[$i][$fieldname] = $pRow[$fieldname];
					}
					$i++;
				}
			}
		break;
		
		case 'layout':
			$layout_id =  isset($_REQUEST['layout_id'])?$_REQUEST['layout_id']:'';
			if($layout_id != '')
			{
				$pResults = $domain->get_domain_data_list('layout',$layout_id);
				foreach($pResults as $pRow)
				{
					foreach($fields as $fieldname)
					{
						$domainsArray[$i][$fieldname] = $pRow[$fieldname];
					}
					$i++;
				}
			}
		break;
		
		case 'domains':
			$list = isset($_REQUEST['domains'])?$_REQUEST['domains']:'';
			if($list != '')
			{
				$domains = $domain->extractTextarea($list);
				$pResults = $domain->get_domain_data_list('domain',$domains);
				foreach($pResults as $pRow)
				{
					foreach($fields as $fieldname)
					{
						$domainsArray[$i][$fieldname] = $pRow[$fieldname];
					}
					$i++;
				}
			}
		break;
	}
	
	$output = implode(',',$fields);;
	$output .= "\n";
	
	foreach($domainsArray as $domainDetail)
	{
		$output .= implode(',',$domainDetail);
		$output .= "\n";
	}
	
	
	$outputfilename = "domain_settings_".date("d-m-Y")."_".time().".csv";
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"$outputfilename\"");
	
	echo $output;
}

function trim_value(&$value) 
{ 
    $value = trim($value); 
}
?>