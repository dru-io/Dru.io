<?php

namespace Drupal\feeds\Feeds\Target;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\feeds\FieldTargetDefinition;
use Drupal\feeds\Plugin\Type\Target\FieldTargetBase;

/**
 * Defines a feeds_item field mapper.
 *
 * @FeedsTarget(
 *   id = "feeds_item",
 *   field_types = {"feeds_item"}
 * )
 */
class FeedsItem extends FieldTargetBase {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('url')
      ->addProperty('guid')
      ->markPropertyUnique('url')
      ->markPropertyUnique('guid');
  }

}
