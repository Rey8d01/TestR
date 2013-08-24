<!DOCTYPE html>
    <head>
        <meta charset="{{ @init.charset }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>{{ @init.page_title }}</title>
        <meta name="description" content="{{ @init.metadesc}}">
        <meta name="viewport" content="width=device-width">

        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <!-- <link rel="stylesheet" href="{{ @init.sys.include }}template/css/bootstrap.min.css"> -->
        <link rel="stylesheet" href="{{ @init.sys.include }}template/css/bootstrap.css">
        <link rel="stylesheet" href="{{ @init.sys.include }}template/css/bootstrap-responsive.min.css">
        <link rel="stylesheet" href="{{ @init.sys.include }}template/css/jquery-ui.css">
        <link rel="stylesheet" href="{{ @init.sys.include }}template/css/main.css">

        <link rel="stylesheet" href="{{ @init.sys.include }}wysihtml5/prettify.css">
        <link rel="stylesheet" href="{{ @init.sys.include }}wysihtml5/bootstrap-wysihtml5.css">
        <link rel="stylesheet" href="{{ @init.sys.include }}wysihtml5/wysiwyg-color.css">

        <link rel="stylesheet" href="{{ @init.sys.include }}gp-gallery/gp-gallery.css">

        <link rel="stylesheet" href="{{ @init.sys.include }}colorbox/colorbox.css">

        <script src="{{ @init.sys.include }}template/js/vendor/jquery.min.js"></script>
        <script>
            // Links API
            var ajax_get_list_user = "{{ @tmp_ajax_user_get_list_user }}";
        </script>
        <style type="text/css">
            .image_loading {
                background-image: url("{{ @init.sys.include }}images/rotor.gif");
                background-repeat: no-repeat;
                margin: 1em auto 1em auto;
                height: 60px;
                width: 60px;
                display: none;
            }

            .image_processing {
                background-image: url("{{ @init.sys.include }}images/loading.gif");
                background-repeat: no-repeat;
                height: 15px;
                weight: 15px;
/*                padding: 0.7em;
                margin: 1em;*/
                display: none;
            }
        </style>
    </head>
    <body>
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="{{ @init.sys.url }}">{{ @init.site_name }}</a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <include href="app/module/navigate/view/top_menu.php" />
                        </ul>
                        <include href="{{ @tmp.user.panel }}" />
                        <span id="navbar_result" class="navbar-text pull-right"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">

            <include href="{{ @tmp.hero }}" />

            <div class="row">
                <div class="span12">
                    <include href="{{ @tmp.html }}" />
                </div>
            </div>

            <hr>

            <footer>
                <p>{{ @init.copyright }} | <a href="{{ @init.sys.url }}main/user/talk/1">Написать администратору</a></p>
            </footer>

        </div>

        <script src="{{ @init.sys.include }}template/js/vendor/jquery-ui.min.js"></script>

        <script src="{{ @init.sys.include }}template/js/vendor/bootstrap.min.js"></script>
        <script src="{{ @init.sys.include }}template/js/plugins.js"></script>
        <script src="{{ @init.sys.include }}wysihtml5/prettify.js"></script>
        <script src="{{ @init.sys.include }}wysihtml5/wysihtml5.js"></script>
        <script src="{{ @init.sys.include }}wysihtml5/bootstrap-wysihtml5.js"></script>

        <script src="{{ @init.sys.include }}fileupload/jquery.iframe-transport.js"></script>
        <script src="{{ @init.sys.include }}fileupload/jquery.fileupload.js"></script>

        <script src="{{ @init.sys.include }}gp-gallery/gp-gallery.js"></script>

        <script src="{{ @init.sys.include }}colorbox/jquery.colorbox.js"></script>

        <script src="{{ @init.sys.include }}template/js/main.js"></script>
        <script>
//            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
//            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
//            g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
//            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>

    <!-- Modal -->
    <div class="modal hide" id="dialog" tabindex="-1" role="dialog" aria-labelledby="dialog_label" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="dialog_label"></h3>
        </div>
        <div class="modal-body data"></div>
        <div class="modal-body result"></div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Отмена</button>
            <button class="btn btn-primary">Ок</button>
        </div>
    </div>

    <div id="debug"></div>

    </body>
</html>
