<h2 class="category_header">{{ @tmp.category.title }}</h2>

<check if="{{ @tmp.category.list_sub }}">
    <true>
        <ul class="list_category nav nav-pills">
            <li class="category_separate">&raquo;</li>
            <repeat group="{{ @tmp.category.list_sub }}" value="{{ @sub }}">
                <li><a href="{{ @sub.url }}">{{ @sub.title }}</a></li>
                <li class="category_separate">|</li>
            </repeat>
        </ul>
    </true>
</check>
<h6>{{ @tmp.category.path | raw }}</h6>

{{ @tmp.category.desc | raw }}

<include href="{{ @tmp.category.list_content }}" />