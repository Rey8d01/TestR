<script language="JavaScript" type="text/javascript">

// Объявление пременных

// Активная таблица
var setting_table = "{{ @tmp.setting.table }}";

// Cелекты для опций
var select_field = "";

//Количество условий фильтра - изначально 1 условие
var condition = 0;

// Параметр определяющий загрузку всех полей для изменения при загрузке страницы - при последующих обновлениях они загружатся не будут.
var schema_load = false;

// Коллекция wysihtml5 для каждого textarea поля.
// Что бы изменить запись нужно вставить текущее значение в это поле, но стандантрными методами этого не добится, поэтому
// нужно иметь в доступе каждый wysihtml5 редактор как объект
var wysi = {};

//--------------------------------------------------------------------------------------------------

// console.log(records[record][0]);
/*
В jQ обратная функция срабатывает для каждого селектора, т.е. Для фейда каждого элемента в коллекции будет вызвана функция,
при этом если элементов в коллекции более одного функция сработает ранее чем будет завершен эффект.
Поэтому для плавности надо было организовать рекурсивный обход коллекции и по очереди вызывать фейд каждого элемента,
в таком случае каллбак будет вызван только после его отработки.
*/
function qfade(collection, i) {
    if (i == undefined) {
        qfade($(".emerge, .main_table, #setting_error"), 0);
    }

    if ($(collection).length <= i) {
        get_table();
        return true;
    }

    $($(collection)[i]).fadeOut("fast", function() {
        qfade($(collection), ++i);
    });
}

// Функция конструирует поля для редатирования и вставки записей
function set_field(field) {
    var name = "name='" + field.name + "'";
    var id = "id='field_" + field.name + "'";
    var type = field.type.match(/\w*/);
    var length = field.type.match(/\d+/g) == null ? "" : "maxlength='" + field.type.match(/\d+/g)[0] + "'";
    var value = "";
    var placeholder = "placeholder='" + (field.def || (field.empty ? "..." : '')) + "'";
    var foreign_table = field.fk;

    // Если поле - является внешним ключем на другую таблицу
    if (foreign_table != false) {
        return "<div class='input-append'><input type='number' " + id + " " + name + " " + length + " size='20'>" +
"<span class='add-on' table='" + foreign_table + "' field='" + field.name + "' id='foreign_key'>Выбрать</span></div>";
    }

    //1 = tinyint, 3 = int, 4 = float
    if ((type == 'int') || (type == 'tinyint') || (type == 'float')) {
        //Отключаем для редактирования поле id - его редактирование недопустимо
        var disabled = '';
        if (field.name == 'id') {
            disabled = "disabled='disabled'";
        }

        return "<input " + disabled + " type='number' " + id + " " + name + " " + length + " " + placeholder + " size='20'>";
    }

    //253 = varchar
    if (type == 'varchar') {
        return "<input type='text' " + id + " " + name + " " + length + " size='20'>";
    }

    //7 = timestamp, 12 = datetime, 10 = date
    if ((type == 'timestamp') || (type == 'datetime') || (type == 'date')) {
        return "<input type='text' title='ДД.ММ.ГГГГ ЧЧ:ММ:СС' " + id + " " + name + " " + length + " " + placeholder + " size='20'>";
        // 'onmousedown'   => "showdate(this.id);",
    }

    //252 = text
    if ((type == 'text') || (type == 'mediumtext')) {
        return "<textarea " + id + " " + name + " class='textarea'></textarea>";
    }

    return "Unknown type - " . type;
}

