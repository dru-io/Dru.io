<?php
/**
 * @file
 * Returns the HTML for comments.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728216
 */
?>
<article
  class="<?php print $classes; ?> clearfix gl-g"<?php print $attributes; ?>>
  <div class="comment-left gl-s-5-24 gl-s-lg-2-24">
    <?php print $picture; ?>
  </div>

  <div class="comment-content gl-s-19-24 gl-s-lg-22-24">

    <header class="comment-info">
      <div class="submitted">
        <?php print $author; ?>
        <span
          class="comment-date"><?php print date('j.n.Y - G:i', $comment->created); ?></span>

        <?php print render($content['links']) ?>

      </div>
    </header>
    <?php
    // We hide the comments and links now so that we can render them later.
    hide($content['links']);
    print render($content);
    ?>
  </div>

</article><!-- /.comment -->