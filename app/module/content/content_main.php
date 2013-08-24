<?php

if (!defined('tr')) exit('No direct script access allowed');

/**
* content_main - Класс для отображения текстовых материалов
*
* @uses     main
*
* @category Category
* @package  Package
* @author    <Rey>
* @license
* @link
*/
class content_main extends main {

    public function __construct() {
        parent::__construct();
        $this->_model = new \content_model;
    }

    /**
     * index - Возвращает список всех последних материалов.
     *
     * @access public
     *
     * @return string Адрес шаблона.
     */
    public function index() {
        $this->f3->set('category.inner_contents', TRUE);
        return $this->get_list_content(0);
    }

    /**
     * get - Вовзращает запрошенный текстовый материал и функции для его комментирования
     *
     * @param int $id Идентификатор материала.
     *
     * @access public
     *
     * @return string Адрес шаблона.
     */
    public function get($id = NULL) {
        if (is_null($id)) {
            $id = (int)$this->_id;
        }
        if (!is_int($id)) {
            $this->error_page(404);
        }

        //Запрос материала
        $content = $this->_model->get_content($id);

        //Ошибка если материал не найден
        if ($content == FALSE) {
            $this->error_page(404);
        }

        $user = new \user_main;
        $category = new \category_main;

        // Формирование даты материала
        $created = date_parse($content['created']);
        $date = date($this->f3->get('init.date_format'),
            mktime($created['hour'], $created['minute'], $created['second'],
                $created['month'], $created['day'], $created['year']));

        // Фотогалерея
        $photos = json_decode($content['photos'], TRUE);

        // Удаление разрыва страницы для полной статьи
        $break = mb_strpos($content['desc'], '@pagebreak', 0, 'utf-8');
        if ($break === FALSE) {
            $desc = $content['desc'];
        } else {
            $desc = mb_substr($content['desc'], 0, $break, 'utf-8') . mb_substr($content['desc'], $break + mb_strlen('@pagebreak', 'utf-8'), mb_strlen($content['desc'], 'utf-8'), 'utf-8');
        }

        // Комментарии к материалу
        $list_comment = array();
        foreach ($this->_model->get_list_comment($id) as $comment) {
            $list_comment[] = array(
                'user'      => $user->get_user_link($comment['id_user']),
                'avatar'    => $user->get_url_avatar($comment['id_user'], 'navbar'),
                'created'   => $comment['created'],
                'text'      => $comment['message']
            );
        }

        $this->f3->set('tmp.content.id',            $id);
        $this->f3->set('tmp.content.title',         $content['title']);
        $this->f3->set('tmp.content.date',          $date);
        $this->f3->set('tmp.content.path',          $category->get_path($content['id_category'], FALSE));
        $this->f3->set('tmp.content.desc',          $desc);
        $this->f3->set('tmp.content.category',      $category->get_category_link($content['id_category']));
        $this->f3->set('tmp.content.user',          $user->get_user_link($content['id_user']));
        $this->f3->set('tmp.content.view',          $content['view']);
        $this->f3->set('tmp.content.photos',        $photos);
        $this->f3->set('tmp.content.comment',       $this->_model->get_count_comments($content['id']));
        $this->f3->set('tmp.content.form_comment',  $this->get_access('content'));
        $this->f3->set('tmp.content.list_comment',  $list_comment);

        return 'app/module/content/view/content.php';
    }

