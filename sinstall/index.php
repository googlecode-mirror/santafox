<?php

clearstatcache ();
if (file_exists("../ini.php"))
	die;

//Проводит инсталяцию CMS через FTP соединение

//if ($_SERVER['SERVER_PORT'] != 443) // если не https, то перекинуть на https
//{
//    header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//    die;
//}
include('install_ftp.class.php');
include('install_modules.class.php');

ini_set('url_rewriter.tags', 'none');
session_start();
// Сто дней хранить куки
$expiry = 60*60*24*1;
setcookie(session_name(), session_id(), time()+$expiry, "/");

$install = new install_ftp();
if ((isset($_GET['install'])) && ($_GET['install'] == 'start'))
{
	set_time_limit(0);
	$install->install();
	die;
}
header("Content-Type: text/html; charset=utf-8");
$html = $install->start();
print $html;