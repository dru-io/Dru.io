<?php
/**
 * @file
 * Zocial icon pack.
 */

/**
 * Plugin declaration.
 */
$plugin = array(
  'title' => t('Zocial'),
  'initialize_callback' => 'hybridauth_zocial_initialize_callback',
  'icon_classes_callback' => 'hybridauth_zocial_icon_classes_callback',
);

function hybridauth_zocial_initialize_callback() {
  $path = 'sites/all/libraries/zocial';
  if (module_exists('libraries')) {
    $path = libraries_get_path('zocial');
  }
  drupal_add_css($path . '/css/zocial.css');
}

function hybridauth_zocial_icon_classes_callback(&$classes, $provider_id) {
  $additional_classes = array('zocial', 'icon');
  if ($provider_id == 'Vkontakte') {
    $additional_classes[] = 'vk';
  }
  $classes = array_merge($classes, $additional_classes);
}
