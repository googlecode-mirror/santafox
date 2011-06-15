<?php
/**
 * Вызывается при инсталяции модуля
 * @copyright ArtProm (с) 2001-2005
 * @version 1.0
 */


require_once("include/indexator.class.php");
require_once("include/searcher.class.php");

require_once("include/urlparser.class.php");
require_once("include/webcontentparser.class.php");
require_once("include/htmlparser.class.php");


require_once("include/searchdb.class.php");


//Осонвные параметры модуля
class search_install extends install_modules
{
	/**
     * Вызывается при инстялции базвоового модуля
     */
	function install($id_module)
	{

	}


	/**
     * Методы вызывается при деинтсаляции базового модуля. ID базоовго модуля
     * точно известно и определется самим модулем, но он (ID) так же передается в
     * качестве параметра. Здесь необходимо производить удаление каталогов, файлов и таблиц используемых
     * базовым модулем и создаваемых в install
     * @param string $id_module ID удаляемого базового модуля
     */

	function uninstall($id_module)
	{

	}


	/**
     * Методы вызывается, при инсталяции каждого дочернего модуля, здесь необходимо
     * создавать таблицы каталоги, или файлы используемые дочерним модулем. Уникальность создаваемых
     * объектов обеспечивается с помощью передвавемого ID модуля
     *
     * @param string $id_module ID вновь создоваемого дочернего модуля
     */
	function install_children($id_module)
	{
		global $kernel;
		$full_prefix = PREFIX."_".$id_module;
		$db = new searchdb($full_prefix);
		$db->install();
	}

   /**
    * Методы вызывается, при деинсталяции каждого дочернего модуля, здесь необходимо
    * удалять таблицы, каталоги, или файлы используемые дочерним модулем.
    *
    * @param string $id_module ID удоляемого дочернего модуля
    */
	function uninstall_children($id_module)
	{
		global $kernel;
		$full_prefix = PREFIX."_".$id_module;
		$db = new searchdb($full_prefix);
		$db->uninstall();
	}


}

$install = new search_install();


$install->set_name('[#search_name_modul_base_name#]');
$install->set_id_modul('search');
$install->set_admin_interface(2);

//Параметры модуля, здесь он один
$param = new properties_string();
$param->set_id('user_name');
$param->set_caption('[#search_modul_prop_user_auth#]');
$install->add_modul_properties($param);

$param = new properties_string();
$param->set_id('user_pass');
$param->set_caption('[#search_modul_prop_pass_auth#]');
$install->add_modul_properties($param);

$param = new properties_pagesite();
$param->set_id('page_search');
$param->set_caption('[#search_modul_prop_page_search#]');
$install->add_modul_properties($param);


//Параметры страницы, прописываемые модулем
//$param = new properties_select();
//$param->set_id("visible");
//$param->set_caption("[#module_waysite_label_visible#]");
//$param->set_data(array ("true"=>"[#module_waysite_visible_var1#]","false"=>"[#module_waysite_visible_var2#]"));
//$install->add_page_properties($param);


//Добавим необходимые поля к пользователю сайта
//
//$param = new properties_string();
//$param->set_id("data_b");
//$param->set_caption("Дата рождения");
//$install->add_user_properties($param, false, true);
//
//$param = new properties_string();
//$param->set_id("data_b2");
//$param->set_caption("Дата рождения 2");
//$install->add_user_properties($param, false, false);


//========================================================================================
//Опишем публичные методы со всеми возможными параметрами
//========================================================================================
//Отображает маленькую форму поиска
$install->add_public_metod('pub_show_only_form', '[#search_pub_show_only_form#]');
$property = new properties_file();
$property->set_caption('[#search_pub_show_only_form_template#]');
$property->set_default('modules/search/templates_user/small_form.html');
$property->set_id('template');
$property->set_mask('html,htm');
$property->set_patch('modules/search/templates_user/');
$install->add_public_metod_parametrs('pub_show_only_form', $property);







$install->add_public_metod('pub_show_search_results', '[#search_show_search_results#]');

/**
 * @param string $
 *
 */

$property = new properties_file();
$property->set_caption('[#search_property_template_search#]');
$property->set_default('modules/search/templates_user/search.html');
$property->set_id('template_search');
$property->set_mask('html,htm');
$property->set_patch('modules/search/templates_user/');
$install->add_public_metod_parametrs('pub_show_search_results', $property);

$property = new properties_file();
$property->set_caption('[#search_property_template_no_results#]');
$property->set_default('modules/search/templates_user/no_results.html');
$property->set_id('template_no_results');
$property->set_mask('html,htm');
$property->set_patch('modules/search/templates_user/');
$install->add_public_metod_parametrs('pub_show_search_results', $property);

$property = new properties_file();
$property->set_caption('[#search_property_template_search_form#]');
$property->set_default('modules/search/templates_user/search_form.html');
$property->set_id('template_search_form');
$property->set_mask('html,htm');
$property->set_patch('modules/search/templates_user/');
$install->add_public_metod_parametrs('pub_show_search_results', $property);

$property = new properties_file();
$property->set_caption('[#search_property_template_search_results#]');
$property->set_default('modules/search/templates_user/search_results.html');
$property->set_id('template_search_results');
$property->set_mask('html,htm');
$property->set_patch('modules/search/templates_user/');
$install->add_public_metod_parametrs('pub_show_search_results', $property);

$property = new properties_file();
$property->set_caption('[#search_property_templates_pages#]');
$property->set_default('modules/search/templates_user/pages.html');
$property->set_id('template_pages');
$property->set_mask('html,htm');
$property->set_patch('modules/search/templates_user/');
$install->add_public_metod_parametrs('pub_show_search_results', $property);

$property = new properties_file();
$property->set_caption('[#search_property_templates_advanced_search#]');
$property->set_default('modules/search/templates_user/advanced_search.html');
$property->set_id('template_advanced_search');
$property->set_mask('html,htm');
$property->set_patch('modules/search/templates_user/');
$install->add_public_metod_parametrs('pub_show_search_results', $property);

//$param1 = new properties_pagesite();
//$param1->set_id("id_page_start");
//$param1->set_caption("[#module_mapsite_id_page_start#]");
//$install->add_public_metod_parametrs('pub_show_waysite',$param1);


//Уровни доступа
//$install->add_admin_acces_label('acces_admin','Доступ в административную часть');
//$install->add_admin_acces_label('acces_admin2','Доступ в административную часть 2');


//То, что ставится автоматически при интсляции базового модуля пока оставим так, как есть...
//Теперь можно прописать дочерние модули, которые будут автоматически созданы при
//инсталяции модуля а так же макросы и свойства, каждого из дочерних модулей.
//Свойства модуля
$install->module_copy[0]['name'] = 'search_name_modul_base_name1';
//$install->module_copy[0]['macros'][0]['caption'] = 'Вывести дорогу';
//$install->module_copy[0]['macros'][0]['id_metod'] = 'pub_show_waysite';
//$install->module_copy[0]['macros'][0]['param']['id_page_start'] = 'index'; //у метода нет параметров
//$install->module_copy[0]['properties_in_page']['index']['visible'] = 'true';
//$install->module_copy[0]['properties']['template'] = 'templates/waysite/map.html';
?>
