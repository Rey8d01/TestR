<style>
#user_control {
    display: none;
}

/* ---------------------------------------------------------------------------------------------- */

#control_error {
    clear: both;
}

/* ---------------------------------------------------------------------------------------------- */
#profile_control, #profile_empty, #profile_field_control {
    display: none;
    margin-top: 1em;
}

#profile_sort {
    list-style-type: none;
    margin: 0;
    padding: 0;
    width: 60%;
    margin-bottom: 1em;
}

#profile_sort li {
    margin: 0 5px 5px 5px;
    padding: 5px;
    font-size: 85%;
    height: 1.5em;
    cursor: pointer;
}
#profile_sort li:hover {
    border-color: #fcefa1;
}

.ui-state-highlight {
/*    height: 1.5em;
    line-height: 1.2em; */
}
</style>


<div id="admin_index" class="span10">
    <h4>{{ @tmp.admin.introduction }}</h4>
    <br>

    <!-- Меню с табами -->
    <ul class="nav nav-tabs" id="testr_tab">
        <li><a href="#t_config">Настройки</a></li>
        <li class="active"><a href="#t_control">Пользователи системы</a></li>
        <li><a href="#t_profile">Поля профиля</a></li>
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
// Функция конструирует поля
function set_field(field) {
    var placeholder = "";

    if (field.type == 'textarea') {
        // textarea
        return "<textarea id='field_user_profile_" + field.id + "' name='" + field.id + "' class='textarea' placeholder='" + placeholder + "'>" + field.value + "</textarea>";
    } else {
        // input
        return "<input type='" + field.type + "' id='field_user_profile_" + field.id + "' name='" + field.id + "' maxlength='" + field.maxlen + "' placeholder='" + placeholder + "' size='20' value='" + field.value + "'>";
    }
}
// Запрос на загрузку данных пользователя
var id_user = 0;
$(document).on("click", "#list_user [person]", function() {
    id_user = $(this).attr("person");

    $("#user_control").fadeOut('fast', function() {
        $(".image_loading").fadeIn('fast');

        $.post("{{ @init.sys.url }}admin/user/get",{
'id_user'    : id_user
        }, function(data) {
            $(".image_loading").fadeOut('fast', function() {
                if (data.error != undefined) {
                    air_error("#control_error", "Ошибка при выборе пользователя.", "text-center alert alert-error");
                    return;
                }

                // Генерация интерфейса данных пользователя
                // data = {
                //     name:       "user name",         field_user_name
                //     id_group:   "group name",        field_user_id_group
                //     avatar:     "avatar url",        field_user_avatar
                //     ip:         "ip addres",         field_user_ip
                //     register:   "date",              field_user_register
                //     email:      "email addres",      field_user_email
                //     profile:    {field1, field2, ...,fieldN}
                // };

                $("#user_control span#field_user_name").text(data.name);
                $("#user_control span#field_user_register").text(data.register);
                $("#user_control span#field_user_ip").text(data.ip);
                $("#user_control img#field_user_avatar").attr("src", data.url_avatar);

                $("#user_control input#field_user_avatar").val(data.avatar);
                $("#user_control input#field_user_name").val(data.name);
                $("#user_control select#field_user_id_group").val(data.id_group);
                $("#user_control input#field_user_email").val(data.email);

                $("#fields_profile").empty();
                $.each(data.profile, function(key, value) {
                    $("#fields_profile").append(
"<div class='control-group'>" +
"    <label class='control-label' for='field_user_profile_" + value.field + "'>" + value.comment + "</label>" +
"    <div class='controls'>" + set_field(value) + "</div>" +
"</div>");
                });

                $("#user_control").fadeIn('fast');
            });
        }, "json").fail(function() {
            $(".image_loading").fadeOut('fast', function() {
                air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
            });
        });

    });

});

