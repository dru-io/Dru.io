<?php
global $user;
$releases_json = $field_project_releases[0]['value'];
$releases_array = json_decode($releases_json, TRUE);
krsort($releases_array);
?>
<article class="<?php print $clean_classes; ?> gl-g"<?php print $attributes; ?>>

  <div class="gl-s-1 gl-s-lg-12-24 project__info">
    <h2 class="project__title"><?php print $title; ?></h2>

    <div class="project__info-update">
      Информация о проекте обновлена <?php print format_date($node->changed, 'short'); ?>
      <?php if (time() >= strtotime('+1 day', $node->changed) || $user->uid == 1): ?>
      <br /><a href="#" id="project-request-update" data-name="<?php print $field_project_short_name[0]['value']; ?>">Запросить обновление данных.</a>
      <?php endif; ?>
    </div>

    <div class="project__status gl-g">
      <div class="project__status__name gl-s-12-24"><?php print t('Maintenance status'); ?>:</div>
      <div class="project__status__value gl-s-12-24"><?php print $field_project_maintenance_status[0]['taxonomy_term']->name; ?></div>

      <div class="project__status__name gl-s-12-24"><?php print t('Development status'); ?>:</div>
      <div class="project__status__value gl-s-12-24"><?php print $field_project_development_status[0]['taxonomy_term']->name; ?></div>

      <div class="project__status__name gl-s-12-24"><?php print t('Project type'); ?>:</div>
      <div class="project__status__value gl-s-12-24"><?php print $field_project_type[0]['taxonomy_term']->name; ?></div>
    </div>

    <h2 class="project__download__title">Загрузки</h2>

    <div id="download">
      <ul class="versions">
        <?php foreach ($releases_array as $version => $release): ?>
          <li><button data-version="<?php print $version; ?>"><?php print $version; ?></button></li>
        <?php endforeach; ?>
      </ul>

      <div class="downloads">
        <?php foreach ($releases_array as $version => $release): ?>
          <div class="downloads-wrapper" data-version="<?php print $version; ?>">
            <?php
            $i = 0;
            foreach($release as $single_version => $single_release): ?>
              <?php
              $re = "/.*(-dev)/";
              preg_match($re, $single_version, $matches);
              if (empty($matches) && $i == 0) {
              ?>
                <div class="release normal">
                  <div class="release__label"><?php print $single_version; ?>
                    <?php if ($single_release['date']):?>
                    <span class="release__date">от <?php  print format_date($single_release['date'], 'short'); ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="release__infolink"><a href="<?php print $single_release['release_link']; ?>" target="_blank">Список изменений версии <?php print $single_version; ?></a></div>
                  <h4 class="release__download-title icon-download">Загрузки</h4>
                  <div class="release__file">
                    <a href="<?php print $single_release['files']['zip']['url']; ?>" class="release__file-title">ZIP</a>
                    <div class="release__file-info">md5: <?php print $single_release['files']['zip']['md5']; ?></div>
                    <div class="release__file-info">Размер: <?php print format_size($single_release['files']['zip']['size']); ?></div>
                  </div>
                  <div class="release__file">
                    <a href="<?php print $single_release['files']['tar']['url']; ?>" class="release__file-title">TAR.GZ</a>
                    <div class="release__file-info">md5: <?php print $single_release['files']['tar']['md5']; ?></div>
                    <div class="release__file-info">Размер: <?php print format_size($single_release['files']['tar']['size']); ?></div>
                  </div>

                  <h4 class="release__drush-title icon-terminal">Drush</h4>
                  <div class="release__drush-command">drush dl <?php print strtolower($field_project_short_name[0]['value']); ?>-<?php print $single_version; ?> -y</div>
                </div>
                <?php $i++; ?>
              <?php } elseif (!empty($matches)) { ?>
                <div class="release dev">
                  <div class="release__label"><?php print $single_version; ?>
                    <?php if ($single_release['date']):?>
                      <span class="release__date">от <?php  print format_date($single_release['date'], 'short'); ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="release__infolink"><a href="<?php print $single_release['release_link']; ?>" target="_blank">Список изменений версии <?php print $single_version; ?></a></div>
                  <h4 class="release__download-title icon-download">Загрузки</h4>
                  <div class="release__file">
                    <a href="<?php print $single_release['files']['zip']['url']; ?>" class="release__file-title">ZIP</a>
                    <div class="release__file-info">md5: <?php print $single_release['files']['zip']['md5']; ?></div>
                    <div class="release__file-info">Размер: <?php print format_size($single_release['files']['zip']['size']); ?></div>
                  </div>
                  <div class="release__file">
                    <a href="<?php print $single_release['files']['tar']['url']; ?>" class="release__file-title">TAR.GZ</a>
                    <div class="release__file-info">md5: <?php print $single_release['files']['tar']['md5']; ?></div>
                    <div class="release__file-info">Размер: <?php print format_size($single_release['files']['tar']['size']); ?></div>
                  </div>

                  <h4 class="release__drush-title icon-terminal">Drush</h4>
                  <div class="release__drush-command">drush dl <?php print strtolower($field_project_short_name[0]['value']); ?>-<?php print $single_version; ?> -y</div>

                  <div class="release__alert">
                    <i class="icon-warning"></i> Обратите внимание, что dev версии предназначены для разработчиков и могут содержать ошибки, которые могут привести к поломке сайта.
                  </div>
                </div>
              <?php } ?>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="gl-s-1 gl-s-lg-12-24">
    <h2 class="project__question__title">Вопросы</h2>
    <?php print views_embed_view('question_nodes', 'questions_in_project'); ?>
    <a href="/node/add/question?project=<?php print $node->nid; ?>" class="icon-help-circled">Задать вопрос по проекту</a>
  </div>

</article>
