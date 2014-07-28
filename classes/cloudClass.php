<?php
/**
 * Cloud - For sending commands down to the slaves in the cloud
 * Author: Quy Tonthat 20110127
 * 
 */
class Cloud
{
	// No. We can't do private const in php :-(
	const cloudcmd = 'sudo -H -u clouder cloudmaster ';
	function createNewSite($hostIP, $acctDomain, $acctPass)
	{
		if ((APPLICATION_ENVIRONMENT == 'TESTLOCAL') || (APPLICATION_ENVIRONMENT == 'TESTING')) 
			return 'testing';
		// TODO: Use escapeshellcmd() here
		$username = trim(shell_exec(self::cloudcmd .
			"create_website_account \"$hostIP\" \"$acctDomain\" \"$acctPass\""));
		if ($username == FALSE) // empty string
			return FALSE;

		$username = trim($username);
		$dbname = $username . '_' . $db_postfix;
		$dbusername = $dbname;
		shell_exec(self::cloudcmd .
			"setup_sx2 \"$hostIP\" \"$acctDomain\" \"$username\"");

		return $username;
	}


	function terminateSite($host_ip, $domain, $keepDns=0)
	{
		if ((APPLICATION_ENVIRONMENT == 'TESTLOCAL') || (APPLICATION_ENVIRONMENT == 'TESTING')) 
			return true;
		$keepstr = $keepDns ? "yes" : "no";
		$result = trim(shell_exec(self::cloudcmd .
			"remove_website_account \"$host_ip\" \"$domain\" \"$keepstr\""));
		if ($result != 'OK')
			return FALSE;
		$result = trim(shell_exec(self::cloudcmd .
			"terminate_sx2_domain \"$host_ip\" \"$domain\""));
		if ($result != 'OK')
			return FALSE;
		return TRUE;
	}

	function terminateDNS($domain)
	{
		if ((APPLICATION_ENVIRONMENT == 'TESTLOCAL') || (APPLICATION_ENVIRONMENT == 'TESTING')) 
			return true;
		$result = trim(shell_exec(self::cloudcmd .
			"remove_dns \"$domain\""));
		if ($result != 'OK')
			return FALSE;
		return TRUE;
	}

}
?>
