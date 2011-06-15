<?PHP
if (file_exists('modules/newssubmit/mysql_submit.php'))
    include_once('modules/newssubmit/mysql_submit.php');

class newssubmit
{

	var $template_array = array();
	var $curent_way = array();
	var $path_templates = "modules/newssubmit/templates_user"; //Путь, к шаблонам модуля (туда будут скопированны шаблоны при инсталяции
	var $mysql_base;
	var $pristavka = 'SF2008';
	var $full_name_serv;
	var $n = 25;

	function newssubmit($modul_id = '')
    {
		global $kernel;
        if (empty($modul_id))
            $modul_id = $kernel->pub_module_id_get();

		$this->mysql_base = new mysql_submit(PREFIX.'_'.$modul_id);
		$this->mysql_base->set_pristavka($this->pristavka);
		if (isset($_SERVER['HTTP_HOST']))
			$this->full_name_serv = $_SERVER['HTTP_HOST'];

		if (!preg_match("/^www\\..*/i", $this->full_name_serv))
			$this->full_name_serv = "www.".$this->full_name_serv;

		$this->full_name_serv = "http://".$this->full_name_serv;

		//Получим ещё параметр модуля, и определим приставку
		$arr = $kernel->pub_modul_properties_get('prefix_unic');
		if (!empty($arr['value']))
		  $this->pristavka = $arr['value'];
    }

    function add2cron2()
    {
		global $kernel;

        $send_all = true;
        //Получим шаблон используемый для отсылки письма
		$prom = $kernel->pub_modul_properties_get("template_send");
		if ($prom["isset"])
		{
            if (file_exists($prom["value"]))
                $this->template_array = $kernel->pub_template_parse($prom["value"]);
		}

		if (empty($this->template_array))
            return "Template not selected";


		//Прежде всего определим, это тестовая рассылка или нет
		$prop = $kernel->pub_modul_properties_get("is_test");
        if ($prop["value"])
            $array_for_submit = $this->prepare_html_for_submit($send_all, false);
        else
            $array_for_submit = $this->prepare_html_for_submit($send_all, true);

        //Проверим, а есть ли-нам что отсылать вообще
		$start_text  = $kernel->pub_httppost_get('start_text',false);
		$end_text    = $kernel->pub_httppost_get('end_text',false);

		//Проверка на то, что хоть что-то должно быть для рассыдки
		if (($array_for_submit === false) && (empty($start_text)) && (empty($end_text)))
    		return 'Not data for submit';

		//Теперь получим список людей, которым нужно сделать рассылку
		$users = $this->mysql_base->get_users_for_submit();

		//Теперь собственно можно перебирать юзеров и отсылать им те секции, которые нужны
		$count = 0;

        //Определим урл для управления рассылкой
        $page = $kernel->pub_modul_properties_get('page_submit', $kernel->pub_module_id_get());
        $page = $page['value'];


        //Определим заголовок письма
        $header = 'Расылка от '.$this->full_name_serv;
        $param = $kernel->pub_modul_properties_get('theme_mail_2');
        if (!empty($param['value']))
            $header = $param['value'];


        $host = preg_replace("/^www\\./", "", $_SERVER['HTTP_HOST']);
        $from_email = "press-center@$host";
        $param = $kernel->pub_modul_properties_get('server_email');
        if (!empty($param['value']))
            $from_email = $param['value'];

        $from_name = "Robot";
        $param = $kernel->pub_modul_properties_get('server_user');
        if (!empty($param['value']))
            $from_name = $param['value'];


		foreach ($users as $val)
		{
			$html_news = array();
			foreach ($val['section'] as $section)
			{
				if (isset($array_for_submit[$section]))
                    $html_news[] = $array_for_submit[$section];
			}
            $link = $this->full_name_serv.'/'.$page.'.html?controlcode='.trim($val['code']);

			$html_submit = $this->template_array['mail'];

			$html_submit = str_replace("%start_text%", $start_text          , $html_submit);
			$html_submit = str_replace("%base_link%" , $this->full_name_serv, $html_submit);
			$html_submit = str_replace("%end_text%"  , $end_text            , $html_submit);
			$html_submit = str_replace("%user_link%" , $link                , $html_submit);

			$html_submit = str_replace("%news%", implode($this->template_array['news_block_delimiter'], $html_news), $html_submit);

			//Проверим, если этому пользователю нечего рассылать, то не будем ничего слать
            if ((count($html_news) <= 0) && (empty($start_text)) && (empty($end_text)))
                continue;

            $this->mysql_base->addMail2CronLetters($from_email, $from_name, $val['mail'], $val['name'],$header,$html_submit );
			$count++;
		}

		return $count;
    }

