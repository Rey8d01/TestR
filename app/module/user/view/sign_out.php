<script language="JavaScript" type="text/javascript">
/* Выход из системы */
$(document).on('click', "button[name='sign_out']", function() {
    delete get_new_message;

    $("#navbar_result").fadeOut('fast', function() {
        $.post("{{ @init.sys.url }}main/user/sign_out", {},
        function(data) {
            if (data.error != undefined) {
                $("#navbar_result").text("При выходе произошла ошибка.").fadeIn('fast');
                return;
            }
            // $("#navbar_result").text("Вы вышли из системы. Перезагрузка...").fadeIn('fast');
            // setTimeout("location.reload();", 1350);
            location.reload();
        }, "json").fail(function() {
            $("#navbar_result").text("Ошибка при обработке принятых данных.").fadeIn('fast');
        });
    });
});

/* Проверка на наличие непрочитанных сообщений */
// var amount_message = 0;
function get_new_message() {
    var person = 0;
    if (typeof $("input#person").val() != 'undefined') {
        person = $("input#person").val();
    }

    $.post("{{ @init.sys.url }}main/user/get_new_message", {
'person'  : person
    }, function(data) {
        if (data.error != undefined) {
            return;
        }

        if (data.amount == 0) {
            $("#navbar_result").fadeOut('fast');
        } else if (data.amount > 0) {
            $("#navbar_result").fadeOut('fast', function() {
                $("#navbar_result").html("<a href='{{ @init.sys.url}}main/user/list_talk'>У вас есть новые сообщения (" + data.amount + ")</a>").fadeIn('fast');
            });
        }

        // amount_message = data.amount;
    }, "json");
    setTimeout("get_new_message();", 5000);
}

$(function () {
    get_new_message();
})

</script>

<form class="navbar-form pull-right ">
    &nbsp;
    <img class="btn-group img-rounded" src="{{ @tmp.user.i.src.navbar }}" height="30">
    <a class="btn" href="{{ @init.sys.url }}main/user/mine" title="Профиль"><span class="icon-user"></span> {{ @tmp.user.i.name }}</a>
    <button name="sign_out" type="button" class="btn"><span class="icon-home"></span> Выход</button>
</form>
