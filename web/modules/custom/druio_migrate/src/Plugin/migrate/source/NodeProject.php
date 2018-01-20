<?php

namespace Drupal\druio_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * @MigrateSource(
 *   id = "druio_node_project"
 * )
 */
class NodeProject extends SqlBase {

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
      ->condition('n.type', 'project');
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
      // Fields below is add programmatically in prepare row method.
      'development_status' => $this->t('Development status of module'),
      'maintenance_status' => $this->t('Maintenance status of module'),
      'project_type' => $this->t('The project type'),
      'drupal_version' => $this->t('Drupal version'),
      'project_short_name' => $this->t('The short name of the project'),
      'project_releases' => $this->t('Information about releases for particular project'),
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
    // Association for development status. TID in Drupal 7 and key for field in
    // Drupal 8.
    $development_statuses = [
      21 => 'maintenance_fixes_only',
      22 => 'no_further_development',
      23 => 'obsolete',
      20 => 'under_active_development',
    ];

    $development_status_query = $this->select('field_data_field_project_development_status', 'ds')
      ->fields('ds', ['field_project_development_status_tid'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'project')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetchCol();

    $development_status_tid = $development_status_query[0];
    $development_status = $development_statuses[$development_status_tid];
    $row->setSourceProperty('development_status', $development_status);

    // Maintenance status.
    $maintenance_statuses = [
      15 => 'actively_maintained',
      16 => 'minimally_maintained',
      17 => 'seeking_comaintainer',
      18 => 'seeking_new_maintainer',
      19 => 'unsupported',
    ];

    $maintenance_status_query = $this->select('field_data_field_project_maintenance_status', 'ms')
      ->fields('ms', ['field_project_maintenance_status_tid'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'project')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetchCol();

    $maintenance_status_tid = $maintenance_status_query[0];
    $maintenance_status = $maintenance_statuses[$maintenance_status_tid];
    $row->setSourceProperty('maintenance_status', $maintenance_status);

    // Project type.
    $project_types = [
      24 => 'core',
      25 => 'distribution',
      26 => 'module',
      27 => 'theme',
    ];

    $project_type_query = $this->select('field_data_field_project_type', 'pt')
      ->fields('pt', ['field_project_type_tid'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'project')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetchCol();

    $project_type_tid = $project_type_query[0];
    $project_type = $project_types[$project_type_tid];
    $row->setSourceProperty('project_type', $project_type);

    // Drupal version.
    $drupal_versions = [
      38 => '8.x',
      37 => '7.x',
      36 => '6.x',
      35 => '5.x',
      33 => '4.7',
      32 => '4.6',
    ];

    $drupal_version_query = $this->select('field_data_field_drupal_version', 'dv')
      ->fields('dv', ['field_drupal_version_tid'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'project')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetchCol();

    $drupal_versions_array = [];
    foreach ($drupal_version_query as $version) {
      $drupal_versions_array[] = $drupal_versions[$version];
    }
    $row->setSourceProperty('drupal_version', $drupal_versions_array);

    // Project short name.
    $project_short_name_query = $this->select('field_data_field_project_short_name', 'sn')
      ->fields('sn', ['field_project_short_name_value'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'project')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetchCol();

    $row->setSourceProperty('project_short_name', $project_short_name_query[0]);

    // Project releases.
    $project_releases = $this->select('field_data_field_project_releases', 'pr')
      ->fields('pr', ['field_project_releases_value'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'project')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetchCol();

    $row->setSourceProperty('project_releases', $project_releases[0]);

    return parent::prepareRow($row);
  }

}
