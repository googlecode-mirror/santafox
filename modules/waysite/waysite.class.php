<?PHP
/**
 * Основной управляющий класс модуля «дорога»
 *
 * Модуль «дорога» предназначен для построения так называемой дороги сайта.
 * Дорога сайта необходимо для того, что бы посетитель сайта мог быстро
 * вернуться ну нужный ему уровень иерархии сайта.
 * @copyright ArtProm (с) 2001-2008
 * @version 1.0
 */

class waysite
{

	/**
	 * Распаршенный шаблон
	 *
	 * Массив с обработанным шаблоном, где в качетсве ключа используется название секции шаблона
	 * а в качестве значения HTML контент, используемый вместо секции
	 * @var array
	 * @access private
	 */
	var $template_array = array();

    /**
     * Конструктор класса
     *
     * @return void
     */
	function waysite ()
    {

    }


//***********************************************************************
//	Наборы Публичных методов из которых будут строится макросы
//**********************************************************************

    /**
     * Публичный метод используемый для построения дороги
     *
     * Служит для формирования действия отвечающего за вывод дороги
     * @return HTML
     */
    function pub_show_waysite($template = '')
    {
    	global $kernel;

    	//Получим массив с дорогой
    	$pages = $kernel->pub_waysite_get();

    	// Удаляем дополнительные элементы пути, чтобы не мешали
        if (isset($pages['additional_way']))
        {
        	$pages_additional_way = $pages['additional_way'];
            unset($pages['additional_way']);
        }

    	//Если в дороге всего одна страница, то не будем её выводить
        if (count($pages)<2)
			return "";

    	//Получим используемый шаблон для формирования дороги
        if (empty($template))
            return '[#module_waysite_errore2#]';

        if (!file_exists($template))
            return '[#module_waysite_errore1#] "<i>'.trim($template).'</i> "';

        $this->template_array = $kernel->pub_template_parse($template);

		$way_arr = array();
		foreach ($pages as $key => $way_item)
		{
			//Возьмем свойство видимости из свойств страницы
			$arr = $kernel->pub_page_property_get($key,'visible');
			$visible = true;
			if ($arr['isset'] && ($arr['value'] == "false"))
				$visible = false;

			//Выводим, если позволяет свойство
			if ($visible)
			{
				if (($key == $kernel->pub_page_current_get()) && (!isset($pages_additional_way)))
					$tmpl = $this->template_array['activelink'];
				else
					$tmpl = $this->template_array['link'];

				$tmpl = str_replace("%text%", htmlspecialchars($way_item['caption']), $tmpl);
                if ($key=="index")
				    $tmpl = str_replace("%link%", "/", $tmpl);
                else
				    $tmpl = str_replace("%link%", $key.".html", $tmpl);

				$way_arr[] = $tmpl;
			}
		}
		if (isset($pages_additional_way))
		{
		    // Определим последний элемент дороги
		    $last_additional_way = array_reverse($pages_additional_way, true);
		    reset($last_additional_way);
		    $last_additional_way = key($last_additional_way);

		    // И достроим дорогу
		    foreach ($pages_additional_way AS $key => $value)
		    {
				if ($key == $last_additional_way)
					$tmpl = $this->template_array['activelink'];
				else
					$tmpl = $this->template_array['link'];

				$tmpl = str_replace("%text%", htmlspecialchars($value['caption']), $tmpl);
				$tmpl = str_replace("%link%", $value['url'], $tmpl);

				$way_arr[] = $tmpl;
		    }
		}


		if (count($way_arr) <= 1)
			return "";

		$html = "";
		$html .= $this->template_array['begin'];
		$html .= join($this->template_array['delimiter'], $way_arr);
		$html .= $this->template_array['end'];

        return $html;
    }

    /**
     * Из данного метода создаётся действие для вывода заголовка страницы
     * по по номеру текущего уровня в дороге
     *
     * Используется для формирования заголовков страницы в контенете
     * @param string $p_id_page Номер уровня в дороге, который будет выводится
     * @return string
     */
    function pub_show_caption_static($level_num)
    {
        //Получим массив с дорогой
        global $kernel;
    	$pages = $kernel->pub_waysite_get();

        // Удаляем дополнительные элементы пути, чтобы не мешали
        $pages_additional_way = array();
        if (isset($pages['additional_way']))
        {
        	$pages_additional_way = $pages['additional_way'];
            unset($pages['additional_way']);
        }
        $pages = array_merge($pages, $pages_additional_way);

    	if (count($pages) <= $level_num)
    	   return "";

    	//Текущая Дорога удволетворяет по длинее и мы можем взять название нужной страницы
        $pages = array_values($pages);

        return $pages[$level_num]['caption'];
    }



//***********************************************************************
//	Наборы методов, для работы с админкой модуля
//**********************************************************************


	/**
	 * Вызывается при переходе в АИ модуля
	 *
	 * Предопределйнный метод, используется для вызова административного интерфейса модуля
	 * АИ может быть разным в зависиости от прав и кол-ва дочерних модулей
	 */
	function start_admin()
	{
		global $kernel;


		return "";
	}
}

?>