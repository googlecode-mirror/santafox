<?php
//Проверим, если нет файла ini.php   то необходимо запустить инсталятор
if (!file_exists("ini.php"))
{
    header("Location: http://".$_SERVER['HTTP_HOST'].'/sinstall/index.php');
    die;
}
require_once ("ini.php"); // Файл с настройками

if (defined("SHOW_INT_ERRORE_MESSAGE") && SHOW_INT_ERRORE_MESSAGE)
    error_reporting(E_ALL);
else
    error_reporting(0);

require_once ("include/kernel.class.php"); //Ядро
require_once ("include/pub_interface.class.php");
require_once ("include/frontoffice_manager.class.php"); //управление фронт офисом
require_once ("admin/manager_modules.class.php"); //Менеджер управления модулями
require_once ("admin/manager_users.class.php"); //Менеджер управления модулями
require_once ("admin/manager_stat.class.php");

$kernel = new kernel(PREFIX);
//Если необходимо то редирект на строку с WWW
if (defined("REDIR_WWW") && REDIR_WWW && !preg_match("/^www\\./i", $_SERVER['HTTP_HOST']))
    $kernel->priv_redirect_301("http://www.".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

if (isset($_GET['_openstat']) && strpos($_GET['_openstat'],";")===false)
{
    $ostat=@base64_decode($_GET['_openstat']);
    if ($ostat)
    {
        $odata=explode(";",$ostat,4);
        if (count($odata)==4 && $odata[0]=='direct.yandex.ru')
        {
            $ruri = $_SERVER["REQUEST_URI"];
            $ruri=preg_replace('~_openstat=([a-z0-9]+)~i','',$ruri);
            $ruri=rtrim($ruri,"&");
            $ruri=rtrim($ruri,"?");
            if (strpos($ruri,"?")===false)
                $ruri.="?";
            else
                $ruri.="&";
            $ruri.="utm_content=".$odata[3];
            $ruri.='&utm_medium=cpc&utm_source=yandex&utm_campaign='.$odata[1];
            header("Location: ".$ruri);
            die();
        }
    }
}

$expiry = 60*24*7;
ini_set('session.gc_maxlifetime', $expiry);
session_start();
setcookie(session_name(), session_id(), time()+$expiry*60, "/");

$front = new frontoffice_manager();
$front->start();