<?php

if (!defined('tr')) exit('No direct script access allowed');

class user_model extends model {

    public function __construct() {
        parent::__construct();
        $this->_table = new DB\SQL\Mapper($this->_db, 'user');
    }

    /**
     *
     *
     * @param int $id
     * @return array
     */
    public function get_user_by_id($id) {
        $this->_table->reset();
        $this->_table->load(array('id = ?', $id));
        return $this->_table->dry() ? FALSE : $this->_table->cast();
    }

    /**
     * Функция предназначена для авторизации человека и занесения необходимой информации в сессию
     *
     * @param string $name
     * @return string
     */
    public function get_user_by_name($name) {
        $this->_table->reset();
        $this->_table->load(array('name = ?', $name));

        return $this->_table->dry() ? FALSE : $this->_table->cast();
    }

    /**
     * Занесение в БД данных по новому пользователю
     *
     * @param type $name
     * @param type $pass
     * @param type $email
     */
    public function add_user($name, $pass, $email = '') {
        $this->_table->reset();
        $this->_table->name = $name;
        $this->_table->pass = $pass;
        $this->_table->email = $email;
        $this->_table->ip = $_SERVER['REMOTE_ADDR'];

        // Если проблем с регистрацией нет - тут же его и авторизуем
        if ($this->_table->save() !== FALSE) {
            $user = $this->get_user_by_name($name) ? : array('id' => 0, 'id_group' => 5);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $name;
            $_SESSION['user_group'] = $user['id_group'];

            return $user['id'] ? TRUE : FALSE;
        }
        return FALSE;
    }

    /**
     * upd_user
     *
     * @param mixed $profile  Description.
     * @param mixed $email    Description.
     * @param mixed $avatar   Description.
     * @param mixed $pass     Description.
     * @param mixed $id_group Description.
     * @param mixed $name     Description.
     * @param mixed $id       Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function upd_user($profile, $email, $avatar, $pass = FALSE, $id_group = FALSE, $name = FALSE, $id = FALSE) {
        if (!$id_group && !$name && !$id) {
            $id = $_SESSION['user_id'];
            $name = $_SESSION['user_name'];
            $id_group = $_SESSION['user_group'];
        }

        if (!$this->get_user_by_id($id)) {
            return FALSE;
        }

        // Изменение полей профиля
        if ($profile) {
            $result = $this->set_profile($profile, $id);
            if (!$result) {
                return FALSE;
            }
        }

        // Изменение настроек пользователя
        if ($pass) {
            $this->_table->pass = $pass;
        }
        $this->_table->name     = $name;
        $this->_table->id_group = $id_group;
        $this->_table->email    = $email;
        $this->_table->avatar   = $avatar;
        return $this->_table->save() === FALSE ? FALSE : TRUE;
    }

    /**
     * del_user
     *
     * @param mixed $id Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del_user($id) {
        if (!$this->get_user_by_id($id)) {
            return FALSE;
        }

        $content = new \content_model;
        $content->del_content_user($id);
        $content->del_comment_user($id);
        $message = new DB\SQL\Mapper($this->_db, 'message');
        $message->erase(array("id_owner = ? OR id_sender = ? OR id_recipient = ?", $id, $id, $id));
        return $this->_table->erase(array("id = ?", $id)) === FALSE ? FALSE : TRUE;
    }

    /**
     * Получение полей профиля пользователя и их значений
     *
     * @param int $id_user
     * @param bool $edit Параметр определяющий показ поля при отсутствующем значении
     * @return array
     */
    public function get_profile($id_user, $edit = TRUE) {
        $fields = $this->get_fields_profile();
        if (!count($fields)) {
            return array();
        }

        $data_profile = new DB\SQL\Mapper($this->_db, 'data_profile');
        foreach ($fields as $key => $field) {
            $data_profile->reset();
            $data_profile->load(array('id_user = ? AND id_profile = ?', $id_user, $field['id']));
            $data = $data_profile->cast();

            if ($edit) {
                $fields[$key]['value'] = $data ? $data['value'] : '';
            } else {
                if (($data) && ($data['value'] != '')) {
                    $fields[$key]['value'] = $data['value'];
                } else {
                    unset($fields[$key]);
                }
            }
        }

        return $fields;
    }

