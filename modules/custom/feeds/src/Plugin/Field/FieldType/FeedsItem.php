<?php

namespace Drupal\feeds\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'feeds_item' field type.
 *
 * @FieldType(
 *   id = "feeds_item",
 *   label = @Translation("Feed"),
 *   description = @Translation("Blah blah blah."),
 *   instance_settings = {
 *     "title" = "1"
 *   },
 *   default_widget = "hidden",
 *   default_formatter = "hidden",
 *   no_ui = true
 * )
 */
class FeedsItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return ['target_type' => 'feeds_feed'] + parent::defaultStorageSettings();
  }


  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['imported'] = DataDefinition::create('timestamp')
      ->setLabel(t('Timestamp'));

    $properties['url'] = DataDefinition::create('uri')
      ->setLabel(t('Item URL'));

    $properties['guid'] = DataDefinition::create('string')
      ->setLabel(t('Item GUID'));

    $properties['hash'] = DataDefinition::create('string')
      ->setLabel(t('Item hash'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'target_id' => [
          'description' => 'The ID of the target feed.',
          'type' => 'int',
          'not null' => TRUE,
          'unsigned' => TRUE,
        ],
        'imported' => [
          'type' => 'int',
          'not null' => TRUE,
          'unsigned' => TRUE,
          'description' => 'Import date of the feed item, as a Unix timestamp.',
        ],
        'url' => [
          'type' => 'text',
          'description' => 'Link to the feed item.',
        ],
        'guid' => [
          'type' => 'text',
          'description' => 'Unique identifier for the feed item.',
        ],
        'hash' => [
          'type' => 'varchar',
          // The length of an md5 hash.
          'length' => 32,
          'not null' => TRUE,
          'description' => 'The hash of the feed item.',
          'is_ascii' => TRUE,
        ],
      ],
      'indexes' => [
        'target_id' => ['target_id'],
        'lookup_url' => ['target_id', ['url', 128]],
        'lookup_guid' => ['target_id', ['guid', 128]],
        'imported' => ['imported'],
      ],
      'foreign keys' => [
        'target_id' => [
          'table' => 'feeds_feed',
          'columns' => ['target_id' => 'fid'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    $this->url = trim($this->url);
    $this->guid = trim($this->guid);
    $this->imported = (int) $this->imported;
  }

}