// Построение таблицы из данных в json и страниц навигации
(function ($) {
    $.fn.json_table = function(settings) {
        var fields  = settings.data.fields;
        var records = settings.data.records;
        var count   = Number(settings.data.count);
        var control = settings.data.control;

        // Если она пустая то нет смысла строить таблицу в браузере
        if (count == 0) {
            $(this).html("<h4>Таблица пустая</h4>");
            return;
        }

        // Определим опции для показа элементов контроля
        var control_th, control_td = '';
        var id_table = 'sub-control';
        if (control) {
            control_th = '<th></th>';
            id_table = 'control';
            control_td = "<td><button option='update' title='Обновить' class='btn'><span class='icon-pencil'></span></button> " +
"<button option='delete' title='Удалить' class='btn'><span class='icon-remove'></span></button></td>";
        }

        // Создадим таблицу внутри переданного селектора
        $(this).html("<table id='" + id_table + "' class='" + settings.class_table + "'><thead></thead><tbody></tbody></table>");

        // Установка заголовков
        $(this.selector + ' thead').append("<tr>" + control_th + "</tr>\n");
        for (var i in fields) {
            var field = fields[i];
            // Установка сортировки на полях таблицы
            var sort = '';

            if (control) {
                //----------------------------------------------------------------------------------
                if (!schema_load) {
                    // Генерация полей для формы внесения изменений
                    $("#form_write > hr").before(
    "<div class='control-group'>" +
    "    <label class='control-label' for='field_" + field.name + "'>" + field.lang + "</label>" +
    "    <div class='controls'>" + set_field(field) + "</div>" +
    "</div>");

                    // Собираем коллекцию wysihtml5 редакторов
                    if ((field.type == 'text') || (field.type == 'mediumtext')) {
                        wysi[field.name] = $("textarea[name='" + field.name + "']").wysihtml5().data("wysihtml5").editor;
                    }

                    // Генерация селектов для опций
                    select_field += "<option value='" + field.name + "'>" + field.lang + "</option>";
                }
                //----------------------------------------------------------------------------------

                if (field.name == $("#setting_table_options > input[name='order_field']").val()) {
                    if ($("#setting_table_options > input[name='order_by']").val() == '') {
                        sort = "<span title='По возрастанию' class='icon-chevron-down'></span>";
                    } else {
                        sort = "<span title='По убыванию' class='icon-chevron-up'></span>";
                    }
                }
            }

            $(this.selector + ' tr').append("<th field='" + field.name + "'>" + field.lang + sort + "</th>\n");
        };

        if (control && !schema_load) {
            $("#form_search select[name='like_field']").empty().append(select_field);
            // wysihtml5 для textarea в форме изменения записей
            // $('.textarea').wysihtml5();
        }

        // Занесение строк и ячеек
        for (var record in records) {
            var row = "";

            row += "<tr record='" + records[record][0] + "'>" + control_td;

            for (var value in records[record]) {
                row += "<td>" + records[record][value] + "</td>";
            };

            row += "</tr>";
            $(this.selector + ' > table > tbody:last').append(row);

            schema_load = true;
        };

        //------------------------------------------------------------------------------------------

        // Создание кнопок постраничной навигации

        var start = {{ @tmp.setting.default_value.start }};
        var limit = {{ @tmp.setting.default_value.limit }};
        if (control) {
            start = Number($("#setting_table_options > input[name='start']").val());
            limit = Number($("#setting_table_options > input[name='limit']").val());
        }
        var tail = (count % limit);

        // Определяем количество страниц
        var count_pages = 0;
        if (tail > 0) {
            count_pages = Math.floor(count / limit) + 1;
        } else {
            count_pages = count / limit;
        }

        var current_page = 0;
        // Страницы будут оформлятся если их больше 1
        if (count_pages > 1) {
            // Оформление блока навигации
            $(this.selector).append("<div class='pagination'></div>");
            // Генерируем кнопки навигации
            for (var i = 0; i <= count_pages -1; i++) {
                if ((current_page <= start) && (start < (current_page + limit))) {
                    // Магия с умножением и плюсами нужна для корректной работы.
                    // Пример: для первой страницы 0*20+1=1 позиция будет начинатся с первой записи, для второй - 1*20+1 = 21
                    $(this.selector + " .pagination").append("<button page='" + (i * limit + 1) + "' class='btn btn-info'>" + (i + 1) + "</button> ");
                } else {
                    $(this.selector + " .pagination").append("<button page='" + (i * limit + 1) + "' class='btn'>" + (i + 1) + "</button> ");
                }
                current_page += Number(limit);
            };
        }
        $(this.selector).append("<p>Показано " + records.length + " записей, начиная с " + start + " (Всего записей: " + count + ")</p>");
    };
}(jQuery));

