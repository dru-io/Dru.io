<?php

namespace Drupal\feeds\Feeds\Target;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\feeds\FieldTargetDefinition;
use Drupal\feeds\Plugin\Type\Target\FieldTargetBase;

/**
 * Defines a link field mapper.
 *
 * @FeedsTarget(
 *   id = "link",
 *   field_types = {"link"}
 * )
 */
class Link extends FieldTargetBase {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('uri')
      ->addProperty('title');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    $values['uri'] = trim($values['uri']);

    if (!UrlHelper::isValid($values['uri'], TRUE)) {
      $values['uri'] = '';
    }
  }

}
