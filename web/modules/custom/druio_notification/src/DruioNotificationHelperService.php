<?php

namespace Drupal\druio_notification;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\druio_notification\Entity\DruioNotificationInterface;

/**
 * Class DruioNotificationHelperService.
 */
class DruioNotificationHelperService {

  /**
   * @var EntityTypeManager.
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DruioNotificationHelperService object.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Returns user notifications.
   *
   * @todo add limit.
   *
   * @param int $uid
   *   User ID for which need to load notifications.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Notification entities if found, NULL if not.
   */
  public function getUserNotifications($uid = NULL) {
    if (!isset($uid)) {
      $uid = \Drupal::currentUser()->id();
    }
    return $this->entityTypeManager->getStorage('druio_notification')
      ->loadByProperties([
        'user_id' => $uid,
      ]);
  }

  /**
   * Returns count of unread notifications for specific user.
   *
   * @param int $uid
   *   User ID for which need to get count of unread notifications.
   *
   * @return int
   *   The number of unread notifications.
   */
  public function getUserUnreadCount($uid) {
    if (!isset($uid)) {
      $uid = \Drupal::currentUser()->id();
    }
    $query = $this->entityTypeManager->getStorage('druio_notification')
      ->getQuery()
      ->condition('user_id', $uid)
      ->condition('is_read', DruioNotificationInterface::NOT_READ);
    return $query->count()->execute();
  }

}
