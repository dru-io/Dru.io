<?php

/**
 * Implementation of hook_features_export_options. [component_hook]
 * @return mixed
 */
function ocupload_templates_features_export_options() {
  return db_select('ocupload_templates', 'oc')
    ->fields('oc', array('mask', 'mask'))
    ->execute()
    ->fetchAllKeyed();
}

/**
 * Implements hook_features_export().
 */
function ocupload_templates_features_export($data, &$export, $module_name = '') {
  $export['dependencies']['ocupload'] = 'ocupload';
  foreach ($data as $mask) {
    $export['features']['ocupload_templates'][$mask] = $mask;
  }
  return array();
}

/**
 * Implements hook_features_export_render().
 */
function ocupload_templates_features_export_render($module_name, $data) {
  $items = array();
  foreach ($data as $mask) {
    $template = db_select('ocupload_templates', 'oc')
      ->fields('oc')
      ->condition('mask', $mask)
      ->execute()
      ->fetchAssoc();
    unset($template['tid']);
    $items[$mask] = $template;
  }

  $code = "  \$items = " . features_var_export($items, '  ') . ";\n";
  $code .= '  return $items;';
  return array('ocupload_templates' => $code);
}

/**
 * Implements hook_features_revert().
 */
function ocupload_templates_features_revert($module) {
  $defaults = features_get_default('ocupload_templates', $module);
  // Revert.
  foreach ($defaults as $template) {
    _ocupload_templates_save_template($template);
  }
}

/**
 * Saves a profile field to the database.
 *
 * @param array $field_data
 *   The field data to save.
 */
function _ocupload_templates_save_template($template) {
  if (!db_query("SELECT * FROM {ocupload_templates} WHERE mask = :mask", array(':mask' => $template['mask']))->fetchObject()) {
    drupal_write_record('ocupload_templates', $template);
  }
  else {
    drupal_write_record('ocupload_templates', $template, array('mask'));
  }
}
