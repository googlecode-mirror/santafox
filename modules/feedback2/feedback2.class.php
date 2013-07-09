<?php
require_once realpath(dirname(__FILE__)."/../../")."/include/basemodule.class.php";
class feedback2 extends BaseModule
{
    protected function fix_field_params($field)
    {
        if (!$field['params'])
            $params = array('values'=>array());
        else
            $params = json_decode($field['params'],1);
        return $params;
    }

    protected function show_field($id)
    {
        global $kernel;
        $field = $this->get_field($id);
        if (!$field)
            return "404";
        $this->set_templates($kernel->pub_template_parse('modules/feedback2/templates_admin/formfield.html'));

        $content = $this->get_template_block('content');

        if ($field['ftype']=='select')
        {
            $type_specific=$this->get_template_block('type_select');

            $params = $this->fix_field_params($field);

            $select_lines = array();
            foreach($params['values'] as $select_val)
            {
                $select_line = $this->get_template_block('selectval_line');
                $select_line = str_replace('%value%',htmlspecialchars($select_val),$select_line);
                $select_line = str_replace('%value_urlencoded%',urlencode($select_val),$select_line);
                $select_lines[]=$select_line;
            }
            $type_specific = str_replace('%selectvals%',implode("\n",$select_lines),$type_specific);
        }
        else
            $type_specific='';
        $content = str_replace('%type_specific%',$type_specific,$content);

        $content = str_replace('%reqchecked%',$field['required']?' checked':'',$content);
        $content = str_replace('%id%',$field['id'],$content);

        $content = str_replace('%order%',$field['order'],$content);
        $content = str_replace('%name%',htmlspecialchars($field['name']),$content);
        $content = str_replace('%human_type%',$this->get_human_type($field['ftype']),$content);

        return $content;
    }

    protected function save_field($id,$name,$req,$order=null)
    {
        global $kernel;

        $field = $this->get_field($id);
        if (!$field)
            return $kernel->pub_httppost_errore("404",'show_form_fields');

        $urec = array(
            'name'=>$name,
            'required'=>$req?1:0,
        );
        if (!is_null($order))
            $urec['order']=intval($order);
        $kernel->db_update_record($this->get_fields_tablename(),$urec,"id=".$id);
        return $kernel->pub_httppost_response('[#feedback2_form_field_saved_msg#]','show_form_fields');
    }

    protected function get_field($id)
    {
        if (!$id)
            return null;
        global $kernel;
        return $kernel->db_get_record_simple($this->get_fields_tablename(),"id=".$id);
    }



    protected function add_select_val($fieldid,$val)
    {
        $field = $this->get_field($fieldid);
        if (!$field)
            return;
        $params = $this->fix_field_params($field);
        if (!in_array($val,$params['values']))
            $params['values'][]=$val;
        $this->save_field_params($fieldid,$params);
    }


    protected function generate_frontend_form()
    {
        global $kernel;

        $newtpl = $kernel->pub_httppost_get('newtpl');
        if(!preg_match('~\.html?$~',$newtpl))
            $newtpl.='.html';

        $fields = $this->get_fields();
        $this->set_templates($kernel->pub_template_parse('modules/feedback2/templates_user/_tpl.html'));
        $content = "<!-- @content -->\n".$this->get_template_block('before_fields');
        $flines = array();
        $requiredIDs = array();
        foreach($fields as $field)
        {
            if ($field['required']==1)
            {
                $requiredIDs[]=$field['id'];
                $blockName = 'field_type_'.$field['ftype'].'_required';
            }
            else
                $blockName = 'field_type_'.$field['ftype'];
            $fline = trim($this->get_template_block($blockName));
            if($field['ftype']=='select')
            {
                $fparams=$this->fix_field_params($field);
                $options = array();
                foreach($fparams['values'] as $value)
                {
                    $option = $this->get_template_block('field_type_select_option');
                    $option = str_replace('%value%',htmlspecialchars($value),$option);
                    $options[]=$option;
                }
                $fline = str_replace('%options%',implode("\n",$options),$fline);
            }
            $fline = str_replace("%name%",htmlspecialchars($field['name']),$fline);
            $fline = str_replace("%id%",$field['id'],$fline);

            $flines[]=$fline;
        }
        $content .= implode($this->get_template_block('fields_separator'),$flines);
        $content.=$this->get_template_block('after_fields');

        $content = str_replace('%json_req_ids%',json_encode($requiredIDs),$content);

        $blocks = array('form_ok_msg',
            'form_error_required_field_not_filled',
            'captcha','form_error_incorrect_captcha',
            'form_error_incorrect_select_field_option',
        );
        foreach($blocks as $block)
        {
            $content.="\n<!-- @".$block." -->\n".trim($this->get_template_block($block));
        }


        $kernel->pub_file_save($kernel->pub_site_root_get().'/modules/feedback2/templates_user/'.$newtpl,$content);
        return $kernel->pub_httppost_response('[#feedback2_frontent_form_generated_msg#]','show_form_fields');
    }