//     //Показ календаря для выбранных полей
//     function showdate(id) {
//        $('#'+id).datepicker();
//     }

//--------------------------------------------------------------------------------------------------

$(function() {
    // Загрузка таблицы после загрузки всей страницы и скриптов
    get_table();
});

//--------------------------------------------------------------------------------------------------

// Запрос на новые данные таблицы с учетом поиска, сортировки, фильтра
function get_table() {
    if (setting_table == '') {
        $("#setting_error").attr("class", "text-center alert alert-info").text("Это упрощенный интерфейс для доступа к базе данных. Для начала работы выберите таблицу из списка в левом меню.").fadeIn('fast');
        return;
    }

    $('.image_loading').fadeIn('fast');
    $.post("{{ @init.sys.url }}admin/setting/get_table", {
'table'         : setting_table,
'order_field'   : $("#setting_table_options > input[name='order_field']").val(),
'order_by'      : $("#setting_table_options > input[name='order_by']").val(),
'like_field'    : $("#setting_table_options > input[name='like_field']").val(),
'like_data'     : $("#setting_table_options > input[name='like_data']").val(),
'filter_field'  : $("#setting_table_options > input[name='filter_field']").val(),
'filter'        : $("#setting_table_options > input[name='filter']").val(),
'filter_data'   : $("#setting_table_options > input[name='filter_data']").val(),
'start'         : $("#setting_table_options > input[name='start']").val(),
'limit'         : $("#setting_table_options > input[name='limit']").val()
    }, function(data) {
        if (data.error != undefined) {
            $(".image_loading").fadeOut('fast', function() {
                $("#setting_error").attr("class", "text-center alert alert-error").text("Операции над этой таблицей с помощью данной функции запрещены.").fadeIn('fast');
            });
            return;
        }

        // Опция показа опций управления данными
        data.control = true;

        $(".main_table").json_table({
            data : data,
            class_table : 'table table-bordered table-hover'
        });

        $(".image_loading").fadeOut('fast', function() {
            $(".main_table").fadeIn('fast');
        });
    }, "json").fail(function() {
        $("#setting_error").attr("class", "text-center alert alert-error").text("Ошибка при обработке принятых данных.").fadeIn('fast');
    });

//         $("#att_db").remove();
//         nexus_stop();
//         nexus_one();

}

//--------------------------------------------------------------------------------------------------

// Навигация по страницам
$(document).on('click', "#setting_table .pagination button", function() {
    $("#setting_table_options > input[name='start']").val($(this).attr('page'));
    qfade();
});

//--------------------------------------------------------------------------------------------------

// Установка сортировки
$(document).on('click', "#setting_table #control th[field]", function() {
    if ($("#setting_table_options > input[name='order_field']").val() == $(this).attr('field')) {
        if ($("#setting_table_options > input[name='order_by']").val() == '') {
            $("#setting_table_options > input[name='order_by']").val('DESC');
        } else {
            $("#setting_table_options > input[name='order_by']").val('');
        }
    } else {
        $("#setting_table_options > input[name='order_field']").val($(this).attr('field'));
        $("#setting_table_options > input[name='order_by']").val('');
    }
    qfade();
});

//--------------------------------------------------------------------------------------------------

