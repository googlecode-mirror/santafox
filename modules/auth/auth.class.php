<?PHP
require_once realpath(dirname(__FILE__)."/../../")."/include/basemodule.class.php";

/**
 * Основной управляющий класс модуля «авторизации»
 *
 * Модуль преднозначен для управления авторизацией и регистрацией
 * посетителей на сайте, а так же управление личным кабинетом
 * @copyright ArtProm (с) 2001-2011
 * @version 1.1
 */

class auth extends basemodule
{
    private $path_templates = "modules/auth/templates_user"; //Путь к шаблонам модуля
    private $path_templates_admin = "modules/auth/templates_admin";
    function auth()
    {
    }


    //***********************************************************************
    //	Наборы Публичных методов из которых будут строится макросы
    //**********************************************************************

    function pub_show_info($tpl=null, $page_cabinet=null)
    {
        global $kernel;
        $html = "";
        if ($kernel->pub_user_is_registred())
        {

            if (empty($page_cabinet))
            {
                $page_cabinet = $kernel->pub_modul_properties_get('id_page_cabinet');
                if (!$page_cabinet['isset'])
                    return ("Не установлен параметр <[#auth_module_method_name1_param2_caption#]>");
                else
                    $page_cabinet = $page_cabinet['value'];
            }

            $user_id = $kernel->pub_user_field_get("id");
            $user_array = $kernel->pub_users_info_get($user_id, false);

            if (empty($tpl))
                $tpl = $this->path_templates."/auth_show_cab.html";
            $this->set_templates($kernel->pub_template_parse($tpl));

            $html .= $this->get_template_block('user');
            $html = str_replace("%fio%", $user_array[$user_id]['name'], $html);
            $u_lines = "";
            foreach ($user_array[$user_id]['fields'] AS $field)
            {
                $u_line = $this->get_template_block('user_line');
                $u_line = str_replace("%caption%", $field['caption'], $u_line);
                $u_line = str_replace("%value%", $field['value'], $u_line);
                $u_lines .= $u_line;
            }
            $html = str_replace("%lines%", $u_lines, $html);
            $html = str_replace("%page_cab%", $page_cabinet, $html);
        }
        return $html;
    }


    function pub_show_remember($tpl=null)
    {
        global $kernel;
        $my_post = $kernel->pub_httppost_get();
        if (empty($tpl))
            $tpl=$this->path_templates."/auth_show_remember.html";
        $this->set_templates($kernel->pub_template_parse($tpl));
        if (isset($my_post['rem']['login']) || isset($my_post['rem']['email']))
        {
            if (isset($my_post['rem']['login']))
			    $array = $kernel->pub_user_login_info_get($my_post['rem']['login'], true);
			else
				$array = $kernel->pub_user_login_info_get($my_post['rem']['email'], false);


            if ($array)
            {
                //$password = $array['password'];
                $toname[] = $array['name'];
                $toaddr[] = $array['email'];
                $fromname = $_SERVER['HTTP_HOST'];
                $fromaddr = "noreply@".$_SERVER['HTTP_HOST'];
                $subject = $_SERVER['HTTP_HOST'].$kernel->pub_page_textlabel_replace("[#auth_other#]");

                $message = $this->get_template_block('mail');
                $message = str_replace("%login%", $array['login'], $message);
                $message = str_replace("%password%", $array['password'], $message);

                $kernel->pub_mail($toaddr, $toname, $fromaddr, $fromname, $subject, $message, 1);

                $html = $this->get_template_block('sended');
            }
            else
                $html = $this->get_template_block('incorrect');
        }
        else
            $html = $this->get_template_block('form');
        return $html;
    }


