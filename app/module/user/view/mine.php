<script language="JavaScript" type="text/javascript">

$(document).on('click', "button[name='change_profile']", function() {
    // Валидация пароля и повтора
    if ($("input[name='new_pass']").val() != $("input[name='repeat_pass']").val()) {
        $("#new_password").addClass("error");
        $("p#repeat_pass").fadeIn('fast');
        return;
    }
    $("#new_password").removeClass("error");
    $("p#repeat_pass").fadeOut('fast');

    $(".result_change_profile img").fadeIn('fast');

    var profile = {};
    $("#fields_profile input").each(function() {
        profile[$(this).attr('name')] = $(this).val();
    });

    $.post("{{ @init.sys.url }}main/user/change_profile", {
'email'         : $("input[name='email']").val(),
'avatar'        : $("input[name='avatar']").val(),
'profile'       : profile,
'new_pass'      : $("input[name='new_pass']").val(),
'repeat_pass'   : $("input[name='repeat_pass']").val()
    }, function(data) {
        if (data.error != undefined) {
            air_error(".result_change_profile span", "Ошибка при изменении данных профиля.", "text-error");
            return;
        }
        air_error(".result_change_profile span", "Изменения применены успешно.", "text-success");
    }, "json").fail(function() {
        air_error(".result_change_profile span", "Ошибка при обработке принятых данных.", "text-error");
    }).always(function() {
        $(".result_change_profile img").fadeOut('fast');
    });
});

$(function () {
    $('#fileupload').fileupload({
        url: '{{ @init.sys.url }}main/user/change_avatar',
        dataType: 'json',
        done: function (e, data) {
            $('.progress .bar').text('Загружено');
            $.each(data.result.files, function (index, file) {
                $('#avatar').attr('src', file.avatarUrl);
                $("input[name='avatar']").val(file.name);
            });
            $("#change_avatar").fadeIn('fast');
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

</script>

<div class="row">
    <div class="span10 row">
        <h5>{{ @tmp.user.i.name }}. Дата регистрации: {{ @tmp.user.i.register }}</h5>

        <div class="span10">
            <label class="control-label">E-mail</label>
            <div class="controls">
                <input name="email" class="span3" type="email" placeholder="E-mail" value="{{ @tmp.user.i.email }}">
                <input name="avatar" class="span3" type="hidden" value="{{ @tmp.user.i.avatar }}">
            </div>
            <label class="control-label">Поменять пароль</label>
            <div id="new_password" class="control-group">
                <div class="controls">
                    <p><input name="new_pass" class="span3" type="password" placeholder="Новый пароль"></p>
                    <p><input name="repeat_pass" class="span3" type="password" placeholder="Повтор пароля"></p>
                    <p class="text-info">Если вы не хотите менять пароль - оставьте эти поля пустыми.</p>
                    <p id="repeat_pass" class="help-inline hide">Ваш новый пароль не совпадает с повторно введенным.</p>
                </div>
            </div>
            <br>
        </div>

        <h5>Публичная информация - она будет отображаться для всех кто посещает ваш профиль.</h5>

        <div class="span10" id="fields_profile">
            <check if="{{ count(@tmp.user.i.profile) }}">
                <true>
                    <repeat group="{{ @tmp.user.i.profile }}" value="{{ @item }}">
                        <label class="control-label" for="profile_{{ @item.id }}">{{ @item.field }}</label>
                        <div class="controls">
                            <input name="{{ @item.id }}" class="span3" type="{{ @item.type }}" placeholder="{{ @item.comment }}" id="profile_{{ @item.id }}" value="{{ @item.value }}">
                        </div>
                    </repeat>
                </true>
                <false>
                    <h5>Публичная информация сейчас не доступна.</h5>
                </false>
            </check>
            <br>
        </div>

        <div class="span10">
            <div class="result_change_profile">
                <button name="change_profile" class="btn btn-primary">Применить изменения</button>
                <img src="{{ @init.sys.include }}images/loading.gif" class="hide" />
                <span></span>
            </div>
        </div>
    </div>

    <div class="span2 text-center">
        <h5>Аватар</h5>
        <p><img id="avatar" src="{{ @tmp.user.i.src.avatar }}" class="img-rounded"></p>

        <p>
        <span class="btn btn-primary fileinput-button">
            <span>Загрузить аватар</span>
            <input id="fileupload" type="file" name="files[]">
        </span>
        </p>

        <div class="progress">
            <div class="bar"></div>
        </div>

        <p id="change_avatar" class="text-info hide">Не забудьте применить изменения.</p>
    </div>
</div>

