<?php
/**
 * Создает древовидную структуру для управления данными
 *
 * Создававемая древовидная структура поддерживает следующие особенности:
 * <ul>
 * <li>Создание интерактивного дерева</li>
 * <li>Использование возможности Drag&Drop для изменения взаимо связи между элементами</li>
 * <li>Возможность блокировки корневого узла</li>
 * <li>Возможность создания контекстного меню, вызываемого по клику правой кнопки мыши</li>
 * </ul>
 *
 * @name data_tree
 * @package  PublicFunction
 * @copyright ArtProm (с) 2010
 * @version 2.0
 */
class data_tree
{

    /**
     * Поределяет приоритет выставления текущей ноды
     *
     * Если стоит в false, то при нали
     * @var bool
     */
    var $prioritet_node_and_other_menu = true;
    /**
     * Уникальное имя для сохранения куков о текущем положении пользователя
     *
     * @var string
     */
    var $name_for_cookie = "tree";

	/**
	 * Действие для загрузки дерева
	 *
	 * Определяет название действия, которое бужет передаваться в функциию ajax, и использоваться
	 * для загрузки дерева
	 * @var string
	 */
	var $action_get_data = '';

	/**
	 * ID главной ноды
	 *
	 * @var string
	 */
    var $root_id = 'index';

    /**
     * Имя главной ноды
     *
     * @var string
     */
    var $root_name = 'Tree';

	/**
	 * Разрешает/запрещает перенос нод
	 *
	 * @var boolean
	 */
    var $drag_and_drop = false;

    /**
     * Определяет ID действие, которое должно выполнояться при клике по ноде
     *
     * @var string
     */
    var $action_node = '';

    /**
     * ID действия, вызываемого при перемещении ноды
     *
     * @var unknown_type
     */
    var $action_move = '';

    /**
     * Шаблон, используемый для построения дерева
     *
     * @var String
     */
	var $template;

    /**
     * Массив действий контекстного меню
     *
     * Пример массива
     * <code>
	 * 	[1] => array
	 * 	(
	 *      [type] Один из 4 возможных типов элемента контекстного меню (context_empty, context_element_normal, context_element_remov, context_element_add)
     * 		[name] Название пункта меню
     * 		[link] ссылка, начинающаяся с ID дейсвтия, выполняемого по клику по пункту меню
     *      [exclude] ID нод, через запятую, где данные пункт меню будет недоступен
     * 		[confirm] Строка предупреждения, перед выполением действия (если не задана, то предупреждение не выводится)
	 * 	)
     * </code>
     * @var array
     * @access private
     */
    var $contextmenu = array();

    /**
     * ID ноды, которую нужно открыть при первом фомировании списка
     *
     * @var string
     */
    var $node_default = '';

    /**
     * Запрещает/разрешает выполнять действие при клике по центральной ноде
     *
     * @var string
     */
    var $not_click_main = 'false';

    /**
     * Если параметр установлен в false, то все ссылки (id действий) должны быть указаны вместе
     * с полем action, тем самым можно самостоятельно определить куда будет направлен запроса от
     * построенного дерева.
     *
     * @var boolean
     */
    var $relativ_url = true;

    /**
     * Массив с нодами дерева
     *
     * @var array
     */
    var $nodes = null;

    /**
     * Признак того, что идёт работа со структурой сайта
     *
     * @var boolean
     */
    var $is_page_structure = false;

    /**
     * Устанавливает массив с данными
     *
     * @param array $nodes
     */
    function set_nodes($nodes)
    {
        $this->nodes = $nodes;
    }

    function set_action_get_data($action)
    {
        $this->action_get_data = $action;
    }


    /**
     * Вызвается только для структуры сайта
     *
     * Определяет, что клик по ноде должен обрабатываться по особому, так как
     * идёт работа со структурой сайта.
     * @param boolean $bool
     */
    function set_work_page_structure($bool = true)
    {
        $this->is_page_structure = $bool;
    }

