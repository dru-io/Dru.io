<?php
/**
 * @file
 * HTML for a node.
 *
 * Default variables you can find here: https://api.drupal.org/api/drupal/modules!node!node.tpl.php/7
 * New variables available:
 * - $clean_classes: This is replacement for default $classes. They are more
 *   tidy and handy. F.e.:
 *     .[node-type]
 *     .[display-mode]
 *     .teaser
 */
global $user;
?>
<article class="<?php print $clean_classes; ?>"<?php print $attributes; ?>>

  <div class="gl-g">
    <div class="gl-s-5-24 gl-s-lg-3-24 gl-s-xl-2-24 rating">
      <?php
      print rate_embed($node, 'vote_up_down', RATE_FULL);
      ?>
    </div>

    <div class="gl-s-19-24 gl-s-lg-15-24 gl-s-xl-16-24">
      <?php if ($title_prefix || $title_suffix || $display_submitted || !$page && $title): ?>
        <header>
          <?php print render($title_prefix); ?>
          <h2<?php print $title_attributes; ?>
            class="question__title"><?php print $title; ?></h2>
          <?php print render($title_suffix); ?>
        </header>
      <?php endif; ?>

      <?php
      hide($content['comments']);
      hide($content['links']);
      print render($content);
      ?>

      <?php if ($field_drupal_version): ?>
        <div class="question__drupal-version icon-drupal"><span class="question__drupal-version--label"></span>
          <?php
          $versions = array();

          foreach ($field_drupal_version as $version):
            $version = taxonomy_term_load($version['tid']);
            $versions[] = $version->name;
          endforeach;

          print rtrim(implode(', ', $versions), ',')
          ?>
        </div>
      <?php endif; ?>

      <?php if ($field_project_reference): ?>
        <div class="question__projects icon-plug"><span class="question__projects--label"></span>
          <?php
          $projects = array();

          foreach ($field_project_reference as $project):
            $project = node_load($project['target_id']);
            $projects[] = '<a href="'. url('node/'. $project->nid) .'">'. $project->title .'</a>';
          endforeach;

          print rtrim(implode(', ', $projects), ',')
          ?>
        </div>
      <?php endif; ?>

      <?php if (isset($content['links']['comment']['#links']['comment-add']['href'])): ?>
        <a href="/<?php print $content['links']['comment']['#links']['comment-add']['href']; ?>" class="answer--add-comment">Добавить комментарий</a>
      <?php endif; ?>
    </div>

    <div class="gl-s-1 gl-s-lg-6-24 gl-s-xl-6-24">
      <div class="question__user-info">
        <div class="question__date"><?php print format_date($node->created, 'ru_medium'); ?></div>
        <div class="question__author">
          <?php
          $author = user_load($node->uid);
          ?>
          <?php print theme('user_picture', array('account' => $author)); ?>
          <div class="question__user-name">
            <a
              href="<?php print url('user/' . $author->uid); ?>"><?php print $author->name; ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php print render($content['comments']); ?>

  <?php if (arg(0) != 'comment'): ?>
  <h2 class="answers__title">Ответы</h2>
  <?php
  print views_embed_view('answers', 'answers');

  // Load Answer form;
  if (drupal_valid_path('node/add/answer')) {
    module_load_include('inc', 'node', 'node.pages');
    $form = node_add('answer');
    $output = drupal_render($form);
    print $output;
  }
  ?>
  <?php endif; ?>
</article>
