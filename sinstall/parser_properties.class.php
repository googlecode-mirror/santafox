<?php
class parse_properties
{
	var $curent_mudul;			//id модуля, если свойства парсяться для модуля
	var $curent_metod;			//id метода, если свойства парсяться для метода модуля, или для страницы сайта
	var $curent_page;			//id страницы сайта, чьи свойства будем выводить
	var $for_page = false;		//Признак того, что свойства парсяться для страницы сайта
	var $nasledovanie = false;
	var $macros_value = array();

	function parse_properties()
	{

	}

    //*****************************************************************************************************************
    /**
    @return
    @param $id_modul String, $parent String
    @desc Устанавливает модуль, чьи парметры будем вытаскивать
    **/
	function set_modul($id_modul, $parent = "")
	{
		$this->curent_mudul = $id_modul;
		if (!empty($parent))
			$this->nasledovanie = true;

	}

    //*****************************************************************************************************************
    /**
    @return
    @param $id_metod String
    @desc Устанавливает metod, чьи парметры будем вытаскивать
    **/
	function set_metod($id_metod = "")
	{
		$this->curent_metod = $id_metod;
		$this->nasledovanie = false;

	}

	//******************************************************************************************
	//
	/**
	 * Устанавливает признак того, что парситься страница
	 *
	 * @param String $id_page Индентефикатор страницы, чьи свойства надо взять
	 */
	function set_page($id_page)
	{
		$this->for_page = true;
		$this->curent_page = $id_page;
	}

	//*****************************************************************************************************************
    /**
    @return
    @param $value array
    @desc Устанавливает массив значений для метода get_default, в случае если работа идет с макросом
    **/
	function set_value_default($value)
	{
		if (is_array($value))
			$this->macros_value = $value;
	}
    //*****************************************************************************************************************
    /**
    @return
    @param $id_metod String
    @desc Возврашает текущее значение заданного свойства для модуля для метода(макроса) возвращает пустой массив
    **/
	function  get_default($name)
	{
		global $kernel;

		if ((!empty($this->curent_mudul)) && (!$this->for_page))
		{
			//Параметры модуля
			$ret_array = $kernel->get_modul_properties($name, $this->curent_mudul,true);
		}
		elseif ($this->for_page)
		{
			//Параметры страницы (которые добавляет модуль
			$ret_array = $kernel->get_page_properties($this->curent_page,$name,true);
		}
		else
		{
			//Параметры макроса
			$ret_array['isset'] = false;
			$ret_array['value'] = "";

			if (!empty($this->macros_value))
			{
				$ret_array['isset'] = isset($this->macros_value[$name]);
				if ($ret_array['isset'])
				{
					//нужно убрать кавычки, обрамляющие значение
					$temp_str = $this->macros_value[$name];
					if (substr($temp_str,0,1) == '"')
						$temp_str = substr($temp_str,1);

					if (substr($temp_str,-1) == '"')
						$temp_str = substr($temp_str,0,(strlen($temp_str)-1));

					$ret_array['value'] = trim($temp_str);
				}
			}
		}
		return $ret_array;
	}

	//********************************************************************************

	/**
	 * Определяет что нужно вызвать, в зависимости от типа парметра указанного свойсвта
	 *
	 * @param Array $in
	 * @return HTML
	 */
	function create_html($in)
	{
		global $kernel;

		$html = "";
		if (is_array($in))
		{
			if ($this->for_page)
				$in['name'] = trim($this->curent_mudul).'_'.$in['name'];

			//$kernel->debug($in);

			switch ($in['type'])
			{
				case "file":
					$html .= $this->html_file($in);
					break;
				case "select":
					$html .= $this->html_select($in);
					break;
				case "radio":
					$html .= $this->html_radio($in);
					break;
				case "check":
					$html .= $this->html_check($in);
					break;
				case "text":
					$html .= $this->html_text($in);
					break;
				case "data":
					$html .= $this->html_data($in);
					break;
				case "page":
					$html .= $this->html_page($in);
					break;


			}
		}
		return $html;

	}

