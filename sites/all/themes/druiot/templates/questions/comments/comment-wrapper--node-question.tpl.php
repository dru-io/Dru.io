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
<section
  class="comments-to-question"<?php print $attributes; ?>>
  <?php print $comments; ?>
</section>