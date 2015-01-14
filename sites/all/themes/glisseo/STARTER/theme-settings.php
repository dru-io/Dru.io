<?php
/**
 * Implements hook_form_system_theme_settings_alter() function.
 *
 * Replace THEMENAME by yours.
 * If you wants to remove some elements from theme settings, use unset() function.
 */
function THEMENAME_form_system_theme_settings_alter(&$form, &$form_state, $form_id = NULL)  {
  // Bug workaround (#943212).
  if (isset($form_id)) {
    return;
  }

}
