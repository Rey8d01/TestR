<repeat group="{{ @tmp.navigate }}" value="{{ @li }}">
    <check if="{{ @li.dropdown }}">
        <true>
            <li class="dropdown {{ @li.class }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ @li.title }} <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <repeat group="{{ @li.dropdown }}" value="{{ @sub_li }}">
                        <li class="{{ @sub_li.class }}">{{ @sub_li.link | raw}}</li>
                    </repeat>
                </ul>
            </li>
        </true>
        <false>
            <li class="{{ @li.class }}"><a href="{{ @li.href }}">{{ @li.title }}</a></li>
        </false>
    </check>
</repeat>