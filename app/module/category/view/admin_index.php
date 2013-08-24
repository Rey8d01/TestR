<style>
#control_error {
    clear: both;
}

/* ---------------------------------------------------------------------------------------------- */

#control_category, #list_category {
    display: none;
    margin-top: 1em;
}

#list_category li > span {
    cursor: pointer;

}

#list_category li > span:hover {
    text-decoration: underline;
}
</style>


<div id="admin_index" class="span10">
    <h4>{{ @tmp.admin.introduction }}</h4>
    <br>

    <!-- Меню с табами -->
    <ul class="nav nav-tabs" id="testr_tab">
        <li><a href="#t_config">Настройки</a></li>
        <li class="active"><a href="#t_control">Категории</a></li>
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

// id текущей категории с которой происходит работа
var id_category = 0;
var admin_loading, admin_processing;
// Плоский список со всеми категориями для опций селекта
var list_category = {};

$(function() {
    admin_loading = $("#admin_index .image_loading")[0];
    // admin_processing = $("#admin_index .image_processing")[0];

    get_list_category();
});

// Запрос на получение списка всех категорий
function get_list_category() {
    $("#control_category").fadeOut('fast');

    $("#list_category").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/category/get_list", {
        }, function(data) {
            // Парсинг всех полей профиля
            if (data.length == 0) {
                $("#list_category").text("Пока не создано ни одной категории.").fadeIn('fast');
                return;
            }

            $("#list_category").empty();

            generate_list_category(data);

            $("#list_category [inner_category]").sortable({placeholder: "ui-state-highlight"});

            $("#control_category [name='id_parent']").html("<option value='0'>Корень</option>")
            $.each(list_category, function(key, title) {
                $("#control_category [name='id_parent']").append("<option value='" + key + "'>" + title + "</option>")
            })

            $(admin_loading).fadeOut('fast', function() {
                $("#list_category").fadeIn('fast');
            });
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        });
    });
}

// Рекурсивная генерация списка категорий
function generate_list_category(data, category) {
    if (category == undefined) {
        category = 0;
        $("#list_category").append("<ul inner_category='0'></ul>");
    }
    $.each(data, function(key, item) {
        list_category[item.id] = item.title;
        if (item.inner != undefined) {
            $("#list_category [inner_category='" + category + "']").append("<li><span id_category='" + item.id + "'><strong>" + item.id + "</strong> - " + item.title + "</span><ul inner_category='" + item.id + "'></ul></li>");
            generate_list_category(item.inner, item.id);
        } else {
            $("#list_category [inner_category='" + category + "']").append("<li><span id_category='" + item.id + "'><strong>" + item.id + "</strong> - " + item.title + "</span></li>");
        }
    })
}

$(document).on("click", "button[name='refresh'], button[name='control_category_abort']", function() {
    get_list_category();
});

// Включение интерфейса для изменения данных
$(document).on("click", "#list_category li > span[id_category]", function() {
    id_category = $(this).attr('id_category');
    $("#control_category [name='id_parent'] option[value='" + id_category + "']").attr("disabled", true);

    $("[name='control_category_del']").fadeIn('fast');

    $("#list_category").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/category/get", {
'id_category' : id_category
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при получении данных категории.", "text-center alert alert-error");
                return;
            }

            $("#control_category [name='title']").val(data.title);
            $("#control_category [name='id_parent']").val(data.id_parent);
            $("#control_category [name='visible']").val(data.visible);
            wysiarea.focus();
            wysiarea.composer.clear();
            wysiarea.composer.commands.exec("insertHTML", data.desc);

            $(admin_loading).fadeOut('fast', function() {
                $("#control_category").fadeIn('fast');
            });
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        });
    });
});