    /**
     * Возвращает форму авторизации
     *
     * @param string $redirectToPage
     * @return string
     */
    function pub_show_authorize($tpl=null, $page_reg=null, $page_cabinet=null, $redirectToPage="")
    {
        global $kernel;
        $my_post = $kernel->pub_httppost_get();

        if (empty($page_reg))
        {
            $page_reg = $kernel->pub_modul_properties_get('id_page_registration', 'auth1');
            if (!$page_reg['isset'])
                return ("Не установлен параметр <[#auth_module_method_name1_param1_caption#]>");
            else
                $page_reg = $page_reg['value'];
        }
        if (empty($page_cabinet))
        {
            $page_cabinet = $kernel->pub_modul_properties_get('id_page_cabinet', 'auth1');
            if (!$page_cabinet['isset'])
                return ("Не установлен параметр <[#auth_module_method_name1_param2_caption#]>");
            else
                $page_cabinet = $page_cabinet['value'];
        }

        if (empty($tpl))
            $tpl = $this->path_templates."/auth_show_auth.html";
        $this->set_templates($kernel->pub_template_parse($tpl));
        $html = $this->get_template_block('begin');
        if (!$kernel->pub_user_is_registred())
        {
            if (isset($my_post['login']) && isset($my_post['pass']))
            {
                if (empty($my_post['login']) || empty($my_post['pass']))
                    $html .= $this->get_template_block('empty_fields');
                else
                {
                    $errorlevel = $kernel->pub_user_register($my_post['login'], $my_post['pass']);
                    switch ($errorlevel)
                    {
                        case 1:
                            if (isset($my_post['redirect2page']) && !empty($my_post['redirect2page']))
                                $kernel->pub_redirect_refresh_global($my_post['redirect2page']);
                            else
                                $kernel->pub_redirect_refresh_global("/".$page_cabinet.".html");
                            break;
                        case -1:
                            $html .= $this->get_template_block('inc_login');
                            break;
                        case -2:
                            $html .= $this->get_template_block('disabled_by_admin');
                            break;
                        case -3:
                            $html .= $this->get_template_block('verifying');
                            break;
                        default:
                            $html .= $this->get_template_block('unknown_err');
                    }
                }
            }
            $html .= str_replace("%redirect2page%", $redirectToPage, $this->get_template_block('login'));
        }
        elseif(isset($my_post['exit']))
        {
            $kernel->pub_user_unregister();
            $kernel->pub_redirect_refresh_global("/");
        }
        elseif (isset($my_post['redirect2page']) && !empty($my_post['redirect2page']))
            $kernel->pub_redirect_refresh_global($my_post['redirect2page']);
        else
        {
            $html .= $this->get_template_block('exit');

            $array = $kernel->pub_user_info_get();

            unset($array['indexes']);
            foreach ($array AS $key => $value)
            {
                $html = str_replace("%".$key."%", $value, $html);
            }
        }
        $html .= $this->get_template_block('end');
        $html = str_replace("%reg%", $page_reg, $html);
        //$html = str_replace("%personal%", $page_cabinet, $html);
        $html = str_replace("%personal%", $kernel->pub_page_current_get(), $html);
        return $html;
    }


