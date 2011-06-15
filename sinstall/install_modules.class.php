<?php
class install_modules
{
	var $modul_name				= '';		//Имя базвоого модуля, используемое при инсталяции
	var $modul_id				= '';		//id базвого модуля
	var $type_admin_interfase 	= 0; 		//Тип администртивного интерфейса:
											//	0 - модули не имеют административного интерфейса (АИ)
											//	1 - модули имеют один АИ, на базовый модуль
											//	2 - каждый экземпляр модуля имеет свою админку

	var $modul_properties 		= array();	//Массив параметров модуля
	var $page_properties		= array(); 	//Массив параметров, которые модуль добовляет к каждой странице
	var $users_properties_one	= array();  //Массив дополнительных полей, которые будут прописаны к БАЗОВОМУ модулю
	var $users_properties_multi	= array();  //Массив дополнительных полей, которые будут прописаны к КАЖДОМУ дочернему модулю
	var $admin_acces_label 		= array();	//Массив признаков доступа для администраторов сайта
	var $admin_public_metods 	= array(); 	//Массив методов, из которых строятся макросы, с параметрами
	var $parametrs_def 			= array(); 	//Массив значений параметров модуля, выставляемых при исталяции

	var $module_copy = array(); 		//Массив, показывающий сколько нужно сделать экземпляров модуля при


	//Конструктор класса
	function install_modules()
	{
	}


	/**
     * Вызывается при инстялции базвоового модуля
     */
	function install()
	{
		global $kernel;

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
		global $kernel;

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

	}



	function add_public_metod($name, $caption)
	{
		$this->admin_public_metods[trim($name)]['id'] = trim($name);
		$this->admin_public_metods[trim($name)]['name'] = trim($caption);
	}

	function add_public_metod_parametrs($name, $param)
	{
		$this->admin_public_metods[trim($name)]['parametr'][] = $param->get_array();
	}

	//************************************************************************
	/**
     * Возрашает доступные методы для построения макрасов
     *
     * @return Array
     */
	function get_public_metod()
	{
		return $this->admin_public_metods;
	}


	/**
	 * Добавляет новый "разрез" контроля прав групп администраторов сайта
	 *
	 * @param string $name ID права
	 * @param string $caption Представления имени для администратора
	 */
	function add_admin_acces_label($name, $caption)
	{
		$this->admin_acces_label[trim($name)] = trim($caption);
	}

	//************************************************************************
    /**
     * Возврашает сформированный массив id уровней доступа для групп администраторов сайта
	 *
     * @return Array
     */

    function get_admin_acces_label()
    {
		return $this->admin_acces_label;
    }

    /**
     * Устанавливает имя модуля базавого модуля с
     * которым он будет инсталирован в системе
     *
     * @param string $name
     */
    function set_name($name)
    {
    	$this->modul_name = trim($name);
    }

    /**
     * Возвращает имя базового модуля
     *
     * @return string
     */
    function get_name()
    {
    	return $this->modul_name;
    }

    /**
     * Устанавливает ID базавого модуля
     *
     * @param string $id
     */
    function set_id_modul($id)
    {
    	$this->modul_id = trim($id);
    }

    /**
     * Возвращает ID базового модуля
     *
     * @return string
     */
    function get_id_modul()
    {
    	return $this->modul_id;
    }

    /**
     * Устанавливает тип административного интерфейса для модуля
     *  0 - модули не имеют административного интерфейса (АИ)
	 *  1 - модули имеют один АИ, на базовый модуль
	 *  2 - каждый экземпляр модуля имеет свою админку
	 *
     * @param integer $type_in Тип интерфейса
     */
    function set_admin_interface($type_in)
    {
  		$this->type_admin_interfase  = intval($type_in);
    }

    /**
     * Возвращает тип административного интерфейса для модуля
     *  0 - модули не имеют административного интерфейса (АИ)
	 *  1 - модули имеют один АИ, на базовый модуль
	 *  2 - каждый экземпляр модуля имеет свою админку
     * @return integer
     */
    function get_admin_interface()
    {
    	return $this->type_admin_interfase;
    }

    /**
     * Добавляет новый параметр модуля
     *
     * @param object $param Объект одного из "типов propertie_*"
     */
    function add_modul_properties($param)
    {
    	if (is_object($param))
    	{
    		$arr = $param->get_array();
  			$this->modul_properties[] = $arr;
  			if (!empty($arr['default']))
  				$this->parametrs_def[$arr['name']] = $arr['default'];
    	}

    }

    /**
     * Возврашает массив параметров модуля для проведения интсляции
     *
     * @return Array
     */
	function get_modul_properties()
	{
		return $this->modul_properties;
	}

    /**
     * Добавляет новый параметр, прописываемый модулем к каждой странице сайта
     *
     * @param object $param Объект одного из "типов propertie_*"
     */
    function add_page_properties($param)
    {
    	if (is_object($param))
  			$this->page_properties[] = $param->get_array();
    }

    /**
     * Возврашает массив параметров модуля для проведения интсляции
     *
     * @return Array
     */
	function get_page_properties()
	{
		return $this->page_properties;
	}


	/**
	 * Добовляет новое свойтсво к пользовтаелю сайта. Пока поддерживаются свойства только строкового значения
	 *
	 * @param object $param Объект типа  propertie_string
	 * @param boolean $multi Если TRUE - то этот параметр будет прописываться каждым экземпляром дочернего модуля, в противном случае только базовым модулем
	 * @param boolean $admin Если TRUE - то значит доступ к этому парметру пользователя должен иметь только администратор, в противном случае и сам пользователь имеет доступ к этому свойству
	 */
	function add_user_properties($param, $multi = false, $admin = false)
	{
		$arr = $param->get_array();
		$arr['admin'] = $admin;

		if ($multi)
			$this->users_properties_multi[] = $arr;
		else
			$this->users_properties_one[] = $arr;

	}

    function get_users_properties_one()
    {
		return $this->users_properties_one;
    }


    function get_users_properties_more()
    {
		return $this->feilds_users_more;
    }




    //РАЗОБРАТСЯ с ЭТИМИ ФУНКЦИЯМИ


    //************************************************************************
	/**
     * Возвращает массив установленных параметров модуля (только название и значение)
     *
     * @return  Array
     */
	function return_default_properties()
	{
		return $this->parametrs_def;
	}


	function get_module_copy()
	{
		return $this->module_copy;

	}



}
?>