    /** Возвращает письма для отправки кроном2, с учётом ограничения модуля
     * @return array
     */
    function getCron2Letters4send()
    {
        global $kernel;
        $maxp = $kernel->pub_modul_properties_get('max_letters');
        $limit = null;
		if (!empty($maxp['value']))
            $limit = $maxp['value'];
        return $this->mysql_base->getCron2Letters($limit);
    }


    /** Удаляет письмо для крон2 из таблицы (после успешной отправки)
     * @param  $id
     * @return void
     */
    function deleteCron2Letter($id)
    {
        $this->mysql_base->deleteCron2Letter($id);
    }


    function user_save_control($code)
    {//@todo check
        global $kernel;
        if (empty($code))
            return $this->template_array['code_not_exist'];
        $user = $this->mysql_base->get_info_user($code);
		if (empty($user))
			return $this->template_array['code_not_exist'];
        $my_post = $kernel->pub_httppost_get();
        if (!isset($my_post['section']))
            return $this->template_array['errore_news'];
        $userid = $user['id'];
        if (isset($my_post['name']) && !empty($my_post['name']))
            $this->mysql_base->update_user($userid, array("name"=>$my_post['name']));

        $news = $my_post['section'];

        //Сначала создаим массив с ID рубрик, от которых пользователь отписался
        $user = $this->mysql_base->get_all_user($userid);
        $user = $user[$userid];
        $section = $user['section'];
        foreach ($section as $key => $val)
        {
            if (!isset($news[$key]))
                $this->mysql_base->delete_news_group($val);
        }
        //Теперь добавим новые группы
        foreach ($news as $key => $val)
        {
            $this->mysql_base->add_news_group($userid, $key);
        }

        return true;
    }
    /*******************************************************
	Наборы Публичных методов из которых будут строится действия
    ********************************************************/
	/**
	 * Выводит форму для осуществелния подписки на новости
	 *
	 * @param string $errore Сообщение, выводимое как ошибка
	 */
	function pub_formsubmit_show($file_template, $file_template_control)
	{
		global $kernel;

		$my_post = $kernel->pub_httppost_get();
		$my_get  = $kernel->pub_httpget_get();

        if (file_exists($file_template))
            $this->template_array = $kernel->pub_template_parse($file_template);

		if (empty($this->template_array))
            return "Template not select";

		$template = $this->template_array;

		//Проверим, может надо вывести подтвреждение рассылки
		if ((isset($my_get['activatecode'])) && (!empty($my_get['activatecode'])))
            return $this->user_activate(base64_decode(mysql_real_escape_string($my_get['activatecode'])));

        //возможно пользователь захотел удалить себя из подписки
        if ((isset($my_post['del_user'])) && (isset($my_post['code'])) && (isset($my_post['id'])))
        {
            $this->template_array = $kernel->pub_template_parse($file_template_control);
            return $this->user_delete($kernel->pub_httppost_get('id'), $kernel->pub_httppost_get('code'));
        }
        //Возможно захотел настроить свою подписку
        if ((isset($my_post['save_control'])) && (isset($my_post['code'])) && (isset($my_post['id'])))
        {
            $this->template_array = $kernel->pub_template_parse($file_template_control);
            $errore = '';
            $ret = $this->user_save_control($my_post['code']);
            if ($ret === true) {}
            else
                $errore = $ret;

            return $this->user_controle($kernel->pub_httppost_get('code'), $errore);
        }

		if ((isset($my_get['controlcode'])) && (!empty($my_get['controlcode'])))
		{
		    $this->template_array = $kernel->pub_template_parse($file_template_control);
            return $this->user_controle(mysql_real_escape_string($my_get['controlcode']));
		}

        $result = '';

        $curent_name = $kernel->pub_httppost_get('name');
        $curent_mail = $kernel->pub_httppost_get('mail');
        $select_section = $kernel->pub_httppost_get('section');

        //Проверим, если пост не пуст, то форма уже заполнялась и может
        //уже можно формировать письмо подтверждением отсылки

        if (isset($_POST['name']) && isset($_POST['mail']))
            $result = $this->create_new_submit($curent_name, $curent_mail, $select_section);

        if ($result === true)
            return $template['sucсess_subscribe'];
        else
            $errore = $result;

		$news_lent = $kernel->pub_modules_get('newsi');
		$html_page = '';
		foreach ($news_lent as $key => $val)
		{
		    $select = ' checked';
		    if ((!empty($select_section)) && (!isset($select_section[$key])))
                $select = '';
            $caption =$kernel->pub_page_textlabel_replace($val['caption']);
            $html_page .= '<input id="'.$key.'" name="section['.$key.']" value="'.$caption.'" type="checkbox" '.$select.'><label for="'.$key.'">'.$caption.'</label><br>';
		}
        $html = $template['form_subscrip'];
		$html = str_replace("%pages_news%", $html_page, $html);
		$html = str_replace("%name%", $curent_name, $html);
		$html = str_replace("%mail%", $curent_mail, $html);
		$html = str_replace("%errore%", $errore, $html);
		return $html;
	}


