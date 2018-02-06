<?php

/**
 * @file
 * Custom theme hooks.
 */

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Template\Attribute;


/**
 * Implements hook_theme().
 */
function druio_theme_theme($existing, $type, $theme, $path) {
  $templates_path = "$path/templates/theme";
  return [
    'druio_theme_top_line_user' => [
      'variables' => [],
      'path' => $templates_path,
    ],
    'druio_theme_top_line_search' => [
      'variables' => [],
      'path' => $templates_path,
    ],
    'druio_theme_created_relative' => [
      'variables' => [
        'timestamp' => NULL,
        'format' => 'd.m.Y',
      ],
      'path' => $templates_path,
    ],
  ];
}

/**
 * Implements hook_preprocess_HOOK().
 */
function druio_theme_preprocess_druio_theme_top_line_user(&$variables) {
  $cache = CacheableMetadata::createFromObject([]);
  $cache->addCacheContexts(['user']);
  $variables['user'] = NULL;
  $variables['is_authenticated'] = FALSE;
  $attributes = new Attribute();
  $attributes->addClass('druio-theme-top-line-user');

  if (\Drupal::currentUser()->isAuthenticated()) {
    $attributes->addClass('is-authenticated');
    $variables['is_authenticated'] = TRUE;
    $user = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->load(\Drupal::currentUser()->id());
    $variables['user'] = $user;
    // Add user as cache dependency.
    $cache->addCacheableDependency($user);
  }
  else {
    $attributes->addClass('is-anonymous');
  }

  // Apply all cache dependencies to render array.
  $cache->applyTo($variables);
  // Add attributes to variable.
  $variables['attributes'] = $attributes;
}
