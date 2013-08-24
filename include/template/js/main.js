try {
    var wysiarea = $(".textarea").wysihtml5().data("wysihtml5").editor;
} catch (e) {

}

$("#myModal").modal({
     backdrop: true,
     show: false
});

$(function() {
    $("#gp-gallery").gpGallery('img');

    $('#myCarousel').carousel({
      interval: 7000
    });
});

/* Автокомплит пользователей */
var auto_user = '';
$(document).on("keyup", "#auto_user", function() {
    if ((auto_user == $("#auto_user").val()) || ($("#auto_user").val().length < 2)) {
        return;
    }
    auto_user = $("#auto_user").val();

    var all = $(this).attr('all');
    $("#list_user").hide('fade', 'fast', function() {
        $("#list_user").empty();
        $.post(ajax_get_list_user, {
'sym'   : auto_user,
'all'   : all,
        }, function(data) {
            var response = $.parseJSON(data);
            $.each(response.list_user, function(key, value) {
                $("#list_user").append("<div id='person' class='span1' person='" + key + "'><img src='" + value['avatar'] + "''><br><small>" + value['name'] + "</small></div>");
            });
            $("#list_user").show('fade', 'fast');
        });
    });
});

/* Переключение табов */
$('#testr_tab a').click(function (e) {
  e.preventDefault();
  $(this).tab('show');
})

// Всплывающее сообщение об ошибке
function air_error(id, text, attr) {
    if (attr == undefined) {
        attr = '';
    }
    $(id).attr("class", attr).text(text).fadeIn('fast', function() {
        setTimeout("air_error_hide('" + id + "');", 3500);
    });
}

function air_error_hide(id) {
    $(id).fadeOut('fast', function() {
        $(id).text('');
    });
}