	/*******************************************************
	Наборы внутренних методов модуля
    ********************************************************/


	function user_delete($user_id, $code)
	{
		$this->mysql_base->delet_user($user_id, $code);
		return $this->template_array['user_delet'];
	}

	/**
	 * Выводит форму для редактирования подписки
	 *
	 * @param string $errore Сообщение, выводимое как ошибка
	 */
	function user_controle($code, $errore = '')
	{
		global $kernel;
		//Вытащим уникальный код из параметров
		if (empty($code))
            return ;
		//Узнаем данные пользователя рассылки по коду
		$user = $this->mysql_base->get_info_user($code);

		if (empty($user))
			return $this->template_array['code_not_exist'];

		//Пользователь найден, отобразим для него форму
		$curent_name = $user['name'];
		if ($kernel->pub_httppost_get('name'))
			$curent_name = $kernel->pub_httppost_get('name');

		$curent_mail = $user['mail'];
		if ($kernel->pub_httppost_get('mail'))
			$curent_mail = $kernel->pub_httppost_get('mail');

		$select_section = $user['section'];
		if ($kernel->pub_httppost_get('section'))
			$select_section = $kernel->pub_httppost_get('section');

		$date = $user['date'];
		$id = $user['id'];

		$html_page = '';
		$news_lent = $kernel->pub_modules_get('newsi');
		foreach ($news_lent as $key => $val)
		{
            $select = '';
            $id_str_sec = 0;
            if (isset($select_section[$key]))
            {
            	$select = ' checked';
            	$id_str_sec = $select_section[$key];
            }
            $caption =$kernel->pub_page_textlabel_replace($val['caption']);
            $html_page .= '<input id="'.$key.'" name="section['.$key.']" value="'.$id_str_sec.'" type="checkbox" '.$select.'>&nbsp<label for="'.$key.'">'.$caption.'</label><br>';
		}
		$html = $this->template_array['form'];
		$html = str_replace("%pages_news%", $html_page, $html);
		$html = str_replace("%name%",   $curent_name, $html);
		$html = str_replace("%mail%",   $curent_mail, $html);
		$html = str_replace("%errore%", $errore, $html);
		$html = str_replace("%date%",   $date, $html);
		$html = str_replace("%id%",     $id, $html);
		$html = str_replace("%code%",   $code, $html);
		return $html;
	}
	/**
	 * Создаёт новую запись в базе, для подготовки рассылки и формирует письмо на
	 * на подтверждение
	 *
	 */
	function create_new_submit($name, $mail, $news)
	{
		global $kernel;
		//Узнаем значения из переданной нам формы
		$errore = '';
		//Проверим на заполненность всех полей
		if (!preg_match("/[а-яa-z-_\\. ]{4,255}/i",$name))
			$errore .= $this->template_array['errore_name'];

		if (!$kernel->pub_is_valid_email($mail))
			$errore .= $this->template_array['errore_email'];

	    $mail = mysql_real_escape_string($mail);
	    $name = mysql_real_escape_string($name);

		//Теперь проверим, что бы этого мыло ещё не было в подписчиках.
		if ($this->mysql_base->isset_email($mail))
			$errore .= $this->template_array['exist_subscrip'];

		if ((!is_array($news)) || (count($news) <= 0 ))
			$errore .= $this->template_array['not_news'];

		if (!empty($errore))
			return $errore;

		//Прошли все проверки, можно добавлять нового пользователя
		$code = $this->mysql_base->add_new_user($name, $mail, $news, '0');

		if (!empty($code))
		{
			//Всё хорошо, и можно сформировать письмо с подтеверждением подписки
			$html = $this->template_array['letter_confirm'];
			$html_news = '';
			foreach ($news as $val)
			{
			    $tmp = $this->template_array["letter_confirm_news_one"];
			    $tmp = str_replace("%name%", $val, $tmp);
				$html_news .= $tmp;
			}

			$page = $kernel->pub_modul_properties_get('page_submit');
			$page = $page['value'];
			$link = "http://".$_SERVER['HTTP_HOST'].'/'.$page.'.html?activatecode='.base64_encode(trim($code));

			$html = str_replace("%site_name%", $_SERVER['HTTP_HOST'], $html);
			$html = str_replace("%news%"     , $html_news           , $html);
			$html = str_replace("%link%"     , $link                , $html);

			//Определим мыло отправителя
			$host = preg_replace("/^www\\./", "", $_SERVER['HTTP_HOST']);
			$customer_email = "press-center@$host";
			$param = $kernel->pub_modul_properties_get('server_email');
			if (!empty($param['value']))
                $customer_email = $param['value'];

            //Определим заголовок письма
			$header = "Подтверждение подписки на сайте $host";
			$param = $kernel->pub_modul_properties_get('theme_mail_1');
			if (!empty($param['value']))
                $header = $param['value'];

            $customer_name = "Robot";
			$param = $kernel->pub_modul_properties_get('server_user');
			if (!empty($param['value']))
                $customer_name = $param['value'];
			$kernel->pub_mail(array($mail), array($name), $customer_email, $customer_name, $header, $html);
		}
		return true;
	}

