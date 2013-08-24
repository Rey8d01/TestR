<?php

if (!defined('tr')) exit('No direct script access allowed');

class content_admin extends admin {

    public function __construct() {
        parent::__construct();
        $this->_model = new \content_model;
    }

    /**
     * index
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function index() {
        $this->f3->set('tmp.admin.introduction', 'Контент системы');
        $this->f3->set('tmp.admin.config', $this->get_config('content'));
        $this->f3->set('tmp.admin.module', 'content');
        $this->f3->set('tmp.admin.html', 'app/module/content/view/admin_index.php');

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
        return $this->_model->get_content($id);
    }

    /**
     * get_list
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_list() {
        $id_category = (int)$this->f3->get('POST.id_category');
        return $this->_model->get_list($id_category);
    }

    public function set() {
        $id             = (int)$this->f3->get("POST.id");
        $id_category    = (int)$this->f3->get("POST.id_category");
        $title          = $this->f3->get("POST.title");
        $photos         = json_encode($this->f3->get("POST.photos"));
        $icon           = $this->f3->get("POST.icon");
        // Текстовку принимаем в чистов виде без фильтрации
        $desc           = $this->f3->get("POST.desc", NULL, FALSE);

        return $this->_model->set($id, $id_category, $title, $photos, $icon, $desc);
    }

    public function del() {
        $id = $this->f3->get("POST.id");
        return $this->_model->del($id);
    }

    public function upload_photos() {
        $this->_upload = new \UploadHandler;
    }
}
