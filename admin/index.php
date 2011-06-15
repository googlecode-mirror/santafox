<?php
    chdir('../');
    include ("ini.php"); // Файл с настройками
    include ("include/kernel.class.php"); //Ядро
    include ("include/install_modules.class.php");
    include ("include/mysql_table.class.php");
    include ("admin/data_tree.class.php");
    include ("include/edit_content.class.php");
    include ("admin/manager_interface.class.php");    //Управление основным интефейсом
    include ("admin/top_menu.class.php");
    include ("admin/manager_users.class.php");
    include ("admin/manager_modules.class.php");
    include ("admin/manager_structue.class.php");
    include ("admin/manager_properties_page.class.php");
    include ("admin/manager_global_properties.class.php");
    include ("admin/manager_stat.class.php");
    include ("admin/parser_properties.class.php");
    include ("admin/backup.class.php");
    include ("admin/manager_chmod.class.php");
    include ("include/pub_interface.class.php");

    //В старых ini файлах эта константа может быть не определена сначала.
    if (!defined('SSL_CONNECTION'))
        DEFINE ("SSL_CONNECTION", false);

    $kernel = new kernel(PREFIX);

    if (SSL_CONNECTION && (!isset($_SERVER['HTTPS'])))
        $kernel->pub_redirect_refresh_reload($_SERVER['REQUEST_URI'], SSL_CONNECTION);

    if (SHOW_INT_ERRORE_MESSAGE)
        error_reporting(E_ALL);
    else
        error_reporting(0);

    // Не дописываем сессию в урлы и формы. Всё в куках.
    // Храним сессию неделю
    session_cache_expire(60*60*24*7);
    session_start();
    // Сто дней хранить куки
    $expiry = 60*60*24*100;
    setcookie(session_name(), session_id(), time()+$expiry, "/");

    $main_interface = new manager_interface();
    $main_interface->start();
?>