	/**
	 * Производит активацию юзера в рассылке
	 *
	 * @param string $code MD5 от ящика и приставки
	 */
	function user_activate($code)
	{
		global $kernel;

		//Сначала проверим
		//Что данный код есть в базе
		$num_user = $this->mysql_base->veref_user($code);
		if ($num_user > 0)
		{
			$data = array();
			$data['submit'] = '1';
			$this->mysql_base->update_user($num_user, $data);

			$html = $this->template_array['activate_success'];
		}
		else
		{
			$page = $kernel->pub_modul_properties_get('page_submit');
			$page = $page['value'];
			$link = '/'.$page. ".html";
			$html = $this->template_array['activate_faild'];
			$html = str_replace("%link%", $link, $html);
		}
		return $html;
	}

    /********************************************************
    Наборы методов, для работы с админкой модуля
    *********************************************************/


	/**
	 * Предопределйнный метод, используется для вызова административного интерфейса модуля
	 *
	 */
	function start_admin()
	{
		global $kernel;
		$html_content = '';
		switch ($kernel->pub_section_leftmenu_get())
		{
		    //Покажем список сущестующих подписчиков
		    case 'show_users':
		        $page = 1;
		        if ($kernel->pub_httpget_get('page'))
		        	$page = $kernel->pub_httpget_get('page');
		        if ($page <= 0)
		            $page = 1;
		        $html_content = $this->users_show_all($page);
		        break;

		    //Добавляем нового подписчика через форму
		    case 'add_user':
		        $html_content = $this->users_edit();
		        break;

		    //Редактируем конкртеного подписчика
		    case 'edit_user':
		        $html_content = $this->users_edit($kernel->pub_httpget_get('user_id'));
                break;

		    //Действия из формы подписчика (удалить, активировать, сохранить
            case 'action_user':
                if ($kernel->pub_httppost_get('save_user'))
	       	        $this->user_save();
                elseif ($kernel->pub_httppost_get('delete_user'))
    				$this->users_delete($kernel->pub_httppost_get('id_user'));
    		    elseif ($kernel->pub_httppost_get('activate_user'))
    		    {
        			$data = array();
        			$data['submit'] = '1';
        			$this->mysql_base->update_user($kernel->pub_httppost_get('id_user'), $data);
    		    }
                $kernel->pub_redirect_refresh_reload('show_users');
                break;

		    case 'delete_users':
				$this->users_delete($kernel->pub_httppost_get('select'));
				$kernel->pub_redirect_refresh_reload('show_users');
				break;

			case 'show_news_submit':
			    $html_content = $this->show_form_submit();
		        break;

		    case 'run_submit':
		        $this->run_submit();
		        die;
		        break;

            case 'add2cron2':
                $this->add2cron2();
                return $kernel->pub_httppost_response("OK","show_news_submit");
                break;
		}
		return $html_content;
	}

    /**
     * Функция для построения меню для административного интерфейса
     *
     * @param pub_interface $menu Обьект класса для управления построением меню
     * @return boolean true
     */
	function interface_get_menu($menu)
	{
        $menu->set_menu_block('[#module_newssubmit_menu_label#]');
        $menu->set_menu("[#module_newssubmit_menu_show_users#]","show_users");
        $menu->set_menu("[#module_newssubmit_menu_add_user#]","add_user");
        $menu->set_menu("[#module_newssubmit_menu_show_news_submit#]","show_news_submit");
        $menu->set_menu_default('show_users');
	    return true;
	}

	/********************************************************
	 Методы обеспечения работы административного интерфейса
	*********************************************************/