// Открытие формы применения различных опций
$(document).on('click', "button[option]", function() {
    // option = insert|search|filter|limit| update|delete
    var option = $(this).attr('option');
    var id = 0;
    var form_selector = '';

    switch (option) {
        case 'update':
            // Выводит форму для записи в таблицу
            id = $($(this).parents('tr')[0]).attr('record');
            form_selector = "#form_write";
            $("#form_write [name][type!='hidden']").val('');

            // $('.main_table').fadeOut('fast', function() {
                // $('.image_loading').fadeIn('fast');

                $.post("{{ @init.sys.url }}admin/setting/get_record", {
'table' : setting_table,
'id'    : id,
                }, function(data) {
                    if (data.error != undefined) {
                        $("#setting_error").attr("class", "text-center alert alert-error").text("При загрузке данных произошла ошибка, вероятно что такой записи уже нет.").fadeIn('fast');
                    } else {
                        $.each(data.record, function(key, value) {
                            // Финт ушами для wysihtml5 - очищаем поля и вставляем данные для изменений
                            if (wysi[key] != undefined) {
                                wysi[key].focus();
                                wysi[key].composer.clear();
                                wysi[key].composer.commands.exec("insertHTML", value);
                            } else {
                                $("#form_write [name='" + key + "']").val(value);
                            }
                        });
                    }

                    // $(".image_loading").fadeOut('fast');
                }, "json").fail(function() {
                    $("#setting_error").attr("class", "text-center alert alert-error").text("Ошибка при обработке принятых данных.").fadeIn('fast');
                });
            // });
            break;

        case 'insert':
            form_selector = "#form_write";
            $("#form_write [name][type!='hidden']").val('');

            for (w in wysi) {
                wysi[w].focus();
                wysi[w].composer.clear();
            };
            break;

        case 'delete':
            if (!confirm("Вы действительно хотите удалить запись?")) return;
            id = $($(this).parents('tr')[0]).attr('record');

            $('.main_table').fadeOut('fast', function() {
                $('.image_loading').fadeIn('fast');

                $.post("{{ @init.sys.url }}admin/setting/delete_record", {
'table' : setting_table,
'id'    : id,
                }, function(data) {
                    $(".image_loading").fadeOut('fast', function() {
                        if (data.error != undefined) {
                            $("#setting_error").attr("class", "text-center alert alert-error").text("При удалении записи произошла ошибка, проверьте есть ли связанные записи.").fadeIn('fast');
                        } else {
                            $("#setting_error").attr("class", "text-center alert alert-success").text("Запись успешно удалена.").fadeIn('fast');
                        }
                        setTimeout("qfade();",1350);
                    });
                }, "json").fail(function() {
                    $("#setting_error").attr("class", "text-center alert alert-error").text("Ошибка при обработке принятых данных.").fadeIn('fast');
                });
            });

            return;
            break;

        case 'search':
            // Показ формы для поиска в таблице по полям
            form_selector = "#form_search";

            //Функция проверяет - был ли применен поиск и если да то возвращает актуальный поисковый запрос для его необходимой коррекции
            // Вобщем... проверка будет не лишней.
            var like_field = $("#setting_table_options > input[name='like_field']").val();
            var like_data = $("#setting_table_options > input[name='like_data']").val();

            if ( (like_data != '') && (like_field != '') ) {
                $("#form_search select[name='like_field'] option[value='" + like_field + "']").attr("selected",true);
                $("#form_search input[name='like_data']").val(like_data);
            }
            break;

        case 'filter':
            // Показ формы установки фильтрации
            form_selector = "#form_filter";

            // Обнуляем значения
            $("#form_filter table tbody").html('');
            condition = 0;

            // Если поля фильтрации не были применены
            if ( ($("#setting_table_options > input[name='filter_field']").val() == '') &&
                 ($("#setting_table_options > input[name='filter']").val() == '') &&
                 ($("#setting_table_options > input[name='filter_data']").val() == '') ) {
                //Создается первое пустое поле для фильтрации
                add_filter();
            } else {
                //Если фильтр уже был применен - создаются соответствующие поля для их редактирования
                var M_FIELD = $("#setting_table_options > input[name='filter_field']").val().split(',');
                var M_FILTER = $("#setting_table_options > input[name='filter']").val().split(',');
                var M_FILTER_DATA = $("#setting_table_options > input[name='filter_data']").val().split(',');

                if ( (M_FIELD.length == M_FILTER.length) && (M_FIELD.length == M_FILTER_DATA.length) ) {
                    for (var i=0; i<M_FIELD.length; i++)  {
                        //Создание поля
                        add_filter();
                        //Восстановление значений
                        $("#form_filter select[name='filter_field_" + i + "'] option[value='" + M_FIELD[i] + "']").attr("selected", true);
                        $("#form_filter select[name='filter_" + i + "'] option[value='" + M_FILTER[i] + "']").attr("selected", true);
                        $("#form_filter input[name='filter_data_" + i + "']").attr("value", M_FILTER_DATA[i]);
                    }
                }
            }
            break;

        case 'limit':
            // Показ формы для установки лимита
            form_selector = "#form_limit";
            $("#form_limit input[name='start']").val($("#setting_table_options > input[name='start']").val());
            $("#form_limit input[name='limit']").val($("#setting_table_options > input[name='limit']").val());
            break;

        default:
            return;
            break;
    }

    // Очередная мутата с плавностью на случай если одна из опций открыта и пользователь захотел другую
    $(".main_table").fadeOut('fast', function() {
        if ($(".emerge[active='true']").length > 0) {
            $(".emerge[active='true']").attr('active', 'false').fadeOut('fast', function() {
                $(form_selector).attr('active', 'true').fadeIn('fast');
            });
        } else {
            $(form_selector).attr('active', 'true').fadeIn('fast');
        }
    });

// console.log();
// nexus_stop();

});

