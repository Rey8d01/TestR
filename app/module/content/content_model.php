<?php

if (!defined('tr')) exit('No direct script access allowed');

class content_model extends model {

    public function __construct() {
        parent::__construct();
        $this->_table = new DB\SQL\Mapper($this->_db, 'content');
    }

    /**
     * get_list_content - Получение всех материалов в категории.
     *
     * @param int|string $list_category id категории или их перечень через ','.
     * @param int        $start         Стартовая позиция в показе.
     * @param int        $limit         Количество элементов в показе.
     *
     * @access public
     *
     * @return array Value.
     */
    public function get_list_content($list_category, $start = 1, $limit = 0) {
        $this->_table->reset();
        // $page=$user->paginate(2,5,array('visits>?',3));

        $param = array(
                'order' => 'created DESC',
                'limit' => $limit,
                'offset'=> $start == 0 ? 0 : $start -1);

        // if (($start == 0) && ($limit == 0)) {
        //     $param = NULL;
        // }

        if (is_int($list_category)) {
            $this->_table->load(array("id_category = ?", $list_category), $param);
        } else {
            $this->_table->load(array("id_category IN (" . $list_category . ")"), $param);
        }

        return orm_transfer($this->_table);
    }


    /**
     * get_count_content - Вернет количество материалов в категории
     *
     * @param mixed $list_category Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_count_content($list_category) {
        $this->_table->reset();

        if (is_int($list_category)) {
            return $this->_table->count(array("id_category = ?", $list_category));
        } else {
            return $this->_table->count(array("id_category IN (" . $list_category . ")"));
        }
    }

    /**
     * get_list
     *
     * @param mixed $id_category Description.
     * @param int   $start       Description.
     * @param int   $limit       Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_list($id_category = 0, $start = 1, $limit = 10) {
        $this->_table->reset();

        // $param = array(
        //         'order' => 'created DESC',
        //         'limit' => $limit,
        //         'offset'=> $start == 0 ? 0 : $start -1);
        $param = array('order' => 'created DESC');

        $where = array('id_category = ?', $id_category);
        if ($id_category == 0) {
            $where = NULL;
        }
        $this->_table->load($where, $param);

        return orm_transfer($this->_table);
    }

    /**
     * del_content_category - Удаление всех материалов в категории
     *
     * @param int $id_category Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del_content_category($id_category) {
        $this->_table->reset();
        $this->_table->load(array('id_category = ?', $id_category));

        $list_id_content = array();
        while (!$this->_table->dry()) {
            $content = $this->_table->cast();
            $list_id_content[] = $content['id'];
            $this->_table->skip();
        }
        $id_content = implode(',', $list_id_content);

        $comment = new DB\SQL\Mapper($this->_db, 'comment');
        $comment->erase(array("id_content IN (" . $id_content . ")"));

        return $this->_table->erase(array('id_category = ?', $id_category));
    }

    /**
     * del_content_user - Удаление всех материалов от пользователя
     *
     * @param int $id_user Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del_content_user($id_user) {
        $this->_table->reset();
        $this->_table->load(array('id_user = ?', $id_user));

        $list_id_content = array();
        while (!$this->_table->dry()) {
            $content = $this->_table->cast();
            $list_id_content[] = $content['id'];
            $this->_table->skip();
        }
        if ($list_id_content) {
            $id_content = implode(',', $list_id_content);

            $comment = new DB\SQL\Mapper($this->_db, 'comment');
            $comment->erase(array("id_content IN (" . $id_content . ")"));
        }

        return $this->_table->erase(array('id_user = ?', $id_user)) === FALSE ? FALSE : TRUE;
    }

    /**
     * del_comment_user - Удаление всех комментариев от пользователя
     *
     * @param int $id_user Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function del_comment_user($id_user) {
        $comment = new DB\SQL\Mapper($this->_db, 'comment');
        return $comment->erase(array('id_user = ?', $id_user)) === FALSE ? FALSE : TRUE;
    }

    /**
     * Получение указанного материала и обновление у него количества просмотров
     *
     * @param int $id
     * @return array
     */
    public function get_content($id) {
        $this->_table->reset();
        $this->_table->load(array('id = ?', $id));
        if ($this->_table->dry()) {
            return FALSE;
        }
        $this->_table->view++;
        $this->_table->save();
        return $this->_table->cast();
    }

    /**
     * Получение комментариев к материалу
     *
     * @param int $id ИД материала
     * @return int
     */
    public function get_list_comment($id_content) {
        $comment = new DB\SQL\Mapper($this->_db, 'comment');
        $comment->load(
            array('id_content = ?', $id_content),
            array('created' => 'created ASC'));
        return orm_transfer($comment);
    }

    /**
     * Получение количества комментариев к материалу
     *
     * @param int $id ИД материала
     * @return int
     */
    public function get_count_comments($id_content) {
        $comment = new DB\SQL\Mapper($this->_db, 'comment');
        $comment->load(
            array('id_content = ?', $id_content),
            array('created' => 'created ASC'));
        return count(orm_transfer($comment));
    }


    /**
     * Добавление комментария
     *
     * @param int $id_content
     * @param string $message
     * @return bool TRUE|FALSE
     */
    public function add_comment($id_content, $message) {
        $comment = new DB\SQL\Mapper($this->_db, 'comment');
        $comment->id_user = $_SESSION['user_id'];
        $comment->id_content = $id_content;
        $comment->message = $message;
        return $comment->save() === FALSE ? FALSE : TRUE;
    }

    public function set($id, $id_category, $title, $photos, $icon, $desc) {
        $this->_table->reset();
        if ($id > 0) {
            $this->_table->load(array('id = ?', $id));
        }

        $this->_table->id_category  = $id_category;
        $this->_table->id_user      = $_SESSION['user_id'];
        $this->_table->title        = $title;
        $this->_table->photos       = $photos;
        $this->_table->desc         = $desc;

        return $this->_table->save() === FALSE ? FALSE : TRUE;
    }

    public function del($id) {
        $this->_table->reset();
        $comment = new DB\SQL\Mapper($this->_db, 'comment');
        return ($comment->erase(array('id_content = ?', $id)) !== FALSE) && ($this->_table->erase(array('id = ?', $id)) !== FALSE);
    }
}