	function users_show_all($page)
	{
		global $kernel;
		$template = $kernel->pub_template_parse("modules/newssubmit/templates_admin/list_users.html");
		$html = $template['form'];

		//Узнаем сколько новостных лент, что бы вывести по ним колонки
		$sort_section = array();
		$lenta_news_label = "";
		$lenta_news_count = 0;

		$news_lent = $kernel->pub_modules_get('newsi');
		foreach ($news_lent as $key => $val)
		{
            $lenta_news_label .= '<th>'.$val['caption'].'</th>';
            $sort_section[$key] = 0;
            $lenta_news_count ++;
		}

		$html = str_replace("%lenta_news_count%", $lenta_news_count, $html);
		$html = str_replace("%lenta_news_label%", $lenta_news_label, $html);

		//Теперь собсвтенно необходимо вывести подписчиков
		$users = $this->mysql_base->get_all_user(null, ($page -1));

		$html_users = '';

		$i = 1;
		foreach ($users as $key => $val)
		{
      		//Определим на что подписан пользователь
      		$user_news = '';
      		foreach ($news_lent as $news_page => $num)
      		{
      			if (isset($val['section'][$news_page]))
      			{
      				$user_news .= '<td align="center"><img src="/admin/templates/default/images/24-em-check.gif"/></td>';
      				$sort_section[$news_page]++;
      			}
      			else
      				$user_news .= '<td>&nbsp;</td>';
      		}

		    //Сначала покажем постоянные поля
			$pname = $val['name'];
			if ($val['off'] == '1')
				$pname = '<font color="#990000">'.$pname.'</font>';

            $user = $template['user'];
			$user = str_replace("%class%"     , $kernel->pub_table_tr_class($i), $user);
			$user = str_replace("%num%"       , $i, $user);
			$user = str_replace("%name%"      , $pname, $user);
			$user = str_replace("%mail%"      , $val['mail'], $user);
			$user = str_replace("%user_id%"   , $key, $user);
			$user = str_replace("%lenta_news%", $user_news, $user);
            $html_users .= $user;
			$i++;
		}

		//Сформируем постраничную навигацию
		$pages = $template['pages'];

		$num_pages = $this->mysql_base->get_num_pages();
		$arr_pages = array();
		for ($i = 1; $i <= $num_pages; $i++)
		{
		    $tmp = $template['page_activ'];
		    if ($i == $page)
		        $tmp = $template['page_passiv'];

            $arr_pages[] = str_replace("%num_page%", $i, $tmp);
		}
		$pages = str_replace("%pages%", implode($template['page_delimiter'], $arr_pages), $pages);

		//Заполним окончательно все переменные
		$html = str_replace("%pages%"      , $pages , $html);
        $html = str_replace('%form_url%'   , $kernel->pub_redirect_for_form('delete_users'), $html);
        $html = str_replace('%users_count%', count($users), $html);
		$html = str_replace('%users%'      , $html_users, $html);

		return $html;
	}

	/**
	 * Форма редактирования и добавления подписчика
	 *
	 * @param int $id_user
	 * @return HTML
	 */
	function users_edit($id_user = -1)
	{
		global $kernel;
		$html = file_get_contents("modules/newssubmit/templates_admin/edit_user.html");
		$html = str_replace('%url_form%', $kernel->pub_redirect_for_form('action_user'), $html);

		//Если форма редактирования существующего пользователя то проверим нужно ли
		//активировать кнопку активации
		$button_activ = ' disabled="disabled"';
		$button_del   = ' disabled="disabled"';
		if ($id_user > 0 )
		{
            $user = $this->mysql_base->get_all_user($id_user);
            $user = $user[$id_user];
            $button_del = '';
		    if ((!isset($user['activate'])) || !($user['activate'] == '1'))
                $button_activ = '';
		}

		//Сформируем список новостных групп
		$select_section = array();
		if (isset($user['section']))
		  $select_section = $user['section'];

		$news_lent = $kernel->pub_modules_get('newsi');

		$html_page = '';
		foreach ($news_lent as $key => $val)
		{
				$select = '';
				$id_str_sec = 0;
				if (isset($select_section[$key]))
				{
					$select = ' checked';
					$id_str_sec = $select_section[$key];
				}

				$html_page .= '<input id="'.$key.'" name="section['.$key.']" value="'.$id_str_sec.'" type="checkbox" '.$select.'>&nbsp;&nbsp;<label for="'.$key.'">'.$val['caption'].'</label><br>';
		}

		//Доформируем остальные поля
		$str_data = date("d-m-Y");
		if (isset($user['date']))
            $str_data = $user['date'];


		if ((!isset($user['activate'])) || !($user['activate'] == '1'))
			$str_data .= '&nbsp;&nbsp;&nbsp;<font size="-2">[#newssubmit_admin_user_control_not_activate#]</font>';

		if ((isset($user['activate'])) && ($user['off'] == '1'))
			$html = str_replace("%off_status%", 'checked', $html);
		else
			$html = str_replace("%off_status%", '', $html);

		$name = '';
		$mail = '';
	    if (isset($user['name']))
            $name = $user['name'];

        if (isset($user['mail']))
            $mail = $user['mail'];

		$html = str_replace("%pages_news%", $html_page, $html);
		$html = str_replace("%name%", $name, $html);
		$html = str_replace("%mail%", $mail, $html);
		$html = str_replace("%errore%", '', $html);
		$html = str_replace("%date%", $str_data, $html);
		$html = str_replace("%id_user%", $id_user, $html);
		$html = str_replace("%button_activ%", $button_activ, $html);
		$html = str_replace("%button_del%", $button_del, $html);

		return $html;
	}