	//************************************************************************
    /**
    @return void
    @param
    @desc Возвращает значение параметра, после выбора в форме макроса, и его значение
    	  Если этого параметра нет, то возвращается False. Если есть, но пустое значение
    	  то возвращается именно пустое значение, так как методу нужно передать все значения
    **/
	function return_normal_value($value, $dat)
	{
		$ret = '"'.'"';
		$name = $dat['name'];
		if ($dat['type'] == "check")
		{
			if (isset($value[$name]))
				$ret = "true";
			else
				$ret = "false";
		} else
		{
			if (isset($value[$name]))
				//$ret = '"'.trim($value[$name]['value']).'"';
				$ret = trim($value[$name]['value']);
		}
		return $ret;
	}



	//********************************************************************************

	/**
	 * Возвращает кусочек JScript, отвечающий за выключение элементов, если они наследуются
	 *
	 * @param String $name Дополнительное, уникальное имя
	 * @param String $name_disable
	 * @return HTML
	 */
	function return_code_for_disable_element($name, $name_disable = '')
	{
		$html = '<script language="JavaScript">
					function set_nasledovanie_'.$name.'()
					{
						var def_val = document.form_all_properties.flag_nasl_'.$name.'.checked;
						var curent_count = document.all.text_lavel_'.$name.'.length;
				';

		if (!empty($name_disable))
			$html .='document.form_all_properties.'.$name_disable.$name.'.disabled = def_val;';

		$html .='
						document.form_all_properties.nasleduem_'.$name.'.value = def_val;


						if (curent_count == undefined)
							document.all.text_lavel_'.$name.'.disabled = def_val;
				  		else
				  		{
							for (var i = 0; i < curent_count; i++)
								document.all.text_lavel_'.$name.'[i].disabled = def_val;
				  		}
				  	}
				  	</script>
				  ';

		return $html;
	}


	//********************************************************************************
	/**
	 * Создает HTML код для свойства типа "файл"
	 *
	 * @param Array $in
	 * @return HTML
	 */
	function html_file($in)
	{
		global $kernel;

		$html = "";

		// Узнаем текущее значение этого свойства
		$def = $this->get_default($in['name']);

		$select_nasled = "";
		$disabl = "";
		$def_val = "false";
		if ($this->nasledovanie)
		{
			if ((!$def['isset']) || ($def['naslednoe']))
			{
				$select_nasled  = " checked";
				$disabl = " disabled";
				$def_val = "true";
			}
		}


		//$kernel->debug($select_nasled);

		if (isset($in['caption']))
			$html .= '<td align=right class="caption_in_popertes"><label id="text_lavel_'.$in['name'].'"'.$disabl.'>'.$in['caption'].':</label>';

		if ($this->nasledovanie)
		{
			$html .= $this->return_code_for_disable_element($in['name'], 'selec_');
			$html .= '<input '.$select_nasled.' onclick="set_nasledovanie_'.$in['name'].'()" type="checkbox" class="text" id="flag_nasl_'.$in['name'].'">';
			$html .= '<input type="hidden" class="text" id="nasleduem_'.$in['name'].'" name="properties['.$in['name'].'][nasled]" value="'.$def_val.'">';
		}
		$html .= '</td>';

		$html .= '<td><select id="selec_'.$in['name'].'" name="properties['.$in['name'].'][value]" class="text"'.$disabl.'>';

		// Если есть дополнительные параметры обработаем их
		$array_mask = array();
		if (isset($in['mask']))
		{
			$array_mask = explode(",",$in['mask']);
			$array_mask = array_flip($array_mask);
		}

		$html .= '<option value="">[#label_properties_no_select_option#]</option>';
		$d = dir($in['patch']);
		while (false !== ($entry = $d->read())) {
			$link = $in['patch'].'/'.$entry;
			if (is_file($link))
			{
				if (!empty($array_mask))
				{
					$_file = explode(".",$entry);
					if (count($_file) > 1 )
						$_file = $_file[(count($_file)-1)];
					else
						$_file = '';

					if (empty($_file))
						continue;

					if (!isset($array_mask[$_file]))
						continue;
				}

				$select = "";
				if (($def['isset']) && (!empty($def['value'])))
					if ($link == $def['value'])
						$select = " selected";

				$html .= '<option value="'.$link.'" '.$select.'>'.$entry.'</option>';
			}
		}
		$d->close();
		$html .= "</select></td>";

		return $html;

	}


