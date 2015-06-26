<?php
/**
 * @file
 * Question full layout.
 */
?>
<<?php print $layout_wrapper; ?> <?php print $layout_attributes; ?> class="<?php print $clean_classes; ?>">
<?php print render($title_suffix['contextual_links']); ?>
<?php if ($first): ?>
  <<?php print $first_wrapper; ?> class="<?php print $first_classes; ?>">
    <?php print $first; ?>
  </<?php print $first_wrapper; ?>>
<?php endif; ?>
</<?php print $layout_wrapper; ?>>
