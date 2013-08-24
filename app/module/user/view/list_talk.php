<check if="{{ count(@tmp.user.list_talk) }}">
    <true>
        <table id="list_talk" class="table table-hover">
            <caption>
                Ваши беседы
            </caption>
            <thead>
                <th class="span1">Пользователь</th>
                <th class="span11">Последнее сообщение</th>
            </thead>
            <tbody>
                <repeat group="{{ @tmp.user.list_talk }}" value="{{ @talk }}">
                    <tr person="{{ @talk.person }}">
                        <td>
                            <a person="{{ @talk.person }}" href="{{ @talk.link }}">
                                <div>
                                    <span id="delete" person="{{ @talk.person }}" class='icon-remove' title='Удалить разговор'></span>
                                    <img src="{{ @talk.avatar }}">
                                </div>
                                <br>
                                <small>
                                    <check if="{{ @talk.showed }}">
                                        <true>{{ @talk.user_name }}</true>
                                        <false><b>{{ @talk.user_name }}</b></false>
                                    </check>
                                </small>
                            </a>
                        </td>
                        <td>
                            <check if="{{ @talk.showed }}">
                                <true>
                                    {{ @talk.last_message.user_link | raw }} <small>({{ @talk.last_message.last_date }})</small>&raquo; {{ @talk.last_message.text }}
                                </true>
                                <false>
                                    <b>{{ @talk.last_message.user_link | raw }} <small>({{ @talk.last_message.last_date }})</small>&raquo; {{ @talk.last_message.text }}</b>
                                </false>
                            </check>
                        </td>
                    </tr>
                </repeat>
            </tbody>
            <tfoot>
                <tr>
                    <td><a href="#" onclick="new_talk();"><img src="{{ @init.sys.include }}icon/1/64x64/add.png" class="img-rounded"><br><small>Добавить собеседника</small></a></td>
                </tr>
            </tfoot>
        </table>

    </true>
    <false>
        <h5>У вас нет ни одной активной беседы, желаете <a href="#" onclick="new_talk();">начать</a>?</h5>
    </false>
</check>