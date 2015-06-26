<?php if ($element['#view_mode'] == 'teaser'): ?>
  <div class="question__info project">
    <?php
    $projects = array();
    foreach ($items as $item):
      $projects[] = $item['#markup'];
    endforeach;
    print implode(', ', $projects);
    ?>
  </div>
<?php endif; ?>
<?php if ($element['#view_mode'] == 'full'): ?>
  <div class="projects">
    <?php
    $projects = array();
    foreach ($items as $item) {
      $projects[] = $item['#markup'];
    }
    print implode(', ', $projects);
    ?>
  </div>
<?php endif; ?>
