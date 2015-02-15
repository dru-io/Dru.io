<?php
$form['field_project_reference']['und']['#description'] .= '<br/>Если проект отсутствует на нашем сайте (не подсказывает автозаполнение), в таком случае вы можете добавить проект к нам на сайт очень легко, просто нажав кнопку:<br />
<button id="add-project-ajax" type="button">Добавить новый проект в нашу базу</button>';
?>
<div class="gl-g">
  <div class="gl-s-1">
    <?php print drupal_render($form['title']); ?>
    <?php print drupal_render($form['body']); ?>
    <?php print drupal_render($form['field_project_reference']); ?>
    <?php print drupal_render($form['field_drupal_version']); ?>
  </div>
</div>
<?php print drupal_render_children($form); ?>