    protected function save_field_params($fieldid,$params)
    {
        global $kernel;
        $kernel->db_update_record($this->get_fields_tablename(),array('params'=>mysql_real_escape_string(json_encode($params))),"id=".$fieldid);
    }

    protected function delete_select_val($fieldid,$val)
    {
        $field=$this->get_field($fieldid);
        if (!$field)
            return ;
        $params = $this->fix_field_params($field);
        $key=array_search($val,$params['values']);
        if ($key===false)
            return;
        unset($params['values'][$key]);
        $this->save_field_params($fieldid,$params);
    }

    /**
     * Функция для отображения административного интерфейса
     *
     * @return string
     */
    public function start_admin()
    {
        global $kernel;
        switch ($kernel->pub_section_leftmenu_get())
        {
            case 'generate_frontend_form':
                return $this->generate_frontend_form();
            case 'delete_select_val':
                $fieldid=intval($kernel->pub_httpget_get('id'));
                $this->delete_select_val($fieldid,$kernel->pub_httpget_get('value'));
                $kernel->pub_redirect_refresh('show_field&id='.$fieldid);
                break;
            case 'add_select_val':
                $fieldid=intval($kernel->pub_httppost_get('fieldid'));
                $this->add_select_val($fieldid,$kernel->pub_httppost_get('newselectval'));
                return $kernel->pub_httppost_response('[#feedback2_form_field_select_value_added_msg#]','show_field&id='.$fieldid);
            case 'save_field':
                return $this->save_field(intval($kernel->pub_httppost_get('id')),$kernel->pub_httppost_get('name'),$kernel->pub_httppost_get('required'),$kernel->pub_httppost_get('order'));
            case 'show_field':
                return $this->show_field($kernel->pub_httpget_get('id'));
            case 'add_field':
                $type = $kernel->pub_httppost_get('type');
                $newID=$this->add_field($kernel->pub_httppost_get('name'),$type,$kernel->pub_httppost_get('required'));
                if (!$newID)
                    return $kernel->pub_httppost_errore('[#feedback2_no_req_fields_msg#]',1);
                if($type=='select')
                    $redir2='show_field&id='.$newID;
                else
                    $redir2='show_form_fields';
                return $kernel->pub_httppost_response('[#feedback2_field_added_msg#]',$redir2);
            case 'delete_field':
                $this->delete_field(intval($kernel->pub_httpget_get('id')));
                $kernel->pub_redirect_refresh('show_form_fields');
                break;
            case 'show_form_fields':
                return $this->show_form_fields();
        }
        return '';
    }

    protected $forder_inc=5;
    protected function get_next_order()
    {
        global $kernel;
        $rec = $kernel->db_get_record_simple($this->get_fields_tablename(),"moduleid='".$kernel->pub_module_id_get()."'","MAX(`order`) AS `order`");
        return $rec['order']+$this->forder_inc;
    }

