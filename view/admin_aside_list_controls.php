<h4 class="nav-header">Панель управления</h4>
<check if="{{ @tmp.admin.list_controls }}">
    <true>
        <repeat group="{{ @tmp.admin.list_controls }}" value="{{ @controls }}">
            <li class="<check if="{{ @controls.active }}"><true> active</true></check>">
            {{ @controls.link | raw }}</li>
        </repeat>
    </true>
    <false>
        <li>Пусто? :(</li>
    </false>
</chek>