	/**
	 * Сохраняет изменённые данные о подписке
	 *
	 * @return unknown
	 */
	function user_save()
	{
		global $kernel;
		$my_post = $kernel->pub_httppost_get();
		$errore = '';
		$name = $kernel->pub_httppost_get('name');
		if (!preg_match("/[а-яa-z-_\\. ]{4,255}/i",$name))
			$errore .= "Ошибка в имени пользователя<br>";

		$mail = $kernel->pub_httppost_get('mail');
		if (!preg_match("/^.+@.+\\..+$/i",$mail))
			$errore .= "Ошибка в адресе электронной почты<br>";

		$news = array();
		if (isset($my_post['section']))
			$news = $my_post['section'];
		else
			$errore .= "Необходимо отметить хотя бы одну новостную группу<br>";

		if (!empty($errore))
			return '';

		//Обновим данные о пользователи
		$user_id = $kernel->pub_httppost_get('id_user'); //Ссылка на id в таблице подписчиков
		$data = array();
		$data['name'] = $name;
		$data['mail'] = $mail;
		$data['control'] = md5($this->pristavka.$mail);
		if (isset($my_post['off_user']))
			$data['off'] = '1';
		else
			$data['off'] = '0';

		//Это редакатирование пользователя
		if ($user_id <= 0)
            $this->mysql_base->add_new_user($name, $mail, $news);
	    elseif ($user_id > 0)
	    {
            $this->mysql_base->update_user($user_id, $data);

            //Теперь надо обновить данные о секциях
            //Сначала создаим массив с ID рубрик, от которых пользователь отписался
            $user = $this->mysql_base->get_all_user($user_id);
            $user = $user[$user_id];
            $section = $user['section'];
            foreach ($section as $key => $val)
            {
            	if (!isset($news[$key]))
            		$this->mysql_base->delete_news_group($val);
            }
            //Теперь добавим новые группы
            foreach ($news as $key => $val)
            {
            	if (intval($val) == 0)
            		$this->mysql_base->add_news_group($user_id, $key);
            }
	    }
		return $user_id;
	}


	function users_delete($users)
	{
        if (empty($users))
            return false;
        if (is_array($users))
        {
            foreach ($users as $key => $val)
            {
                $this->mysql_base->delet_user($key);
            }
        }
        else
            $this->mysql_base->delet_user($users);
	}


	/**
	 * Отображает форму выбора новостей и начала рассылки
	 *
	 */
	function show_form_submit()
	{
		global $kernel;

		$this->template_array = $kernel->pub_template_parse("modules/newssubmit/templates_admin/list_submit.html");
		$html = $this->template_array["form"];
		$html = str_replace("%url_form%", $kernel->pub_redirect_for_form('run_submit'), $html);
        $html = str_replace("%add2cron2_form_submit%", $kernel->pub_redirect_for_form('add2cron2'), $html);

		//узнаем новости, которые подлежат рассылки и Сформируем
		$html_news = '';

		$news_lent = $kernel->pub_modules_get('newsi');
		foreach ($news_lent as $key => $val)
		{
			//Теперь узнаем, какие новости для этой рубрики нам нужно отослать
			$html_news .= $this->get_news_for_submit($key, $val['caption']);

		}
		$html = str_replace("%news_for_submit%", $html_news, $html);
		return $html;
	}


