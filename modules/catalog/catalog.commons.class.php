<?php

class CatalogCommons
{

    /**
     * Префикс путей к шаблонам административного интерфейса
     *
     * @var string
     */
    private static $templates_admin_prefix = 'modules/catalog/templates_admin/';

    /**
     * Префикс путей к шаблонам frontend
     *
     * @var string
     */
    private static $templates_user_prefix = 'modules/catalog/templates_user/';


    /**
     * Возвращает свойство КАТЕГОРИИ по id-шнику
     *
     * @param integer $id  id-шник свойства
     * @return array
     */
    public static function get_cat_prop($id)
    {
        global $kernel;
        return $kernel->db_get_record_simple('_catalog_'.$kernel->pub_module_id_get().'_cats_props',"id=".$id);
    }


    /**
     * Возвращает запись товара по id-шнику (только common-свойства)
     *
     * @param integer $id id-шник товара
     * @return array
     */
    public static function get_item($id)
    {
        global $kernel;
        return $kernel->db_get_record_simple('_catalog_'.$kernel->pub_module_id_get().'_items', '`id` ="'.intval($id).'"');
    }


    /**
     *  Перенос товара в другую тов. группу. Сохраняются только общие св-ва
     * @param $itemid
     * @param $groupid
     * @return bool
     */
    public static function move_item2group($itemid,$groupid)
    {
        global $kernel;
        $to_group=self::get_group($groupid);
        if(!$to_group)
            return false;
        $item = self::get_item($itemid);
        if(!$item)
            return false;
        if($item['group_id']==$groupid)
            return false;
        $from_group = self::get_group($item['group_id']);
        if (!$from_group)
            return false;
        $moduleid = $kernel->pub_module_id_get();
        $from_group_table = '_catalog_items_'.$moduleid.'_'.strtolower($from_group['name_db']);
        $to_group_table = '_catalog_items_'.$moduleid.'_'.strtolower($to_group['name_db']);
        $new_ext_id=$kernel->db_add_record($to_group_table,array("id"=>null));
        if(!$new_ext_id)
            return false;
        $kernel->runSQL("DELETE FROM ".PREFIX.$from_group_table."  WHERE id=".$item['ext_id']);
        $kernel->db_update_record("_catalog_".$moduleid."_items",array('group_id'=>$groupid,'ext_id'=>$new_ext_id),"id=".$item['id']);

        //удаление картинок и файлов из свойств старой группы
        $old_group_props = self::get_props($from_group['id'],false);
        foreach($old_group_props as $old_group_prop)
        {
            self::process_item_prop_delete($old_group_prop,$item);
        }

        return true;
    }

    /**
     * Возвращает товар по общему свойству
     *
     * @param string $propname
     * @param string $propval
     * @return mixed
     */
    public static function get_item_by_prop($propname, $propval)
    {
        global $kernel;
        $res = false;
        $query = 'SELECT * FROM `'.$kernel->pub_prefix_get().'_catalog_'.$kernel->pub_module_id_get().'_items` '.
            'WHERE `'.$propname.'` ="'.mysql_real_escape_string($propval).'" LIMIT 1';
        $result = $kernel->runSQL($query);
        if ($row = mysql_fetch_assoc($result))
            $res = $row;
        mysql_free_result($result);
        return $res;
    }

    /**
     * Возвращает товары из БД, не принадлежащие ни к одной категории
     *
     * @param integer $offset        смещение
     * @param integer $limit         лимит
     * @return array
     */
    public static function get_items_without_cat($offset = 0, $limit = 100)
    {
        global $kernel;
        $items = array();
        $query = 'SELECT items.* FROM `'.$kernel->pub_prefix_get().'_catalog_'.$kernel->pub_module_id_get().'_items` AS items '.
            'LEFT JOIN `'.$kernel->pub_prefix_get().'_catalog_'.$kernel->pub_module_id_get().'_item2cat` AS i2c ON i2c.item_id=items.id '.
            'WHERE i2c.item_id IS NULL';
        $sort_field = self::get_common_sort_prop();
        if ($sort_field)
        {
            $query .= ' ORDER BY ISNULL(items.`'.$sort_field['name_db'].'`),  items.`'.$sort_field['name_db'].'` ';
            if ($sort_field['sorted'] == 2)
                $query .= " DESC ";
        }
        if ($limit != 0)
            $query .= ' LIMIT '.$offset.','.$limit;
        $result = $kernel->runSQL($query);
        while ($row = mysql_fetch_assoc($result))
            $items[] = $row;
        mysql_free_result($result);
        return $items;
    }

