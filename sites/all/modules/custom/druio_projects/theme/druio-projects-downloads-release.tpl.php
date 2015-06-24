<?php
/**
 * @file
 * Theme for release downloads.
 *
 * Variables:
 * - $release_info: all data for selected by user release.
 * - $entity: entity of project.
 */

$files = $release_info['files'];
$version = $release_info['version'];
$field_project_short_name = field_get_items('node', $entity, 'field_project_short_name');
$project_machine_name = $field_project_short_name[0]['safe_value'];
?>
<div class="archives">
  <h3 class="title">Скачать архив</h3>
  <div class="list">
    <a href="<?php print $files->tar->url; ?>" class="item">
      <div class="item-label">TAR</div>
      <div class="md5" title="<?php print $files->zip->md5; ?>"><span class="label">MD5:</span> <?php print $files->tar->md5; ?></div>
      <div class="size"><span class="label">Размер:</span> <?php print format_size($files->tar->size); ?></div>
      <div class="date"><span class="label">Релиз:</span> <?php print format_date($files->tar->date, 'ru_medium'); ?></div>
    </a>

    <a href="<?php print $files->zip->url; ?>" class="item">
      <div class="item-label">ZIP</div>
      <div class="md5" title="<?php print $files->zip->md5; ?>"><span class="label">MD5:</span> <?php print $files->zip->md5; ?></div>
      <div class="size"><span class="label">Размер:</span> <?php print format_size($files->zip->size); ?></div>
      <div class="date"><span class="label">Релиз:</span> <?php print format_date($files->zip->date, 'ru_medium'); ?></div>
    </a>
  </div>
</div>
<div class="drush">
  <h3 class="title">Drush</h3>
  <div class="item">
    <span>Скачать и включить последнюю версию:</span>
    <pre><code class="command">drush en <?php print $project_machine_name; ?> -y</code></pre>
  </div>
  <div class="item">
    <span>Скачать и включить выбранную версию:</span>
    <pre><code class="command">drush en <?php print $project_machine_name . '-' . $version; ?> -y</code></pre>
  </div>
</div>
