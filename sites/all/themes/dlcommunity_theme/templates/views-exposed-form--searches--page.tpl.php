<?php
/**
 * @file
 * Search page form alter.
 */
$form['reset']['#attributes'] = array(
  'class' => array('button-preview'),
);
?>
<div class="gl-g">
  <div class="gl-s-lg-18-24">
    <?php print render($form['s']); ?>
  </div>

  <div class="gl-s-lg-3-24">
    <?php print render($form['submit']); ?>
  </div>

  <div class="gl-s-lg-3-24">
    <?php print render($form['reset']); ?>
  </div>
</div>
