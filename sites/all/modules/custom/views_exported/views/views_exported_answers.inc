<?php

$view = new view();
$view->name = 'answers';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'node';
$view->human_name = 'Answers';
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
$handler->display->display_options['query']['options']['distinct'] = TRUE;
$handler->display->display_options['query']['options']['pure_distinct'] = TRUE;
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
$handler->display->display_options['row_options']['view_mode'] = 'teaser';
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
/* Связь: Флаги: best_answer */
$handler->display->display_options['relationships']['flag_content_rel']['id'] = 'flag_content_rel';
$handler->display->display_options['relationships']['flag_content_rel']['table'] = 'node';
$handler->display->display_options['relationships']['flag_content_rel']['field'] = 'flag_content_rel';
$handler->display->display_options['relationships']['flag_content_rel']['label'] = 'best answer';
$handler->display->display_options['relationships']['flag_content_rel']['required'] = 0;
$handler->display->display_options['relationships']['flag_content_rel']['flag'] = 'best_answer';
$handler->display->display_options['relationships']['flag_content_rel']['user_scope'] = 'any';
/* Поле: Содержимое: Заголовок */
$handler->display->display_options['fields']['title']['id'] = 'title';
$handler->display->display_options['fields']['title']['table'] = 'node';
$handler->display->display_options['fields']['title']['field'] = 'title';
$handler->display->display_options['fields']['title']['label'] = '';
$handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
$handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
/* Критерий сортировки: Флаги: Помечено */
$handler->display->display_options['sorts']['flagged']['id'] = 'flagged';
$handler->display->display_options['sorts']['flagged']['table'] = 'flagging';
$handler->display->display_options['sorts']['flagged']['field'] = 'flagged';
$handler->display->display_options['sorts']['flagged']['relationship'] = 'flag_content_rel';
$handler->display->display_options['sorts']['flagged']['order'] = 'DESC';
/* Критерий сортировки: Результаты голосования: Значение */
$handler->display->display_options['sorts']['value']['id'] = 'value';
$handler->display->display_options['sorts']['value']['table'] = 'votingapi_cache';
$handler->display->display_options['sorts']['value']['field'] = 'value';
$handler->display->display_options['sorts']['value']['relationship'] = 'votingapi_cache';
$handler->display->display_options['sorts']['value']['order'] = 'DESC';
$handler->display->display_options['sorts']['value']['coalesce'] = 1;
/* Критерий сортировки: Содержимое: Дата публикации */
$handler->display->display_options['sorts']['created']['id'] = 'created';
$handler->display->display_options['sorts']['created']['table'] = 'node';
$handler->display->display_options['sorts']['created']['field'] = 'created';
/* Контекстный фильтр: Содержимое: answer_question_reference (field_answer_question_reference) */
$handler->display->display_options['arguments']['field_answer_question_reference_target_id']['id'] = 'field_answer_question_reference_target_id';
$handler->display->display_options['arguments']['field_answer_question_reference_target_id']['table'] = 'field_data_field_answer_question_reference';
$handler->display->display_options['arguments']['field_answer_question_reference_target_id']['field'] = 'field_answer_question_reference_target_id';
$handler->display->display_options['arguments']['field_answer_question_reference_target_id']['default_action'] = 'default';
$handler->display->display_options['arguments']['field_answer_question_reference_target_id']['exception']['title'] = 'Все';
$handler->display->display_options['arguments']['field_answer_question_reference_target_id']['default_argument_type'] = 'node';
$handler->display->display_options['arguments']['field_answer_question_reference_target_id']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['field_answer_question_reference_target_id']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['field_answer_question_reference_target_id']['summary_options']['items_per_page'] = '25';
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
  'answer' => 'answer',
);

/* Display: Вложение */
$handler = $view->new_display('attachment', 'Вложение', 'answers');
$handler->display->display_options['pager']['type'] = 'none';
$handler->display->display_options['pager']['options']['offset'] = '0';
$translatables['answers'] = array(
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
  t('best answer'),
  t('Все'),
  t('Вложение'),
);