    function pub_show_registration($tpl=null, $page_cabinet=null)
    {
        global $kernel;
        $my_post = $kernel->pub_httppost_get();
        $my_get = $kernel->pub_httpget_get();

        if (empty($tpl))
            $tpl = $this->path_templates."/auth_show_reg.html";
        $this->set_templates($kernel->pub_template_parse($tpl));
        $html = $this->get_template_block('begin');
        if (!isset($my_get['regaction']))
            $my_get['regaction'] = "";
        $action = $my_get['regaction'];

        switch ($action)
        {
            // Вводим данные для регистрации
            case 'input':
                if ($kernel->pub_is_valid_email( $my_post['reg']['email']))
                {
                    $reg = $my_post['reg'];
                    foreach ($reg as $rk=>$rv)
                    {
                        $reg[$rk] = $kernel->pub_str_prepare_set($rv);
                    }
                    $id = $kernel->pub_user_add_new($reg['login'], $reg['pass'], $reg['email'], $reg['name']);

                    if ($id > 0)
                    {
                        //Запишем информацию о доп полях к юзеру
                        $user = array();
                        @$user[$id]['fields']['shop-info']       = $reg['misc'];
                        @$user[$id]['fields']['shop-icquin']     = $reg['icq'];
                        @$user[$id]['fields']['shop-phone2']     = $reg['phone2'];
                        @$user[$id]['fields']['shop-phone']      = $reg['phone'];
                        @$user[$id]['fields']['shop-bitrhdate']  = $reg['birthdate'];
                        @$user[$id]['fields']['shop-sex']        = $reg['sex'];
                        $kernel->pub_users_info_set($user, false);


                        //$kernel->fof
                        $url = $id.md5($reg['email']);
                        $url = "http://".$_SERVER['HTTP_HOST']."/".$kernel->pub_page_current_get().".html?regaction=confirm&code=".$url;
                        $name = $reg['name'];
                        $toaddr[] = $reg['email'];
                        $toname[] = $reg['name'];

                        $message = $this->get_template_block('mail');
                        $message = str_replace("%url%", $url, $message);
                        $message = str_replace("%name%", $name, $message);
                        $message = str_replace("%host%",$_SERVER['HTTP_HOST'], $message);
                        $message = str_replace("%email%", $reg['email'], $message);

                        @$kernel->pub_mail($toaddr, $toname, "mail@".$_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST'], "Регистрация на сайте ".$_SERVER['HTTP_HOST'], $message, 1);
                        $html .= $this->get_template_block('follow_link');
                    }
                    else
                    {
                        $html .= $this->get_template_block('not_unique');
                        $html .= $this->get_template_block('register');
                    }
                }
                else
                    $html .= $this->get_template_block('invalid_email');
                break;


            case 'confirm':
                $code = $my_get['code'];
                if (preg_match("|^(\\d+)([0-9a-f]{32})$|", $code, $arr))
                {
                    $sql = "SELECT email, password, login FROM ".$kernel->pub_prefix_get()."_user WHERE id='$arr[1]'";
                    $data = mysql_fetch_assoc($kernel->runSQL($sql));
                    if (md5($data['email']) == $arr[2])
                    {
                        $kernel->pub_user_unregister();
                        $kernel->pub_user_verify($arr[1]);
                        $html .= $this->get_template_block('success');
                        $kernel->pub_user_register($data['login'], $data['password']);
                        if (empty($page_cabinet))
                        {
                            $page_cabinet = $kernel->pub_modul_properties_get('id_page_cabinet');
                            $page_cabinet = $page_cabinet['value'];
                        }
                        $kernel->pub_redirect_refresh_global("/".$page_cabinet.".html");
                    }
                }
                else
                    $html .= $this->get_template_block('invalid_link');
                break;

                // Просто зашли на страницу регистрацииы
            default:
                if (!$kernel->pub_user_is_registred())
                    $html .= $this->get_template_block('register');
                else
                    $html .= $this->get_template_block('also_registred');

        }
        $html .= $this->get_template_block('end');
        return $html;
    }


    function pub_show_cabinet($tpl=null)
    {
        global $kernel;
        $my_post = $kernel->pub_httppost_get();
        if (empty($tpl))
            $tpl = $this->path_templates."/auth_show_cab.html";
        $this->set_templates($kernel->pub_template_parse($tpl));
        $content="";
        if ($kernel->pub_user_is_registred())
        {
            if (isset($my_post['values']))
            {
                if ($my_post['values'][$kernel->pub_user_field_get("id")]['password'] == "")
                {
                    unset($my_post['values'][$kernel->pub_user_field_get("id")]['password']);
                }
                $values = $my_post['values'];
                if ($kernel->pub_users_info_set($values))
                    $content = $this->get_template_block('save_success');
            }
            else
            {
                $content = $this->get_template_block('userform_begin');
                $array = $kernel->pub_user_info_get(true);
                if (isset($array['fields']))
                {
                    $fields = $array['fields'];
                    unset($array['fields']);
                }
                else
                    $fields = array();

                $id = $array['id'];
                foreach ($array AS $key => $value)
                {
                    $content = str_replace("%".$key."%", $value, $content);
                }
                if (!empty($fields))
                {
                    foreach ($fields AS $m_key => $m_value)
                    {
                        foreach ($fields[$m_key] AS $id_key => $id_value)
                        {
//                            $content .= $this->template_array['userform_line'];
                            $name = "[$id][fields][$id_key]";
                            $caption = $id_value['caption'];
                            $id_field = $id_value['name'];

                            $value = $id_value['value'];
                            $content = str_replace("%values ".$id_field."%", $name, $content);
                            $content = str_replace("%value ".$id_field."%", $value, $content);
                            $content = str_replace("%value ".$id_field.$value."%", "checked", $content);
                            $content = str_replace("%caption%", $caption, $content);
                        }
                    }
                }
                $content = str_replace("%action%", "auth_users_save", $content);
                $content .= $this->get_template_block('userform_end');
            }
        }
        else
            $content = $this->get_template_block('not_authorized');
        return $content;
    }


    //***********************************************************************
    //	Наборы внутренних методов модуля
    //**********************************************************************


    //***********************************************************************
    //	Наборы методов, для работы с админкой модуля
    //**********************************************************************
    /**
     * Функция для построения меню для административного интерфейса
     *
     * @param pub_interface $menu Обьект класса для управления построением меню
     * @return boolean true
     */
	public function interface_get_menu($menu)
	{
	    //Создаётся заголовок первого блока элементов меню
        $menu->set_menu_block('[#auth_admin_leftmenu_caption#]');
        //Задаются элементы меню, входящие в первый блок
        //сначала указывается название элемента меню (языковая переменная или
        //непосредственный русский текст) и затем ID этого пункта меню
        $menu->set_menu("[#auth_group_list#]","group_list");
        $menu->set_menu("[#auth_users_list#]","users_list");
        $menu->set_menu("[#auth_protect_dir#]","protect_dir");
        //Указываем ID меню по умолчанию
        $menu->set_menu_default('group_list');
	    return true;
	}


    /**
	 * Предопределйнный метод, используется для вызова административного интерфейса модуля
	 * У данного модуля админка одна, для всех экземпляров, так как в админке надо только
	 * Редактировать шаблоны
	 */

    function start_admin()
    {
        global $kernel;
        $my_post = $kernel->pub_httppost_get();
        $html = '';
        $id_user = $kernel->pub_httpget_get('id');
        $id_group = intval($kernel->pub_httpget_get('id_group'));
        $cur_action = $kernel->pub_section_leftmenu_get();
        switch ($cur_action)
        {
            //Выводим список доступных групп, всё редактируется прямо там
            case 'group_list':
                $html = $this->priv_group_list();
                break;

            //Редактируем группу
            case 'group_edit':
                $html = $this->priv_group_edit($id_group);
                break;

            //Добовляем новую группу
            case 'group_add':
                $html = $this->priv_group_edit();
                break;

            //Удаляем существующую группу
            case 'group_delet':
                $this->priv_group_delete($id_group);
                $kernel->pub_redirect_refresh("group_list");
                break;

            //Сохраняем информацию о группах, просто "умираем" так как запрос отправляется без перегрузки страницы
            case 'group_save':
                $html = $this->priv_group_save();
                break;

            //Работа с пользователями
            //Формируем список пользователей
            case 'users_list':
                $html = $this->priv_user_list();
                break;

            //Придобавлении нового пользователя просто открываем форму редактирования с пустым ID
            case 'user_add':
                $html = $this->priv_user_edit();
                break;

            //Выводит форму для добавления нового и редактирования существующего
            //пользователя сайта и указания групп, в которые он входит
            case 'user_edit':
                $html = $this->priv_user_edit($id_user);
                break;

            //Сохраняем отредактированного или нового пользователя
            case 'user_save':
                $html = $this->priv_user_save();
                break;

            case 'user_delete':
                $kernel->pub_user_delete($id_user);
                $kernel->pub_redirect_refresh("users_list");
                break;

            case 'protect_dir':
                $this->set_templates($kernel->pub_template_parse($this->path_templates_admin."/pfolder.html"));
                $html = $this->get_template_block('form');
                $html = str_replace("%url%", $kernel->pub_redirect_for_form('protect_dir'), $html);

                if (isset($my_post['pfolder']))
                {//защищаем
                    $folder = $my_post['pfolder'];
                    if (mb_substr($folder, mb_strlen($folder)-1, 1) != "/")
                        $folder .= "/"; //добавляем / в конец, если его нет
                    if (mb_substr($folder, 0, 1) != "/")
                        $folder = "/".$folder; //добавляем / в начало, если его нет

                    $path2file = $kernel->pub_site_root_get().$folder.".htaccess";

                    $depth = mb_substr_count($folder, "/", 1);

                    $contents = "<IfModule mod_rewrite.c>\n".
								" RewriteEngine On\n".
                                " RewriteBase ".$folder."\n".
								" RewriteRule . ".str_repeat("../", $depth)."modules/auth/download.php\n".
                                "</IfModule>";
                    if ($kernel->pub_file_save($path2file, $contents))
                        return $kernel->pub_httppost_response($this->get_template_block('protect_success'));
                    else
                        return $kernel->pub_httppost_response($this->get_template_block('protect_failed'));
                }
                break;
        }
        return $html;
    }


	/**
    * Формирует список существующих в системе групп вместе с галочками
	*
	* @return HTML
	* @access private
	*/
	function priv_group_list()
    {
    	global $kernel;
        $this->set_templates($kernel->pub_template_parse($this->path_templates_admin."/groups.html"));
    	$arr_group = $kernel->pub_users_group_get();
        $number = 1;
        $html_row = '';
		foreach ($arr_group as $val)
	    {
	        $row = $this->get_template_block('all_group_row');
	        $row = str_replace("%number%"   , $number                             , $row);
	        $row = str_replace("%name%"     , $val['name']                        , $row);
	        $row = str_replace("%full_name%", $val['full_name']                   , $row);
	        $row = str_replace("%id%"       , $val['id']                          , $row);
	        $row = str_replace("%classtr%"  , $kernel->pub_table_tr_class($number), $row);
			$html_row .= $row;
			$number++;
        }
        $html = $this->get_template_block('all_group_table');
        $html = str_replace("%rows%", $html_row, $html);
        return $html;
    }

    /**
     * Создаёт форму для редактирования группы пользователей
     *
     * @param int $id Идентификатор группы пользователей, если это реакдтирование
     * @return HTML
     */

    function priv_group_edit($id = 0)
    {
		global $kernel;

		$groups = $kernel->pub_users_group_get();
		if (count($groups) <= 0)
            return "";

        if (($id > 0 ) && (!isset($groups[$id])))
            return "";

        $name  = '';
        $fname = '';
        if ($id > 0)
        {
            $name  = $groups[$id]['name'];
            $fname = $groups[$id]['full_name'];
        }
        $this->set_templates($kernel->pub_template_parse($this->path_templates_admin."/groups.html"));
        $html = $this->get_template_block('group_edit');
        $html = str_replace("%name%" , $name , $html);
        $html = str_replace("%fname%", $fname, $html);
        $html = str_replace("%url%"  , $kernel->pub_redirect_for_form('group_save&id='.$id), $html);
		return $html;
    }

    /**
     * Сохраняет изменения в группах пользователей
     *
     * @access private
     * @return void
     */
    function priv_group_save()
	{
		global $kernel;

		//Прежде всего проверка на то, что все данные введены
		$id_group  = intval($kernel->pub_httpget_get('id'));
		$name      = $kernel->pub_httppost_get('name');
		$full_name = $kernel->pub_httppost_get('fname');
		if (empty($name) || empty($full_name))
            return $kernel->pub_httppost_errore('[#auth_group_save_errore1#]', true);

        //Теперь либо добавляем новую запись, либо изменяем старую
        if ($id_group <= 0)
        {
            $query = "INSERT INTO `".$kernel->pub_prefix_get()."_user_group`
                       (`name`, `full_name`)
                      VALUES
                       ('".$kernel->pub_str_prepare_set($name)."', '".$kernel->pub_str_prepare_set($full_name)."')
             ";
        }
        else
        {
            $query = "UPDATE `".$kernel->pub_prefix_get()."_user_group`
        			  SET
					  	`name` = '".$kernel->pub_str_prepare_set($name)."',
                        `full_name` = '".$kernel->pub_str_prepare_set($full_name)."'
                      WHERE `id` = ".$id_group;

		}

		//Теперь сообщения
		$message = "[#auth_group_sucse_add#]";
		if ($id_group > 0)
            $message = '[#auth_group_sucse_save#]';


		if (!$kernel->runSQL($query))
            $kernel->pub_httppost_errore('[#auth_group_save_errore2#]');

		//Данные необходимо возвратить через функцию ядра
		return $kernel->pub_httppost_response($message, 'group_list&my_param=1');
	}

    /**
    * Удаляет выбранную группу посетителей сайта
    *
    * @access private
    * @return void
    */
	function priv_group_delete($id_group)
    {
    	global $kernel;

    	$id_group = intval($id_group);
    	if ($id_group <= 0)
    	   return false;

		//Непосредственно удалим группу
    	$query = "DELETE FROM ".$kernel->pub_prefix_get()."_user_group
        		  WHERE id = '".$id_group."'";
        $kernel->runSQL($query);

		//Удалим связи между администраторами и этой группой
    	$query = "DELETE FROM ".$kernel->pub_prefix_get()."_user_cross_group
        		  WHERE group_id = '".$id_group."'";
        $kernel->runSQL($query);

        //Удалим права, проставленные для этой группы
    	//$query = "DELETE FROM ".$kernel->priv_prefix_get()."_admin_group_access
        //		  WHERE group_id = '".$array_form['id_str_del']."'";
        //$result = $kernel->runSQL($query);
        return true;
    }

    /**
     * Выводит список пользователей сайта
     *
     * @param array $template используемый шаблон
     * @access private
     * @return HTML
     */
    function priv_user_list()
    {
        global $kernel;
        $this->set_templates($kernel->pub_template_parse($this->path_templates_admin."/users.html"));
        $html = $this->get_template_block('begin');
        $sortFields = array("login","email","date");
        $sortBy = $kernel->pub_httpget_get("sortby");
        if (!in_array($sortBy, $sortFields))
            $sortBy = "login";
        $offset=intval($kernel->pub_httpget_get("offset"));
        $limit=100;
        $array = $kernel->pub_users_info_get("", true, $sortBy,$offset,$limit);
        $i = $offset+1;
        foreach ($array AS $id => $info)
        {
            $str_html = $this->get_template_block('line');
            $str_html = str_replace('%number%', $i, $str_html);
            $str_html = str_replace('%login%', $info['login'], $str_html);
            $str_html = str_replace('%name%', $info['name'], $str_html);
            $str_html = str_replace('%email%', $info['email'], $str_html);
            $str_html = str_replace('%fdate%', $info['fdate'], $str_html);
            $str_verified = '';
            if ($info['verified'] == 1)
                $str_verified = 'checked="checked"';
            $str_html = str_replace('%verified%', '<input type="checkbox" '.$str_verified.' disabled="disabled"/>', $str_html);
            $str_enabled = '';
            if ($info['enabled'] == 1)
                $str_enabled = 'checked="checked"';
            $str_html = str_replace('%enabled%', '<input type="checkbox" '.$str_enabled.' disabled="disabled"/>', $str_html);
            $str_html = str_replace("%id%", $id, $str_html);
            $html .= $str_html;
            $i++;
        }
        $html .= $this->get_template_block('end');
        $total = $kernel->pub_users_total_get();
        $html = str_replace('%pages%', $this->build_pages_nav($total,$offset, $limit,'users_list&sortby='.$sortBy.'&offset=',0,'link'), $html);
        return $html;

    }

    /**
     * Выводит форму для редактирования параметров пользователя.
     *
     * @param Intger $id
     * @return HTML
     */
    function priv_user_edit($id = 0)
    {
        global $kernel;

        $this->set_templates($kernel->pub_template_parse($this->path_templates_admin."/users.html"));
        $html = $this->get_template_block('form_user');

        //Сначала обработаем обязательные поля, которые точно есть
        //и заполним их если редактируется уже сущесвующий элемент
        $login = '';
        $name = '';
        $password = '';
        $email = '';
        //$date = time('Y-m-d H:m:s');
        $fdate = trim(date('d.m.Y'));
        $curent['verified'] = "0";
        $curent['enabled'] = "0";

        //Если это редактирование то заменим на существующие данные
        if ($id > 0)
        {
            $curent = $kernel->pub_users_info_get($id);
            $curent = $curent[intval($id)];
            $login      = $curent['login'];
            $name       = $curent['name'];
            $password   = $curent['password'];
            $email      = $curent['email'];
            //$date       = $curent['date'];
            $fdate      = trim($curent['fdate']);
        }

        $html = str_replace("%login%",      $login,     $html);
        $html = str_replace("%name%",       $name,      $html);
        $html = str_replace("%password%",   $password,  $html);
        $html = str_replace("%email%",      $email,     $html);
        $html = str_replace("%date%",       $fdate,     $html);
        $html = str_replace("%id%",         $id,        $html);
        $html = str_replace("%url%",        $kernel->pub_redirect_for_form('user_save&id='.$id), $html);

        //Проставим галки, если они есть в текущем, так как по умолчанию они
        //у нас выключены
        $val = '';
        if ($curent['verified'] == "1")
            $val = 'checked="checked"';

        $html = str_replace("%v_checked%", $val, $html);

        $val = '';
        if ($curent['enabled'] == "1")
            $val = 'checked="checked"';

        $html = str_replace("%e_checked%", $val, $html);

        //Теперь пришла очередь дополнительных полей
        $html_fields = '';
        if (empty($id))
            $fields = $kernel->pub_users_fields_get();
        else
            $fields = $curent['fields'];

        //$select_str = false;
        $num = 0;
        foreach ($fields AS $m_key => $m_value)
        {
            //Попали в доп. поля конкретног модуля
            foreach ($fields[$m_key] AS $id_key => $id_value)
            {
                //Выводим собственно эти поля
                $html_str = $this->get_template_block('form_line');

                $tmp = '';
                if ($id_value['only_admin'])
                    $tmp = "[#auth_users_edit_user_label11#]";

                $html_str = str_replace("%caption%",    $id_value['caption'],                $html_str);
                $html_str = str_replace("%value%",      $id_value['value'],                  $html_str);
                $html_str = str_replace("%id%",         $id_key,                             $html_str);
                $html_str = str_replace("%modid%",      $m_key,                              $html_str);
                $html_str = str_replace("%only_admin%", $tmp,                                $html_str);
                $html_str = str_replace("%class_str%",  $kernel->pub_table_tr_class($num++), $html_str);
                $html_fields .= $html_str;
            }
        }
        $html = str_replace("%str_fields%", $html_fields, $html);

        //Теперь добавим информацию о тех группах, в кторые входит
        $html_group = '';
        $arr = $kernel->pub_users_group_get();
        $cgroup = $kernel->pub_user_group_get($id);
        $num = 0;
        foreach ($arr as $val)
        {
            $html_str = $this->get_template_block('form_line_group');
            $chek = '';
            if (isset($cgroup[$val['id']]))
                $chek = 'checked="checked"';

            $html_str = str_replace("%checked%",   $chek,                               $html_str);
            $html_str = str_replace("%id%",        $val['id'],                          $html_str);
            $html_str = str_replace("%name%",      $val['full_name'],                   $html_str);
            $html_str = str_replace("%class_str%", $kernel->pub_table_tr_class($num++), $html_str);

            $html_group .= $html_str;
        }

        $html = str_replace("%str_fields_group%", $html_group, $html);
        return $html;
    }

    /**
     * Сохраняет форму с данными пользователя сайта
     *
     */
    function priv_user_save()
    {
        global $kernel;

        //ID пользователя
        $id_user  = intval($kernel->pub_httpget_get('id'));

        //Преобразуем параметры, которые выбираются галочками
        $values = $kernel->pub_httppost_get();

        $values['verified'] = intval($values['verified']);
        $values['enabled'] = intval($values['enabled']);

        //Проверка логина
        $values['login'] = trim($values['login']);
        if (empty($values['login']))
            $kernel->pub_httppost_errore("[#auth_users_edit_user_errore1#]", true);
        elseif (!preg_match("/^[a-zA-Z0-9]+$/", $values['login']))
            $kernel->pub_httppost_errore("[#auth_users_edit_user_errore2#]", true);

        //Проверка заполнености пароля
        $values['password']    = trim ($values['password']);
        $values['re_password'] = trim ($values['re_password']);
        if (mb_strlen($values['password']) < 4 )
            $kernel->pub_httppost_errore("[#auth_users_edit_user_errore3#]", true);
        elseif ($values['re_password'] !== $values['password'])
            $kernel->pub_httppost_errore("[#auth_users_edit_user_errore4#]", true);

        //Проверка заполненности адреса почты
        $values['email']    = trim ($values['email']);
        if (empty($values['email']))
            $kernel->pub_httppost_errore("[#auth_users_edit_user_errore5#]", true);
        elseif (!$kernel->pub_is_valid_email($values['email']))
            $kernel->pub_httppost_errore("[#auth_users_edit_user_errore6#]", true);

        if ($kernel->pub_httppost_errorecount() > 0)
            return $kernel->pub_httppost_errore('Ошибка', true);

        //Подготовим дополнительные поля, с ними чуть сложнее такак
        //через такой пост нельзя передать массивы
        $values['fields'] = array();
        $fields = $kernel->pub_users_fields_get();

        foreach ($fields AS $m_key => $m_value)
        {
            //Попали в доп. поля конкретног модуля
            foreach ($fields[$m_key] AS $id_key => $id_value)
            {
                $fname  = 'fields_'.$id_key;
                if (isset($values[$fname]))
                    $values['fields'][$id_key] = $values[$fname];

                unset($values[$fname]);
            }
        }
        //Прошли, и можем обрабатывать данные
        //Значит сохраняем нового пользователя, и перед сохранением мы его просто
        //добавим


        //Ещё надо сохранить список групп, в которые он входит
        //Просмотрим пост в котором есть эти данные
        $select_group = array();
        $all_group = $kernel->pub_users_group_get();
        foreach ($all_group as $val)
        {
            $gname  = 'group_'.$val['id'];
            if (intval($values[$gname]) > 0)
                $select_group[] = $val['id'];

            unset($values[$gname]);

        }

        //Так как функция сохранения расчитана на множество пользователей
        //то массив с параметрами пользователя помещаем к значение с ключем
        //равным его ID
        if ($id_user <= 0)
            $id_user = $kernel->pub_user_add_new($values['login'], $values['password'], $values['email'], $values['name']);


        $kernel->pub_users_info_set(array($id_user => $values), false);
        $kernel->pub_users_group_set($id_user, $select_group);
        //$kernel->pub_redirect_refresh("/admin/?action=bottom_frames&view=users_list");
        return $kernel->pub_httppost_response("[#auth_users_edit_result_true#]");

    }

}

?>