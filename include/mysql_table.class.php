<?php
/**
 * Описывает инсталируемые CMS mySQL таблицы
 *
 * Содрежит SQL запросы для создания необходимых CMS
 * таблиц. По мимо этого, содержит список значений по умолчанию
 * которые прописываются в таблицы при инсталяции
 *
 * @name dhtml_data
 * @package Install
 * @copyright ArtProm (с) 2001-2007
 * @version 1.0
 */
class mysql_table
{
	/**
	 * Массив SQL зпросов, выполняемыйх при инсталяции CMS
	 *
	 * @access private
	 * @var array
	 */
	var $sql = array();

	/**
	 * Префикс создаваемых таблиц
	 *
	 * @access private
	 * @var string
	 */
	var $prefix = "";

	/**
	 * Переменная куда передаётся ядро
	 *
	 * @access private
	 * @var object
	 */
	var $kernel;

	/**
	 * Конструктор. Опиcывает создаваемые таблицы и значения, прописываемые при инстляции
	 *
	 * @param string $prefix_base
	 * @return void
	 */

	function mysql_table($prefix_base, $in_core)
    {

    	$this->prefix = $prefix_base;
        $this->kernel = $in_core;

    }
   //=====================   Слежение за пользователем бэкофиса ====================================



    /**
     * Подготавливает БД к инсталяции таблиц
     *
     * Необходимо для совестимости с различно настроеными БД
     * @param array $comands
     * @access private
     * @return string
     */
    function prepare_table($comands)
    {
        $str = $comands['CREATE'];
        $str .= " ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
        if (!isset($comands['COMMENT']))
            $str .= " COMMENT = 'Это моя тестовая таблица'";

        return $str;
    }

	/**
	 * Создает необходимые таблицы в базе данных

	 * @access public
	 * @return void
	 */
	function install($is_etalon_install)
    {
        $kernel = $this->kernel;
        $sql_files_dir = $kernel->pub_site_root_get()."/sinstall/sql";
        $log_file = $kernel->pub_site_root_get()."/sinstall/_install.log";
        //print "log: ".$log_file."<br>\n";
        //error_reporting(E_ALL);
        $sql_files = array_keys($kernel->pub_files_list_get($sql_files_dir));
        foreach($sql_files as $sql_file)
        {
            $kernel->pub_add_line2file($log_file, "processing ".$sql_file);
            $file_content = file_get_contents($sql_file);
	    	$queries = explode(";\n", $file_content);
            $kernel->pub_add_line2file($log_file, "queries: ".count($queries));
            foreach ($queries as $query)
            {
                $query = trim($query);
                if (empty($query))
                    continue;
                $query = str_replace("`%PREFIX%","`".$this->prefix, $query);
                //$kernel->pub_add_line2file($log_file, "executing query:\n ".$query); //
                $kernel->runSQL($query);
                $err = mysql_error();
                if (!empty($err))
                {
                    $msg = "MySQL ERROR: ".$err.", query: ".$query;
                    $kernel->pub_add_line2file($log_file, $msg);
                    print $msg."<br>\n";
                }
            }
        }

        if ($is_etalon_install)
            $last_sql_file = "after_etalon_install.sql";
        else
            $last_sql_file = "after_clean_install.sql";

        $kernel->pub_add_line2file($log_file, "processing ".$last_sql_file);

        $file_content = file_get_contents($kernel->pub_site_root_get()."/sinstall/".$last_sql_file);
        $queries = explode(";\n", $file_content);

        $kernel->pub_add_line2file($log_file, "queries: ".count($queries));
        foreach ($queries as $query)
        {
            $query = trim($query);
            if (empty($query))
                continue;
            //$query = substr($query, 0, -1);
            $query = str_replace("`%PREFIX%","`".$this->prefix, $query);
            $kernel->pub_add_line2file($log_file, "executing query:\n ".$query);
            $kernel->runSQL($query);
            $err = mysql_error();
            if (!empty($err))
            {
                $msg = "MySQL ERROR: ".$err.", query: ".$query;
                $kernel->pub_add_line2file($log_file, $msg);
                print $msg."<br>\n";
            }
        }

    }


	/**
	 * Проверяет наличие в базе данных укзанной языковой переменной
	 *
	 * @param string $lang Двух буквенный код языка.
	 * @param String $elem Языковая переменная
	 * @access private
	 * @return bool
	 */
	function lang_exist($lang, $elem)
	{
    	$query = "SELECT * FROM ".$this->prefix."_all_lang
    			  WHERE (lang = '".$lang."') and (element ='".$elem."')
    			 ";

    	$result = $this->kernel->runSQL($query);

        if (mysql_num_rows($result) > 0)
        	return true;

        return false;

	}

    /**
    * Считывает языковые файлы и по их содержимому заполняет языковую таблицу
    *
    * @param  string $path_in_lang Путь к языковам файлам
    * @access public
    * @return void
    */
	function add_langauge($path_in_lang)
    {

		if (!file_exists($path_in_lang))
            return;

        $lang_isset = $this->kernel->priv_languages_get(false);
        foreach ($lang_isset as $lang_code => $lang_name)
        {
            $file_name = $path_in_lang.'/'.$lang_code.'.php';
            if (file_exists($file_name))
            {
                include $file_name;
                foreach ($il as $key => $val)
                	if (!$this->lang_exist($type_langauge, $key))
                    	$this->add_data_langauge($type_langauge, $key, $val);
                $il = array();
            }
        }
    }

    /**
    * Считывает языковые файлы и удаляет их из таблицы
    *
    * Вызывается при удалении базавого модуля с тем что бы очистить
    * языковую таблицу от лишней информации
    * @param  string $path_in_lang Путь к языковам файлам
    * @access public
    * @return void
    */
	function del_langauge($path_in_lang)
    {

		if (!file_exists($path_in_lang))
            return ;

        $dir = dir($path_in_lang);
        while ($file = $dir->read())
        {
            if (is_file($path_in_lang.'/'.$file))
            {
            	$il = array();
                include $path_in_lang.'/'.$file;

                //Удалим все записи
                if (!empty($il))
                {
					$query = "DELETE FROM ".$this->prefix."_all_lang
    						  WHERE element IN ('".join("','",array_keys($il))."')";

					$this->kernel->runSQL($query);
                }
            }
        }
    }

    /**
    * Производит непосредственно запись значений языка в таблицу
    *
    * @param string $lang Двух буквенный код языка
    * @param string $id id языковой переменной
    * @param string $val Представление языковой переменной
    * @access private
    * @return void
    */
	function add_data_langauge($lang, $id, $val)
    {

    	//Если такой такой id, такой языковой переменной существет, то он будет
    	//заменён
		$query = "REPLACE INTO ".$this->prefix."_all_lang VALUES
				   (
    	           		'".$lang."',
        	            '".$id."',
            	        NULL,
                	    '".mysql_real_escape_string($val)."'
                   )
	             ";

		$this->kernel->runSQL($query);
    }

    /**
     * Очищает всю языковую таблицу
     *
     * @param array $nodel значения, которые не нужно удалять
     * @access private
     * @return void
     */
    function lang_all_clear($nodel)
    {

		$query = "DELETE FROM ".$this->prefix."_all_lang";
		if (count($nodel) > 0)
		  $query .= " WHERE element NOT IN ('".join("','",array_values($nodel))."')";


		$this->kernel->runSQL($query);
    }

}

?>