//--------------------------------------------------------------------------------------------------
// Изменение данных пользователя
$(document).on("click", "#user_control button[name='control_ok']", function() {
    var profile = {};
    $("#user_control #fields_profile input").each(function() {
        profile[$(this).attr('name')] = $(this).val();
    });

    $(".image_loading").fadeIn('fast');

    $.post("{{ @init.sys.url }}admin/user/set", {
'id_user'   : id_user,
'name'      : $("#user_control input#field_user_name").val(),
'pass'      : $("#user_control input#field_user_pass").val(),
'id_group'  : $("#user_control select#field_user_id_group").val(),
'avatar'    : $("#user_control input#field_user_avatar").val(),
'email'     : $("#user_control input#field_user_email").val(),
'profile'   : profile
    }, function(data) {
        $(".image_loading").fadeOut('fast', function() {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при изменении данных пользователя.", "text-center alert alert-error");
                return;
            }
            air_error("#control_error", "Данные успешно изменены.", "text-center alert alert-success");
        });
    }, 'json').fail(function() {
        $(".image_loading").fadeOut('fast', function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        });
    });
});

//--------------------------------------------------------------------------------------------------

// Удаление пользователя
$(document).on("click", "#user_control button[name='control_del']", function() {
    if (!confirm("Вы действительно хотите удалить этого пользователя и всю связанные с ним информацию (включая материалы, сообщения, комментарии)?")) {
        return;
    }

    $("#user_control").fadeOut('fast', function() {
        $(".image_loading").fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/user/del", {
'id_user'   : id_user
        }, function(data) {
            if (data.error != undefined) {
                air_error("#control_error", "Ошибка при удалении пользователя.", "text-center alert alert-error");
                return;
            }
            air_error("#control_error", "Пользователь удален из системы.", "text-center alert alert-success");
        }, 'json').fail(function() {
            $(".image_loading").fadeOut('fast', function() {
                air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
            });
        }).always(function() {
            $(".image_loading").fadeOut('fast');
        });
    });
});

// Удаление аватара
$(document).on("click", "#user_control button[name='del_avatar']", function() {
    $("#user_control input#field_user_avatar").val('');
    $("#user_control img#field_user_avatar").fadeOut('fast');
});
</script>

        <div class="tab-pane active" id="t_control">

            <p>
                Начните вводить имя пользователя, после того как он появится в списке -
                кликните на нем что бы перейти к просмотру и редактированию его профиля.
            </p>

            <input type="text" name="name" id="auto_user" all="1" placeholder="Введите имя пользователя" />
            <div id="list_user" class="list_user row"></div>

            <div id="user_control">

                <h5>Профиль <span id="field_user_name"></span>. Дата регистрации: <span id="field_user_register"></span>. IP: <span id="field_user_ip"></span></h5>

                <div class="span7 form-horizontal">
                    <div class="control-group">
                        <label class="control-label" for="field_user_name">Имя пользователя</label>
                        <div class="controls"><input type="text" id="field_user_name" name="field_user_name" placeholder="" size="20" value=""></div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="field_user_id_group"></label>
                        <div class="controls">
                            <select id="field_user_id_group" name="field_user_id_group" size="1">
                                <option value="1">Администраторы</option>
                                <option value="2">Модераторы</option>
                                <option value="3">Пользователи</option>
                                <option value="4">Заблокированные</option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="field_user_email">E-mail</label>
                        <div class="controls"><input type="text" id="field_user_email" name="field_user_email" size="20" value=""></div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="field_user_pass">Поменять пароль</label>
                        <div class="controls"><input type="text" id="field_user_pass" name="field_user_pass" placeholder="" size="20" value=""></div>
                        <p class="text-info">Если вы не хотите менять пароль - оставьте это поле пустым.</p>
                    </div>

                    <hr>
                    <h5>Публичная информация.</h5>
                    <div id="fields_profile"></div>

                    <div class="control-group">
                        <div class="controls">
                            <button name="control_ok" class="btn"><span class="icon-ok"></span> Применить изменения</button>
                            <button name="control_del" class="btn"><span class="icon-ok"></span> Удалить</button>
                        </div>
                    </div>
                </div>

                <div class="span2">
                    <h5>Аватар</h5>
                    <p><img id="field_user_avatar" src="" class="img-rounded"></p>
                    <input id="field_user_avatar" type="hidden">
                    <button class="btn" name="del_avatar">Удалить аватар</button>
                </div>
            </div>

        </div>
        <!-- =================================================================================== -->