//--------------------------------------------------------------------------------------------------

// Внесение изменений в БД
$(document).on('click', "#form_write button[option='form_write_ok']", function() {
    // Собираем запись по полям формы записи
    var record = {};
    $("#form_write [name][type!='hidden']").each(function() {
        record[$(this).attr('name')] = $(this).val();
    });

    $("#form_write").fadeOut('fast', function() {
        $('.image_loading').fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/setting/set_record", {
'table'     : setting_table,
'record'    : record,
        }, function(data) {
            $(".image_loading").fadeOut('fast', function() {
                if (data.error != undefined) {
                    $("#setting_error").attr("class", "text-center alert alert-error").text("При внесении изменений произошла ошибка").fadeIn('fast');
                } else {
                    $("#setting_error").attr("class", "text-center alert alert-success").text("Изменение данных прошло успешно").fadeIn('fast');
                }
                setTimeout("qfade();",1350);
            });
        }, "json").fail(function() {
            $("#setting_error").attr("class", "text-center alert alert-error").text("Ошибка при обработке принятых данных.").fadeIn('fast');
        });
    });
});

//--------------------------------------------------------------------------------------------------

// Установка поиска
$(document).on('click', "#form_search button[option='form_search_ok']", function() {
    $("#setting_table_options > input[name='like_field']").val($("#form_search select[name='like_field']").val());
    $("#setting_table_options > input[name='like_data']").val($("#form_search input[name='like_data']").val());
    $("input[name='start']").val('{{ @tmp.setting.default_value.start }}');
    $("input[name='limit']").val('{{ @tmp.setting.default_value.limit }}');
    qfade();
});

//--------------------------------------------------------------------------------------------------

// Устновка фильтрации
$(document).on('click', "#form_filter button[option='form_filter_ok']", function() {
    // Объявление переменных
    var filter_field = "";
    var filter = "";
    var filter_data = "";
    // Массивы в которые помщается суммарная информация
    var M_FIELD = new Array;
    var M_FILTER = new Array;
    var M_FILTER_DATA = new Array;

    // Валидный счетчик
    var j = 0;
    for (var i = 0; i < condition; i++)  {
        // Проверка каждой строки фильтрации на наличие данных
        filter_field = $("#form_filter select[name='filter_field_" + i + "']").val();
        filter = $("#form_filter select[name='filter_" + i + "']").val();
        filter_data = $("#form_filter input[name='filter_data_" + i + "']").val();
        //Если некоторые поля в строке не заполнены - она отбрасывается
        if ( (filter_field != '') && (filter != '') && (filter_data != '') ) {
            M_FIELD[j] = filter_field;
            M_FILTER[j] = filter;
            M_FILTER_DATA[j] = filter_data;
            j++;
        }
    }

    $("#setting_table_options > input[name='filter_field']").val(M_FIELD);
    $("#setting_table_options > input[name='filter']").val(M_FILTER);
    $("#setting_table_options > input[name='filter_data']").val(M_FILTER_DATA);

    $("input[name='start']").val('{{ @tmp.setting.default_value.start }}');
    $("input[name='limit']").val('{{ @tmp.setting.default_value.limit }}');

    qfade();
});

