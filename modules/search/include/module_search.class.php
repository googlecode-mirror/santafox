<?php
/*

Модуль поиска.

    Поддержка языков сообщений сделана следующим образом:
в каждом языке создаётся своя страница поиска. У каждой из этих страниц поиска, естественно,
есть какой-то идентификатор.
   Так вот, модуль поиска смотрит, нет ли папки с таким
   идентификатором в папке user_templates/search_templates/, т.е. например, если
   текущая страница etalon.ap/search-eng.html, то модуль ищет шаблоны
   в папке user_templates/search_templates/search-eng/
   Если не находит, тогда ищет в user_templates/search_templates/  (дефолтовые значения)

   Ну и в полях action форм поиска должны стоять соответствующие значения, т.е. на всех английских
   страницах должны стоять <form action="search-eng.html"

*/


require_once("modules/module_search/indexator.class.php");
require_once("modules/module_search/searcher.class.php");

require_once("modules/module_search/urlparser.class.php");
require_once("modules/module_search/webcontentparser.class.php");
require_once("modules/module_search/htmlparser.class.php");


require_once("modules/module_search/searchdb.class.php");
if (!class_exists(ApTemplate))
	require_once("modules/module_search/aptemplate.class.php");

require_once("modules/module_search/lingua_stem_ru.class.php");

