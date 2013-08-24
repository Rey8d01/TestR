<?php

if (!defined('tr')) exit('No direct script access allowed');

class category_admin extends admin {

    public function __construct() {
        parent::__construct();
        $this->_model = new \category_model;
    }

    /**
     * index
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function index() {
        $this->f3->set('tmp.admin.introduction', 'Категории материалов');
        $this->f3->set('tmp.admin.config', $this->get_config('category'));
        $this->f3->set('tmp.admin.module', 'category');
        $this->f3->set('tmp.admin.html', 'app/module/category/view/admin_index.php');

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
        $id = $this->f3->get('POST.id_category');
        return $this->_model->get_category($id);
    }

    /**
     * get_list
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_list() {
        return $this->_model->get_inner_category_r();
    }

    /**
     * get_flat_list
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_flat_list() {
        return $this->_model->get_list();
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
        $title      = $this->f3->get('POST.title');
        $id_parent  = $this->f3->get('POST.id_parent');
        $desc       = $this->f3->get('POST.desc');
        $visible    = $this->f3->get('POST.visible');

        return $this->_model->set($id, $title, $id_parent, $desc, $visible);
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