    /**
     * Возвращает общее свойство, по которому вести сортировку
     * @return array
     */
    public static function get_common_sort_prop()
    {
        global $kernel;
        return $kernel->db_get_record_simple('_catalog_item_props', '`group_id`=0 AND `sorted`>0 AND module_id="'.$kernel->pub_module_id_get().'"', '`name_db`,`sorted`');
    }

    /**
     * Возвращает кол-во товаров из БД, не принадлежащие ни к одной категории
     *
     * @return integer
     */
    public static function get_items_without_cat_count()
    {
        global $kernel;
        $query = 'SELECT count(items.id) as count FROM `'.$kernel->pub_prefix_get().'_catalog_'.$kernel->pub_module_id_get().'_items` AS items '.
            'LEFT JOIN `'.$kernel->pub_prefix_get().'_catalog_'.$kernel->pub_module_id_get().'_item2cat` AS i2c ON i2c.item_id=items.id '.
            'WHERE i2c.item_id IS NULL';
        $result = $kernel->runSQL($query);
        $total = 0;
        if ($row = mysql_fetch_assoc($result))
            $total = $row['count'];
        mysql_free_result($result);
        return $total;
    }


    /**
     * Возвращает кол-во товаров, если $group_id>0, то входящих в указанную тов. группу
     *
     * @param integer  $group_id      id-шник товарной группы
     * @param boolean $only_visible  только видимые?
     * @return array
     */
    public static function get_items_count($group_id = 0, $only_visible = false)
    {
        global $kernel;
        $where = array();
        $query = 'SELECT COUNT(*) AS count FROM `'.$kernel->pub_prefix_get().'_catalog_'.$kernel->pub_module_id_get().'_items` AS items';
        if ($only_visible)
            $where[] = 'items.`available`=1';
        if ($group_id > 0)
            $where[] = 'items.`group_id`='.$group_id;
        if (count($where) > 0)
            $query .= ' WHERE '.implode(' AND ', $where);
        $count = 0;
        $result = $kernel->runSQL($query);
        if ($row = mysql_fetch_assoc($result))
            $count = $row['count'];
        mysql_free_result($result);
        return $count;
    }


    /**
     * Возвращает категорию по id-шнику
     *
     * @param integer $id  id-шник категории
     * @return array
     */
    public static function get_category($id)
    {
        global $kernel;
        return $kernel->db_get_record_simple("_catalog_".$kernel->pub_module_id_get()."_cats", "`id`=".$id);
    }

    /**
     * Возвращает только custom-поля товара (из таблицы тов. группы) по id-шнику
     *
     * @param integer $id  ext-id-шник товара
     * @param string  $group_name БД-название товарной группы
     * @return array
     */
    public static function get_item_group_fields($id, $group_name)
    {
        global $kernel;
        return $kernel->db_get_record_simple('_catalog_items_'.$kernel->pub_module_id_get().'_'.strtolower($group_name), '`id`='.$id);
    }


    /**
     * Возвращает запись товара по id-шнику common-свойства + custom
     *
     * @param integer $id  id-шник товара
     * @return array
     */
    public static function get_item_full_data($id = 0)
    {
        $id = intval($id);
        //Сначала получаем общие свойства
        $item1 = self::get_item($id);
        if (!$item1)
            return false;

        $group = self::get_group($item1['group_id']);
        $commonid = $item1['id'];
        unset($item1['id']);
        $item1['commonid'] = $commonid;

        //теперь добавим custom-поля из тов. группы
        $itemc = self::get_item_group_fields($item1['ext_id'], $group['name_db']);
        if ($itemc)
            $item1 = $item1 + $itemc;
        return $item1;
    }

