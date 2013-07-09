<?php

class feedback2_install extends install_modules
{
    /**
     * Инсталяция базового модуля
     * @param string $id_module Идентификатор создаваемого базового модуля
     * @param boolean $reinstall переинсталяция?
     */
    function install($id_module, $reinstall = false)
    {
        global $kernel;
        $q="CREATE TABLE `".$kernel->pub_prefix_get()."_feedback2_fields` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `moduleid` varchar(255) CHARACTER SET utf8 NOT NULL,
              `ftype` enum('select','string','textarea','file','checkbox') CHARACTER SET utf8 NOT NULL,
              `name` varchar(255) CHARACTER SET utf8 NOT NULL,
              `order` int(5) unsigned NOT NULL,
              `required` tinyint(1) unsigned NOT NULL DEFAULT '0',
              `params` text CHARACTER SET utf8 DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `moduleid_order` (`moduleid`,`order`)
            ) ENGINE=MyISAM";
        $kernel->runSQL($q);
    }

    /**
     * Деинсталяция базового модуля
     * @param string $id_module Идентификатор удаляемого базового модуля
     */
    function uninstall($id_module)
    {
        global $kernel;
        $q="DROP TABLE `".$kernel->pub_prefix_get()."_feedback2_fields`";
        $kernel->runSQL($q);
    }

    /**
     * Инсталяция дочернего модуля
     * @param string $id_module Идентификатор вновь создаваемого дочернего модуля
     * @param boolean $reinstall переинсталяция?
     */
    function install_children($id_module, $reinstall = false)
    {

    }

    /**
     * Деинсталяция дочернего модуля
     * @param string $id_module ID удаляемого дочернего модуля
     */
    function uninstall_children($id_module)
    {

    }
}


$install = new feedback2_install();
$install->set_name('[#feedback2_modul_base_name#]');
$install->set_id_modul('feedback2');
$install->set_admin_interface(2);


$install->add_public_metod('pub_show_form', '[#feedback2_pub_show_form#]');

$p = new properties_file();
$p->set_id('template');
$p->set_caption('[#common_module_tpl#]');
$p->set_patch('modules/feedback2/templates_user');
$p->set_mask('htm,html');
$p->set_default('modules/feedback2/templates_user/form.html');
$install->add_public_metod_parametrs('pub_show_form',$p);

$p = new properties_string();
$p->set_id('admin_email');
$p->set_caption('[#feedback2_param_email#]');
$p->set_default(isset($_SERVER['SERVER_ADMIN'])?$_SERVER['SERVER_ADMIN']:'');
$install->add_modul_properties($p);

$p = new properties_string();
$p->set_id('email_from');
$p->set_caption('[#feedback2_param_email_from#]');
$p->set_default('noreply@'.$_SERVER['HTTP_HOST']);
$install->add_modul_properties($p);


$p = new properties_checkbox();
$p->set_id('show_captcha');
$p->set_caption('[#feedback2_show_captcha#]');
$p->set_default(1);
$install->add_modul_properties($p);


$p = new properties_file();
$p->set_id('email_tpl');
$p->set_mask('htm,html');
$p->set_caption('[#feedback2_param_email_tpl#]');
$p->set_patch('modules/feedback2/templates_user');
$p->set_default('modules/feedback2/templates_user/email.html');
$install->add_modul_properties($p);



$install->module_copy[0]['name'] = 'feedback2_modul_base_name';