	/**
	 * Возвращает html-ку для конкрентой рубрике при построение формы
	 * с новостями, готовыми к отправке
	 *
	 */
	function get_news_for_submit($section, $caption)
	{
		$html = '';

		$arr = $this->mysql_base->get_news_for_submit($section);

		if (count($arr) == 0)
			return $html;

	    //Сформируем собсвтенно HTML с новостями
	    //Заголовок блока
	    $html = $this->template_array['news_lenta'];
	    $html = str_replace("%name%", $caption, $html);
	    $html = str_replace("%id%", $section, $html);

	    //Теперь смотрим сами новости и добавляем их.
		$i = 1;
		foreach ($arr as $key => $val)
		{
		    $check = '';
			$parts = explode('-', substr($val['time'],0,10));
			$date = trim($parts[2]).'.'.trim($parts[1]).'.'.trim($parts[0]);

            $html_news = $this->template_array['news_one'];
	        $html_news = str_replace("%num%"     , $i              , $html_news);
	        $html_news = str_replace("%section%" , $section        , $html_news);
	        $html_news = str_replace("%id%"      , $key            , $html_news);
	        $html_news = str_replace("%check%"   , $check          , $html_news);
	        $html_news = str_replace("%date%"    , $date           , $html_news);
	        $html_news = str_replace("%header%"  , $val['header']  , $html_news);
	        $html_news = str_replace("%announce%", $val['announce'], $html_news);
	        $html .= $html_news;
	        $i++;
		}
		return $html;
	}


	/**
	 * Собственно осуществляет рассылку
	 *
	 */
	function run_submit($send_all = false)
	{
		global $kernel;
		set_time_limit(0);

        //Получим шаблон используемый для отсылки письма
		$prom = $kernel->pub_modul_properties_get("template_send");
		if ($prom["isset"])
		{
            if (file_exists($prom["value"]))
                $this->template_array = $kernel->pub_template_parse($prom["value"]);
		}

		if (empty($this->template_array))
            return die("Template not found");

		//Прежде всего определим, это тестовая рассылка или нет
		$prop = $kernel->pub_modul_properties_get("is_test");
        if ($prop["value"])
            $array_for_submit = $this->prepare_html_for_submit($send_all, false);
        else
            $array_for_submit = $this->prepare_html_for_submit($send_all, true);


        //Проверим, а есть ли-нам что отсылать вообще
		$start_text  = $kernel->pub_httppost_get('start_text', false);
		$end_text    = $kernel->pub_httppost_get('end_text', false);

		//Проверка на то, что хоть что-то должно быть для рассыдки
		if (($array_for_submit === false) && (empty($start_text)) && (empty($end_text)))
		{
    		print "<h3>Not data for submit</h3>";
    		print str_repeat(" ", 500);
    		print "<br>";
    		flush();
    		die;
		}

		//Теперь получим список людей, которым нужно сделать рассылку
		$users = $this->mysql_base->get_users_for_submit();

		//Теперь собственно можно перебирать юзеров и отсылать им те секции, которые нужны
		$count = 0;
        print "<h3>Start send...</h3>";
        print str_repeat(" ", 500);
        print "<br>";
        flush();
        $page = $kernel->pub_modul_properties_get('page_submit', $kernel->pub_module_id_get());
        $page = $page['value'];
        //Определим заголовок письма
        $header = 'Расылка от '.$this->full_name_serv;
        $param = $kernel->pub_modul_properties_get('theme_mail_2');
        if (!empty($param['value']))
            $header = $param['value'];


        $host = preg_replace("/^www\\./", "", $_SERVER['HTTP_HOST']);
        $from_email = "press-center@$host";
        $param = $kernel->pub_modul_properties_get('server_email');
        if (!empty($param['value']))
            $from_email = $param['value'];

        $from_name = "Robot";
        $param = $kernel->pub_modul_properties_get('server_user');
        if (!empty($param['value']))
            $from_name = $param['value'];

		foreach ($users as $val)
		{
		    //Соберём для этого пользователя необходимые ему
		    //секции в массив
			$html_news = array();
			foreach ($val['section'] as $section)
			{
				if (isset($array_for_submit[$section]))
                    $html_news[] = $array_for_submit[$section];
			}

            //Определим урл для управления рассылкой
			$link = $this->full_name_serv.'/'.$page.'.html?controlcode='.trim($val['code']);


			//Дособировываем текст рассылки
			$html_submit = $this->template_array['mail'];

			$html_submit = str_replace("%start_text%", $start_text          , $html_submit);
			$html_submit = str_replace("%base_link%" , $this->full_name_serv, $html_submit);
			$html_submit = str_replace("%end_text%"  , $end_text            , $html_submit);
			$html_submit = str_replace("%user_link%" , $link                , $html_submit);

			$html_submit = str_replace("%news%", implode($this->template_array['news_block_delimiter'], $html_news), $html_submit);

			//Проверим, если этому пользователю нечего рассылать, то не будем ничего слать
            if ((count($html_news) <= 0) && (empty($start_text)) && (empty($end_text)))
                continue;

			//Собственно можно делать отсылку
		    if ($kernel->pub_mail(array($val['mail']),array($val['name']), $from_email, $from_name, $header, $html_submit))
				$str_print = "Send to: ".trim($val['mail']);
			else
				$str_print = "<b>Errore: ".trim($val['mail']);

            print $str_print;
            print str_repeat(" ", 500);
            print "<br>";
            flush();
			$count++;
		}
		return $count;
	}


