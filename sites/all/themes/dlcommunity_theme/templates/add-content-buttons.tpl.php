<?php
$node = menu_get_object();

$node ? $node_type = $node->type : $node_type = NULL;

if (arg(0) == 'question' || $node_type == 'question' || (arg(0) == 'node' && arg(1) == 'add')) {
  print '<a href="/node/add/question" class="add-content-button question icon-help-circled-alt">Задать вопрос</a>';
}

if (arg(0) == 'post' || $node_type == 'post' || (arg(0) == 'node' && arg(1) == 'add')) {
  print '<a href="/node/add/post" class="add-content-button post icon-newspaper">Добавить публикацию</a>';
}

if (arg(0) == 'project' || $node_type == 'project' || (arg(0) == 'node' && arg(1) == 'add')) {
  print '<a href="#" id="add-project-ajax" class="add-content-button project icon-plug">Добавить расширение</a>';
}
?>