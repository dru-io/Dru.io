<?php
/**
 * New checkbox styles.
 */
?>
<input
  id="<?php print render($element['#id']); ?>"
  name="<?php print render($element['#name']); ?>"
  value="<?php print render($element['#default_value']); ?>"
  class="form-checkbox glisseo-checkbox"
  type="checkbox"
  <?php $element['#checked'] ? print " checked='checked'" : ''; ?>
  <?php isset($element['#disabled']) && $element['#disabled'] ? print " disabled" : ''; ?>>
<label class="glisseo-checkbox-switcher" for="<?php print render($element['#id']); ?>">
  <div class="glisseo-checkbox-switcher-container">
    <div class="left"><?php print t('Yes'); ?></div>
    <div class="mid"></div>
    <div class="right"><?php print t('No'); ?></div>
  </div>
</label>
