<div id="content">
    <div id="content_header">
        <h2>{{ @tmp.content.title }}</h2>
        <h6>{{ @tmp.content.path | raw }}</h6>
    </div>

{{ @tmp.content.desc | raw }}

    <check if="{{ @tmp.content.photos }}">
        <true>
            <div id="gp-gallery">
                <repeat group="{{ @tmp.content.photos }}" value="{{ @item }}">
                    <img src="{{ @item.photo }}" height="{{ @item.height }}" width="{{ @item.width }}">
                </repeat>
                <div class="clearfix"></div>
            </div>
        </true>
    </check>

    <hr>
    <div class="content_footer">
        <small>{{ @tmp.content.date }} | Автор: {{ @tmp.content.user | raw }} | Комментариев {{ @tmp.content.comment }} | Просмотров: {{ @tmp.content.view }} | Категория: {{ @tmp.content.category | raw }}</small>
    </div>

    <check if="{{ @tmp.content.list_comment }}">
        <true>
            <include href="app/module/content/view/form_add_comment.php" />
            <div class="list_comment">
                <h5>Комментарии</h5>
                <repeat group="{{ @tmp.content.list_comment }}" value="{{ @comment }}">
                    <fieldset class="comment">
                        <legend><img src="{{ @comment.avatar }}" height="30"><small> {{ @comment.user | raw }}, {{ @comment.created }}</small></legend>
                        <p>{{ @comment.text }}</p>
                    </fieldset>
                </repeat>
            </div>
        </true>
        <false>
            <h4>Комментариев к материалу нет. Нечего сказать?</h4>
            <include href="app/module/content/view/form_add_comment.php" />
        </false>
    </check>
</div>