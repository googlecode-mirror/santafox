<?php
/**
 * Модуль "Комментарии"
 *
 * @author Александр Ильин mecommayou@gmail.com
 * @copyright ArtProm (с) 2001-2008
 * @name comments
 * @version 1.0 beta
 *
 */
class comments
{
    /**
     * Действие по умолчанию
     *
     * @var string
     */
    private $action_default = 'form_show';

    const ADMNAME = 'Админ';//добавлено aim ... админ сайта, от чьего имени будут добавлятся комменты из админки
	                        //Измените по своему усмотрению

    /**
     * Имя параметра при успешной публикации
     *
     * @var string
     */
    private $publish_success_param = 'published_successfully';

    /**
     * Имя параметра при публикации, которая должна быть промодерирована админом
     *
     * @var string
     */
    private $publish_2moderate_param = 'published_need_approve';

    /**
     * Название перемнной в GET запросе определяющей действие
     *
     * @var string
     */
    private $action_name = 'view';
    /**
     * Префикс путей к шаблонам административного интерфейса//
     *
     * @var string
     */
    private $templates_admin_prefix = '';

    /**
     * Префикс путей к шаблонам пользовательского интерфейса
     *
     * @var string
     */
    private $templates_user_prefix = '';

    /**
     * Массив частей шаблона
     * @var array
     */
    private $templates=array();

    public function comments()
    {
    	global $kernel;
    	if ($kernel->pub_httpget_get('flush'))
            $kernel->pub_session_unset();
    }

    function pub_show_selection($template)
    {
        global $kernel;
        $this->set_templates($kernel->pub_template_parse($template));
        $content = $this->get_template_block('form');
        $content = str_replace('%url%', $kernel->pub_page_current_get().'.html', $content);
        $content = str_replace('%date_alone_name%', 'date', $content);
        $content = str_replace('%date_start_name%', 'start', $content);
        $content = str_replace('%date_stop_name%', 'stop', $content);
        return $content;
    }


    private function priv_generate_captcha_part($content)
    {
        global $kernel;
        $iscaptcha = $kernel->pub_modul_properties_get('showcaptcha');

        if ($iscaptcha['value']=='true')
            $content = str_replace('%form_captcha%', $this->get_template_block('captcha'), $content);
        else
            $content = str_replace('%form_captcha%', '', $content);
        return $content;
    }


