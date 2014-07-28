<?php
require_once('config.php');
$Profile = new Profile();

$profile_id=isset($_GET['profile_id'])?$_GET['profile_id']:'';
if($profile_id == '')
	echo '';
else
{
	$accounts=$Profile->getAccounts($profile_id);
	foreach ($accounts as $key => $val) {
	echo '|'.$val['account_id'].'#'.$val['account_name'];
}
}
?>