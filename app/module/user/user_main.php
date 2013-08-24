<?php

if (!defined('tr')) exit('No direct script access allowed');

class user_main extends main {

    public function __construct() {
        parent::__construct();
        $this->_model = new \user_model;

        $this->f3->set('tmp_ajax_user_get_list_user',    $this->f3->get('init.sys.url') . "main/user/get_list_user");
    }

    /**
     * panel_user - Панель пользователя для входа в систему и выхода.
     *
     * @access public
     *
     * @return NULL
     */
    public function panel_user() {
        $panel = 'app/module/user/view/sign_in.php';
        $this->f3->set('tmp.user.i.id', 0);

        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != 0) {
            $user = $this->_model->get_user_by_id((int)$_SESSION['user_id']);

            if (!$user) {
                $this->sign_out();
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_group'] = $user['id_group'];
                $_SESSION['user_name'] = $user['name'];

                $this->f3->set('tmp.user.i.id',         $user['id']);
                $this->f3->set('tmp.user.i.name',       $user['name']);
                $this->f3->set('tmp.user.i.register',   $user['register']);
                $this->f3->set('tmp.user.i.email',      $user['email']);
                $this->f3->set('tmp.user.i.avatar',     $user['avatar']);
                $this->f3->set('tmp.user.i.src.avatar', $this->get_url_avatar($user));
                $this->f3->set('tmp.user.i.src.navbar', $this->get_url_avatar($user, 'navbar'));
                $panel = 'app/module/user/view/sign_out.php';
            }
        }