    /**
     * get_fields_profile
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_fields_profile($id = 0) {
        $profile = new DB\SQL\Mapper($this->_db, 'profile');

        $where = NULL;
        if ($id > 0) {
            $where = array("id = ?", $id);
        }

        $profile->load($where, array('order' => 'sort ASC'));
        return $id ? $profile->cast() : orm_transfer($profile);
    }

    /**
     * Установка параметров профиля
     *
     * @param array $profile [id_profile] => value
     * @return bool
     */
    public function set_profile($profile, $id_user = FALSE) {
        if (!$id_user) {
            $id_user = $_SESSION['user_id'];
        }

        $data_profile = new DB\SQL\Mapper($this->_db, 'data_profile');

        foreach ($profile as $id_profile => $value) {
            $data_profile->reset();

            $data_profile->load(array('id_user = ? AND id_profile = ?', $id_user, $id_profile));

            $data_profile->id_user = $id_user;
            $data_profile->id_profile = $id_profile;
            $data_profile->value = $value;

            if ($data_profile->save() === FALSE) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * set_fields_profile
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set_fields_profile($id, $field, $comment, $type, $maxlen) {
        $m = $this->_db->exec("SELECT MAX(`sort`) as 'm' FROM `profile`");
        $max = (int)$m[0]['m'];

        $profile = new DB\SQL\Mapper($this->_db, 'profile');
        $profile->reset();
        if ($id > 0) {
            $profile->load(array("id = ?", $id));
        }

        $profile->field     = $field;
        $profile->comment   = $comment;
        $profile->type      = $type;
        $profile->maxlen    = $maxlen;
        $profile->sort      = ++$max;

        return $profile->save();
    }

    /**
     * set_sort_profile
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set_sort_profile($sort) {
        $profile = new DB\SQL\Mapper($this->_db, 'profile');

        $i = 1;
        foreach ($sort as $id) {
            $profile->load(array("id = ?", $id));
            $profile->sort = $i;

            if ($profile->save() === FALSE) {
                return FALSE;
            }

            $i++;
        }
        return TRUE;
    }

    /**
     * del_fields_profile
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del_fields_profile($id) {
        $profile = new DB\SQL\Mapper($this->_db, 'profile');
        $data_profile = new DB\SQL\Mapper($this->_db, 'data_profile');
        return ($data_profile->erase(array("id_profile = ?", $id)) !== FALSE) && ($profile->erase(array("id = ?", $id)) !== FALSE);
    }

    /**
     * Получение списка собеседников и сообщения с ними
     *
     * @param int $without_user ИД пользователя с которым будет проведена сейчас беседа,
     * данные по нему в общий список собеседников не вноясятся т.к. они будут получены отдельно
     */
    public function get_list_talk($without_user = 0) {
        $message = new DB\SQL\Mapper($this->_db, 'message');
        if (!$without_user) {
            $q = array('id_owner = ? AND deleted = 0', $_SESSION['user_id']);
        } else {
            $q = array('id_owner = ? AND deleted = 0 AND id_sender <> ? AND id_recipient <> ?', $_SESSION['user_id'], $without_user, $without_user);
        }
        $message->load($q);

        return $message->dry() ? array() : orm_transfer($message);

    }

    /**
     * Получаем список пользователей
     *
     *
     */
    public function get_list_user($sym = '', $all = TRUE) {
        $this->_table->reset();
        if ($all) {
            $this->_table->load(array('name LIKE ?', "%" . $sym . "%"), array('limit' => $this->f3->get('user.count_list_user')));
        } else {
            $this->_table->load(array('name LIKE ? AND id <> ?', "%" . $sym . "%", $_SESSION['user_id']), array('limit' => $this->f3->get('user.count_list_user')));
        }
        return $this->_table->dry() ? array() : orm_transfer($this->_table);
    }

    /**
     * Создание нового сообщения
     *
     *
     */
    public function add_message($person, $text_message) {
        $message = new DB\SQL\Mapper($this->_db, 'message');
        // Сообщение для отправителя
        $message->id_owner= $_SESSION['user_id'];
        $message->id_sender = $_SESSION['user_id'];
        $message->id_recipient = $person;
        $message->message = $text_message;
        $message->showed = 1;
        $message->deleted = 0;

        if ($message->insert() === FALSE) {
            return FALSE;
        }

        // Сообщение для получателя
        $message->reset();
        $message->id_sender = $_SESSION['user_id'];
        $message->id_recipient = $person;
        $message->message = $text_message;
        $message->deleted = 0;
        $message->showed = 0;
        $message->id_owner= $person;

        return $message->insert() === FALSE ? FALSE : TRUE;
    }

    /**
     * Получение сообщений
     *
     *
     */
    public function get_list_message($person, $last_date) {
        $message = new DB\SQL\Mapper($this->_db, 'message');
        $message->load(
            array('id_owner = ? AND deleted = 0 AND (id_sender = ? OR id_recipient = ?) AND date > ?', $_SESSION['user_id'], $person, $person, $last_date),
            array('order' => 'date ASC'));

        if ($message->dry()) {
            return array(
                0 => FALSE,
                1 => $last_date,
                2 => array());
        } else {
            // Обновление информации в сообщении о его прочитывании (для принимаемых сообщений от собеседника)
            $show = clone $message;

            while (!$show->dry()) {
                syslog(LOG_ERR, print_r('1', TRUE));
                $show->showed = 1;
                $show->save();
                $show->skip();
            }

            $list_message = orm_transfer($message);
            $last_message = end($list_message);
            return array(
                0 => TRUE,
                1 => $last_message['date'],
                2 => $list_message);
        }
    }

    /**
     * Удаление сообщения
     *
     *
     */
    public function remove_message($id_message) {
        $message = new DB\SQL\Mapper($this->_db, 'message');
        $message->load(array('id = ?', $id_message));
        $message->deleted = 1;
        return $message->save() === FALSE ? FALSE : TRUE;
    }

    /**
     * Удаление всех сообщений в разговоре
     *
     *
     */
    public function remove_talk($person) {
        $message = new DB\SQL\Mapper($this->_db, 'message');
        $message->load(array('id_owner = ? AND deleted = 0 AND (id_sender = ? OR id_recipient = ?)', $_SESSION['user_id'], $person, $person));

        $result = TRUE;
        while (!$message->dry()) {
            $message->deleted = 1;
            $result = ($message->save() !== FALSE) && $result ? TRUE : FALSE;
            $message->skip();
        }

        return $result;
    }
}