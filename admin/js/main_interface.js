var curent_action_confirm = "";


//Открывает окно для редактирования или создания нового действия модуля
function show_action_edit(strlink, name)
{
    $("#popup_div").load(start_interface.global_link + strlink);
    $("#popup_div").dialog({
        resizable: true,
        height:400,
        width:600,
        zIndex:1000,
        title:name,
        buttons:{},
        modal: true,
        show: "scale",
        hide: "scale",
        close: function() {update_action_list(); }

    });
}

function santaUpdateRegion(regid, loadFrom)
{
    $('#popup_div').css('display','none');
    $("#"+regid).html('<span id="contentLoading">Loading, Please wait..</span>'); // @todo use lang[]
    $("#"+regid).load(loadFrom, function(response, status, xhr) {
          if (status == "error")
          {
            $("#"+regid).html("Load error: " + xhr.status + " " + xhr.statusText);
          }
    });
}

/**
 * Эмитирует клик по левому пункту меню (с подтверждением).
 *
 * Аналогично jspub_click, но перед тем как осуществить переход выводится сообщение
 * и переход осуществиться только в том случае, если пользователь подтвердит это
 * сообщение (тоесть ответит "Yes")
 * @dialog_action URL, на который осуществляем переход
 * @dialog_message Сообщение, которое нужно подтвердить, прежде чем будет осуществлён переход
 */

function jspub_confirm(dialog_action, dialog_message)
{
	//curent_action_confirm = dialog_action;
    //$( "#ext_layout:ui-dialog" ).dialog( "destroy" );
    $("#popup_msg_div").html('<p>'+dialog_message+'</p>');
    $("#popup_msg_div").dialog({
        resizable: false,
        height:180,
        modal: true,
        buttons: {
            "Yes": function() {
                start_interface.link_go(dialog_action);
                $(this).dialog( "close" );
            },
            "No": function() {
                $(this).dialog( "close" );
            }
        }
    });
	return false;
}

/**
 * Сохраняем значение в куках пользователя под определённым именем
 *
 */
