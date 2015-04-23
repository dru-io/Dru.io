<?php

/**
 * @file
 * Provide customizations for some components of the configuration UI.
 */

/**
 * Theme function for displaying form buttons
 */
function theme_configuration_ui_form_buttons(&$vars) {
  $output = drupal_render_children($vars['element']);
  return !empty($output) ? "<div class='buttons clear-block'>{$output}</div>" : '';
}


function theme_configuration_table($form) {
  $element = $form['form'];
  $rows = array();
  $header = $element['#header'];

  // Generate a table row for each selectable item in #options.
  foreach (element_children($element['items']) as $key) {

    $row = array();

    $row['items'] = drupal_render($element['items'][$key]);
    foreach (array_keys($header) as $item) {
      $row[$item] = drupal_render($element[$item][$key]);
    }

    $rows[] = $row;
  }

  // Add an empty header or a "Select all" checkbox to provide room for the
  // checkboxes/radios in the first table column.

  // Add a "Select all" checkbox.
  drupal_add_js('misc/tableselect.js');
  array_unshift($header, array('class' => array('select-all')));

  return theme('table', array('header' => $header, 'rows' => $rows));
}
