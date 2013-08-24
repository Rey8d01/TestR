<?php
define('tr', TRUE);

$f3 = require('lib/base.php');

$f3->set('DEBUG',0);
error_reporting(0);

/**
 * Изменения в системных файлах
 * * при изменении в БД при возникновении исключения f3 прерывает сценарий, поставил обработчик
 * /lib/db/cursor.php:156
 * * хотя при error_reporting(0) оно, возможно, там даже не нужно
 * /lib/base.php:254
 * В стандартную функцию get внесены изменения - третьим параметром идет флаг определяющий возвращать ли данные заменив все
 * html символы их аналогами - f3->esc() - почему то f3->scrub() коментирует непечатаемые символы но пропускает <script> что позволяет ввести js код на страницу
 */

//--------------------------------------------------------------------------------------------------
// Инициализация системы и определение ведущего контроллера

// Подключение библиотеки с функциями помошника
require('app/helper.php');

// Чтение базовой конфигурации
$f3->config('app/config.cfg');

// Соединение с БД
try {
    $f3->_db = new \DB\SQL($f3->get('init.db.dsn'), $f3->get('init.db.user'), $f3->get('init.db.pw'), $f3->get('init.db.opt'));
} catch (Exception $e) {
    die('Connect to DB failed. Please check config file and available database.');
}

// Загрузка таблиц и установка системы если она запускается первый раз и таблиц в базе недостаточно.
try {
    $f3->_db->exec("SELECT `table_name` FROM `information_schema`.`tables` where `table_schema` = '" . $f3->_db->name() . "'");
    if ($f3->_db->count() < 9) {
        set_time_limit(0);
        $setup = file('app/setup.sql');
        if (!$setup) {
            throw new Exception('Not found app/setup.sql');
        }
        if ($f3->_db->exec($setup) === FALSE) {
            throw new Exception('Setup is faild.');
        }
        unlink('app/setup.sql');

        die("The installation was successful. Use username: 'admin' and password '12345'. To continue, please reload the page. Good luck! :)");
    }
} catch (Exception $e) {
    die("Setup script is failed. " . $e->getMessage());
}

// Установка и определение параметров системы
// $_SERVER['SERVER_NAME'] = 'localhost'
// $_SERVER['SERVER_ADDR'] = '127.0.0.1';
// $_SERVER['SERVER_PORT'] = '8080';
// $_SERVER['DOCUMENT_ROOT'] = '/var/www/';
// $_SERVER['SCRIPT_FILENAME'] = '/var/www/php/testr/index.php';
// $_SERVER['REQUEST_URI'] = '/php/testr/';
// $_SERVER['SCRIPT_NAME'] = '/php/testr/index.php';
// Соглашения:
// dir Путь по серверной ОС (Linux, Win)
// path Путь по непосредственно серверу (Apache, Nginx)
// url Ссылка для доступа через http
// folder Поддиректория в базовой директории
// Путь к системе относительно DOCUMENT_ROOT
// php/testr/
$path = mb_substr($_SERVER['SCRIPT_NAME'], 0, mb_strpos($_SERVER['SCRIPT_NAME'], "index.php", 0, "utf-8"), "utf-8");

// Базовый URL по которому идет обращение со всех мест (css, js, jpg...)
// 127.0.0.1:8080/php/testr/
// !убрано  . ":" . $_SERVER['SERVER_PORT']
$url = "http://" . $_SERVER['SERVER_NAME'] . $path;

// Базовая директория расположения системы на сервере
// /var/www/php/testr/
$dir = $_SERVER['DOCUMENT_ROOT'] . $path;

$f3->set('init.sys.path',     $path);
// $f3->get('init.sys.path');
$f3->set('init.sys.url',      $url);
// $f3->get('init.sys.url');
$f3->set('init.sys.dir',      $dir);
// $f3->get('init.sys.dir');

// Определение ссылок на директории для загрузки файлов и подключаемых библиотек
$f3->set('init.sys.include',  $url . "include/");
// $f3->get('init.sys.include');
$f3->set('init.sys.upload',   $url . "upload/");
// $f3->get('init.sys.upload');

// Загрузка настроек из БД
$config = new DB\SQL\Mapper($f3->_db, 'config');
$config->load();
while (!$config->dry()) {
    $variable = $config->cast();
    $f3->set($variable['module'] . "." . $variable['variable'], $variable['value']);
    $config->skip();
}

$f3->set('init.page_title',   $f3->get('init.site_name') . " - " . $f3->get('init.site_slogan'));

$f3->set('testing_result', '');
$f3->set('test', '');

// Старт системной сессии
session_name('testr');
// Устанавливаем время жизни 7 дней
session_set_cookie_params(7*24*60*60);
session_start();
// Проверка параметров сессии
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 0;
}
if (!isset($_SESSION['user_group'])) {
    $_SESSION['user_group'] = 5;
}
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = '';
}

// Подгрузка внешних библиотек
$f3->_log = new \Log(date($f3->get('init.date_format')) . ".log");

// Получение сегментов URI для определения подключаемых модулей и запрашиваемого контента
if (!array_key_exists('PATH_INFO', $_SERVER) && !array_key_exists('REQUEST_URI', $_SERVER)) {
    return FALSE;
}

if (array_key_exists('PATH_INFO', $_SERVER)) {
    $segments = $_SERVER['PATH_INFO'];
} else {
    $segments = mb_strcut($_SERVER['REQUEST_URI'], mb_strlen($f3->get('init.sys.path'), "utf-8"));
}

// Для разделения URI по сегментам используется регулярка выдранная из CodeIgniter'a
$_segments = explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $f3->scrub($segments)));
// Нулевой сегмент всегда присутствует - он определяет контроллер обработки
// если он не указан (то есть в URI прописан голый адрес без параметров),
// то он будет пустым, но он все равно будет
// Если первые сегменты отсутствуют (а именно они определеяют какой контент должен
// быть загружен), то определим их сразу как FALSE, чтобы система не выбрасывала исключение
array_push($_segments, FALSE);
array_push($_segments, FALSE);
array_push($_segments, FALSE);
$f3->_segments = $_segments;

$unit = $_segments[0] = $_segments[0] == 'admin' ? 'admin' : 'main';
//--------------------------------------------------------------------------------------------------

// $testr - Объект через котрый осуществляется работа с системой
$testr = new $unit;

// Вот и весь роутинг - ajax по одну сторону, html по другую
$f3->route('GET|POST /* [sync]',
    function($f3) use ($testr) {
        $testr->get_html();

        // Для логов подсчитываем память. (в Mбайтах)
        $memory_use     = round(memory_get_usage()/1024/1024, 4);
        $memory_peak    = round(memory_get_peak_usage()/1024/1024, 4);
        $memory_real    = round(memory_get_usage(TRUE)/1024/1024, 4);
        $memory_real_peak    = round(memory_get_peak_usage(TRUE)/1024/1024, 4);
        $f3->_log->write("use = " . $memory_use . "; peak = " . $memory_peak . "; real = " . $memory_real . "; real_peak = " . $memory_real_peak);
    }
);

$f3->route('POST /* [ajax]',
    function() use ($testr) {
        $testr->get_ajax();
    }
);

// Переопределение страницы ошибок
$f3->set('ONERROR',
    function($f3) use ($testr) {
        $testr->error_page($f3->get('ERROR.code'));
    }
);

$f3->run();