    /*********************************************************
     * Публичное действие для отображения комментариев
     *
     * @param string $template Путь к файлу с шаблонами
     * @param numeric $limit Количество выводимых комментариев
     * @param string $type Тип отбора комментариев для вывода
     * @return string
     *///изменено aim
     public function pub_show_comments($template, $limit, $type, $httpparams, $no_parent)
     {
        global $kernel;
        $this->set_templates($kernel->pub_template_parse($template));
		$content='';

		if (strlen(trim($httpparams))>0)
        {
            if ($no_parent == 'no')
            {
                $http_param = $kernel -> pub_httpget_get($httpparams);
                if (empty($http_param))
                    $condition = 'hidden';
                else
                    $condition = 'visible';
            }
            else
                $condition = 'visible';
		}
        else
            $condition = 'visible';

		switch($condition)
		{
            case 'hidden':
                $content = $content.$this->get_template_block('comment_no');
                break;

            case 'visible':
                $content = $content.$this->get_template_block('header');
                $items = $this->pub_items_get($limit, $this->pub_offset_get($limit,'comments_block', $httpparams), $type, $httpparams);
                if (empty($items))
                   $content = $content.$this->get_template_block('no_data');
                else
                {
                      $content = $content.$this->get_template_block('totals');
                      $content = str_replace('%totals%', $this->pub_comments_avaiable_get($httpparams), $content);
                }
                $content = $content.$this->get_template_block('comment_content');

                $content = str_replace('%show_comments%', $this->priv_show_comments($items,$limit,$httpparams), $content);
                $content = str_replace('%show_form%', $this->priv_show_form($httpparams), $content);
                break;
		}
	    return $content;
     }

//добавлено aim
	private function priv_show_comments($items, $limit, $httpparams)
	{
	    global $kernel;
	    $this->set_templates($kernel->pub_template_parse('modules/comments/templates_user/show_comments.html'));
		$content = '';
		if (!empty($items))
		    {
		        $lines = '';
				foreach ($items as $item)
                {
                    $line = $this->get_template_block('rows');
                    $line = str_replace('%num%', $item['num'], $line);
                    $line = str_replace('%date%', $item['date'], $line);
                    $line = str_replace('%time%', $item['time'], $line);
					$line = str_replace('%txt%', $item['txt'], $line);
                    $line = str_replace('%author%', (($item['author']==comments::ADMNAME)?('<span style="color:blue">'.$item['author'].'</span>'):($item['author'])), $line);
                    $lines .= $line;
                }
				$content = $content.$this->get_template_block('content');
				$content = str_replace('%rows%', $lines, $content);
				$content = $content.$this->get_template_block('pages');
		        $content = str_replace('%pages%', $this->pub_pages_get_block($limit, $this->pub_offset_get($limit,'comments_block',$httpparams), $httpparams), $content);
		    }
		return $content;
	}

//добавлено aim
	private function priv_show_form($httpparams)
	{
	    global $kernel;
		$this->set_action_default('form_show');
		$this->set_templates($kernel->pub_template_parse('modules/comments/templates_user/show_form.html'));
		$content = '';
		$content = $content.$this->get_template_block('get_form');
		switch ($this->get_action_value($this->get_action_name()))
        {
            default:
            case 'form_show':
                if (strlen($kernel->pub_httpget_get($this->publish_success_param))>0)
                    $content = $content.$this->get_template_block('processing_success');
                else if (strlen($kernel->pub_httpget_get($this->publish_2moderate_param))>0)
                    $content = $content.$this->get_template_block('need_admin_approve');
                $content = $content.$this->get_template_block('form');
                $content = str_replace('%user_txt%', '', $content);
                $content = str_replace('%user_name%', '', $content);
                $content = $this->priv_generate_captcha_part($content);
              break;

            // Обработаем данные введенные пользователем
            case 'form_processing':
                $cmnt_name = trim($kernel->pub_httppost_get('cmnt_name', false));
                $cmnt_txt = $kernel->pub_httppost_get('cmnt_txt', false);
                $aval=1;
                $addok=true;
                $this->priv_session_user_set($cmnt_name, $cmnt_txt);

                //простейшая проверка на заполнение всех полей формы
                if ($cmnt_name =='' || $cmnt_txt =='')
                {
                    $addok=false;
                    $content = $content.$this->get_template_block('fields_not_filled');
                }

                //защита от добавления коммента от имени админа сайта //добавлено aim
                if ($cmnt_name == comments::ADMNAME)
                {
                    $addok=false;
                    $content = $content.$this->get_template_block('no_admin_comments');
                    $kernel->pub_session_unset('cmnt', $kernel->pub_module_id_get());
                }

                $iscaptcha = $kernel->pub_modul_properties_get('showcaptcha');
                $ispremod = $kernel->pub_modul_properties_get('premod');
                if ($ispremod['value']=='true')
                    $aval=0;
                if ($iscaptcha['value']=='true')
                {
                    $cmnt_code = $kernel->pub_httppost_get('cmnt_captcha');
                    require_once('php-captcha.inc.php');
                    if (!PhpCaptcha::Validate($cmnt_code))
                    {
                        $content = $content.$this->get_template_block('badcaptcha').$cmnt_code;
                        $addok=false;
                    }
                }
                //начало общего блока вывода формы и капчи //добавлено aim
                $content = $content.$this->get_template_block('form');
                $val = $kernel->pub_session_get('cmnt');
                $user_name = $val['user_name'];
                $user_txt = $val['user_txt'];
                $content = str_replace('%user_txt%', $user_txt, $content);
                $content = str_replace('%user_name%', $user_name, $content);
                $content=$this->priv_generate_captcha_part($content);
                //$kernel->pub_session_unset('cmnt', $kernel->pub_module_id_get());
                //конец общего блока

                if ($addok)
                {
                    $page_sub_id='""';
                    $httpLink = "http://".$_SERVER['HTTP_HOST']."/".$kernel->curent_page.".html?";
                    if (strlen(trim($httpparams))>0)
                    {
                        $page_sub_id='"';
                        $params = explode(',',$httpparams);
                        foreach ($params as $param)
                        {
                            $httpLink.= $param.'='.urlencode($kernel->pub_httpget_get($param,false))."&";
                            $page_sub_id=$page_sub_id.$param.'='.$kernel->pub_httpget_get($param).',';
                        }
                        $page_sub_id.='"';
                    }

                    $sql = 'INSERT INTO `'.$kernel->pub_prefix_get().'_comments` (page_id,page_sub_id,module_id,txt,author,available,`date`,`time`)'.
                            ' VALUES ("'.$kernel->curent_page.'",'.$page_sub_id.', "'.$kernel->pub_module_id_get().'","'.mysql_real_escape_string(htmlspecialchars($cmnt_txt)).'","'.mysql_real_escape_string(htmlspecialchars($cmnt_name)).'",'.$aval.',CURDATE(),CURTIME());';

                    $kernel->runSQL($sql);
                    $admEmailProp = $kernel->pub_modul_properties_get('comments_admin_email');
                    if (!empty($admEmailProp['value']))
                    {
                        $host = $_SERVER['HTTP_HOST'];
                        $host = preg_replace("/^www\\./i","", $host);
                        $fromname = $host;
                        $fromaddr = "noreply@".$host;
                        $subj = "Новый комментарий";
                        if ($aval!=1)
                            $subj.= " требующий модерации";
                        $body = "<html><body><a href='".$httpLink."'>Ссылка</a><br>Имя: ".htmlspecialchars($cmnt_name)."<br>Текст: ".htmlspecialchars($cmnt_txt)."</body></html>";
                        $kernel->pub_mail(array($admEmailProp['value']), array("admin"),$fromaddr, $fromname, $subj,$body);
                    }
                    $redirUrl=$_SERVER["REQUEST_URI"];
                    if (strpos($redirUrl,"?")===FALSE)
                        $redirUrl.="?";
                    else
                        $redirUrl.="&";
                    if ($aval==1)
                        $redirUrl=$redirUrl.$this->publish_success_param."=1";
                    else
                        $redirUrl=$redirUrl.$this->publish_2moderate_param."=1";
                    $kernel->priv_redirect_301($redirUrl);
                 }
                 break;
        }
	    return $content;
	}


//добавлено aim
	   function priv_session_user_set($value1, $value2)
	   {
	     global $kernel;
		   $sess_value = array();
	       $sess_value['user_name'] = $value1;
	       $sess_value['user_txt'] = $value2;
		   $kernel->pub_session_set('cmnt', $sess_value);
	   }

//добавлено aim
//постраничная навигация
    function pub_pages_get_block($limit, $offset = 0, $httpparams)
    {
        global $kernel;
        $total = $this->pub_comments_avaiable_get($httpparams);
        if ($total <= $limit)
            return $this->get_template_block('page_no');
		$pages = ceil($total / $limit);
        if ($pages == 1)
        	return '';
        $content = array();
        for ($page = 0; $page < $pages; $page++)
        {
            if (strlen(trim($httpparams))>0)
			{
			    $params = explode(',',$httpparams);
			    foreach ($params as $param)
                {
                    $page_sub_id = $param.'='.$kernel->pub_httpget_get($param).'&';
                }
			    $link = $kernel->curent_page.'.html?'.$page_sub_id.'comments_block='.$limit * $page;
			}
			else
			    $link = $kernel->curent_page.'.html?comments_block='.$limit * $page;
            $content[] = str_replace(array('%link%', '%text%'), array($link, ($page+1)), (($limit * $page == $offset)?($this->get_template_block('page_passive')):($this->get_template_block('page_active'))));
        }
        $content = implode($this->get_template_block('delimeter'), $content);
        return $content;
    }

//изменено aim
    function pub_items_get($limit, $offset = 0, $type = null, $httpparams='')
    {
        global $kernel;
        $items = array();
        $where = array();
        $order = array();

        $where[] = '`comments`.`module_id` = "'.$kernel->pub_module_id_get().'"';
        $where[] = '`comments`.`available` = 1';
        $where[] = '`comments`.`page_id` = "'.$kernel->curent_page.'"';
        if (!empty($httpparams))
        {
            $params = explode(",",$httpparams);
            $wstr='';
            foreach ($params as $param)
            {
                $wstr=$wstr.$param.'='.$kernel->pub_httpget_get($param).',';
            }
            $where[] = '`comments`.`page_sub_id` = "'.$wstr.'"';
        }

        switch ($type)
        {
        	case 'new_at_top':
                $order[] = '`comments`.`date` DESC';
                $order[] = '`comments`.`time` DESC';
        		break;

          case 'new_at_bottom':
                $order[] = '`comments`.`date` ASC';
                $order[] = '`comments`.`time` ASC';
        		break;

        	default:
                $order[] = '`comments`.`date` DESC';
                $order[] = '`comments`.`time` DESC';
        		break;
        }

        $query = 'SELECT `id` , DATE_FORMAT(`date`, "%d-%m-%Y") AS `date` , `time` , `txt` , `author` '
        . ' FROM `'.$kernel->pub_prefix_get().'_comments` AS `comments` '
        . ' WHERE '.implode(' AND ', $where).' ORDER BY '.implode(', ', $order).' LIMIT '.$limit.' OFFSET '.$offset;

        $result = $kernel->runSQL($query);

	    $num = '';
	    if ($type == "new_at_top" or $type == "default")
            $num = $this->pub_comments_avaiable_get($httpparams);
	    elseif ($type == "new_at_bottom")
            $num = 1;
	    while ($row = mysql_fetch_assoc($result))
        {
            if ($type == "new_at_top" or $type == "default")
                $nums = ($num--) - $offset;
            elseif ($type == "new_at_bottom")
                $nums = $offset + $num++;
            $row['num']=$nums;
            $items[] = $row;
        }
	    return $items;
	}

