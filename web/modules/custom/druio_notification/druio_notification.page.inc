<?php

/**
 * @file
 * Contains druio_notification.page.inc.
 *
 * Page callback for Druio notification entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Druio notification templates.
 *
 * Default template: druio_notification.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_druio_notification(array &$variables) {
  // Fetch DruioNotification Entity Object.
  $druio_notification = $variables['elements']['#druio_notification'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
