/**
 * @file
 * Example of JavaScript file for theme.
 * You can edit it or write on your own.
 */
(function ($) {
    'use strict';

    Drupal.behaviors.dlCommunityTheme = {
        attach: function (context, settings) {

            // Fix wrapper for Rate module.
            $('.rate-widget').each(function () {
                $(this).addClass($(this).attr('id'));
            });

            /**
             * Табы для загрузок говнокод эдишн. Один фиг потом дизайн переделывать :)
             */
            if ($('#download').length) {
                var download_version = '7.x';

                $('[data-version="' + download_version + '"]').addClass('active');

                $('#download button').on('click', function () {
                    download_version = $(this).data('version');
                    $('#download .versions .active').removeClass('active');
                    $('#download .downloads .active').removeClass('active');
                    $('[data-version="' + download_version + '"]').addClass('active');
                });
            }

            /**
             * Добавление проекта на сайт.
             */
            if ($('#add-project-ajax').length) {
                $('#add-project-ajax').on('click', function() {
                    var project = prompt('Пожалуйста укажите машинное имя проекта, или ссылку на проект с Drupal.org.', 'http://drupal.org/project/views');

                    if (project != null) {
                        $.ajax({
                            type: 'POST',
                            url: '/dlcommunity/project/ajax-add',
                            data: 'project=' + project,
                            success: function(data){
                                alert(data);
                            }
                        });
                    }

                    return false;
                });
            }

            /**
             * Обновление данных.
             */
            if ($('#project-request-update').length) {
                $('#project-request-update').on('click', function() {
                    var project = $(this).data('name');
                    $(this).text('Запрос отправлен. Дождитесь обновления страницы.');

                    if (project != null) {
                        $.ajax({
                            type: 'POST',
                            url: '/dlcommunity/project/update',
                            data: 'project=' + project,
                            success: function(data){
                                location.reload();
                            }
                        });
                    }
                    return false;
                });
            }
        }
    };

})(jQuery);