    function pub_offset_get($limit, $offset_name = 'comments_block', $httpparams)
    {
        global $kernel;
    	$offset = $kernel->pub_httpget_get($offset_name);
    	if (!is_numeric($offset) || ($offset > ($this->pub_comments_avaiable_get($httpparams))))
    	    $offset = 0;
    	return $offset;
    }

//изменено aim
    function pub_comments_avaiable_get($httpparams)
    {
        global $kernel;
        $where = array();
        $where[] = '`module_id` = "'.$kernel->pub_module_id_get().'"';
        $where[] = '`available` = 1';
		$where[] = '`page_id` = "'.$kernel->curent_page.'"';
		if (!empty($httpparams))
        {
            $params = explode(",",$httpparams);
            $wstr='';
            foreach ($params as $param)
            {
                $wstr=$wstr.$param.'='.$kernel->pub_httpget_get($param).',';
            }
            $where[] = '`page_sub_id` = "'.$wstr.'"';
        }

        $query = 'SELECT COUNT(*) AS `total` FROM `'.$kernel->pub_prefix_get().'_comments` WHERE '.implode(' AND ', $where).'';
        $total = mysql_result($kernel->runSQL($query), 0, 'total');
        return $total;
    }

    //админка
    /**
     * Функция для построения меню для административного интерфейса
     *
     * @param pub_interface $menu Обьект класса для управления помтроеним меню
     * @return boolean
     */
	public function interface_get_menu($menu)
	{
        $menu->set_menu_block('[#comments_menu_label#]');
        $menu->set_menu("[#comments_menu_show_list#]","show_list", array('flush' => 1));
        $menu->set_menu("[#comments_menu_add_new#]","show_add", array('flush' => 1));
        $menu->set_menu("[#comments_menu_between#]","select_between", array('flush' => 1));
        $menu->set_menu("[#comments_menu_notmoderated#]","select_notmoderated", array('flush' => 1));
        $menu->set_menu_block('[#comments_menu_label1#]');
        $menu->set_menu_plain($this->priv_show_date_picker());
        $this->priv_show_date_picker();
        $menu->set_menu_default('show_list');
	    return true;
	}

	function priv_show_date_picker()
	{
	    global $kernel;
	    $this->set_templates_admin_prefix('modules/comments/templates_admin/');
        $this->set_templates($kernel->pub_template_parse($this->get_templates_admin_prefix().'date_picker.html'));
        $content = $this->get_template_block('date_picker');
        return $content;
	}

