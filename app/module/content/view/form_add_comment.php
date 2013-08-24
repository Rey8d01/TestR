<check if="{{ @tmp.content.form_comment }}">
    <true>

<script type="text/javascript">
$(document).on('click', "button[name='add_comment']", function() {
    $.post("{{ @init.sys.url }}main/content/add_comment", {
'id'        : "{{ @tmp.content.id }}",
'message'   : $("[name='message']").val()
    }, function(data) {
        if (data.error != undefined) {
            air_error("#result_comment", "Ошибка при добавлении комментария.", "alert alert-error");
            return;
        }
        location.reload();
        // air_error("#result_comment", "Комментарий успешно добавлен.", "alert alert-success");
        // setTimeout("location.reload();", 1350);
    }, "json").fail(function() {
        air_error("#result_comment", "Ошибка при обработке принятых данных.", "alert alert-error");
    });
});
</script>

<div class="form_add_comment">
    <h4>Ваш комментарий</h4>
    <br />
    <textarea name="message" rows="3" class="span6"></textarea>
    <br />
    <p><button name="add_comment" type="button" class="btn btn-primary"><span class="icon-plus-sign"></span> <span class="btn-text">Добавить комментарий</span></button></p>
    <p id="result_comment" class="hide"></p>
</div>

    </true>
    <false>
        <h4>Вы не можете оставлять комментарии.</h4>
    </false>
</check>