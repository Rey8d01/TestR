<h4 class="nav-header">Управление данными</h4>
<check if="{{ @tmp.setting.list_tables }}">
    <true>
        <repeat group="{{ @tmp.setting.list_tables }}" value="{{ @table }}">
            <li class="<check if="{{ @table.active }}"><true> active</true></check><check if="{{ @table.disabled }}"><true> disabled</true></check>">
            {{ @table.link | raw }}</li>
        </repeat>
    </true>
    <false>
        <li>Пусто? :(</li>
    </false>
</chek>