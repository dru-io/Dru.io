<?php

/**
 * @file
 * Custom hook preprocesses.
 */

use Drupal\field\Entity\FieldConfig;

/**
 * Implements template_preprocess_HOOK().
 */
function template_preprocess_druio_author_min(&$variables) {
  $user = $variables['user'];
  $variables['username'] = $user->label();
  $variables['url'] = $user->toUrl()->toString();
  $variables['avatar'] = FALSE;
  $avatar_image_style = $style = \Drupal::entityTypeManager()
    ->getStorage('image_style')
    ->load('user_avatar_min');
  if (!$user->user_picture->isEmpty()) {
    $variables['avatar'] = $avatar_image_style->buildUrl($user->user_picture->entity->uri->value);
  }
  else {
    $field = FieldConfig::loadByName('user', 'user', 'user_picture');
    $default_image = $field->getSetting('default_image');
    if (!empty($default_image['uuid'])) {
      $file = \Drupal::entityTypeManager()
        ->getStorage('file')
        ->loadByProperties(['uuid' => $default_image['uuid']]);
      $variables['avatar'] = $avatar_image_style->buildUrl(reset($file)->uri->value);
    }
  }
}
