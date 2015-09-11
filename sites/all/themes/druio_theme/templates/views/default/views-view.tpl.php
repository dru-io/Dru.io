<?php
/**
 * @file
 * Main view template.
 *
 * Variables available:
 * - $classes_array: An array of classes determined in
 *   template_preprocess_views_view(). Default classes are:
 *     .view
 *     .view-[css_name]
 *     .view-id-[view_name]
 *     .view-display-id-[display_name]
 *     .view-dom-id-[dom_id]
 * - $classes: A string version of $classes_array for use in the class attribute
 * - $css_name: A css-safe version of the view name.
 * - $css_class: The user-specified classes names, if any
 * - $header: The view header
 * - $footer: The view footer
 * - $rows: The results of the view query, if any
 * - $empty: The empty text to display if the view is empty
 * - $pager: The pager next/prev links to display, if any
 * - $exposed: Exposed widget form/info to display
 * - $feed_icon: Feed icon to display, if any
 * - $more: A link to view more, if any
 *
 * @ingroup views_templates
 */
?>
<section
  class="view-<?php print $css_name; ?>-<?php print str_replace('_', '-', $view->current_display); ?>">
  <?php print render($title_prefix); ?>
  <?php if ($title): ?>
    <?php print $title; ?>
  <?php endif; ?>
  <?php print render($title_suffix); ?>
  <?php if ($header): ?>
    <header class="header">
      <?php print $header; ?>
    </header>
  <?php endif; ?>

  <?php if ($exposed): ?>
    <section class="filters">
      <?php print $exposed; ?>
    </section>
  <?php endif; ?>

  <?php if ($attachment_before): ?>
    <section class="attachment attachment-before">
      <?php print $attachment_before; ?>
    </section>
  <?php endif; ?>

  <?php if ($rows): ?>
    <section class="content">
      <?php print $rows; ?>
    </section>
  <?php elseif ($empty): ?>
    <section class="view-empty">
      <?php print $empty; ?>
    </section>
  <?php endif; ?>

  <?php if ($pager): ?>
    <?php print $pager; ?>
  <?php endif; ?>

  <?php if ($attachment_after): ?>
    <section class="attachment attachment-after">
      <?php print $attachment_after; ?>
    </section>
  <?php endif; ?>

  <?php if ($more): ?>
    <?php print $more; ?>
  <?php endif; ?>

  <?php if ($footer): ?>
    <footer class="footer">
      <?php print $footer; ?>
    </footer>
  <?php endif; ?>

  <?php if ($feed_icon): ?>
    <div class="feed-icon">
      <?php print $feed_icon; ?>
    </div>
  <?php endif; ?>

</section>