        $this->f3->set('tmp.user.panel', $panel);
    }

    /**
     * sign_out - Выход из системы
     *
     * @access public
     *
     * @return bool TRUE.
     */
    public function sign_out() {
        session_unset();
        $_SESSION['user_id'] = 0;
        $_SESSION['user_group'] = 5;
        $_SESSION['user_name'] = '';

        return TRUE;
    }

    /**
     * sign_in - Функция предназначена для авторизации человека и занесения необходимой информации в сессию.
     *
     * @access public
     *
     * @return bool TRUE|FALSE.
     */
    public function sign_in() {
        $bcrypt = new \Bcrypt;

        $name = $this->f3->get('POST.name');
        $pass = $this->f3->get('POST.pass');

        $user = $this->_model->get_user_by_name($name);

        // 1 - В БД нет такого пользователя
        // 2 - Введенный пароль и пароль пользователя не совпадают
        if (($user) && $bcrypt->verify($pass, $user['pass'])) {
            //Когда все проверки завершены - информация помещается в сессию
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $name;
            $_SESSION['user_group'] = $user['id_group'];
            return TRUE;
        }

        return FALSE;
    }

    /**
     * sign_up - Регистрация на сайте.
     *
     * @access public
     *
     * @return bool TRUE|FALSE.
     */
    public function sign_up() {
        $bcrypt = new \Bcrypt;

        $name   = $this->f3->get('POST.name');
        $pass   = $bcrypt->hash($this->f3->get('POST.pass'));
        $email  = $this->f3->get('POST.email');

        // Проверка на наличие пользователя с таким же именем и наличие ошибки при шифровании пароля
        if (($pass === FALSE) || ($this->_model->get_user_by_name($name))) {
            return FALSE;
        }
        // Добавление пользователя в БД
        return $this->_model->add_user($name, $pass, $email);
    }

    /**
     * get_user_link - Получить ссылку на профиль пользователя по id.
     *
     * @param int $id id пользователя.
     *
     * @access public
     *
     * @return bool|string FALSE|HTML.
     */
    public function get_user_link($user) {
        if (is_int($user)) {
            $user = $this->_model->get_user_by_id($user);
        }

        return $user == FALSE ? : get_link("main/user/profile/" . $user['id'], $user['name']);
    }

    /**
     * profile - Доступ к профилю пользователя.
     *
     * @access public
     *
     * @return string Адрес шаблона.
     */
    public function profile() {
        $id = (int)$this->_id;

        $user = $this->_model->get_user_by_id($id);

        if (!$user) {
            $this->error_page(404);
        }

        $this->f3->set('tmp.user.profile.name',         $user['name']);
        $this->f3->set('tmp.user.profile.register',     $user['register']);
        $this->f3->set('tmp.user.profile.list_field',   $this->_model->get_profile($id, FALSE));
        $this->f3->set('tmp.user.profile.id',           $id);
        $this->f3->set('tmp.user.profile.url_avatar',   $this->get_url_avatar($user));

        return 'app/module/user/view/profile.php';
    }

    /**
     * mine - Доступ к своему профилю.
     *
     * @access public
     *
     * @return string Адрес шаблона.
     */
    public function mine() {
        if (!$this->get_access('user')) {
            $this->error_page(403);
        }

        // К этому моменту вся основная информация о пользователе уже имеется, осталось загрузить только поля профиля
        $this->f3->set('tmp.user.i.profile', $this->_model->get_profile($_SESSION['user_id']));
        return 'app/module/user/view/mine.php';
    }

    /**
     * change_profile - Изменение своего профиля.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function change_profile() {
        if (!$this->get_access('user')) {
            return FALSE;
        }

        $new_pass       = $this->f3->get('POST.new_pass');
        $repeat_pass    = $this->f3->get('POST.repeat_pass');
        $email          = $this->f3->get('POST.email');
        $avatar         = $this->f3->get('POST.avatar');
        $profile        = $this->f3->get('POST.profile');

        $error = $success = '';
        $pass = FALSE;
        if ($new_pass != '') {
            if ($new_pass === $repeat_pass) {
                $bcrypt = new \Bcrypt;
                $pass = $bcrypt->hash($new_pass);
            } else {
                return FALSE;
            }
        }

        return $this->_model->upd_user($profile, $email, $avatar, $pass);
    }

    /**
     * get_url_avatar - Получить url аватара.
     *
     * @param array  $user    Массив информации о пользователе или его id.
     * @param string $version Версия аватара.
     *
     * @access public
     *
     * @return string url.
     */
    public function get_url_avatar($user, $version = 'avatar') {
        // $user['avatar'];
        // $user['id'];
        if (is_int($user)) {
            $user = $this->_model->get_user_by_id($user);
        }

        if (!$user) {
            return $this->f3->get('init.sys.include') . "images/user_64.png";
        }

        //Проверка наличия аватара
        $avatar = $this->f3->get('init.sys.dir') . "upload/" . $user['id'] . "/" . $version . "/" . $user['avatar'];
        if ((!$user['avatar']) || !file_exists($avatar)) {
            $avatar = $this->f3->get('init.sys.include') . "images/user_64.png";
        } else {
            $avatar = $this->f3->get('init.sys.upload') . $user['id'] . "/" . $version . "/" . $user['avatar'];
        }

        return $avatar;
    }

    /**
     * change_avatar - Изменение аватара пользователя.
     *
     * @access public
     *
     * @return string JSON строка с результатом изменений.
     */
    public function change_avatar() {
        if (!$this->get_access('user')) {
            return FALSE;
        }

        $this->_upload = new \UploadHandler;
    }

    /**
     * message_env - Загружает функциональное окружение механизма отправки сообщений.
     *
     * @param string $table Description.
     *
     * @access public
     *
     * @return string Адрес шаблона.
     */
    protected function message_env($table = '') {
        if (!$this->get_access('user')) {
            $this->error_page(403);
        }

        if (!$table) {
            return $this->list_talk();
        }

        $this->f3->set('tmp.user.form_talk', $table);
        return 'app/module/user/view/message_env.php';
    }

    /**
     * list_talk - Функция возвращает список входящих/исходящих писем пользователя.
     *
     * @access public
     *
     * @return string HTML.
     */
    public function list_talk() {
        if (!$this->get_access('user')) {
            return $this->f3->get('AJAX') ? FALSE : $this->error_page(403);
        }

        $data = $this->_model->get_list_talk();

        $user_link = $list_talk = $person = array();
        foreach ($data as $key => $value) {
            $id_person = $value['id_owner'] == $value['id_recipient'] ? $value['id_sender'] : $value['id_recipient'];
            if (!isset($person[$value['id_sender']])) {
                $person[$id_person] = $this->_model->get_user_by_id($id_person);
            }

            $message = array(
                'user_link' => $this->get_user_link(isset($person[$value['id_sender']]) ? $person[$value['id_sender']] : $value['id_sender']),
                'last_date' => $value['date'],
                'text'      => $value['message']
            );
            if (isset($list_talk[$id_person])) {
                // Для существующего разговора обновляем дату и последнее сообщение
                if ($list_talk[$id_person]['last_date'] < $value['date']) {
                    $list_talk[$id_person]['last_date'] = $value['date'];
                    $list_talk[$id_person]['last_message'] = $message;
                    $list_talk[$id_person]['showed'] = $value['showed'] == 0 ? 0 : 1;
                }
            } else {
                $list_talk[$id_person] = array(
                    'person'        => $id_person,
                    'link'          => $this->f3->get('init.sys.url') . "main/user/talk/" . $id_person,
                    'avatar'        => $this->get_url_avatar($person[$id_person]),
                    'user_name'     => $person[$id_person]['name'],
                    'last_date'     => $value['date'],
                    'showed'        => $value['showed'],
                    'last_message'  => $message);
            }
        }

        $transform_talk = array();
        foreach ($list_talk as $value) {
            $transform_talk[strtotime($value['last_date'])] = $value;
        }
        krsort($transform_talk);

        $this->f3->set('tmp.user.list_talk', $transform_talk);

        if ($this->f3->get('AJAX')) {
            $template = new \Template;
            return array(
                'table' => $template->render('app/module/user/view/list_talk.php')
            );
        }

        return $this->message_env('app/module/user/view/list_talk.php');
    }

    /**
     * talk - Проведение текущего разговора.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function talk() {
        if (!$this->get_access('user')) {
            return $this->f3->get('AJAX') ? FALSE : $this->error_page(403);
        }

        $id_person = (int)$this->_id;
        if (($id_person <= 0) && ($id_person == $_SESSION['user_id'])) {
            header("Location: " . $this->f3->get('init.sys.url') . "main/user/list_talk");
            return;
        }

        $data = $this->_model->get_list_talk($id_person);
        // Данные пользователя с которым будет проведен разговор
        $user = $this->_model->get_user_by_id($id_person);

        $list_talk = $person = array();
        foreach ($data as $key => $value) {
            $id_person = $value['id_owner'] == $value['id_recipient'] ? $value['id_sender'] : $value['id_recipient'];
            if (!isset($person[$id_person])) {
                $person[$id_person] = $this->_model->get_user_by_id($id_person);
            }

            if (isset($list_talk[$id_person])) {
                if ($list_talk[$id_person]['last_date'] < $value['date']) {
                    $list_talk[$id_person]['last_date'] = $value['date'];
                    $list_talk[$id_person]['showed'] = $value['showed'] == 0 ? 0 : 1;
                }
            } else {
                $list_talk[$id_person] = array(
                    'person'    => $id_person,
                    'link'      => $this->f3->get('init.sys.url') . "main/user/talk/" . $id_person,
                    'avatar'    => $this->get_url_avatar($person[$id_person]),
                    'user_name' => $person[$id_person]['name'],
                    'showed'    => $value['showed'],
                    'last_date' => $value['date']);
            }
        }

        // Поскольку будет произведена сортировка по дате последних принятых сообщений,
        // для человека с которым будет вести сейчас разговор определим время как 0,
        // таким образом он будет вверху списка собеседников, + определяем его отедльно от людей
        // с которыми беседа ведется для ситуации когда таковых нет.
        $talk_person = array(
            'person'    => $user['id'],
            'link'      => $this->f3->get('init.sys.url') . "main/user/talk/" . $user['id'],
            'avatar'    => $this->get_url_avatar($user),
            'user_name' => $user['name'],
            'last_date' => 0);
        $transform_talk = array();
        foreach ($list_talk as $value) {
            $transform_talk[strtotime($value['last_date'])] = $value;
        }
        krsort($transform_talk);

        $this->f3->set('tmp.user.talk_person',   $talk_person);
        $this->f3->set('tmp.user.list_person',   $transform_talk);
        $this->f3->set('tmp.user.id_person',     $user['id']);
        $this->f3->set('tmp.user.link_person',   $this->get_user_link($user));

        if ($this->f3->get('AJAX')) {
            $template = new \Template;
            return array(
                'table'     => $template->render('app/module/user/view/talk.php')
            );
        }

        return $this->message_env('app/module/user/view/talk.php');
    }

    /**
     * get_list_user - Возвращает список пользователей для поиска.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_list_user() {
        $sym = $this->f3->get('POST.sym');
        $all = (int)$this->f3->get('POST.all');

        $list_user = array();
        if ($sym) {
            $list_user = $this->_model->get_list_user($sym, $all);
        }

        if ($list_user) {
            $data = array();

            foreach ($list_user as $user) {
                $data[$user['id']] = array(
                    'name'      => $user['name'],
                    'avatar'    => $this->get_url_avatar(array('id' => $user['id'], 'avatar' => $user['avatar'])));
            }

            $list_user = $data;
        }

        return array(
            'list_user' => $list_user
        );
    }

    /**
     * exchange - Передача сообщений пользователю.
     *
     * @access public
     *
     * @return array|bool Результат|FALSE.
     */
    public function exchange() {
        if (!$this->get_access('user')) {
            return FALSE;
        }

        $person = (int)$this->f3->get('POST.person');
        // Какие сообщения необходимо отправить определяем по параметру времени последнего переданного сообщения
        $last_date = $this->f3->get('POST.last_date');

        $list_message = $this->_model->get_list_message($person, $last_date);

        // Если сообщений много то каждый раз запрашивать url к профилю отправителя сообщений не выгодно.
        // Уже известны id собеседников, поэтому определим для них url и будм после обращаться к уже подготовленным данным
        $sender[$person] = $this->get_user_link($person);
        $sender[$_SESSION['user_id']] = $this->get_user_link($_SESSION['user_id']);

        $talk = array();
        foreach ($list_message[2] as $message) {
            $talk[$message['id']] = array(
                'sender'    => $sender[$message['id_sender']],
                'date'      => $message['date'],
                'message'   => $message['message']);
        }

        return array(
            'new'       => $list_message[0],
            'last_date' => $list_message[1],
            'talk'      => $talk
        );
    }

    /**
     * new_message - Отправка нового сообщения пользователю.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function new_message() {
        if (!$this->get_access('user')) {
            return FALSE;
        }

        $person = (int)$this->f3->get('POST.person');
        $message = $this->f3->get('POST.message');

        return $this->_model->add_message($person, $message);
    }

    /**
     * del_message - Удаление сообщения.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del_message() {
        if (!$this->get_access('user')) {
            return FALSE;
        }

        $id_message = (int)$this->f3->get('POST.id_message');

        return $this->_model->remove_message($id_message);
    }

    /**
     * del_talk - Удаление разговора.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del_talk() {
        if (!$this->get_access('user')) {
            return FALSE;
        }

        $person = (int)$this->f3->get('POST.person');
        $table = $this->f3->get('POST.table');

        return array_merge(
            array('success' => $this->_model->remove_talk($person)),
            $table ? $this->list_talk() : array()
        );
    }

    /**
     * get_new_message - Передача новых непрочитанных сообщений.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_new_message() {
        if (!$this->get_access('user')) {
            return FALSE;
        }

        $person = (int)$this->f3->get('POST.person');
        $list_message = $this->_model->get_list_talk($person);

        $amount = 0;
        foreach ($list_message as $message) {
            if ($message['showed'] == 0) {
                $amount++;
            }
        }

        return array(
            'new'       => (bool)$amount,
            'amount'    => $amount
        );
    }
}
