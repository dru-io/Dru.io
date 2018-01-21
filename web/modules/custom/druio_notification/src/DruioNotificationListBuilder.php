<?php

namespace Drupal\druio_notification;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Druio notification entities.
 *
 * @ingroup druio_notification
 */
class DruioNotificationListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Druio notification ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\druio_notification\Entity\DruioNotification */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.druio_notification.edit_form',
      ['druio_notification' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
