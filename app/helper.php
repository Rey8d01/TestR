<?php

if (!defined('tr')) exit('No direct script access allowed');

/**
 * Преобразование набора данных в ORM'е в виде массива или объекта
 *
 * @param object $table Объект таблтицы после запроса - набор данных
 * @param bool $obj Параметр указывает вернуть данные в виде объекта или map'a
 * @return array Набор данных в виде массива map'ов или объектов
 */
function orm_transfer($table, $obj = FALSE) {
    // Поля таблицы
    $schema = array_keys($table->schema());

    // Для объекта будем строить отдельный класс
    if ($obj) {
        eval("class transfer {};");
    }

    $result = array();
    $i = 0;
    while (!$table->dry()) {
        $result[$i] = $obj ? new transfer : array();

        reset($schema);
        if ($obj) {
            foreach ($schema as $field) {
                $result[$i]->$field = $table->$field;
            }
        }
        $result[$i] = $table->cast();

        $table->skip();
        $i++;
    }
    return $result;
}

function get_link($link, $title) {
    $f3 = Base::instance();
    return "<a title='" . $title . "' href='" . $f3->get('init.sys.url') . $link . "'>" . $title . "</a>";
}


// из CI2
/**
 * Character Limiter
 *
 * Limits the string based on the character count.  Preserves complete words
 * so the character count may not be exactly as specified.
 *
 * @access	public
 * @param	string
 * @param	integer
 * @param	string	the end character. Usually an ellipsis
 * @return	string
 */
	function character_limiter($str, $n = 500, $end_char = '&#8230;')
	{
		if (strlen($str) < $n)
		{
			return $str;
		}

		$str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));

		if (strlen($str) <= $n)
		{
			return $str;
		}

		$out = "";
		foreach (explode(' ', trim($str)) as $val)
		{
			$out .= $val.' ';

			if (strlen($out) >= $n)
			{
				$out = trim($out);
				return (strlen($out) == strlen($str)) ? $out : $out.$end_char;
			}
		}
	}