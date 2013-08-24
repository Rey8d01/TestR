<?php

if (!defined('tr')) exit('No direct script access allowed');

/**
* navigate_main
*
* @uses     main
*
* @category Category
* @package  Package
* @author    <>
* @license
* @link
*/
class navigate_main extends main {

    public function __construct() {
        parent::__construct();
        $this->_model = new \navigate_model;
    }

    /**
     * index - Создание топового меню.
     *
     * @access public
     *
     * @return array Набор параметров для меню.
     */
    public function top_menu() {
        $list_item = $this->_model->get_list_recursion(0);

        $menu = array();
        foreach ($list_item ? : array() as $item) {
            $uri = $this->generate_uri($item);
            $class = $this->generate_active($uri);

            // Генерация выпадающего меню
            $dropdown = FALSE;
            if (isset($item['inner'])) {
                $dropdown = array();
                foreach ($item['inner'] as $sub_item) {
                    $sub_uri = $sub_class = '';
                    // Валидация для стилизация типов элементов выпадающего меню
                    switch ($sub_item['type']) {
                        case 'divider':
                            $sub_class = 'divider';
                            $sub_item['title'] = '';
                            $link = '';
                            unset($sub_item['inner']);
                            break;
                        case 'nav-header':
                            $sub_class = 'nav-header';
                            $link = $sub_item['title'];
                            unset($sub_item['inner']);
                            break;
                        default:
                            $sub_uri = $this->generate_uri($sub_item);
                            $sub_class = $this->generate_active($sub_uri);
                            $link = get_link($sub_uri, $sub_item['title']);
                            break;
                    }

                    array_push($dropdown, array(
                        'class'     => $sub_class,
                        'link'      => $link
                    ));

                    // Если элемент в подменю активен то родительский элемент тоже будет активен
                    if ($sub_class == 'active') {
                        $class = 'active';
                    }
                }
            }

            array_push($menu, array(
                'class'     => $class,
                'href'      => $item['type'] == 'dropdown' ? "#" : $this->f3->get('init.sys.url') . $uri,
                'title'     => $item['title'],
                'dropdown'  => $dropdown
            ));
        }

        $this->f3->set('tmp.navigate', $menu);
    }

    /**
     * generate_uri - Определить ссылку.
     *
     * @param array uri.
     *
     * @access public
     *
     * @return string uri.
     */
    private function generate_uri(array $item) {
        $uri = '';
        // Исключаем названия главных контроллеров
        if (($item['__module'] != 'main') && ($item['__module'] != 'admin')) {
            $uri .= "main/" . $item['__module'];
            if ($item['__function']) {
                $uri .=  "/" . $item['__function'];
            }
            if ($item['__id']) {
                $uri .=  "/" . $item['__id'];
            }
        } else {
            $uri .= $item['__module'] . "/";
        }

        return $uri;
    }

    /**
     * generate_active - Определить активную ссылку и задать для нее класс.
     *
     * @param string $uri uri.
     *
     * @access private
     *
     * @return string 'active'|''.
     */
    private function generate_active($uri) {
        $class = '';

        $segments = '';
        foreach ($this->_segments as $segment) {
            if ($segment) {
                $segments .= $segment . "/";
            }
        }

        if ($uri . "/" == $segments) {
            $class = "active";
        }

        return $class;
    }

}
