<?php
/**
 * @file
 * Question full layout.
 */
?>
<<?php print $layout_wrapper; ?> <?php print $layout_attributes; ?> class="<?php print $classes; ?>">
  <?php print render($title_suffix['contextual_links']); ?>

  <?php if ($main): ?>
    <?php print $main; ?>
  <?php endif; ?>

</<?php print $layout_wrapper; ?>>