	//********************************************************************************
	/**
	 * Создает HTML код для свойства типа "select"
	 *
	 * @param Array $in
	 * @return HTML
	 */
	function html_select($in)
	{
		global $kernel;

		$html = "";

		// Узнаем текущее значение этого свойства
		$def = $this->get_default($in['name']);
		$select_nasled = "";
		$disabl = "";
		$def_val = "false";
		if ($this->nasledovanie)
		{
			if ((!$def['isset']) || ($def['naslednoe']))
			{
				$select_nasled  = " checked";
				$disabl = " disabled";
				$def_val = "true";
			}
		}

		if (isset($in['caption']))
			$html .= '<td align=right class="caption_in_popertes"><label id="text_lavel_'.$in['name'].'"'.$disabl.'>'.$in['caption'].':</label>';

		//Если нужно отобразить галочку с наследованием - то проставим её
		if ($this->nasledovanie)
		{
			$html .= $this->return_code_for_disable_element($in['name'], 'selec_');
			$html .= '<input type="hidden" class="text" id="nasleduem_'.$in['name'].'" name="properties['.$in['name'].'][nasled]" value="'.$def_val.'">';
			$html .= '<input '.$select_nasled.' onclick="set_nasledovanie_'.$in['name'].'()" type="checkbox" class="text" id="flag_nasl_'.$in['name'].'">';
		}
		$html .= '</td>';

		$html .= '<td>';
		$html .= '<select id="selec_'.$in['name'].'" name="properties['.$in['name'].'][value]" class="text"'.$disabl.'>';

		$html .= '<option value="">[#label_properties_no_select_option#]</option>';
		if (isset($in['data']))
			foreach ($in['data'] as $key=>$val)
			{

				$select = "";
				if (($def['isset']) && (!empty($def['value'])))
					if ($key == $def['value'])
						$select = " selected";


				$html .= '<option value="'.$key.'"'.$select.'>'.$val.'</option>';
			}

		$html .= "</select></td>";



		return $html;

	}

	//********************************************************************************
	/**
	 * Создает HTML код для свойства типа "radio" (набор переключателей)
	 *
	 * @param Array $in
	 * @return HTML
	 */
	function html_radio($in)
	{
		global $kernel;

		$html = "";

		$caption = '';
		if (isset($in['caption']))
			$caption = $in['caption'];

		// Узнаем текущее значение этого свойства
		$def = $this->get_default($in['name']);

		$select_nasled = "";
		$disabl = "";
		$def_val = "false";
		if ($this->nasledovanie)
		{
			if ((!$def['isset']) || ($def['naslednoe']))
			{
				$select_nasled  = " checked";
				$disabl = " disabled";
				$def_val = "true";
			}
		}


		if (isset($in['data']))
		{

			$html .= '<td align=right valign=top class="caption_in_popertes"><label id="text_lavel_'.$in['name'].'"'.$disabl.'>'.$caption.':</label>';

			if ($this->nasledovanie)
			{
				$html .= $this->return_code_for_disable_element($in['name']);
				$html .= '<input '.$select_nasled.' onclick="set_nasledovanie_'.$in['name'].'()" type="checkbox" class="text" id="flag_nasl_'.$in['name'].'">';
				$html .= '<input type="hidden" class="text" id="nasleduem_'.$in['name'].'" name="properties['.$in['name'].'][nasled]" value="'.$def_val.'">';
			}
			$html .= '</td>';

			$html .= '<td>';
			foreach ($in['data'] as $key=>$val)
			{
				$select = "";
				if (($def['isset']) && (!empty($def['value'])))
					if ($key == $def['value'])
						$select = " checked";

				$html .= '<label id="text_lavel_'.$in['name'].'"'.$disabl.'><input type="radio" '.$select.' id="'.$in['name'].'" name="properties['.$in['name'].'][value]" value="'.$key.'">&nbsp;'.$val.'&nbsp;</label><br>';
			}
			$html .= "</td>";


		}

		return $html;

	}

