<?php

namespace Drupal\feeds\Feeds\Target;

/**
 * Defines an integer field mapper.
 *
 * @FeedsTarget(
 *   id = "integer",
 *   field_types = {
 *     "integer",
 *     "list_integer"
 *   }
 * )
 */
class Integer extends Number {

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    $value = trim($values['value']);
    $values['value'] = is_numeric($value) ? (int) $value : '';
  }

}
