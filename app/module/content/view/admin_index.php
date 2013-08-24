<style>
#control_error {
    clear: both;
}

/* ---------------------------------------------------------------------------------------------- */

#control_content, #list_content {
    display: none;
    margin-top: 1em;
}

#list_content table tbody tr {
    cursor: pointer;

}

#list_content table tbody tr > td:last-of-type {
    text-overflow : ellipsis;
    overflow: hidden;
}

#list_content li > span:hover {
    text-decoration: underline;
}




ul#content_photos {
    list-style-type: none;
    margin: 0;
    padding: 0;
    margin-bottom: 10px;
}

#content_photos li {
    margin: 5px;
    padding: 5px;
    width: 100px;
    height: 100px;
    float: left;
    opacity: 0.8;
    text-align: center;
}

#content_photos li img:hover {
    cursor: move;
}

#content_photos li:hover {
    background-color: beige;
    opacity: 1;
}

#content_photos .control_photo {
    position: absolute;
    width: 100px;
    display: none;
    background-color: #c0c0c0;
}

#content_photos li:hover .control_photo {
    display: block;
    opacity: 0.9;
}

#content_photos li:hover .control_photo:hover {
}

#content_photos .img_del {
    position: relative;
    float: right;
    display: block;
    opacity: 0.7;
}

#content_photos .img_zoom {
    position: relative;
    float: left;
    display: block;
    opacity: 0.7;
}

#content_photos .img_del:hover, #content_photos .img_zoom:hover {
    cursor: pointer;
    opacity: 1;
}

</style>


<div id="admin_index" class="span10">
    <h4>{{ @tmp.admin.introduction }}</h4>
    <br>

    <!-- Меню с табами -->
    <ul class="nav nav-tabs" id="testr_tab">
        <li><a href="#t_config">Настройки</a></li>
        <li class="active"><a href="#t_control">Материалы</a></li>
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

// id текущего материала с которым происходит работа
var id_content = 0;
var id_category = 0;
var admin_loading, admin_processing;
// Плоский список со всеми категориями для опций селекта
var list_category = {};
//JSON с названиями фотографий
var photos = [];

$(function() {
    admin_loading = $("#admin_index .image_loading")[0];
    // admin_processing = $("#admin_index .image_processing")[0];
    get_list_category();

    // Формируем работу с фотогаллереей: увеличение/удаление фото, сортировка положения
    $('#content_photos').sortable({revert: true});
    $('ul, li').disableSelection();

    $('#fileupload').fileupload({
        url: "{{ @init.sys.url }}admin/content/upload_photos",
        dataType: 'json',
        done: function (e, data) {
            $('.progress .bar').text('Загружено');
            $.each(data.result.files, function (index, file) {
                var img_height = file.photo_height == undefined ? file.img_height : file.photo_height;
                var img_width = file.photo_width == undefined ? file.img_width : file.photo_width;
                photos.push({"photo" : file.photoUrl, "thumbnail" : file.thumbnailUrl, "height" : img_height, "width" : img_width});
                var i = photos.length -1;
                $('#content_photos').append(
"<li photo='" + i + "' ><div class='control_photo'><a href='" + file.photoUrl + "' class='icon-eye-open img_zoom'></a><span photo='" + i + "' class='icon-eye-close img_del'></span></div>" +
"<img src='" + file.thumbnailUrl + "'></li>");

                $(".img_zoom").colorbox({rel:'img_zoom'});
            });
        },
        progress: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('.progress .bar').css(
                'width',
                progress + '%'
            );
            $('.progress .bar').text(progress + '%');
        },
        fail: function (e, data) {
            $('.progress .bar').text('Ошибка');
        }
    });

});

//Удаление конкретной фотографии
$(document).on('click', ".img_del", function() {
    if (!confirm("Убрать фотографию?")) return;

    var i = $(this).attr('photo');
    photos.splice(i, 1, undefined);

    $("#content_photos li[photo='" + i + "']").fadeOut('fast', function() {
        $(this).remove();
    });
});

//Удаление всех фотографии
$(document).on('click', "#t_control [name='control_content_photos_del_all']", function() {
    if (!confirm("Убрать все фотографии из фотогалереи?")) return;
    photos = [];
    $('#content_photos li').fadeOut('fast', function() {
        $(this).remove();
    });
});

