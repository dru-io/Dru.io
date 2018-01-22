<?php

namespace Drupal\druio_notification\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Druio notification entities.
 *
 * @ingroup druio_notification
 */
interface DruioNotificationInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Denotes that notification was read by user.
   */
  const READ = TRUE;

  /**
   * Denotes that notification wasn't read by user.
   */
  const NOT_READ = FALSE;

  /**
   * Mark notification as read.
   */
  public function markAsRead();

  /**
   * Returns UNIX timestamp when notification was created.
   */
  public function getCreated();

}
