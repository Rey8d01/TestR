<?php

if (!defined('tr')) exit('No direct script access allowed');

class setting_model extends model {

    //Настройки сортировки
    /**
     * @var string Название поля по которому будет проведена операция сортировки
     */
    public $order_field = 'id';
    /**
     * @var string Опция - сортировка по возростанию (ASC) [''] или убыванию ['DESC']
     */
    public $order_by = '';

    //Настройки поиска
    /**
     * @var string Название поля по которому будет проведена операция поиска
     */
    public $like_field = '';
    /**
     * @var string Строка искомых данных
     */
    public $like_data = '';

    //Настройки фильтрации
    /**
     * @var array Массив строк с названиями полей по которым будет проведена операция фильтрации
     */
    public $filter_field = '';
    /**
     * @var array Массив строк с операциями фильтрации
     */
    public $filter = '';
    /**
     * @var array Массив строк с фильтруемым тестом
     */
    public $filter_data = '';
    /**
     * @var array Массив строк с таблицами исключениями
     */
    public $exception_table  = array('online', 'data_profile');
    /**
     * Количество строк которые пользователь увидит на странице
     *
     * @var int Количество строк
     */
    public $limit = 20;
    /**
     * Старотовая попозиция показа элементов
     *
     * @var int Номер строки
     */
    public $start = 1;

	public function __construct() {
        parent::__construct();
	}

    //---------------------------------------data-function------------------------------------------

    /**
     * list_tables - Возвращает список таблиц
     *
     * @access public
     *
     * @return array Value.
     */
    public function list_tables() {
        return $this->_db->exec("SELECT `table_name` FROM `information_schema`.`tables` WHERE `table_schema` = ?;", $this->_db->name());
    }

