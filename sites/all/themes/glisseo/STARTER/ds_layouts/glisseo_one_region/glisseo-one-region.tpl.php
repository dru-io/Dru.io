<?php
/**
 * @file
 * Question full layout.
 */
?>
<<?php print $layout_wrapper; ?> <?php print $layout_attributes; ?> class="<?php print $classes; ?>">
  <?php print render($title_suffix['contextual_links']); ?>

  <?php if ($main): ?>
    <<?php print $main_wrapper; ?> class="<?php print $main_classes; ?>">
      <?php print $main; ?>
    </<?php print $main_wrapper; ?>>
  <?php endif; ?>

</<?php print $layout_wrapper; ?>>
