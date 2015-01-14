<?php
/**
 * @file
 * Returns the HTML for a wrapping container around comments.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728230
 */
// Render the comments and form first to see if we need headings.
$comments = render($content['comments']);
$comment_form = render($content['comment_form']);
?>
<section class="question--comments comments gl-g <?php print $classes; ?>"<?php print $attributes; ?>>
  <div class="gl-s-5-24 gl-s-lg-3-24 gl-s-xl-2-24">
    &nbsp;
  </div>

  <div class="gl-s-19-24 gl-s-lg-21-24 gl-s-xl-22-24 question--comments__content">
    <?php print $comments; ?>
  </div>
</section>