    public static function process_item_prop_delete($prop,$item)
    {
        global $kernel;
        if (!in_array($prop['type'], array('file', 'pict')) || !$item[$prop['name_db']])
            return;
        $modid = $kernel->pub_module_id_get();
        $kernel->pub_file_delete($item[$prop['name_db']]);
        if ($prop['type'] == 'pict')
        {
            //надо также удалить source и tn изображения
            $kernel->pub_file_delete(str_replace($modid.'/', $modid.'/tn/', $item[$prop['name_db']]));
            $kernel->pub_file_delete(str_replace($modid.'/', $modid.'/source/', $item[$prop['name_db']]));
        }
    }

    /**
     * Удаляет товар из БД
     *
     * @param $id integer id-шник товара
     * @return boolean
     */
    public static function delete_item($id)
    {
        global $kernel;
        $item = self::get_item_full_data($id);
        if (!$item)
            return false;
        $group = self::get_group($item['group_id']);
        if (!$group)
            return false;

        $modid = $kernel->pub_module_id_get();

        //удаление картинок и файлов
        $props = self::get_props($item['group_id'], true);
        foreach ($props as $prop)
        {
           self::process_item_prop_delete($prop,$item);
        }
        //из общей таблицы товаров
        $query = 'DELETE FROM `'.$kernel->pub_prefix_get().'_catalog_'.$modid.'_items` WHERE `id`='.$id;
        $kernel->runSQL($query);
        //из таблицы связанных товаров
        $query = 'DELETE FROM `'.$kernel->pub_prefix_get().'_catalog_'.$modid.'_items_links` WHERE `itemid1`='.$id.' OR `itemid2`='.$id;
        $kernel->runSQL($query);
        //из таблицы принадлежности к категориям
        $query = 'DELETE FROM `'.$kernel->pub_prefix_get().'_catalog_'.$modid.'_item2cat` WHERE `item_id`='.$id;
        $kernel->runSQL($query);
        //из таблицы тов. группы
        $query = 'DELETE FROM `'.$kernel->pub_prefix_get().'_catalog_items_'.$modid.'_'.strtolower($group['name_db']).'` WHERE `id`='.$item['ext_id'];
        $kernel->runSQL($query);
        return true;
    }

    /**
     *  Удаляет категорию из БД
     *
     * @param $cat array удаляемая категория
     * @return void
     */
    public static function delete_category($cat)
    {
        global $kernel;
        $query = 'DELETE FROM `'.$kernel->pub_prefix_get().'_catalog_'.$kernel->pub_module_id_get().'_cats` WHERE `id`='.$cat['id'];
        $kernel->runSQL($query);
        $query = 'DELETE FROM `'.$kernel->pub_prefix_get().'_catalog_'.$kernel->pub_module_id_get().'_item2cat` WHERE `cat_id`='.$cat['id'];
        $kernel->runSQL($query);
        //переносим child'ы удаляемой категории на уровень выше
        $query = 'UPDATE `'.$kernel->pub_prefix_get().'_catalog_'.$kernel->pub_module_id_get().'_cats` SET `parent_id`='.$cat['parent_id'].' WHERE `parent_id`='.$cat['id'];
        $kernel->runSQL($query);
        //удаление картинок и файлов
        $props = self::get_cats_props();
        foreach ($props as $prop)
        {
            self::process_item_prop_delete($prop,$cat);
        }
    }


    /**
     * Возвращает свойство по id-шнику
     *
     * @param integer $id id-шник свойства
     * @return array
     */
    public static function get_prop($id)
    {
        global $kernel;
        $res = $kernel->db_get_record_simple('_catalog_item_props', 'id='.$id);
        //Если свойство с типом=картинка, то сразу вытащим из дополнительных параметров информацию по картинке
        if ($res['type'] == 'pict')
        {
            if (isset($res['add_param']) && !empty($res['add_param']))
                $res['add_param'] = @unserialize($res['add_param']);
            else
                $res['add_param'] = BaseModule::make_default_pict_prop_addparam();
        }
        return $res;
    }

