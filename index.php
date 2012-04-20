<?php
//Проверим, если нет файла ini.php   то необходимо запустить инсталятор
if (!file_exists("ini.php"))
{
    header("Location: http://".$_SERVER['HTTP_HOST'].'/sinstall/index.php');
    die;
}
include ("ini.php"); // Файл с настройками

if (SHOW_INT_ERRORE_MESSAGE)
    error_reporting(E_ALL);
else
    error_reporting(0);


// Выдали заголовки
//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
//header("Cache-Control: no-store, no-cache, must-revalidate");
//header("Cache-Control: post-check=0, pre-check=0", false);
//header("Pragma: no-cache");


include ("include/kernel.class.php"); //Ядро
include ("include/pub_interface.class.php");
include ("include/frontoffice_manager.class.php"); //управление фронт офисом
include ("admin/manager_modules.class.php"); //Менеджер управления модулями
include ("admin/manager_users.class.php"); //Менеджер управления модулями
include ("admin/manager_stat.class.php");

// Не дописываем сессию в урлы и формы. Всё в куках.
ini_set('url_rewriter.tags', 'none');

// Храним сессию неделю
session_cache_expire(60*60*24*7);

session_start();

// Сто дней хранить куки
$expiry = 60*60*24*100;
setcookie(session_name(), session_id(), time()+$expiry, "/");
$kernel = new kernel(PREFIX);

//Если необходимо то редирект на строку с WWW
if ((REDIR_WWW == true) && (!preg_match("/^www\\./", $_SERVER['HTTP_HOST'])))
    $kernel->priv_redirect_301("http://www.".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);


$front = new frontoffice_manager();
$front->start();

?>