    /**
     * table_exists - Проверяет существование таблицы
     *
     * @param string $name Название таблицы.
     *
     * @access public
     *
     * @return bool Результат проверки существования таблицы.
     */
    public function table_exists($name = '') {
        $tables = $this->list_tables();

        foreach ($tables as $table) {
            if ($table['table_name'] === $name) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * select_field_data - Получение данных полей таблицы
     *
     * @param string $table Название таблицы из которой будет получен набор данных.
     *
     * @access public
     *
     * @return array Map с данными полей указанной таблицы и их свойствами в ней.
     */
    public function table_schema($table) {
        return $this->_db->schema($table);
    }

    /**
     * get_records - Получение всех записей таблицы
     *
     * @param mixed $table Название таблицы из которой будет получен набор данных.
     *
     * @access public
     *
     * @return mixed Map состоящий из записей (с применением параметров).
     */
    public function get_records($table) {
        $this->_table = new DB\SQL\Mapper($this->_db, $table);

        // Сортировка - По умолчанию сортировка если не указана то ASC - в oci неадекватная обработка ASC
        $order = $this->order_field . " " . $this->order_by;
        // $this->db->order_by($this->order_field, $this->order_by);

        // Генерация WHERE части запроса
        $where = $this->set_where();

        $this->_table->load($where, array(
            'order' => $order,
            'limit' => $this->limit,
            'offset' => $this->start -1
        ));

        $records = array();
        while (!$this->_table->dry()) {

            $row = $this->_table->cast();
            $record = array();
            foreach ($row as $value) {
                $value = htmlspecialchars($value);
                //Обрезаем строку значения если она больше 51 символов и ставим '...'
                $val = mb_strlen($value, 'utf-8') > 20 ? mb_substr($value, 0, 20, 'utf-8') . "..." : $value;
                $record[] = $val;
            }

            $records[] = $record;

            $this->_table->skip();
        }

        return $records;
    }

    /**
     * get_count - Получение количества запрошенных данных
     *
     * @param mixed $table Название таблицы из которой будет получен набор данных.
     *
     * @access public
     *
     * @return mixed Количество строк.
     */
    public function get_count($table) {
        $where = $this->set_where();

        return $this->_table->count($where);
    }

    /**
     * set_where - Формирует WHERE часть для передачи в качестве аргумента функции f3 формирующей запрос к БД
     *
     * @access public
     *
     * @return string|array Массив параметров для запроса.
     */
    public function set_where() {
        $left = array();
        $right = array();
        // Поиск опредленных данных при необходимости
        if (($this->like_field != '') && ($this->like_data != '')) {
            $left[] = $this->like_field . " LIKE ?";
            $right[] = "%" . $this->like_data . "%";
            // $this->db->like($this->like_field, $this->like_data, 'both');
        }

        // Фильтрация
        if (($this->filter_field != '') && ($this->filter != '') && ($this->filter_data != '')) {

            reset($this->filter_field);
            reset($this->filter);
            reset($this->filter_data);

            while (
            (list(, $filter_field) = each($this->filter_field))  &&
            (list(, $filter) = each($this->filter)) &&
            (list(, $filter_data) = each($this->filter_data))) {
                $left[] = $filter_field . " " . $filter . " ?";
                $right[] = $filter_data;
                // $this->db->where($filter_field." ".$filter, $filter_data);
            }
        }

        $where = '';
        $where_string = implode(' AND ', $left);
        if ($where_string) {
            $where = array($where_string);
            foreach ($right as $data) {
                $where[] = $data;
            }

        }

        return $where;
    }

    /**
     * get_foreign_keys - Получение внешних ключей - из представления формирующего их
     *
     * @param mixed $table Название таблицы для которой требуется получить внешние ключи.
     *
     * @access public
     *
     * @return array Map с внешними ключами и указателем на родительские таблицы.
     */
    public function get_foreign_keys($table) {
        //CREATE VIEW `v_foreign_key` AS
        //select
        //  `KEY_COLUMN_USAGE`.`TABLE_NAME` AS `child_table`,
        //  `KEY_COLUMN_USAGE`.`COLUMN_NAME` AS `child_field`,
        //  `KEY_COLUMN_USAGE`.`REFERENCED_TABLE_NAME` AS `parent_table`,
        //  `KEY_COLUMN_USAGE`.`REFERENCED_COLUMN_NAME` AS `parent_field`
        //from `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
        //where (
        //  (`KEY_COLUMN_USAGE`.`CONSTRAINT_SCHEMA` = 'ngp') and
        //  (`KEY_COLUMN_USAGE`.`REFERENCED_TABLE_NAME` <> '')
        //);database

        $fk = $this->_db->exec(
            "SELECT
                `KEY_COLUMN_USAGE`.`TABLE_NAME` AS `child_table`,
                `KEY_COLUMN_USAGE`.`COLUMN_NAME` AS `child_field`,
                `KEY_COLUMN_USAGE`.`REFERENCED_TABLE_NAME` AS `parent_table`,
                `KEY_COLUMN_USAGE`.`REFERENCED_COLUMN_NAME` AS `parent_field`
            FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
            WHERE
                `KEY_COLUMN_USAGE`.`CONSTRAINT_SCHEMA` = '".$this->_db->name()."' AND
                `KEY_COLUMN_USAGE`.`REFERENCED_TABLE_NAME` <> '' AND
                `KEY_COLUMN_USAGE`.`TABLE_NAME` = '".$table."'");

        $result = array();
        foreach ($fk as $key => $value) {
            $result[$value['child_field']] = $value['parent_table'];
            # code...
        }
        // $fk = $this->_db->exec("SELECT `KEY_COLUMN_USAGE`.`TABLE_NAME` AS `child_table`, `KEY_COLUMN_USAGE`.`COLUMN_NAME` AS `child_field`, `KEY_COLUMN_USAGE`.`REFERENCED_TABLE_NAME` AS `parent_table`, `KEY_COLUMN_USAGE`.`REFERENCED_COLUMN_NAME` AS `parent_field` FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` WHERE  `KEY_COLUMN_USAGE`.`CONSTRAINT_SCHEMA` = ? AND  `KEY_COLUMN_USAGE`.`REFERENCED_TABLE_NAME` <> '' AND `KEY_COLUMN_USAGE`.`TABLE_NAME` = ?", $this->_db->name(), $table);

        return $result;
    }

    /**
     * get_record - Выбор значений поля для последующего редактирования
     *
     * @param mixed $table Название таблицы.
     * @param mixed $id    Номер записи которую необходимо отредактировать.
     *
     * @access public
     *
     * @return array|bool Map со значениями этой записи.
     */
    public function get_record($table, $id) {
        $this->_table = new DB\SQL\Mapper($this->_db, $table);
        $this->_table->load(array("id = ?", $id));
        return $this->_table->dry() ? FALSE : $this->_table->cast();
    }

    /**
     * save_record - Вставка/Обновление данных в таблице
     *
     * @param mixed $table  Название таблицы в которую будет вставлена новая запись.
     * @param mixed $record Map с данными на вставку.
     *
     * @access public
     *
     * @return bool Результат операции.
     */
    public function save_record($table, $record) {
        $this->_table = new DB\SQL\Mapper($this->_db, $table);

        foreach ($record as $key => $value) {
            if (($key == 'id') && $value) {
                // Если имеется ключевое поле id то знаит запись будет одновлена,
                // для этого требуется выполнить предварительный select запрос к этой записи
                $this->_table->load(array("id = ?", $value));
                continue;
            }
            $this->_table->$key = $value;
        }

        return $this->_table->save() === FALSE ? FALSE : TRUE;
    }

    /**
     * delete_date - Удаление данных.
     *
     * @param mixed $table Название таблицы из которой будет удалена запись.
     * @param mixed $id    Номер записи которую необходимо удалить.
     *
     * @access public
     *
     * @return bool Результат операции.
     */
    public function delete_record($table, $id) {
        $this->_table = new DB\SQL\Mapper($this->_db, $table);
        return $this->_table->erase(array("id = ?", $id));
    }
}
