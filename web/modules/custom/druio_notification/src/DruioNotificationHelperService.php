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
   * Denotes how much entities to load.
   */
  protected $limit = 5;

  /**
   * Constructs a new DruioNotificationHelperService object.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Returns user notifications.
   *
   * @param int $uid
   *   User ID for which need to load notifications.
   * @param int $limit
   *   How much notifications to load.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Notification entities if found, empty array if not.
   */
  public function getUserNotifications($uid = NULL, $limit = NULL) {
    if (!isset($uid)) {
      $uid = \Drupal::currentUser()->id();
    }
    $query = $this->entityTypeManager->getStorage('druio_notification')
      ->getQuery()
      ->condition('user_id', $uid)
      ->range(0, isset($limit) ? $limit : $this->limit);
    $result = $query->execute();
    return $result ? $this->entityTypeManager->getStorage('druio_notification')
      ->loadMultiple($result) : [];
  }

  /**
   * Returns user unread notifications.
   *
   * @param int $uid
   *   User ID for which need to load notifications.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Notification entities if found, empty array if not.
   */
  public function getUserUnreadNotifications($uid = NULL) {
    if (!isset($uid)) {
      $uid = \Drupal::currentUser()->id();
    }
    $query = $this->entityTypeManager->getStorage('druio_notification')
      ->getQuery()
      ->condition('user_id', $uid)
      ->condition('is_read', DruioNotificationInterface::NOT_READ);
    $result = $query->execute();
    return $result ? $this->entityTypeManager->getStorage('druio_notification')
      ->loadMultiple($result) : [];
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
  public function getUserUnreadCount($uid = NULL) {
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
