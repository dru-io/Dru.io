<?php
/**
 * @file
 * Block template.
 *
 * Available variables:
 *
 * - $block_id: Blocks unique id (integer).
 * - $block_html_id: Block system name.
 * - $classes: Classes list for this block.
 * - $title: Block title.
 */
?>

<?php print render($title_prefix); ?>
<?php if ($title): ?>
  <h2 class="title"><?php print $title; ?></h2>
<?php endif; ?>
<?php print render($title_suffix); ?>

<?php print $content; ?>
