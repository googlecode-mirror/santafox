<?php

clearstatcache ();
if (file_exists("../ini.php"))
	die;


// Вызывается только для инсталяции CMS при ручном создании ini.php
chdir('..');
include ("ini.php"); // Файл с настройками

if (SHOW_INT_ERRORE_MESSAGE)
    error_reporting(E_ALL);
else
    error_reporting(0);


include ("include/kernel.class.php"); //Ядро
include ("include/pub_interface.class.php");
include ("include/mysql_table.class.php"); //Ядро
include ("admin/manager_modules.class.php"); //Менеджер управления модулями
include ("admin/manager_users.class.php"); //Менеджер управления модулями
include ("admin/manager_stat.class.php");

ini_set('url_rewriter.tags', 'none');
session_cache_expire(60*60*24*7);
session_start();

// Сто дней хранить куки
$expiry = 60*60*24*100;
setcookie(session_name(), session_id(), time()+$expiry, "/");
$kernel = new kernel(PREFIX);
$kernel->runSQL("SET NAMES utf8");

$m_table = new mysql_table(PREFIX, $kernel);
$m_table->install();


//Выводим сообщение о том всё заврешено
$url_admin = 'http://'.$_SERVER['HTTP_HOST'].'/admin/';
echo '<p class="header_box">Всё проинсталированно.</p>';
echo '<p>Вы можете перейти в интерфейс администратора, пройдя по ссылке <a href="'.$url_admin.'">'.$url_admin.'</a></p>';
echo '<p>Для авторизации в административном интерфейсе используйте указанный вами логин и пароль</p>';
echo '<p><b>Не забудьте обязательно стереть папку sinstall с вашего сайта.</b></p>';
?>