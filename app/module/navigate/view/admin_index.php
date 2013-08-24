<style>
#control_error {
    clear: both;
}

/* ---------------------------------------------------------------------------------------------- */

#control_navigate, #list_navigate {
    display: none;
    margin-top: 1em;
}

#list_navigate li > span {
    cursor: pointer;

}

#list_navigate li > span:hover {
    text-decoration: underline;
}


#list_navigate > ul ul {
    border: 1px #cacaca dashed;
    padding-right: 1em;
}
</style>


<div id="admin_index" class="span10">
    <h4>{{ @tmp.admin.introduction }}</h4>
    <br>

    <!-- Меню с табами -->
    <ul class="nav nav-tabs" id="testr_tab">
        <li><a href="#t_config">Настройки</a></li>
        <li class="active"><a href="#t_control">Навигация</a></li>
    </ul>
    <!-- ================ -->

    <!-- Содержимое табов -->
    <div class="tab-content">
        <!-- =================================================================================== -->
        <div class="tab-pane" id="t_config">
            <include href="/view/admin_config.php" />
        </div>
        <!-- =================================================================================== -->

<script language="JavaScript" type="text/javascript">

// id текущего элемента меню с которой происходит работа
var id_navigate = 0;
var admin_loading, admin_processing;
// Плоский список со всеми элементами меню для опций селекта
var list_navigate = {};
var list_module = {{ json_encode(@init.modules) }};
var list_group = {{ json_encode(@init.groups) }};

$(function() {
    admin_loading = $("#admin_index .image_loading")[0];
    // admin_processing = $("#admin_index .image_processing")[0];

    get_list_navigate();

    for (var module in list_module) {
        $("select[name='__module']").append("<option value='" + module + "'>" + list_module[module].dict + "</option>");
    }
    generate_list_function();
    generate_input_id();

    for (var i in list_group) {
        $("select[name='id_group']").append("<option value='" + i + "'>" + list_group[i][1] + "</option>");
    }
});

// Генерация опций селекта для функций
function generate_list_function() {
    var __module = $("select[name='__module']").val();

    $("select[name='__function']").empty();

    for (var i in list_module[__module].func) {
        $("select[name='__function']").append("<option value='" + list_module[__module].func[i].name + "'>" + list_module[__module].func[i].dict + "</option>");
    }
}
// Генерация опций селекта для функций
function generate_input_id() {
    var __module = $("select[name='__module']").val();
    var __function = $("select[name='__function']").val();

    $("input[name='__id']").val('');
    for (var i in list_module[__module].func) {
        if (list_module[__module].func[i].name == __function) {
            if (list_module[__module].func[i].id == true) {
                $("input[name='__id']").attr("disabled", false);
            } else {
                $("input[name='__id']").attr("disabled", true);
            }
            break;
        }
    }
}

$(document).on("change", "select[name='__module']", function() {
    generate_list_function();
    generate_input_id();
});

$(document).on("change", "select[name='__function']", function() {
    generate_input_id();
});

$(document).on("change", "select[name='type']", function() {
    switch ($(this).val()) {
        case 'link':
            $("input[name='title']").attr("disabled", false);
            $("select[name='__module']").attr("disabled", false);
            $("select[name='__function']").attr("disabled", false);
            $("input[name='__id']").attr("disabled", false);
            break;
        case 'divider':
            $("input[name='title']").attr("disabled", true).val('Разделительная черта');
            $("select[name='__module']").attr("disabled", true).val('');
            $("select[name='__function']").attr("disabled", true).val('');
            $("input[name='__id']").attr("disabled", true).val('');
            break;
        case 'nav-header':
            $("input[name='title']").attr("disabled", false);
            $("select[name='__module']").attr("disabled", true).val('');
            $("select[name='__function']").attr("disabled", true).val('');
            $("input[name='__id']").attr("disabled", true).val('');
            break;
        case 'dropdown':
            $("input[name='title']").attr("disabled", false);
            $("select[name='__module']").attr("disabled", true).val('');
            $("select[name='__function']").attr("disabled", true).val('');
            $("input[name='__id']").attr("disabled", true).val('');
            break;
        default:
            $("input[name='title']").attr("disabled", false);
            $("select[name='__module']").attr("disabled", false);
            $("select[name='__function']").attr("disabled", false);
            $("input[name='__id']").attr("disabled", false);
            break;
    }
});

