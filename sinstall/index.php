<?php
clearstatcache();
if (file_exists("../ini.php"))
	die;

require('install_ftp.class.php');
require('install_modules.class.php');

ini_set('url_rewriter.tags', 'none');
session_start();
$expiry = 60*60*24*1;
setcookie(session_name(), session_id(), time()+$expiry, "/");

$install = new install_ftp();
if ((isset($_GET['install'])) && $_GET['install'] == 'start')
{
	set_time_limit(0);
	$install->install();
	die;
}
header("Content-Type: text/html; charset=utf-8");
print $install->start();