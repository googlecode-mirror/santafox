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
        close: function() {update_action_list(); }

    });
}

function santaUpdateRegion(regid, loadFrom)
{
    //на всякий случай скроем всякие всплывающие окна
    $('#popup_msg_div').css('display','none');
    $('#popup_div').css('display','none');
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

    if ($('#'+elem1ID).attr("checked"))
        $('#'+elem2ID).attr("disabled",true);
    else
        $('#'+elem2ID).removeAttr("disabled");
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
                    santaShowPopupHint(msg_label, msg,1500);
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
        show: "slide",
	    hide: "slide"//"explode"
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
		var width = parseInt(screen.width - 810);
		var height = parseInt(screen.height - 180);
		if (width < 810)
			width = 810;
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
               skin : 'v2',
               autoUpdateElement:true,
               filebrowserBrowseUrl :'/components/html_editor/plugins/ajaxfilemanager/ajaxfilemanager.php',
               filebrowserImageBrowseUrl : '/components/html_editor/plugins/ajaxfilemanager/ajaxfilemanager.php',
               filebrowserFlashBrowseUrl :'/components/html_editor/plugins/ajaxfilemanager/ajaxfilemanager.php',
               LinkBrowserWindowHeight:440,
               ImageBrowserWindowHeight:440,
               FlashBrowserWindowHeight:440,
               LinkUpload:false,
               ImageUpload:false,
               FlashUpload:false,
               language:'ru',
               toolbar_Full: [
                ['Source','-',/*'AjaxSave'*/,'Preview'],
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
    if (typeof inst != 'undefined')
        inst.destroy();
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