// Запрос на получение списка всех элементов меню
function get_list_navigate() {
    $("#control_navigate").fadeOut('fast');

    $("#list_navigate").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/navigate/get_list_tree", {
        }, function(data) {
            // Парсинг всех полей профиля
            if (data.length == 0) {
                $("#list_navigate").text("Пока не создано ни одного элемента меню.").fadeIn('fast');
                return;
            }

            $("#list_navigate").empty();

            generate_list_navigate(data);

            $("#list_navigate [inner_navigate]").sortable({placeholder: "ui-state-highlight"});
            $("#list_navigate [inner_navigate]").disableSelection();

            $("#control_navigate [name='id_parent']").html("<option value='0'>Корень</option>")
            $.each(list_navigate, function(key, title) {
                $("#control_navigate [name='id_parent']").append("<option value='" + key + "'>" + title + "</option>")
            });

            $(admin_loading).fadeOut('fast', function() {
                $("#list_navigate").fadeIn('fast');
            });
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        });
    });
}

// Рекурсивная генерация списка элементов меню
function generate_list_navigate(data, navigate) {
    if (navigate == undefined) {
        navigate = 0;
        $("#list_navigate").append("<ul inner_navigate='0'></ul>");
    }
    $.each(data, function(key, item) {
        list_navigate[item.id] = item.title;
        if (item.inner != undefined) {
            $("#list_navigate [inner_navigate='" + navigate + "']").append("<li><span id_navigate='" + item.id + "'>" + item.title + "</span><ul inner_navigate='" + item.id + "'></ul></li>");
            generate_list_navigate(item.inner, item.id);
        } else {
            $("#list_navigate [inner_navigate='" + navigate + "']").append("<li><span id_navigate='" + item.id + "'>" + item.title + "</span></li>");
        }
    });
}

$(document).on("click", "button[name='refresh'], button[name='control_navigate_abort']", function() {
    get_list_navigate();
});

// Включение интерфейса для изменения данных
$(document).on("click", "#list_navigate li > span[id_navigate]", function() {
    id_navigate = $(this).attr('id_navigate');
    $("#control_navigate [name='id_parent'] > option[value='" + id_navigate + "']").attr("disabled", true);

    $("[name='control_navigate_del']").fadeIn('fast');

    $("#list_navigate").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/navigate/get", {
'id' : id_navigate
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при получении элемента меню.", "text-center alert alert-error");
                return;
            }

            $("#control_navigate [name='id_parent']").val(data.id_parent);
            $("#control_navigate [name='id_group']").val(data.id_group);
            $("#control_navigate [name='type']").val(data.type);
            $("select[name='type']").change();
            $("#control_navigate [name='title']").val(data.title);
            $("#control_navigate [name='__module']").val(data.__module);
            generate_list_function();
            $("#control_navigate [name='__function']").val(data.__function);
            generate_input_id()
            $("#control_navigate [name='__id']").val(data.__id);

            $(admin_loading).fadeOut('fast', function() {
                $("#control_navigate").fadeIn('fast');
            });
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        });
    });
});

// Для добавления нового элемента меню
$(document).on("click", "button[name='new_navigate']", function() {
    id_navigate = 0;
    $("#control_navigate [name='id_parent'] > option").attr("disabled", false);

    $("[name='control_navigate_del'], #control_navigate").fadeOut('fast');

    $("#list_navigate").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');

        $("#control_navigate [name='id_parent']").val('');
        $("#control_navigate [name='id_group']").val('');
        $("#control_navigate [name='type']").val('link');
        $("select[name='type']").change();
        $("#control_navigate [name='title']").val('');
        $("#control_navigate [name='__module']").val('');
        $("#control_navigate [name='__function']").val('');
        $("#control_navigate [name='__id']").val('');

        $(admin_loading).fadeOut('fast', function() {
            $("#control_navigate").fadeIn('fast');
        });

    });
});

//--------------------------------------------------------------------------------------------------

// Отправка изменений
$(document).on("click", "#control_navigate button[name='control_navigate_ok']", function() {
    $("#control_navigate").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/navigate/set", {
'id'            : id_navigate,
'id_parent'     : $("#control_navigate [name='id_parent']").val(),
'id_group'      : $("#control_navigate [name='id_group']").val(),
'type'          : $("#control_navigate [name='type']").val(),
'title'         : $("#control_navigate [name='title']").val(),
'__module'      : $("#control_navigate [name='__module']").val(),
'__function'    : $("#control_navigate [name='__function']").val(),
'__id'          : $("#control_navigate [name='__id']").val()
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при изменении элемента меню.", "text-center alert alert-error");
                return;
            }
            air_error("#control_error", "Изменение элемента меню прошло успешно.", "text-center alert alert-success");
            get_list_navigate();
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        });
    });
});

