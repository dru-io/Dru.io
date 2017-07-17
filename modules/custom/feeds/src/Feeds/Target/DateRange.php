<?php

namespace Drupal\feeds\Feeds\Target;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\feeds\FieldTargetDefinition;

/**
 * Defines a daterange field mapper.
 *
 * @FeedsTarget(
 *   id = "daterange",
 *   field_types = {"daterange"}
 * )
 */
class DateRange extends DateTime {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('value')
      ->addProperty('end_value');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    $values['value'] = $this->prepareDateValue($values['value']);
    $values['end_value'] = $this->prepareDateValue($values['end_value']);
  }

}
