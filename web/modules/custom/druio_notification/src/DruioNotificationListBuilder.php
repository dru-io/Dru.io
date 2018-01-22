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
    $header['id'] = $this->t('Notification ID');
    $header['user'] = $this->t('User');
    $header['name'] = $this->t('Subject');
    $header['message'] = $this->t('Message');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\druio_notification\Entity\DruioNotification */
    $row['id'] = $entity->id();
    $row['user'] = Link::createFromRoute(
      $entity->user_id->entity->label(),
      'entity.user.canonical',
      ['user' => $entity->user_id->entity->id()]
    );
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.druio_notification.edit_form',
      ['druio_notification' => $entity->id()]
    );
    $row['message'] = $entity->message->value;
    return $row + parent::buildRow($entity);
  }

}