//Вставка разрыва страницы
$(document).on('click', "#t_control [name='page_break']", function() {
    wysiarea.focus();
    wysiarea.composer.commands.exec("insertHTML", "@pagebreak");
});

// Запрос на получение списка всех материалов
function get_list_content() {
    $("#list_content tbody").empty();
    $("#control_content").fadeOut('fast');
    $("#list_content").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/content/get_list", {
"id_category"   : id_category
        }, function(data) {
            // Парсинг всех полей профиля
            if (data.length == 0) {
                $("#list_content p").append("В этой категории пока не создано ни одного материала.");
                return;
            }

            $.each(data, function(key, item) {
                $("#list_content tbody").append("<tr id_content='" + item.id + "'><td>" + item.id + "</td><td>" + list_category[item.id_category] + "</td><td>" + item.title + "</td><td>" + item.created + "</td><td>" + item.desc.substring(0,50) + "...</td></tr>");
            });
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        }).always(function() {
            $(admin_loading).fadeOut('fast', function() {
                $("#list_content").fadeIn('fast');
            });
        });
    });
}

// Генерация списка категорий для селектов
function get_list_category() {
    $("#t_control [name='select_category'], #control_content [name='id_category']").empty();
    $.post("{{ @init.sys.url }}admin/category/get_flat_list", {
    }, function(data) {
        // Парсинг всех полей профиля
        if (data.length == 0) {
            $("#t_control [name='select_category'], #t_control [name='new_content']").fadeOut('fast');
            $("#list_content tbody").empty();
            $("#list_content p").html("Категорий еще не создано. <br>");
            $("#list_content").fadeIn('fast');
            return;
        }

        $("#t_control [name='select_category'], #t_control [name='new_content']").fadeIn('fast');
        $("#t_control [name='select_category']").append("<option value='0'>Все материалы</option>");
        // i - параметр для восстановления прежде выбранной категории на случай если она была удалена во время работы с контентом
        var i = false;
        $.each(data, function(key, item) {
            list_category[item.id] = item.title;
            $("#t_control [name='select_category']").append("<option value='" + item.id + "'>" + item.title + "</option>");
            $("#control_content [name='id_category']").append("<option value='" + item.id + "'>" + item.title + "</option>");
            if (id_category == item.id) {
                i = true;
                $("#t_control [name='select_category']").val(id_category);
            }
        });

        if (i == false) {
            id_category = 0;
        }
    }, "json").fail(function() {
        air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
    });

    get_list_content();
}

// Обновление списков
$(document).on("click", "button[name='refresh'], button[name='control_content_abort']", function() {
    $("#list_content p").empty();
    get_list_category();
});

//Переключение между категориями
$(document).on("change", "#t_control [name='select_category']", function() {
    $("#list_content p").empty();
    id_category = $(this).val();
    get_list_content();
});

// Включение интерфейса для изменения данных
$(document).on("click", "#list_content tbody tr[id_content]", function() {
    id_content = $(this).attr('id_content');
    $("[name='control_content_del']").fadeIn('fast');

    $("#list_content").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/content/get", {
'id' : id_content
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при получении данных материала.", "text-center alert alert-error");
                $("#list_content").fadeIn('fast');
                return;
            }

            $("#control_content [name='title']").val(data.title);
            $("#control_content [name='id_category']").val(data.id_category);
            $("#control_content [name='desc']").val(data.desc);
            wysiarea.focus();
            wysiarea.composer.clear();
            wysiarea.composer.commands.exec("insertHTML", data.desc);

            photos = [];
            $('#content_photos').empty();
            if (typeof data.photos == "string") {
                photos = $.parseJSON(data.photos) == null ? [] : $.parseJSON(data.photos);
                for (var i in photos) {
                    $('#content_photos').append(
"<li photo='" + i + "' ><div class='control_photo'><a href='" + photos[i].photo + "' class='icon-eye-open img_zoom'></a><span photo='" + i + "' class='icon-eye-close img_del'></span></div>" +
"<img src='" + photos[i].thumbnail + "'></li>"
                    );
                }

                $(".img_zoom").colorbox({rel:'img_zoom'});
            }

            $("#control_content").fadeIn('fast');
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        }).always(function() {
            $(admin_loading).fadeOut('fast');
        });
    });
});

