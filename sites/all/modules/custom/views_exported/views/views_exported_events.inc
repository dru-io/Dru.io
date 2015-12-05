<?php

$view = new view();
$view->name = 'events';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'node';
$view->human_name = 'Мероприятия';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['title'] = 'Мероприятия';
$handler->display->display_options['css_class'] = 'events';
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['use_more_text'] = 'Все мероприятия';
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['query']['options']['distinct'] = TRUE;
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['exposed_form']['options']['submit_button'] = 'Применить';
$handler->display->display_options['exposed_form']['options']['reset_button_label'] = 'Сбросить';
$handler->display->display_options['exposed_form']['options']['exposed_sorts_label'] = 'Сортировать по';
$handler->display->display_options['exposed_form']['options']['sort_asc_label'] = 'По возрастанию';
$handler->display->display_options['exposed_form']['options']['sort_desc_label'] = 'По убыванию';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['pager']['options']['items_per_page'] = '20';
$handler->display->display_options['pager']['options']['expose']['items_per_page_label'] = 'Элементов на страницу';
$handler->display->display_options['pager']['options']['expose']['items_per_page_options_all_label'] = '- Все -';
$handler->display->display_options['pager']['options']['expose']['offset_label'] = 'Пропустить';
$handler->display->display_options['pager']['options']['tags']['first'] = '« первая';
$handler->display->display_options['pager']['options']['tags']['previous'] = '‹ предыдущая';
$handler->display->display_options['pager']['options']['tags']['next'] = 'следующая ›';
$handler->display->display_options['pager']['options']['tags']['last'] = 'последняя »';
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['style_options']['row_class'] = 'event_item';
$handler->display->display_options['row_plugin'] = 'fields';
/* Шапка: Глобальный: Нефильтруемый текст */
$handler->display->display_options['header']['area_text_custom']['id'] = 'area_text_custom';
$handler->display->display_options['header']['area_text_custom']['table'] = 'views';
$handler->display->display_options['header']['area_text_custom']['field'] = 'area_text_custom';
$handler->display->display_options['header']['area_text_custom']['content'] = '<h2 class="title">Мероприятия</h2>';
/* Связь: Флаги: will_go2_event */
$handler->display->display_options['relationships']['flag_content_rel']['id'] = 'flag_content_rel';
$handler->display->display_options['relationships']['flag_content_rel']['table'] = 'node';
$handler->display->display_options['relationships']['flag_content_rel']['field'] = 'flag_content_rel';
$handler->display->display_options['relationships']['flag_content_rel']['label'] = 'Я пойду';
$handler->display->display_options['relationships']['flag_content_rel']['required'] = 0;
$handler->display->display_options['relationships']['flag_content_rel']['flag'] = 'will_go2_event';
$handler->display->display_options['relationships']['flag_content_rel']['user_scope'] = 'any';
/* Связь: Содержимое: Автор */
$handler->display->display_options['relationships']['uid']['id'] = 'uid';
$handler->display->display_options['relationships']['uid']['table'] = 'node';
$handler->display->display_options['relationships']['uid']['field'] = 'uid';
/* Поле: Содержимое: Постер */
$handler->display->display_options['fields']['field_event_poster']['id'] = 'field_event_poster';
$handler->display->display_options['fields']['field_event_poster']['table'] = 'field_data_field_event_poster';
$handler->display->display_options['fields']['field_event_poster']['field'] = 'field_event_poster';
$handler->display->display_options['fields']['field_event_poster']['label'] = '';
$handler->display->display_options['fields']['field_event_poster']['element_class'] = 'event__poster';
$handler->display->display_options['fields']['field_event_poster']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['field_event_poster']['element_default_classes'] = FALSE;
$handler->display->display_options['fields']['field_event_poster']['click_sort_column'] = 'fid';
$handler->display->display_options['fields']['field_event_poster']['settings'] = array(
  'image_style' => 'event_poster',
  'image_link' => 'content',
);
/* Поле: Содержимое: Заголовок */
$handler->display->display_options['fields']['title']['id'] = 'title';
$handler->display->display_options['fields']['title']['table'] = 'node';
$handler->display->display_options['fields']['title']['field'] = 'title';
$handler->display->display_options['fields']['title']['label'] = '';
$handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
$handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
$handler->display->display_options['fields']['title']['element_type'] = 'h2';
$handler->display->display_options['fields']['title']['element_class'] = 'event__title';
$handler->display->display_options['fields']['title']['element_label_colon'] = FALSE;
/* Поле: Пользователь: Имя */
$handler->display->display_options['fields']['name']['id'] = 'name';
$handler->display->display_options['fields']['name']['table'] = 'users';
$handler->display->display_options['fields']['name']['field'] = 'name';
$handler->display->display_options['fields']['name']['relationship'] = 'uid';
$handler->display->display_options['fields']['name']['label'] = '';
$handler->display->display_options['fields']['name']['element_class'] = 'event__author';
$handler->display->display_options['fields']['name']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['name']['element_default_classes'] = FALSE;
/* Поле: Содержимое: Где */
$handler->display->display_options['fields']['field_event_place']['id'] = 'field_event_place';
$handler->display->display_options['fields']['field_event_place']['table'] = 'field_data_field_event_place';
$handler->display->display_options['fields']['field_event_place']['field'] = 'field_event_place';
$handler->display->display_options['fields']['field_event_place']['label'] = '';
$handler->display->display_options['fields']['field_event_place']['element_type'] = 'div';
$handler->display->display_options['fields']['field_event_place']['element_class'] = 'event__place';
$handler->display->display_options['fields']['field_event_place']['element_label_colon'] = FALSE;
/* Поле: Содержимое: Дата */
$handler->display->display_options['fields']['field_event_date']['id'] = 'field_event_date';
$handler->display->display_options['fields']['field_event_date']['table'] = 'field_data_field_event_date';
$handler->display->display_options['fields']['field_event_date']['field'] = 'field_event_date';
$handler->display->display_options['fields']['field_event_date']['label'] = '';
$handler->display->display_options['fields']['field_event_date']['element_type'] = 'div';
$handler->display->display_options['fields']['field_event_date']['element_class'] = 'event__date';
$handler->display->display_options['fields']['field_event_date']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['field_event_date']['settings'] = array(
  'format_type' => 'ru_medium',
  'fromto' => 'both',
  'multiple_number' => '',
  'multiple_from' => '',
  'multiple_to' => '',
);
/* Поле: Содержимое: Город */
$handler->display->display_options['fields']['field_event_city']['id'] = 'field_event_city';
$handler->display->display_options['fields']['field_event_city']['table'] = 'field_data_field_event_city';
$handler->display->display_options['fields']['field_event_city']['field'] = 'field_event_city';
$handler->display->display_options['fields']['field_event_city']['label'] = '';
$handler->display->display_options['fields']['field_event_city']['element_class'] = 'event__city';
$handler->display->display_options['fields']['field_event_city']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['field_event_city']['type'] = 'taxonomy_term_reference_plain';
/* Поле: Содержимое: Описание */
$handler->display->display_options['fields']['body']['id'] = 'body';
$handler->display->display_options['fields']['body']['table'] = 'field_data_body';
$handler->display->display_options['fields']['body']['field'] = 'body';
$handler->display->display_options['fields']['body']['label'] = '';
$handler->display->display_options['fields']['body']['element_class'] = 'event__about';
$handler->display->display_options['fields']['body']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['body']['type'] = 'text_summary_or_trimmed';
$handler->display->display_options['fields']['body']['settings'] = array(
  'trim_length' => '600',
);
/* Поле: Содержимое: Счетчик комментариев */
$handler->display->display_options['fields']['comment_count']['id'] = 'comment_count';
$handler->display->display_options['fields']['comment_count']['table'] = 'node_comment_statistics';
$handler->display->display_options['fields']['comment_count']['field'] = 'comment_count';
$handler->display->display_options['fields']['comment_count']['label'] = '';
$handler->display->display_options['fields']['comment_count']['element_class'] = 'event_comments';
$handler->display->display_options['fields']['comment_count']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['comment_count']['separator'] = '';
/* Поле: Содержимое: Фото */
$handler->display->display_options['fields']['field_event_photo']['id'] = 'field_event_photo';
$handler->display->display_options['fields']['field_event_photo']['table'] = 'field_data_field_event_photo';
$handler->display->display_options['fields']['field_event_photo']['field'] = 'field_event_photo';
$handler->display->display_options['fields']['field_event_photo']['label'] = '';
$handler->display->display_options['fields']['field_event_photo']['alter']['alter_text'] = TRUE;
$handler->display->display_options['fields']['field_event_photo']['alter']['text'] = '<a href="#">Есть фото-отчет</a>';
$handler->display->display_options['fields']['field_event_photo']['element_class'] = 'event__photoreport';
$handler->display->display_options['fields']['field_event_photo']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['field_event_photo']['element_default_classes'] = FALSE;
$handler->display->display_options['fields']['field_event_photo']['hide_empty'] = TRUE;
$handler->display->display_options['fields']['field_event_photo']['empty_zero'] = TRUE;
$handler->display->display_options['fields']['field_event_photo']['click_sort_column'] = 'fid';
$handler->display->display_options['fields']['field_event_photo']['settings'] = array(
  'image_style' => '',
  'image_link' => '',
);
$handler->display->display_options['fields']['field_event_photo']['delta_limit'] = '1';
$handler->display->display_options['fields']['field_event_photo']['delta_offset'] = '0';
/* Поле: Содержимое: Ссылка */
$handler->display->display_options['fields']['view_node']['id'] = 'view_node';
$handler->display->display_options['fields']['view_node']['table'] = 'views_entity_node';
$handler->display->display_options['fields']['view_node']['field'] = 'view_node';
$handler->display->display_options['fields']['view_node']['label'] = '';
$handler->display->display_options['fields']['view_node']['element_class'] = 'event__link';
$handler->display->display_options['fields']['view_node']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['view_node']['text'] = 'Подробнее';
/* Поле: Флаги: Ссылка флага */
$handler->display->display_options['fields']['ops']['id'] = 'ops';
$handler->display->display_options['fields']['ops']['table'] = 'flagging';
$handler->display->display_options['fields']['ops']['field'] = 'ops';
$handler->display->display_options['fields']['ops']['relationship'] = 'flag_content_rel';
$handler->display->display_options['fields']['ops']['label'] = '';
$handler->display->display_options['fields']['ops']['element_type'] = 'div';
$handler->display->display_options['fields']['ops']['element_class'] = 'event__go';
$handler->display->display_options['fields']['ops']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['ops']['element_default_classes'] = FALSE;
/* Критерий сортировки: Содержимое: Дата (field_event_date) */
$handler->display->display_options['sorts']['field_event_date_value']['id'] = 'field_event_date_value';
$handler->display->display_options['sorts']['field_event_date_value']['table'] = 'field_data_field_event_date';
$handler->display->display_options['sorts']['field_event_date_value']['field'] = 'field_event_date_value';
$handler->display->display_options['sorts']['field_event_date_value']['order'] = 'DESC';
/* Критерий фильтра: Содержимое: Опубликовано */
$handler->display->display_options['filters']['status']['id'] = 'status';
$handler->display->display_options['filters']['status']['table'] = 'node';
$handler->display->display_options['filters']['status']['field'] = 'status';
$handler->display->display_options['filters']['status']['value'] = 1;
$handler->display->display_options['filters']['status']['group'] = 1;
$handler->display->display_options['filters']['status']['expose']['operator'] = FALSE;
/* Критерий фильтра: Содержимое: Тип */
$handler->display->display_options['filters']['type']['id'] = 'type';
$handler->display->display_options['filters']['type']['table'] = 'node';
$handler->display->display_options['filters']['type']['field'] = 'type';
$handler->display->display_options['filters']['type']['value'] = array(
  'event' => 'event',
);

