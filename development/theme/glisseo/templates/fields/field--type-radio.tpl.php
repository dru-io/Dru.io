<?php
/**
 * New checkbox styles.
 */
?>
<input
  id="<?php print render($element['#id']); ?>"
  name="<?php print render($element['#name']); ?>"
  value="<?php print render($element['#return_value']); ?>"
  class="form-radio glisseo-radio"
  type="radio"
  <?php $element['#return_value'] == $element['#default_value']? print " checked='checked'" : ''; ?>
  <?php isset($element['#disabled']) && $element['#disabled'] ? print " disabled" : ''; ?>>
<label class="glisseo-radio-wrapper" for="<?php print render($element['#id']); ?>">
  <div class="glisseo-radio-mark"></div>
</label>