	/**
	 * Конструктр класса
	 *
	 * @param string $root_name Имя корневой ноды
	 * @param string $root_id ID корневой ноды
	 * @param array $nodes ноды
	 * @return data_tree
	 */
	function data_tree($root_name = "", $root_id = "", $nodes = null)
	{
	    global $kernel;

	    $this->template = $kernel->pub_template_parse('admin/templates/default/tree.html');
	    $this->set_nodes($nodes);

        //$this->template['main'] = str_replace('%tree_data_url%', $this->template['tree_data_url'], $this->template['main']);
	    if (is_null($this->nodes))
	    {
            $this->template['main'] = str_replace('%tree_data_url%', $this->template['tree_data_url'], $this->template['main']);
            $this->template['main'] = str_replace('%tree_data_children%', 'null', $this->template['main']);
	    }
	    else
	    {
            $this->template['main'] = str_replace('%tree_data_url%', '', $this->template['main']);
            $this->template['main'] = str_replace('%tree_data_children%', $kernel->pub_json_encode($this->nodes), $this->template['main']);
	    }

	    if ($root_name)
	        $this->root_name = $root_name;

	    //Именно так, так как может быть пустой строкой
	    if ($root_id !== "")
	        $this->root_id = $root_id;

    }


    /**
     * Устанвливает/снимает прямые ссылки в действиях дерева
     *
     * При вызове данного параметра, все действия дерева должны быть указаны в виде полных ссылок
     *
     * @param boolean $param
     */
    function set_direct_action($param = true)
    {
        $this->relativ_url = !$param;
    }


    /**
     * Задаёт ID ноды по умолчанию
     * На этой ноде как бы автоматически происходит клик, при построение меню
     * @param string $node
     */
    function set_node_default($node = '')
    {
        $this->node_default = $node;

    }

    /**
     * Определяет имя действия, передаваемого при клике по ноде
     *
     * Данное действие будет переданно в управляющую структуру соответсвующего
     * класса и доступно там с помощью метода $kernel->pub_section_leftmenu_get()
     * @param boolean $val Имя действия
     */
    /*
    function set_prioritet_node($val = true)
    {
        $this->prioritet_node_and_other_menu = $val;
    }
    */

    /**
     * Определяет имя действия, передаваемого при клике по ноде
     *
     * Данное дейсвтие будет переданно в управляющую структуру соответсвующего
     * класса и доступно там с помощью метода $kernel->pub_section_leftmenu_get()
     * @param string $action Имя действия
     */
    function set_action_click_node($action)
    {
        $this->action_node = $action;
    }

    /**
     * Возвращает имя действия, передаваемого при клике по ноде
     *
     * Данное дейсвтие будет переданно в управляющую структуру соответсвующего
     * класса и доступно там с помощью метода $kernel->pub_section_leftmenu_get()
     * @return string
     */
    function get_action_click_node()
    {
        return $this->action_node;
    }


    /**
     * Определяет имя действия, передаваемого при перемещении ноды
     *
     * Данное дейсвтие будет переданно в управляющую структуру соответсвующего
     * класса и доступно там с помощью метода $kernel->pub_section_leftmenu_get()
     * @param string $action Имя действия
     */

    function set_action_move_node($action)
    {
        $this->action_move = $action;
    }


    /**
     * Включет (выключает) функцию переноса нод внутри структуры
     *
     * По умолчанию данная функция выключена
     * @param boolean $value
     */
    function set_drag_and_drop($value = true)
    {
        $this->drag_and_drop = $value;
    }

    /**
     * Устанавливает уникальное название cookie для дерева,
     * для того, чтобы у разных деревьев были свои
     *
     * @param string $name
     */
    function set_name_cookie($name = 'tree')
    {
        $this->name_for_cookie = $name;
    }


    /**
     * Устанавливает элемент контекстного меню
     *
     * С выполнением данного дейсвтия, в дереве не происходят никакие изменения
     * @param string $name Название пункта контекстного меню
     * @param string $link Линк для действия, выполняемого по клику на этот пункт меню
     * @param string|boolean $exclude ID нод, через запятую, для которых этот пункт будет недоступен
     * @param string $message_confirm Сообщение, которе должно выводиться перед тем как выполнить этот пункт меню
     */