<script type="text/javascript">

// id текущего поля с которым происходит работа
var id_profile = 0;
var profile_processing;

$(function() {
    profile_processing = $("#profile_control .image_processing")[0];

    get_profile();

    $("#profile_sort").sortable({
        placeholder: "ui-state-highlight"
    });
    $("#profile_sort").disableSelection();
});

function get_profile() {
    $("#profile_empty, #profile_field_control").fadeOut('fast');

    $("#profile_control").fadeOut('fast', function() {
        $(".image_loading").fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/user/get_profile", {
'id_profile' : 0
        }, function(data) {
            // Парсинг всех полей профиля
            if (data.length == 0) {
                $("#profile_empty").fadeIn('fast');
                return;
            }

            $("#profile_sort").empty();
            $.each(data, function(key, value) {
                $("#profile_sort").append("<li id_profile='" + value.id + "' class='ui-state-default'>" + value.field + " (" + value.comment + ")</li>");
            });

            $("#profile_control").fadeIn('fast');
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        }).always(function() {
            $(".image_loading").fadeOut('fast');
        });
    });
}


$(document).on("click", "button[name='refresh'], #profile_field_control button[name='profile_field_control_abort']", function() {
    get_profile();
});

//--------------------------------------------------------------------------------------------------

// Включение интерфейса для изменения данных в поле профиля
$(document).on("click", "#profile_sort li[id_profile]", function() {
    $("#profile_empty").fadeOut('fast');
    // get_profile();
    id_profile = $(this).attr('id_profile');
    $("[name='profile_field_control_del']").fadeIn('fast');

    $("#profile_control").fadeOut('fast', function() {
        $(".image_loading").fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/user/get_profile", {
'id_profile' : id_profile
        }, function(data) {
            // if (data.error != undefined) {
            //     air_error("#control_error", "Ошибка при получении данных профиля.", "text-center alert alert-error");
            //     return;
            // }

            // Загружаем данные по профилю в поля для редактирования
            $("#profile_field_control [name='field']").val(data.field);
            $("#profile_field_control [name='comment']").val(data.comment);
            $("#profile_field_control [name='type']").val(data.type);
            $("#profile_field_control [name='maxlen']").val(data.maxlen);

            $("#profile_field_control").fadeIn('fast');
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        }).always(function() {
            $(".image_loading").fadeOut('fast');
        });
    });
});

// Для добавления нового поля профиля
$(document).on("click", "#t_profile button[name='new_profile']", function() {
    id_profile = 0;
    $("[name='profile_field_control_del'], #profile_empty, #profile_field_control").fadeOut('fast');

    $("#profile_control").fadeOut('fast', function() {
        $(".image_loading").fadeIn('fast');

        $("#profile_field_control [name='field']").val('');
        $("#profile_field_control [name='comment']").val('');
        $("#profile_field_control [name='type']").val('');
        $("#profile_field_control [name='maxlen']").val('');
        $(".image_loading").fadeOut('fast', function() {
            $("#profile_field_control").fadeIn('fast');
        });

    });
});

//--------------------------------------------------------------------------------------------------

// Отправка изменений
$(document).on("click", "#profile_field_control button[name='profile_field_control_ok']", function() {
    $("#profile_field_control").fadeOut('fast', function() {
        $(".image_loading").fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/user/set_profile", {
'id'        : id_profile,
'field'     : $("#profile_field_control [name='field']").val(),
'comment'   : $("#profile_field_control [name='comment']").val(),
'type'      : $("#profile_field_control [name='type']").val(),
'maxlen'    : $("#profile_field_control [name='maxlen']").val(),
        }, function(data) {
            if (data == null) {
                air_error("#control_error", "Изменение данных профиля прошло успешно.", "text-center alert alert-success");
                return;
            }
            air_error("#control_error", "Ошибка при изменении данных профиля.", "text-center alert alert-error");
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        }).always(function() {
            get_profile();
        });
    });
});

//--------------------------------------------------------------------------------------------------

