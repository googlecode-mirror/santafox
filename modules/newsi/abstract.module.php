<?php
/**
 * Абстрактный класс модуля
 *
 * @author Александр Ильин mecommayou@gmail.com
 * @version 1.0.1 beta
 *
 */
abstract class module {

    /**
     * Содержит массив распаршенных шаблонов
     *
     * @var array
     */
    protected $templates = array();

    /**
     * Действие по умолчанию
     *
     * @var string
     */
    protected $action_default = '';

    /**
     * Название перемнной в GET запросе определяющей действие
     *
     * @var string
     */
    protected $action_name = 'view';

    /**
     * Префикс путей к шаблонам административного интерфейса
     *
     * @var string
     */
    protected $templates_admin_prefix = '';

    /**
     * Префикс путей к шаблонам пользовательского интерфейса
     *
     * @var string
     */
    protected $templates_user_prefix = '';

    /**
     * Возвращает имя переменной GET запроса определяющей действие
     *
     * @return string
     */
    protected function get_action_name()
    {
    	return $this->action_name;
    }

    /**
     * Возвращает значение указанного действия, если установленно или значение по умолчанию
     *
     * @param string $action_name Имя параметра в GET запросе
     * @return string
     */
    protected function get_action_value($action_name)
    {
        global $kernel;

        if ($kernel->pub_httpget_get($action_name))
        {
            return $kernel->pub_httpget_get($action_name);
        }
        else
        {
            return $this->action_default;
        }
    }

    /**
     * Возвращает указанный блок шаблона
     *
     * @param string $block_name Имя блока
     * @return mixed
     */
    protected function get_template_block($block_name)
    {
        return ((isset($this->templates[$block_name]))?(trim($this->templates[$block_name])):(null));
    }

    /**
     * Устанавливает действие по умолчанию
     *
     * @param string $value Имя GET параметра определяющего действие
     */
    protected function set_action_default($value)
    {
        $this->action_default = $value;
    }

    /**
     * Устанавливает имя переменной в GET запросе определяющей действие
     *
     * @param string $name
     */
	protected function set_action_name($name)
	{
        $this->action_name = $name;
	}

    /**
     * Устанавливает шаблоны
     *
     * @param array $templates Массив распаршенных шаблонов
     */
    protected function set_templates($templates)
    {
        $this->templates = $templates;

    }

    /**
     * Возвращет префикс путей к шаблонам административного интерфейса
     *
     * @return string
     */
    protected function get_templates_admin_prefix()
    {
    	return $this->templates_admin_prefix;
    }

    /**
     * Устанавливает префикс к шаблонам админки
     *
     * @param string $prefix
     */
    protected function set_templates_admin_prefix($prefix)
    {
        $this->templates_admin_prefix = $prefix;
    }

    /**
     * Возвращет префикс путей к шаблонам пользовательского интерфейса
     *
     * @return string
     */
    protected function get_templates_user_prefix()
    {
    	return $this->templates_user_prefix;
    }

    /**
     * Устанавливает префикс путей к шаблонам пользовательского интерфейса
     *
     * @param string $prefix
     */
    protected function set_templates_user_prefix($prefix)
    {
    	$this->templates_user_prefix = $prefix;
    }

    /**
     * Функция для построения меню для административного интерфейса
     *
     * @param object $menu Обьект класса для управления помтроеним меню
     * @return boolean
     */
	abstract public function interface_get_menu($menu);

	/**
	 * Функция для отображаения административного интерфейса
	 *
	 * @return string
	 */
	abstract function start_admin();
}