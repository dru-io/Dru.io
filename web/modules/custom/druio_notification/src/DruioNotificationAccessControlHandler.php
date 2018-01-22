<?php

namespace Drupal\druio_notification;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Druio notification entity.
 *
 * @see \Drupal\druio_notification\Entity\DruioNotification.
 */
class DruioNotificationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\druio_notification\Entity\DruioNotificationInterface $entity */
    switch ($operation) {
      case 'view':
        // @todo make new permission specific for notifications.
//        if (!$entity->isPublished()) {
//          return AccessResult::allowedIfHasPermission($account, 'view unpublished druio notification entities');
//        }
        return AccessResult::allowedIfHasPermission($account, 'view published druio notification entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit druio notification entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete druio notification entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add druio notification entities');
  }

}
