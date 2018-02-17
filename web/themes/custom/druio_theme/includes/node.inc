<?php

/**
 * @file
 * Theme and preprocess functions for nodes
 */

/**
 * Implements hook_preprocess_HOOK().
 */
function druio_theme_preprocess_node__question__teaser(&$variables) {
  $node = $variables['elements']['#node'];
  /** @var \Drupal\druio_question\DruioQuestionService $druio_question */
  $druio_question = \Drupal::service('druio_question');
  $variables['answers_count'] = $druio_question->getQuestionAnswerCount($node->id());
  $variables['last_answer_time'] = [
    '#theme' => 'druio_theme_created_relative',
    '#timestamp' => $druio_question->getQuestionLastAnswerCreatedTime($node->id()),
  ];
}