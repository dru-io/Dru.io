<?php
/**
 * Implements hook_form_system_theme_settings_alter() function.
 */
function glisseo_form_system_theme_settings_alter(&$form, $form_state, $form_id = NULL) {
  // Bug workaround (#943212).
  if (isset($form_id)) {
    return;
  }

  // We move default theme settings to new fieldset.
  $form['theme_settings_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Base theme settings'),
    '#weight' => 1,
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );

  $form['theme_settings_fieldset']['theme_settings'] = $form['theme_settings'];
  unset($form['theme_settings']);
  $form['theme_settings_fieldset']['logo'] = $form['logo'];
  unset($form['logo']);
  $form['theme_settings_fieldset']['favicon'] = $form['favicon'];
  unset($form['favicon']);

  $form['developers_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Development'),
    '#weight' => 2,
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );

  $form['developers_settings']['glisseo_rebuild_registry'] = array(
    '#weight' => 1,
    '#type' => 'checkbox',
    '#title' => t('Rebuild theme registry on every page load.'),
    '#default_value' => theme_get_setting('glisseo_rebuild_registry')
  );

  $form['developers_settings']['glisseo_body_classes'] = array(
    '#weight' => 2,
    '#type' => 'checkbox',
    '#title' => t('Add HTML classes to body-tag element.'),
    '#description' => t('Adds classes to body element. F.e.: html front logged-in page-node ...'),
    '#default_value' => theme_get_setting('glisseo_body_classes')
  );

  $form['developers_settings']['glisseo_new_checkboxes'] = array(
    '#weight' => 3,
    '#type' => 'checkbox',
    '#title' => t('New checkbox style.'),
    '#description' => t('Checkboxes will be displayed as switchers.'),
    '#default_value' => theme_get_setting('glisseo_new_checkboxes')
  );

  $form['developers_settings']['glisseo_new_radios'] = array(
    '#weight' => 4,
    '#type' => 'checkbox',
    '#title' => t('New radio-button style.'),
    '#description' => t('Radio-buttons will be themed.'),
    '#default_value' => theme_get_setting('glisseo_new_radios')
  );

  $form['developers_settings']['glisseo_disable_grippie'] = array(
    '#weight' => 5,
    '#type' => 'checkbox',
    '#title' => t('Disable textarea grippie.'),
    '#description' => t('Modern browser support textarea resize out the box. You can disable default grippie.'),
    '#default_value' => theme_get_setting('glisseo_disable_grippie')
  );

  // @TODO: manage all files without custom code.
  // This is some workaround for allow submit theme form with managed_file.
  //$theme = $GLOBALS['theme_key'];
  //$themes = list_themes();
  //$form_state['build_info']['files'][] = str_replace("/$theme.info", '', $themes[$theme]->filename) . '/theme-settings.php';
  //$form['#submit'][] = 'glisseo_theme_settings_form_submit';
}