    function contextmenu_action_set($name, $link, $exclude = false, $message_confirm = '')
    {
        $this->contextmenu[] = array("type" => "context_element_normal",
                                     "name" => $name,
                                     "link" => $link,
                                     "exclude" => $exclude,
                                     "confirm" => $message_confirm
                                     );
    }


    /**
     * Устанваливает элемент контекстного меню, удаляющий ноду
     *
     * Используется когда с выполнением дейсвтия в контекстном меню необходимо удалить ноду, на которой
     * происходит вызов контекстного меню
     * @param string $name Название пункта контекстного меню
     * @param string $link Линк для действия, выполняемого по клику на этот пункт меню
     * @param string|boolean $exclude ID нод, через запятую, для которых этот пункт будет недоступен
     * @param string $message_confirm Сообщение, которое должно выводиться перед тем, как выполнить этот пункт меню
     */

    function contextmenu_action_remove($name, $link, $exclude = false, $message_confirm = '')
    {
        $this->contextmenu[] = array("type" => "context_element_remov",
                                     "name" => $name,
                                     "link" => $link,
                                     "exclude" => $exclude,
                                     "confirm" => $message_confirm
                                     );
    }


    /**
     * Добавляет разделитель в контекстное меню
     * @return void
     */
    function contextmenu_delimiter()
    {//@todo remove
        //$this->contextmenu[] = array("type" => "context_empty", "name" => "", "link" => "", "exclude" => "", "confirm" => "");
    }

