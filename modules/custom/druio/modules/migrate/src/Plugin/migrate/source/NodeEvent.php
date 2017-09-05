<?php

namespace Drupal\druio_migrate\Plugin\migrate\source;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * @MigrateSource(
 *   id = "druio_event"
 * )
 */
class NodeEvent extends SqlBase {

  /**
   * {@inheritdoc}
   *
   * IMPORTANT! This method must return single row result, we can't use joins
   * here. Don't edit!
   */
  public function query() {
    $query = $this->select('node', 'n')
      ->fields('n', [
        'nid',
        'type',
        'title',
        'uid',
        'status',
        'created',
        'changed',
      ])
      ->condition('n.type', 'event');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'nid' => $this->t('The primary identifier for a node'),
      'type' => $this->t('The node_type.type of this node'),
      'title' => $this->t('The title of this node, always treated as non-markup plain text'),
      'uid' => $this->t('The users.uid that owns this node; initially, this is the user that created id'),
      'status' => $this->t('Boolean indicating whether the node is published (visible to non-administrators)'),
      'created' => $this->t('The Unix timestamp when the node was created'),
      'changed' => $this->t('The Unix timestamp when the node was most recently saved.'),
      // Custom added fields.
      'body_value' => $this->t('The value of body field'),
      'body_format' => $this->t('The format of body field'),
      'field_event_poster_fid' => $this->t('File ID for poster image'),
      'field_data_field_event_date' => $this->t('The date of the event'),
      'field_data_field_event_place' => $this->t('The place of the event'),
      'city_tid' => $this->t('City TID in the current database'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');

    // Body field.
    $body_query = $this->select('field_data_body', 'b')
      ->fields('b', ['body_value', 'body_format'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'event')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetch();
    $row->setSourceProperty('body_value', $body_query['body_value']);
    $row->setSourceProperty('body_format', $body_query['body_format']);

    // Poster FID.
    $poster_query = $this->select('field_data_field_event_poster', 'p')
      ->fields('p', ['field_event_poster_fid'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'event')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetch();
    $row->setSourceProperty('field_event_poster_fid', $poster_query['field_event_poster_fid']);

    // Event date.
    $event_date_query = $this->select('field_data_field_event_date', 'd')
      ->fields('d', ['field_event_date_value'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'event')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetch();
    $date = new DrupalDateTime($event_date_query['field_event_date_value']);
    $date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $formatted = $date->format(DATETIME_DATETIME_STORAGE_FORMAT);
    $row->setSourceProperty('field_event_date_value', $formatted);

    // Event place.
    $event_place_query = $this->select('field_data_field_event_place', 'p')
      ->fields('p', ['field_event_place_value'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'event')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetch();
    $row->setSourceProperty('field_event_place_value', $event_place_query['field_event_place_value']);

    // City tid. The main thing here, is to find TID in current database for
    // city, based on city name. This is done because cities is not migrated
    // from the old site, the are imported from new CSV file which has nothing
    // to do with old structure. So we need to handle this manually.
    // This Query builder is not supported for joins, so we split them.
    $old_city_tid = $this->select('field_data_field_event_city', 'c')
      ->fields('c', ['field_event_city_tid'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'event')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetch();
    $old_city_name = $this->select('taxonomy_term_data', 'n')
      ->fields('n', ['name'])
      ->condition('tid', $old_city_tid['field_event_city_tid'])
      ->execute()
      ->fetch();
    $current_city_tid = \Drupal::database()->select('taxonomy_term_field_data', 'td')
      ->fields('td', ['tid'])
      ->condition('name', $old_city_name['name'], 'LIKE')
      ->execute()
      ->fetch();
    $row->setSourceProperty('city_tid', $current_city_tid->tid);

    return parent::prepareRow($row);
  }

}
