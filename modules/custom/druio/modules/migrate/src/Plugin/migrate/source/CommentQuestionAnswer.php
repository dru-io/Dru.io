<?php

namespace Drupal\druio_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * @MigrateSource(
 *   id = "druio_comment_question_answer"
 * )
 */
class CommentQuestionAnswer extends SqlBase {

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
      ->condition('n.type', 'answer');
    return $query;
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
      ->condition('bundle', 'answer')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetch();
    $row->setSourceProperty('body_value', $body_query['body_value']);
    $row->setSourceProperty('body_format', $body_query['body_format']);

    // Question id.
    $question_id_query = $this->select('field_data_field_answer_question_reference', 'ar')
      ->fields('ar', ['field_answer_question_reference_target_id'])
      ->condition('entity_type', 'node')
      ->condition('bundle', 'answer')
      ->condition('entity_id', $nid)
      ->execute()
      ->fetch();
    $row->setSourceProperty('question_target_id', $question_id_query['field_answer_question_reference_target_id']);

    return parent::prepareRow($row);
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
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['nid']['type'] = 'integer';
    return $ids;
  }

}
