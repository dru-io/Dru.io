<?php
/**
 * @file
 * Question full layout.
 */
?>
<<?php print $layout_wrapper; ?> <?php print $layout_attributes; ?> class="<?php print $classes; ?>">
  <?php print render($title_suffix['contextual_links']); ?>

  <?php if ($first): ?>
    <<?php print $first_wrapper; ?> class="<?php print $first_classes; ?>">
      <?php print $first; ?>
    </<?php print $first_wrapper; ?>>
  <?php endif; ?>

  <?php if ($second): ?>
    <<?php print $second_wrapper; ?> class="<?php print $second_classes; ?>">
      <?php print $second; ?>
    </<?php print $second_wrapper; ?>>
  <?php endif; ?>

  <?php if ($third): ?>
    <<?php print $third_wrapper; ?> class="<?php print $third_classes; ?>">
      <?php print $third; ?>
    </<?php print $third_wrapper; ?>>
  <?php endif; ?>
</<?php print $layout_wrapper; ?>>
