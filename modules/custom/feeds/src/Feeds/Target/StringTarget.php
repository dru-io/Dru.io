<?php

namespace Drupal\feeds\Feeds\Target;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\feeds\FieldTargetDefinition;
use Drupal\feeds\Plugin\Type\Target\FieldTargetBase;

/**
 * Defines a string field mapper.
 *
 * @FeedsTarget(
 *   id = "string",
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "list_string"
 *   }
 * )
 */
class StringTarget extends FieldTargetBase {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    $definition = FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('value');

    if ($field_definition->getType() === 'string') {
      $definition->markPropertyUnique('value');
    }
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    // Trim the value if it's too long.
    if (!empty($this->settings['max_length'])) {
      $values['value'] = Unicode::substr($values['value'], 0, $this->settings['max_length']);
    }
  }

}
