<?php
/**
 * @file
 * Returns the HTML for comments.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728216
 */
?>
<article class="question--comment <?php print $classes; ?> clearfix"<?php print $attributes; ?>>

  <div class="author-info">
    <?php print $author; ?> <span class="comment-date">— <?php print date('j.n.Y - G:i', $comment->created); ?></span>
  </div>
  <?php
  // We hide the comments and links now so that we can render them later.
  hide($content['links']);
  print render($content);
  ?>
  <?php print render($content['links']) ?>

</article><!-- /.comment -->