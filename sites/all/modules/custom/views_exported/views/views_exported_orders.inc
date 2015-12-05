<?php

$view = new view();
$view->name = 'orders';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'node';
$view->human_name = 'Заказы';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['title'] = 'Заказы';
$handler->display->display_options['css_class'] = 'orders';
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['use_more_text'] = 'ещё';
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['exposed_form']['options']['submit_button'] = 'Применить';
$handler->display->display_options['exposed_form']['options']['reset_button_label'] = 'Сбросить';
$handler->display->display_options['exposed_form']['options']['exposed_sorts_label'] = 'Сортировать по';
$handler->display->display_options['exposed_form']['options']['sort_asc_label'] = 'По возрастанию';
$handler->display->display_options['exposed_form']['options']['sort_desc_label'] = 'По убыванию';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['pager']['options']['items_per_page'] = '10';
$handler->display->display_options['pager']['options']['expose']['items_per_page_label'] = 'Элементов на страницу';
$handler->display->display_options['pager']['options']['expose']['items_per_page_options_all_label'] = '- Все -';
$handler->display->display_options['pager']['options']['expose']['offset_label'] = 'Пропустить';
$handler->display->display_options['pager']['options']['tags']['first'] = '« первая';
$handler->display->display_options['pager']['options']['tags']['previous'] = '‹ предыдущая';
$handler->display->display_options['pager']['options']['tags']['next'] = 'следующая ›';
$handler->display->display_options['pager']['options']['tags']['last'] = 'последняя »';
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['row_plugin'] = 'fields';
/* Шапка: Глобальный: Нефильтруемый текст */
$handler->display->display_options['header']['area_text_custom']['id'] = 'area_text_custom';
$handler->display->display_options['header']['area_text_custom']['table'] = 'views';
$handler->display->display_options['header']['area_text_custom']['field'] = 'area_text_custom';
/* Поле: Содержимое: Заголовок */
$handler->display->display_options['fields']['title']['id'] = 'title';
$handler->display->display_options['fields']['title']['table'] = 'node';
$handler->display->display_options['fields']['title']['field'] = 'title';
$handler->display->display_options['fields']['title']['label'] = '';
$handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
$handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
$handler->display->display_options['fields']['title']['element_class'] = 'title';
$handler->display->display_options['fields']['title']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['title']['element_wrapper_type'] = 'div';
$handler->display->display_options['fields']['title']['element_wrapper_class'] = 'row';
/* Критерий сортировки: Содержимое: Дата публикации */
$handler->display->display_options['sorts']['created']['id'] = 'created';
$handler->display->display_options['sorts']['created']['table'] = 'node';
$handler->display->display_options['sorts']['created']['field'] = 'created';
$handler->display->display_options['sorts']['created']['order'] = 'DESC';
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
  'order' => 'order',
);