	//********************************************************************************
	/**
	 * Создает HTML код для свойства типа "check" (галочка)
	 *
	 * @param Array $in
	 * @return HTML
	 */
	function html_check($in)
	{
		global $kernel;

		$html = "";

		$caption = '';
		if (isset($in['caption']))
			$caption = $in['caption'];

		// Узнаем текущее значение этого свойства и проставим значения по умолчанию
		//в зависимости от этого значения
		$def = $this->get_default($in['name']);

		$select_nasled = "";
		$disabl = "";
		$def_val = "false";

		if ($this->nasledovanie)
		{
			if ((!$def['isset']) || ($def['naslednoe']))
			{
				$select_nasled  = " checked";
				$disabl = " disabled";
				$def_val = "true";
			}
		}

		$check_val = "";
		if ((!empty($def['value'])) && ($def['value'] != "false"))
			$check_val = ' checked';

		$html .= '<td align=right class="caption_in_popertes"><label id="text_lavel_'.$in['name'].'"'.$disabl.'>'.$caption.':</label>';

		if ($this->nasledovanie)
		{
			$html .= $this->return_code_for_disable_element($in['name']);
			$html .= '<input '.$select_nasled.' onclick="set_nasledovanie_'.$in['name'].'()" type="checkbox" class="text" id="flag_nasl_'.$in['name'].'">';
			$html .= '<input type="hidden" class="text" id="nasleduem_'.$in['name'].'" name="properties['.$in['name'].'][nasled]" value="'.$def_val.'">';
		}



		$html .= '</td><td><label id="text_lavel_'.$in['name'].'"'.$disabl.'><input type="checkbox" name="properties['.$in['name'].'][value]"'.$check_val.'></label></td>';

		return $html;

	}

	//******************************************************************************************
	/**
	 * Создает HTML код для свойства строка обыконовенная
	 *
	 * @param Array $in
	 * @return HTML
	 */
	function html_text($in)
	{
		global $kernel;
		$html = "";

		$caption = '';
		if (isset($in['caption']))
			$caption = $in['caption'];

		// Узнаем текущее значение этого свойства и проставим значения по умолчанию
		//в зависимости от этого значения
		$def = $this->get_default($in['name']);

		$select_nasled = "";
		$disabl = "";
		$def_val = "false";
		if ($this->nasledovanie)
		{
			if ((!$def['isset']) || ($def['naslednoe']))
			{
				$select_nasled  = " checked";
				$disabl = " disabled";
				$def_val = "true";
			}
		}

		$input_val = "";
		if (!empty($def['value']))
			$input_val = $def['value'];

		$html .= '<td align=right class="caption_in_popertes"><label id="text_lavel_'.$in['name'].'"'.$disabl.'>'.$caption.':</label>';

		if ($this->nasledovanie)
		{
			$html .= $this->return_code_for_disable_element($in['name'],'curent_value_');
			$html .= '<input '.$select_nasled.' onclick="set_nasledovanie_'.$in['name'].'()" type="checkbox" class="text" id="flag_nasl_'.$in['name'].'">';
			$html .= '<input type="hidden" class="text" id="nasleduem_'.$in['name'].'" name="properties['.$in['name'].'][nasled]" value="'.$def_val.'">';
		}

		$html .= '</td><td><label id="text_lavel_'.$in['name'].'"'.$disabl.'>';
		$html .= '<input id="curent_value_'.$in['name'].'" name="properties['.$in['name'].'][value]" size="'.$in['size'].'" class="text" maxlength="'.$in['max'].'"'.$disabl.' value="'.$input_val.'" style="width:100%"></label>';
		$html .= '</td>';

		return $html;

	}

	//******************************************************************************************
	/**
	 * Создает HTML код для свойства типа "дата" (поле с кнопкой выбора через календарь)
	 *
	 * @param Array $in
	 * @return HTML
	 */
	function html_data($in)
	{
		GLOBAL $kernel;

		$html = "";

		$caption = '';
		if (isset($in['caption']))
			$caption = $in['caption'];

		// Узнаем текущее значение этого свойства и проставим значения по умолчанию
		//в зависимости от этого значения
		$def = $this->get_default($in['name']);

		$select_nasled = "";
		$disabl = "";
		$def_val = "false";
		if ($this->nasledovanie)
		{
			if ((!$def['isset']) || ($def['naslednoe']))
			{
				$select_nasled  = " checked";
				$disabl = " disabled";
				$def_val = "true";
			}
		}

		$input_val = "";
		if (!empty($def['value']))
			$input_val = $def['value'];


		$html .= file_get_contents("components/calendar/start.html");
		$html .= '<td align=right class="caption_in_popertes"><label id="text_lavel_'.$in['name'].'"'.$disabl.'>'.$caption.':</label>';
		if ($this->nasledovanie)
		{
			$html .= $this->return_code_for_disable_element($in['name'],'cvid');
			$html .= '<input '.$select_nasled.' onclick="set_nasledovanie_'.$in['name'].'()" type="checkbox" class="text" id="flag_nasl_'.$in['name'].'">';
			$html .= '<input type="hidden" class="text" id="nasleduem_'.$in['name'].'" name="properties['.$in['name'].'][nasled]" value="'.$def_val.'">';
		}

		$html .= '</td><td><label id="text_lavel_'.$in['name'].'"'.$disabl.'><input id="cvid'.$in['name'].'" type="text" name="properties['.$in['name'].'][value]" size="10" maxlength="10" class="text" value='.$input_val.'><input type="reset" class="text" value="..." onClick="return showCalendar(\'cvid'.$in['name'].'\', \'%d-%m-%Y\');"></label>';
		$html .= '</td>';

		return $html;

	}

