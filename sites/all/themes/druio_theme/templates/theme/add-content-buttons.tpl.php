<?php
// @TODO: Rework this shi..
$node = menu_get_object();
$node ? $node_type = $node->type : $node_type = NULL;
if (arg(0) == 'question' || $node_type == 'question' || (arg(0) == 'node' && arg(1) == 'add')):
  print '<a href="/node/add/question" class="add-content-button question icon-help-circled-alt">Задать вопрос</a>';
endif;
if (arg(0) == 'post' || $node_type == 'post' || (arg(0) == 'node' && arg(1) == 'add')):
  print '<a href="/node/add/post" class="add-content-button post icon-newspaper">Добавить публикацию</a>';
endif;
if (arg(0) == 'events' || $node_type == 'event' || (arg(0) == 'event' && arg(1) == 'add')):
  print '<a href="/node/add/event" class="add-content-button post icon-newspaper">Добавить Мероприятие</a>';
endif;
if (arg(0) == 'project' || $node_type == 'project' || (arg(0) == 'node' && arg(1) == 'add')):
  // print '<a href="#" id="add-project-ajax" class="add-content-button project icon-plug">Добавить расширение</a>';
endif;
if (arg(0) == 'orders' || $node_type == 'order' || (arg(0) == 'node' && arg(1) == 'add')):
   print '<a href="/node/add/order" class="add-content-button order icon-plug">Добавить заказ</a>';
endif;