// Вставка полей фильтрации
$(document).on('click', "#form_filter button[option='form_filter_add']", function() {
    add_filter();
});

function add_filter() {
    $("#form_filter table tbody").append(
"<tr>" +
"   <td><select name='filter_field_" + condition + "' size='1'>" + select_field + "</select></td>" +
"   <td><select name='filter_" + condition + "' size='1'>" +
"<option value='=' selected='selected'>=</option>" +
"<option value='<'><</option>" +
"<option value='<='>&LessSlantEqual;</option>" +
"<option value='>'>></option>" +
"<option value='>='>&GreaterSlantEqual;</option>" +
"<option value='<>'>&NotEqual;</option>" +
"   </select></td>" +
"   <td><input name='filter_data_" + condition + "' type='search'></td>" +
"</tr>");
    condition++;
}

//--------------------------------------------------------------------------------------------------

// Установка лимитов
$(document).on('click', "#form_limit button[option='form_limit_ok']", function() {
    $("#setting_table_options > input[name='start']").val($("#form_limit input[name='start']").val());
    $("#setting_table_options > input[name='limit']").val($("#form_limit input[name='limit']").val());
    qfade();
});

//--------------------------------------------------------------------------------------------------

// Обновление таблицы
$(document).on('click', "button[option='refresh']", function() {
    qfade();
});

//--------------------------------------------------------------------------------------------------

// Запрос на новые данные таблицы - возвращение изначального состояния таблицы
$(document).on('click', "#setting_control_panel_db button[option='clear']", function() {
    // Все невидимые поля будут очищены
    // Поскольку применяются в качестве параметров к запросу набора данных
    $("#setting_table_options > input[type='hidden']").val('');
    $("#setting_table_options > input[name='start']").val('{{ @tmp.setting.default_value.start }}');
    $("#setting_table_options > input[name='limit']").val('{{ @tmp.setting.default_value.limit }}');
    qfade();
});

//--------------------------------------------------------------------------------------------------

// Вызов диалогового окна для выбора внешних сущностей
$(document).on('click', '#form_write #foreign_key', function() {
    $("#setting_table_options > input[name='foreign_table']").val($(this).attr("table"));
    $("#setting_table_options > input[name='foreign_field']").val($(this).attr("field"));

    $("#dialog .modal-body, #dialog .modal-footer, #dialog #dialog_label").empty();
    $("#dialog #dialog_label").text('Выбрать запись');
    $("#dialog .modal-body.data").html('<div class="image_loading"></div><div id="foreign_table"></div>');
    $("#dialog").modal('show');

    foreign_form(1);
});

// Постраничная навигация для таблицы внешних сущностей
$(document).on('click', "#foreign_table .pagination button", function() {
    foreign_form($(this).attr('page'));
});

// Выбор внешней сущности
$(document).on('click', "#foreign_table table tr", function() {
    $("#form_write input[name='" + $("#setting_table_options > input[name='foreign_field']").val() + "']").val($(this).attr('record'));
    $("#dialog").modal('hide');
    $("#dialog .modal-body, #dialog .modal-footer, #dialog #dialog_label").empty();
});

// Загрузка данных в форму
function foreign_form(page) {
    $("#dialog .modal-body.data #foreign_table").fadeOut('fast', function() {
        $("#dialog .modal-body.data .image_loading").fadeIn('fast', function() {
            $.post("{{ @init.sys.url }}admin/setting/get_table", {
'table'         : $("#setting_table_options > input[name='foreign_table']").val(),
'start'         : page
            }, function(data) {
                data.control = false;

                $("#foreign_table").json_table({
                    data : data,
                    class_table : 'table table-bordered table-striped table-hover'
                });

                $("#dialog .modal-body.data .image_loading").fadeOut('fast', function() {
                    $("#foreign_table").fadeIn('fast');
                });
            }, "json").fail(function() {
                $("#setting_error").attr("class", "text-center alert alert-error").text("Ошибка при обработке принятых данных.").fadeIn('fast');
            });
        });
    });
}