    public static function clean_old_baskets($moduleid)
    {
        global $kernel;
        $prefix = $kernel->pub_prefix_get();
        $kernel->runSQL("DELETE FROM `".$prefix."_catalog_".$moduleid."_basket_orders` WHERE `lastaccess`<'".date("Y-m-d H:i:s",strtotime("-21 days"))."'");
        $q="DELETE items FROM `".$prefix."_catalog_".$moduleid."_basket_items` AS items
            LEFT JOIN `".$prefix."_catalog_".$moduleid."_basket_orders` AS `orders` ON orders.id = items.orderid
            WHERE orders.id IS NULL";
        $kernel->runSQL($q);
    }


    public static function get_child_cats_with_count($moduleid,$parentid,$select_fields="cats.*",$subcats_count=false)
    {
        global $kernel;
        $prfx=$kernel->pub_prefix_get();
        if ($subcats_count)
            $select_fields.=', IFNULL(subcats._count,0) AS _subcats_count';
        $sql = 'SELECT '.$select_fields.', IFNULL(i2c._count,0) AS _items_count FROM `'.$prfx.'_catalog_'.$moduleid.'_cats` AS cats
                LEFT JOIN (SELECT COUNT(item_id)  AS _count, cat_id FROM `'.$prfx.'_catalog_'.$moduleid.'_item2cat` GROUP BY cat_id) AS i2c ON cats.id = i2c.cat_id';
        if ($subcats_count)
            $sql.=' LEFT JOIN (SELECT COUNT(id)  AS _count, parent_id FROM `'.$prfx.'_catalog_'.$moduleid.'_cats`  GROUP BY parent_id) AS subcats ON subcats.parent_id=cats.id';
        $sql.=  ' WHERE cats.`parent_id` = '.$parentid.'
                GROUP BY cats.id
                ORDER BY cats.`order`';
        return $kernel->db_get_list($sql);
    }

    /**
     * Возвращает товарную группу
     *
     * @param integer $id  id-шник группы
     * @return array
     */
    public static function get_group($id)
    {
        global $kernel;
        return $kernel->db_get_record_simple("_catalog_item_groups", "`id`=".$id);
    }

    /**
     * Генерирует случайную строку из латинских букв + цифр
     *
     * @param number $len длина
     * @param boolean $plusNumbers использовать и цифры?
     * @return string
     */
    public static function generate_random_string($len, $plusNumbers=true)
    {
        $arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        $numbers = array('0','1','2','3','4','5','6','7','8','9');
        if ($plusNumbers)
            $arr = array_merge($arr,$numbers);
        $str="";
        for ($i=0;$i<$len;$i++)
             $str=$str.$arr[array_rand($arr)];
        return $str;
    }

    /**
     * Возвращает все внутренние фильтры в виде key=>value массива,
     * где key - строковый ID-шник фильтра,
     * value - полное название фильтра
     *
     * @return array
     */
    public static function get_inner_filters_kvarray()
    {
        $filters = self::get_inner_filters();
        $result = array(""=>"");
        foreach ($filters as $filter)
        {
        	$result[$filter['stringid']] = htmlspecialchars($filter['name']);
        }
        return $result;
    }


    public static function set_items_available($itemids,$available)
    {
        if (!$itemids)
            return;
        global $kernel;
        $kernel->db_update_record("_catalog_".$kernel->pub_module_id_get()."_items",array('available'=>$available),"id IN (".implode(",",$itemids).")");
    }

    /**
     * Возвращает внутренний фильтр по ID
     *
     * @param number $id
     * @return array
     */
    public static function get_inner_filter($id)
    {
        global $kernel;
        $res    = false;
        $query  = 'SELECT * FROM `'.PREFIX.'_catalog_'.$kernel->pub_module_id_get().'_inner_filters` WHERE `id` ='.$id.' LIMIT 1';
        $result = $kernel->runSQL($query);
        if ($row = mysql_fetch_assoc($result))
            $res = $row;
        mysql_free_result($result);
        return $res;
    }


    /**
     * Возвращает внутренний фильтр по строковому ID
     *
     * @param string $stringid
     * @return array
     */
    public static function get_inner_filter_by_stringid($stringid)
    {
        global $kernel;
        return $kernel->db_get_record_simple('_catalog_'.$kernel->pub_module_id_get().'_inner_filters',' `stringid` ="'.mysql_real_escape_string($stringid).'"');
    }

