<?php

abstract class BaseModule
{
    /**
     * parsed template
     * @var array
     */
    public $templates = array();

    public function __construct()
    {

    }

    /**
	 * Функция для отображения административного интерфейса
	 *
	 * @return string
	*/
    abstract public function start_admin();

    /**
     * Функция для построения меню для административного интерфейса
     *
     * @param pub_interface $menu Обьект класса для управления построением меню
     * @return boolean true
     */
    abstract public function interface_get_menu($menu);

    /**
     * Устанавливает шаблоны
     *
     * @param array $templates Массив распаршенных шаблонов
     */
    public function set_templates($templates)
    {
        $this->templates = $templates;
    }

    /**
     * Возвращает указанный блок шаблона
     *
     * @param string $block_name Имя блока
     * @return mixed
     */
    public function get_template_block($block_name)
    {
        return isset($this->templates[$block_name]) ? $this->templates[$block_name] : null;
    }

    /**
     * Возвращает указанный блок шаблона с учётом глубины
     *
     * @param string $block_name Имя блока
     * @param integer $depth глубина
     * @return mixed
     */
    public function get_template_block_with_depth($block_name, $depth)
    {
        if (!isset($this->templates[$block_name]))
            return null;

        if (is_array($this->templates[$block_name]))
        {
            $arr_size = count($this->templates[$block_name]);
            if ($arr_size > $depth)
                return $this->templates[$block_name][$depth];
            else
                return $this->templates[$block_name][$arr_size-1];
        }
        else
            return $this->templates[$block_name];
    }


    /**
     * Удаляет оставшиеся метки %label% в тексте шаблонов
     *
     * @param string $str
     * @return string
     */
    public function clear_left_labels($str)
    {
    	return preg_replace("/\\%([a-z0-9_-]+)\\%/i","", $str);
    }

    /**
     *  Строит блок постраничной навигации
     * @param integer $total общее кол-во элементов в выборке
     * @param integer $offset смещение
     * @param integer $perpage кол-во элементов на страницу
     * @param string $q префикс-строка для урлов страниц
     * @param integer $maxpages макс. кол-во страниц в блоке, если 0, то нет ограничения
     * @param string $linkLabelName название метки, в которой проставляется ссылка (в разных модулях по-разному)
     * @return string
     */
    public function build_pages_nav($total, $offset, $perpage, $q, $maxpages=0, $linkLabelName="link")
    {
        //Строим постраничную навигацию только тогда, когда это нужно
        if ($total<=$perpage)
            return $this->get_template_block('pages_null');
        $pages_count = ceil($total/$perpage);
        $currpage = ceil($offset/$perpage)+1;
        if ($currpage<1 || $currpage>$pages_count)
            $currpage=1;

        if ($maxpages)
        {
            $startBlockPage=$currpage-floor($maxpages/2);
            if ($startBlockPage<1)
                $startBlockPage=1;
            $finishBlockPage=$startBlockPage+$maxpages-1;
            if ($finishBlockPage>$pages_count)
                $finishBlockPage=$pages_count;
        }
        else
        {
            $startBlockPage = 1;
            $finishBlockPage = $pages_count;
        }

        $pblock = $this->get_template_block('pages');
        if ($currpage>1)
        {
            $previous = str_replace('%'.$linkLabelName.'%', $q.($currpage-2)*$perpage, $this->get_template_block('page_previous'));
            $first = str_replace('%'.$linkLabelName.'%', $q.'0', str_replace('%page_num%',1,$this->get_template_block('page_first')));
        }
        else
        {
            $previous = $this->get_template_block('page_previous_disabled');
            $first = $this->get_template_block('page_first_disabled');
        }
        $pblock = str_replace('%first%', $first, $pblock);
        $pblock = str_replace('%previous%', $previous, $pblock);

        if ($startBlockPage>1)
            $backward = str_replace('%'.$linkLabelName.'%', $q.(($startBlockPage-2)*$perpage), $this->get_template_block('page_backward'));
        else
            $backward = $this->get_template_block('page_backward_disabled');
        $pblock = str_replace('%backward%', $backward, $pblock);

        if ($currpage<$pages_count)
        {//есть ли страницы дальше?
            $next = str_replace('%'.$linkLabelName.'%', $q.($currpage*$perpage), $this->get_template_block('page_next'));
            $last = str_replace('%'.$linkLabelName.'%', $q.(($pages_count-1)*$perpage), str_replace('%page_num%',$pages_count,$this->get_template_block('page_last')));
        }
        else
        {
            $next = $this->get_template_block('page_next_disabled');
            $last = $this->get_template_block('page_last_disabled');
        }
        $pblock = str_replace('%last%', $last, $pblock);
        $pblock = str_replace('%next%', $next, $pblock);

        if ($finishBlockPage<$pages_count)
            $forward = str_replace('%'.$linkLabelName.'%',  $q.($finishBlockPage*$perpage), $this->get_template_block('page_forward'));
        else
            $forward = $this->get_template_block('page_forward_disabled');
        $pblock = str_replace('%forward%', $forward, $pblock);


        $pages = array();
        for ($p=$startBlockPage;$p<=$finishBlockPage;$p++)
        {
            $currOffset=($p-1)*$perpage;
            if ($currOffset == $offset)
                $page = $this->get_template_block('page_passive');
            else
                $page = $this->get_template_block('page_active');
            $page = str_replace('%'.$linkLabelName.'%', $q.$currOffset, $page);
            $page = str_replace('%page%', $p, $page);
            $pages[] = $page;
        }
        return  str_replace('%pages_block%', implode($this->get_template_block('page_delimeter'), $pages), $pblock);
    }
}
?>