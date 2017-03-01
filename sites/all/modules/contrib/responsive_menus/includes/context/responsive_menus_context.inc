<?php
/**
 * @file
 * Include for Responsive Menus Context integration.
 */

/**
 * Options form callack for context integration.
 */
function responsive_menus_context_options_form($form, &$form_state) {
  $form = array();
  $rm_defaults = array();
  $rm_defaults['responsive_menus_style_settings'] = array();
  // Gather enabled styles.
  $styles = responsive_menus_styles();
  foreach ($styles as $style => $values) {
    $style_options[$style] = $values['name'];
  }
  // Get style settings form elements from ajax or the currently enabled style.
  if (!empty($form_state['values']['responsive_menus_style'])) {
    $rm_defaults = $form_state['values'];
    $current_style = $form_state['values']['responsive_menus_style'];
  }
  else {
    $current_style = variable_get('responsive_menus_style', 'responsive_menus_simple');
  }
  // Option to not require jquery_update.
  $form['responsive_menus_no_jquery_update'] = array(
    '#type' => 'checkboxes',
    '#description' => t("If the style you want requires newer jQuery version and you don't want to use jquery_update module."),
    '#options' => array(1 => t('I will provide my own jQuery library.')),
    '#default_value' => responsive_menus_var_get('responsive_menus_no_jquery_update', array(1 => 0), $rm_defaults),
  );
  $jq_update_ignore = $form['responsive_menus_no_jquery_update']['#default_value'];
  $style_info = responsive_menus_style_load($current_style, $jq_update_ignore);

  $form['responsive_menus_style'] = array(
    '#type' => 'select',
    '#title' => t('Responsive menu style'),
    '#options' => $style_options,
    '#default_value' => $current_style,
    '#ajax' => array(
      'callback' => 'responsive_menus_context_style_settings_form',
      'wrapper' => 'rm-style-options',
      'method' => 'replace',
      'effect' => 'fade',
    ),
  );
  $form['responsive_menus_style_settings'] = array(
    '#title' => t('Style settings'),
    '#description' => t('Settings for chosen menu style.'),
    '#prefix' => '<div id="rm-style-options">',
    '#suffix' => '</div>',
    '#type' => 'fieldset',
    '#tree' => TRUE,
  );
  // Which selector to use info.
  if (!empty($style_info['selector'])) {
    $form['responsive_menus_style_settings']['selector_info'] = array(
      '#type' => 'item',
      '#title' => t('Selector(s) to use for this style:'),
      '#markup' => '<div class="messages status">' . $style_info['selector'] . '</div>',
    );
  }
  // Build additional style settings from style plugins.
  if (!empty($styles[$current_style]['form']) && function_exists($styles[$current_style]['form'])) {
    $styles_function = $styles[$current_style]['form'];
    foreach ($styles_function() as $name => $element) {
      $element['#default_value'] = responsive_menus_var_get($name, $element['#default_value'], $rm_defaults['responsive_menus_style_settings']);
      $form['responsive_menus_style_settings'][$name] = $element;
    }
  }

  return $form;
}

/**
 * Ajax callback for switching styles.
 */
function responsive_menus_context_style_settings_form($form, $form_state) {
  return $form['reactions']['plugins']['responsive_menus_general']['responsive_menus_style_settings'];
}