function jspub_cookie_set(cookieName, cookieValue, expires, path, domain, secure)
{
	document.cookie =
		encodeURIComponent(cookieName) + '=' + encodeURIComponent(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
}

/**
 * Возвращает из Кук значение по его имени
 *
 */

function jspub_cookie_get(cookieName)
{
	var cookieValue = '';
	var posName = document.cookie.indexOf(encodeURIComponent(cookieName) + '=');

	if (posName != -1)
	{
		var posValue = posName + (encodeURIComponent(cookieName) + '=').length;
		var endPos = document.cookie.indexOf(';', posValue);
		if (endPos != -1)
			cookieValue = decodeURIComponent(document.cookie.substring(posValue, endPos));
		else
			cookieValue = decodeURIComponent(document.cookie.substring(posValue));
	}
	return (cookieValue);
}



/**
 * Эмитирует клик по левому пункту меню.
 *
 * Функция используется для отправки любых GET (и только их) запросов модулю
 * в качестве параметра используется URL для перехода, который должен начинаться с
 * идентификатора пугкта левого меню.
 * @param lnk URL, на который осуществляем переход
 */

function jspub_click(lnk)
{
	start_interface.link_go(lnk);
	return false;
}

/**
 * Включает или выключает элементы HTML элементы административного интерфейса.
 *
 * Функция производит выключение (или включение) заданных элметов в зависимости от того
 * отмечена или не отмечена галочка, переданная в качестве переданного параметра.
 * @elem1ID Передаётся идентификатор чекбокса
 * @elem2ID Идентификатор объекта, который нужно включить или выключить.
 */
function jspub_disabled_change(elem1ID,elem2ID)
{
   if($('#'+elem2ID).is('select'))
   {
      if ($('#'+elem1ID).attr('checked'))
         $('#'+elem2ID).selectmenu('disable');
      else
         $('#'+elem2ID).selectmenu('enable');
   }
   else
   {
      if ($('#'+elem1ID).attr("checked"))
         $('#'+elem2ID).attr("disabled",true);
      else
         $('#'+elem2ID).removeAttr("disabled");
   }
}

/**
 * Производит отправку формы
 *
 * @param formID ID HTML объекта FORM, которую отправляем
 * @param url URL, на который осуществляем отсылку
 */

function jspub_form_submit(formID, url)
{
    //different variants: http://stackoverflow.com/questions/169506/obtain-form-input-fields-using-jquery
	var parameters = new Object();
    $('#'+formID+" :input").each(function(){

        if ($(this).attr('type')=="checkbox")
        {
            if ($(this).attr('checked'))
                parameters[this.name]=1;
            else
                parameters[this.name]=0;
        }
        else
            parameters[this.name] = $(this).val();
    });

    //Теперь непосредственно отсылка формы
    $.post(url, parameters,  function(data){
        try
        {
            //Обрабатываем ответ
            var post_res=jQuery.parseJSON(data);
            //сначала проверка на наличие ошибок
            if (parseInt(post_res.errore_count) > 0)
            {
                santaShowPopupHint("Error", post_res.errore_text,0);
            }
            else
            {
                //Значит ошибок небыло и нужно вывести сообщение с результатом...
                var msg_label = post_res.result_label;
                var msg = post_res.result_message;
                if (msg != "")
                    santaShowPopupHint(msg_label, msg,3000);
                //...и возможно перейти на другой пункт меню
                var id_link =  post_res.redirect;
                if (id_link != "")
                {
                    jspub_click(id_link);
                }
            }
        }
        catch (e)
        {
            santaShowPopupHint('Error', 'Form not submitted:'+e,3000);
        }


    });

    return false;
}

//отображает всплывающее сообщение
function santaShowPopupHint(header, text,timeout)
{
    $("#popup_msg_div").html('<p>'+text+'</p>');
    //$("#popup_msg_div").dialog( "destroy" );
    $("#popup_msg_div").dialog({
        resizable: false,
        height:180,
        width:300,
        modal: true,
        title: header,
        buttons: {
            "OK": function() {
                $(this).dialog( "close" );
            }
        },
        show: "scale",
	    hide: "scale"//"explode"
    });
    //autoclose
    if (timeout!=0)
        setTimeout(function(){ $("#popup_msg_div").dialog("close"); }, timeout);
}



/* Функции, используемые только в административном интерфейсе*/



//Функция открывает слой для выбора страницы сайта
//во всех свойствах с типом (страница сайта)
//function get_properes_page(EventElement, Object)
function get_properes_page(e, fieldID)
{
    $('#popup_sitemap_div').html('');
    $("#popup_sitemap_div").load("index.php?action=select_page", function(response, status, xhr) {
          if (status == "error")
          {
              $("#popup_sitemap_div").html("Load error: " + xhr.status + " " + xhr.statusText);
          }
          else
          {
              //$('#popup_sitemap_div').css('position','absolute').css('top',e.clientY).css('left',e.clientX);
              //$('#popup_sitemap_div').css('display','block');
          }
    });

    $("#popup_sitemap_div").dialog({
        resizable: true,
        height:400,
        width:250,
        zIndex:2000,
        title: 'Выбор страницы',
        buttons: {},
        modal: true
        //,close: function() {update_action_list(); }

    });
    start_interface.select_element = fieldID;
	//start_interface.dialog.addKeyListener(27, start_interface.dialog.hide, start_interface.dialog);
    return true;
}



//Открывает новое окно для загрузки туда редактора контента
//Используется в свойствах страницы, для вызова редактора контента для конкретной
function go_edit_content(name_file, no_redactor)
{
		var name_edit = 'edit_';
		var left=parseInt(100);
		var top=parseInt(1);
		var width = parseInt(screen.width / 3 );
		var height = parseInt(screen.height / 2);
		if (width < 700)
			width = 700;
        var newWin;
		if (no_redactor)
			newWin=window.open('/admin/index.php?action=edit_content&file='+name_file+'&edit='+name_edit+'&no_redactor=1', '_blank', 'alwaysRaised=yes,dependent=yes,resizable=yes,titlebar=no,toolbar=no,menubar=no,location=no,status=no,scrollbars=no,left=' + left + ',top=' + top + ',width=' + width +',height='+height,'Content');
		else
			newWin=window.open('/admin/index.php?action=edit_content&file='+name_file+'&edit='+name_edit, '_blank', 'alwaysRaised=yes,dependent=yes,resizable=yes,titlebar=no,toolbar=no,menubar=no,location=no,status=no,scrollbars=no,left=' + left + ',top=' + top + ',width=' + width +',height='+height,'Content');
		newWin.focus();
}

//Скрывает или показывает иконку возможности редактирования
//контента у определённой метки
function show_icon_go_edit_content(selectID, id_image)
{
    if (!$('#'+selectID).attr("disabled") && $('#'+selectID).val()==1)
        $('#'+id_image).css('display','inline');
    else
        $('#'+id_image).css('display','none');
}

//Обновляет список выбранных текущих действий при редактировании настроек модуля
function update_action_list()
{
    $("#actions_list_table").load(start_interface.global_link + 'action_update_list', function(response, status, xhr) {
          $('#module_actions_container').css("display","block");
          $('#page_tabs').find('a[href=#mod_action_list]').parent().show();
          if (status == "error")
          {
            $("#actions_list_table").html("Load error: " + xhr.status + " " + xhr.statusText);
          }
    });
}




//Функция используется только интерфейсом подсистемы статистики, в
//дальнейшем нужно перейти на стандартную
function admin_form_submit(url, id_insert, formID)
{
    var postArr = $('#'+formID).serializeArray();
    $.post(url, postArr,  function(data){
       $("#"+id_insert).html(data);
    });
}

//Функция выполняет подсвтеку строки с действием в настройках модуля, пока не уневерсальна
//Из ней нужно сделать функцию, которой смогли бы пользоваться все модули.
//used in admin_modules.html only
function mouse_select_element(obj, del_class)
{
	if (del_class)
		$(obj).removeClass('table_action_select');
	else
		$(obj).addClass('table_action_select');
}


//Обновляет левое меню, в случаях когда необходимо перестроить какой-то блок, или обновить какие-то данные
function update_left_menu(url)
{
    santaUpdateRegion('west', "index.php?action=get_left_menu&"+url);
}



function form_submit_include_content_auto()
{
    for (var i in CKEDITOR.instances)
    {
        CKEDITOR.instances[i].destroy();
    }
	return false;
}

//Вызвается в старых модулях, для сабмита формы с редактором контента
function form_submit_include_content(name_area)
{
	if (!name_area)
		name_area = 'content_html';
	//Закрываем редактор контента в этой области, после чего всё и сабмитится
    var inst = CKEDITOR.instances[name_area];
    if (typeof inst != 'undefined')
        inst.destroy();
	return true;
}

// Функция используется в шаблоне main.html активации редактора контента
function start_include_content(name_area)
{
	if (!name_area)
		name_area = 'content_html';
    var config =
           {
               skin : 'kama',
               autoUpdateElement:true,
               filebrowserBrowseUrl : '/components/html_editor/ckeditor/plugins/kcfinder/browse.php?type=files',
               filebrowserImageBrowseUrl : '/components/html_editor/ckeditor/plugins/kcfinder/browse.php?type=images',
               filebrowserFlashBrowseUrl : '/components/html_editor/ckeditor/plugins/kcfinder/browse.php?type=files',
               filebrowserUploadUrl : '/components/html_editor/ckeditor/plugins/kcfinder/upload.php?type=files',
               filebrowserImageUploadUrl : '/components/html_editor/ckeditor/plugins/kcfinder/upload.php?type=images',
               filebrowserFlashUploadUrl : '/components/html_editor/ckeditor/plugins/kcfinder/upload.php?type=files',
               LinkBrowserWindowHeight:440,
               ImageBrowserWindowHeight:440,
               FlashBrowserWindowHeight:440,
               LinkUpload:false,
               ImageUpload:false,
               FlashUpload:false,
               language:'ru',
               toolbar_Full: [
                ['Source','-','Preview'],
                ['Cut','Copy','Paste','PasteText','PasteFromWord'],
                ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                '/',
                ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
                ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
                ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                ['Link','Unlink','Anchor'],
                ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
                '/',
                ['Styles','Format','Font','FontSize'],
                ['TextColor','BGColor'],
                ['Maximize', 'ShowBlocks']
            ]
            //close_on_save:[#close_on_save#]
           };
    //убираем предыдущий instance, если есть
    var inst = CKEDITOR.instances[name_area];
    if (inst)
        inst.destroy(true);
    $('#'+name_area).ckeditor(config);
    return true;
}

//Добавляет к строкам функцию по их образанию
String.prototype.ellipse = function(maxLength){
    if(this.length > maxLength){
        return this.substr(0, maxLength-3) + '...';
    }
    return this;
};


/* Russian (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Andrew Stromnov (stromnov@gmail.com). */
jQuery(function($){
	$.datepicker.regional['ru'] = {
		closeText: 'Закрыть',
		prevText: '&#x3c;Пред',
		nextText: 'След&#x3e;',
		currentText: 'Сегодня',
		monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
		'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
		'Июл','Авг','Сен','Окт','Ноя','Дек'],
		dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
		dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
		dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
		weekHeader: 'Не',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ru']);
});

//Отдельная функция
function structure_tree_click_node(url)
{
    //Самое простое - заполним поля с основными данными страницами новыми значениями.
    //Сначала надо получить эти данные
    var url_link_main = start_interface.global_link + url + "&type=get_main_param";//index.php?action=set_left_menu&leftmenu=view&id=about2&type=get_main_param

    $('#content_header').before('<span id="contentLoading">Loading, Please wait..</span>');
    $('#structure_page_name').html('').parent().hide();
    $('#page_container').css({'display':'none'});
    $('#contentLoading').show();
    
    //Загрузка данных о самой странице и свойствах модулей
    $.get(url_link_main, function (data)
    {
        var comboStore = jQuery.parseJSON(data);
        if (comboStore != null)
            set_propertes_main(comboStore);
        
        $("#contentLoading").remove();
        $('#content_header').show();
        $('#page_tabs').parent().tabs({ selected: 0 });
        $('#page_container').css({'display':'block'});
    });
    
    run_update_metki(url);
}

function run_update_metki(url)
{
    var url_link_metk = start_interface.global_link + url + "&type=get_metka";
    //Второй, отдельный запрос, должен загрузить информацию о метках для выбранного шаблона
    $.get(url_link_metk, function (data)
    {
        var comboStore = jQuery.parseJSON(data);
        if (comboStore != null)
            set_metki(comboStore);
    });

}
//А теперь вложенная функция, чтобы иметь доступ ко всем переменным
//созданым при инсталяции
function set_propertes_main(d)
{
    // название страницы
    $('#structure_page_name').html('"<a href="/'+d.id_curent_page+'.html" class="link2page" target="_blank" title="Откроется в новом окне">'+d.caption+'</a>"');
    
    //Проставляем поля формы
    $("#fieldPageName").val(d.caption);
    $("#fieldPageTitle").val(d.name_title);
    $("#fieldPageURL").val(d.link_other_page);
    $("#fieldPageID").val(d.id_curent_page);
    $("#fieldPageOnlyAuth").val(d.only_auth);
    $('select#fieldPageTemplate').selectmenu("value",d.page_template);

    if (d.template_naslednoe)
    {
        $('#flag_template').attr("checked", "checked");
        $('#fieldPageTemplate').selectmenu('disable');
    }
    else
    {
        $('#flag_template').removeAttr('checked');
        $('#fieldPageTemplate').selectmenu('enable');
    }

    if (d.page_is_main)
        $('#flag_template').attr("disabled", true);
    else
        $('#flag_template').removeAttr("disabled");

    //Заполняем остальные вспомогательные элементы
    $('#main_label_name_page').html(d.caption);
    $('#link_for_preview').attr('href', d.link_for_preview);

    //проставляем свойства страницы для модулей
    for (var i = 0; i < d.page_prop.length; i++)
    {
        $('#' + d.page_prop[i].name).val(d.page_prop[i].value);
        if (d.page_prop[i].naslednoe)//чекбокс наследования если надо
        {
            $('#' + d.page_prop[i].name_nasled).attr("checked", "checked");
            if($('#' + d.page_prop[i].name).is('select'))
               $('#' + d.page_prop[i].name).selectmenu("disable");
            else
               $('#' + d.page_prop[i].name).attr("disabled", true);
        }
        else
        {
            $('#' + d.page_prop[i].name_nasled).removeAttr('checked');
            if($('#' + d.page_prop[i].name).is('select'))
               $('#' + d.page_prop[i].name).selectmenu("enable");
            else
               $('#' + d.page_prop[i].name).removeAttr("disabled");
        }

        if (d.page_is_main)//если главная - отключим чекбокс
            $('#' + d.page_prop[i].name_nasled).attr("disabled", true);
        else
            $('#'+d.page_prop[i].name_nasled).removeAttr("disabled");
    }
}
var metkiCount=0;
var arr_link_content = new Array();
function set_metki(d)
{
    $('#table_metki').html('');//очистим таблицу меток
    var str_code = "";

    metkiCount = d.length;
    arr_link_content = new Array();
    for (var i = 0; i < d.length; i++)
    {
        var str_content = "";
        str_content += '<fieldset>';
        //Имя метки со скрытым инпутом, куда пишется непосредственное значение
        //str_content += '<td class="name">';
        str_content += '<label for="flag_metka_' + i + '">' + d[i].name + '</label>';
        //Галочка наследования
        str_content += '<input type="checkbox" name="' + d[i].name + '" id="flag_metka_' + i + '" onclick="jspub_disabled_change(\'flag_metka_'+i+'\', \'sel_modul_ext_' +i+'\');show_icon_go_edit_content(arr_link_select[' + i + '],\'img_edit_' + i + '\');show_icon_go_edit_content(arr_link_select[' + i + '],\'img_edit_s_' + i + '\');">';
        //Селект, который на который навешивается экстовская форма
        str_content += '<select id="sel_modul_ext_' + i + '"></select>';
        str_content += '<span style="height: 26px; display: block;"><img class="edit_icon" title="Визуальный редактор контента" id="img_edit_' + i + '" src="/admin/templates/default/images/icon_edit.gif" onclick="go_edit_content(arr_link_content[' + i + '], false)"><img class="edit_icon"  id="img_edit_s_' + i + '" title="HTML редактор контента" src="/admin/templates/default/images/icon_edit_textarea.gif"  onclick="go_edit_content(arr_link_content[' + i + '], true)"></span>';
        str_content += '</fieldset>';
        //Добавляем небольшими кусками, так как иначе IE глючит
        $(str_content).appendTo('#table_metki');

        buildMetkaActionsSelect("select#sel_modul_ext_" + i);


        //при изменении селекта с действиями скроем или покажем иконки редактора контента
        $("select#sel_modul_ext_" + i).change(function ()
        {
            var elID = new String(this.id).split("_").pop();
            show_icon_go_edit_content("sel_modul_ext_" + elID, 'img_edit_' + elID);
            show_icon_go_edit_content("sel_modul_ext_" + elID, 'img_edit_s_' + elID);
        });

        $("#sel_modul_ext_" + i).val(d[i].id_action);//поставим в селект выбранное значение


        // если унаследовано - отключим селект и поставим чекбокс наследования
        if (d[i].naslednoe)
        {
            $("#flag_metka_" + i).attr("checked", "checked");
            $("#sel_modul_ext_" + i).attr("disabled", true);
        }

        //повесим обработчик на клик по чекбоксу
        $("#flag_metka_"+i).change(function () {
         var elID = new String(this.id).split("_").pop();
         var disAttr = $("#sel_modul_ext_"+elID).attr("disabled");
         
            if (typeof  disAttr!== 'undefined' && disAttr!=false){
               $("#sel_modul_ext_"+elID).removeAttr("disabled");
               // и покажем иконки редактирования, если требуется
               show_icon_go_edit_content("sel_modul_ext_"+elID,'img_edit_'+elID);
               show_icon_go_edit_content("sel_modul_ext_"+elID,'img_edit_s_'+elID);
            }
            else
            {
               $("#sel_modul_ext_"+elID).attr("disabled",true);
            }
         });


        // прячем иконки
        show_icon_go_edit_content("sel_modul_ext_" + i, 'img_edit_' + i);
        show_icon_go_edit_content("sel_modul_ext_" + i, 'img_edit_s_' + i);

        //сохраняем название файла для редактирования контента
        arr_link_content[i] = d[i].file_edit;

        $('#sel_modul_ext_' + i).selectmenu({
            style:'dropdown',
            maxHeight:200
        });


        // показываем иконки редактирования контента
        $("#flag_metka_" + i).bind('click', function ()
        {
            var elID = new String(this.id).split("_").pop();
            if(!$(this).attr('checked'))
            {
                show_icon_go_edit_content("sel_modul_ext_" + elID, 'img_edit_' + elID);
                show_icon_go_edit_content("sel_modul_ext_" + elID, 'img_edit_s_' + elID);
            }
        });
    }

}
var allModulesActions = new Array();
function buildMetkaActionsSelect(selectID)
{
    var elem;
    var lastOptGroup;
    var option;
    for (var j = 0; j < allModulesActions.length; j++)
    {
        elem = allModulesActions[j];
        if (!elem[0] && !elem[1]) //Действие не выбранно
        {
            option = $(document.createElement("option")).text(elem[2]).val("");
            $(selectID).append(option);
            continue;
        }
        if (elem[0]) //optgroup
        {
            lastOptGroup = $(document.createElement("optgroup")).attr('label', elem[0]);
            $(selectID).append(lastOptGroup);
        }
        else
        {
            option = $(document.createElement("option")).text(elem[2]).val(elem[1]);
            $(lastOptGroup).append(option);
        }
    }
}


/*!
 * Autogrow Textarea Plugin Version v2.0
 * http://www.technoreply.com/autogrow-textarea-plugin-version-2-0
 *
 * Copyright 2011, Jevin O. Sewaruth
 *
 * Date: March 13, 2011
 */
jQuery.fn.autoGrow = function(){
	return this.each(function(){
		// Variables
		var colsDefault = this.cols;
		var rowsDefault = this.rows;
		
		//Functions
		var grow = function() {
			growByRef(this);
		}
		
		var growByRef = function(obj) {
			var linesCount = 0;
			var lines = obj.value.split('\n');
			
			for (var i=lines.length-1; i>=0; --i)
			{
				linesCount += Math.floor((lines[i].length / colsDefault) + 1);
			}

			if (linesCount >= rowsDefault)
				obj.rows = linesCount + 1;
			else
				obj.rows = rowsDefault;
		}
		
		var characterWidth = function (obj){
			var characterWidth = 0;
			var temp1 = 0;
			var temp2 = 0;
			var tempCols = obj.cols;
			
			obj.cols = 1;
			temp1 = obj.offsetWidth;
			obj.cols = 2;
			temp2 = obj.offsetWidth;
			characterWidth = temp2 - temp1;
			obj.cols = tempCols;
			
			return characterWidth;
		}
		
		// Manipulations
		this.style.width = "auto";
		this.style.height = "auto";
		this.style.overflow = "hidden";
		this.style.width = ((characterWidth(this) * this.cols) + 6) + "px";
		this.onkeyup = grow;
		this.onfocus = grow;
		this.onblur = grow;
		growByRef(this);
	});
};