    /**
     * Возвращает все внутренние фильтры
     *
     * @return array
     */
    public static function get_inner_filters()
    {
        global $kernel;
        return $kernel->db_get_list_simple('_catalog_'.$kernel->pub_module_id_get().'_inner_filters',"true");
    }


    /**
     * Возвращает все переменные модуля
     *
     * @return array
     */
    public static function get_variables()
    {
        global $kernel;
        $items = array();
        $query = 'SELECT * FROM `'.PREFIX.'_catalog_'.$kernel->pub_module_id_get().'_variables`';
        $result = $kernel->runSQL($query);
        while ($row = mysql_fetch_assoc($result))
            $items[$row['name_db']] = $row;
        mysql_free_result($result);
        return $items;
    }

    /**
     * Возвращает переменную модуля по идентификатору
     * @param string $name_db
     * @return array
     */
    public static function get_variable($name_db)
    {
        global $kernel;
        return $kernel->db_get_record_simple('_catalog_'.$kernel->pub_module_id_get().'_variables'," `name_db`='".$name_db."'");
    }

    /**
     * Возвращает все поля заказа (корзины)
     *
     * @return array
     */
    public static function get_order_fields()
    {
        global $kernel;
        return $kernel->db_get_list_simple('_catalog_'.$kernel->pub_module_id_get().'_basket_order_fields'," true ORDER BY `order`");
    }

    /**
     * Возвращает все поля заказа (корзины) в виде
     * key=>value массива, где key - DB-имя поля
     *
     * @return array
     */
    public static function get_order_fields2()
    {
        global $kernel;
        $items = array();
        $query = 'SELECT * FROM `'.PREFIX.'_catalog_'.$kernel->pub_module_id_get().'_basket_order_fields` '.
        		 'ORDER BY `order`';
        $result = $kernel->runSQL($query);
        while ($row = mysql_fetch_assoc($result))
            $items[$row['name_db']] = $row;
        mysql_free_result($result);
        return $items;
    }


    /**
     * Возвращает поле заказа (корзины)
     *
     * @param integer $id id-шник поля
     * @return array
     */
    public static function get_order_field($id)
    {
        global $kernel;
        return $kernel->db_get_record_simple('_catalog_'.$kernel->pub_module_id_get().'_basket_order_fields',"id=".$id);
    }



    /**
     * Проверяет, существует ли поле с указанным БД-именем
     * ($dbname) таблице заказов
     *
     * @param string $dbname имя свойства
     * @return boolean
     */
    public static function is_order_field_exists($dbname)
    {
        $reserved = array('id', 'sessionid', 'lastaccess', 'isprocessed');
        if (in_array($dbname, $reserved))
            return true;
        global $kernel;
        return $kernel->db_get_record_simple('_catalog_'.$kernel->pub_module_id_get().'_basket_order_fields','`name_db` ="'.$dbname.'"');
    }

    /**
     * Возвращает свойства для категорий
     *
     * @return array
     */
    public static function get_cats_props()
    {
        global $kernel;
        $moduleid = $kernel->pub_module_id_get();
        if ($moduleid=='catalog')
            return array();
        return $kernel->db_get_list_simple('_catalog_'.$moduleid.'_cats_props', 'true');
    }

    //catalog_menu_cat_props
    /**
     * Возвращает список свойств категорий в хтмл
     *
     * @return string
     */
    public static function get_cats_props_html()
    {
        $cprops = self::get_cats_props();
        $str = "<b>[#catalog_menu_cat_props#]</b><ul>";
        foreach ($cprops as $cprop)
        {
            $str .= "<li>&nbsp;&nbsp;".htmlspecialchars($cprop['name_full'])." (".$cprop['name_db'].")</li>";
        }
        $str .= "</ul>";
        return $str;
    }

