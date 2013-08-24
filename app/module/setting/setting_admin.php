<?php

if (!defined('tr')) exit('No direct script access allowed');

class setting_admin extends admin {

    /**
     * $show_id - Опция показа идентификаторов полей
     *
     * @var bool
     *
     * @access private
     */
    private $show_id = TRUE;

    /**
     * $table_schema - Объект метаданных полей таблицы - получаем после проверок на ее наличие
     *
     * @var object
     *
     * @access private
     */
    private $table_schema;

	public function __construct() {
        parent::__construct();

        $this->_model = new \setting_model;

        // Дальнейшие действия необходимы для AJAX загрузчика для функций управления данными
        if (!$this->f3->get('AJAX')) {
        	return;
        }

        // Получаем название таблицы
        $this->_id = $this->f3->get('POST.table');

        // Все опциональные блоки выводят функционал в модальную форму для проведения операций над соответствующей таблицей.
        // Поэтому для начала надо проверить корректность названия отправленной таблицы и находится ли она в списке исключений
        if (!$this->_model->table_exists($this->_id) || in_array($this->_id, $this->_model->exception_table)) {
            $this->error_ajax(TRUE);
        }

        // После проверки наличия таблицы - получим метаданные ее полей
        $this->table_schema = $this->_model->table_schema($this->_id);

        // Установка начальных параметров сортировки
        if ( (($this->f3->get('POST.order_by') === '') xor ($this->f3->get('POST.order_by') === 'DESC')) &&
            ($this->f3->get('POST.order_field') != '') ) {

            $this->_model->order_field = $this->f3->get('POST.order_field');
            $this->_model->order_by = $this->f3->get('POST.order_by');
        }


        // Установка начальных параметров поиска
        if (($this->f3->get('POST.like_field') != '') && ($this->f3->get('POST.like_data') != '')) {
            $this->_model->like_field = $this->f3->get('POST.like_field');
            $this->_model->like_data = $this->f3->get('POST.like_data');
        }

        // Установка начальных параметров фильтрации
        if ( ($this->f3->get('POST.filter_field') != '') &&
             ($this->f3->get('POST.filter') != '') &&
             ($this->f3->get('POST.filter_data') != '')
           ) {
            $this->_model->filter_field = explode(',', $this->f3->get('POST.filter_field'));
            $this->_model->filter = explode(',', $this->f3->get('POST.filter'));
            $this->_model->filter_data = explode(',', $this->f3->get('POST.filter_data'));
        }

        // Установка лимитов
        if ((int)$this->f3->get('POST.limit') > 0) {
            $this->_model->limit = (int)$this->f3->get('POST.limit');
        }
        if ((int)$this->f3->get('POST.start') > 1) {
            $this->_model->start = (int)$this->f3->get('POST.start');
        }
	}

    /**
     * index - Загружает интерфейс для внесения изменений в БД.
     *
     * @access public
     *
     * @return string HTML.
     */
    public function index() {
        $this->f3->set('tmp.setting.list_tables', $this->aside_list_tables());
        $this->f3->set('tmp.admin.list_controls', $this->aside_list_controls());
        $this->f3->set('tmp.setting.table', $this->_id);

        $default_value = array(
            'order_field'   => $this->_model->order_field,
            'order_by'      => $this->_model->order_by,
            'like_field'    => $this->_model->like_field,
            'like_data'     => $this->_model->like_data,
            'filter_field'  => $this->_model->filter_field,
            'filter'        => $this->_model->filter,
            'filter_data'   => $this->_model->filter_data,
            'limit'         => $this->_model->limit,
            'start'         => $this->_model->start);
        $this->f3->set('tmp.setting.default_value', $default_value);

        return 'app/module/setting/view/index.php';
    }

    /**
     * table - Псеводним index()
     *
     * @access public
     *
     * @return string HTML.
     */
    public function table() {
        return $this->index();
    }

    /**
     * aside_list_tables - Блок навигации по таблицам в БД.
     *
     * @access private
     *
     * @return array Список таблиц готовый для парсинга.
     */
    private function aside_list_tables() {
        $tables = $this->_model->list_tables();
        $list_tables = array();
        foreach ($tables as $table) {
            // Определение активных и заблокированных ссылок
            $active = $disabled = FALSE;
            if ($this->_id == $table['table_name']) {
                $active = TRUE;
            }
            if (in_array($table['table_name'], $this->_model->exception_table)) {
                $disabled = TRUE;
            }
            $list_tables[] = array(
                'active'    => $active,
                'disabled'  => $disabled,
                'link'      => get_link("admin/setting/table/" . $table['table_name'], $this->f3->get("lang.setting.table." . $table['table_name']))
            );
        }

        return $list_tables;
    }

    //--------------------------------------ajax-side---------------------------------------------//

    /**
     * refresh_table - Обновляет данные у клиента в таблице - Функция строит таблицу представления данных.
     *
     * @access public
     *
     * @return array JSON набора данных
     */
    public function get_table() {
        // Определение заголовков
        $fields = array();
        $fk = $this->_model->get_foreign_keys($this->_id);
        foreach ($this->table_schema as $key => $value) {
            $fields[] = array(
                'name'  => $key,
                'lang'  => $this->f3->get("lang.setting.field." . $this->_id . "." . $key),
                'type'  => $value['type'],
                'def'   => $value['default'],
                'empty' => $value['nullable'],
                'fk'    => isset($fk[$key]) ? $fk[$key] : false
            );

            // $fields[$key] = $key;
        }

        //Запрашиваем содержимое таблицы
        $records = $this->_model->get_records($this->_id);

        return array(
            'fields'    => $fields,
            'records'   => $records,
            'count'     => $this->_model->get_count($this->_id),
            'show_id'   => $this->show_id
        );
    }

    /**
     * get_record - Вохвращает данные для обновления выбранной записи.
     *
     * @access public
     *
     * @return array JSON записи.
     */
    public function get_record() {
        //Проверка на передачу id записи
        $id = $this->f3->get('POST.id');
        $record = $this->_model->get_record($this->_id, $id);
        if ($record) {
            return array('record' => $record);
        }
        return array('error' => TRUE);
    }

    /**
     * set_record - Вставка/Обновление данных.
     *
     * @access public
     *
     * @return array Value.
     */
    public function set_record() {
        $table = $this->f3->get('POST.table');
        $record = $this->f3->get('POST.record');

        if ($this->_model->save_record($table, $record)) {
            return array();
        }
        return array('error' => TRUE);
    }

    /**
     * delete_record - Удаляет запись.
     *
     * @access public
     *
     * @return array Value.
     */
    public function delete_record() {
        $table = $this->f3->get('POST.table');
        $id = $this->f3->get('POST.id');

        if ($this->_model->delete_record($table, $id)) {
            return array();
        }
        return array('error' => TRUE);
    }

    /**
     * opt_dataset - Хеш набора данных.
     *
     * @access public
     *
     * @return array Value.
     */
    public function hash_table() {
        return array('hash' => md5(json_encode($this->get_table())));
    }
}
