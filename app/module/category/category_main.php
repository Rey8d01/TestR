<?php

if (!defined('tr')) exit('No direct script access allowed');

/**
* category_main
*
* @uses     main
*
* @category Category
* @package  Package
* @author    <>
* @license
* @link
*/
class category_main extends main {

    public function __construct() {
        parent::__construct();

        $this->_model = new \category_model;
    }

    /**
     * index - Показ 0 - корневой категории.
     *
     * @access public
     *
     * @return string Адрес шаблона.
     */
    public function index() {
        return $this->get(0);
    }

    /**
     * get - Функция возвращает построенную категорию со всеми ссылками на внутреение категории
     * и материалами содержащимися в ней самой.
     *
     * @param int $id   Идентификатор категории.
     * @param int $page Номер страницы перечисления материалов в данной категории.
     *
     * @access public
     *
     * @return string Адрес шаблона.
     */
    public function get($id = NULL, $page = NULL) {
        if ($id === NULL) {
            $id = (int)$this->_id;
        }
        if ($page === NULL) {
            // 4 сегментом идет порядок начала списка - ожидаем число - преобразуем к int
            $page = array_key_exists(4, $this->_segments) ? (int)$this->_segments[4] : 1;
            $page = $page ? : 1;
        }
        if (!is_int($id) || !is_int($page)) {
            $this->error_page(500);
        }

        // Проверка существования категории
        if ($id == 0) {
            $category = array(
                'title' => '',
                'desc'  => ''
            );
        } else {
            $category = $this->_model->get_category($id);
        }
        if ($category == FALSE) {
            $this->error_page(404);
        }

        //------------------------------------------------------------------------------------------
        // Оформление подкатегорий
        // Запрос на подкатегории которые входят в категорию
        $list_category = $this->_model->get_inner_category($id);
        $list_sub = array();
        foreach ($list_category as $sub) {
            $list_sub[] = array(
                'url'   => $this->f3->get('init.sys.url') . "main/category/get/" . $sub['id'],
                'title' => $sub['title'],
            );
        }

        //------------------------------------------------------------------------------------------
        //Оформление материалов входящих в данную категорию
        $content = new \content_main;
        // В качестве первого параметра функции передавать можно как один ид указанной категории,
        // так и плоский массив с идентификаторами категорий - на случай если захочется
        // выбрать материалы которые входят в подкатегории укзанной категории, но это необходимо
        // определять здесь
        //------------------------------------------------------------------------------------------

        // Собирание переменных для передачи их в шаблон
        $this->f3->set('tmp.category.id',           $id);
        $this->f3->set('tmp.category.title',        $category['title']);
        $this->f3->set('tmp.category.desc',         $category['desc']);
        $this->f3->set('tmp.category.path',         $this->get_path($id));
        $this->f3->set('tmp.category.list_sub',     $list_sub);
        $this->f3->set('tmp.category.list_content', $content->get_list_content($id, $page));

        return 'app/module/category/view/category.php';
    }

    /**
     * get_path - Получение пути местонахождения пользователя.
     *
     * @param int  $id   Идентификатор категории.
     * @param bool $tail Для хвоста ссылки не будет, для контента который находится внутри категории
     * устанавливать в FALSE для показа ссылки на ту категорию, в которой он находится.
     *
     * @access public
     *
     * @return string HTML.
     */
    public function get_path($id = 0, $tail = TRUE) {
        if (!is_int($id) || !is_bool($tail)) {
            $this->error_page(500);
        }

        $categories = $this->_model->get_path($id);
        if ((count($categories) == 1) && ($tail)) {
            return '';
        }

        //Построение пути местонахождения пользователя
        sort($categories);
        $path = array();
        foreach ($categories as $category) {
            if (($category['id'] == $id) && ($tail)) {
                // $path[] = $category['title'];
            } else {
                $path[] = get_link("main/category/get/" . $category['id'], $category['title']);
            }
        }

        return "&raquo; " . implode(' &raquo; ', $path);
    }

    /**
     * get_category_link - Получение честной ссылки на категорию по ее id.
     *
     * @param int $id Идентификатор категории.
     *
     * @access public
     *
     * @return string HTML.
     */
    public function get_category_link($id = 0) {
        if (!is_int($id)) {
            $this->error_page(500);
        }

        $category = $this->_model->get_category($id);
        return get_link("main/category/get/" . $category['id'], $category['title']);
    }

    /**
     * get_flat_list_inner_category - Получение плоского списка вложенных категорий
     *
     * @param int $id Идентификатор категории.
     *
     * @access public
     *
     * @return array Массив идентификаторов категорий.
     */
    public function get_flat_list_inner_category($id = 0) {
        if (!is_int($id)) {
            $this->error_page(500);
        }

        return $this->_model->get_flat_list_inner_category($id);
    }
}
