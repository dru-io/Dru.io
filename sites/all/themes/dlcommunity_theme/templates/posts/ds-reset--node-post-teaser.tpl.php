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
?>
<article class="<?php print $clean_classes; ?>"<?php print $attributes; ?>>

  <?php if ($title_prefix || $title_suffix || $display_submitted || !$page && $title): ?>
    <header>
      <?php print render($title_prefix); ?>
      <?php if (!$page && $title): ?>
        <h2<?php print $title_attributes; ?>><a
            href="<?php print $node_url; ?>"><?php print $title; ?></a></h2>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
    </header>
  <?php endif; ?>

  <?php
  hide($content['comments']);
  hide($content['links']);
  print render($content);
  ?>

  <div class="post-bottom">
    <div class="post-bottom__item">
      <?php print rate_embed($node, 'vote_up_down', RATE_FULL); ?>
    </div>
    <div class="post-bottom__item post-bottom__date">
      Дата добавления: <?php print format_date($node->created, 'ru_medium'); ?>
    </div>
    <div class="post-bottom__item">
      Автор: <a href="<?php print url('user/'.$node->uid); ?>"><?php print $node->name; ?></a>
    </div>
  </div>

</article>
