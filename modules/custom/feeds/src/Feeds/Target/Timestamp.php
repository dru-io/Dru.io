<?php

namespace Drupal\feeds\Feeds\Target;

/**
 * Defines a timestamp field mapper.
 *
 * @FeedsTarget(
 *   id = "timestamp",
 *   field_types = {
 *     "created",
 *     "timestamp"
 *   }
 * )
 */
class Timestamp extends Number {

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    $value = trim($values['value']);

    // This is a year value.
    if (ctype_digit($value) && strlen($value) === 4) {
      $value = strtotime('January ' . $value);
    }
    $values['value'] = is_numeric($value) ? (int) $value : '';
  }

}
