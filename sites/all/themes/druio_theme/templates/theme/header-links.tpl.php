<?php

/**
 * @file
 * Theme template defined in template.php of druio_theme theme.
 */
?>
<div class="header-links">
  <?php if (isset($links)): ?>
    <?php foreach ($links as $link): ?>
      <a href="<?php print $link['href']; ?>" class="<?php print $link['classes']; ?>" <?php print $link['attributes']; ?>>
        <?php print $link['title']; ?>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
