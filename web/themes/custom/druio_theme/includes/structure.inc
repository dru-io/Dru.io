<?php

/**
 * @file
 * Theme and preprocess functions for breadcrumbs, messages, tabs..etc
 */

/**
 * Implements hook_preprocess_HOOK().
 */
function druio_theme_preprocess_page(&$variables) {
  $variables['top_line_auth'] = [
    '#theme' => 'druio_theme_top_line_user',
  ];
  $variables['top_line_search'] = [
    '#theme' => 'druio_theme_top_line_search',
  ];
}

/**
 * Implements hook_preprocess_HOOK().
 */
function druio_theme_preprocess_html(&$variables) {
  $sidebar_count = 0;
  foreach ($variables['page'] as $element_key => $element) {
    if (preg_match("/sidebar_(.+)/", $element_key)) {
      $variables['attributes']['class'][] = 'is-layout-' . str_replace('_', '-', $element_key);
      $sidebar_count++;
    }
  }

  if ($sidebar_count == 0) {
    $variables['attributes']['class'][] = 'is-layout-no-sidebars';
  }
}
