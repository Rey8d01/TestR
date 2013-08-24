<script language="JavaScript" type="text/javascript">
$(document).on("click", "#admin_config button[option='config_ok']", function() {
    var control = $(this).parents("#admin_config")[0];
    var processing = $(control).find(".image_processing")[0];

    // Собираем конфиг по полям формы
    var config = {};
    $(control).find("[name][type!='hidden']").each(function() {
        config[$(this).attr('name')] = $(this).val();
    });

    $(processing).fadeIn('fast');
    $.post("{{ @init.sys.url }}admin/index/set_config",{
'module'    : "{{ @tmp.admin.module }}",
'config'    : config,
    }, function(data) {
        $(processing).fadeOut('fast', function() {
            if (data.error != undefined) {
                air_error("#config_error", "При внесении изменений произошла ошибка", "text-center alert alert-error");
            } else {
                air_error("#config_error", "Изменение конфигурации прошло успешно", "text-center alert alert-success");
            }
        });
    }, "json").fail(function() {
        air_error("#config_error", "Ошибка при обработке принятых данных", "text-center alert alert-error");
    });
});
</script>

<div id="admin_config" class="form-horizontal">
    <check if="{{ @tmp.admin.config }}">
        <true>
            <repeat group="{{ @tmp.admin.config }}" value="{{ @item }}">
                <div class="control-group">
                    <label class="control-label" for="field_limit">{{ @item.desc }}</label>
                    <div class="controls">
                        <input type="text" placeholder="{{ @item.default }}" name="{{ @item.variable }}" size="20" value="{{ @item.value }}">
                    </div>
                </div>
            </repeat>
            <hr>
            <div class="control-group">
                <div class="controls">
                    <button option='config_ok' class='btn'><span class='icon-ok'></span> Сохранить</button>
                    <div class="image_processing"></div>
                </div>
            </div>
        </true>
        <false>
            <p>Настроек для данного модуля нет.</p>
        </false>
    </check>
</div>

<h5 id="config_error"></h5>