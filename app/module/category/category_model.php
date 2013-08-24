<?php

if (!defined('tr')) exit('No direct script access allowed');

class category_model extends model {

    public function __construct() {
        parent::__construct();
        $this->_table = new DB\SQL\Mapper($this->_db, 'category');
    }

    /**
     * Возвращает данные из БД по одной категории, в случаее е отсутствия FALSE
     *
     * @param int $id ИД категории
     * @return array Map с данными категории
     */
    public function get_category($id) {
        $this->_table->reset();
        $this->_table->load(array("id = ?", $id));
        return $this->_table->dry() ? FALSE : $this->_table->cast();
    }

    /**
     * get_list
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_list() {
        $this->_table->reset();
        $this->_table->load(NULL, array('order' => 'sort ASC'));
        return orm_transfer($this->_table);
    }

    /**
     * set
     *
     * @param mixed $id        Description.
     * @param mixed $title     Description.
     * @param mixed $id_parent Description.
     * @param mixed $desc      Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set($id, $title, $id_parent, $desc) {
        $this->_table->reset();
        if ($id > 0) {
            $this->_table->load(array("id = ?", $id));
        }

        $this->_table->title     = $title;
        $this->_table->id_parent = $id_parent;
        $this->_table->desc      = $desc;
        $this->_table->visible   = $visible;

        if ($id == 0) {
            $m = $this->_db->exec("SELECT MAX(`sort`) as 'm' FROM `category` WHERE `id_parent` = '" . $id_parent . "';");
            $max = (int)$m[0]['m'];

            $this->_table->sort     = ++$max;
        }

        return $this->_table->save() === FALSE ? FALSE : TRUE;
    }

    /**
     * del
     *
     * @param mixed $id Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del($id) {
        // Удалить все комментарии в материалах которые находятся в категории
        // Удалить все материалы которые находятся в категории
        // Удалить категорию
        $this->_table->reset();
        $content = new \content_model;
        return ($content->del_content_category($id) !== FALSE) && ($this->_table->erase(array("id = ?", $id)) !== FALSE);
    }

    /**
     * Получение подкатегорий которые входят в категорию $id
     *
     * @param int $id ИД категории родителя
     * @return array Map с данными категории
     */
    public function get_inner_category($id) {
        $this->_table->reset();
        $this->_table->load(
            array('id_parent = ? AND visible = 1', $id),
            array('order' => 'sort ASC'));
        return orm_transfer($this->_table);
    }

    /**
     * get_inner_category_r - Получение категорий и их подкатегорий которые входят в категорию $id (рекурсивно).
     *
     * @param int $id Идентификатор категории родителя.
     *
     * @access public
     *
     * @return array Map с данными категории.
     */
    public function get_inner_category_r($id = 0) {
        $this->_table->reset();
        $this->_table->load(array('id_parent = ?', $id), array('order' => 'sort ASC'));

        if ($this->_table->dry()) {
            return array();
        }

        $result = array();
        $list = orm_transfer($this->_table);
        foreach ($list as $category) {
            $this->_table->reset();
            $this->_table->load(array('id_parent = ?', $category['id']), array('order' => 'sort ASC'));

            if (!$this->_table->dry()) {
                $category['inner'] = $this->get_inner_category_r($category['id']);
            }

            $result[] = $category;
        }

       return $result;
    }

    /**
     * get_flat_list_inner_category - Вернет плосский список всех id вложенных категорий
     *
     * @param int   $id            Description.
     * @param mixed $list_category Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_flat_list_inner_category($id = 0, array $list_category = NULL) {
        if (!is_int($id)) {
            return FALSE;
        }
        if (is_null($list_category)) {
            $list_category = $this->get_inner_category_r($id);
        }

        $result = array();
        foreach ($list_category as $category) {
            if ($category['visible']) {
                $result[] = $category['id'];
                if (isset($category['inner'])) {
                    $inner = $this->get_flat_list_inner_category(0, $category['inner']);
                    $result = array_merge($result, $inner);
                }
            }
        }

        return $result;
    }

    public function get_path($id) {
        $category = $this->get_category($id);

        if ($category['id_parent'] == 0) {
            return array(array(
                'id'    => $category['id'],
                'title' => $category['title']));
        } else {
            return array_merge(
                array(array('id' => $category['id'], 'title' => $category['title'])),
                $this->get_path($category['id_parent']));
        }
    }

    /**
     * set_sort
     *
     * @param mixed $sort Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set_sort($sort) {
        $result = TRUE;
        $i = 1;
        foreach ($sort as $id) {
            $this->_table->reset();
            $this->_table->load(array("id = ?", $id));
            $this->_table->sort = $i++;
            $result = $this->_table->save() === FALSE ? FALSE : TRUE;
        }
        return $result;
    }
}