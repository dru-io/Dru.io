<?php

namespace Drupal\feeds\Plugin\Type\Target;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\Exception\TargetValidationException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\FieldTargetDefinition;
use Drupal\feeds\FeedTypeInterface;
use Drupal\feeds\Plugin\Type\Processor\EntityProcessorInterface;

/**
 * Helper class for field mappers.
 */
abstract class FieldTargetBase extends TargetBase {

  /**
   * The field settings.
   *
   * @var array
   */
  protected $fieldSettings;

  /**
   * {@inheritdoc}
   */
  public static function targets(array &$targets, FeedTypeInterface $feed_type, array $definition) {
    $processor = $feed_type->getProcessor();

    if (!$processor instanceof EntityProcessorInterface) {
      return $targets;
    }

    $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($processor->entityType(), $processor->bundle());

    foreach ($field_definitions as $id => $field_definition) {
      if ($field_definition->isReadOnly() || $id === $processor->bundleKey()) {
        continue;
      }
      if (in_array($field_definition->getType(), $definition['field_types'])) {
        if ($target = static::prepareTarget($field_definition)) {
          $target->setPluginId($definition['id']);
          $targets[$id] = $target;
        }
      }
    }
  }

  /**
   * Prepares a target definition.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return \Drupal\feeds\FieldTargetDefinition
   *   The target definition.
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('value');
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $this->targetDefinition = $configuration['target_definition'];
    $this->settings = $this->targetDefinition->getFieldDefinition()->getSettings();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function setTarget(FeedInterface $feed, EntityInterface $entity, $field_name, array $values) {
    if ($values = $this->prepareValues($values)) {
      $item_list = $entity->get($field_name);

      // Append these values to the existing values.
      $values = array_merge($item_list->getValue(), $values);

      $item_list->setValue($values);
    }
  }

  /**
   * Prepares the the values that will be mapped to an entity.
   *
   * @param array $values
   *   The values.
   */
  protected function prepareValues(array $values) {
    $return = [];
    foreach ($values as $delta => $columns) {
      try {
        $this->prepareValue($delta, $columns);
        $return[] = $columns;
      }
      catch (EmptyFeedException $e) {
        // Nothing wrong here.
      }
      catch (TargetValidationException $e) {
        // Validation failed.
        drupal_set_message($e->getMessage(), 'error');
      }
    }

    return $return;
  }

  /**
   * Prepares a single value.
   *
   * @param int $delta
   *   The field delta.
   * @param array $values
   *   The values.
   */
  protected function prepareValue($delta, array &$values) {
    foreach ($values as $column => $value) {
      $values[$column] = (string) $value;
    }
  }

  protected function getUniqueQuery() {
    return \Drupal::entityQuery($this->feedType->getProcessor()->entityType())
      ->range(0, 1);
  }

  public function getUniqueValue(FeedInterface $feed, $target, $key, $value) {
    $base_fields = \Drupal::service('entity_field.manager')->getBaseFieldDefinitions($this->feedType->getProcessor()->entityType());

    if (isset($base_fields[$target])) {
      $field = $target;
    }
    else {
      $field = "$target.$key";
    }
    if ($result = $this->getUniqueQuery()->condition($field, $value)->execute()) {
      return reset($result);
    }
  }

}