	/**
	 * Функция для отображаения административного интерфейса
	 *
	 * @return string
	 */
    public function start_admin()
    {
        global $kernel;

        $this->set_templates_admin_prefix('modules/comments/templates_admin/');
        switch ($kernel->pub_section_leftmenu_get())
        {
            default:
        	case 'show_list':
                return $this->priv_show_list($this->priv_get_limit_admin(), $this->priv_get_offset(), $this->priv_get_field(), $this->priv_get_direction(), $this->priv_get_start(), $this->priv_get_stop(), $this->priv_get_date());
        		break;

        	case 'select_between':
                $this->set_templates($kernel->pub_template_parse($this->get_templates_admin_prefix().'select_between.html'));
                $content = $this->get_template_block('form');
                $content = str_replace('%form_action%', $kernel->pub_redirect_for_form('test_select_between'), $content);
                $content = str_replace('%form_action_sucsess%', 'admin/index.php?action=set_left_menu&leftmenu=show_list', $content);
                return $content;
                break;

            case 'select_notmoderated':
               return $this->priv_show_list($this->priv_get_limit_admin(), $this->priv_get_offset(), $this->priv_get_field(), $this->priv_get_direction(), $this->priv_get_start(), $this->priv_get_stop(), $this->priv_get_date(),true);
               break;

        	case 'test_select_between':
        	    return '{success: true}';
        	    break;

            case 'show_edit':
                return $this->show_item_form($kernel->pub_httpget_get('id'));
                break;

            case 'show_add':
                return $this->show_item_form();
                break;

            case 'item_save':
                $values = $kernel->pub_httppost_get('values');
                $values['description_full'] = $kernel->pub_httppost_get('content_html');
                $this->priv_item_save($values);
                $kernel->pub_redirect_refresh_reload('show_list');
                break;

            case 'item_remove':
                $this->priv_item_delete($kernel->pub_httpget_get('id'));
                $kernel->pub_redirect_refresh('show_list');
//                $kernel->pub_redirect_refresh_reload('show_list');
                break;

            case 'list_actions':
                $this->priv_items_do_action($kernel->pub_httppost_get('action'), $kernel->pub_httppost_get('items'));
                $kernel->pub_redirect_refresh_reload('show_list');
                break;

            case 'show_between':
                break;
        }

        return (isset($content)?$content:null);
    }

    function priv_items_do_action($action, $items)
    {
        global $kernel;
        if (empty($items))
            return false;
        switch ($action)
        {
        	case 'available_on':
        	    $query = 'UPDATE `'.$kernel->pub_prefix_get().'_comments` SET `available` = "1" WHERE `id` IN ('.implode(', ', $items).')';
        	    $kernel->runSQL($query);
        		break;
        	case 'available_off':
        	    $query = 'UPDATE `'.$kernel->pub_prefix_get().'_comments` SET `available` = "0" WHERE `id` IN ('.implode(', ', $items).')';
        	    $kernel->runSQL($query);
        		break;
        	case 'delete':
        	    $query = 'DELETE FROM `'.$kernel->pub_prefix_get().'_comments` WHERE `id` IN ('.implode(', ', $items).')';
        	    $kernel->runSQL($query);
        		break;
            default:
        	    $query = 'UPDATE `'.$kernel->pub_prefix_get().'_comments` SET `module_id` = "'.$action.'" WHERE `id` IN ('.implode(', ', $items).')';
        	    $kernel->runSQL($query);
        		break;
        }
        return mysql_affected_rows();
    }

    function priv_item_delete($item_id)
    {
        global $kernel;
        $query = 'DELETE FROM `'.$kernel->pub_prefix_get().'_comments` WHERE `id` = '.$item_id.'';
        $kernel->runSQL($query);
    }

    private function priv_item_save($item_data)
    {
        global $kernel;
        list($day, $month, $year) = explode('-', $item_data['date']);
//        if (preg_match('/^\d{1,2}:\d{1,2}:\d{1,2}$/', trim($item_data['time'])) && checkdate($month, $day, $year)) {
            $query = 'REPLACE `'.$kernel->pub_prefix_get().'_comments` (`id`, `module_id`, `date`, `time`, `available`, `txt`, `author`,`page_id`,`page_sub_id`) '
            . ' VALUES ('.$item_data['id'].',"'.$kernel->pub_module_id_get().'","'.$year.'-'.$month.'-'.$day.'","'.$item_data['time'].'","'.((isset($item_data['available']))?(1):(0)).'","'.$item_data['txt'].'","'.$item_data['author'].'","'.$item_data['page_id'].'","'.$item_data['page_sub_id'].'")';
            $kernel->runSQL($query);
//        }
    }


//изменено aim
    private function show_item_form($item_id = null)
    {
       	global $kernel;
        $this->set_templates($kernel->pub_template_parse($this->get_templates_admin_prefix().'item_form.html'));
        $content = $this->get_template_block('form');
        $content = str_replace('%form_action%', $kernel->pub_redirect_for_form('item_save'), $content);
        $content = str_replace('%id%', ((is_numeric($item_id))?($item_id):('NULL')), $content);
        $item_data = $this->get_item_data($item_id);
        //Если это ввод новой новости, то надо добавить значение текущего времени и даты
        if ($item_id == null)
        {
          $content = str_replace('%time%',         date('H:i:s'), $content);
          $content = str_replace('%date%',         date('d-m-Y'), $content);
		  $select_page = $this->priv_get_item_select_page();
		  $res = $this->priv_select_page_id();
		  $sub_select_page = '';
		     while($row = mysql_fetch_assoc($res))
              {
		        $sub_select_page .= $this->priv_select_page_sub_id($row['page_id']);
              }
			  $sub_select_page = substr(trim($sub_select_page),0,-1);

		  $content = str_replace('%rows%', $this->get_template_block('select'), $content);
		  $content = str_replace('%select_page%', $select_page, $content);
		  $content = str_replace('%sub_select_page%', $sub_select_page, $content);
		} else {
		$content = str_replace('%rows%', $this->get_template_block('page_info'), $content);
		$info = '<b style="padding-left:30px">'.'%page_id%'.'  '.'%page_sub_id%'.'<input type="hidden" name="values[page_id]" value='.'%page_id%'.'><input type="hidden" name="values[page_sub_id]" value='.'%page_sub_id%'.'>';
		$content = str_replace('%page_info%', $info, $content);
	   }
        $content = str_replace($this->priv_get_item_data_search(), $this->priv_get_item_data_replace($item_id), $content);
      return $content;
    }

