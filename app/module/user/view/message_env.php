<script type="text/javascript">
/* Отключаем у ссылок прямой переход по клику - пусть разговор подгружается через ajax*/
$(document).on("click", "a[person]", function() {
    return false;
});

/* Переход к разговору */
$(document).on("click", "[person], div#person", function() {
    // Для кнопок которые что нибудь удаляют определен ид и для него действия определены другие
    if ($(this).attr('id') == 'delete') {
        return false;
    }
    // Приостанавливаем обновление сообщениями, если таковое и было
    if (typeof repeater != 'undefined') {
        clearTimeout(repeater);
        delete repeater;
    }

    $("#dialog").modal('hide');

    var person = $(this).attr('person');
    $("#form_talk").hide('fade', 'fast', function() {
        $("#rotor").show('fade', 'fast', function() {
            $.post("{{ @init.sys.url }}main/user/talk/" + person, {}, function(data) {
                $("#rotor").hide('fade', 'fast', function() {
                    $("#form_talk").empty().html(data.table).show('fade', 'fast');
                    $("#message").focus();
                });
            }, "json").fail(function() {
                air_error("#error_message", "Ошибка при обработке принятых данных.", "text-error");
            });
        });
    });
});

/* Отправка сообщения */
function send_message() {
    var message = $("#message").val();
    $("#message").val('').attr('rows', 1);

    $.post("{{ @init.sys.url }}main/user/new_message", {
'person'    : $("input#person").val(),
'message'   : message
    }, function(data) {
        if (data.error != undefined) {
            air_error("#error_message", "При отправке сообщения возникла ошибка, попробуйте еще раз.", "text-error");
        }
    }, "json").fail(function() {
        air_error("#error_message", "Ошибка при обработке принятых данных.", "text-error");
    });
}

/* - через нажатый Enter */
$(document).on("keypress", "#message", function(e) {

    if ((e.keyCode == 13) && (!e.shiftKey)) {
        if ($("#message").val() != '') {
            send_message();
        }

        return false;
    }

    if ((e.keyCode == 13) && (e.shiftKey)) {
        var rows = Number($("#message").attr('rows'));
        $("#message").attr('rows', rows + 1);
    }

});

/* - через нажатую кнопку */
$(document).on("click", "button[name='send_message']", send_message);

/* Удаление сообщения */
$(document).on("click", "span#delete[message]", function() {
    var tr = $(this).parent("td").parent("tr");

    $.post("{{ @init.sys.url }}main/user/del_message", {
'id_message'   : $(this).attr('message')
    }, function(data) {
        if (data.error != undefined) {
            air_error("#error_message", "Ошибка при удалении сообщения.", "text-error");
            return;
        }

        $(tr).hide('fade', 'fast', function() {
            $(this).remove();
        });
    }, "json").fail(function() {
        air_error("#error_message", "Ошибка при обработке принятых данных.", "text-error");
    });

});

/* Удаление разговора */
$(document).on("click", "span#delete[person]", function() {
    if (!confirm("Желаете удалить все сообщения с этим человеком?")) {
        return false;
    }

    var tr = $(this).parents('tr')[0];
    var person = $(this).attr('person');

    if ($.isNumeric($("input#person").val())) {
        $("#form_talk").hide('fade', 'fast', function() {
            $("#rotor").show('fade', 'fast', function() {
                $.post("{{ @init.sys.url }}main/user/del_talk", {
'person'    : person,
'table'     : true
                }, function(data) {
                    if (data.success) {
                        $("#rotor").hide('fade', 'fast', function() {
                            $("#form_talk").html(data.table).show('fade', 'fast');
                        });
                        return;
                    }
                    air_error("#error_message", "Произошла ошибка при удалении разговора.", "text-error");
                }, "json").fail(function() {
                    air_error("#error_message", "Ошибка при обработке принятых данных.", "text-error");
                });
            });
        });
    } else {
        $.post("{{ @init.sys.url }}main/user/del_talk", {
'person'    : person,
'table'     : false,
        }, function(data) {
            if (data.success) {
                $(tr).hide('fade', 'fast', function() {
                    $(this).remove();
                });
            } return;
            air_error("#error_message", "Произошла ошибка при удалении разговора.", "text-error");
        }, "json").fail(function() {
            air_error("#error_message", "Ошибка при обработке принятых данных.", "text-error");
        });
    }
});

/* Функция для получения сообщений */
function exchange() {
    $.post("{{ @init.sys.url }}main/user/exchange", {
'person'    : $("input#person").val(),
'last_date' : $("input#last_date").val()
    }, function(data) {
        if (data.error != undefined) {
            air_error("#error_message", "Ошибка при получении сообщений.", "text-error");
            return;
        }

        // Данные успешно приняты
        if (data.new) {
            // Имеются свежие сообщения
            $.each(data.talk, function(key, value) {
                $("#talk>tbody").append("<tr><td>" + value['sender'] + " <small>(" + value['date'] + ")</small>&raquo; " + value['message'] + "<span id='delete' message='" + key + "' class='icon-remove close' title='Удалить сообщение'></span></td></tr>");
            });
            $("input#last_date").val(data.last_date);
            // Двигаем скроллбар к последнему сообщению
            $("#scrollbar").prop('scrollTop', $("#scrollbar").prop('scrollHeight'));
        }
        repeater = setTimeout("exchange();", 500);
    }, "json").fail(function() {
        air_error("#error_message", "Ошибка при обработке принятых данных.", "text-error");
    });
}

/* Вызов диалогового окна для создания новой беседы */
function new_talk() {
    $("#dialog .modal-body, #dialog .modal-footer, #dialog #dialog_label").empty();
    $("#dialog #dialog_label").text('Новый собеседник');
    $("#dialog .modal-body.data").html(
"<p>Введите имя пользователя с которым хотите поговорить.</p>" +
"<p><input type='text' name='name' all='0' id='auto_user' /></p>" +
"<div id='list_user' class='row'></div>");

    $("#dialog .modal-footer").html(
"<button class='btn' data-dismiss='modal' aria-hidden='true'>Отмена</button>");
    $("#dialog").modal('show');
}
</script>

<style>
#list_talk th:first-of-type, #list_talk tr td:first-of-type {
    /*width: 130px;*/
}

#list_talk thead th, #list_talk tfoot td, #list_talk>tbody>tr>td:first-of-type, #list_person td, #person {
    text-align: center;
}

span[message] {
    margin-top: 4px;
    float: right;
    display: none;
}

#talk>tbody tr td:hover span[message] {
    display: block;
}

/* этот див содержит в себе только фотографию и кнопку удаления - определим его размер по содержимому контента - т.е. Фотки
кнопка удаления автоматически впишется в фотографию
*/
a[person] div {
    margin-right: auto;
    margin-left: auto;
    display: inline-block;
}

span[person] {
    float: right;
    display: none;
    position: absolute;
    cursor: pointer;
}

tr[person]:hover span[person], th[person]:hover span[person] {
    display: block;
}

a[person]:hover {
    text-decoration: none;
}

a[person]:hover small {
    text-decoration: underline;
}
</style>

<div id="rotor" class="text-center hide"><img src="{{ @init.sys.include }}images/rotor.gif" /></div>

<h4 id="error_message" class="text-error hide"></h4>

<div id="form_talk">
    <include href="{{ @tmp.user.form_talk }}" />
</div>