/* Display: Page */
$handler = $view->new_display('page', 'Page', 'page');
$handler->display->display_options['defaults']['style_plugin'] = FALSE;
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['style_options']['row_class'] = 'event__item';
$handler->display->display_options['defaults']['style_options'] = FALSE;
$handler->display->display_options['defaults']['row_plugin'] = FALSE;
$handler->display->display_options['row_plugin'] = 'entity';
$handler->display->display_options['row_options']['view_mode'] = 'teaser';
$handler->display->display_options['defaults']['row_options'] = FALSE;
$handler->display->display_options['path'] = 'events';
$handler->display->display_options['menu']['type'] = 'normal';
$handler->display->display_options['menu']['title'] = 'Мероприятия';
$handler->display->display_options['menu']['name'] = 'main-menu';

/* Display: Блок на главной */
$handler = $view->new_display('block', 'Блок на главной', 'frontpage');
$handler->display->display_options['defaults']['use_more'] = FALSE;
$handler->display->display_options['defaults']['use_more_always'] = FALSE;
$handler->display->display_options['defaults']['use_more_always'] = FALSE;
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['defaults']['use_more_text'] = FALSE;
$handler->display->display_options['use_more_text'] = 'Все мероприятия';
$handler->display->display_options['defaults']['pager'] = FALSE;
$handler->display->display_options['pager']['type'] = 'some';
$handler->display->display_options['pager']['options']['items_per_page'] = '2';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['defaults']['relationships'] = FALSE;
$handler->display->display_options['defaults']['fields'] = FALSE;
/* Поле: Содержимое: Постер */
$handler->display->display_options['fields']['field_event_poster']['id'] = 'field_event_poster';
$handler->display->display_options['fields']['field_event_poster']['table'] = 'field_data_field_event_poster';
$handler->display->display_options['fields']['field_event_poster']['field'] = 'field_event_poster';
$handler->display->display_options['fields']['field_event_poster']['label'] = '';
$handler->display->display_options['fields']['field_event_poster']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['field_event_poster']['element_wrapper_class'] = 'event__poster';
$handler->display->display_options['fields']['field_event_poster']['element_default_classes'] = FALSE;
$handler->display->display_options['fields']['field_event_poster']['click_sort_column'] = 'fid';
$handler->display->display_options['fields']['field_event_poster']['settings'] = array(
  'image_style' => 'event_poster_main',
  'image_link' => 'content',
);
/* Поле: Содержимое: Заголовок */
$handler->display->display_options['fields']['title']['id'] = 'title';
$handler->display->display_options['fields']['title']['table'] = 'node';
$handler->display->display_options['fields']['title']['field'] = 'title';
$handler->display->display_options['fields']['title']['label'] = '';
$handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
$handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
$handler->display->display_options['fields']['title']['element_type'] = 'h3';
$handler->display->display_options['fields']['title']['element_class'] = 'event__title';
$handler->display->display_options['fields']['title']['element_label_colon'] = FALSE;
/* Поле: Содержимое: Где */
$handler->display->display_options['fields']['field_event_place']['id'] = 'field_event_place';
$handler->display->display_options['fields']['field_event_place']['table'] = 'field_data_field_event_place';
$handler->display->display_options['fields']['field_event_place']['field'] = 'field_event_place';
$handler->display->display_options['fields']['field_event_place']['label'] = '';
$handler->display->display_options['fields']['field_event_place']['element_type'] = 'div';
$handler->display->display_options['fields']['field_event_place']['element_class'] = 'event__place';
$handler->display->display_options['fields']['field_event_place']['element_label_colon'] = FALSE;
/* Поле: Содержимое: Дата */
$handler->display->display_options['fields']['field_event_date']['id'] = 'field_event_date';
$handler->display->display_options['fields']['field_event_date']['table'] = 'field_data_field_event_date';
$handler->display->display_options['fields']['field_event_date']['field'] = 'field_event_date';
$handler->display->display_options['fields']['field_event_date']['label'] = '';
$handler->display->display_options['fields']['field_event_date']['element_type'] = 'div';
$handler->display->display_options['fields']['field_event_date']['element_class'] = 'event__date';
$handler->display->display_options['fields']['field_event_date']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['field_event_date']['settings'] = array(
  'format_type' => 'ru_medium',
  'fromto' => 'both',
  'multiple_number' => '',
  'multiple_from' => '',
  'multiple_to' => '',
);
/* Поле: Содержимое: Город */
$handler->display->display_options['fields']['field_event_city']['id'] = 'field_event_city';
$handler->display->display_options['fields']['field_event_city']['table'] = 'field_data_field_event_city';
$handler->display->display_options['fields']['field_event_city']['field'] = 'field_event_city';
$handler->display->display_options['fields']['field_event_city']['label'] = '';
$handler->display->display_options['fields']['field_event_city']['element_class'] = 'event__city';
$handler->display->display_options['fields']['field_event_city']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['field_event_city']['type'] = 'taxonomy_term_reference_plain';
$handler->display->display_options['block_description'] = 'Мероприятия: блок на главной';
$translatables['events'] = array(
  t('Master'),
  t('Мероприятия'),
  t('Все мероприятия'),
  t('Применить'),
  t('Сбросить'),
  t('Сортировать по'),
  t('По возрастанию'),
  t('По убыванию'),
  t('Элементов на страницу'),
  t('- Все -'),
  t('Пропустить'),
  t('« первая'),
  t('‹ предыдущая'),
  t('следующая ›'),
  t('последняя »'),
  t('<h2 class="title">Мероприятия</h2>'),
  t('Я пойду'),
  t('автор'),
  t('.'),
  t('<a href="#">Есть фото-отчет</a>'),
  t('Подробнее'),
  t('Page'),
  t('ещё'),
  t('Блок на главной'),
  t('Мероприятия: блок на главной'),
);
