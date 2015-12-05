<?php

$view = new view();
$view->name = 'posts_node';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'node';
$view->human_name = 'Публикации Nodes';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
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
$handler->display->display_options['pager']['options']['expose']['items_per_page_label'] = 'Элементов на страницу';
$handler->display->display_options['pager']['options']['expose']['items_per_page_options_all_label'] = '- Все -';
$handler->display->display_options['pager']['options']['expose']['offset_label'] = 'Пропустить';
$handler->display->display_options['pager']['options']['tags']['first'] = '« первая';
$handler->display->display_options['pager']['options']['tags']['previous'] = '‹ предыдущая';
$handler->display->display_options['pager']['options']['tags']['next'] = 'следующая ›';
$handler->display->display_options['pager']['options']['tags']['last'] = 'последняя »';
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['row_plugin'] = 'entity';
$handler->display->display_options['row_options']['view_mode'] = 'minimal';
/* Связь: Содержимое: Vote results */
$handler->display->display_options['relationships']['votingapi_cache']['id'] = 'votingapi_cache';
$handler->display->display_options['relationships']['votingapi_cache']['table'] = 'node';
$handler->display->display_options['relationships']['votingapi_cache']['field'] = 'votingapi_cache';
$handler->display->display_options['relationships']['votingapi_cache']['label'] = 'Результаты голосования';
$handler->display->display_options['relationships']['votingapi_cache']['votingapi'] = array(
  'value_type' => 'points',
  'tag' => '',
  'function' => 'sum',
);
/* Поле: Содержимое: Заголовок */
$handler->display->display_options['fields']['title']['id'] = 'title';
$handler->display->display_options['fields']['title']['table'] = 'node';
$handler->display->display_options['fields']['title']['field'] = 'title';
$handler->display->display_options['fields']['title']['label'] = '';
$handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
$handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
/* Критерий сортировки: Содержимое: Дата публикации */
$handler->display->display_options['sorts']['created']['id'] = 'created';
$handler->display->display_options['sorts']['created']['table'] = 'node';
$handler->display->display_options['sorts']['created']['field'] = 'created';
$handler->display->display_options['sorts']['created']['order'] = 'DESC';
$handler->display->display_options['filter_groups']['groups'] = array(
  1 => 'AND',
  2 => 'OR',
);
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
  'post' => 'post',
);
$handler->display->display_options['filters']['type']['group'] = 1;
/* Критерий фильтра: Результаты голосования: Значение */
$handler->display->display_options['filters']['value_1']['id'] = 'value_1';
$handler->display->display_options['filters']['value_1']['table'] = 'votingapi_cache';
$handler->display->display_options['filters']['value_1']['field'] = 'value';
$handler->display->display_options['filters']['value_1']['relationship'] = 'votingapi_cache';
$handler->display->display_options['filters']['value_1']['operator'] = 'empty';
$handler->display->display_options['filters']['value_1']['group'] = 2;
/* Критерий фильтра: Результаты голосования: Значение */
$handler->display->display_options['filters']['value']['id'] = 'value';
$handler->display->display_options['filters']['value']['table'] = 'votingapi_cache';
$handler->display->display_options['filters']['value']['field'] = 'value';
$handler->display->display_options['filters']['value']['relationship'] = 'votingapi_cache';
$handler->display->display_options['filters']['value']['operator'] = '>=';
$handler->display->display_options['filters']['value']['value']['value'] = '-4';
$handler->display->display_options['filters']['value']['group'] = 2;

/* Display: На главной */
$handler = $view->new_display('attachment', 'На главной', 'frontpage');
$handler->display->display_options['pager']['type'] = 'some';
$handler->display->display_options['defaults']['header'] = FALSE;
/* Шапка: Глобальный: Текстовое поле */
$handler->display->display_options['header']['area']['id'] = 'area';
$handler->display->display_options['header']['area']['table'] = 'views';
$handler->display->display_options['header']['area']['field'] = 'area';
$handler->display->display_options['header']['area']['content'] = '<h2 class="title">Последние публикации</h2>';
$handler->display->display_options['header']['area']['format'] = 'html';
$handler->display->display_options['defaults']['filter_groups'] = FALSE;
$handler->display->display_options['filter_groups']['groups'] = array(
  1 => 'AND',
  2 => 'OR',
);
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
  'post' => 'post',
);
$handler->display->display_options['filters']['type']['group'] = 1;
/* Критерий фильтра: Результаты голосования: Значение */
$handler->display->display_options['filters']['value_1']['id'] = 'value_1';
$handler->display->display_options['filters']['value_1']['table'] = 'votingapi_cache';
$handler->display->display_options['filters']['value_1']['field'] = 'value';
$handler->display->display_options['filters']['value_1']['relationship'] = 'votingapi_cache';
$handler->display->display_options['filters']['value_1']['operator'] = 'empty';
$handler->display->display_options['filters']['value_1']['group'] = 2;
/* Критерий фильтра: Результаты голосования: Значение */
$handler->display->display_options['filters']['value']['id'] = 'value';
$handler->display->display_options['filters']['value']['table'] = 'votingapi_cache';
$handler->display->display_options['filters']['value']['field'] = 'value';
$handler->display->display_options['filters']['value']['relationship'] = 'votingapi_cache';
$handler->display->display_options['filters']['value']['operator'] = '>=';
$handler->display->display_options['filters']['value']['value']['value'] = '-4';
$handler->display->display_options['filters']['value']['group'] = 2;
/* Критерий фильтра: Содержимое: Помещено на главную страницу */
$handler->display->display_options['filters']['promote']['id'] = 'promote';
$handler->display->display_options['filters']['promote']['table'] = 'node';
$handler->display->display_options['filters']['promote']['field'] = 'promote';
$handler->display->display_options['filters']['promote']['value'] = '0';
$translatables['posts_node'] = array(
  t('Master'),
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
  t('Результаты голосования'),
  t('На главной'),
  t('<h2 class="title">Последние публикации</h2>'),
);
