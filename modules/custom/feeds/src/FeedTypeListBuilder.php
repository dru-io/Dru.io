<?php

namespace Drupal\feeds;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a listing of feed types.
 *
 * @todo Would making this sortable help in specifying the importance of a feed?
 */
class FeedTypeListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['description'] = $entity->getDescription();
    $row['operations']['data'] = $this->buildOperations($entity);
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $row['label'] = $this->t('Label');
    $row['id'] = $this->t('Machine name');
    $row['description'] = $this->t('Description');
    $row['operations'] = $this->t('Operations');
    return $row;
  }

}