    protected function add_field($name,$type,$req)
    {
        if (!$name || !$type)
            return 0;
        global $kernel;
        $rec = array(
            'name'=>$name,
            'ftype'=>$type,
            'required'=>$req?1:0,
            'moduleid'=>$kernel->pub_module_id_get(),
            'order'=>$this->get_next_order(),
        );
        return $kernel->db_add_record($this->get_fields_tablename(),$rec);
    }
    protected function delete_field($id)
    {
        global $kernel;
        $kernel->runSQL("DELETE FROM ".$kernel->pub_prefix_get().$this->get_fields_tablename()." WHERE id=".$id);
    }


    protected function get_fields_tablename()
    {
        return "_feedback2_fields";
    }

    protected function get_fields()
    {
        global $kernel;
        return $kernel->db_get_list_simple($this->get_fields_tablename(),"true ORDER BY `order`");
    }


    protected function get_human_type($type)
    {
        $arr = array(
            'select'=>'[#feedback2_type_select#]',
            'string'=>'[#feedback2_type_string#]',
            'textarea'=>'[#feedback2_type_textarea#]',
            'file'=>'[#feedback2_type_file#]',
            'checkbox'=>'[#feedback2_type_checkbox#]',
        );

        if (!isset($arr[$type]))
            return '???';
        return $arr[$type];
    }

    public function pub_show_form($template)
    {
        global $kernel;
        $this->set_templates($kernel->pub_template_parse($template));
        $message = '';

        $useCaptcha = $this->get_module_prop_value('show_captcha');
        $content = $this->get_template_block('content');

        $fields = $this->get_fields();
        $filled_fields = array();

        if(isset($_POST['feedback2']) && is_array($_POST['feedback2']))
        {
            $errors = array();
            if ($useCaptcha && !$this->is_valid_captcha($kernel->pub_httppost_get('captcha')))
                $errors[] = $this->get_template_block('form_error_incorrect_captcha');

            foreach($fields as $field)
            {
                $fieldID = $field['id'];

                if ($field['ftype']=='file')
                {
                    if (!isset($_FILES['feedback2']['tmp_name'][$fieldID]) || !is_uploaded_file($_FILES['feedback2']['tmp_name'][$fieldID]))
                        $val = null;
                    else
                        $val = $_FILES['feedback2']['tmp_name'][$fieldID];
                }
                else
                {
                    if (!array_key_exists($fieldID,$_POST['feedback2']) || !is_scalar($_POST['feedback2'][$fieldID]) || mb_strlen($_POST['feedback2'][$fieldID])==0)
                        $val = null;
                    else
                        $val = $_POST['feedback2'][$fieldID];
                }

                $error = null;

                if ($field['required']==1 && is_null($val))
                    $error = $this->get_template_block('form_error_required_field_not_filled');
                elseif($field['ftype']=='select')
                {
                    $param = $this->fix_field_params($field);
                    if (!in_array($val,$param['values']))
                        $error = $this->get_template_block('form_error_incorrect_select_field_option');
                }


                if ($error)
                {
                    $error = str_replace('%name%',$field['name'],$error);
                    $errors[]=$error;
                }
                else
                {
                    $field['value']=$val;
                    $filled_fields[]=$field;
                }


            }

            if(!$errors)
            {
                $message = $this->get_template_block('form_ok_msg');
                $email_tpl_file = $this->get_module_prop_value('email_tpl');
                $email_tpl=$kernel->pub_template_parse($email_tpl_file);
                $email_subj = isset($email_tpl['email_subj'])?trim($email_tpl['email_subj']):'feedback2';
                $email_body = isset($email_tpl['email_body'])?$email_tpl['email_body']:'';

                $flines = array();
                $attached_files = array();
                foreach($filled_fields as $field)
                {
                    if ($field['ftype']=='file')
                    {
                        $fn = $_FILES['feedback2']['name'][$field['id']];
                        $tmp_path = sys_get_temp_dir().'/'.$fn;
                        //сначала пробуем записать в системную temp-папку
                        if (!@move_uploaded_file($field['value'],$tmp_path))
                        {
                            //если не получилось (например ошибка "open_basedir restriction in effect"), то пробуем сохранить в папке для бэкапов
                            $tmp_path = $kernel->pub_site_root_get()."/backup/".$fn;
                            if (!move_uploaded_file($field['value'],$tmp_path))
                                $tmp_path=$field['value'];//если и так не получилось - аттачим что есть
                        }
                        $attached_files[]=$tmp_path;
                        continue;
                    }
                    $fline = isset($email_tpl['field_line'])?$email_tpl['field_line']:'%name% %value%<br>';

                    $fline = str_replace('%name%',htmlspecialchars($field['name']),$fline);
                    $fline = str_replace('%value%',htmlspecialchars($field['value']),$fline);
                    $flines[]=$fline;
                }
                $email_body = str_replace('%fields%',implode("\n",$flines),$email_body);
                $to_email = $this->get_module_prop_value('admin_email');
                $from_email = $this->get_module_prop_value('email_from');
                if (!$from_email)
                    $from_email = 'noreply@'.$_SERVER['HTTP_HOST'];
                $kernel->pub_mail(array($to_email),array($to_email),$from_email,$from_email,$email_subj,$email_body,false,'',$attached_files);
                foreach($attached_files as $afile)
                {
                    @unlink($afile);
                }
                $filled_fields = array();
            }
            else
                $message = implode("\n",$errors);
        }

        if($useCaptcha)
        {
            $captcha = $this->get_template_block('captcha');
            $captcha = str_replace('%captcha_url%',$this->get_captcha_img_url(),$captcha);
        }
        else
            $captcha = '';
        $content = str_replace('%captcha%',$captcha,$content);
        $content = str_replace('%message%',$message,$content);
        foreach($filled_fields as $field)
        {
            if($field['ftype']=='file')
                continue;
            $val=$field['value'];

            if($field['ftype']=='checkbox' && $val)
                $content = str_replace("%feedback2_".$field['id']."_checked%",' checked',$content);
            elseif($field['ftype']=='select')
                $content = str_replace("%feedback2_".$field['id']."_".$val."_option%"," selected",$content);
            else
                $content = str_replace("%feedback2_".$field['id']."_value%",htmlspecialchars($val),$content);
        }
        $content = preg_replace('~%feedback2_(\d+)_value%~','',$content);
        $content = preg_replace('~%feedback2_(\d+)_checked%~','',$content);
        $content = preg_replace('~%feedback2_(\d+)_(.+)_option%~','',$content);
        return $content;
    }

