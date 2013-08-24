<check if="{{ @tmp.content.list }}">
    <true>
        <div class="list_content">
            <repeat group="{{ @tmp.content.list }}" value="{{ @content }}">
                <div class="content">
                    <div class="content_header">
                        <h3>{{ @content.title | raw }}</h3>
                    </div>
                    <hr>
                    {{ @content.desc | raw }}
                    <p class="readmore"><strong><a href="{{ @content.url }}">подробнее...</a></strong></p>
                    <div class="content_footer">
                        <small>
                            {{ @content.date }} |
                            Автор: {{ @content.user | raw }} |
                            Комментариев {{ @content.comment }} |
                            Просмотров: {{ @content.view }} |
                            Категория: {{ @content.category | raw }}
                        </small>
                    </div>
                </div>
            </repeat>
        </div>
    </true>
    <false>
        <h4>Пока что новостей сюда не добавили...</h4>
    </false>
</check>

<check if="{{ @tmp.content.pages }}">
    <true>
        <div class="">
            <repeat group="{{ @tmp.content.pages }}" value="{{ @page }}">
                <a href="{{ @init.sys.url }}main/category/get/{{ @tmp.category.id }}/{{ @page.page }}" class="btn {{ @page.class ? 'btn-info' : '' }}">{{ @page.num }}</a>
            </repeat>
        </div>
    </true>
</check>