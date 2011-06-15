<?php
	/**
	 * language pack
	 * @author s@nchez (s [at] nchez [dot] me)
	 */
	define('DATE_TIME_FORMAT', 'd/M/Y H:i:s');
	//Common
	//Menu
	define('MENU_SELECT', 'Выбрать');
	define('MENU_DOWNLOAD', 'Скачать');
	define('MENU_PREVIEW', 'Просмотр');
	define('MENU_RENAME', 'Переименовать');
	define('MENU_EDIT', 'Редактировать');
	define('MENU_CUT', 'Вырезать');
	define('MENU_COPY', 'Скопировать');
	define('MENU_DELETE', 'Удалить');
	define('MENU_PLAY', 'Play');
	define('MENU_PASTE', 'Вставить');

	//Label
		//Top Action
		define('LBL_ACTION_REFRESH', 'Обновить');
		define('LBL_ACTION_DELETE', 'Удалить');
		define('LBL_ACTION_CUT', 'Вырезать');
		define('LBL_ACTION_COPY', 'Скопировать');
		define('LBL_ACTION_PASTE', 'Вставить');
		define('LBL_ACTION_CLOSE', 'Закрыть');
		define('LBL_ACTION_SELECT_ALL', 'Выделить всё');
		//File Listing
	define('LBL_NAME', 'Название');
	define('LBL_SIZE', 'Размер');
	define('LBL_MODIFIED', 'Изменён');
		//File Information
	define('LBL_FILE_INFO', 'Информация о файле:');
	define('LBL_FILE_NAME', 'Имя:');
	define('LBL_FILE_CREATED', 'Создан:');
	define('LBL_FILE_MODIFIED', 'Изменён:');
	define('LBL_FILE_SIZE', 'Размер файла:');
	define('LBL_FILE_TYPE', 'Тип файла:');
	define('LBL_FILE_WRITABLE', 'Открыт на запись?');
	define('LBL_FILE_READABLE', 'Читаем?');
		//Folder Information
	define('LBL_FOLDER_INFO', 'Информация о папке');
	define('LBL_FOLDER_PATH', 'Папка:');
	define('LBL_CURRENT_FOLDER_PATH', 'Текущий путь:');
	define('LBL_FOLDER_CREATED', 'Создана:');
	define('LBL_FOLDER_MODIFIED', 'Изменена:');
	define('LBL_FOLDER_SUDDIR', 'Подпапок:');
	define('LBL_FOLDER_FIELS', 'Файлов:');
	define('LBL_FOLDER_WRITABLE', 'Записываема?');
	define('LBL_FOLDER_READABLE', 'Читаема?');
	define('LBL_FOLDER_ROOT', 'Контент');//Начальная папка
		//Preview
	define('LBL_PREVIEW', 'Preview');
	define('LBL_CLICK_PREVIEW', 'Нажмите для просмотра');
	//Buttons
	define('LBL_BTN_SELECT', 'Выбрать');
	define('LBL_BTN_CANCEL', 'Отменить');
	define('LBL_BTN_UPLOAD', 'Загрузить');
	define('LBL_BTN_CREATE', 'Создать');
	define('LBL_BTN_CLOSE', 'Закрыть');
	define('LBL_BTN_NEW_FOLDER', 'Новая папка');
	define('LBL_BTN_NEW_FILE', 'Новый файл');
	define('LBL_BTN_EDIT_IMAGE', 'Редактировать');
	define('LBL_BTN_VIEW', 'Вид');
	define('LBL_BTN_VIEW_TEXT', 'Текст');
	define('LBL_BTN_VIEW_DETAILS', 'Детально');
	define('LBL_BTN_VIEW_THUMBNAIL', 'Мелкие значки');
	define('LBL_BTN_VIEW_OPTIONS', 'View In:');
	//pagination
	define('PAGINATION_NEXT', 'След.');
	define('PAGINATION_PREVIOUS', 'Пред.');
	define('PAGINATION_LAST', 'Последняя');
	define('PAGINATION_FIRST', 'Первая');
	define('PAGINATION_ITEMS_PER_PAGE', 'Показать %s элементов на страницу');
	define('PAGINATION_GO_PARENT', 'На уровень выше');
	//System
	define('SYS_DISABLED', 'В доступе отказано. Система отключена');

	//Cut
	define('ERR_NOT_DOC_SELECTED_FOR_CUT', 'Не выбраны документы для вырезания');
	//Copy
	define('ERR_NOT_DOC_SELECTED_FOR_COPY', 'Не выбраны документы для копирования');
	//Paste
	define('ERR_NOT_DOC_SELECTED_FOR_PASTE', 'Не выбраны документы для вставки');
	define('WARNING_CUT_PASTE', 'Вы уверены, что хотите переместить выбранные документы в текущую папку?');
	define('WARNING_COPY_PASTE', 'Вы уверены, что хотите скопировать выбранные документы в текущую папку?');
	define('ERR_NOT_DEST_FOLDER_SPECIFIED', 'Не выбрана папка назначения');
	define('ERR_DEST_FOLDER_NOT_FOUND', 'Папка назначения не найдена');
	define('ERR_DEST_FOLDER_NOT_ALLOWED', 'Запрещено перемещение в эту папку');
	define('ERR_UNABLE_TO_MOVE_TO_SAME_DEST', 'Невозможно переместить файл (%s): Оригинальный путь совпадает с путём назначения.');
	define('ERR_UNABLE_TO_MOVE_NOT_FOUND', 'Невозможно переместить файл (%s): Файл не существует');
	define('ERR_UNABLE_TO_MOVE_NOT_ALLOWED', 'Невозможно переместить файл (%s): Отказано в доступе к файлу.');

	define('ERR_NOT_FILES_PASTED', 'Файлы не были вставлены');

	//Search
	define('LBL_SEARCH', 'Поиск');
	define('LBL_SEARCH_NAME', 'Название или часть:');
	define('LBL_SEARCH_FOLDER', 'Искать в:');
	define('LBL_SEARCH_QUICK', 'Быстрый поиск');
	define('LBL_SEARCH_MTIME', 'Время изменения (диапазон):');
	define('LBL_SEARCH_SIZE', 'Размер файла:');
	define('LBL_SEARCH_ADV_OPTIONS', 'Расширенные настройки');
	define('LBL_SEARCH_FILE_TYPES', 'Типы файлов:');
	define('SEARCH_TYPE_EXE', 'Приложение EXE');

	define('SEARCH_TYPE_IMG', 'Изображение');
	define('SEARCH_TYPE_ARCHIVE', 'Архив');
	define('SEARCH_TYPE_HTML', 'HTML');
	define('SEARCH_TYPE_VIDEO', 'Видео');
	define('SEARCH_TYPE_MOVIE', 'Фильм');
	define('SEARCH_TYPE_MUSIC', 'Музыка');
	define('SEARCH_TYPE_FLASH', 'Флэш');
	define('SEARCH_TYPE_PPT', 'PowerPoint');
	define('SEARCH_TYPE_DOC', 'Документ');
	define('SEARCH_TYPE_WORD', 'Word');
	define('SEARCH_TYPE_PDF', 'PDF');
	define('SEARCH_TYPE_EXCEL', 'Excel');
	define('SEARCH_TYPE_TEXT', 'Текст');
	define('SEARCH_TYPE_UNKNOWN', 'Неизвестный');
	define('SEARCH_TYPE_XML', 'XML');
	define('SEARCH_ALL_FILE_TYPES', 'Все типы файлов');
	define('LBL_SEARCH_RECURSIVELY', 'Искать во вложенных:');
	define('LBL_RECURSIVELY_YES', 'Да');
	define('LBL_RECURSIVELY_NO', 'Нет');
	define('BTN_SEARCH', 'Искать');
	//thickbox
	define('THICKBOX_NEXT', 'След.&gt;');
	define('THICKBOX_PREVIOUS', '&lt;Пред.');
	define('THICKBOX_CLOSE', 'Закрыть');
	//Calendar
	define('CALENDAR_CLOSE', 'Закрыть');
	define('CALENDAR_CLEAR', 'Очистить');
	define('CALENDAR_PREVIOUS', '&lt;Пред');
	define('CALENDAR_NEXT', 'След.&gt;');
	define('CALENDAR_CURRENT', 'Сегодня');
	define('CALENDAR_MON', 'Пон');
	define('CALENDAR_TUE', 'Вт');
	define('CALENDAR_WED', 'Ср');
	define('CALENDAR_THU', 'Чт');
	define('CALENDAR_FRI', 'Пт');
	define('CALENDAR_SAT', 'Суб');
	define('CALENDAR_SUN', 'Воскр');
	define('CALENDAR_JAN', 'Янв');
	define('CALENDAR_FEB', 'Фев');
	define('CALENDAR_MAR', 'Март');
	define('CALENDAR_APR', 'Апр');
	define('CALENDAR_MAY', 'Май');
	define('CALENDAR_JUN', 'Июнь');
	define('CALENDAR_JUL', 'Июль');
	define('CALENDAR_AUG', 'Авг');
	define('CALENDAR_SEP', 'Сент');
	define('CALENDAR_OCT', 'Окт');
	define('CALENDAR_NOV', 'Ноябрь');
	define('CALENDAR_DEC', 'Дек');
	//ERROR MESSAGES
		//deletion
	define('ERR_NOT_FILE_SELECTED', 'Выберите файл');
	define('ERR_NOT_DOC_SELECTED', 'Документы для удаления не выбраны');
	define('ERR_DELTED_FAILED', 'Невозможно удалить выбранные');
	define('ERR_FOLDER_PATH_NOT_ALLOWED', 'Этот путь не разрешён');
		//class manager
	define('ERR_FOLDER_NOT_FOUND', 'Невозможно найти указанный путь: ');
		//rename
	define('ERR_RENAME_FORMAT', 'Имя может содержать только буквы, цифры, пробел, подчёркивание и дефис.');
	define('ERR_RENAME_EXISTS', 'Папка с таким именем уже существует.');
	define('ERR_RENAME_FILE_NOT_EXISTS', 'Файл/папка не существует.');
	define('ERR_RENAME_FAILED', 'Невозможно переименовать, попробуйте ещё раз.');
	define('ERR_RENAME_EMPTY', 'Введите имя.');
	define('ERR_NO_CHANGES_MADE', 'Изменений нет.');
	define('ERR_RENAME_FILE_TYPE_NOT_PERMITED', 'Запрещено изменение расширения на такое.');
		//folder creation
	define('ERR_FOLDER_FORMAT', 'Имя может содержать только буквы, цифры, пробел, подчёркивание и дефис.');
	define('ERR_FOLDER_EXISTS', 'Папка с таким именем уже существует.');
	define('ERR_FOLDER_CREATION_FAILED', 'Невозможно создать папку, попробуйте ещё раз.');
	define('ERR_FOLDER_NAME_EMPTY', 'Введите название.');
	define('FOLDER_FORM_TITLE', 'Форма создания папки');
	define('FOLDER_LBL_TITLE', 'Имя:');
	define('FOLDER_LBL_CREATE', 'Создать папку');
	//New File
	define('NEW_FILE_FORM_TITLE', 'Форма создания файла');
	define('NEW_FILE_LBL_TITLE', 'Имя:');
	define('NEW_FILE_CREATE', 'Создать файл');
		//file upload
	define('ERR_FILE_NAME_FORMAT', 'Имя может содержать только буквы, цифры, пробел, подчёркивание и дефис.');
	define('ERR_FILE_NOT_UPLOADED', 'Файл для загрузки не выбран.');
	define('ERR_FILE_TYPE_NOT_ALLOWED', 'Запрещена загрузка этого типа файла.');
	define('ERR_FILE_MOVE_FAILED', 'Невозможно переместить файл');
	define('ERR_FILE_NOT_AVAILABLE', 'Файл недоступен.');
	define('ERROR_FILE_TOO_BID', 'Файл слишком большой. (макс: %s)');
	define('FILE_FORM_TITLE', 'Форма загрузки');
	define('FILE_LABEL_SELECT', 'Выбрать файл');
	define('FILE_LBL_MORE', 'Добавить ещё');
	define('FILE_CANCEL_UPLOAD', 'Отменить загрузку');
	define('FILE_LBL_UPLOAD', 'Загрузить');
	//file download
	define('ERR_DOWNLOAD_FILE_NOT_FOUND', 'Файлы для скачивания не выбраны');
	//Rename
	define('RENAME_FORM_TITLE', 'Форма переименования');
	define('RENAME_NEW_NAME', 'Новое имя');
	define('RENAME_LBL_RENAME', 'Переименовать');

	//Tips
	define('TIP_FOLDER_GO_DOWN', 'Кликните для перехода в папку...');
	define('TIP_DOC_RENAME', 'Двойной клик для редактирования...');
	define('TIP_FOLDER_GO_UP', 'Кликните для перехода в папку выше...');
	define('TIP_SELECT_ALL', 'Отметить всё');
	define('TIP_UNSELECT_ALL', 'Снять всё');
	//WARNING
	define('WARNING_DELETE', 'Удалить выбранные документы?.');
	define('WARNING_IMAGE_EDIT', 'Выберите изображение для редактирования');
	define('WARNING_NOT_FILE_EDIT', 'Выберите файл для редактирования');
	define('WARING_WINDOW_CLOSE', 'Закрыть окно?');
	//Preview
	define('PREVIEW_NOT_PREVIEW', 'Предпросмотра нет');
	define('PREVIEW_OPEN_FAILED', 'Невозможно открыть файл.');
	define('PREVIEW_IMAGE_LOAD_FAILED', 'Невозможно загрузить изображение');

	//Login
	define('LOGIN_PAGE_TITLE', 'Ajax File Manager Login Form');
	define('LOGIN_FORM_TITLE', 'Login Form');
	define('LOGIN_USERNAME', 'Username:');
	define('LOGIN_PASSWORD', 'Password:');
	define('LOGIN_FAILED', 'Invalid username/password.');


	//88888888888   Below for Image Editor   888888888888888888888
		//Warning
		define('IMG_WARNING_NO_CHANGE_BEFORE_SAVE', 'You have not made any changes to the images.');

		//General
		define('IMG_GEN_IMG_NOT_EXISTS', 'Изображение не существует');
		define('IMG_WARNING_LOST_CHANAGES', 'Все несохранённые изменения будут потеряны. Продолжить?');
		define('IMG_WARNING_REST', 'Все несохранённые изменения будут потеряны. Продолжить?');
		define('IMG_WARNING_EMPTY_RESET', 'Изображение не было изменено');
		define('IMG_WARING_WIN_CLOSE', 'Закрыть окно?');
		define('IMG_WARNING_UNDO', 'Уверены, что хотите восстановить предыдущее состояние?');
		define('IMG_WARING_FLIP_H', 'Уверены, что хотите отразить по горизонтали?');
		define('IMG_WARING_FLIP_V', 'Уверены, что хотите отразить по вертикали?');
		define('IMG_INFO', 'Информаиця о изображении');

		//Mode
			define('IMG_MODE_RESIZE', 'Изменить размер:');
			define('IMG_MODE_CROP', 'Crop:');
			define('IMG_MODE_ROTATE', 'Повернуть:');
			define('IMG_MODE_FLIP', 'Отразить:');
		//Button

			define('IMG_BTN_ROTATE_LEFT', '90&deg;CCW');
			define('IMG_BTN_ROTATE_RIGHT', '90&deg;CW');
			define('IMG_BTN_FLIP_H', 'Отразить по горизонтали');
			define('IMG_BTN_FLIP_V', 'Отразить по вертикали');
			define('IMG_BTN_RESET', 'Сбросить');
			define('IMG_BTN_UNDO', 'Отменить');
			define('IMG_BTN_SAVE', 'Сохранить');
			define('IMG_BTN_CLOSE', 'Закрыть');
			define('IMG_BTN_SAVE_AS', 'Сохранить как');
			define('IMG_BTN_CANCEL', 'Отмена');
		//Checkbox
			define('IMG_CHECKBOX_CONSTRAINT', 'Constraint?');
		//Label
			define('IMG_LBL_WIDTH', 'Ширина:');
			define('IMG_LBL_HEIGHT', 'Высота:');
			define('IMG_LBL_X', 'X:');
			define('IMG_LBL_Y', 'Y:');
			define('IMG_LBL_RATIO', 'Пропорции:');
			define('IMG_LBL_ANGLE', 'Угол:');
			define('IMG_LBL_NEW_NAME', 'Новое имя:');
			define('IMG_LBL_SAVE_AS', 'Сохарнить как:');
			define('IMG_LBL_SAVE_TO', 'Сохранить в :');
			define('IMG_LBL_ROOT_FOLDER', 'Контент');//Root Folder
		//Editor
		//Save as
		define('IMG_NEW_NAME_COMMENTS', 'Не вводите расширение');
		define('IMG_SAVE_AS_ERR_NAME_INVALID', 'Имя может содержать только буквы, цифры, пробел, подчёркивание и дефис.');
		define('IMG_SAVE_AS_NOT_FOLDER_SELECTED', 'Не выбрана папка назначения.');
		define('IMG_SAVE_AS_FOLDER_NOT_FOUND', 'Папка не существует');
		define('IMG_SAVE_AS_NEW_IMAGE_EXISTS', 'Изображение с таким именем уже существует');

		//Save
		define('IMG_SAVE_EMPTY_PATH', 'Пустой путь');
		define('IMG_SAVE_NOT_EXISTS', 'Изображение не существует');
		define('IMG_SAVE_PATH_DISALLOWED', 'У вас нет прав на доступ к этому файлу');
		define('IMG_SAVE_UNKNOWN_MODE', 'Unexpected Image Operation Mode');
		define('IMG_SAVE_RESIZE_FAILED', 'Невозможно сделать ресайз');
		define('IMG_SAVE_CROP_FAILED', 'Невозможно сделать crop');
		define('IMG_SAVE_FAILED', 'Невозможно сохранить');
		define('IMG_SAVE_BACKUP_FAILED', 'Невозможно сделать бэкап существующего изображения');
		define('IMG_SAVE_ROTATE_FAILED', 'Невозможно повернуть изображение');
		define('IMG_SAVE_FLIP_FAILED', 'Невозможно отразить изображение');
		define('IMG_SAVE_SESSION_IMG_OPEN_FAILED', 'Невозможно открыть изображение из сессии');
		define('IMG_SAVE_IMG_OPEN_FAILED', 'Невозможно открыть изображение');


		//UNDO
		define('IMG_UNDO_NO_HISTORY_AVAIALBE', 'История для отмены пустая');
		define('IMG_UNDO_COPY_FAILED', 'Невозможно восставноить изображение');
		define('IMG_UNDO_DEL_FAILED', 'Невозможно удалить изображение из сессии');

	//88888888888   Above for Image Editor   888888888888888888888

	//88888888888   Session   888888888888888888888
		define('SESSION_PERSONAL_DIR_NOT_FOUND', 'Unable to find the dedicated folder which should have been created under session folder');
		define('SESSION_COUNTER_FILE_CREATE_FAILED', 'Unable to open the session counter file.');
		define('SESSION_COUNTER_FILE_WRITE_FAILED', 'Unable to write the session counter file.');
	//88888888888   Session   888888888888888888888

	//88888888888   Below for Text Editor   888888888888888888888
		define('TXT_FILE_NOT_FOUND', 'Файл не найден');
		define('TXT_EXT_NOT_SELECTED', 'Выберите расширение');
		define('TXT_DEST_FOLDER_NOT_SELECTED', 'Выберите папку');
		define('TXT_UNKNOWN_REQUEST', 'Неизвестный запрос');
		define('TXT_DISALLOWED_EXT', 'You are allowed to edit/add such file type.');
		define('TXT_FILE_EXIST', 'Такой файл уже существует');
		define('TXT_FILE_NOT_EXIST', 'Не найдено.');
		define('TXT_CREATE_FAILED', 'Невозможно создать файл.');
		define('TXT_CONTENT_WRITE_FAILED', 'Невозможно записать контент в файл.');
		define('TXT_FILE_OPEN_FAILED', 'Невозможно открыть файл.');
		define('TXT_CONTENT_UPDATE_FAILED', 'Невозможно обновить содержание файла');
		define('TXT_SAVE_AS_ERR_NAME_INVALID', 'Имя может содержать только буквы, цифры, пробел, подчёркивание и дефис.');
	//88888888888   Above for Text Editor   888888888888888888888


?>