<?php

namespace Drupal\feeds\Feeds\Target;

use Drupal\feeds\Plugin\Type\Target\FieldTargetBase;

/**
 * Defines a number field mapper.
 *
 * @FeedsTarget(
 *   id = "number",
 *   field_types = {
 *     "decimal",
 *     "float",
 *     "list_float"
 *   }
 * )
 */
class Number extends FieldTargetBase {

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    $values['value'] = trim($values['value']);

    if (!is_numeric($values['value'])) {
      $values['value'] = '';
    }
  }

}