    //добавлено aim
	//функция по формированию 1-ого списка со страницами добавления комментов (page_id)
	function priv_get_item_select_page()
    {
	    global $kernel;
		$res = $this->priv_select_page_id();
		$select = '';
		while($row = mysql_fetch_assoc($res))
          {
		   $select .= '<option value="'.$row['page_id'].'">'.$row['page_id'].'</option>'."\n";
          }
	    return $select;
	}

	//добавлено aim
	function priv_select_page_id()
    {
	    global $kernel;
		$query = 'SELECT DISTINCT `page_id` FROM `'.$kernel->pub_prefix_get().'_comments` WHERE module_id="'.$kernel->pub_module_id_get().'"';
	    return $kernel->runSQL($query);
	}

	//добавлено aim
	//функция формирования куска JS-кода для вывода 2-ого связанного списка page_sub_id в шаблоне item_form.html
	function priv_select_page_sub_id($page_id)
    {
	    global $kernel;
		$query = 'SELECT DISTINCT `page_sub_id` FROM `'.$kernel->pub_prefix_get().'_comments` WHERE module_id="'.$kernel->pub_module_id_get().'" AND page_id="'.$page_id.'"';
	    $res = $kernel->runSQL($query);
		$sub_select = '';
		while($row = mysql_fetch_assoc($res))
        {
		    $sub_select .= '"'.$row['page_sub_id'].'":"'.(($row['page_sub_id'] == '')?('без параметра(ов)'):(substr($row['page_sub_id'],0,-1))).'",';
        }
		$sub_select = '"'.$page_id.'":{'.substr(trim($sub_select),0,-1).'},';
		return $sub_select;
	}

//
    function priv_get_item_data_replace($item_id)
    {
        $item_data = $this->get_item_data($item_id);
        if (empty($item_data))
        {
            return array(
                '',
                '',
                'checked',
                '',
                comments::ADMNAME,//добавлено aim
                '',
                ''
            );
        }
        else
        {
        	return array(
        	   $item_data['date'],
        	   $item_data['time'],
        	   ($item_data['available'] == 1)?('checked'):(''),
        	   $item_data['txt'],
        	   $item_data['author'],
               $item_data['page_id'],
               $item_data['page_sub_id']
			 );
        }
    }

    function priv_get_item_data_search()
    {
    	$array = array(
    	   '%date%',
    	   '%time%',
    	   '%available%',
    	   '%txt%',
    	   '%author%',
           '%page_id%',
           '%page_sub_id%'
    	);
    	return $array;
    }

    /**
     * Возвращает данные по указанному ID
     *
     * @param integer $item_id
     * @return array
     */
    private function get_item_data($item_id)
    {
        global $kernel;
        if (!is_numeric($item_id))
            return array();
        $query = 'SELECT * FROM `'.$kernel->pub_prefix_get().'_comments` WHERE `id` = '.$item_id.' ';
        $array = mysql_fetch_assoc($kernel->runSQL($query));
        //Дату необходимо вернуть в формате ДД-ММ-ГГГГ
        $array['date'] = substr($array['date'],-2,2)."-".substr($array['date'],5,2)."-".substr($array['date'],0,4);
        return $array;
    }


    private function priv_get_limit_admin()
    {
    	global $kernel;
    	$property = $kernel->pub_modul_properties_get('comments_per_page_admin');
    	if ($property['isset'] && is_numeric($property['value']))
    	    return $property['value'];
    	else
    	    return 10;
    }


    /**
     * Возвращает текущий сдвиг
     *
     * @return integer
     */
    private function priv_get_offset()
    {
        global $kernel;
        $offset = $kernel->pub_httpget_get('offset');
        if (trim($offset) == '')
            $offset = $kernel->pub_session_get('offset');
    	if (!is_numeric($offset))
    	    $offset = 0;
    	$kernel->pub_session_set('offset', $offset);
    	return $offset;
    }

    private function priv_get_direction()
    {
    	global $kernel;
    	$direction = $kernel->pub_httpget_get('direction');
    	if (empty($direction))
            $direction = $kernel->pub_session_get('direction');
    	if (!in_array(strtoupper($direction), array('ASC', 'DESC')))
    	    $direction = 'ASC';
    	$kernel->pub_session_set('direction', $direction);
    	return $direction;
    }