    /**
     * Возвращает html вида
     *   Общие свойства
     *   	Артикул (articul)
     *   	Наименование (name)
     *   	Класс продукции (class)
     *   Группа 1
     *   	Модель (model)
     *   	Производитель (manufacturer)
     *   Группа 2
     *   	Бренд (brand)
     *   	Год выпуска (year)
     * для вывода в админке
     * @param $needid boolean
     * @return string
     */
    public static function get_all_group_props_html($needid = false)
    {
        $groups = self::get_all_group_props_array();
        $str = '<ul>';
        foreach ($groups as $gname=>$gprops)
        {
            $str .= "<li><b>".$gname."</b>";
            $str .= "<ul>";
            if ($needid && $gprops['id']==0)
                $str .= "<li>&nbsp;&nbsp;id (id)</li>";
            foreach ($gprops['props'] as $propname=>$propvars)
            {
                $str .= "<li>&nbsp;&nbsp;".htmlspecialchars($propvars['name_full'])." (".$propname.")</li>";
            }
            $str .= "</ul>";
            $str .= "</li>";
        }
        $str .= "</ul>";
        return $str;
    }



    /**
     * Возвращает массив всех тов. групп с их свойствами (включая общие)
     * вид массива
     *   [Общие свойства]=>
     * 	    array(
     *      [id] =>0,
     * 	    [props]=>array(...)
     * 		)
     *   [Группа 1]=>
     *      array(
     *      [id] =>0,
     * 	    [props]=>array(...)
     *      )
     *   [Группа 2]=>
     *      array(
     *      [id] =>0,
     * 	    [props]=>array(...)
     *      )
     * @return array
     */
    public static function get_all_group_props_array()
    {
        $ret = array();

        //сначала общие свойства
        $ret["[#catalog_common_props#]"]=array("id"=>0, "props"=>self::get_props2(0), "name_full"=>"[#catalog_common_props#]");

        //теперь свойства для всех товарных групп
        $groups = self::get_groups();
        foreach ($groups as $grop_values)
        {
            $ret[$grop_values['name_full']] =  array("id"=>$grop_values['id'], "name_full"=>$grop_values["name_full"], "props"=>self::get_props2($grop_values['id']));
        }
        return $ret;
    }

  /**
     * Возвращает свойства для группы
     *
     * @param integer $gid          id-шник группы
     * @param boolean $need_common  нужны ли общие для всех товаров свойства?
     * @return array
     */
    public static function get_props($gid, $need_common = false)
    {
        global $kernel;
        $cond = '`module_id` = "'.$kernel->pub_module_id_get().'" AND (`group_id`='.$gid;
        if ($need_common)
            $cond .= ' OR `group_id`=0';
        $cond .= ') ORDER BY `order`,`name_full`';   //чтобы сначала шли common-свойства
        return $kernel->db_get_list_simple("_catalog_item_props",$cond);
    }

    /**
     * Возвращает свойства для группы в виде массива с элементами namedb=>array(...свойства...)
     *
     * @param integer $gid id-шник группы
     * @return array
     */
    public static function get_props2($gid)
    {
        global $kernel;
        $items = array();
        $query = 'SELECT * FROM `'.PREFIX.'_catalog_item_props` '.
        		 'WHERE `module_id` = "'.$kernel->pub_module_id_get().'" '.
        		 'AND (`group_id`='.$gid.') ORDER BY `order`, `name_full`';
        $result = $kernel->runSQL($query);
        while ($row = mysql_fetch_assoc($result))
            $items[$row['name_db']] = $row;
        mysql_free_result($result);
        return $items;
    }

    /**
     * Возвращает все категории из БД
     *
     * @param integer $moduleid id-шник модуля
     * @return array
     */
    public static function get_all_categories($moduleid)
    {
    	global $kernel;
        $dbcats = $kernel->db_get_list_simple('_catalog_'.$moduleid.'_cats', "true","id, name,parent_id,_hide_from_waysite");
        $cats = array();
        foreach ($dbcats as $cat)
        {
            $cats[$cat['id']]=$cat;
        }
        return $cats;
    }

