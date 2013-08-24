<?php

if (!defined('tr')) exit('No direct script access allowed');

class user_admin extends admin {

    public function __construct() {
        parent::__construct();
        $this->_model = new \user_model;
    }

    /**
     * index - Интерфейс управления пользователями
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function index() {
        $this->f3->set('tmp.admin.introduction', 'Пользователи системы');
        $this->f3->set('tmp.admin.config', $this->get_config('user'));
        $this->f3->set('tmp.admin.module', 'user');
        $this->f3->set('tmp.admin.html', 'app/module/user/view/admin_index.php');

        return $this->admin_env();
    }

    /**
     * get - Запрос всей информации по пользователю.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get() {
        $id_user = $this->f3->get('POST.id_user');
        $user = $this->_model->get_user_by_id($id_user);

        if (!$user) {
            return FALSE;
        }

        $user_main = new \user_main;
        return array(
            "name"      => $user['name'],
            "id_group"  => $user['id_group'],
            "avatar"    => $user['avatar'],
            "url_avatar"=> $user_main->get_url_avatar($user),
            "ip"        => $user['ip'],
            "register"  => $user['register'],
            "email"     => $user['email'],
            "profile"   => $this->_model->get_profile($id_user)
        );
    }

    /**
     * set - Внесение изменений в данные пользователя
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set() {
        $id_user = $this->f3->get('POST.id_user');
        $name = $this->f3->get('POST.name');
        $pass = $this->f3->get('POST.pass') != '' ? $this->f3->get('POST.pass') : FALSE;
        $id_group = $this->f3->get('POST.id_group');
        $avatar = $this->f3->get('POST.avatar');
        $email = $this->f3->get('POST.email');
        $profile = $this->f3->get('POST.profile');

        if ($this->_model->upd_user($profile, $email, $avatar, $pass, $id_group, $name, $id_user) === FALSE) {
            return array("error" => TRUE);
        }
        return array();
    }

    /**
     * del - Удаление пользователя
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del() {
        $id_user = $this->f3->get('POST.id_user');
        return $this->_model->del_user($id_user);
    }

    /**
     * get_profile - Получение полей профиля
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_profile() {
        $id = (int)$this->f3->get('POST.id_profile');
        return $this->_model->get_fields_profile($id);
    }

    /**
     * set_profile - Добавление/Обновление полей профиля
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set_profile() {
        $id         = (int)$this->f3->get('POST.id');
        $field      = $this->f3->get('POST.field');
        $comment    = $this->f3->get('POST.comment');
        $type       = $this->f3->get('POST.type');
        $maxlen     = $this->f3->get('POST.maxlen');

        if ($this->_model->set_fields_profile($id, $field, $comment, $type, $maxlen) === FALSE) {
            return array('error' => TRUE);
        }
        return;
    }

    /**
     * set_profile_sort - Изменение порядка сортировки профиля
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set_profile_sort() {
        $sort = $this->f3->get('POST.sort');

        if ($this->_model->set_sort_profile($sort) === FALSE) {
            return array('error' => TRUE);
        }
        return;
    }

    /**
     * del_profile - Удаление поля профиля
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del_profile() {
        $id = (int)$this->f3->get('POST.id');

        if ($this->_model->del_fields_profile($id) === FALSE) {
            return array('error' => TRUE);
        }
        return;
    }
}


