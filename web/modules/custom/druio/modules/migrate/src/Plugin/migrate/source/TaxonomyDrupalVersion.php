<?php

namespace Drupal\druio_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * @MigrateSource(
 *   id = "druio_taxonomy_drupal_version"
 * )
 */
class TaxonomyDrupalVersion extends SqlBase {

  /**
   * {@inheritdoc}
   *
   * IMPORTANT! This method must return single row result, we can't use joins
   * here. Don't edit!
   */
  public function query() {
    $query = $this->select('taxonomy_term_data', 'td')
      ->fields('td', [
        'tid',
        'name',
        'weight',
      ])
      ->condition('td.vid', '6');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'nid' => $this->t('Primary Key: Unique term ID'),
      'name' => $this->t('The term name'),
      'weight' => $this->t('The weight of this term in relation to other terms'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'tid' => [
        'type' => 'integer',
        'alias' => 'td',
      ],
    ];
  }

}
