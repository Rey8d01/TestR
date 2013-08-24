<script type="text/javascript">
$(function() {
    exchange();
    // repeater = setTimeout("exchange();",1);
});
</script>

<table id="list_talk" class="table">
    <thead>
    <th class="span1">Ваши собеседники</th>
    <th class="span11">Беседа с {{ @tmp.user.link_person | raw }}</th>
    </thead>
    <tbody>
        <tr>
            <td>

<table id="list_person" class="table table-bordered table-hover">

    <thead>
        <th person="{{ @tmp.user.talk_person.person }}">
            <a person="{{ @tmp.user.talk_person.person }}" href="{{ @tmp.user.talk_person.link }}">
                <div>
                    <span id="delete" person="{{ @tmp.user.talk_person.person }}" class='icon-remove' title='Удалить разговор'></span>
                    <img src="{{ @tmp.user.talk_person.avatar }}">
                </div>
                <br>
                <small>{{ @tmp.user.talk_person.user_name }}</small>
            </a>
        </th>
    </thead>

<check if="{{ count(@tmp.user.list_person) }}">
    <true>
        <tbody>
            <repeat group="{{ @tmp.user.list_person }}" value="{{ @talk }}">
                <tr person="{{ @talk.person }}">
                    <td>
                        <a person="{{ @talk.person }}" href="{{ @talk.link }}">
                            <div>
                                <span id="delete" person="{{ @talk.person }}" class='icon-remove' title='Удалить разговор'></span>
                                <img src="{{ @talk.avatar }}">
                            </div>
                            <br>
                            <check if="{{ @talk.showed }}">
                                <true>
                                    <small>{{ @talk.user_name }}</small>
                                </true>
                                <false>
                                    <small><b>{{ @talk.user_name }}</b></small>
                                </false>
                            </check>
                        </a>
                    </td>
                </tr>
            </repeat>
        </tbody>
    </true>
</check>

    <tfoot>
        <tr>
            <td><a href="#" onclick="new_talk();"><img src="{{ @init.sys.include }}icon/1/64x64/add.png" class="img-rounded"><br><small>Добавить собеседника</small></a></td>
        </tr>
    </tfoot>
</table>

            </td>
            <td >

<div id="scrollbar" class="pre-scrollable">
    <table id="talk" class="table table-hover"><tbody></tbody></table>
</div>

<div class="form-horizontal text-center">
    <textarea id="message" rows="1" class="span8"></textarea>
    <button name="send_message" class="btn btn-primary"><span class="icon-plus"></span> Отправить</button>
    <p><small>Переход на новую строку Shift + Enter</small></p>
    <input id="person" type="hidden" value="{{ @tmp.user.id_person }}">
    <input id="last_date" type="hidden" value="0">
</div>

            </td>
        </tr>
    </tbody>
</table>