    /*
    function set_menu_link($link)
    {
        $this->menu_global_link = $link;
    }
*/
    function get_tree()
    {
        global $kernel;

        $notclick = "false";
        if ($this->not_click_main === true)
            $notclick = "true";

        $direct_click = "false";
        if ($this->relativ_url === false)
            $direct_click = "true";

        //Сформируем код для котекстного меню
        //$html_context_menu = $this->template['context_menu_not']; //если нет контекст-меню
        $html_context_menu_function = '';

        if (!empty($this->contextmenu))
        {
            //Соберём меню
            $i = 0;
            $array_function = array();
            foreach ($this->contextmenu as $element)
            {

                //Если это разделитель, то просто добавим его и пойдём дальше
                /*
            	if ($element['type'] == 'context_empty')
            	{
            	   $html_elements .= $this->template['context_empty'];
            	   continue;
            	}

                 */

                /*
            	$str_object = $this->template['context_element'];
                $str_object = str_replace("[#name#]"        , $element['name']   , $str_object);
                //$str_object = str_replace("[#id_context#]"  , $i                 , $str_object);
                $str_object = str_replace("[#node_exclude#]", $element['exclude'], $str_object);
                */
                //Создадим функцию, которая будет обрабатывать клик по меню её шаблон
                //зависит от типа элемента меню, так как возможно нужно выполнять разные
                //действия
                $str = $this->template[$element['type'].'_click_function'];

                //@todo use real disabled elements - http://stackoverflow.com/questions/4559543/configuring-jstree-right-click-contextmenu-for-different-node-types
                //or http://www.devcomments.com/jsTree-5672-Re-Disable-Specific-Context-Menu-Item-based-off-node-at290978.htm
                
                $str = str_replace("[#node_exclude#]", $element['exclude'], $str);

                //Если нужно - вставим обвязку для вопроса, внутри которой снова появляется [#action#]
                //Конфирм общий, и не зависит от типа меню
                if (!empty($element['confirm']))
                	$str = str_replace("[#action#]", $this->template['message_confirm'], $str);

                //Теперь по-любому добавляем действие
                $str = str_replace("[#action#]", $this->template[$element['type'].'_context_action'], $str);
                $str = str_replace("[#name#]"        , $element['name']   , $str);
                //Ну теперь заменим имеющиеся переменные в получившемся коде
                //$str = str_replace("[#id_context#]"  , $i,                                            $str);
                $str = str_replace("[#link_orig#]", $element['link'], $str);
                $str = str_replace("[#link#]", $kernel->pub_redirect_for_form($element['link'], $this->relativ_url), $str);

                $str = str_replace("[#confirm#]", $element['confirm'], $str);
                $array_function[] = $str;
                //$html_elements .= $str_object;
                $i++;
            }
            //$html_context_menu = $html_elements;
            if (count($array_function) > 0)
                $html_context_menu_function = ",".join(',',$array_function);

        }

         

        //Путь для получения данных о структуре и выполнении различных жействий
        $data_url = $kernel->pub_redirect_for_form($this->action_get_data, $this->relativ_url);
        $move_url = $kernel->pub_redirect_for_form($this->action_move, $this->relativ_url);
        $def_link = '';
        if ($this->node_default !== "") //Используем именно такое сравнение, так как может быть 0
        {
            if (is_array($this->node_default))
            {
                //$node = $this->node_default[(count($this->node_default)-1)];
                $this->node_default = "'".join("','", $this->node_default)."'";
            }
            else
            {
                //$node = $this->node_default;
                $this->node_default = "'".$this->node_default."'";
            }
            //Это первый возов этого интерфейса, и значит что нужно полностью
            //загрузить интерфейс формы, так как его ещё точно нет
            if ($this->is_page_structure)
            {
            	//$def_link = $this->action_node;
            	//$def_link = 'start_interface.link_go("'.$this->action_node.'&id="+sel_node.id);';
            	$def_link = 'start_interface.link_go("'.$this->action_node.'&id="+sel_node);';
            }
        }

        $treeID = "tree".substr(md5(rand(1000,9999)),0,5);
        //Сформируем непосредственно дерево
        $html = $this->template['main'];
        $html = str_replace("[#tree_id#]"   , $treeID,   $html);
        $html = str_replace("[#root_name#]"   , $this->root_name,   $html);
        $html = str_replace("[#root_id#]"     , $this->root_id,     $html);
        $html = str_replace("[#data_url#]"    , $data_url,          $html);
        $html = str_replace("[#move_url#]"    , $move_url,          $html);
        $html = str_replace("[#not_click#]"   , $notclick,          $html);
        $html = str_replace("[#direct_click#]", $direct_click,      $html);
        $html = str_replace("[#node_default#]", $this->node_default, $html);

        //Настройки drag&drop, меняются 3 части шаблона
        if ($this->drag_and_drop)
        {
            $html = str_replace("[#dnd_enabled#]", $this->template['dnd_enabled'], $html);
            $html = str_replace("[#dnd_action1#]", $this->template['dnd_action1'], $html);
            $html = str_replace("[#dnd_action2#]", $this->template['dnd_action2'], $html);
        }
        else
        {
            $html = str_replace("[#dnd_enabled#]", "", $html);
            $html = str_replace("[#dnd_action1#]", "", $html);
            $html = str_replace("[#dnd_action2#]", "", $html);
        }


        //$html = str_replace("[#context_menu#]"          , $html_context_menu         , $html);
        $html = str_replace("[#context_menu_functions#]", $html_context_menu_function, $html);

        //Обычный клик по ноде будет вызывать перегрузку сентрaльного блока
        $link = 'start_interface.link_go("'.$this->action_node.'&id=" + currNodeId)';
        //А вот если это работа со структурой, то всё сложнее, так как нужно только обновить
        //уже загруженную форму
        //if ($this->is_page_structure)//@todo uncomment?
        //    $link = 'structure_tree_click_node("'.$this->action_node.'&id=" + currNodeId)';

        $html = str_replace("[#linkmenu#]"  , $link, $html);

        $html = str_replace("[#click_node_default#]" , $def_link                      , $html);//@todo remove?
        $html = str_replace("%cookie_name_tree%"     , "name_".$this->name_for_cookie , $html);



        //И последнее, вставим признак необходимости выставлять дефолтную ноду, или ноду из кук
        //Это можно делать только в том случае, если текущего пункта левого меню нет вообще
        //или если он взять именно из дефолтного, тоесть не выбран
        if ($this->prioritet_node_and_other_menu)
            $html = str_replace("%nod_select_node%" , "true" , $html);
        else
        {
            if (!($kernel->pub_section_leftmenu_get() == $this->action_node))
                $html = str_replace("%nod_select_node%" , "true" , $html);
            else
                $html = str_replace("%nod_select_node%" , "false" , $html);
        }

        return $html;
    }

    function get_id()
    {
        return "divtree_".$this->root_id;
    }

}


?>