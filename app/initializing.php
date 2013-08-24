<?php

if (!defined('tr')) exit('No direct script access allowed');

class initializing {
    /**
     * Объект своей модели для каждого модуля
     *
     * @var object
     */
    public $_model = NULL;

    /**
     * Массив сегментов URI для определения запрашиваемого контента
     *
     * @var array
     */
    public $_segments = array();

    /**
     * Параметры необходимые для формирования контента
     * Извлекаются из URI по порядку идущих сегментов
     * http://www.sitename.com/main_controller/module/function/id/other_params_for_module
     * Нулевой сегмент определяет активный контроллер main или admin
     */
    public $_module = '';
    public $_function = '';
    public $_id = '';

    /**
     * __construct
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function __construct() {
        //Берем себе копию f3 для прямого обращения к его функциям
        $this->f3 = Base::instance();

        $this->_segments    = &$this->f3->_segments;
        $this->_module      = &$this->f3->_segments[1];
        $this->_function    = &$this->f3->_segments[2];
        $this->_id          = &$this->f3->_segments[3];
    }
}
