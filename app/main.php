<?php

if (!defined('tr')) exit('No direct script access allowed');

/**
* main
*
* @uses     initializing
*
* @category Category
* @package  Package
* @author    <>
* @license
* @link
*/
class main extends initializing {

    /**
     * $unit - переменная определяет какую часть загружать пользователю - главную или административную
     *
     * @var string
     *
     * @access protected
     */
    protected $_unit = 'main';

    public function __construct() {
        parent::__construct();
    }

    /**
     * get_html
     *
     * @access public
     *
     * @return mixed Value.
     */
    final public function get_html() {
        $navigate = new \navigate_main;
        $navigate->top_menu();

        $user = new \user_main;
        $user->panel_user();

        if (!$this->get_access()) {
            $this->error_page(403);
        }
        $this->_module = $this->_module ? $this->_module : 'index';

        // Загрузка контента из указанного в uri модуля, индексной страницы, страницы ошибок
        $content = '';
        $this->f3->set('tmp.hero', $hero);
        if (!$this->_module || ($this->_module == 'index')) {
            $content = $this->index();
        } else if (file_exists("app/module/" . $this->_module . "/" . $this->_module . "_" . $this->_unit . ".php")) {
            $module = $this->_module . "_" . $this->_unit;
            $obj_content = new $module;

            $function = $this->_function ? $this->_function : 'index';

            if (method_exists($obj_content, $function)) {
                // $content = $obj_content->$function();
                $content = $obj_content->$function();
                // die($this->f3->get('tmp.html'));
            } else {
                $this->error_page(404);
            }
        } else {
            $this->error_page(404);
        }

        // $this->f3->set('tmp_panel', $this->panel());
        // $this->f3->set('tmp_content', $content);
        $this->f3->set('tmp.html', $content);
        $this->f3->set('tmp_test', '');
        // $this->f3->set('tmp_test', $this->__test__());

//        echo gettype($this->f3->get('testing_result'));
        $template = new \Template;
        print($template->render("/view/template.php"));
    }

    /**
     * get_ajax
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_ajax() {
        $content = json_encode(array('error' => TRUE));

        if (file_exists("app/module/" . $this->_module . "/" . $this->_module . "_" . $this->_unit . ".php")) {
            $module = $this->_module . "_" . $this->_unit;
            $obj_content = new $module;
            if (method_exists($obj_content, $this->_function)) {
                $function = $this->_function;
                $content = $obj_content->$function();
            }
        }

        if ($content === FALSE) {
            $content = array('error' => TRUE);
        }

        if ($content === NULL) {
            return;
        }

        print(json_encode($content));
    }

    /**
     * index
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function index() {
        $content = new \content_main;
        if ((int)$this->f3->get('content.hero_id') > 0) {
            $content->get_hero((int)$this->f3->get('content.hero_id'));
        }
        return $content->index();
    }

    final public function error_page($error = 404) {
        switch ($error) {
            case 404:
                break;
            case 403:
                break;
            case 500:
                break;
            default:
                $error = 500;
                break;
        }
        $this->f3->_log->write("ERROR page" . $error . " " . $_SERVER['REMOTE_ADDR'] . " " . $_SERVER['REQUEST_URI']);
        $template = new \Template;
        printf($template->render("/view/" . $error . ".php"));
        die;
    }

    final public function error_ajax($error = array("error" => "Ошибка при загрузке данных")) {
        if (is_string($error) || is_bool($error)) {
            $error = array("error" => $error);
        }
        $this->f3->_log->write("ERROR ajax " . $_SERVER['REMOTE_ADDR'] . " " . $_SERVER['REQUEST_URI']);
        printf(json_encode($error));
        die;
    }

    /**
     * get_access - Определение уровня доступа пользователя
     *
     * @param mixed $user      Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    final public function get_access($module = '') {
        switch ($module) {
            // Проверка доступа к контроллеру
            case '':
                return (bool)$this->f3->get("init.access." . $_SESSION['user_group'] . "." . $this->_unit);
                break;
            case 'index':
                return (bool)$this->f3->get("init.access." . $_SESSION['user_group'] . "." . $this->_unit);
                break;
            default:
                // Проверка доступа к модулю в контексте контроллера
                if (is_bool($this->f3->get("init.access." . $_SESSION['user_group'] . "." . $this->_unit))) {
                    return FALSE;
                }
                return array_search($module, $this->f3->get("init.access." . $_SESSION['user_group'] . "." . $this->_unit)) === FALSE ? FALSE : TRUE;
                break;
        }

        return FALSE;
    }
}