    private function priv_get_field()
    {
        global $kernel;
        $query = 'SHOW COLUMNS FROM `'.$kernel->pub_prefix_get().'_comments`';
        $result = $kernel->runSQL($query);
        $fields = array();
        while ($row = mysql_fetch_assoc($result))
        {
            $fields[] = $row['Field'];
        }
        $field = $kernel->pub_httpget_get('field');
        if (empty($field))
        	$field = $kernel->pub_session_get('field');
        if (!in_array($field, $fields))
            $field = 'date';
        $kernel->pub_session_set('field', $field);
        return $field;
    }

    private function priv_get_start()
    {
        global $kernel;
        $start = $kernel->pub_httpget_get('start');
        if (empty($start))
        	$start = $kernel->pub_session_get('start');
        $kernel->pub_session_set('start', $start);
        return $start;
    }

    private function priv_get_stop()
    {
        global $kernel;
        $stop = $kernel->pub_httpget_get('stop');
        if (empty($stop))
        	$stop = $kernel->pub_session_get('stop');
        $kernel->pub_session_set('stop', $stop);
        return $stop;
    }

    private function priv_get_date()
    {
        global $kernel;
    	$date = $kernel->pub_httpget_get('date');
    	if (empty($date))
    		$date = $kernel->pub_session_get('date');
    	$kernel->pub_session_set('date', $date);
    	return $date;
    }


    function priv_offset_check($limit, $offset, $field, $direction, $start = null, $stop = null, $date = null)
    {
        global $kernel;

        if (!is_null($start) && !is_null($stop))
        {
        	$query = 'SELECT *, DATE_FORMAT(`date`, "%d-%m-%Y") AS `date_rus` FROM `'.$kernel->pub_prefix_get().'_comments` '
        	. ' WHERE `module_id` = "'.$kernel->pub_module_id_get().'" AND `date` BETWEEN "'.$start.'" AND "'.$stop.'"'
        	. ' ORDER BY `'.$field.'` '.$direction.' '
        	. ' LIMIT '.$limit.' OFFSET %offset%';
        }
        elseif (!is_null($date))
        {
        	$query = 'SELECT *, DATE_FORMAT(`date`, "%d-%m-%Y") AS `date_rus` FROM `'.$kernel->pub_prefix_get().'_comments` '
        	. ' WHERE `module_id` = "'.$kernel->pub_module_id_get().'" AND `date` = "'.$date.'"'
        	. ' ORDER BY `'.$field.'` %offset%';
        }
        else
        {
        	$query = 'SELECT *, DATE_FORMAT(`date`, "%d-%m-%Y") AS `date_rus` FROM `'.$kernel->pub_prefix_get().'_comments` '
        	. ' WHERE `module_id` = "'.$kernel->pub_module_id_get().'" '
        	. ' ORDER BY `'.$field.'` '.$direction.' '
        	. ' LIMIT '.$limit.' OFFSET %offset%';
        }
        $result = $kernel->runSQL(str_replace('%offset%', $offset, $query));
        while ($offset > 0 && mysql_num_rows($result) == 0)
        {
            $offset = $offset - $limit;
            $result = $kernel->runSQL(str_replace('%offset%', $offset, $query));
        }
        return $offset;
    }