	/**
     * Возвращает все товарные группы для текущего модуля из БД
     * @param string $moduleid
     * @param boolean $with_items_count результат с кол-вом товаров в каждой группе?
     * @return array
     */
    public static function get_groups($moduleid=null,$with_items_count=false)
    {
        global $kernel;
        if (!$moduleid)
            $moduleid=$kernel->pub_module_id_get();
        if ($with_items_count)
        $query = 'SELECT groups.*, COUNT(items.id) AS _items_count FROM `'.PREFIX.'_catalog_item_groups` AS groups
            LEFT JOIN `'.PREFIX.'_catalog_'.$moduleid.'_items` AS items ON items.group_id=groups.id
            WHERE groups.`module_id` = "'.$moduleid.'"
            GROUP BY groups.id
            ORDER BY groups.id';
        else
            $query = 'SELECT * FROM `'.PREFIX.'_catalog_item_groups` WHERE `module_id` = "'.$moduleid.'"  ORDER BY `id`';
        $ret=array();
        $groups = $kernel->db_get_list($query);
        foreach($groups as $g)
        {
            $ret[$g['id']]=$g;
        }
        return $ret;
    }


    /**
     * Проверяет, изменился ли md5 для файла
     *
     * @param string $filename полный путь к файлу
     * @param string $md5 MD5 предыдущей версии файла
     * @return boolean
     */
    public static function isTemplateChanged($filename, $md5)
    {
        if (empty($md5) || !file_exists($filename))
            return false;
        if ($md5 == md5_file($filename))
            return false;
        return true;
    }

    /**
     * Пересоздаёт шаблон для отображения common-свойств товара во frontend
     *
     * @return boolean
     */
    /*
    public static function regenerate_frontend_item_common_block($id_module, $group = array(), $force=false)
    {
        global $kernel;

        $fname = self::get_templates_user_prefix().$id_module.'_'.$group['name_db'].'_card.html';

        $msettings = $kernel->pub_module_serial_get($id_module);
        if (!isset($msettings['frontend_items_list_tpl_md5']))
            $msettings['frontend_items_list_tpl_md5']='';

        //Пока без проверок
        //if ($force || !self::isTemplateChanged($fname, $msettings['frontend_items_list_tpl_md5']))
        //{
            $html = '';
            $template = $kernel->pub_template_parse(self::get_templates_admin_prefix().'frontend_templates/blank_item_one.html');
            //$fh    = fopen($fname, "w");
            //if (!$fh)
            //    return false;

            $props = self::get_common_props($id_module, false);
            $lines = '';
            foreach ($props as $prop)
            {
                $line = $template['prop_'.$prop['type']];
                $line = str_replace('%prop_name_full%', $prop['name_full'], $line);
                $line = str_replace('%prop_value%', '%'.$prop['name_db'].'_value%', $line);
                $lines .= $line;
            }

            //$content = str_replace('%props%', $lines, $content);
            $html .= "<!-- @list_item_data -->";
            $html .= $lines;


            //блок для вывода навигации по страницам
            $kernel->pub_file_save($fname);

            //$msettings['frontend_items_list_tpl_md5'] = md5_file($fname);

            //$kernel->pub_module_serial_set($msettings);

            return true;
        //}
        //else
        //    return false;
    }
    */

    /**
     * Возвращает common-свойства (общие для всех товаров)
     *
     * @param $id_module string модуль
     * @param $only_listed boolean возвращать только свойства, которые выводим в списке товаров
     * @return array
     */
    public static function get_common_props($id_module, $only_listed=false)
    {
        global $kernel;
        $items = array();
        $query = 'SELECT * FROM `'.PREFIX.'_catalog_item_props` '.
        ' WHERE `module_id` = "'.$id_module.'" AND `group_id`=0';
        $result = $kernel->runSQL($query);
        while ($row = mysql_fetch_assoc($result))
        {
            if (!$only_listed)
                $items[] = $row;
            elseif ($row['showinlist'] == 1)
                $items[] = $row;
        }

        mysql_free_result($result);
        return $items;
    }

    /**
     * Возвращет префикс путей к шаблонам пользовательского интерфейса
     *
     * @return string
     */
    public static function get_templates_user_prefix()
    {
        return self::$templates_user_prefix;
    }

    /**
     * Возвращет префикс путей к шаблонам административного интерфейса
     *
     * @return string
     */
    public static function get_templates_admin_prefix()
    {
        return self::$templates_admin_prefix;
    }


    public static function is_valid_itemid($id)
    {
        if (!is_numeric($id))
            return false;
        $id=intval($id);
        if ($id<1)
            return false;
        return $id;
    }
}