<?php

if (!defined('tr')) exit('No direct script access allowed');

class navigate_model extends model {

    public function __construct() {
        parent::__construct();
        $this->_table = new DB\SQL\Mapper($this->_db, 'navigate');
    }

    public function get_data($id_parent = 0) {
        $this->_table->load(
            array('id_parent = ? AND id_group >= ?', $id_parent, $_SESSION['user_group']),
            array('order' => 'sort ASC'));

        return orm_transfer($this->_table);
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
     * get_list_recursion
     *
     * @param int $id Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_list_recursion($id = 0) {
        $this->_table->reset();
        $this->_table->load(array('id_parent = ? AND id_group >= ?', $id, $_SESSION['user_group']), array('order' => 'sort ASC'));

        if ($this->_table->dry()) {
            return FALSE;
        }

        $result = array();
        $list = orm_transfer($this->_table);
        foreach ($list as $navigate) {
            $this->_table->reset();
            $this->_table->load(array('id_parent = ? AND id_group >= ?', $navigate['id'], $_SESSION['user_group']), array('order' => 'sort ASC'));

            if (!$this->_table->dry()) {
                $navigate['inner'] = $this->get_list_recursion($navigate['id']);
            }

            $result[] = $navigate;
        }

       return $result;
    }

    /**
     * get
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get($id) {
        $this->_table->reset();
        $this->_table->load(array('id = ?', $id));
        return $this->_table->cast();
    }

    /**
     * set
     *
     * @param mixed $id         Description.
     * @param mixed $id_parent  Description.
     * @param mixed $title      Description.
     * @param mixed $__module   Description.
     * @param mixed $__function Description.
     * @param mixed $__id       Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set($id, $id_parent, $id_group, $type, $title, $__module, $__function, $__id) {
        $this->_table->reset();
        if ($id > 0) {
            $this->_table->load(array("id = ?", $id));
        }

        $this->_table->id_parent    = $id_parent;
        $this->_table->id_group     = $id_group;
        $this->_table->type         = $id_parent == 0 ? 'link' : $type;
        $this->_table->title        = $title;
        $this->_table->__module     = $__module;
        $this->_table->__function   = $__function;
        $this->_table->__id         = $__id;

        // Не менять положение сортировки для существующих элементов
        if ($id == 0) {
            $m = $this->_db->exec("SELECT MAX(`sort`) as 'm' FROM `navigate` WHERE `id_parent` = '" . $id_parent . "';");
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
        $this->_table->reset();
        return $this->_table->erase(array("id = ?", $id)) !== FALSE;
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