	//********************************************************************************
	/**
	 * Создает HTML код для свойства типа "страницы" (поле с кнопкой выбора через структуру сайта)
	 *
	 * @param Array $in
	 * @return HTML
	 */
	function html_page($in)
	{
		GLOBAL $kernel;

		$html = "";

		$caption = '';
		if (isset($in['caption']))
			$caption = $in['caption'];

		//Узнаем текущее значение этого свойства и проставим значения по умолчанию
		//в зависимости от этого значения
		$def = $this->get_default($in['name']);

		$select_nasled = "";
		$disabl = "";
		$def_val = "false";
		if ($this->nasledovanie)
		{
			if ((!$def['isset']) || ($def['naslednoe']))
			{
				$select_nasled  = " checked";
				$disabl = " disabled";
				$def_val = "true";
			}
		}

		$input_val = "";
		if (!empty($def['value']))
			$input_val = $def['value'];


		$html .= '<script type="text/javascript" src="js/manager_win.js"></script>';

		$html .= '<script type="text/javascript">
					function SelectPage(name_pole)
					{
						var template = Array();
						template["name"] = "[#modules_install_new_macros#]";
						template["file"]   = "?action=select_page";
						template["width"]  = 200;
						template["height"] = 300;
						template["posx"]  = 20;
						template["posy"] = 3;


						var arguments = Array();
						arguments["resizable"] = "no";
						arguments["scrollbars"] = "yes";

						openWindow(template, arguments);
					}
					function SetPage(param)
					{
						document.all["pgid'.$in['name'].'"].value = param;
					}
					</script>
				';

		$html .= '<td align=right class="caption_in_popertes"><label id="text_lavel_'.$in['name'].'"'.$disabl.'>'.$caption.':</label>';
		if ($this->nasledovanie)
		{
			$html .= $this->return_code_for_disable_element($in['name'],'pgid');
			$html .= '<input '.$select_nasled.' onclick="set_nasledovanie_'.$in['name'].'()" type="checkbox" class="text" id="flag_nasl_'.$in['name'].'">';
			$html .= '<input type="hidden" class="text" id="nasleduem_'.$in['name'].'" name="properties['.$in['name'].'][nasled]" value="'.$def_val.'">';
		}

		$html .= '</td><td><label id="text_lavel_'.$in['name'].'"'.$disabl.'><input id="pgid'.$in['name'].'" type="text" name="properties['.$in['name'].'][value]" size="15" maxlength="100" class="text" value='.$input_val.'><input type="button" class="text" value="..." onClick="return SelectPage(\'selp'.$in['name'].'\',\'pgid'.$in['name'].'\');"></label>';

		//Теперь проресуем скрытый слой, для выбора страницы сайта
		//$html .= '<div style="position:absolute;left:32px;top:28px;width:160px;height:97px;z-index:1; display:none;" id="selp'.$in['name'].'">
		//		  Здесь будет выбор страницы
		//		  </div>
		//';

		$html .= '</td>';

		return $html;

	}

}

//Пока тут находятся классы для поисания используемых типов параметров

class properties_file
{
	var $id			= '';		// Уникальное название параметра (желательно без подчеркивания)
	var $caption	= '';		// Название параметра
	var $type		= 'file';
	var $patch		= '/';		// путь от корня сайта, где брать файлы
	var $mask		= ''; 		// Маска допустимых файлов (расширений),
	var $default	= '';


	/**
	 * Устанваливает значение по умолчанию
	 *
	 * @param string $value
	 */
	function set_default($value)
	{
		$this->default = trim($value);
	}

