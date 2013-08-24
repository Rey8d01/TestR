<script language="JavaScript" type="text/javascript">

</script>

<div class="row">
    <div class="span10 row">
        <h5>Профиль пользователя, {{ @tmp.user.profile.name }}. Дата регистрации: {{ @tmp.user.profile.register }}</h5>

        <div class="span10">
            <check if="{{ @tmp.user.profile.list_field }}">
                <true>
                    <repeat group="{{ @tmp.user.profile.list_field }}" value="{{ @field }}">
                        <label class="control-label">{{ @field.field }}</label>
                        <span class="controls">{{ @field.value }}</span>
                    </repeat>
                </true>
                <false>
                    <h5>Публичная информация сейчас не доступна.</h5>
                </false>
            </check>

            <br>
        </div>

        <div class="span10">
        <check if="{{ @tmp.user.profile.id == @tmp.user.i.id }}">
            <true>
                <p>Это вы ;)</p>
            </true>
            <false>
                <a class="btn btn-primary" href="{{ @init.sys.url }}main/user/talk/{{ @tmp.user.profile.id }}">Отправить сообщение</a>
            </false>
        </check>
        </div>
    </div>

    <div class="span2 text-center">
        <h5>Аватар</h5>
        <p><img src="{{ @tmp.user.profile.url_avatar }}" class="img-rounded"></p>
    </div>
</div>