// Удаление поля профиля
$(document).on("click", "#profile_field_control button[name='profile_field_control_del']", function() {
    if (!confirm("Вы действительно хотите удалить это поле в профиле и все данные у пользователей связанные с ним?")) {
        return;
    }

    $("#profile_field_control").fadeOut('fast', function() {
        $(".image_loading").fadeIn('fast');
        $.post("{{ @init.sys.url }}admin/user/del_profile", {
'id'        : id_profile
        }, function(data) {
            if (data == null) {
                air_error("#control_error", "Удаление данных профиля прошло успешно.", "text-center alert alert-success");
                return;
            }
            air_error("#control_error", "Ошибка при удалении данных профиля.", "text-center alert alert-error");
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке данных.", "text-center alert alert-error");
        }).always(function() {
            get_profile();
        });
    });
});

//--------------------------------------------------------------------------------------------------

// Сохраняет порядок отображения полей
$(document).on("click", "#profile_control button[name='save_sort']", function() {
    // get_profile();
    var sort = [];
    $("#profile_sort li").each(function() {
        sort.push($(this).attr("id_profile"));
    });

    $(profile_processing).fadeIn('fast', function() {
        $.post("{{ @init.sys.url }}admin/user/set_profile_sort", {
'sort' : sort
        }, function(data) {
            if (data == null) {
                air_error("#control_error", "Порядок отображения полей профиля успешно изменен.", "text-center alert alert-success");
                return;
            }
            air_error("#control_error", "Ошибка при изменении порядка отображения данных профиля.", "text-center alert alert-error");
        }, "json").fail(function() {
            air_error("#control_error", "Ошибка при обработке принятых данных.", "text-center alert alert-error");
        }).always(function() {
            $(profile_processing).fadeOut('fast');
        });
    });
});
</script>

        <div class="tab-pane" id="t_profile">

            <p>
                Редактирование полей для профиля.
                Это публичные поля и они отображаются в профилях пользователей, которые их заполнили.
            </p>

            <button name="new_profile" class="btn"><span class="icon-plus"></span> Добавить новое поле</button>
            <button name="refresh" class="btn"><span class="icon-refresh"></span> Обновить</button>

            <div id="profile_control">
                <ul id="profile_sort"></ul>
                <button name="save_sort" class="btn"><span class="icon-random"></span> Сохранить позиции</button>
                <div class="image_processing"></div>
            </div>
            <h4 id="profile_empty">Поля для профиля отсутствуют</h4>

            <hr>

            <div id="profile_field_control" class="form-horizontal">

                <div class="control-group">
                    <label class="control-label" for="field_profile_field">Название поля</label>
                    <div class="controls"><input type="text" id="field_profile_field" name="field" placeholder="" size="20" value=""></div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_profile_comment">Описание</label>
                    <div class="controls"><input type="text" id="field_profile_comment" name="comment" placeholder="" size="20" value=""></div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_profile_type">Тип</label>
                    <div class="controls">
                        <select id="field_profile_type" name="type" size="1">
                            <option value="text">Простое текстовое поле</option>
                            <option value="textarea">Большое текстовый блок</option>
                            <option value="date">Поле для выбора календарной даты</option>
                            <option value="email">Для адресов электронной почты</option>
                            <option value="number">Ввод чисел</option>
                            <!-- <option value="range">Ползунок для выбора чисел в указанном диапазоне</option> -->
                            <option value="search">Поле для поиска</option>
                            <option value="tel">Для телефонных номеров</option>
                            <option value="url">Для веб-адресов</option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="field_profile_maxlen">Максимальная длина</label>
                    <div class="controls"><input type="number" id="field_profile_maxlen" name="maxlen" placeholder="" size="20" value=""></div>
                </div>

                <hr>
                <div class="control-group">
                    <div class="controls">
                        <button name="profile_field_control_ok" class="btn"><span class="icon-ok"></span> Применить</button>
                        <button name="profile_field_control_abort" class="btn"><span class="icon-remove"></span> Отмена</button>
                        <button name="profile_field_control_del" class="btn"><span class="icon-minus"></span> Удалить</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- =================================================================================== -->
    </div>
    <!-- ================ -->

<div class="image_loading"></div>
<h5 id="control_error"></h5>
</div>