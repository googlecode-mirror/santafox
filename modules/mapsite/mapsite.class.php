<?PHP
/**
 * Основной управляющий класс модуля «карта сайта»
 *
 * Модуль «карта сайта» предназначен для построения так называемой
 * ссылочной карты сайта. Это страница на которой полностью отображена
 * вся структура сайта.
 *
 * Часто картой сайта пользуются посетители сайта, но гораздо более важное
 * значение она имеет для равномерной индексации поисковыми
 * системами страниц сайта
 * @copyright ArtProm (с) 2001-2013
 * @version 2.0
 */

class mapsite
{
    protected $template_array = array(); //Содержит распаршенный шаблон
    protected $one_admin = true;		  //Содержит признак того, что админка у модуля одна,
                                  //в не зависимости от количества дочерних модулей
	protected  $path_templates = "modules/mapsite/templates_user"; //Путь, к шаблонам модуля (туда будут скопированны шаблоны при инсталяции

    protected $currentMapFile;
    protected $maxLinksPerFile=10000;
    protected $domain;
    protected $currentFileLinksCount;
    protected $currentFileNum=1;
    protected $lastClosedFile;

    protected function prepare_all_pages($p_id_page='')
    {
        global $kernel;
        $pages = $kernel->pub_mapsite_cashe_create(1, $p_id_page);

        //поищем установленные модули "каталог товаров", "вопрос-ответ" и "новости"
        $modules = $kernel->pub_modules_get();
        $faq_module = false;
        $news_modules = $catalog_modules = array();

        $structRecs = $kernel->db_get_list_simple("_structure",1,"id,serialize");
        foreach ($modules as $module_name=>$module_vars)
        {
            if (preg_match("/^faq\\d+$/", $module_name))
                $faq_module = $module_name;
            elseif (preg_match("/^catalog\\d+$/", $module_name))
            {
                $vals = $this->get_module_action_values($module_name, "pub_catalog_show_cats");
                if ($vals && isset($vals['catalog_items_pagename']) && $vals['catalog_items_pagename'])
                    $catalog_modules[$vals['catalog_items_pagename']] = $module_name;
            }
            elseif(preg_match("/^newsi\\d+$/", $module_name))
            {
                //попытаемся найти страницу архива через действие вывода ленты
                $vals = $this->get_module_action_values($module_name, "pub_show_lenta");
                if($vals && isset($vals['page']) && $vals['page'])
                    $news_modules[$vals['page']]=$module_name;
                else
                {
                    //если не было ленты, или в ней не была установлена страница архива, ищем в структуре
                    $actionRecs=$kernel->db_get_list_simple("_action","`id_module`='".$module_name."' AND link_str='pub_show_archive'","id");
                    foreach($actionRecs as $arec)
                    {
                        foreach($structRecs as $srec)
                        {
                            if(!$srec['serialize'])
                                continue;
                            $srec['serialize'] = unserialize($srec['serialize']);
                            foreach($srec['serialize'] as $mdata)
                            {
                                if($mdata['id_action']==$arec['id'])
                                {
                                    $news_modules[$srec['id']]=$module_name;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($faq_module)
        {
            $vals = $this->get_module_action_values($faq_module, "pub_show_partitions");
            if (!empty($vals['faq_answers_page']))
            {
                $partitions = $this->faq_get_partitions($vals['faq_answers_page']);
                if (count($partitions)>0)
                    $pages = $this->attach_more_pages($pages, $vals['faq_answers_page'], $partitions);
            }
        }

        foreach ($catalog_modules as $cpagename=>$cmoduleid)
        {
            $catalogModuleCats = $this->catalog_get_tree(0, $cmoduleid, $cpagename);
            if (count($catalogModuleCats)>0)
                $pages = $this->attach_more_pages($pages, $cpagename, $catalogModuleCats);
        }

        foreach ($news_modules as $npagename=>$nmoduleid)
        {
            $nrecs=$kernel->db_get_list_simple("_newsi","`module_id`='".$nmoduleid."'","id,header");
            if(!$nrecs)
                continue;
            $attachNews = array();
            foreach($nrecs as $nrec)
            {
                $array = array(
                    'caption' 	=> $nrec['header'],
                    'parent_id' => '',
                    'curent'    => false,
                    'include'   => array(),
                );
                $attachNews[$npagename.".html?id=".$nrec['id']] = $array;
            }
            $pages = $this->attach_more_pages($pages, $npagename, $attachNews);
        }

        return $pages;
    }

    //***********************************************************************
    //	Наборы Публичных методов из которых будут строится макросы
    //**********************************************************************

    /**
     * Из данного метода создаётся действие для вывода карты сайта
     *
     * @param string $p_id_page Задаётся id страницы, с которой будет начинаться вывод карты
     * @param string $template
     * @return string
     */
    public function pub_show_mapsite($p_id_page, $template)
    {
        $pages=$this->prepare_all_pages($p_id_page);
        if (empty($template))
            return '[#module_mapsite_errore2#]';
        if (!file_exists($template))
            return '[#module_mapsite_errore1#] "<i>'.trim($template).'</i> "';
    	$this->parse_template($template);
        //Начнем формировать HTML код карты сайта, используя
        //рекурсивную функцию
        $html = $this->recurs($pages);
        return $html;
    }

    protected function start_current_file_content()
    {
        $this->currentFileLinksCount=0;
        file_put_contents($this->currentMapFile,'<?xml version="1.0" encoding="utf-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
    }

    public function pub_create_sitemapxml()
    {
        global $kernel;
        if(!$kernel->pub_module_id_get(true))
            $kernel->priv_module_for_action_set('mapsite1');
        $pages=$this->prepare_all_pages();
        $site_root=$kernel->pub_site_root_get();
        $this->currentMapFile=$site_root."/sitemap1.xml";
        if (file_exists($this->currentMapFile))
            unlink($this->currentMapFile);
        $this->start_current_file_content();
        $prop = $kernel->pub_modul_properties_get('domain');
        if (!$prop || !$prop['isset'])
        {
            print "no domain prop set";
            return;
        }
        $this->domain=$prop['value'];
        $this->recurs_xml($pages);
        //finalize
        if ($this->lastClosedFile != $this->currentMapFile)
            $this->close_current_xml_file();
        $smFile = $site_root.'/sitemap.xml';
        file_put_contents($smFile,"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");
        for($i=1;$i<=$this->currentFileNum;$i++)
        {
            $file = $site_root.'/sitemap'.$i.'.xml';
            file_put_contents($smFile,"<sitemap>\n <loc>http://".$this->domain."/sitemap".$i.".xml</loc>\n <lastmod>".date("c",filemtime($file))."</lastmod>\n</sitemap>\n",FILE_APPEND);
        }
        file_put_contents($smFile,"</sitemapindex>",FILE_APPEND);
    }


    protected function close_current_xml_file()
    {
        file_put_contents($this->currentMapFile,"\n</urlset>",FILE_APPEND);
    }

    public function put_link2sitemap_xml($link,$priority=null,$changeFreq=null,$lastModDate=null)
    {
        global $kernel;
        if (!$lastModDate)
            $lastModDate=date("Y-m-d");
        $link='http://'.$this->domain.$link;
        $this->currentFileLinksCount++;
        $line="<url>\n <loc>".$link."</loc>\n <lastmod>".$lastModDate."</lastmod>\n</url>\n";
        if($changeFreq)
            $line.="<changefreq>".$changeFreq."</changefreq>\n";
        if(!is_null($priority))
            $line.="<priority>".$priority."</priority>\n";
        file_put_contents($this->currentMapFile,$line,FILE_APPEND);

        //Можно предоставить несколько файлов Sitemap, однако в каждом из этих файлов должно быть не более 50000 URL,
        // а размер каждого из этих файлов не должен превышать 10 МБ.
        //http://www.sitemaps.org/ru/protocol.html
        if ($this->currentFileLinksCount==$this->maxLinksPerFile) //в текущем файле уже максимум ссылок
        {
            $this->lastClosedFile = $this->currentMapFile;
            $this->close_current_xml_file();
            $this->currentFileNum++;
            $this->currentMapFile= $kernel->pub_site_root_get()."/sitemap".$this->currentFileNum.".xml";
            $this->start_current_xmlfile_content();
        }
    }

    protected function start_current_xmlfile_content()
    {
        $this->currentFileLinksCount=0;
        file_put_contents($this->currentMapFile,'<?xml version="1.0" encoding="utf-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
    }


    /**
     * Рекурсивно обходит дерево страниц сайта и добавляет дополнительное дерево
     *
     * @param array $pages  оригинальный массив страниц
     * @param string $attach_in_page id-шник страницы, к которой надо добавить
     * @param array $more_pages дополнительное дерево страниц
     * @return array
     */
    protected function attach_more_pages($pages, $attach_in_page, $more_pages)
    {
        foreach ($pages as $key => $val)
        {
            if (!isset($val['include']))
                $inc = array();
            else
                $inc = $val['include'];
            if ($key == $attach_in_page)
            {//нужная нам страница
                $inc = $inc+$more_pages;
                $pages[$key]['include'] = $inc;
                return $pages;
            }
            else
            {
                $pages[$key]['include'] = $this->attach_more_pages($inc, $attach_in_page, $more_pages);
            }
        }
        return $pages;
    }

    /**
     * Рекурсивно создаёт дерево категорий каталога товаров в нужном нам виде
     *
     * @param integer $node_id id-шник родительской категории
     * @param integer $module_id id-шник модуля
     * @param string $pagename страница
     * @return array
     */
    protected function catalog_get_tree($node_id, $module_id, $pagename)
    {
    	global $kernel;
		$data  = array();
		$sql   = 'SELECT id,name FROM `'.PREFIX.'_catalog_'.$module_id.'_cats` WHERE `parent_id` = '.$node_id;
		$query = $kernel->runSQL($sql);
        while ($row = mysql_fetch_assoc($query))
        {
            $array = array(
                'caption' 	=> $row['name'],
                'parent_id' => $node_id,
                'curent'    => false,
            );

            //товары категории
            $isql   = 'SELECT items.id,items.name FROM `'.PREFIX.'_catalog_'.$module_id.'_item2cat` AS i2c
                       INNER JOIN `'.PREFIX.'_catalog_'.$module_id.'_items` AS items ON items.id=i2c.item_id
                       WHERE i2c.`cat_id` = '.$row['id'].' AND items.available=1';
            $ir = $kernel->runSQL($isql);
            while($irow = mysql_fetch_assoc($ir))
            {
                $data[$pagename.".html?itemid=".$irow['id']]=array(
                    'caption' 	=> $irow['name'],
                    'parent_id' => $node_id,
                    'curent'    => false,
                );
            }
            mysql_free_result($ir);

            $children = $this->catalog_get_tree($row['id'], $module_id, $pagename);
            $array['include'] = $children;
            $data[$pagename.".html?cid=".$row['id']] = $array;
        }
		mysql_free_result($query);
		return $data;
    }

    /**
     * Возвращает список разделов модуля FAQ в нужном нам виде
     *
     * @param string $pagename имя страницы вывода вопросов-ответов
     * @return array
     */
    function faq_get_partitions($pagename)
    {
    	global $kernel;
		$data  = array();
		$sql   = 'SELECT id,name FROM `'.PREFIX.'_faq_partitions`';
		$query = $kernel->runSQL($sql);

        while ($row = mysql_fetch_assoc($query))
        {
            $array = array(
                'caption' 	=> $row['name'],
                'parent_id' => '',
                'curent'    => false,
                'include'   => array(),
            );
            $data[$pagename.".html?a=2&b=".$row['id']] = $array;
        }
		mysql_free_result($query);
		return $data;
    }


    /**
     * Возвращает список имя=>значение параметров действия модуля
     *
     * @param integer $module_id id-шник модуля
     * @param string $action_str название действия модуля
     * @return array
     */
    function get_module_action_values($module_id, $action_str)
    {
        $mod = new manager_modules();
        $actions = $mod->list_array_macros($module_id);
        foreach ($actions as $action)
        {
            if ($action['link_str']==$action_str)
            {
                return unserialize($action['param_array']);
            }
        }
        return false;
    }

    //***********************************************************************
    //	Наборы внутренних методов модуля
    //**********************************************************************


	/**
	 * Рекурсивная функция, для преобразования массива с картой в HTML форму в соответсвтии с
	 * шаблоном
	 * @param Array $pages Содержит страницы определнного уровня
	 * @param Int $level Задаёт уровень прохождения по массиву
	 * @return string
	 */
	protected function recurs($pages, $level=-1)
	{
		global $kernel;
		$level++;

		//Если в шаблоне нет необходимого нам уровня, то создадим его из предыдущего
		foreach ($this->template_array as $key=>$val)
        {
			if (!isset($val[$level]))
				$this->template_array[$key][$level] = $this->template_array[$key][count($this->template_array[$key])-1];
        }

		//Начнем вывод
		$map_arr = array();
		foreach ($pages as $key => $val)
		{

			$html = "";
			$id = $key;
			$caption = $val['caption'];

			//Возьмем свойство видимости из свойств страницы
			$arr = $kernel->pub_page_property_get($key,'visible');
			$visible = true;
			if (($arr['isset']) && ($arr['value'] == "false"))
				$visible = false;

			if ($visible)
			{
				if ($val['curent'])
					$tmpl = $this->template_array['activelink'][$level];
				else
					$tmpl = $this->template_array['link'][$level];
				$tmpl = str_replace("%text%", $caption, $tmpl);
				if (strpos($id, ".html?")===false)
                    $repl=$id.".html";
                else
                    $repl=$id;
                $tmpl = str_replace("%link%", $repl, $tmpl);

				$html .= $tmpl;
				if ((isset($val['include'])) && (!empty($val['include'])))
					$html .= $this->recurs($val['include'],$level);
			}
			else
			{
				if ((isset($val['include'])) && (!empty($val['include'])))
				{
            		$html .= $this->template_array['end'][$level];
					$html .= $this->recurs($val['include'],$level);
            		$html .= $this->template_array['begin'][$level];
				}
			}
			$map_arr[] = $html;
		}

		if (empty($map_arr))
			return "";

		$html = $this->template_array['begin'][$level];
		$html .= join($this->template_array['delimiter'][$level], $map_arr);
		$html .= $this->template_array['end'][$level];

		return $html;

	}


	/**
	 * Рекурсивная функция, для преобразования массива с картой в sitemap.xml форму
	 * @param array $pages Содержит страницы определнного уровня
	 * @return string
	 */
	protected function recurs_xml($pages)
	{
		global $kernel;
		//Начнем вывод
		foreach ($pages as $key => $val)
		{
			$id = $key;
			//Возьмем свойство видимости из свойств страницы
			$arr = $kernel->pub_page_property_get($key,'visible');
			$visible = true;
			if ($arr['isset'] && ($arr['value'] == "false"))
				$visible = false;
			if ($visible)
			{
				if (strpos($id, ".html?")===false)
                    $link=$id.".html";
                else
                    $link=$id;
                $this->put_link2sitemap_xml('/'.$link);
				if (isset($val['include']) && !empty($val['include']))
					$this->recurs_xml($val['include']);

			}
			else
			{
				if (isset($val['include']) && !empty($val['include']))
					$this->recurs_xml($val['include']);
			}
		}
	}

    //********************************************************************************
	/**
    * Разбирает шаблон, создает $this->template_array
    * @return void
    * @param String $filename Путь к файлу шаблонов
    */
	function parse_template($filename)
	{
		global $kernel;

		//Парсим шаблон с учётом нулевого уровня
		$arr = $kernel->pub_template_parse($filename, true);

		//Теперь проверим блоки на их наличие и значение по умолчанию
		if (!isset($arr['begin']))
			$arr['begin'][0] = "";

		if (!isset($arr['delimiter']))
			$arr['delimiter'][0] = "";

		if (!isset($arr['passiveactive']))
			$arr['passiveactive'] = $arr['link'];

		if (!isset($arr['activelink']))
			$arr['activelink'] = $arr['link'];

		if (!isset($arr['end']))
			$arr['end'][0] = "";


		$this->template_array = $arr;

	}

    /**
     * Функция для отображения административного интерфейса
     *
     * @return string
     */
    public function start_admin()
    {
    }

    /**
     * Функция для построения меню для административного интерфейса
     *
     * @param pub_interface $menu Обьект класса для управления построением меню
     * @return boolean true
     */
    public function interface_get_menu($menu)
    {
    }
}