    /**
     * Отображает список комментариев
     *
     * @param integer $limit Лимит новостей
     * @param integer $offset Сдвиг
     * @param string $field Поле для сортировки
     * @param string $direction НАправление сортировки
     * @return string
     */
    private function priv_show_list($limit, $offset, $field, $direction, $start = null, $stop = null, $date = null, $only_not_moderated=false)
    {
        global $kernel;
        $this->set_templates($kernel->pub_template_parse($this->get_templates_admin_prefix().'show_list.html'));
        if ($offset > 0)
        	$offset = $this->priv_offset_check($limit, $offset, $field, $direction, $start, $stop, $date);
        if (!is_null($start) && !is_null($stop))
        {
        	$query = 'SELECT *, DATE_FORMAT(`date`, "%d-%m-%Y") AS `date_rus` FROM `'.$kernel->pub_prefix_get().'_comments` '
        	. ' WHERE `module_id` = "'.$kernel->pub_module_id_get().'" AND `date` BETWEEN "'.$start.'" AND "'.$stop.'"'
        	. ' ORDER BY `'.$field.'` '.$direction.' '
        	. ' LIMIT '.$limit.' OFFSET '.$offset.' ';
        }
        elseif (!is_null($date))
        {
        	$query = 'SELECT *, DATE_FORMAT(`date`, "%d-%m-%Y") AS `date_rus` FROM `'.$kernel->pub_prefix_get().'_comments` '
        	. ' WHERE `module_id` = "'.$kernel->pub_module_id_get().'" AND `date` = "'.$date.'"'
        	. ' ORDER BY `'.$field.'` '.$direction.' ';
        }
        else if ($only_not_moderated==true)
        {
         $query = 'SELECT *, DATE_FORMAT(`date`, "%d-%m-%Y") AS `date_rus` FROM `'.$kernel->pub_prefix_get().'_comments` '
         . ' WHERE `module_id` = "'.$kernel->pub_module_id_get().'" AND `available`=0 '
         . ' ORDER BY `'.$field.'` '.$direction.' '
         . ' LIMIT '.$limit.' OFFSET '.$offset.' ';
        }
        else
        {
        	$query = 'SELECT *, DATE_FORMAT(`date`, "%d-%m-%Y") AS `date_rus` FROM `'.$kernel->pub_prefix_get().'_comments` '
        	. ' WHERE `module_id` = "'.$kernel->pub_module_id_get().'" '
        	. ' ORDER BY `'.$field.'` '.$direction.' '
        	. ' LIMIT '.$limit.' OFFSET '.$offset.' ';
        }

    	$result = $kernel->runSQL($query);

    	if ((mysql_num_rows($result) == 0))
        {
            return $this->get_template_block('no_data');
    	}

    	$lines = array();
    	$first_element_number = $offset+1;
        while ($row = mysql_fetch_assoc($result))
        {
            $line = $this->get_template_block('table_body');
            $line = str_replace('%number%', $first_element_number++, $line);
			$line = str_replace('%page_id%', $row['page_id'].'&nbsp;'.substr($row['page_sub_id'],0,-1), $line);//добавлено aim
            $line = str_replace('%id%', $row['id'], $line);
            $line = str_replace('%date%', $row['date_rus'], $line);
			$line = str_replace('%time%', $row['time'], $line);//добавлено aim
            $line = str_replace('%author%', (($row['author']==comments::ADMNAME)?('<span style="color:blue">'.$row['author'].'</span>'):($row['author'])), $line);//добавлено aim
            $line = str_replace('%available%', (($row['available'])?($this->get_template_block('on')):($this->get_template_block('off'))), $line);
            $line = str_replace('%txt%', $row['txt'], $line);
            $line = str_replace('%action_edit%', 'show_edit', $line);
            $line = str_replace('%action_remove%', 'item_remove', $line);
            $lines[] = $line;
        }

        $header  = $this->get_template_block('table_header');
        $header = str_replace('%img_sort_'.$field.'%', (($direction == 'ASC')?($this->get_template_block('img_sort_asc')):($this->get_template_block('img_sort_desc'))), $header);
        $header = preg_replace('/\%img_sort_\w+%/', '', $header);

        $content = $header.implode("\n", $lines).$this->get_template_block('table_footer');
        $content = str_replace('%form_action%', $kernel->pub_redirect_for_form('list_actions'), $content);

        $modules = $kernel->pub_modules_get('comments');
        $array = array();
        foreach ($modules as $module_id => $properties)
        {
            if ($module_id != $kernel->pub_module_id_get())
            {
        	   $array[$module_id] = $properties['caption'];
            }
        }
        if (count($modules) > 1)
        {
            $actions = array(
                '[#comments_actions_simple#]' => array(
                    'available_on' => '[#comments_show_list_action_available_on#]',
                    'available_off' => '[#comments_show_list_action_available_off#]',
                    'delete' => '[#comments_show_list_action_delete#]'
//                    'move' => '[#comments_show_list_action_move#]'
                ),
                '[#comments_actions_advanced#]' => $array
            );
            $content = str_replace('%actions%', $this->priv_show_html_select('action', $actions, array(), true), $content);
        }
        else
        {
            $actions = array(
                'available_on' => '[#comments_show_list_action_available_on#]',
                'available_off' => '[#comments_show_list_action_available_off#]',
                'delete' => '[#comments_show_list_action_delete#]'
            );
            $content = str_replace('%actions%', $this->priv_show_html_select('action', $actions), $content);
        }

        $content = str_replace('%pages%', (is_null($date)?($this->priv_show_pages($offset, $limit, $field, $direction, $date, $start, $stop)):('')), $content);
        $sort_headers = $this->priv_get_sort_headers($field, $direction, $kernel->pub_httpget_get('date'), $start, $stop);
        $content = str_replace(array_keys($sort_headers), $sort_headers, $content);
        return $content;
    }

    private function priv_get_sort_headers($field, $direction, $date = null, $start = null, $stop = null )
    {
        $url = 'show_list&offset=0&field=%field%&direction=%direction%';
        if (!empty($date))
        	$url .= '&date='.$date;
        elseif (!empty($start) && !empty($stop))
        	$url .= '&start='.$start.'&stop='.$stop;
        $array = array(//
            '%url_sort_id%' => (($field == 'id')?(str_replace(array('%direction%', '%field%'), array((strtoupper($direction) == 'ASC')?('DESC'):('ASC'), 'id'), $url)):(str_replace(array('%direction%', '%field%'), array((strtoupper($direction) == 'ASC')?('DESC'):('ASC'), 'id'), $url))),
            '%url_sort_date%' => (($field == 'date')?(str_replace(array('%direction%', '%field%'), array((strtoupper($direction) == 'ASC')?('DESC'):('ASC'), 'date'), $url)):(str_replace(array('%direction%', '%field%'), array('ASC', 'date'), $url))),
            '%url_sort_txt%' => (($field == 'txt')?(str_replace(array('%direction%', '%field%'), array((strtoupper($direction) == 'ASC')?('DESC'):('ASC'), 'txt'), $url)):(str_replace(array('%direction%', '%field%'), array('ASC', 'txt'), $url))),
            '%url_sort_available%' => (($field == 'available')?(str_replace(array('%direction%', '%field%'), array((strtoupper($direction) == 'ASC')?('DESC'):('ASC'), 'available'), $url)):(str_replace(array('%direction%', '%field%'), array('ASC', 'available'), $url))),
            '%url_sort_lenta%' => (($field == 'lenta')?(str_replace(array('%direction%', '%field%'), array((strtoupper($direction) == 'ASC')?('DESC'):('ASC'), 'lenta'), $url)):(str_replace(array('%direction%', '%field%'), array('ASC', 'lenta'), $url))),
            '%url_sort_rss%' => (($field == 'rss')?(str_replace(array('%direction%', '%field%'), array((strtoupper($direction) == 'ASC')?('DESC'):('ASC'), 'rss'), $url)):(str_replace(array('%direction%', '%field%'), array('ASC', 'rss'), $url))),
            '%url_sort_author%' => (($field == 'author')?(str_replace(array('%direction%', '%field%'), array((strtoupper($direction) == 'ASC')?('DESC'):('ASC'), 'author'), $url)):(str_replace(array('%direction%', '%field%'), array('ASC', 'author'), $url))),
        );
        return $array;
    }

