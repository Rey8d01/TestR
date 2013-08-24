<?php

if (!defined('tr')) exit('No direct script access allowed');

class admin extends main {

    protected $_unit = 'admin';

    public function __construct() {
        parent::__construct();

        // Блокировка доступа к активному модулю при отсутствии прав
        if ($this->_module && ($this->_module != 'index')) {
	        if (!$this->get_access($this->_module)) {
	            $this->error_page(403);
	        }
        }
    }

    /**
     * get_ajax - Переопределние функции передачи данных через ajax для учета структуры функций в контроллере admin
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_ajax() {
        $content = FALSE;

        // Часть функций - таких как изменение параметров конфигурации для каждого модуля вынесена в контроллер так как идентична для каждого модуля
        // В связи с этим пришлось перекроить обработчик ajax-запросов
        if (($this->_module == 'index') && !$this->_function) {
            $content = $this->$index();
        } else
        if (($this->_module == 'index') && $this->_function) {
            $function = $this->_function;
            $content = $this->$function();
        } else
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
     * admin_env - Индексная функция загружает интерфейс для настройки системы.
     *
     * @param bool|object $module Объект для загрузки интерфейсов администрирования из модулей.
     *
     * @access public
     *
     * @return string HTML.
     */
    public function admin_env($module = FALSE) {
        // if ($module != FALSE) {
        //     $this->f3->set('tmp.admin.html', $module->index());
        // }

        // $this->f3->set('tmp_setting_list_tables', $this->aside_list_tables());
        $this->f3->set('tmp.admin.list_controls', $this->aside_list_controls());

        return '/view/admin_env.php';
    }

    /**
     * index
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function index() {
        $this->f3->set('tmp.admin.introduction', 'Базовые настройки для системы');
        $this->f3->set('tmp.admin.config', $this->get_config('init'));
        $this->f3->set('tmp.admin.module', 'init');
        $this->f3->set('tmp.admin.html', '/view/admin_index.php');

        return $this->admin_env();
    }

    /**
     * aside_list_controls - Блок навигации по настройкам системы.
     *
     * @access private
     *
     * @return array Список элементов меню.
     */
    protected function aside_list_controls() {
        // Определение меню навигации
        $controls = array(
            'index'     => 'Настройки',
            'navigate'  => 'Меню',
            'category'  => 'Категории',
            'content'   => 'Контент',
            'user'      => 'Пользователи',
            'setting'   => 'Данные',
        );

        $list_controls = array();
        foreach ($controls as $module => $title) {
            if (!$this->get_access($module)) {
                continue;
            }

            $active = FALSE;
            if ($this->_module == $module) {
                $active = TRUE;
            }
            $list_controls[] = array(
                'active'    => $active,
                'link'      => get_link("admin/". $module, $title)
            );
        }

        return $list_controls;
    }

    /**
     * get_config
     *
     * @param string $module Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_config($module = 'init') {
        $_table = new DB\SQL\Mapper($this->f3->_db, 'config');
        $_table->load(array("module = ?", $module));
        return $_table->dry() ? FALSE : orm_transfer($_table);
    }

    /**
     * set_config
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set_config() {
        $module = $this->f3->get('POST.module');
        $config = $this->f3->get('POST.config');

        $_table = new DB\SQL\Mapper($this->f3->_db, 'config');

        foreach ($config as $key => $value) {
            $_table->load(array("module = ? AND variable = ?", $module, $key));
            $_table->value = $value;

            if ($_table->save() === FALSE) {
                return array('error' => TRUE);
            }
        }
        return array();
    }
}