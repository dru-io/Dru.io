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

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Druio notification name.
   *
   * @return string
   *   Name of the Druio notification.
   */
  public function getName();

  /**
   * Sets the Druio notification name.
   *
   * @param string $name
   *   The Druio notification name.
   *
   * @return \Drupal\druio_notification\Entity\DruioNotificationInterface
   *   The called Druio notification entity.
   */
  public function setName($name);

  /**
   * Gets the Druio notification creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Druio notification.
   */
  public function getCreatedTime();

  /**
   * Sets the Druio notification creation timestamp.
   *
   * @param int $timestamp
   *   The Druio notification creation timestamp.
   *
   * @return \Drupal\druio_notification\Entity\DruioNotificationInterface
   *   The called Druio notification entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Druio notification published status indicator.
   *
   * Unpublished Druio notification are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Druio notification is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Druio notification.
   *
   * @param bool $published
   *   TRUE to set this Druio notification to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\druio_notification\Entity\DruioNotificationInterface
   *   The called Druio notification entity.
   */
  public function setPublished($published);

}
