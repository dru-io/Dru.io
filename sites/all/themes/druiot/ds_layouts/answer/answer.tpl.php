<?php
/**
 * @file
 * Question full layout.
 */
?>
<<?php print $layout_wrapper; ?> <?php print $layout_attributes; ?> class="<?php print $clean_classes; ?>">
  <?php print render($title_suffix['contextual_links']); ?>

  <?php if ($left): ?>
    <<?php print $left_wrapper; ?> class="<?php print $left_classes; ?>">
      <?php print $left; ?>
    </<?php print $left_wrapper; ?>>
  <?php endif; ?>

  <?php if ($right): ?>
    <<?php print $right_wrapper; ?> class="<?php print $right_classes; ?>">
      <?php print $right; ?>
    </<?php print $right_wrapper; ?>>
  <?php endif; ?>
</<?php print $layout_wrapper; ?>>
