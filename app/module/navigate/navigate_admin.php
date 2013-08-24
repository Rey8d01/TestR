<?php

if (!defined('tr')) exit('No direct script access allowed');

class navigate_admin extends admin {

    public function __construct() {
        parent::__construct();

        $this->_model = new \navigate_model;
    }

    /**
     * Создание топового меню
     *
     * @return string html
     */
    public function index() {
        $this->f3->set('tmp.admin.introduction', 'Навигационное меню');
        $this->f3->set('tmp.admin.config', $this->get_config('navigate'));
        $this->f3->set('tmp.admin.module', 'navigate');
        $this->f3->set('tmp.admin.html', 'app/module/navigate/view/admin_index.php');

        return $this->admin_env();
    }

    /**
     * get
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get() {
        $id = $this->f3->get('POST.id');
        return $this->_model->get($id);
    }

    /**
     * get_list
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_list_flat() {
        return $this->_model->get_list();
    }

    /**
     * get_list_tree
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_list_tree() {
        return $this->_model->get_list_recursion();
    }

    /**
     * set
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set() {
        $id         = $this->f3->get('POST.id');
        $id_parent  = $this->f3->get('POST.id_parent');
        $id_group   = $this->f3->get('POST.id_group');
        $type       = $this->f3->get('POST.type');
        $title      = $this->f3->get('POST.title');
        $__module   = $this->f3->get('POST.__module');
        $__function = $this->f3->get('POST.__function');
        $__id       = $this->f3->get('POST.__id');

        return $this->_model->set($id, $id_parent, $id_group, $type, $title, $__module, $__function, $__id);
    }

    /**
     * del
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del() {
        $id = $this->f3->get('POST.id');
        return $this->_model->del($id);
    }

    /**
     * set_sort
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set_sort() {
        $sort = $this->f3->get('POST.sort');
        return $this->_model->set_sort($sort);
    }
}