require_once("modules/module_search/pdfparser/pdfobject.class.php");
require_once("modules/module_search/pdfparser/type1encoding/win-1251.inc.php");
require_once("modules/module_search/pdfparser/dictionaryparser.class.php");
require_once("modules/module_search/pdfparser/kvadrpdfobject.class.php");
require_once("modules/module_search/pdfparser/pdfparser.class.php");
require_once("modules/module_search/pdfparser/spacepdfobject.class.php");
require_once("modules/module_search/pdfparser/ugolpdfobject.class.php");



	class module_search
	{
		var $section_id;
		var $indexator;

		var $search_prefix;

		/**
		* @return module_content
		* @param $section_id String
		* @desc Конструктор
 		*/
		function module_search($section_id)
		{
			global $kernel;
			$this->section_id = $section_id;

			$this->search_prefix = PREFIX."search";
			$this->indexator 	= new Indexator($this->search_prefix);

		}

		/**
		* @return Array
		* @param String text
		* @param String number
		* @desc Возвращает массив со ссылкой для администрирования данного модуля и иконкой
		  text - текст на ссылку
		  number - номер контента на странице
 		*/
		function backoffice_link()
		{
			$link = '<a href="/?action=backoffice_content&backoffice_module=module_search&section_id='.$this->section_id.'">Индексирование</a>';
			$icon = "modules/module_content/images/edit.gif";
			return Array('icon' => $icon, 'link' => $link);
		}


		/**
		* @return unknown
		* @desc Форма редактирования контента
		*/
		function edit_page()
		{
			$html = "";
			if (!isset($_POST['module_action']))
				return $this->backoffice_get_method();


			switch ($_POST['module_action'])
			{
				case 'install':
					$this->install();
					break;
				case 'index':
					$this->index();
					break;
			}

		}


		function backoffice_get_method()
		{
			$html = "
			<form method=POST>
				<input type=hidden name=action 				value=backoffice_content>
				<input type=hidden name=backoffice_module 	value=module_search>
				<input type=hidden name=section_id 			value=section_id>

				<input type=hidden name=module_action		value=\"index\">

				<input type=submit value=\"Переиндексировать сайт\">
			</form>";
			return $html;

		}

		function index()
		{
			/* @var $indexator Indexator */
			$this->indexator->index_site('http://'.$_SERVER['HTTP_HOST']."/");
			print "<a href=\"/?action=backoffice_content&section_id=$this->section_id\">Продолжить</a><br>";
			die;

		}

		function install()
		{
			/* @var $indexator Indexator */
			$this->indexator->install();
			global $kernel;
			$kernel->redirect_refresh("/?action=backoffice_content&section_id=$this->section_id");
		}

		function installed()
		{
			/* @var $indexator Indexator */
			return $this->indexator->is_installed();
		}

		function get_install_button()
		{
			$html = "
			<form method=POST>
				<input type=hidden name=action 				value=backoffice_content>
				<input type=hidden name=backoffice_module 	value=module_search>
				<input type=hidden name=section_id 			value=section_id>

				<input type=hidden name=module_action		value=install>

				<input type=submit value=инсталлировать>
			</form>";
			return $html;
		}


		function advanced_search_page()
		{
			$html = file_get_contents($this->get_abs_template_name("advanced_search.html"));
			return $html;
		}


		function get_search_parameters()
		{
			$parameters =
			array(
			'operation' => 'or',
			'results_per_page' => 10,
			'doc_format'	=> 'any'
			);

			if (isset($_GET['operation']) && $_GET['operation'] == 'and')
				$parameters['operation'] = 'and';

			if (isset($_GET['results_per_page']))
			{
				$results_per_page = intval($_GET['results_per_page']);
				if ($results_per_page > 0 && $results_per_page <= 100)
					$parameters['results_per_page'] = $results_per_page;
			}

			if (isset($_GET['doc_format']))
				$parameters['doc_format'] = $_GET['doc_format'];
			return $parameters;
		}



		function frontoffice()
		{
			$search_text = isset($_GET['search']) ? stripslashes($_GET['search']) : "";

			if (isset($_GET['mode']) && $_GET['mode'] == 'advanced')
			{
				$html = $this->advanced_search_page();
				$html = preg_replace("'%%search_text%%'i", $search_text, $html);
				return $html;
			}

			$parameters = $this->get_search_parameters();

			$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;


			/*************** Собственно поиск ******************/
			/* @var $searcher Searcher */
			$searcher = new Searcher($this->search_prefix);

			$searcher->set_results_per_page	($parameters['results_per_page']);
			$searcher->set_doc_format		($parameters['doc_format']);
			$searcher->set_operation		($parameters['operation']);

			$results = $searcher->search($search_text, $page);
			$number_of_pages = $searcher->get_number_of_pages();
			/*************************************************/



			if (empty($results))
				$html = file_get_contents($this->get_abs_template_name("no_results.html"));
			else
				$html = file_get_contents($this->get_abs_template_name("search.html"));


			$search_form = file_get_contents($this->get_abs_template_name("search_form.html"));

			$ap_template = new ApTemplate($this->get_abs_template_name("search_results.html"));
			$ap_template->parse();

			$results_html = $ap_template->get_part("begin");

			$num = 0;
			$result_parts = array();
			$result_template = $ap_template->get_part("result");

			foreach ($results as $result)
			{
				$num++;
				$url = $result['url'];
				$result_html = $result_template;
				$result_html = preg_replace("'%%link%%'i", $url, $result_html);
				$result_html = preg_replace("'%%linktext%%'i", $url, $result_html);
				$result_html = preg_replace("'%%title%%'i", $result['title'], $result_html);
				$result_html = preg_replace("'%%snipped%%'i", $result['snipped'], $result_html);
				$result_html = preg_replace("'%%num%%'i", $result['num'], $result_html);
				$result_parts[] = $result_html;
			}
			$results_html .= join($ap_template->get_part('delimiter'), $result_parts);

			$results_html .= $ap_template->get_part("end");


			$advanced_search_link = "?mode=advanced&search=".urlencode($search_text);

			$html = preg_replace("'%%search_results%%'i", $results_html, $html);
			$html = preg_replace("'%%search_form%%'i", $search_form, $html);
			$html = preg_replace("'%%advanced_search_link%%'i", $advanced_search_link, $html);

			$html = preg_replace("'%%search_text%%'i", $search_text, $html);
			$pages = $this->get_page_numbers($number_of_pages, $page, $search_text);
			$html = preg_replace("'%%pages%%'i", $pages, $html);

			return $html;
		}




		function get_page_numbers($number_of_pages, $page, $search_text)
		{
			$parameters = $this->get_search_parameters();
			if ($number_of_pages == 1)
				return "";
			$ap_template = new ApTemplate($this->get_abs_template_name("pages.html"));
			$ap_template->parse();

			$html = $ap_template->get_part('begin');

			$active_page = $ap_template->get_part("activepage");
			$passive_page = $ap_template->get_part("page");
			$parts = array();

			$url_addition = "operation=$parameters[operation]&results_per_page=$parameters[results_per_page]&doc_format=$parameters[doc_format]";
			for ($i=1; $i<=$number_of_pages; $i++)
			{
				$url = "?section=$this->section_id&search=".urlencode($search_text)."&p=$i&$url_addition";
				if ($i==$page)
					$page_html = $active_page;
				else
					$page_html = $passive_page;

				$page_html = preg_replace("'%%link%%'i", $url, $page_html);
				$page_html = preg_replace("'%%num%%'i", $i, $page_html);
				$parts[] = $page_html;
			}
			$html .= join($ap_template->get_part('delimiter'), $parts);
			$html .= $ap_template->get_part('end');
			return $html;
		}


		function get_abs_template_name($template_name)
		{
			$dir = "user_templates/search_templates";
			$sect = $this->section_id;
			$sect_file_name = "$dir/$sect/$template_name";
			if (file_exists($sect_file_name))
				return $sect_file_name;
			else
				return "$dir/$template_name";
		}


}

?>