	/**
	 * Устанавливает id параметра, через которое в дальнейшем будет идти обращение к значению этого параметра
	 *
	 * @param string $id
	 */
	function set_id($id)
	{
		$this->id = $id;
	}

	/**
	 * Устанавливает пользовательское представление этого параметра
	 *
	 * @param string $caption
	 */
	function set_caption($caption)
	{
		$this->caption = $caption;
	}

	/**
	 * Устанавливает путь от корня сайта, от куда производить считывание файлов для выбора в качестве
	 * значения параметра
	 *
	 * @param string $patch
	 */
	function set_patch($patch)
	{
		$this->patch = $patch;
	}


	/**
	 * Пределяет расширения(типы) файлов поподающих в список для выбора в качестве значения парамтера
	 *
	 * @param string $mask Если нужно передать несколько расширений, указываются через запятую
	 */
	function set_mask($mask)
	{
		$this->mask = $mask;
	}

	/**
	 * Возвращает значения паарметра в виде массива,
	 *
	 * @return array
	 */
	function get_array()
	{
		$param = array();
		$param["name"]		= $this->id;
		$param["caption"]	= $this->caption;
		$param["type"]		= $this->type;
		$param["patch"]		= $this->patch;
		$param["mask"]		= $this->mask;
		$param["default"]	= $this->default;
		return $param;
	}

}

class properties_select
{
	var $id			= '';		// Уникальное название параметра (желательно без подчеркивания)
	var $caption	= '';		// Название параметра
	var $type		= 'select';
	var $data		= array();	// Массив выбираемых значений где ключь - значение а знaчение массива - представление значения
	var $default	= '';


	/**
	 * Устанваливает значение по умолчанию
	 *
	 * @param string $value
	 */
	function set_default($value)
	{
		$this->default = trim($value);
	}


	/**
	 * Устанавливает id параметра, через которое в дальнейшем будет идти обращение к значению этого параметра
	 *
	 * @param string $id
	 */
	function set_id($id)
	{
		$this->id = $id;
	}

	/**
	 * Устанавливает пользовательское представление этого параметра
	 *
	 * @param string $caption
	 */
	function set_caption($caption)
	{
		$this->caption = $caption;
	}

	/**
	 * Устанавливает массив возможных значений параметра
	 *
	 * @param array $data Ключ - варинат значения параметра, значениее - представление для пользователя
	 */
	function set_data($data)
	{
		$this->data = $data;
	}

	/**
	 * Возвращает значения паарметра в виде массива
	 *
	 * @return array
	 */
	function get_array()
	{
		$param = array();
		$param["name"]		= $this->id;
		$param["caption"]	= $this->caption;
		$param["type"]		= $this->type;
		$param["data"]		= $this->data;
		$param["default"]	= $this->default;
		return $param;
	}
}

class properties_radio
{
	var $id			= '';		// Уникальное название параметра (желательно без подчеркивания)
	var $caption	= '';		// Название параметра
	var $type		= 'radio';
	var $data		= array();	// Массив выбираемых значений где ключь - значение а знaчение массива - представление значения
	var $default	= '';


	/**
	 * Устанваливает значение по умолчанию
	 *
	 * @param string $value
	 */
	function set_default($value)
	{
		$this->default = trim($value);
	}


	/**
	 * Устанавливает id параметра, через которое в дальнейшем будет идти обращение к значению этого параметра
	 *
	 * @param string $id
	 */
	function set_id($id)
	{
		$this->id = $id;
	}

	/**
	 * Устанавливает пользовательское представление этого параметра
	 *
	 * @param string $caption
	 */
	function set_caption($caption)
	{
		$this->caption = $caption;
	}

	/**
	 * Устанавливает массив возможных значений параметра
	 *
	 * @param array $data Ключ - варинат значения параметра, значениее - представление для пользователя
	 */
	function set_data($data)
	{
		$this->data = $data;
	}

	/**
	 * Возвращает значения паарметра в виде массива
	 *
	 * @return array
	 */
	function get_array()
	{
		$param = array();
		$param["name"]		= $this->id;
		$param["caption"]	= $this->caption;
		$param["type"]		= $this->type;
		$param["data"]		= $this->data;
		$param["default"]	= $this->default;
		return $param;
	}
}

