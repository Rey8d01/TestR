# TestR

### CMS с минимальным набором функций.

## Что есть:
* Пользовательский функционал - регистрация, разделение прав, профиль для отражения некоторой информации о себе, отправка сообщений другим пользователям, комментирование новостей.
* Контент - работа с текстовыми материалами предусматривает наличие небольшого wysiwyg-редкатора и возможность оставлять отзывы к каждой новости. Так же имеется некоторые плюшки в виде создании фотоленты для новости.
* Категории - для тематического разделения контента реализовано в виде иерархии.
* Административная панель - и соответствующий доступ включает в себя управление и настройку системы управления сайтом.

## На чем сделана:

Серверная часть:
* Система написана на языке php 5.3 с иcпользованием микрофреймворка Fat-Free 3 (так же известный как f3);
* В качестве субд используется MariaDB 10 (форк MySQL);
* Система адаптивна для nginx (Без использования apache на стороне back-end);

Клиентская часть:
* Интерфейс класcический Twitter Bootstrap 2;
* Для анимации, подгрузки данных и прочих вещей jQuery 1.9;
* Для ajax загрузки файлов используется jquery.fileupload.js;
* Работу с фотолентой и отображением загруженных фотографий организуют библиотеки colorbox и gp-gallery;
* Визуальным редактором контента является wysihtml5;

## Установка

0. Проверьте конфигурацию сервера. Пример конфигурации для сервера nginx:

        server {
            listen   80;
            charset utf-8;
            root /var/www;
            server_name example.co;

            location / {
                index index.php index.html index.htm;
                try_files $uri /index.php;
            }
            location ~* \.php$ {
                # php-fpm
                fastcgi_pass 127.0.0.1:9000;
                fastcgi_index index.php;
                fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
                include fastcgi_params;
            }

            location ~* ^.+.(svg|css|rss|atom|js|jpg|jpeg|gif|png|ico|zip|rar|doc|xls|exe|ppt|bmp)$ {
               access_log        off;
               expires           max;
            }
            # Запрет на чтение конфиг файлов и логов
            location ~* \.cfg
            {
                deny all;
            }
            location ~* \.log
            {
                deny all;
            }
        }

1. Создайте базу данных (назовите ее к примеру *testr*);
2. Разместите файлы на сервере.
3. Отредактируйте файл *app/config.cfg*, указав имя БД логин и пароль для доступа к ней.

        ; Настройки для БД
        init.db.dsn             = "mysql:host=localhost;port=3306;dbname=testr"
        init.db.user            = "usernamedb"
        init.db.pw              = "passworddb"

Из соображений безопасности установите для этого файла права только на чтение.
4. Убедитесь что имеется файл *app/setup.sql*
5. Зайдите на сайт через браузер. При первом запуске система отработает скрипт установки (на это потребуется меньше минуты) и удалит установочный файл setup.sql, после чего уведомит вас что все прошло успешно. Перезагрузите страницу и можете использовать систему. Войдите используя стандартный логин 'admin' и пароль '12345' и незабудьте изменить пароль на свой.

### Пример того как это работает вы можете увидеть [здесь](http://testrey.co "TestR").