<?php

namespace Drupal\druio_migrate\Plugin\migrate\source;

use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Migration for comments related to node type question.
 *
 * This migration get all comments from database, bu on preprate state skips
 * unnecessary comments. There is no other simple way, because we cant use joins
 * in initial query.
 *
 * @MigrateSource(
 *   id = "druio_node_question_comments"
 * )
 */
class NodeQuestionComments extends SqlBase {

  /**
   * {@inheritdoc}
   *
   * IMPORTANT! This method must return single row result, we can't use joins
   * here. Don't edit!
   */
  public function query() {
    $query = $this->select('comment', 'c')
      ->fields('c', [
        'cid',
        'pid',
        'nid',
        'uid',
        'hostname',
        'created',
        'changed',
        'status',
        'name',
      ]);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');
    /*$query = $this->select('node', 'n')
      ->condition('n.type', 'question')
      ->condition('n.nid', $nid)
      ->count()
      ->execute()
      ->fetch();

    throw new MigrateSkipProcessException();*/
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'cid' => $this->t('Primary Key: Unique comment ID'),
      'pid' => $this->t('The comment.cid to which this comment is a reply. If set to 0, this comment is not a reply to an existing comment'),
      'nid' => $this->t('The node.nid to which this comment is a reply'),
      'uid' => $this->t('The users.uid who authored the comment. If set to 0, this comment was created by an anonymous user'),
      'hostname' => $this->t('The author’s host name'),
      'created' => $this->t('The time that the comment was created, as a Unix timestamp'),
      'changed' => $this->t('The time that the comment was last edited, as a Unix timestamp'),
      'status' => $this->t('The published status of a comment. (0 = Not Published, 1 = Published)'),
      'name' => $this->t('The comment author’s name. Uses users.name if the user is logged in, otherwise uses the value typed into the comment form'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'cid' => [
        'type' => 'integer',
        'alias' => 'c',
      ],
    ];
  }

}