    /**
     * get_hero - Загрузка информации для блока hero
     *
     * @param int $id Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get_hero($id = 0) {
        $content = $this->_model->get_content((int)$id);
        if ($content == FALSE) {
            return;
        }
        $this->f3->set('tmp.hero', 'app/module/content/view/hero.php');
        $this->f3->set('tmp.content.hero.title',    $content['title']);
        $this->f3->set('tmp.content.hero.desc',     $content['desc']);
    }

    /**
     * get_list_content - Возвращает список материлов в категории.
     *
     * @param int $id_category Идентификатор категории.
     * @param int $start       Номер страницы перечисления материалов в данной категории.
     *
     * @access public
     *
     * @return string Адрес шаблона.
     */
    public function get_list_content($id_category = 0, $start = 1) {
        // Функция принимает управление из других функций, которые принимают данные от пользователей
        // Предполагаем принятие валидных данных - ограничиваемся простой проверкой
        if (!is_int($id_category) || !is_int($start)) {
            $this->error_page(500);
        }

        $user = new \user_main;
        $category = new \category_main;

        $list_category = $id_category;
        if ($this->f3->get('category.inner_contents') || ($id_category == 0)) {
            $list_inner_category = $category->get_flat_list_inner_category($id_category);
            if ($list_inner_category) {
                $list_inner_category[] = $id_category;
                $list_category = implode(',', $list_inner_category);
            }
        }

        // Послылаем запрос на все материалы относящиеся к указанной(-ым) категории(-ей)
        $contents = $this->_model->get_list_content($list_category, $start, $this->f3->get('category.limit_contents'));
        $count = $this->_model->get_count_content($list_category);

        $result = array();
        foreach ($contents ? : array() as $content) {
            // Формирование даты материала
            $created = date_parse($content['created']);
            $date = date($this->f3->get('init.date_format'),
                mktime($created['hour'], $created['minute'], $created['second'],
                    $created['month'], $created['day'], $created['year']));

            // Определение разрыва страницы для превью
            $break = mb_strpos($content['desc'], '@pagebreak', 0, 'utf-8');
            if ($break === FALSE) {
                $desc = "<p>" . character_limiter($content['desc'], $this->f3->get('category.limit_intro')) . "</p>";
            } else {
                $desc = mb_substr($content['desc'], 0, $break, 'utf-8');
            }

            $result[] = array(
                'title'         => get_link("main/content/get/" . $content['id'], $content['title']),
                'date'          => $date,
                'desc'          => $desc,
                'url'           => $this->f3->get("init.sys.url") . "main/content/get/" . $content['id'],
                'category'      => $category->get_category_link($content['id_category']),
                'user'          => $user->get_user_link($content['id_user']),
                'view'          => $content['view'],
                'comment'       => $this->_model->get_count_comments($content['id'])
            );
        }

        // Генерация навигации по страницам
        $this->f3->get('category.limit_contents');
        $tail = $count % $this->f3->get('category.limit_contents');

        $count_pages = 0;
        if ($tail > 0) {
            $count_pages = floor($count / $this->f3->get('category.limit_contents')) + 1;
        } else {
            $count_pages = $count / $this->f3->get('category.limit_contents');
        }

        $current_page = 0;
        $pages = array();
        if ($count_pages > 1) {
            for ($i = 0; $i <= $count_pages -1; $i++) {
                $page = $i * $this->f3->get('category.limit_contents') + 1;
                $num = $i + 1;
                $class = '';
                $current_page += $this->f3->get('category.limit_contents');
                if (($current_page <= $start) && ($start < ($current_page + $this->f3->get('category.limit_contents')))) {
                    $class = 'active';
                }

                $pages[] = array(
                    'page'  => $page,
                    'num'   => $num,
                    'class' => $class
                );
            };
        }


        $this->f3->set('tmp.content.list',  $result);
        $this->f3->set('tmp.category.id',   $id_category);
        $this->f3->set('tmp.content.pages', $pages);

        return 'app/module/content/view/list_content.php';
    }

    /**
     * add_comment - Добавление комментария
     *
     * @access public
     *
     * @return bool Результат добавления.
     */
    public function add_comment() {
        //Проверка прав комментирования
        if (!$this->get_access('content')) {
            return FALSE;
        }

        $id = (int)$this->f3->get('POST.id');
        $message = $this->f3->get('POST.message');

        return $this->_model->add_comment($id, $message);
    }
}
