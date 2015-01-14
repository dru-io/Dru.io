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
$answers_count = dlcommunity_question_answers_count($node->nid);
$author = user_load($node->uid);
$has_answer = dlcommunity_question_is_best_answer($node->nid);
?>
<article
  class="<?php print $clean_classes; ?> <?php $has_answer ? print 'has-answer': ''; ?> icon-chat-empty"<?php print $attributes; ?>>

  <?php print render($title_prefix); ?>
  <h2 class="question__title">
    <?php $has_answer ? print '<i class="icon-ok" title="Вопрос помечен как решённый"></i>': ''; ?>
    <a
      href="<?php print $node_url; ?>"><?php print $title; ?></a></h2>
  <?php print render($title_suffix); ?>

  <div class="gl-g">
    <div class="question__rating icon-chart-bar gl-s-1 gl-s-md-1-3 gl-s-lg-2-24">
      <?php
      $rating_data = rate_get_results('node', $node->nid, 1);
      print $rating_data['rating'];
      ?>
    </div>

    <div class="question__answer icon-comment-empty gl-s-1 gl-s-md-1-3 gl-s-lg-2-24">
      <?php
      print $answers_count;
      ?>
    </div>

    <?php if ($field_drupal_version): ?>
      <div class="question__drupal-version icon-drupal gl-s-1 gl-s-md-1-3 gl-s-lg-3-24">
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
      <div class="question__projects icon-plug gl-s-1 gl-s-md-1 gl-s-lg-17-24">
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
  </div>

  <div class="question__author"><?php print theme('user_picture', array('account' => $author)); ?>
    <a
      href="<?php print url('user/' . $author->uid); ?>"><?php print $author->name; ?></a>
  </div>
</article>
