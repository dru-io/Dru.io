<?php
/**
 * @file
 * Main hooks for base theme.
 */

/**
 * Auto rebuild theme registry.
 */
if (theme_get_setting('glisseo_rebuild_registry') && !defined('MAINTENANCE_MODE')) {
  system_rebuild_theme_data();
  drupal_theme_rebuild();
}

/**
 * Implements template_preprocess_html().
 */
function glisseo_preprocess_html(&$variables) {
  // HTML Attributes
  $html_attributes = array(
    'lang' => $variables['language']->language,
    'dir' => $variables['language']->dir,
  );

  $variables['html_attributes'] = drupal_attributes($html_attributes);

  // RDF namespaces.
  if ($variables['rdf_namespaces']) {
    $prefixes = array();
    foreach (explode("\n  ", ltrim($variables['rdf_namespaces'])) as $namespace) {
      $prefixes[] = str_replace('="', ': ', substr($namespace, 6, -1));
    }
    $variables['rdf_namespaces'] = ' prefix="' . implode(' ', $prefixes) . '"';
  }

  // Add template suggestions for 404 and 403 errors.
  // F.e.: html--404.tpl.php
  $status = drupal_get_http_header("status");
  if($status == "404 Not Found") {
    $variables['theme_hook_suggestions'][] = 'html__404';
  }

  if($status == "403 Forbidden") {
    $variables['theme_hook_suggestions'][] = 'html__403';
  }
}

/**
 * Implements hook_preprocess_page().
 */
function glisseo_preprocess_page(&$variables, $hook) {
  // Add template suggestions for 404 and 403 errors.
  // F.e.: page--404.tpl.php
  $status = drupal_get_http_header("status");
  if($status == "404 Not Found") {
    $variables['theme_hook_suggestions'][] = 'page__404';
  }

  if($status == "403 Forbidden") {
    $variables['theme_hook_suggestions'][] = 'page__403';
  }

}

/**
 * Implements hook_preprocess_block().
 */
function glisseo_preprocess_block(&$variables, $hook) {
  $variables['title'] = isset($variables['block']->subject) ? $variables['block']->subject : '';
}

/**
 * Implements template_preprocess_node().
 */
function glisseo_preprocess_node(&$variables) {
  // New classes. More clear then defaults.
  $variables['clean_classes_array'] = array();
  $variables['clean_classes_array'][] = drupal_html_class($variables['type']);
  $variables['clean_classes_array'][] = drupal_html_class($variables['view_mode']);
  // We add 'teaser' class, if content is teaser and don't have 'teaser' vew mode.
  if ($variables['teaser'] && !in_array('teaser', $variables['clean_classes_array'])) {
    $variables['clean_classes_array'][] = drupal_html_class('teaser');
  }
  // If content is sticky, we add special class.
  if ($variables['sticky']) {
    $variables['clean_classes_array'][] = drupal_html_class('sticky');
  }
  // We add that class only when contextual links enabled.
  if (module_exists('contextual')) {
    $variables['clean_classes_array'][] = drupal_html_class('contextual-links-region');
  }

  // Generate clean classes variable.
  $variables['clean_classes'] = implode(' ', $variables['clean_classes_array']);

  // Work with Node object.
  $node = $variables['node'];
  // Save field values to variables.
  foreach ($node as $label => $data) {
    // Is label is field.
    if (preg_match("/field_(.*)?/i", $label, $matches)) {
      $variables[$label] = field_get_items('node', $node, $label);
    }
  }
}

/**
* Implements hook_theme().
*/
function glisseo_theme($existing, $type, $theme, $path) {
  $theme = array();

  // Rewrite checkboxes.
  if (theme_get_setting('glisseo_new_checkboxes')) {
    $theme['checkbox'] = array(
      'render element' => 'element',
      'template' => 'templates/fields/field--type-checkbox',
    );
  }

  // Rewrite radios.
  if (theme_get_setting('glisseo_new_radios')) {
    $theme['radio'] = array(
      'render element' => 'element',
      'template' => 'templates/fields/field--type-radio',
    );
  }

  return $theme;
}

/**
 * Implements theme_textarea().
 * Disable grippie.
 */
function glisseo_textarea($variables) {
  if (theme_get_setting('glisseo_disable_grippie')) {
    $variables['element']['#resizable'] = FALSE;
  }

  return theme_textarea($variables);
}