/* Display: Page */
$handler = $view->new_display('page', 'Page', 'page');
$handler->display->display_options['defaults']['style_plugin'] = FALSE;
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['defaults']['style_options'] = FALSE;
$handler->display->display_options['defaults']['row_plugin'] = FALSE;
$handler->display->display_options['row_plugin'] = 'entity';
$handler->display->display_options['row_options']['view_mode'] = 'teaser';
$handler->display->display_options['defaults']['row_options'] = FALSE;
$handler->display->display_options['defaults']['fields'] = FALSE;
/* Поле: Содержимое: Заголовок */
$handler->display->display_options['fields']['title']['id'] = 'title';
$handler->display->display_options['fields']['title']['table'] = 'node';
$handler->display->display_options['fields']['title']['field'] = 'title';
$handler->display->display_options['fields']['title']['label'] = '';
$handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
$handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
$handler->display->display_options['fields']['title']['element_type'] = 'h3';
$handler->display->display_options['fields']['title']['element_class'] = 'title';
$handler->display->display_options['fields']['title']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['title']['element_wrapper_type'] = 'div';
$handler->display->display_options['fields']['title']['element_wrapper_class'] = 'row';
/* Поле: Содержимое: Дата публикации */
$handler->display->display_options['fields']['created']['id'] = 'created';
$handler->display->display_options['fields']['created']['table'] = 'node';
$handler->display->display_options['fields']['created']['field'] = 'created';
$handler->display->display_options['fields']['created']['element_type'] = 'span';
$handler->display->display_options['fields']['created']['element_label_type'] = 'span';
$handler->display->display_options['fields']['created']['element_wrapper_type'] = 'div';
$handler->display->display_options['fields']['created']['element_wrapper_class'] = 'date';
$handler->display->display_options['fields']['created']['date_format'] = 'time ago';
$handler->display->display_options['fields']['created']['second_date_format'] = 'long';
/* Поле: Содержимое: Бюджет */
$handler->display->display_options['fields']['field_order_budgeting']['id'] = 'field_order_budgeting';
$handler->display->display_options['fields']['field_order_budgeting']['table'] = 'field_data_field_order_budgeting';
$handler->display->display_options['fields']['field_order_budgeting']['field'] = 'field_order_budgeting';
$handler->display->display_options['fields']['field_order_budgeting']['element_type'] = 'span';
$handler->display->display_options['fields']['field_order_budgeting']['element_label_type'] = 'span';
$handler->display->display_options['fields']['field_order_budgeting']['element_wrapper_type'] = 'div';
$handler->display->display_options['fields']['field_order_budgeting']['element_wrapper_class'] = 'budgeting';
$handler->display->display_options['fields']['field_order_budgeting']['hide_empty'] = TRUE;
$handler->display->display_options['fields']['field_order_budgeting']['empty_zero'] = TRUE;
$handler->display->display_options['fields']['field_order_budgeting']['settings'] = array(
  'thousand_separator' => ' ',
  'prefix_suffix' => 1,
);
/* Поле: Содержимое: Описание */
$handler->display->display_options['fields']['body']['id'] = 'body';
$handler->display->display_options['fields']['body']['table'] = 'field_data_body';
$handler->display->display_options['fields']['body']['field'] = 'body';
$handler->display->display_options['fields']['body']['label'] = '';
$handler->display->display_options['fields']['body']['element_class'] = 'description';
$handler->display->display_options['fields']['body']['element_label_colon'] = FALSE;
/* Поле: Содержимое: Техническое задание */
$handler->display->display_options['fields']['field_order_specification']['id'] = 'field_order_specification';
$handler->display->display_options['fields']['field_order_specification']['table'] = 'field_data_field_order_specification';
$handler->display->display_options['fields']['field_order_specification']['field'] = 'field_order_specification';
$handler->display->display_options['fields']['field_order_specification']['label'] = '';
$handler->display->display_options['fields']['field_order_specification']['element_class'] = 'specification';
$handler->display->display_options['fields']['field_order_specification']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['field_order_specification']['click_sort_column'] = 'fid';
$handler->display->display_options['fields']['field_order_specification']['delta_offset'] = '0';
/* Поле: Содержимое: Ссылка */
$handler->display->display_options['fields']['view_node']['id'] = 'view_node';
$handler->display->display_options['fields']['view_node']['table'] = 'views_entity_node';
$handler->display->display_options['fields']['view_node']['field'] = 'view_node';
$handler->display->display_options['fields']['view_node']['label'] = '';
$handler->display->display_options['fields']['view_node']['element_class'] = 'link';
$handler->display->display_options['fields']['view_node']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['view_node']['text'] = 'Подробнее';
$handler->display->display_options['path'] = 'orders';
$handler->display->display_options['menu']['type'] = 'normal';
$handler->display->display_options['menu']['title'] = 'Заказы';
$handler->display->display_options['menu']['weight'] = '0';
$handler->display->display_options['menu']['name'] = 'main-menu';
$handler->display->display_options['menu']['context'] = 0;
$handler->display->display_options['menu']['context_only_inline'] = 0;

/* Display: Блок на главной */
$handler = $view->new_display('block', 'Блок на главной', 'frontpage');
$handler->display->display_options['defaults']['pager'] = FALSE;
$handler->display->display_options['pager']['type'] = 'some';
$handler->display->display_options['pager']['options']['items_per_page'] = '4';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['defaults']['style_plugin'] = FALSE;
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['defaults']['style_options'] = FALSE;
$handler->display->display_options['defaults']['row_plugin'] = FALSE;
$handler->display->display_options['row_plugin'] = 'entity';
$handler->display->display_options['row_options']['view_mode'] = 'teaser';
$handler->display->display_options['defaults']['row_options'] = FALSE;
$handler->display->display_options['defaults']['header'] = FALSE;
/* Шапка: Глобальный: Нефильтруемый текст */
$handler->display->display_options['header']['area_text_custom']['id'] = 'area_text_custom';
$handler->display->display_options['header']['area_text_custom']['table'] = 'views';
$handler->display->display_options['header']['area_text_custom']['field'] = 'area_text_custom';
$handler->display->display_options['header']['area_text_custom']['content'] = '<h2 class="title">Новые заказы</h2>';
$handler->display->display_options['defaults']['filter_groups'] = FALSE;
$handler->display->display_options['defaults']['filters'] = FALSE;
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
  'order' => 'order',
);
/* Критерий фильтра: Содержимое: Статус (field_order_status_term) */
$handler->display->display_options['filters']['field_order_status_term_tid']['id'] = 'field_order_status_term_tid';
$handler->display->display_options['filters']['field_order_status_term_tid']['table'] = 'field_data_field_order_status_term';
$handler->display->display_options['filters']['field_order_status_term_tid']['field'] = 'field_order_status_term_tid';
$handler->display->display_options['filters']['field_order_status_term_tid']['value'] = array(
  3420 => '3420',
);
$handler->display->display_options['filters']['field_order_status_term_tid']['type'] = 'select';
$handler->display->display_options['filters']['field_order_status_term_tid']['vocabulary'] = 'order_status';
$translatables['orders'] = array(
  t('Master'),
  t('Заказы'),
  t('ещё'),
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
  t('Page'),
  t('Дата публикации'),
  t('Бюджет'),
  t('Подробнее'),
  t('Блок на главной'),
  t('<h2 class="title">Новые заказы</h2>'),
);