    private function priv_show_pages($offset, $limit, $field, $direction, $date = null, $start = null, $stop = null)
    {
        global $kernel;
        $this->set_templates($kernel->pub_template_parse($this->get_templates_admin_prefix().'pages.html'));

        if (!empty($date))
            $query = 'SELECT COUNT(*) AS totalCount FROM `'.$kernel->pub_prefix_get().'_comments` WHERE `module_id` = "'.$kernel->pub_module_id_get().'" AND `date` = "'.$date.'"';
        elseif (!empty($start) && !empty($stop))
            $query = 'SELECT COUNT(*) AS totalCount FROM `'.$kernel->pub_prefix_get().'_comments` WHERE `module_id` = "'.$kernel->pub_module_id_get().'" AND `date` BETWEEN "'.$start.'" AND "'.$stop.'"';
        else
            $query = 'SELECT COUNT(*) AS totalCount FROM `'.$kernel->pub_prefix_get().'_comments` WHERE `module_id` = "'.$kernel->pub_module_id_get().'"';
        $total = mysql_result($kernel->runSQL($query), 0, 'totalCount');
        $pages = ceil($total / $limit);
        if ($pages == 1)
        	return '';
        $content = array();
        for ($page = 0; $page < $pages; $page++)
        {
            $url = 'show_list&offset='.$limit * $page.'&field='.$field.'&direction='.$direction;

            if (!empty($date))
            	$url .= '&date='.$date;
            elseif (!empty($start) && !empty($stop))
            	$url .= '&start='.$start.'&stop='.$stop;
            $content[] = str_replace(array('%url%', '%page%'), array($url, ($page+1)), (($limit * $page == $offset)?($this->get_template_block('page_passive')):($this->get_template_block('page'))));
        }
        $content = implode($this->get_template_block('delimeter'), $content);
        return $content;
    }

    /**
     * Возвращает html код select'a
     *
     * @param string $name
     * @param array $array
     * @param array $selected
     * @param boolean $optgruop
     * @param string $style
     * @param boolean $multiple
     * @param integer $size
     * @param boolean $disabled
     * @param string $adds
     * @return string
     */
    private function priv_show_html_select($name, $array, $selected = array(), $optgruop = false, $style = "", $multiple = false, $size = 1, $disabled = false, $adds = '')
    {
        $html_select = '<select id="'.$name.'" '.($multiple?'multiple="multiple"':'').' size="'.$size.'" name="'.$name.'" style="'.$style.'"'.($disabled?'disabled="disabled"':'').' class="text" '.$adds.'>'."\n";
        switch ($optgruop)
        {
            case false:
                foreach ($array as $option => $label)
                {
                    if (!is_null($selected) && in_array($option, $selected))
                        $html_select .= '<option value="'.$option.'" selected="selected"">'.htmlspecialchars($label).'</option>'."\n";
                    else
                        $html_select .= '<option value="'.$option.'">'.$label.'</option>'."\n";
                }
                break;

            case true:
                foreach ($array as $key => $value)
                {
                    $html_select .= '<optgroup label="'.$key.'">'."\n";
                    foreach ($value as $option => $label)
                    {
                        if (!is_null($selected) && in_array($option, $selected))
                            $html_select .= '<option value="'.$option.'" selected="selected" style="background-color: white;">'.htmlspecialchars($label).'</option>'."\n";
                        else
                            $html_select .= '<option value="'.$option.'">'.$label.'</option>'."\n";
                    }
                    $html_select .= '</optgroup>'."\n";
                }
            	break;
        }
        $html_select .= '</select>'."\n";
        return $html_select;
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
    private function get_templates_admin_prefix()
    {
        return $this->templates_admin_prefix;
    }

    /**
     * Устанавливает префикс к шаблонам админки
     *
     * @param string $prefix
     */
    private function set_templates_admin_prefix($prefix)
    {
        $this->templates_admin_prefix = $prefix;
    }


    /**
     * Возвращет префикс путей к шаблонам пользовательского интерфейса
     *
     * @return string
     */
    private function get_templates_user_prefix()
    {
        return $this->templates_user_prefix;
    }

    /**
     * Устанавливает префикс путей к шаблонам пользовательского интерфейса
     *
     * @param string $prefix
     */
    private function set_templates_user_prefix($prefix)
    {
        $this->templates_user_prefix = $prefix;
    }


    /**
     * Устанавливает действие по умолчанию
     *
     * @param string $value Имя GET параметра определяющего действие
     */
    private function set_action_default($value)
    {
        $this->action_default = $value;
    }


    /**
     * Возвращает название перемнной в GET запросе определяющей действие
     *
     * @return string
     */
    private function get_action_name()
    {
        return $this->action_name;
    }

    /**
     * Возвращает значение указанного действия, если установленно или значение по умолчанию
     *
     * @param string $action_name Имя параметра в GET запросе
     * @return string
     */
    private function get_action_value($action_name)
    {
        global $kernel;
        if ($kernel->pub_httppost_get($action_name))
            return $kernel->pub_httppost_get($action_name);
        else
            return $this->get_action_default();
    }


    /**
     * Возвращает значение действия по умолчанию
     *
     * @return string
     */
    private function get_action_default()
    {
        return $this->action_default;
    }
}