//--------------------------------------------------------------------------------------------------

//     //Асинхронная связь с сервером
//     function nexus_one() {
//         var link = "{AJAX}opt_dataset";
//         $.post(link,{
// 'table'         : '{TABLE}'
//         },function(response){
//             $("input[name='hashing']").val(response);
//         });
//         nexus = setTimeout("nexus_s();",10000);
//     }

//     function nexus_s() {
//         var link = "{AJAX}opt_dataset";
//         $.post(link,{
// 'table'         : '{TABLE}'
//         },function(response) {
//             if ( $("input[name='hashing']").val() != response ) {
//                 $("#attention").append("<p id='att_db'>{ATTENTION}</p>");
//                 delete nexus;
//             } else {
//                 nexus = setTimeout("nexus_s();",10000);
//             }
//         });
//     }

//     function nexus_stop() {
//         if (typeof nexus != 'undefined') {
//             clearTimeout(nexus);
//             delete nexus;
//         }
//     }
</script>

<style type="text/css">
.main_table, .emerge, #foreign_table {
    display: none;
    margin-top: 1em;
}

/* Настройки для заголовков */
.main_table > table th span {
    margin-left: 0.5em;
}

.main_table > table th[field] {
    cursor: pointer;
    background-color: #cacaca;
}

.main_table > table th {
    background-color: #cacaca;
}

.main_table > table th:not(:first-of-type):hover {
    text-decoration: underline;
}

/* Настройки для кнопок управления строками */
.main_table > table td:first-of-type {
    /*background-color: #cacaca;*/
}

/* Установка ширины для первой колонки */
.main_table > table th:first-of-type, .main_table > table td:first-of-type {
    /*display: inline;*/
    width: 84px;
}

#form_write #foreign_key {
    cursor: pointer;
}
/*
/*Перекрытие для таблицы control/
.emerge table#control td:first-of-type, .emerge table#control th:first-of-type {
    width: 10em;
}
*/
#foreign_table table tbody tr {
    cursor: pointer;
}

#setting_error {
    display : none;
}

#setting_control_panel_db {
    margin-top: 1em;
}
</style>