//--------------------------------------------------------------------------------------------------

// Удаление элемента меню
$(document).on("click", "#control_navigate button[name='control_navigate_del']", function() {
    if (!confirm("Вы действительно хотите удалить этот элемент меню")) {
        return;
    }

    $("#control_navigate").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/navigate/del", {
'id'        : id_navigate
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при удалении меню.", "text-center alert alert-error");
                return;
            }
            air_error("#control_error", "Удаление элемента меню прошло успешно.", "text-center alert alert-success");
            get_list_navigate();
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке данных.", "text-center alert alert-error");
        });
    });
});

//--------------------------------------------------------------------------------------------------

// Сохранение положения элементов меню
$(document).on("click", "button[name='save_sort']", function() {
    var sort = [];
    $("#list_navigate li").each(function() {
        sort.push($(this).children("span").attr("id_navigate"));
    });

    $("#list_navigate").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/navigate/set_sort", {
    'sort' : sort
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при изменении позиции элементов меню.", "text-center alert alert-error");
                return;
            }
            air_error("#control_error", "Изменение позиций элементов меню прошло успешно.", "text-center alert alert-success");
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        }).always(function() {
            get_list_navigate();
        });
    });
});
</script>
        <div class="tab-pane active" id="t_control">

            <p>
                Навигационное меню - основной способ перемещения по различным областям и функциям системы.
                Оно расположено в верхней части экрана.
            </p>
            <p>
                Пожалуйста, соблюдайте один уровень вложенного меню у элемента.<br>
                Вы можете перемещать позиции элементов меню в рамках одного уровня вложенности -
                для этого зажмите левой кнопкой мыши над элементом,
                который желаете переместить и удерживая перетащите его на выбранную позицию,
                после чего нажимте кнопку "Сохранить позиции".
            </p>
            <p>
                Для редактирования элемента меню кликните левой кнопкой мыши на нем.
            </p>

            <button name="new_navigate" class="btn"><span class="icon-plus"></span> Добавить новый элемент меню</button>
            <button name="refresh" class="btn"><span class="icon-refresh"></span> Обновить</button>
            <button name="save_sort" class="btn"><span class="icon-random"></span> Сохранить позиции</button>

            <div id="list_navigate" class=""></div>
            <div id="control_navigate" class="form-horizontal">

                <div class="control-group">
                    <label class="control-label" for="field_navigate_title">Название</label>
                    <div class="controls"><input type="text" id="field_navigate_title" name="title" placeholder="" size="20" value=""></div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_navigate_id_parent">Родительская категория</label>
                    <div class="controls"><select id="field_navigate_id_parent" name="id_parent" size="1"></select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_navigate_type">Тип элемента ('В' - только для элементов выпадающего меню, 'Г' - только для элементов главного меню)</label>
                    <div class="controls">
                        <select id="field_navigate_type" name="type" size="1">
                            <option value="link">Ссылка</option>
                            <option value="dropdown">Выпадающее меню (Г)</option>
                            <option value="divider">Разделительная черта (В)</option>
                            <option value="nav-header">Заголовок (В)</option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_navigate_id_group">Группа пользователей которым доступно меню</label>
                    <div class="controls"><select id="field_navigate_id_group" name="id_group" size="1"></select></div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_navigate__module">Укажите модуль</label>
                    <div class="controls"><select id="field_navigate__module" name="__module" size="1"></select></div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_navigate__function">Укажите функцию модуля</label>
                    <div class="controls"><select id="field_navigate__function" name="__function" size="1"></select></div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_navigate__id">Укажите id контента</label>
                    <div class="controls"><input type="text" id="field_navigate__id" name="__id" placeholder="" size="20" value=""></div>
                </div>

                <hr>
                <div class="control-group">
                    <div class="controls">
                        <button name="control_navigate_ok" class="btn"><span class="icon-ok"></span> Применить</button>
                        <button name="control_navigate_abort" class="btn"><span class="icon-remove"></span> Отмена</button>
                        <button name="control_navigate_del" class="btn"><span class="icon-minus"></span> Удалить</button>
                    </div>
                </div>

            </div>

        </div>
        <!-- =================================================================================== -->
        <!-- =================================================================================== -->
    </div>
    <!-- ================ -->

    <div class="image_loading"></div>
    <h5 id="control_error"></h5>
</div>