// Для добавления новой категории
$(document).on("click", "button[name='new_category']", function() {
    id_category = 0;
    $("#control_category [name='id_parent'] option").attr("disabled", false);

    $("[name='control_category_del'], #control_category").fadeOut('fast');

    $("#list_category").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');

        $("#control_category [name='title']").val('');
        $("#control_category [name='id_parent']").val('');
        $("#control_category [name='visible']").val(1);
        wysiarea.focus();
        wysiarea.composer.clear();

        $(admin_loading).fadeOut('fast', function() {
            $("#control_category").fadeIn('fast');
        });

    });
});

//--------------------------------------------------------------------------------------------------

// Отправка изменений
$(document).on("click", "#control_category button[name='control_category_ok']", function() {
    $("#control_category").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/category/set", {
'id'        : id_category,
'title'     : $("#control_category [name='title']").val(),
'id_parent' : $("#control_category [name='id_parent']").val(),
'desc'      : $("#control_category [name='desc']").val(),
'visible'   : $("#control_category [name='visible']").val()
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при изменении данных категории.", "text-center alert alert-error");
                return;
            }
            air_error("#control_error", "Изменение данных категории прошло успешно.", "text-center alert alert-success");
            get_list_category();
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        });
    });
});

//--------------------------------------------------------------------------------------------------

// Удаление категории
$(document).on("click", "#control_category button[name='control_category_del']", function() {
    if (!confirm("Вы действительно хотите удалить эту категорию и все данные связанные с ней?")) {
        return;
    }

    $("#control_category").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/category/del", {
'id'        : id_category
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при удалении категории.", "text-center alert alert-error");
                return;
            }
            air_error("#control_error", "Удаление данных категории прошло успешно.", "text-center alert alert-success");
            get_list_category();
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке данных.", "text-center alert alert-error");
        });
    });
});

//--------------------------------------------------------------------------------------------------

// Сохранение положения категорий
$(document).on("click", "button[name='save_sort']", function() {
    var sort = [];
    $("#list_category li").each(function() {
        sort.push($(this).children("span").attr("id_category"));
    });

    $("#list_category").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/category/set_sort", {
'sort' : sort
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при изменении позиции категорий.", "text-center alert alert-error");
                return;
            }
            air_error("#control_error", "Изменение позиций категорий прошло успешно.", "text-center alert alert-success");
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        }).always(function() {
            get_list_category();
        });
    });
});
</script>

        <div class="tab-pane active" id="t_control">

            <p>
                Категории необходимы для тематического разделения материалов.
            </p>
            <p>
                Порядок отображения подкатегорий (в момент просмотра категории) можно менять так же,
                как и менять позции элементов меню.
            </p>

            <button name="new_category" class="btn"><span class="icon-plus"></span> Добавить новую категорию</button>
            <button name="refresh" class="btn"><span class="icon-refresh"></span> Обновить</button>
            <button name="save_sort" class="btn"><span class="icon-random"></span> Сохранить позиции</button>


            <div id="list_category" class=""></div>
            <div id="control_category" class="form-horizontal">

                <div class="control-group">
                    <label class="control-label" for="field_category_title">Название</label>
                    <div class="controls"><input type="text" id="field_category_title" name="title" placeholder="" size="20" value=""></div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_category_id_parent">Родительская категория</label>
                    <div class="controls">
                        <select id="field_category_id_parent" name="id_parent" size="1"></select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_category_desc">Описание</label>
                    <div class="controls"><textarea class="textarea" id="field_category_desc" name="desc"></textarea></div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_category_visible">Вдимость (ссылки к невидимым категориям, как и их материалы, не отображаются, но к ним можно получить доступ по прямой ссылке)</label>
                    <div class="controls"><select id="field_category_visible" size="1" name="visible"><option value="1">Видимая</option><option value="0">Невидимая</option></select></div>
                </div>

                <hr>
                <div class="control-group">
                    <div class="controls">
                        <button name="control_category_ok" class="btn"><span class="icon-ok"></span> Применить</button>
                        <button name="control_category_abort" class="btn"><span class="icon-remove"></span> Отмена</button>
                        <button name="control_category_del" class="btn"><span class="icon-minus"></span> Удалить</button>
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