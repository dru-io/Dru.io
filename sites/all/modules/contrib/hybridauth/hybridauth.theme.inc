<?php
/**
 * @file
 * Theme functions for the HybridAuth module.
 */

/**
 * Template preprocess function for hybridauth_widget.
 */
function template_preprocess_hybridauth_widget(&$vars, $hook) {
  $element = $vars['element'];
  $vars['providers'] = array();
  foreach ($element['providers'] as $provider) {
    $vars['providers'][] = l(render($provider['text']), $provider['path'], $provider['options']);
  }
}

/**
 * Template preprocess function for hybridauth_provider_icon.
 */
function template_preprocess_hybridauth_provider_icon(&$vars, $hook) {
  $icon_pack_classes = array(
    'hybridauth-icon',
    drupal_html_class($vars['provider_id']),
    drupal_html_class('hybridauth-icon-' . $vars['icon_pack']),
    drupal_html_class('hybridauth-' . $vars['provider_id']),
    drupal_html_class('hybridauth-' . $vars['provider_id'] . '-' . $vars['icon_pack']),
  );

  ctools_include('plugins');
  // Icon pack modifications.
  if ($function = ctools_plugin_load_function('hybridauth', 'icon_pack', $vars['icon_pack'], 'icon_classes_callback')) {
    $function($icon_pack_classes, $vars['provider_id']);
  }
  // Provider modifications.
  if ($provider = hybridauth_get_provider($vars['provider_id'])) {
    if ($function = ctools_plugin_get_function($provider, 'icon_classes_callback')) {
      $function($icon_pack_classes);
    }
  }

  $vars['icon_pack_classes'] = implode(' ', $icon_pack_classes);
}
