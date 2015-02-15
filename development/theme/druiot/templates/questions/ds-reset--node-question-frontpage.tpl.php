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

  <div class="question__author">
    <?php print theme('user_picture', array('account' => $author)); ?>
    <a
      href="<?php print url('user/' . $author->uid); ?>"><?php print $author->name; ?></a>
  </div>
</article>