// Для добавления нового материала
$(document).on("click", "button[name='new_content']", function() {
    id_content = 0;
    $("[name='control_content_del'], #control_content").fadeOut('fast');

    $("#list_content").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');

        $("#control_content [name='title']").val('');
        $("#control_content [name='id_category']").val('');
        if (id_category > 0) {
            $("#control_content [name='id_category']").val(id_category);
        }
        wysiarea.focus();
        wysiarea.composer.clear();
        photos = [];
        $('#content_photos').empty();
        $(admin_loading).fadeOut('fast', function() {
            $("#control_content").fadeIn('fast');
        });
    });
});

//--------------------------------------------------------------------------------------------------

// Отправка изменений
$(document).on("click", "#control_content button[name='control_content_ok']", function() {
    //Формирование нового массива фотографий с учетом сортировки
    var photo_sort = [];
    $('#content_photos li').each(function(id, item) {
        var i = $(item).attr('photo');
        if (photos[i] != undefined) {
            photo_sort.push(photos[i]);
        }
    });

    $("#control_content").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/content/set", {
'id'            : id_content,
'id_category'   : $("#control_content [name='id_category']").val(),
'title'         : $("#control_content [name='title']").val(),
'photos'        : photo_sort,
'desc'          : $("#control_content [name='desc']").val()
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при изменении материала.", "text-center alert alert-error");
                return;
            }
            air_error("#control_error", "Изменение материала прошло успешно.", "text-center alert alert-success");
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        }).always(function() {
            get_list_content();
        });
    });
});

//--------------------------------------------------------------------------------------------------

// Удаление материала
$(document).on("click", "#control_content button[name='control_content_del']", function() {
    if (!confirm("Вы действительно хотите удалить этот материал и все данные связанные с ним?")) {
        return;
    }

    $("#control_content").fadeOut('fast', function() {
        $(admin_loading).fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/content/del", {
'id'    : id_content
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при удалении материала.", "text-center alert alert-error");
                return;
            }
            air_error("#control_error", "Удаление материала прошло успешно.", "text-center alert alert-success");
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке данных.", "text-center alert alert-error");
        }).always(function() {
            get_list_content();
        });
    });
});

</script>

        <div class="tab-pane active" id="t_control">

            <p>Материалы содержат текстовый контент доступный все пользователям.</p>
            <p>Что бы отделить превью часть от всего материала используйте кнопку "Вставить разрыв страницы".</p>
            <p>Желаете вставить много фотографий? Воспользуйтесь фотолентой!</p>

            <div class="form-inline">
                <button name="new_content" class="btn"><span class="icon-plus"></span> Добавить новый контент</button>
                <select name="select_category"></select>
                <button name="refresh" class="btn"><span class="icon-refresh"></span> Обновить</button>
            </div>


            <div id="list_content" class="">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th class="span1">#</th>
                            <th class="span2">Категория</th>
                            <th>Заголовок</th>
                            <th>Дата</th>
                            <th>Содержимое</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <p></p>
            </div>
            <div id="control_content" class="form-horizontal">

                <div class="control-group">
                    <label class="control-label" for="field_content_title">Название</label>
                    <div class="controls"><input type="text" id="field_content_title" name="title" placeholder="" size="20" value=""></div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_content_id_category">Категория</label>
                    <div class="controls">
                        <select id="field_content_id_category" name="id_category" size="1"></select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_content_desc">Текст материала</label>
                    <div class="controls">
                        <textarea class="textarea" id="field_content_desc" name="desc"></textarea>
                        <br>
                        <br>
                        <button name="page_break" class="btn">Вставить разрыв страницы</button>
                    </div>
                </div>

                <hr>

                <div class="control-group">
                    <label class="control-label" for="field_content_desc">Фотолента</label>
                    <div class="controls">
                        <div id="photos"></div>
                        <ul id="content_photos"></ul>
                        <div class="clearfix"></div>

                        <span class="btn fileinput-button">
                            <span>Загрузить фотографии</span>
                            <input id="fileupload" type="file" name="files[]" multiple>
                        </span>
                        <br>
                        <div class="progress">
                            <div class="bar"></div>
                        </div>

                    </div>
                </div>

                <hr>
                <div class="control-group">
                    <div class="controls">
                        <button name="control_content_ok" class="btn"><span class="icon-ok"></span> Применить</button>
                        <button name="control_content_abort" class="btn"><span class="icon-remove"></span> Отмена</button>
                        <button name="control_content_del" class="btn"><span class="icon-minus"></span> Удалить</button>
                        <button name="control_content_photos_del_all" class="btn">Удалить все фотографии</button>
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