    protected function show_form_fields()
    {
        global $kernel;
        $this->set_templates($kernel->pub_template_parse('modules/feedback2/templates_admin/formfields.html'));
        $fields = $this->get_fields();
        if (!$fields)
            $list = $this->get_template_block('list_null');
        else
        {
            $list = $this->get_template_block('list');
            $lines = array();
            foreach($fields as $field)
            {
                $line = $this->get_template_block('line');
                $line = str_replace("%name%",htmlspecialchars($field['name']),$line);
                $field['human_type']=$this->get_human_type($field['ftype']);
                if ($field['required']==1)
                    $r='[#feedback2_field_required_yes#]';
                else
                    $r='[#feedback2_field_required_no#]';
                $field['req_label']=$r;
                $line = $kernel->pub_array_key_2_value($line,$field);
                $lines[]=$line;
            }
            $list = str_replace("%lines%",implode("\n",$lines),$list);
        }
        $content = $this->get_template_block('content');
        $content = str_replace('%list%',$list,$content);

        return $content;
    }

    /**
     * Функция для построения меню для административного интерфейса
     *
     * @param pub_interface $menu Обьект класса для управления построением меню
     * @return boolean true
     */
    public function interface_get_menu($menu)
    {
        $menu->set_menu_block('[#feedback2_modul_base_name#]');
        $menu->set_menu("[#feedback2_form_fields#]","show_form_fields");

        $menu->set_menu_default('show_form_fields');
        return true;
    }
}