class properties_checkbox
{
	var $id			= '';		// Уникальное название параметра (желательно без подчеркивания)
	var $caption	= '';		// Название параметра
	var $type		= 'check';
	var $default	= '';


	/**
	 * Устанваливает значение по умолчанию
	 *
	 * @param string $value
	 */
	function set_default($value)
	{
		$this->default = trim($value);
	}

	/**
	 * Устанавливает id параметра, через которое в дальнейшем будет идти обращение к значению этого параметра
	 *
	 * @param string $id
	 */
	function set_id($id)
	{
		$this->id = $id;
	}

	/**
	 * Устанавливает пользовательское представление этого параметра
	 *
	 * @param string $caption
	 */
	function set_caption($caption)
	{
		$this->caption = $caption;
	}


	/**
	 * Возвращает значения паарметра в виде массива
	 *
	 * @return array
	 */
	function get_array()
	{
		$param = array();
		$param["name"]		= $this->id;
		$param["caption"]	= $this->caption;
		$param["type"]		= $this->type;
		$param["default"]	= $this->default;

		return $param;
	}
}

class properties_pagesite
{
	var $id			= '';		// Уникальное название параметра (желательно без подчеркивания)
	var $caption	= '';		// Название параметра
	var $type		= 'page';
	var $default	= '';


	/**
	 * Устанваливает значение по умолчанию
	 *
	 * @param string $value
	 */
	function set_default($value)
	{
		$this->default = trim($value);
	}

	/**
	 * Устанавливает id параметра, через которое в дальнейшем будет идти обращение к значению этого параметра
	 *
	 * @param string $id
	 */
	function set_id($id)
	{
		$this->id = $id;
	}

	/**
	 * Устанавливает пользовательское представление этого параметра
	 *
	 * @param string $caption
	 */
	function set_caption($caption)
	{
		$this->caption = $caption;
	}


	/**
	 * Возвращает значения паарметра в виде массива
	 *
	 * @return array
	 */
	function get_array()
	{
		$param = array();
		$param["name"]		= $this->id;
		$param["caption"]	= $this->caption;
		$param["type"]		= $this->type;
		$param["default"]	= $this->default;

		return $param;
	}
}

class properties_string
{
	var $id			= '';		// Уникальное название параметра (желательно без подчеркивания)
	var $caption	= '';		// Название параметра
	var $type		= 'text';
	var $default	= '';


	/**
	 * Устанваливает значение по умолчанию
	 *
	 * @param string $value
	 */
	function set_default($value)
	{
		$this->default = trim($value);
	}

	/**
	 * Устанавливает id параметра, через которое в дальнейшем будет идти обращение к значению этого параметра
	 *
	 * @param string $id
	 */
	function set_id($id)
	{
		$this->id = $id;
	}

	/**
	 * Устанавливает пользовательское представление этого параметра
	 *
	 * @param string $caption
	 */
	function set_caption($caption)
	{
		$this->caption = $caption;
	}


	/**
	 * Возвращает значения паарметра в виде массива
	 *
	 * @return array
	 */
	function get_array()
	{
		$param = array();
		$param["name"]		= $this->id;
		$param["caption"]	= $this->caption;
		$param["type"]		= $this->type;
		$param["default"]	= $this->default;
		return $param;
	}
}

class properties_date
{
	var $id			= '';		// Уникальное название параметра (желательно без подчеркивания)
	var $caption	= '';		// Название параметра
	var $type		= 'data';
	var $default	= '';


	/**
	 * Устанваливает значение по умолчанию
	 *
	 * @param string $value
	 */
	function set_default($value)
	{
		$this->default = trim($value);
	}

	/**
	 * Устанавливает id параметра, через которое в дальнейшем будет идти обращение к значению этого параметра
	 *
	 * @param string $id
	 */
	function set_id($id)
	{
		$this->id = $id;
	}

	/**
	 * Устанавливает пользовательское представление этого параметра
	 *
	 * @param string $caption
	 */
	function set_caption($caption)
	{
		$this->caption = $caption;
	}


	/**
	 * Возвращает значения паарметра в виде массива
	 *
	 * @return array
	 */
	function get_array()
	{
		$param = array();
		$param["name"]		= $this->id;
		$param["caption"]	= $this->caption;
		$param["type"]		= $this->type;
		$param["default"]	= $this->default;
		return $param;
	}
}


?>