	/**
	 * Формирует непосредственно HTML для рассылки
	 * Если задан первый параметр, то значит рассылаем все новости, без привязки
	 * к тому, что отметил пользователь
	 * @return $html;
	 */
	function prepare_html_for_submit($send_all = false, $set_submit = true)
	{
		global $kernel;

		$my_post = $kernel->pub_httppost_get();

		//Создадим ссылки на ВСЕ разделы новостных лент
		$arr_modules = $kernel->pub_modules_get('newsi');
		$link_for_news = array();
		foreach ($arr_modules as $key => $val)
		    $link_for_news[$key] = $this->priv_create_link_razdel($key);

		//Нужно полуить секции и в них id новостей
		//которые будут рассылаться
		//Если передаётся $send_all, значит отсылать надо все
		//новости, и в этом случае мы смотрим уже не POST
		//а просто получаем все достпные для рассылки новости и шлём их\
		//такой способ используется при отсылке кроном.
		$array_news = array();
		if ($send_all)
		{
		    //В этом случае масив будет содержать чуть больше данных, но это не важно.
		    foreach ($arr_modules as $key => $val)
		       $array_news[$key] = $this->mysql_base->get_news_for_submit($key);
		}
		else
		{
            if (isset($my_post['news']))
                $array_news = $my_post['news'];
		}

		//Проверка на то, что хоть что-то должно быть для рассыдки
		if (empty($array_news))
            return false;

        //Проверку прошли. Можно начинать формирование письма
		$time = date("Y-m-d H:i:s");
		$array_html_news = array();

		foreach ($array_news as $section => $news_in_setion)
		{
            //Сформируем HTML новостей, входящих в секцию (модуль)
            $news_in_block = array();
			foreach ($news_in_setion as $news_id => $val)
			{
				//Получим данные на новость
				$news = $this->mysql_base->get_news($news_id, $time, $set_submit);

				//Привили дату к нашему формату
				$parts = explode('-', $news['date']);
				$date = trim($parts[2]).'.'.trim($parts[1]).'.'.trim($parts[0]);

				$url = $link_for_news[$section]['link'].'?id='.$news['id'];

				//Начали формировать HTML c одной новостью
				$tmp = $this->template_array['news'];

				//Заполняем шаблон
				$tmp = str_replace("%data%"     , $date          , $tmp);
				$tmp = str_replace("%time%"     , $news['time']  , $tmp);
				$tmp = str_replace("%header%"   , $news['header'], $tmp);
				$tmp = str_replace("%url%"      , $url           , $tmp);
				$tmp = str_replace("%url_block%", $link_for_news[$section]['link']  , $tmp);
				$tmp = str_replace("%description_short%", $news['description_short'], $tmp);
				$tmp = str_replace("%description_full%" , $news['description_full'] , $tmp);

				$news_in_block[] = $tmp;
			}

		    //Формируем итоговый блок по новости
			$html = $this->template_array['news_block'];
			$html = str_replace("%name%", $link_for_news[$section]["name"], $html);
			$html = str_replace("%url%", $link_for_news[$section]["link"], $html);
			$html = str_replace("%news%", implode($this->template_array['news_delimiter'],$news_in_block), $html);

			$array_html_news[$section] = $html;
		}
		return $array_html_news;
	}

	/**
	 * Формирует ссылку, на страницу сайта, на которой выводиться архив
	 * новостной ленты модуля с переданным ID
	 *
	 * Используется для формирования ссылок на разделы новостей в рассылке
	 * @param string $id_modul
	 * @return array
	 */
    function priv_create_link_razdel($id_modul)
    {
        global $kernel;

        $query = "SELECT `id`, `caption`, `serialize`
                  FROM `".$kernel->pub_prefix_get()."_structure`
                 ";
        $result = $kernel->runSQL($query);
        $ret['link'] = '';
        $ret['name'] = '';
        while ($row = mysql_fetch_assoc($result))
        {
            $arr = unserialize($row['serialize']);
			if (count($arr) > 0 && $arr !== false)
			{
                foreach ($arr as $val)
                {
                    if (trim($val['id_mod']) == trim($id_modul))
                    {
                        if ($val['run']['name'] == "pub_show_archive")
                        {
                            //Сформируем ссылку на страницу, где вызывается
                            //метод этого модуля - вывести архив
                            $ret['link'] = $this->full_name_serv."/".$row['id'].".html";
                            $ret['name'] = $row['caption'];
                        }
                    }
                }
			}

        }
        return $ret;
    }
}
?>