<div class="row">

    <!-- Верхнаяя панель заголовка -->
    <div class="span12">
        <h2>Управление данными</h2>
    </div>
    <!-- ============== -->

    <!-- Боковая панель -->
    <div class="span2">
        <ul class="setting_aside nav nav-list">
            <include href="app/module/setting/view/aside_list_tables.php" />
            <hr>
            <include href="view/admin_aside_list_controls.php" />
        </ul>
    </div>
    <!-- ========================= -->

    <!-- Главное операционное поле -->
    <div id="admin_index" class="span10">
        <check if="{{ @tmp.setting.table }}">
            <true>
                <div id="setting_control_panel_db">
                    <button class="btn" option='insert' title="Добавить"><span class="icon-plus"></span> Добавить</button>
                    <button class="btn" option='search' title="Поиск"><span class="icon-search"></span> Поиск</button>
                    <button class="btn" option='filter' title="Фильтр"><span class="icon-filter"></span> Фильтр</button>
                    <button class="btn" option='limit' title="Диапазон"><span class="icon-resize-full"></span> Диапазон</button>
                    <button class="btn" option='refresh' title="Обновить"><span class="icon-refresh"></span> Обновить</button>
                    <button class="btn" option='clear' title="Сброс"><span class="icon-remove"></span> Сброс</button>
                </div>
            </true>
        </check>

        <h5 id="setting_error" class="text-center"></h5>
        <div class="image_loading"></div>
        <div id="setting_table" class="main_table"></div>

        <!-- Скрытые поля для хранения параметров и выполнения функций -->
        <div id="setting_table_options">

            <!-- Форма для записи в таблицу -->
            <div id="form_write" class="form-horizontal emerge" active='false'>
                <div class="controls"><h4>Запись в таблице</h4></div>

                <hr>
                <div class="control-group">
                    <div class="controls">
                        <button option='form_write_ok' class='btn'><span class='icon-ok'></span> Сохранить</button>
                        <button option='refresh' class='btn'><span class='icon-remove'></span> Отмена</button>
                    </div>
                </div>
            </div>
            <!-- ========================== -->

            <!-- Форма установки фильтрации -->
            <div id="form_filter" class="form-horizontal emerge" active='false'>
                <div class="controls"><h4>Фильтрация данных</h4></div>
                <table class='table'>
                    <thead>
                        <tr>
                            <th>Поле данных</th>
                            <th>Фильтр</th>
                            <th>Значение</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <hr>
                <button option='form_filter_ok' class='btn'><span class='icon-ok'></span> Ок</button>
                <button option='form_filter_add' class='btn'><span class='icon-plus'></span> Добавить поле</button>
                <button option='refresh' class='btn'><span class='icon-remove'></span> Отмена</button>
            </div>
            <!-- ========================== -->

            <!-- Форма для установки лимита -->
            <div id="form_limit" class="form-horizontal emerge" active="false">
                <div class="controls"><h4>Диапазон</h4></div>

                <div class="control-group">
                    <label class="control-label" for="field_start">Стартовая позиция</label>
                    <div class="controls">
                        <input type="number" placeholder="" id="field_start" name="start" size="20" value>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_limit">Количество записей</label>
                    <div class="controls">
                        <input type="number" placeholder="" id="field_limit" name="limit" size="20" maxlength="10" value>
                    </div>
                </div>

                <hr>
                <div class="control-group">
                    <div class="controls">
                        <button option="form_limit_ok" class="btn"><span class="icon-ok"></span> Ок</button>
                        <button option="refresh" class="btn"><span class="icon-remove"></span> Отмена</button>
                    </div>
                </div>
            </div>
            <!-- =================================== -->

            <!-- Форма для поиска в таблице по полям -->
            <div id="form_search" class="form-horizontal emerge" active="false">
                <div class="controls"><h4>Поиск</h4></div>

                <div class="control-group">
                    <label class="control-label" for="field_like_field">Поиск по полю</label>
                    <div class="controls">
                        <select name="like_field" id="field_like_field" size="1"></select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_like_data">Искомое значение</label>
                    <div class="controls">
                        <input type="search" name="like_data" id="field_like_data" size="20" maxlength="255" value>
                    </div>
                </div>

                <hr>
                <div class="control-group">
                    <div class="controls">
                        <button option="form_search_ok" class="btn"><span class="icon-ok"></span> Ок</button>
                        <button option="refresh" class="btn"><span class="icon-remove"></span> Отмена</button>
                    </div>
                </div>
            </div>
            <!-- ========================== -->

            <input type="hidden" name="order_field" value="{{ @tmp.setting.default_value.order_field }}" />
            <input type="hidden" name="order_by" value="{{ @tmp.setting.default_value.order_by }}" />

            <input type="hidden" name="like_field" value="{{ @tmp.setting.default_value.like_field }}" />
            <input type="hidden" name="like_data" value="{{ @tmp.setting.default_value.like_data }}" />

            <input type="hidden" name="filter_field" value="{{ @tmp.setting.default_value.filter_field }}" />
            <input type="hidden" name="filter" value="{{ @tmp.setting.default_value.filter }}" />
            <input type="hidden" name="filter_data" value="{{ @tmp.setting.default_value.filter_data }}" />

            <input type="hidden" name="limit" value="{{ @tmp.setting.default_value.limit }}" />
            <input type="hidden" name="start" value="{{ @tmp.setting.default_value.start }}" />

            <input type="hidden" name="hashing" value="" />

            <input type="hidden" name="foreign_table" value="" />
            <input type="hidden" name="foreign_field" value="" />
        </div>

    </div>

</div>

