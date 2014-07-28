<?php
/*
logout
*/
$pageCat = 'Logout';
require_once('config.php');
$user = new User();

$user->logout('index.php');
?>