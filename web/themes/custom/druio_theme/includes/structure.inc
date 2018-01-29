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
}