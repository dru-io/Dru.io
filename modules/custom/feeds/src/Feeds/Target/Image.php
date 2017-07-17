<?php

namespace Drupal\feeds\Feeds\Target;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\feeds\FieldTargetDefinition;

/**
 * Defines a file field mapper.
 *
 * @FeedsTarget(
 *   id = "image",
 *   field_types = {"image"},
 *   arguments = {"@entity_type.manager", "@entity.query", "@http_client", "@token", "@entity_field.manager", "@entity.repository"}
 * )
 */
class Image extends File {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('target_id')
      ->addProperty('alt')
      ->addProperty('title');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    foreach ($values as $column => $value) {
      switch ($column) {
        case 'target_id':
          $values[$column] = $this->getFile($value);
          break;

        default:
          $values[$column] = (string) $value;
          break;
      }
    }
  }

}
