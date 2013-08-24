<script language="JavaScript" type="text/javascript">
$(document).on('click', "button[name='sign_up']", function() {
    $("#dialog .modal-body, #dialog .modal-footer, #dialog #dialog_label").empty();
    $("#dialog #dialog_label").text('Регистрация');
    $("#dialog .modal-body.data").html(
"<p>Для регистрации заполните все поля.</p>" +
"<fieldset>" +
"    <label for='name'>Имя</label>" +
"    <input type='text' name='name' id='name' />" +
"    <label for='email'>Адрес электронной почты</label>" +
"    <input type='email' name='email' id='email' value='' />" +
"    <label for='pass'>Пароль</label>" +
"    <input type='password' name='pass' id='password' value='' />" +
"</fieldset>");
    $("#dialog .modal-footer").html(
"<button class='btn' data-dismiss='modal' aria-hidden='true'>Отмена</button>" +
"<button class='btn btn-primary' onclick='sign_up();'>Ок</button>");
    $("#dialog").modal('show');
});

function sign_up() {
    $(".modal-body.result").empty().fadeOut('fast');

    $.post("{{ @init.sys.url }}main/user/sign_up", {
'name'  : $("#dialog input[name='name']").val(),
'pass'  : $("#dialog input[name='pass']").val(),
'email' : $("#dialog input[name='email']").val()
    }, function(data) {
        if (data.error != undefined) {
            $(".modal-body.result").text('Ошибка при регистрации, попробуйте еще раз.');
        } else {
            $(".modal-body.result").text("Вы успешно зарегистрировались. Перезагрузка...");
            setTimeout("location.reload();", 1350);
        }
    }, "json").fail(function() {
        $(".modal-body.result").text('Ошибка при обработке принятых данных.');
    }).always(function() {
        $(".modal-body.result").fadeIn('fast');
    });
}

$(document).on('mouseenter', "button[name='sign_up']", function() {
    $("span.sign_in").fadeOut('fast');
    $(this).children(".btn-text").text(" Зарегистрироваться");
    $("button[name='sign_in'] .btn-text").text('');
});

$(document).on('mouseenter', "button[name='sign_in']", function() {
    $("span.sign_in").fadeIn('fast');
    $(this).children(".btn-text").text(" Войти");
    $("button[name='sign_up'] .btn-text").text('');
});

$(document).on("submit", "form[name='panel_user']", function() {
    $("form[name='panel_user'] span.sign_in, form[name='panel_user'] button").fadeOut('fast');
    $("#navbar_result").text("Ожидайте...").fadeIn('fast');

    $.post("{{ @init.sys.url }}main/user/sign_in", {
'name'  : $("form[name='panel_user'] input[name='name']").val(),
'pass'  : $("form[name='panel_user'] input[name='pass']").val()
    }, function(data) {
        if (data.error != undefined) {
            $("#navbar_result").text("Кажется, вы ввели неверное имя или пароль.");
            $("form[name='panel_user'] button").fadeIn('fast');
            return;
        }

        location.reload();
        // $("#navbar_result").text("Вы успешно вошли. Перезагрузка...");
        // setTimeout("location.reload();", 1350);
    }, "json").fail(function() {
        $("#navbar_result").text("Ошибка при обработке принятых данных.");
        $("form[name='panel_user'] button").fadeIn('fast');
    });

    return false;
});
</script>

<form name="panel_user" action="#" method="POST" class="navbar-form pull-right">
    &nbsp;
    <span class="sign_in" style="display: none;">
        <input name="name" class="span2" type="text" placeholder="Ваше имя в системе">
        <input name="pass" class="span2" type="password" placeholder="Пароль">
    </span>
    <button name="sign_in" type="submit" class="btn"><span class="icon-user"></span> <span class="btn-text"></span></button>
    <button name="sign_up" type="button" class="btn"><span class="icon-tag"></span> <span class="btn-text"></span></button>
</form>