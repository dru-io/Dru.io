<?php

/**
 * @file
 * Preprocesses for custom HOOK's.
 */

/**
 * Implements template_preprocess_HOOK().
 */
function template_preprocess_druio_question_meta_field(&$variables) {
  $entity = $variables['entity'];
  $items = [];

  // Drupal version.
  if (!$entity->field_drupal_version->isEmpty()) {
    $items[] = [
      'label' => $entity->field_drupal_version->value,
      'class' => ['drupal-version'],
    ];
  }

  // Categories.
  foreach ($entity->field_question_categories->referencedEntities() as $category) {
    $items[] = [
      'label' => $category->label(),
      'class' => ['category'],
    ];
  }

  // Projects.
  foreach ($entity->field_project_references->referencedEntities() as $project) {
    $items[] = [
      'label' => $project->label(),
      'class' => ['project'],
    ];
  }

  $variables['items'] = $items;
}
